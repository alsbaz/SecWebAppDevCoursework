<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function(Request $request, Response $response)
{
    $errorMessage = null;
    if(isset($_SESSION['error']))
    {
        $errorMessage = $_SESSION['error'];
        unset($_SESSION['error']);
    }
    return $this->view->render($response,
    'homepageform.html.twig',
    [
        'css_path' => CSS_PATH,
        'landing_page' => $_SERVER["SCRIPT_NAME"],
        'action1' => 'testpage',
        'action2' => 'register',
        'page_heading_1' => 'Login page',
        'page_heading_2' => 'Please login or choose to register',
        'error' => $errorMessage,
    ]);
})->setName('homepage');