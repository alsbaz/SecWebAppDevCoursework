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

    public function logAction($message, $identifier, $logType)
    {
        $logs_file_path = '../logs/m2mService_log.log';
        $current_page = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $log = new Logger($current_page);
        $log->pushHandler(new StreamHandler($logs_file_path, Logger::INFO));

            $log->$logType('Identifier: ' . $identifier . ' ' . $logType. ': ' . $message);

    }



}