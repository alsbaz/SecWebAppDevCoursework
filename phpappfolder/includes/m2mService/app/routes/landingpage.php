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
        if(!isset($_SESSION['message'])) $_SESSION['message'] = 'SendCompAuto'; //If want to generate messages automatically
        $tainted_params = $request->getParsedBody();
        $message = false;
        $result_array = false;
        try {
            if(isset($_SESSION['message'])) switch($_SESSION['message']) {
                case 'Login':
                    $validator = $this->m2mInputValidator;
                    if($tainted_params == null) {
                        throw new Exception('Please log in before accessing that', 2);
                    }
                    $cleaned_params = $validator->cleanParams1($tainted_params);

                    if ($cleaned_params == false) {
                        throw new Exception("Wrong username or password - failed cleaning", 2);
                    }
                    $result_hash_id = getHashLogin($app, $cleaned_params['username']);
                    if (empty($result_hash_id)) {
                        throw new Exception("Wrong username or password - database connection/hash retrieving error", 2);
                    }
                    $hasher = $this->m2mBcryptWrapper;
                    if ($hasher->authenticateHash($cleaned_params['password'], $result_hash_id[0]['m2m_pass_hash'])) {
                        $_SESSION['unique_id'] = $result_hash_id[0]['m2m_id'];
                        $_SESSION['username'] = $cleaned_params['username'];
                        $_SESSION['email'] = $result_hash_id[0]['m2m_email'];
                    } else {
                        throw new Exception("Wrong username or password - password authentication error", 2);
                    }

                    $message = 'Welcome to the M2M service interface ' . $cleaned_params['username'] . '!';
                    break;
                case 'SendComp':
                    if($tainted_params == null) break;
                    $method = 'sendMessage';
//var_dump($tainted_params);
                    if ($tainted_params['message'] == '') {
                        throw new Exception("Please enter a message to send", 3);
                    } elseif (strlen($tainted_params['message']) > 39015) {
                        throw new Exception("Message too long", 3);
                    }
                    $result = doSoapFunction($app, $tainted_params, $method);
                    if($result == true) $message = 'Message sent successfully.';
                    break;
                case 'SendCompAuto':
                    $method = 'sendMessageAuto';
                    $result = doSoapFunction($app, $tainted_params, $method);
                    if($result == true) $message = 'Message sent successfully.';
                    break;
                case 'ReadComp':
                    if($tainted_params == null) break;
                    $method = 'peekMessages';
                    $result_messages = doSoapFunction($app, $tainted_params, $method);

                    $handler = $this->m2mMessageHandler;
                    $result_array = $handler->splitMessageRegex($result_messages);
                    if($result_array == null) $message = 'No messages have been received.';
//var_dump($result_messages);
                    break;
                case 'DownloadComp':
                    if($tainted_params == null) break;
                    $method = 'readMessages';
                    $result_messages = doSoapFunction($app, $tainted_params, $method);
                    if($result_messages == null) {
                        $message = 'No messages have been received.';
                        break;
                    }
//var_dump($result_messages);
                    $handler = $this->m2mMessageHandler;
                    $result_array = $handler->splitMessageRegex($result_messages);
//var_dump($result_array);
                    $result = storageM2mMessages($app, $result_array);
                    if ($result == true) $message = 'Successfully saved to database.';
                    break;
                case 'ShowDownloaded':
                    if($tainted_params == null) break;
                    foreach ($tainted_params as $param => $value) {
                        if(empty($value)) unset($tainted_params[$param]);
                    }
                    if(isset($tainted_params['receivedtime'])) {
                        if($tainted_params['receivedtime'] == 'Format: YYYY-MM-DD *HH:MM:SS*') {
                            unset($tainted_params['receivedtime']);
                        } elseif(!preg_match("~^(\d{4}-\d{2}-\d{2})$|^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})$~", $tainted_params['receivedtime'])) {
                            throw new Exception("Please enter the date and time in valid format", 3);
                        }
                    }
//var_dump($tainted_params);
                    if(empty($tainted_params))
                        throw new Exception("Please enter at least one parameter to search by", 3);
                    $result_array = getM2mMessages($app, $tainted_params);
//var_dump($result_array);
                    break;
                default:
                    break;
            }
        } catch (Exception $e) {
            switch ($e->getCode()) {
                case 2:
                    header("Location: /");
                    $_SESSION['error'] = $e->getMessage();
                    break;
                case 3:
                    header("Location: " . $_SERVER['HTTP_REFERER']);
                    $_SESSION['error'] = $e->getMessage();
                    break;
                default:
                    header("Location: " . $_SERVER['HTTP_REFERER']);
                    $_SESSION['error'] = "An unexpected error occurred, sorry for the inconvenience" . $e->getMessage();
            }
            unset($_SESSION['message']);
            exit();
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
                'page_title' => 'M2M Services',
                'page_heading_1' => 'M2M Services',
                'page_heading_2' => 'M2M Services',
                'message' => $message,
                'message_array' => $result_array,
                'landing_page' => 'landingpage',
                'landing_page2' => 'sendmessagepage',
                'landing_page3' => 'readmessagepage',
                'landing_page4' => 'downloadmessagepage',
                'landing_page5' => $_SERVER["SCRIPT_NAME"],
                'landing_page6' =>'showdownloadedpage',

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

function doSoapFunction($app, $tainted_params, $method)
{
    $soapModel = $app->getContainer()->get('m2mSoapModel');
    $soapModel->method_to_use = $method;
    if(isset($tainted_params['username'])) $soapModel->username = $tainted_params['username'];
    if(isset($tainted_params['password'])) $soapModel->password = $tainted_params['password'];
    if(isset($tainted_params['msisdn'])) $soapModel->device_MSISDN = $tainted_params['msisdn'];
    if(isset($tainted_params['count'])) $soapModel->count = $tainted_params['count'];
    if(isset($tainted_params['message'])) $soapModel->message = $tainted_params['message'];

    $test = $soapModel->performSoapCall();

    return $soapModel->result;
}

function storageM2mMessages($app, $params)
{
    $db_connection_settings = $app->getContainer()->get('doctrine_db_settings');
    $doctrine_queries = $app->getContainer()->get('m2mDoctrineSqlQueries');
    $db_connection = DriverManager::getConnection($db_connection_settings);

    $queryBuilder = $db_connection->createQueryBuilder();
    $storage_result = $doctrine_queries::queryStoreM2mMessages($queryBuilder, $params);

    return $storage_result;
}

function getM2mMessages($app, array $params)
{
    $db_connection_settings = $app->getContainer()->get('doctrine_db_settings');
    $doctrine_queries = $app->getContainer()->get('m2mDoctrineSqlQueries');
    $db_connection = DriverManager::getConnection($db_connection_settings);

    $queryBuilder = $db_connection->createQueryBuilder();
    $matching_db_entries = $doctrine_queries::queryRetrieveM2mMessages($queryBuilder, $params);
    return $matching_db_entries;
}