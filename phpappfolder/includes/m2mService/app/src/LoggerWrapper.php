<?php

namespace M2mService;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggerWrapper

{
    public function __construct()
    {

    }

    public function __destruct()
    {

    }

    public function logAction($message, $identifier, $log_type, $optional1 = null)
    {
        $current_page = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $log = new Logger($current_page);

        switch ($log_type) {
            case 'ERROR':
                $logs_file_path = '../logs/m2mService_error.log';
                $log->pushHandler(new StreamHandler($logs_file_path, Logger::ERROR));
                $log->$log_type('Identifier: ' . $identifier . ' ' . $optional1 . ': ' . $message);
                break;
            case 'INFO':
                $logs_file_path = '../logs/m2mService_info.log';
                $log->pushHandler(new StreamHandler($logs_file_path, Logger::INFO));
                $log->$log_type('Identifier: ' . $identifier . ' ' . $message);
                break;
            case 'WARNING':
                $logs_file_path = '../logs/m2mService_error.log';
                $log->pushHandler(new StreamHandler($logs_file_path, Logger::WARNING));
                $values = '';
                if ($message != null) {
                    foreach ($message as $key => $item) {
                        $values = $values . ' ' . $key . ' ' . $item . ',';
                    }
                }
                $log->$log_type('Identifier: ' . $identifier . ' ' . $optional1 . ':' . $values);
                break;
            case 'INPUT':
                $logs_file_path = '../logs/m2mService_inputs.log';
                $log->pushHandler(new StreamHandler($logs_file_path, Logger::DEBUG));
                $values = '';
                if ($message != null) {
                    foreach ($message as $key => $item) {
                        $values = $values . ' ' . $key . ' ' . $item . ',';
                    }
                }
                $log->DEBUG('Identifier: ' . $identifier . ' ' . $optional1 . ':' . $values);
                break;

        }
    }
//
////    public function logAction($message, $identifier, $log_type)
////    {
////        $logs_file_path = '../logs/m2mService_info.log';
////        $current_page = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
////
////        $log = new Logger($current_page);
////        $log->pushHandler(new StreamHandler($logs_file_path, Logger::INFO));
////
////        $log->$log_type('Identifier: ' . $identifier . ' ' . $log_type. ': ' . $message);
////    }
//
//    public function logError($message, $identifier, $log_type, $error_type)
//    {
//        $logs_file_path = '../logs/m2mService_error.log';
//        $current_page = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
//
//        $log = new Logger($current_page);
//        $log->pushHandler(new StreamHandler($logs_file_path, Logger::ERROR));
//
//        $log->$log_type('Identifier: ' . $identifier . ' ' . $error_type . ': ' . $message);
//    }
//
//    public function logValues($values_array, $identifier, $log_type, $error_type)
//    {
//        $logs_file_path = '../logs/m2mService_error.log';
//        $current_page = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
//
//        $log = new Logger($current_page);
//        $log->pushHandler(new StreamHandler($logs_file_path, Logger::ERROR));
//
//        $values = '';
//        foreach ($values_array as $key => $item) {
//            $values = $values . ' ' . $key . ' ' . $item . ',';
//        }
//
//        $log->$log_type('Identifier: ' . $identifier . ' ' . $error_type . ':' . $values);
//    }
}