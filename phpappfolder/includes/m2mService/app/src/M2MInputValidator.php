<?php

namespace M2mService;

class M2MInputValidator
{
    private $cleaned_params = [];
    public function __construct()
    {

    }

    public function __destruct()
    {

    }

    public function cleanParams1(array $tainted_params)
    {
        $cleaned_params = [];
//var_dump(preg_match("[^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$]",
//        $tainted_params['password']) && strlen($tainted_params['password']) < 21);
//var_dump(!empty($tainted_params['username']) && strlen($tainted_params['username']) < 21);

        if (!empty($tainted_params['username']) && strlen($tainted_params['username']) < 21) {
            if (ctype_alnum($tainted_params['username'])) {
                $cleaned_params['username'] = $tainted_params['username'];
            } else {
                return false;
            }
        } else {
            return false;
        }

        if (preg_match("[^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$]",
                $tainted_params['password']) && strlen($tainted_params['password']) < 21) {
            $cleaned_params['password'] = $tainted_params['password'];
        } else {
            return false;
        }
        return $cleaned_params;
    }

    public function cleanParams2(array $tainted_params)
    {
        $tainted_user_pass['username'] = $tainted_params['username'];
        $tainted_user_pass['password'] = $tainted_params['password'];
        $cleaned_params = [];

        $cleaned_params = $this->cleanParams1($tainted_user_pass);
        if ($cleaned_params == false) {
            return false;
        }

        if (isset($tainted_params['password2'])) {
            if ($cleaned_params['password'] == $tainted_params['password2']) {
                    $cleaned_params['password2'] = $tainted_params['password2'];
                } else {
                    return false;
            }
        } else {
            return false;
        }

        if (isset($tainted_params['email'])) {
            $cleaned_params['email'] = filter_var($tainted_params['email'], FILTER_SANITIZE_EMAIL);
            if (!filter_var($cleaned_params['email'], FILTER_VALIDATE_EMAIL) ||
                $cleaned_params['email'] != $tainted_params['email']) {
                return false;
            }
        } else {
            return false;
        }
        return $cleaned_params;
    }
}