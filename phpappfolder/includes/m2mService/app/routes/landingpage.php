<?php

use Doctrine\DBAL\DriverManager;
use Slim\Http\Request;
use Slim\Http\Response;

$app->any(
    '/landingpage',
    function(Request $request, Response $response) use ($app)
    {
//$time_start = microtime(true);
//$time_end = microtime(true);
//$time = ($time_end - $time_start) * 10;
//var_dump($time);
        $message = false;
        if(isset($_SESSION['message'])) switch($_SESSION['message']) {
            case 'Login':
                $tainted_params = $request->getParsedBody();
                $validator = $this->m2mInputValidator;
//var_dump($tainted_params);
//var_dump($cleaned_params);

                try {
                    if($tainted_params == null) {
                        throw new Exception('Please log in before accessing that', 2);
                    }
                    $cleaned_params = $validator->cleanParams1($tainted_params);

                    if ($cleaned_params == false) {
                        throw new Exception("Wrong username or password - failed cleaning", 2);
                    }
                    $result_hash_id = getHashLogin($app, $cleaned_params['username']);
//var_dump($result_hash_id);
                    if (empty($result_hash_id)) {
                        throw new Exception("Wrong username or password - database connection/hash retrieving error", 2);
                    }
                    $hasher = $this->m2mBcryptWrapper;
                    if ($hasher->authenticateHash($cleaned_params['password'], $result_hash_id[0]['m2m_pass_hash'])) {
                        $_SESSION['unique_id'] = $result_hash_id[0]['m2m_id'];
                    } else {
                        throw new Exception("Wrong username or password - password authentication error", 2);
                    }
                } catch (Exception $e) {
                    header("Location: /");
                    switch ($e->getCode()) {
                        case 2:
                            $_SESSION['error'] = $e->getMessage();
                            break;
                        default:
                            $_SESSION['error'] = "An unexpected error occurred, sorry for the inconvenience";
                    }
                    unset($_SESSION['message']);
                    exit();
                }
                $message = 'Welcome to the M2M service interface ' . $cleaned_params['username'] . '!';
                break;
            case 'SendComp':
                break;
            default:
                break;
        }
//var_dump($_SESSION);
        unset($_SESSION['message']);

        if(!isset($_SESSION['unique_id'])) {
            header("Location: /");
            $_SESSION['error'] = 'Please log in before accessing that';
            exit();
        }

//var_dump($_SESSION);

        return $this->view->render($response,
            'landingpage.html.twig',
            [
                'css_path' => CSS_PATH,
                'page_heading_1' => 'M2M Services',
                'page_heading_2' => 'M2M Services',
                'landing_page1' => 'landingpage',
                'landing_page2' => 'sendmessagepage',
                'landing_page3' => 'readmessagepage',
                'landing_page4' => $_SERVER["SCRIPT_NAME"],
                'message' => $message,
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