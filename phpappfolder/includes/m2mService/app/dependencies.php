<?php

/**
 * Dependencies is called by each file in case they need an instance of an object.
 * This file prepares them for use, and establishes dependencies.
 */

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(
        $container['settings']['view']['template_path'],
        $container['settings']['view']['twig'],
        [
            'debug' => true // This line should enable debug mode
        ]
    );

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

$container['m2mInputValidator'] = function () {
    $validator = new \M2mService\M2MInputValidator();
    return $validator;
};

$container['m2mDoctrineSqlQueries'] = function () {
    $wrapper = new \M2mService\M2MDoctrineSqlQueries();
    return $wrapper;
};

$container['m2mBcryptWrapper'] = function () {
  $wrapper = new \M2mService\M2MBcryptWrapper();
  return $wrapper;
};

$container['m2mSoapModel'] = function () {
    $model = new \M2mService\M2MSoapModel();
    return $model;
};

$container['m2mMessageHandler'] = function () {
    $handler = new \M2mService\M2MMessageHandler();
    return $handler;
};

$container['loggerWrapper'] = function () {
    $logger = new \M2mService\LoggerWrapper();
    return $logger;
};

$container['m2mBaseFunctions'] = function () {
    $handler = new \M2mService\M2MBaseFunctions();
    return $handler;
};