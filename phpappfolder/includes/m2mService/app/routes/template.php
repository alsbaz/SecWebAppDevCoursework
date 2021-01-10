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

        $error = $_SESSION['error'];

        return $this->view->render($response,
            'template.html.twig',
            [
                'css_path' => CSS_PATH,
                'page_title' => 'M2M Services',
                'action_read' => 'landingpage',
                'page_heading_1' => 'M2M Services',
                'page_heading_2' => 'Enter the details to read message(s)',
                'error' => $error,
                'landing_page' => 'landingpage',
                'landing_page2' => 'sendmessagepage',
                'landing_page3' => 'readmessagepage',
                'landing_page4' => 'downloadmessagepage',
                'landing_page5' => $_SERVER["SCRIPT_NAME"],
                'landing_page6' =>'showdownloadedpage',
            ]);
    });