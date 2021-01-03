<?php

namespace M2mService;

class M2MInputValidator
{
    public function __construct() {}

    public function __destruct() {}

    public function cleanParams1(array $tainted_params)
    {
        $cleaned_params = [];

        // if the username is not empty and is less than 21 characters and is greater than 5 characters
        if (!empty($tainted_params['username']) && strlen($tainted_params['username']) < 21 && strlen($tainted_params['username']) > 5) {
            // if the username is alphanumerical
            if (ctype_alnum($tainted_params['username'])) {
                // accept the username
                $cleaned_params['username'] = $tainted_params['username'];
            } else {
                // decline the username
                return false;
            }
        } else {
            // decline the username
            return false;
        }

        /** Regex component breakdown:
        (?=.*[a-z]) Must contain at least one lowercase character
        (?=.*[A-Z]) Must contain at least one uppercase character
        (?=.*\d) Must contain at least 1 digit
        [A-Za-z\d!@#£$%^&:;<>,.?/~_+=|] Rest of the characters (accepts uppercase, lowercase, digits and some symbols
        {8,20} Must be between 8 and 20 characters
        **/

        // old regex: ^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$
        // new regex: ^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d!@#£$%^&:;<>,.?/~_+=|]{8,20}$

        // if unsanitised password meets regex criteria
        if (preg_match("[^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d!@#£$%^&:;<>,.?/~_+=|]{8,20}$]", $tainted_params['password'])) {
            // set cleaned password as tested and passed password
            $cleaned_params['password'] = $tainted_params['password'];
        } else {
            // decline the password
            return false;
        }
        // accept the password
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
            // test if password matches the verify password entered
            if ($cleaned_params['password'] == $tainted_params['password2']) {
                    // assign password 2 as clean password 1 already checked to be clean
                    // so if it matches it must also be clean
                    $cleaned_params['password2'] = $tainted_params['password2'];
            } else {
                return false;
            }
        } else {
            return false;
        }

        if (isset($tainted_params['email'])) {
            // filters email to validate if input is a valid email address
            $cleaned_params['email'] = filter_var($tainted_params['email'], FILTER_SANITIZE_EMAIL);
            // If the email doesn't match the filter OR the clean email doesn't match the tainted email
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
