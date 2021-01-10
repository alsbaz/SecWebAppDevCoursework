<?php

use Doctrine\DBAL\DriverManager;
use Slim\Http\Request;
use Slim\Http\Response;

$app->post( // change to any for testing
    '/registercomp',
    function(Request $request, Response $response) use ($app)
    {









        try {
//            if($cleaned_params == false){
//                throw new Exception("Incorrect inputs, please try again.", 2);
//            } elseif($cleaned_params['password'] == false){
//                throw new Exception("Error with password hashing", 9999);
//            }

            $cleaned_params['unique_id'] = $_SESSION['unique_id'];

            $storage_result = storeRegDetails($app, $cleaned_params);
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
            exit();
        }

        unset($_SESSION['unique_id']);

        return $this->view->render($response,
            'template.html.twig',
            [
                'logout' => true,
                'css_path' => CSS_PATH,
                'page_title' => 'M2M Services',
                'landing_page' => $_SERVER["SCRIPT_NAME"],
            ]);
    });


//function storeRegDetails($app, $cleaned_params)
//{
//    $storage_result = [];
//    $store_result = '';
//    $db_connection_settings = $app->getContainer()->get('doctrine_db_settings');
//    $doctrine_queries = $app->getContainer()->get('m2mDoctrineSqlQueries');
//    $db_connection = DriverManager::getConnection($db_connection_settings);
//
//    $queryBuilder = $db_connection->createQueryBuilder();
//    $storage_result = $doctrine_queries::queryStoreUserData($queryBuilder, $cleaned_params);
//
//    return $storage_result;
//}