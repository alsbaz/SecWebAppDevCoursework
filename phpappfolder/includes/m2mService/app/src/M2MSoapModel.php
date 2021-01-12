<?php

/**
 * Handles all the soap call methods.
 */

namespace M2mService;

use PHPUnit\Util\Exception;

class M2MSoapModel
{
    private $method_to_use;
    private $params;
    private $username;
    private $password;
    private $device_MSISDN;
    private $message;
    private $mt_bearer;
    private $count;

    public function __construct() {}

    public function __destruct() {}

    /**
     * @param string $property
     * @param string $value
     * Uses the set magic function to assign values.
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * Method performing the soap call, handling all sub-methods.
     * The soap call is surrounded by try catch block with valid
     * error handling in case something goes wrong.
     */
    public function performSoapCall()
    {
        $result = null;
        $soap_client_handle = null;
        $soap_client_handle = $this->createSoapClient();

        $soap_function = $this->selectSoapCall();

        try {
            if (isset($_SESSION['username'])) {
                if ($soap_client_handle !== false) {
                    $call_result = $soap_client_handle->__soapCall($soap_function, $this->params);
                }
                $this->result = $call_result;
            }
        } catch(\SoapFault $e) {
            switch ($e->getMessage()) {
                case 'java.lang.NullPointerException':
                    $_SESSION['error'] = 'Please enter a MSISDN number. ' . $e->getMessage();
                    break;
                case 'com.orange.telematics.otel.soap.MessageServiceException: login failed':
                    $_SESSION['error'] = 'Please try again with valid login credentials. ' . $e->getMessage();
                    break;
                default:
                    $_SESSION['error'] = 'Something went wrong, please try again. ' . $e->getMessage();
                    break;
            }
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
    }

    /**
     * @return string
     * @throws \Exception
     * Sets the method and variables depending on the method passed earlier.
     * Private values are set by the magic function before calling this class.
     * I use values save in the Session global variable to identify the user.
     */
    private function selectSoapCall()
    {
        $soap_call_params = [];

        switch($this->method_to_use) {
            case 'sendMessage':
                $soap_function = 'sendMessage';
                $soap_call_params = [
                    'username' => $this->username,
                    'password' => $this->password,
                    'deviceMSISDN' => $this->device_MSISDN,
                    'message' => '<id>20-3110-AD</id><username>' . $_SESSION['username'] . '</username><message_content>' . $this->message . '</message_content>',
                    'deliveryReport' => false,
                ];
                if(isset($this->mt_bearer)) {
                    $soap_call_params += ['mtBearer' => $this->mt_bearer];
                } else {
                    $soap_call_params += ['mtBearer' => 'SMS'];
                }
                break;
            case 'sendMessageAuto':
                $soap_function = 'sendMessage';
                $soap_call_params = [
                    'username' => '20_17209674',
                    'password' => 'CGs74bktVKzAHxC',
                    'deviceMSISDN' => '447817814149',
                    'message' => '<id>20-3110-AD</id><username>' . $_SESSION['username'] . '</username><message_content>' . bin2hex(random_bytes(5)) . '</message_content>',
                    'deliveryReport' => false,
                    'mtBearer' => "SMS",
                ];
                break;
            case 'peekMessages':
                $soap_function = 'peekMessages';
                $soap_call_params = [
                    'username' => $this->username,
                    'password' => $this->password,
                    'count' => $this->count,
                    'deviceMSISDN' => $this->device_MSISDN,
                    'countryCode' => null,
                ];
                break;
            case 'readMessages':
                $soap_function = 'readMessages';
                $soap_call_params = [
                    'username' => $this->username,
                    'password' => $this->password,
                    'count' => $this->count,
                    'deviceMSISDN' => $this->device_MSISDN,
                    'countryCode' => null,
                ];
                break;
            default:
                $soap_function = null;
        }
        $this->params = $soap_call_params;

        return $soap_function;
    }

    /**
     * @return false|\SoapClient
     * This method handles the creation of the soap client.
     * Creation of the client is captured by a try catch block with error handling.
     */
    private function createSoapClient()
    {
        $soap_client_handle = false;
        $soapclient_attributes = ['trace' => true, 'exceptions' => true];
        $wsdl = WSDL;

        try {
            $soap_client_handle = new \SoapClient($wsdl, $soapclient_attributes);
        } catch (\SoapFault $e) {
            $_SESSION['error'] = 'Can\'t connect to the Soap client'  . $e->getMessage();
        }
        return $soap_client_handle;
    }
}
