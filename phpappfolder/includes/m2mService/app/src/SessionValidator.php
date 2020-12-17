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
        $cleaned_params = [];
        $error = false;

        if (!empty($tainted_params['username'])){
            if (ctype_alnum($tainted_params['username'])){
                $cleaned_params['username'] = $tainted_params['username'];
            } else {
                $error = true;
            }
        } else {
            $error = true;
        }

        if (preg_match("[^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$]",
                $tainted_params['password']) && strlen($tainted_params['password']<21)){
            $cleaned_params['password'] = $tainted_params['password'];
        } else {
            $error = true;
        }

        if (isset($tainted_params['password2'])) {
            if (preg_match("[^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$]",
                    $tainted_params['password2']) && strlen($tainted_params['password2'] < 21)) {
                $cleaned_params['password2'] = $tainted_params['password2'];
            }
            if (isset($cleaned_params['password2'])) {
                if ($cleaned_params['password'] != $cleaned_params['password2']) {
                    $error = true;
                }
            }
            else {
                $error = true;
            }
        }

        if ($error != false){
            return false;
        }
//var_dump($error);
        return $cleaned_params;

//        if (!isset($cleaned_params['password'])){
//            $error = true;
//        }

//        $tainted_username = $tainted_params['username'];
//        $cleaned_params['username'] = $this->sanitiseString($tainted_username);

//        if (strcmp($tainted_params['username'], $cleaned_params['username']) != 0){
//            $error = true;
//        }
    }

//    public function sanitiseString($string_to_sanitise)
//    {
//        $sanitised_string = false;
//
//        if (!empty($string_to_sanitise))
//        {
//            if (ctype_alnum($string_to_sanitise)) {
//                $sanitised_string = $string_to_sanitise;
//            }
////            $sanitised_string = filter_var($string_to_sanitise, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
//        }
//        return $sanitised_string;
//    }
}