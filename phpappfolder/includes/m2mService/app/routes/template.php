<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->post(
    '/nameHere',
    function(Request $request, Response $response) use ($app)
    {

        return $this->view->render($response,
            'template_page.html.twig',
            [
                'css_path' => CSS_PATH,
                'landing_page' => $_SERVER["SCRIPT_NAME"],
            ]);
    });