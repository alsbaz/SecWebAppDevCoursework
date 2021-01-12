<?php

/**
 * The register action takes to this page or /register.
 *
 */

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/register', function (Request $request, Response $response) use ($app)
{
    $logger = $this->loggerWrapper;
    $errorMessage = null;
    if(isset($_SESSION['error']))
    {
        $logger->logAction($_SESSION['error'], $_SERVER['REMOTE_ADDR'], 'ERROR', 'Error message: ');
        $errorMessage = $_SESSION['error'];
        unset($_SESSION['error']);
    }

    $_SESSION['message'] = 'Register';

    return $this->view->render($response,
        'registerform.html.twig',
        [
            'logout' => true,
            'css_path' => CSS_PATH,
            'page_title' => 'M2M Services',
            'landing_page' => $_SERVER["SCRIPT_NAME"],
            'action1' => $_SERVER["SCRIPT_NAME"],
            'page_heading_1' => 'Registration',
            'page_heading_2' => 'Please enter your details for registration',
            'error' => $errorMessage,
        ]);
})->setName('register');