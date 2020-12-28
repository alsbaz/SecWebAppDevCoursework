<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->post(
    '/nameHere',
    function(Request $request, Response $response) use ($app)
    {
        if(!isset($_SESSION['unique_id'])) { //For any logged in content
            header("Location: /");
            $_SESSION['error'] = 'Please log in before accessing that';
            exit();
        }

        return $this->view->render($response,
            'template.html.twig',
            [
                'css_path' => CSS_PATH,
                'landing_page' => $_SERVER["SCRIPT_NAME"],
            ]);
    });