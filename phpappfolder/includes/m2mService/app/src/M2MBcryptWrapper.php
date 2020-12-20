<?php


namespace M2mService;


class M2MBcryptWrapper
{
    public function hashPassword($plain_pass)
    {
        if(!empty($plain_pass)){
            $hashed_pass = password_hash($plain_pass, PASSWORD_DEFAULT);
        } else {
            return false;
        }
        return $hashed_pass;
    }

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
//    public function authenticatePassword($string_to_check, $stored_user_password_hash)
//    {
//        $user_authenticated = false;
//        $current_user_password = $string_to_check;
//        $stored_user_password_hash = $stored_user_password_hash;
//        if (!empty($current_user_password) && !empty($stored_user_password_hash))
//        {
//            if (password_verify($current_user_password, $stored_user_password_hash))
//            {
//                $user_authenticated = true;
//            }
//        }
//        return $user_authenticated;
//    }

}