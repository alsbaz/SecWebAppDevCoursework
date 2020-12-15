<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->post(
    '/registercomp',
    function(Request $request, Response $response) use ($app)
    {
//        $tainted_params = $request->getParsedBody();
//
//        $validator = $this->sessionValidator;
//
//        $cleaned_params = cleanParams($validator, $tainted_params);
//
//        try {
//            if($tainted_params != $cleaned_params){
//                throw new Exception("Incorrect, please try again");
//            }
//        }
//        catch(Exception $e) {
//           header("Location: /register");
//           $_SESSION['error'] = $e->getMessage();
//           exit();
//        }
        return $this->view->render($response,
            'template_page.html.twig',
            [
                'css_path' => CSS_PATH,
                'landing_page' => $_SERVER["SCRIPT_NAME"],
            ]);
    });

//function cleanParams($validator, array $tainted_params): array
//{
//    $cleaned_params = [];
//    $tainted_username = $tainted_params['username'];
//
//    $cleaned_params['username'] = $validator->sanitiseString($tainted_username);
////    $cleaned_params['password'] = $tainted_params['password']; //Easy way
//    if (preg_match("[^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$]",
//            $tainted_params['password']) && strlen($tainted_params['password']<21)){
//        $cleaned_params['password'] = $tainted_params['password'];
//    } //Hard way
//    return $cleaned_params;
//}