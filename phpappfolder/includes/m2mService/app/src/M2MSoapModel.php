<?php

/**
 * Handles all the soap call methods.
 */

namespace M2mService;

use PHPUnit\Util\Exception;

class M2MSoapModel
{
    // set all these private values for handling later on
    // can't be accessed outside of this class
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
     */
    public function performSoapCall()
    {
        $result = null;
        $soap_client_handle = null;
        // creating the client handle
        $soap_client_handle = $this->createSoapClient();

        // select call method and set variables
        $soap_function = $this->selectSoapCall();

        // use try catch block to stop fatal error
        try {
            // only does the soap call if user is logged in
            if (isset($_SESSION['username'])) {
                // and the handle is set
                if ($soap_client_handle !== false) {
                    // use the __soapCall method from the handle instance with the right variables
                    $call_result = $soap_client_handle->__soapCall($soap_function, $this->params);
                }
                // sets the result as a value of the class instance
                $this->result = $call_result;
            }
        // if Soap exception is thrown, catch it
        } catch(\SoapFault $e) {
            // depending on the exception, set the error message to the specified message
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
        }
    }

    /**
     * @return string
     * @throws \Exception
     * Sets the method and variables depending on the method passed earlier.
     */
    private function selectSoapCall()
    {
        $soap_call_params = [];

        // switch the method_to_use and sets the values depending on the method
        switch($this->method_to_use) {
            case 'sendMessage':
                $soap_function = 'sendMessage';
                $soap_call_params = [
                    'username' => $this->username,
                    'password' => $this->password,
                    'deviceMSISDN' => $this->device_MSISDN,
                    // message contains some crafted XML tags along the message
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
     */
    private function createSoapClient()
    {
        // sets the soap options, including the address for wsdl
        $soap_client_handle = false;
        $soapclient_attributes = ['trace' => true, 'exceptions' => true];
        $wsdl = WSDL;

        // try catch block to handle connecting to soap client
        try {
            $soap_client_handle = new \SoapClient($wsdl, $soapclient_attributes);
        } catch (\SoapFault $e) {
            $_SESSION['error'] = 'Can\'t connect to the Soap client'  . $e->getMessage();
        }
        return $soap_client_handle;
    }
}
