<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->post(
    '/registercomp',
    function(Request $request, Response $response) use ($app)
    {
        $tainted_params = $request->getParsedBody();
//var_dump($tainted_params);

        $validator = $this->m2mInputValidator;
        $cleaned_params = $validator->cleanParams2($tainted_params);

        try {
            if($cleaned_params == false){
                throw new Exception("Incorrect inputs, please try again.");
            }
        }
        catch(Exception $e) {
            header("Location: /register");
            $_SESSION['error'] = $e->getMessage();
//var_dump($_SESSION['error']);
            exit();
        }

        return $this->view->render($response,
            'template_page.html.twig',
            [
                'css_path' => CSS_PATH,
                'landing_page' => $_SERVER["SCRIPT_NAME"],
            ]);
    });

