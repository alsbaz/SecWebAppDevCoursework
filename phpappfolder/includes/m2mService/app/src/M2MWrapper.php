<?php

namespace M2mService;

class M2MWrapper implements M2MInterface
{

    //private $soap_call_parameters;
    //private $result;
    //private $resultAttribute;


    public function __construct()
    {

    }

    public function __destruct()
    {

    }

    private function
    recieveData() {
        $soap_function = '';
        $soap_call_parameters = [];

        return $soap_function;


    }

    private function createSoapClient() {
        $soap_client_handle = false;
        $soapclient_attributes = ['trace' => true, 'exceptions' => true];
        $wsdl = WSDL;

        try
        {

                $soap_client_handle = new \SoapClient($wsdl, $soapclient_attributes);
                //var_dump($soap_client_handle->__getFunctions());

        } catch (\SoapFault $exception)
        {
            trigger_error($exception);
        }
        return $soap_client_handle;
    }

    public function setSessionVar($session_key, $session_value_to_set)
    {
        // TODO: Implement setSessionVar() method.
    }

    public function getSessionVar($session_key)
    {
        // TODO: Implement getSessionVar() method.
    }

    public function unsetSessionVar($session_key)
    {
        // TODO: Implement unsetSessionVar() method.
    }

    public function setLogger()
    {
        // TODO: Implement setLogger() method.
    }
}
