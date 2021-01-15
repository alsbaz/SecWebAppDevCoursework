<?php


namespace M2mService;


class M2MBcryptWrapper
{
    /**
     * @param string $plain_pass
     * @return false|string|null
     * Hashes the passed password and returns the hash.
     */
    public function hashPassword($plain_pass)
    {
        if(!empty($plain_pass)){
            $hashed_pass = password_hash($plain_pass, PASSWORD_DEFAULT);
        } else {
            return false;
        }
        return $hashed_pass;
    }

    /**
     * @param string $string_to_check
     * @param string $hashed_pass
     * @return bool
     * When the user tries to log in, the password and the hash stored in
     * the database is passed to this method.
     * password_verify does a verification and returns true if it was a match.
     */
    public function authenticateHash($string_to_check, $hashed_pass)
    {
        $authentication = false;
        if(!empty($string_to_check) && !empty($hashed_pass)){
            if(password_verify($string_to_check, $hashed_pass)){
                $authentication = true;
            }
        }
        return $authentication;
    }
}