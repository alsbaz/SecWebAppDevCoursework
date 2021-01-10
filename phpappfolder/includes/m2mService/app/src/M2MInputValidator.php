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
     */
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
         *(?=.*[a-z]) Must contain at least one lowercase character
         *(?=.*[A-Z]) Must contain at least one uppercase character
         *(?=.*\d) Must contain at least 1 digit
         *[A-Za-z\d!@#£$%^&:;<>,.?/~_+=|] Rest of the characters (accepts uppercase, lowercase, digits and some symbols
         *{8,20} Must be between 8 and 20 characters
         */

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

    /**
     * @param array $tainted_params
     * @return array|false
     * Extention of the cleanParams1 method. It is exclusively
     * used for the registration inputs.
     */
    public function cleanParams2(array $tainted_params)
    {
        $tainted_user_pass['username'] = $tainted_params['username'];
        $tainted_user_pass['password'] = $tainted_params['password'];
        // pass the username and password to the above method for checking and cleaning
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
     */
    public function cleanEmail($tainted_email)
    {
        if (isset($tainted_email)) {
            // filters email to validate if input is a valid email address
            $result = filter_var(($tainted_email), FILTER_SANITIZE_EMAIL);
            // If the email doesn't match the filter OR the clean email doesn't match the tainted email
            if (!filter_var($result, FILTER_VALIDATE_EMAIL) || $result != ($tainted_email)) return false;
        } else {
            return false;
        }
        return $result;
    }

    /**
     * @param array $params
     * @return array|false
     * All general input is sanitised here.
     */
    public function sanitiseInput($params)
    {
        $sanitised_params = [];
        if (!empty($params))
        {
            foreach ($params as $key => $value) {
                // password is not used anywhere except for hashing, so it is not touched
                if ($key != 'password') {
                    // removes all the XML flags
                    $sanitised_params[$key] = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
                    // using regex for removing code breaking characters such as " or ;
                    $sanitised_params[$key] = preg_replace("(\"|\'|\;)", "", $value);
                    // replacing newline characters with space
                    $sanitised_params[$key] = preg_replace('/\s+/', " ", $value);
                } else {
                    $sanitised_params[$key] = $value;
                }
            }
        } else return false;

        return $sanitised_params;
    }
}
