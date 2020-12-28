<?php

session_start();
//$_SESSION['unique_id'] = bin2hex(random_bytes(4));
//$_SESSION['unique_id'] = random_bytes(4);
//var_dump($_SESSION['unique_id']);
//$_SESSION['test'] = 'foo';
//unset($_SESSION['test']);
//$_SESSION['error'] = false;
//var_dump($_SESSION);
//var_dump(session_id());
//If we work with sessions, start here
require 'vendor/autoload.php';

$app_path = __DIR__ . '/app/';

$settings = require $app_path . 'settings.php';
//Check through settings make sure its fine for use

//if (function_exists('xdebug_start_trace'))
//{
//    xdebug_start_trace();
//}

$container = new \Slim\Container($settings);

require $app_path . 'dependencies.php';
//Need to write depending on what wrappers we will use

$app = new \Slim\App($container);

require $app_path .'routes.php';

//var_dump(session_id());
//var_dump($_SESSION);
//var_dump(session_id());

session_regenerate_id(true);
$app->run();



//if (function_exists('xdebug_stop_trace'))
//{
//    xdebug_stop_trace();
//}