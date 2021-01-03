<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function(Request $request, Response $response)
{
    if(isset($_SESSION['unique_id'])) unset($_SESSION['unique_id']);
    $errorMessage = null;
    if(isset($_SESSION['error']))
    {
        $errorMessage = $_SESSION['error'];
        unset($_SESSION['error']);
    }
    $_SESSION['message'] = 'Login';
//var_dump($_SESSION);
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
    ]);
})->setName('homepage');
