<?php


namespace M2mService;


class M2MBaseFunctions
{
    public function baseFunctions($app)
    {
        if (!isset($_SESSION['unique_id'])) {
            header("Location: /");
            $_SESSION['error'] = 'Please log in before accessing that';
            exit();
        };

        $logger = $app->getContainer()->get('loggerWrapper');
        $error = false;
        if(isset($_SESSION['error'])) {
            $error = $_SESSION['error'];
            $logger->logAction($_SESSION['error'], $_SESSION['unique_id'], 'ERROR', 'Error message: ');
            unset($_SESSION['error']);
        }

        $logger->logAction('Access', $_SESSION['unique_id'], 'INFO');

        return $error;
    }
}