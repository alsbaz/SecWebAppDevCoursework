<?php

use Doctrine\DBAL\DriverManager;
use Slim\Http\Request;
use Slim\Http\Response;

$app->post(
    '/registercomp',
    function(Request $request, Response $response) use ($app)
    {

//$time_start = microtime(true);

        $tainted_params = $request->getParsedBody();
//var_dump($tainted_params);

        $validator = $this->m2mInputValidator;
        $cleaned_params = $validator->cleanParams2($tainted_params);
//var_dump($cleaned_params);

        $hasher = $this->m2mBcryptWrapper;
        if($cleaned_params != false){
            $plain_password = $cleaned_params['password'];
            $cleaned_params['password'] = $hasher->hashPassword($plain_password);
        }

        if(!isset($_SESSION['unique_id'])){
            $_SESSION['unique_id'] = bin2hex(random_bytes(10));
        }

        try {
            if($cleaned_params == false){
                throw new Exception("Incorrect inputs, please try again.", 2);
            } elseif($cleaned_params['password'] == false){
                throw new Exception("Error with password hashing", 9999);
            }
            $cleaned_params['unique_id'] = $_SESSION['unique_id'];
//var_dump($cleaned_params['unique_id']);

            $storage_result = storeRegDetails($app, $cleaned_params);
//var_dump($storage_result);
        }
        catch(Exception $e) {
            header("Location: /register");
            unset($_SESSION['unique_id']);
            switch ($e->getCode()) {
                case 0:
                    $_SESSION['error'] = "This username or email is already registered" . $e->getMessage();
                    break;
                case 2:
                    $_SESSION['error'] = $e->getMessage();
                    break;
                default:
                    $_SESSION['error'] = "An unexpected error occurred, sorry for the inconvenience" . $e->getMessage();
            }
//            var_dump($e->getCode());
//var_dump($e->getMessage());
            exit();
        }

        unset($_SESSION['unique_id']);

//$time_end = microtime(true);
//$time = ($time_end - $time_start) * 10;
//var_dump($time);

        return $this->view->render($response,
            'template_page.html.twig',
            [
                'css_path' => CSS_PATH,
                'landing_page' => $_SERVER["SCRIPT_NAME"],
            ]);
    });


function storeRegDetails($app, $cleaned_params)
{
    $storage_result = [];
    $store_result = '';
    $db_connection_settings = $app->getContainer()->get('doctrine_db_settings');
    $doctrine_queries = $app->getContainer()->get('m2mDoctrineSqlQueries');
    $db_connection = DriverManager::getConnection($db_connection_settings);

    $queryBuilder = $db_connection->createQueryBuilder();
    $storage_result = $doctrine_queries::queryStoreUserData($queryBuilder, $cleaned_params); //$hashed_password

    return $storage_result;
}