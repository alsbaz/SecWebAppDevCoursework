<?php

/**
 * This page starts off the site, prompts the user to enter details into form created by twig.
 * Starts on this page as at beginning the pattern is going to be empty, or '/'
 *
 * $_SESSION['unique_id'] used to keep tracked of logged in users, each user with unique key.
 * If user is on home page, they are logged out, therefore I am unsetting unique ID.
 *
 * $_SESSION['error'] is used to pass around possible errors. Twig contains an
 * if statement looking for the $errorMessage, and if is set, outputs it in red for user to see
 * what went wrong.
 *
 * $_SESSION['message'] is used to track which page is sending information to the central or
 * landing page.
 *
 * Using a try catch block to run the register completion process.
 * Full validation of the inputs is done with exceptions returned on fail.
 * I use switch to look at the exceptions codes to determine what what to do with them.
 *
 * header("Location: *location here*);
 * exit();
 * These are used to return to the sender page in case of issue with the user input.
 * A error message is carried along to let the user know what went wrong.
 *
 * All pages contain the basic Request and Response, as it was an easy implementation
 * from template, and can be changed down the line to be get, post or any.
 */

use Doctrine\DBAL\DriverManager;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->any('/', function(Request $request, Response $response) use ($app)
{
    $errorMessage = null;
    $messageOutput = null;
    $message = null;
//var_dump($_SESSION);
//var_dump(session_id());
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
    }
    if(isset($_SESSION['unique_id'])) unset($_SESSION['unique_id']);
//    if(isset($_SESSION)) unset($_SESSION);

    $tainted_params = $request->getParsedBody();
    try {
        if ($message == 'Register' && $tainted_params != null) {
            $validator = $this->m2mInputValidator;
            $cleaned_params = $validator->cleanParams2($tainted_params);

            $hasher = $this->m2mBcryptWrapper;

            if ($cleaned_params != false) {
                $plain_password = $cleaned_params['password'];
                $cleaned_params['password'] = $hasher->hashPassword($plain_password);
                if ($cleaned_params['password'] == false) throw new Exception("Error with password hashing", 1);
            } else {
                throw new Exception("Error while cleaning", 1);
            }

            if (!isset($_SESSION['unique_id'])) {
                $cleaned_params['unique_id'] = bin2hex(random_bytes(10));
            } else {
                unset($_SESSION['unique_id']);
                throw new Exception("Unique ID pre set, please try again", 1);
            }

            $storage_result = storeRegDetails($app, $cleaned_params);
            if ($storage_result['outcome'] == 1) {
                $messageOutput = 'Successful registered, please log in to use our services';
            } else {
                throw new Exception("Error with saving to database, please try again", 1);
            }
        }
    } catch (Exception $e) {
        switch ($e->getCode()) {
            case 0:
                $_SESSION['error'] = "This username or email is already registered" . $e->getMessage();
                header("Location: /register");
                exit();
                break;
            case 1:
                $_SESSION['error'] = $e->getMessage();
                header("Location: /register");
                exit();
            default:
            $_SESSION['error'] = $e->getMessage() . 'something';
        }
    }

    if(isset($_SESSION['error']))
    {
        $errorMessage = $_SESSION['error'];
        unset($_SESSION['error']);
    }

    $_SESSION['message'] = 'Login';
    return $this->view->render($response,
    'homepageform.html.twig',
    [
        'logout' => true,
        'css_path' => CSS_PATH,
        'page_title' => 'M2M Services',
        'landing_page' => $_SERVER["SCRIPT_NAME"],
        'action1' => 'landingpage',
        'action2' => 'register',
        'page_heading_1' => 'Login page',
        'page_heading_2' => 'Please login or choose to register',
        'error' => $errorMessage,
        'message' => $messageOutput,
    ]);
})->setName('homepage');


/**
 * @param $app
 * @param $cleaned_params
 * @return mixed
 * @throws \Doctrine\DBAL\Exception
 *
 *
 *
 * Function is used to build the database (db) connection and use Doctrine
 * predefined scripts to store data in db. Returns an array containing the outcome,
 * 1 on success, or 0 on fail, and the sql code that has been sent to the database.
 */
function storeRegDetails($app, $cleaned_params)
{
    $db_connection_settings = $app->getContainer()->get('doctrine_db_settings');
    $doctrine_queries = $app->getContainer()->get('m2mDoctrineSqlQueries');
    $db_connection = DriverManager::getConnection($db_connection_settings);

    $queryBuilder = $db_connection->createQueryBuilder();
    $storage_result = $doctrine_queries::queryStoreUserData($queryBuilder, $cleaned_params);

    return $storage_result;
}