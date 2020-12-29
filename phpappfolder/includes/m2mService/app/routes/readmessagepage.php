<?php


use Slim\Http\Request;
use Slim\Http\Response;

$app->get(
    '/readmessagepage',
    function (Request $request, Response $response) use ($app) {
        if(!isset($_SESSION['unique_id'])) {
            header("Location: /");
            $_SESSION['error'] = 'Please log in before accessing that';
            exit();
        }

        $error = false;

        if(isset($_SESSION['error'])) {
            $error = $_SESSION['error'];
            unset($_SESSION['error']);
        }

        return $this->view->render($response,
            'readmessage.html.twig',
            [
                'css_path' => CSS_PATH,
                'action_read' => 'notImportantYet',
                'page_heading_1' => 'M2M Services',
                'page_heading_2' => 'Enter the details to read message(s)',
                'error' => $error,
                'landing_page' => 'landingpage',
                'landing_page2' => 'sendmessagepage',
                'landing_page3' => 'readmessagepage',
                'landing_page4' => $_SERVER["SCRIPT_NAME"],
            ]);
    });