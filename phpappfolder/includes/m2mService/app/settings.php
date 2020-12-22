<?php
/**
 * Created by PhpStorm.
 * User: slim
 * Date: 13/10/17
 * Time: 12:33
 */

ini_set('display_errors', 'On');
ini_set('html_errors', 'On');
ini_set('xdebug.trace_output_name', 'session_example.%t');

$app_url = dirname($_SERVER['SCRIPT_NAME']);
//var_dump($_SERVER['SCRIPT_NAME']);
$css_path = $app_url . 'css/standard.css';
//var_dump($css_path);
//var_dump($app_url);
define('CSS_PATH', $css_path);

$wsdl = 'https://m2mconnect.ee.co.uk/orange-soap/services/MessageServiceByCountry?wsdl';
define('WSDL', $wsdl);

$settings = [
    "settings" => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,
        'mode' => 'development',
        'debug' => true,
        'view' => [
            'template_path' => __DIR__ . '/templates/',
            'twig' => [
                'cache' => false,
                'auto_reload' => true,
            ]],
    ],
    'doctrine_db_settings' => [
        'driver' => 'pdo_mysql',
        'host' => 'localhost', // 'mysql.tech.dmu.ac.uk'
        'dbname' => 'm2m_db', // 'p17209674db'
        'port' => '3306',
        'user' => 'm2m_user', // 'p17209674_web'
        'password' => 'm2m_user_pass', // 'lubEd+11'
        'charset' => 'utf8mb4'
    ],
];

return $settings;