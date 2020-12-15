<?php


use Slim\Http\Request;
use Slim\Http\Response;

$app->get(
    '/register',
    function (Request $request, Response $response) use ($app) {

        return $this->view->render($response,
            'registerform.html.twig',
            [
                'css_path' => CSS_PATH,
                'landing_page' => $_SERVER["SCRIPT_NAME"],
                'action1' => 'registercomp',
                'page_heading_1' => 'Registration',
                'page_heading_2' => 'Please enter your details for registration',
            ]);
    });