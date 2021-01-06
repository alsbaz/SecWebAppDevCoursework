<?php

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
    private $delivery_report;
    private $mt_bearer;
    private $count;
    private $country_code;
    private $timeout;
    private $msgref;

    public function __construct()
    {

    }

    public function __destruct()
    {

    }

    public function __set($property, $value)
    {
        $this->$property = $value;
    }

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
//            unset($_SESSION['message']);
//            header("Location: " . $_SERVER['HTTP_REFERER']);
//            exit();
//var_dump($e->getMessage());
//var_dump($e->getCode());
        }

    }


    private function selectSoapCall()
    {
        $soap_function = '';
        $soap_call_params = [];

        switch($this->method_to_use) {
            case 'sendMessage':
                $soap_function = 'sendMessage';
                $soap_call_params = [
                    'username' => $this->username, //'20_17209674'
                    'password' => $this->password, //'CGs74bktVKzAHxC',
                    'deviceMSISDN' => $this->device_MSISDN, //'+447817814149',
//                    'message' => '&lt;unique_id&gt;skateFastEatAss&lt;/unique_id&gt;' . 'Hello World',//$this->message, //&lt;msg&gt;Bob &amp; Jane&lt;/msg&gt; --> <msg>Bob & Jane</msg>
                    'message' => '<id>20-3110-AD</id><username>' . $_SESSION['username'] . '</username><message_content>' . $this->message . '</message_content>',
                    'deliveryReport' => false, //$this->delivery_report,
                    'mtBearer' => "SMS",// $this->mt_bearer
                ];
                break;
            case 'sendMessageAuto':
                $soap_function = 'sendMessage';
                $soap_call_params = [
                    'username' => '20_17209674',
                    'password' => 'CGs74bktVKzAHxC',
                    'deviceMSISDN' => '447817814149',
//                    'message' => '&lt;unique_id&gt;skateFastEatAss&lt;/unique_id&gt;' . 'Hello World',//$this->message, //&lt;msg&gt;Bob &amp; Jane&lt;/msg&gt; --> <msg>Bob & Jane</msg>
                    'message' => '<id>20-3110-AD</id><username>' . $_SESSION['username'] . '</username><message_content>' . bin2hex(random_bytes(5)) . '</message_content>',
                    'deliveryReport' => false, //$this->delivery_report,
                    'mtBearer' => "SMS",// $this->mt_bearer
                ];
                break;
            case 'peekMessages':
                $soap_function = 'peekMessages';
                $soap_call_params = [
                    'username' => $this->username, //'20_17209674',
                    'password' => $this->password, //'CGs74bktVKzAHxC',
                    'count' => $this->count, //5,
                    'deviceMSISDN' => $this->device_MSISDN, //null, //'+447817814149',  //THIS MIGHT BE A TYPO CHECK var_dump($soap_client_handle->__getFunctions())
                    'countryCode' => null, //$this->country_code
                ];
                break;
            case 'readMessages': //Dont use
                $soap_function = 'readMessages';
                $soap_call_params = [
                    'username' => $this->username, //'20_17209674',
                    'password' => $this->password, //'CGs74bktVKzAHxC',
                    'count' => $this->count, //5,
                    'deviceMSISDN' => $this->device_MSISDN, //'+447817814149', //null,
                    'countryCode' => null, //$this->country_code
                ];
                break;
//            case 'waitForMessage':
//                $soap_function = 'waitForMessage';
//                $soap_call_params = [
//                    'username' => $this->username,
//                    'password' => $this->password,
//                    'timeout' => $this->timeout,
//                    'deviceMSISDN' => $this->device_MSISDN,
//                    'msgref' => $this->msgref,
//                    'countryCode' => $this->country_code
//                ];
//                break;
//            case 'sendAndWait':
//                $soap_function = 'sendAndWait';
//                $soap_call_params = [
//                    'username' => $this->username,
//                    'password' => $this->password,
//                    'timeout' => $this->timeout,
//                    'deviceMSISDN' => $this->device_MSISDN,
//                    'message' => $this->message,
//                    'deliveryReport' => $this->delivery_report,
//                    'mtBearer' => $this->mt_bearer
//                ];
//                break;
//            case 'flushMessages': //Most likely these wont be needed by us
//                                  //But they are still possible calls
//                break;
//            case 'getDeliveryReports':
//
//                break;
//            case 'sendMessageWithValidityPeriod':
//
//                break;
//            case 'sendBinarySmsMessage':
//
//                break;
//            case 'sendBinarySmsAndWait':
//
//                break;
//            case 'getDeliveryReportsFromDate':
//
//                break;
            default:
                $soap_function = null;
        }
        $this->params = $soap_call_params;

        return $soap_function;
    }


    private function createSoapClient()
    {
        $soap_client_handle = false;
        $soapclient_attributes = ['trace' => true, 'exceptions' => true];
        $wsdl = WSDL;

        try
        {

            $soap_client_handle = new \SoapClient($wsdl, $soapclient_attributes);
//var_dump($soap_client_handle->__getFunctions());
//var_dump($soap_client_handle->__getTypes());

        } catch (\SoapFault $e)
        {
var_dump($e->getMessage());
//            trigger_error($exception);
        }
        return $soap_client_handle;
    }

//    private function methodNameHere($soap_client_handle, $soap_function)
//    {
//        $result = null;
//
//        try {
//            $call_result = $soap_client_handle->__soapCall($soap_function);
//
//
//        } catch (\SoapFault $e) {
//var_dump($e->getMessage());
//        }
//
//        return $result;
//    }
}
