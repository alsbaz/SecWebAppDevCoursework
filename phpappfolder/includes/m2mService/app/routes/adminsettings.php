<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get(
    '/adminsettings',
    function (Request $request, Response $response) use ($app) {
        if($_SESSION['rank'] != 'Admin') {
            header("Location: landingpage");
            $_SESSION['error'] = 'You don\'t have permissions to access that.';
            exit();
        }

        $_SESSION['message'] = 'AdminSetting';

        $handler = $this->m2mBaseFunctions;
        $error = $handler->baseFunctions($app);

        return $this->view->render($response,
            'adminsettings.html.twig',
            [
                'css_path' => CSS_PATH,
                'page_title' => 'M2M Services',
                'action' => 'landingpage',
                'page_heading_1' => 'M2M Services',
                'page_heading_2' => 'Change the details of the switchboard',
                'error' => $error,
                'landing_page' => 'landingpage',
                'landing_page2' => 'sendmessagepage',
                'landing_page3' => 'readmessagepage',
                'landing_page4' => 'downloadmessagepage',
                'landing_page5' => $_SERVER["SCRIPT_NAME"],
                'landing_page6' =>'showdownloadedpage',
                'landing_page7' => 'adminsettings',
                'rank' => $_SESSION['rank'],
        ]);
    });