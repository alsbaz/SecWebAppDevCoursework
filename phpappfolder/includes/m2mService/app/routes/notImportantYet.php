<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->post(
    '/notImportantYet',
    function(Request $request, Response $response) use ($app)
    {
        if(!isset($_SESSION['unique_id'])) { //For any logged in content
            header("Location: /");
            $_SESSION['error'] = 'Please log in before accessing that';
            exit();
        }

        $tainted_params = $request->getParsedBody();

        $soapModel = $this->m2mSoapModel;
        $soapModel->method_to_use = 'peekMessages';
        $soapModel->username = $tainted_params['username'];
        $soapModel->password = $tainted_params['password'];
        $soapModel->device_MSISDN = $tainted_params['msisdn'];
        $soapModel->count = $tainted_params['count'];

        $test = $soapModel->performSoapCall();
var_dump($soapModel->result);
        return $this->view->render($response,
            'template4loggedin.html.twig',
            [
                'css_path' => CSS_PATH,
                'landing_page' => 'landingpage',
                'landing_page2' => 'sendmessagepage',
                'landing_page3' => 'readmessagepage',
                'landing_page4' => 'downloadmessagepage',
                'landing_page5' => $_SERVER["SCRIPT_NAME"],
            ]);
    });