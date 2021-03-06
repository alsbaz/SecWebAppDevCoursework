<?php

use Doctrine\DBAL\DriverManager;
use Slim\Http\Request;
use Slim\Http\Response;

$app->any(
    '/landingpage',
    function(Request $request, Response $response) use ($app)
    {
        $logger = $this->loggerWrapper;
//$time_start = microtime(true);
//$time_end = microtime(true);
//$time = ($time_end - $time_start) * 10;
//var_dump($time);
//        if(!isset($_SESSION['message'])) $_SESSION['message'] = 'SendCompAuto'; //If want to generate messages automatically
//        if(!isset($_SESSION['unique_id']) && $_SESSION['message'] != 'Login') {
//            header("Location: /");
//            $_SESSION['error'] = 'Please log in before accessing that';
//            exit();
//        }
//var_dump($_SESSION);
        $tainted_params = $request->getParsedBody();
        if (isset($_SESSION['unique_id']) && $tainted_params != null) {
            $logger->logAction($tainted_params, $_SERVER['REMOTE_ADDR'] . $_SESSION['unique_id'], 'INPUT', 'Input values');
        } elseif ($tainted_params != null) {
            $logger->logAction($tainted_params, $_SERVER['REMOTE_ADDR'], 'INPUT', 'Input values');
        }

        $message = false;
        $result_array = false;
        $rank = null;
        try {
            if(isset($_SESSION['message'])) switch($_SESSION['message']) {
                case 'Login':
                    $validator = $this->m2mInputValidator;
                    if($tainted_params == null) {
                        throw new Exception('Please log in before accessing that', 2);
                    }
                    $cleaned_params = $validator->cleanParams1($tainted_params);

                    if ($cleaned_params == false) {
                        $logger->logAction($tainted_params, $_SERVER['REMOTE_ADDR'], 'WARNING', 'Validation failed, Input values');
                        throw new Exception("Wrong username or password", 2);
                    }
                    $result_hash_id = getHashLogin($app, $cleaned_params['username']);
                    if (empty($result_hash_id)) {
                        $logger->logAction($tainted_params, $_SERVER['REMOTE_ADDR'], 'WARNING', 'User not found in db, Input values');
                        throw new Exception("Wrong username or password", 2);

                    }
                    $hasher = $this->m2mBcryptWrapper;
                    if ($hasher->authenticateHash($cleaned_params['password'], $result_hash_id[0]['m2m_pass_hash'])) {
                        $_SESSION['unique_id'] = $result_hash_id[0]['m2m_id'];
                        $_SESSION['username'] = $cleaned_params['username'];
//                        $_SESSION['email'] = $result_hash_id[0]['m2m_email'];
                        if($result_hash_id[0]['m2m_admin'] == 1) {
                            $_SESSION['rank'] = 'Admin';
                        } else {
                            $_SESSION['rank'] = 'User';
                        }
                    } else {
                        $logger->logAction($tainted_params, $_SERVER['REMOTE_ADDR'], 'WARNING', 'Wrong password, Input values');
                        throw new Exception("Wrong username or password", 2);
                    }

                    $message = 'Welcome to the M2M service interface ' . $cleaned_params['username'] . '!';
                    break;
                case 'SendComp':
                    if($tainted_params == null) break;
                    $method = 'sendMessage';

                    if ($tainted_params['message'] == '') {
                        throw new Exception("Message field cannot be empty", 3);
                    } elseif (strlen($tainted_params['message']) > 65) {
                        throw new Exception("Message too long", 3);
                    }

                    $sanitiser = $this->m2mInputValidator;
                    $cleaned_params = $sanitiser->sanitiseInput($tainted_params);

                    $result = doSoapFunction($app, $cleaned_params, $method);
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

                    $sanitiser = $this->m2mInputValidator;
                    $cleaned_params = $sanitiser->sanitiseInput($tainted_params);
                    $result_messages = doSoapFunction($app, $cleaned_params, $method);
                    $handler = $this->m2mMessageHandler;

                    $result_array = $handler->splitMessageRegex($result_messages);
                    if($result_array == null) $message = 'No messages have been received.';
                    break;
                case 'DownloadComp':
                    if($tainted_params == null) break;
                    $method = 'peekMessages';

                    $sanitiser = $this->m2mInputValidator;
                    $cleaned_params = $sanitiser->sanitiseInput($tainted_params);

                    $result_messages = doSoapFunction($app, $cleaned_params, $method);
                    if($result_messages == null) {
                        $message = 'No messages have been received.';
                        break;
                    }
                    $handler = $this->m2mMessageHandler;
                    $split_array = $handler->splitMessageRegex($result_messages);

                    if($split_array != null) {
                        $result_array = storageM2mMessages($app, $split_array);
                        if ($result_array == true) {
                            $message = 'Successfully saved to database.';
                        } else {
                            $message = 'No new messages.';
                        }
                    } else $message = 'No new messages.';
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


                    $sanitiser = $this->m2mInputValidator;
                    $cleaned_params = $sanitiser->sanitiseInput($tainted_params);

                    if(empty($tainted_params))
                        throw new Exception("Please enter at least one parameter to search by", 3);
                    $result_array = getM2mMessages($app, $cleaned_params);
                    if($result_array == null) $message = 'No matches were found.';
                    break;
                case 'AdminSetting':
                    if($tainted_params == null) break;
                    if($tainted_params['heaterTemp'] == '' || $tainted_params['lastDigit'] == ''){
                        throw new Exception("Please fill each field", 3);
                    } elseif(strlen($tainted_params['heaterTemp']) > 3 || strlen($tainted_params['lastDigit']) > 1) {
                        throw new Exception("Funny one, please be serious", 3);
                    }

                    $query_outcome = queryUpdateM2mSwitch($app, $tainted_params);
                    if($query_outcome['outcome'] == 1) {
                        $message = 'Successfully updated switchboard status.';
                    }
                    elseif ($query_outcome['outcome'] == 0) {
                        $message = 'Switchboard wasn\'t changed.';
                    }
                    break;
                default:
                    break;
            }
            else {
                $handler = $this->m2mBaseFunctions;
                $error = $handler->baseFunctions($app);
            }
//                $method = 'sendMessageAuto';
//                $result = doSoapFunction($app, $tainted_params, $method);
//
//
//                $method = 'peekMessages';
//
//                $params = [
//                    'username' => '20_17209674',
//                    'password' => 'CGs74bktVKzAHxC',
//                ];
//                $result_messages = doSoapFunction($app, $params, $method);
//                if ($result_messages != null) {
//                    $handler = $this->m2mMessageHandler;
//                    $split_array = $handler->splitMessageRegex($result_messages);
//
//                    if ($split_array != null) {
//                        storageM2mMessages($app, $split_array);
//                    }
//                }
//            }


            $switchboard_result_unhandled = getSwithboardState($app);

            $switchboard_result = [
                'Last Updated: ' => $switchboard_result_unhandled[0]['switch_timestamp'],
                'Switch 1: ' => $switchboard_result_unhandled[0]['switch1'],
                'Switch 2: ' => $switchboard_result_unhandled[0]['switch2'],
                'Switch 3: ' => $switchboard_result_unhandled[0]['switch3'],
                'Switch 4: ' => $switchboard_result_unhandled[0]['switch4'],
                'Fan Direction: ' => $switchboard_result_unhandled[0]['fan'],
                'Temperature: ' => $switchboard_result_unhandled[0]['heaterTemp'],
                'Keypad Last Digit: ' => $switchboard_result_unhandled[0]['lastDigit']
            ];

            $limit = 15;
            $feed_message = 'Most recent ' . $limit . ' messages from the database:';
            $result_array1 = queryRetrieveM2mMessagesLimit($app, $limit);

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
                    if (isset($_SESSION['unique_id'])) {
                        $logger->logAction($tainted_params, $_SERVER['REMOTE_ADDR'] . $_SESSION['unique_id'], 'WARNING', $e->getMessage() . 'Input values');
                    } else {
                        $logger->logAction($tainted_params, $_SERVER['REMOTE_ADDR'], 'WARNING', $e->getMessage() . 'Input values');
                    }
                    $_SESSION['error'] = "An unexpected error occurred, sorry for the inconvenience";
            }
            unset($_SESSION['message']);
            exit();
        }
        unset($_SESSION['message']);

        $handler = $this->m2mBaseFunctions;
        $error = $handler->baseFunctions($app);

        return $this->view->render($response,
            'landingpage.html.twig',
            [
                'css_path' => CSS_PATH,
                'page_title' => 'M2M Services',
                'page_heading_1' => 'M2M Services',
                'page_heading_2' => 'M2M Services',
                'error' => $error,
                'message' => $message,
                'feed_message' => $feed_message,
                'message_array' => $result_array,
                'message_array_feed' => $result_array1,
                'switchboard_result' => $switchboard_result,
                'landing_page' => 'landingpage',
                'landing_page2' => 'sendmessagepage',
                'landing_page3' => 'readmessagepage',
                'landing_page4' => 'downloadmessagepage',
                'landing_page5' => $_SERVER["SCRIPT_NAME"],
                'landing_page6' =>'showdownloadedpage',
                'landing_page7' => 'adminsettings',
                'rank' => $_SESSION['rank'],
                'trigger' => true,
        ]);
    });

function doSoapFunction($app, $params, $method)
{
    $soapModel = $app->getContainer()->get('m2mSoapModel');
    $soapModel->method_to_use = $method;
    if(isset($params['username'])) $soapModel->username = $params['username'];
    if(isset($params['password'])) $soapModel->password = $params['password'];
    if(isset($params['msisdn'])) {
        $soapModel->device_MSISDN = $params['msisdn'];
    } else {
        $soapModel->device_MSISDN = '';
    }
    if(isset($params['count'])) {
        $soapModel->count = $params['count'];
    } else {
        $soapModel->count = '';
    }
    if(isset($params['message'])) $soapModel->message = $params['message'];
    if(isset($params['mtBearer'])) $soapModel->mt_bearer = $params['mtBearer'];
    $test = $soapModel->performSoapCall();

    return $soapModel->result;
}

function getHashLogin($app, $param)
{
    $db_handlers = dbSetupConnection($app);
    $hash_to_check = $db_handlers['doctrine_queries']::queryRetrieveUserData($db_handlers['query_builder'], $param);

    return $hash_to_check;
}

function storageM2mMessages($app, $params)
{
    $db_handlers = dbSetupConnection($app);
    $db_handlers['query_builder2'] = $db_handlers['connection']->createQueryBuilder();
    $storage_result = $db_handlers['doctrine_queries']::queryStoreM2mMessages($db_handlers['query_builder'], $params, $db_handlers['query_builder2']);

    return $storage_result;
}

function getM2mMessages($app, array $params)
{
    $db_handlers = dbSetupConnection($app);
    $matching_db_entries = $db_handlers['doctrine_queries']::queryRetrieveM2mMessages($db_handlers['query_builder'], $params);

    return $matching_db_entries;
}

function queryUpdateM2mSwitch($app, $params)
{
    $db_handlers = dbSetupConnection($app);
    $storage_result = $db_handlers['doctrine_queries']::queryUpdateM2mSwitch($db_handlers['query_builder'], $params);

    return $storage_result;
}

function getSwithboardState($app)
{
    $db_handlers = dbSetupConnection($app);
    $queryResult = $db_handlers['doctrine_queries']::getSwitchboardState($db_handlers['query_builder']);

    return $queryResult;
}

function queryRetrieveM2mMessagesLimit($app, $limit)
{
    $db_handlers = dbSetupConnection($app);
    $storage_result = $db_handlers['doctrine_queries']::queryRetrieveM2mMessagesLimit($db_handlers['query_builder'], $limit);
    $db_handlers['log']->sqlLogging($app, 'selectResult');

    return $storage_result;
}

function dbSetupConnection($app)
{
    $db_handlers['settings'] = $app->getContainer()->get('doctrine_db_settings');
    $db_handlers['doctrine_queries'] = $app->getContainer()->get('m2mDoctrineSqlQueries');
    $db_handlers['connection'] = DriverManager::getConnection($db_handlers['settings']);
    $db_handlers['query_builder'] = $db_handlers['connection']->createQueryBuilder();
    $log = $app->getContainer()->get('m2mBaseFunctions');
    $log->sqlLogging($app, 'connection');
    $db_handlers['log'] = $log;

    return $db_handlers;
}

