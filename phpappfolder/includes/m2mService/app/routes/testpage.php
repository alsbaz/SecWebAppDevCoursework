<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->post(
    '/testpage',
    function(Request $request, Response $response) use ($app)
    {
        $tainted_params = $request->getParsedBody();
$time_start = microtime(true);
        $validator = $this->sessionValidator;
$time_end = microtime(true);
$time = ($time_end - $time_start) * 10;
var_dump($time);

        $cleaned_params = $validator->cleanParams($tainted_params);
//var_dump($tainted_params);
//var_dump($cleaned_params);

        try {
//            if($tainted_params != $cleaned_params){
            if($cleaned_params == false){
                throw new Exception("Incorrect, please try again.");
            }
        }
        catch(Exception $e) {
            header("Location: /");
            $_SESSION['error'] = $e->getMessage();
//var_dump($_SESSION['error']);
            exit();
        }

//var_dump($cleaned_params);


        return $this->view->render($response,
            'testpage.html.twig',
            [
                'css_path' => CSS_PATH,
                'landing_page' => $_SERVER["SCRIPT_NAME"],
            ]);
    });

