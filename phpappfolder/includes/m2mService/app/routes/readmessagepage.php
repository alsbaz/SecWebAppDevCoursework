<?php


use Slim\Http\Request;
use Slim\Http\Response;

$app->get(
    '/readmessagepage',
    function (Request $request, Response $response) use ($app) {
        if(!isset($_SESSION['unique_id'])) {
            header("Location: /");
            $_SESSION['error'] = 'Please log in before accessing that';
            $logger = $this->loggerWrapper;
            $logger->logAction($_SESSION['error'], $_SESSION['unique_id'], 'ERROR');
            exit();
        }

        $rank = true;
        $error = false;

        if(isset($_SESSION['error'])) {
            $error = $_SESSION['error'];
            $logger = $this->loggerWrapper;
            $logger->logAction($_SESSION['error'], $_SESSION['unique_id'], 'ERROR');
            unset($_SESSION['error']);
        }

        $_SESSION['message'] = 'ReadComp';
        $logger = $this->loggerWrapper;
        $logger->logAction($_SESSION['message'], $_SESSION['unique_id'], 'INFO');
        return $this->view->render($response,
            'readmessage.html.twig',
            [
                'css_path' => CSS_PATH,
                'action_read' => 'landingpage',
                'page_title' => 'M2M Services',
                'page_heading_1' => 'M2M Services',
                'page_heading_2' => 'Enter the details to read messages',
                'error' => $error,
                'landing_page' => 'landingpage',
                'landing_page2' => 'sendmessagepage',
                'landing_page3' => 'readmessagepage',
                'landing_page4' => 'downloadmessagepage',
                'landing_page5' => $_SERVER["SCRIPT_NAME"],
                'landing_page6' =>'showdownloadedpage',
                'landing_page7' => 'adminsettings',
                'rank' => $rank,


            ]);
    });