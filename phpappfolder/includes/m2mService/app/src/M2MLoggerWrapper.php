<?php

namespace M2mService;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
class M2MLoggerWrapper

{
    public function __construct()
    {

    }

    public function __destruct()
    {

    }

    /**
     * @param $message the message explaining whats being logged
     * @param $identifier used to identify the user
     * @param $log_type the type of log, Info, warning or error
     * @param null $optional1 
     * The function to log whatever is happening on the site.
     * Writes the log to one of multiple log files depending on the log type.
     */
    public function logAction($message, $identifier, $log_type, $optional1 = null)
    {
        if (isset($message['password'])) {
            unset($message['password']);
        } elseif (isset($message['password2'])) {
            unset($message['password2']);
        }
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
}