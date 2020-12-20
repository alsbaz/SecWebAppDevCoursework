<?php

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