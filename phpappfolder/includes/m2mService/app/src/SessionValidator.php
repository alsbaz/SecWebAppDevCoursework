<?php

namespace M2mService;

class SessionValidator
{
    private $cleaned_params = [];
    public function __construct()
    {

    }

    public function __destruct()
    {

    }

    public function cleanParams(array $tainted_params)
    {
        $tainted_username = $tainted_params['username'];

        $cleaned_params['username'] = $this->sanitiseString($tainted_username);
        var_dump($cleaned_params['username']);
        return true;
    }

    public function sanitiseString($string_to_sanitise)
    {
        $sanitised_string = false;

        if (!empty($string_to_sanitise))
        {
            $sanitised_string = filter_var($string_to_sanitise, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        }
        return $sanitised_string;
    }
}