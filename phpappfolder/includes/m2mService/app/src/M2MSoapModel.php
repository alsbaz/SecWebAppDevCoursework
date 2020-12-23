<?php

namespace M2mService;

class M2MSoapModel
{
    private $method_to_use;
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
        var_dump($this->createSoapClient());


        try {
            if ($soap_client_handle !== false) {
                $this->selectExecuteSoapCall($soap_client_handle);
//var_dump($soap_function);
            }

            $this->result = $result;
        } catch(\SoapFault $e) {
//            trigger_error($e);
var_dump($e->getMessage());
var_dump($e->getCode());
        }

        }


    private function selectExecuteSoapCall($soap_client_handle)
    {
        $soap_function = '';
        $soap_call_params = [];

        switch($this->method_to_use) {
            case 'sendMessage':
                $soap_function = 'sendMessage';
                $soap_call_params = [
                    'username' => $this->username,
                    'password' => $this->password,
                    'deviceMSISDN' => $this->device_MSISDN,
                    'message' => $this->message,
                    'deliveryReport' => $this->delivery_report,
                    'mtBearer' => $this->mt_bearer
                ];

                $call_result = $soap_client_handle->__soapCall($soap_function, $soap_call_params);


                break;
            case 'readMessages': //Dont use
                $soap_function = 'readMessages';
                $soap_call_params = [
                    'username' => $this->username,
                    'password' => $this->password,
                    'count' => $this->count,
                    'deviceMSISDN' => $this->device_MSISDN,
                    'countryCode' => $this->country_code
                ];
                break;
            case 'waitForMessage':
                $soap_function = 'waitForMessage';
                $soap_call_params = [
                    'username' => $this->username,
                    'password' => $this->password,
                    'timeout' => $this->timeout,
                    'deviceMSISDN' => $this->device_MSISDN,
                    'msgref' => $this->msgref,
                    'countryCode' => $this->country_code
                ];
                break;
            case 'sendAndWait':
                $soap_function = 'sendAndWait';
                $soap_call_params = [
                    'username' => $this->username,
                    'password' => $this->password,
                    'timeout' => $this->timeout,
                    'deviceMSISDN' => $this->device_MSISDN,
                    'message' => $this->message,
                    'deliveryReport' => $this->delivery_report,
                    'mtBearer' => $this->mt_bearer
                ];
                break;
            case 'peekMessages':
                $soap_function = 'peekMessages';
                $soap_call_params = [
                    'username' => $this->username,
                    'password' => $this->password,
                    'count' => $this->count,
                    'deviceMSISDN' => $this->device_MSISDN, //THIS MIGHT BE A TYPO CHECK var_dump($soap_client_handle->__getFunctions())
                    'countryCode' => $this->country_code
                ];
                break;
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


//        return $soap_function;
    }


    private function createSoapClient()
    {
        $soap_client_handle = false;
        $soapclient_attributes = ['trace' => true, 'exceptions' => true];
        $wsdl = WSDL;

        try
        {

            $soap_client_handle = new \SoapClient($wsdl, $soapclient_attributes);
            var_dump($soap_client_handle->__getFunctions());
//            var_dump($soap_client_handle->__getTypes());

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
