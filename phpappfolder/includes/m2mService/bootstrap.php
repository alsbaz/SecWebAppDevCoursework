<?php

session_start();
session_regenerate_id(true);

require 'vendor/autoload.php';

$app_path = __DIR__ . '/app/';

$settings = require $app_path . 'settings.php';

//if (function_exists('xdebug_start_trace'))
//{
//    xdebug_start_trace('/p3t/phpappfolder/xdebug/trace/');
//}

$container = new \Slim\Container($settings);

require $app_path . 'dependencies.php';

$app = new \Slim\App($container);

require $app_path .'routes.php';

$app->run();



//if (function_exists('xdebug_stop_trace'))
//{
//    xdebug_stop_trace();
//}