<?php

session_start();

//$_SESSION['test'] = 'foo';
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
//var_dump($app_path);

session_regenerate_id();
//var_dump(session_id());

$app->run();



//if (function_exists('xdebug_stop_trace'))
//{
//    xdebug_stop_trace();
//}