<?php

use Doctrine\DBAL\DriverManager;
use Slim\Http\Request;
use Slim\Http\Response;

$app->post(
    '/testpage',
    function(Request $request, Response $response) use ($app)
    {

//$time_start = microtime(true);

        $tainted_params = $request->getParsedBody();

        $validator = $this->m2mInputValidator;
        $cleaned_params = $validator->cleanParams1($tainted_params);
//var_dump($tainted_params);
//var_dump($cleaned_params);

        try {
            if($cleaned_params == false){
                throw new Exception("Wrong username or password1", 2);
            }

            $result_hash_id = getHashLogin($app, $cleaned_params['username']);
//var_dump($result_hash_id);
            if(empty($result_hash_id)){
                throw new Exception("Wrong username or password2", 2);
            }

            $hasher = $this->m2mBcryptWrapper;
            if($hasher->authenticateHash($cleaned_params['password'], $result_hash_id[0]['m2m_pass_hash'])){
                $_SESSION['unique_id'] = $result_hash_id[0]['m2m_id'];
            } else{
                throw new Exception("Wrong username or password3", 2);
            }


        }
                catch(Exception $e) {
            header("Location: /");
            switch ($e->getCode()) {
                case 2:
                    $_SESSION['error'] = $e->getMessage();
                    break;
                default:
                    $_SESSION['error'] = "An unexpected error occurred, sorry for the inconvenience";
            }
            exit();
        }

        $soapModel = $this->m2mSoapModel;
        $soapModel->method_to_use = 'sendMessage';

        $test = $soapModel->performSoapCall();
//var_dump($soapModel->soapFunctionHere());
//$time_end = microtime(true);
//$time = ($time_end - $time_start) * 10;
//var_dump($time);

        return $this->view->render($response,
            'testpage.html.twig',
            [
                'css_path' => CSS_PATH,
                'landing_page' => $_SERVER["SCRIPT_NAME"],
            ]);
    });

function getHashLogin($app, $cleaned_param)
{
    $storage_result = [];
    $store_result = '';
    $db_connection_settings = $app->getContainer()->get('doctrine_db_settings');
    $doctrine_queries = $app->getContainer()->get('m2mDoctrineSqlQueries');
    $db_connection = DriverManager::getConnection($db_connection_settings);

    $queryBuilder = $db_connection->createQueryBuilder();
    $hash_to_check = $doctrine_queries::queryRetrieveUserData($queryBuilder, $cleaned_param);
    return $hash_to_check;
}