<?php

/**
 * Handles all the verification and sanitisation of the user inputs.
 */
namespace M2mService;

class M2MInputValidator
{
    public function __construct() {}

    public function __destruct() {}

    /**
     * @param array $tainted_params
     * @return array|false
     * This method is specific for the login inputs.
     * It validates the username against all the requirements and
     * uses regex to match the password. If either fail, false is returned.
     * Does not accept and return filtered values, as that would possibly result in
     * errors and difficulty for the user later on.
     */
    public function cleanParams1(array $tainted_params)
    {
        $cleaned_params = [];

        if (!empty($tainted_params['username']) && strlen($tainted_params['username']) < 21 && strlen($tainted_params['username']) > 5) {
            if (ctype_alnum($tainted_params['username'])) {
                $cleaned_params['username'] = $tainted_params['username'];
            } else {
                return false;
            }
        } else {
            return false;
        }

        /** Regex component breakdown:
         *(?=.*[a-z]) Must contain at least one lowercase character
         *(?=.*[A-Z]) Must contain at least one uppercase character
         *(?=.*\d) Must contain at least 1 digit
         *[A-Za-z\d!@#£$%^&:;<>,.?/~_+=|] Rest of the characters (accepts uppercase, lowercase, digits and some symbols
         *{8,20} Must be between 8 and 20 characters
         */

        if (preg_match("[^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d!@#£$%^&:;<>,.?/~_+=|]{8,20}$]", $tainted_params['password'])) {
            $cleaned_params['password'] = $tainted_params['password'];
        } else {
            return false;
        }
        return $cleaned_params;
    }

    /**
     * @param array $tainted_params
     * @return array|false
     * Extension of the cleanParams1 method. It is exclusively
     * used for the registration inputs.
     * Does not accept and return filtered values, as that would possibly result in
     * errors and difficulty for the user later on.
     */
    public function cleanParams2(array $tainted_params)
    {
        $tainted_user_pass['username'] = $tainted_params['username'];
        $tainted_user_pass['password'] = $tainted_params['password'];
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

        $result = $this->cleanEmail($tainted_params['email']);
        if ($result != false) {
            $cleaned_params['email'] = $result;
        } else {
            return false;
        }

        return $cleaned_params;
    }

    /**
     * @param string $tainted_email
     * @return false|mixed
     * Specific method to check emails.
     * Does not accept and return filtered values, as that would possibly result in
     * errors and difficulty for the user later on.
     */
    public function cleanEmail($tainted_email)
    {
        if (isset($tainted_email)) {
            $result = filter_var(($tainted_email), FILTER_SANITIZE_EMAIL);
            if (!filter_var($result, FILTER_VALIDATE_EMAIL) || $result != ($tainted_email)) return false;
        } else {
            return false;
        }
        return $result;
    }

    /**
     * @param array $params
     * @return array|false
     * All general input is sanitised here. Only pass user inputs here,
     * if it does not bring up any problems if the values are slightly changed.
     */
    public function sanitiseInput($params)
    {
        $sanitised_params = [];
        if (!empty($params))
        {
            foreach ($params as $key => $value) {
                if ($key != 'password') {
                    $sanitised_params[$key] = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
                    $sanitised_params[$key] = preg_replace("(\"|\'|\;)", "", $value);
                    $sanitised_params[$key] = preg_replace('/\s+/', " ", $value);
                } else {
                    $sanitised_params[$key] = $value;
                }
            }
        } else return false;

        return $sanitised_params;
    }
}
