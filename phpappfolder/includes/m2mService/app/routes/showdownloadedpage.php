<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get(
    '/showdownloadedpage',
    function(Request $request, Response $response) use ($app) {
        if(!isset($_SESSION['unique_id'])) { //For any logged in content
            header("Location: /");
            $_SESSION['error'] = 'Please log in before accessing that';
            exit();
        }

        $rank = true;
        $error = false;

        if(isset($_SESSION['error'])) {
            $error = $_SESSION['error'];
            unset($_SESSION['error']);
        }

        $_SESSION['message'] = 'ShowDownloaded';


        return $this->view->render($response,
            'showdownloadedpage.html.twig',
            [
                'css_path' => CSS_PATH,
                'page_title' => 'M2M Services',
                'action' => 'landingpage',
                'page_heading_1' => 'M2M Services',
                'page_heading_2' => 'Enter details of the messages you want to retrieve from database',
                'error' => $error,
                'initial_time_box_value' => 'Format: YYYY-MM-DD *HH:MM:SS*',
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