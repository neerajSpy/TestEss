<?php

/**
 * Created by PhpStorm.
 * User: AC
 * Date: 4/15/2019
 * Time: 3:48 PM
 */
class Password
{

    private $date;

    private $con;

    public function __construct()
    {
        include_once 'config/NewConfig.php';
        include_once 'config/constant.php';
        date_default_timezone_set('Asia/Kolkata');
        $db = new NewConfig();
        $this->con = $db->dbConnect();
        $this->date = date("Y-m-d H:i:s");
    }

    function isValidUser($email)
    {
        $result = $this->con->query("SELECT `id` FROM `user` WHERE `email` = $email");
        return $result->num_rows > 0;
    }

    function insertTempKey($email)
    {

        if ($this->isValidUser($email)) {
            $expFormat = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y"));
            $expDate = date("Y-m-d H:i:s", $expFormat);
            $key = md5(2418 * 2 + $email);
            $addKey = substr(md5(uniqid(rand(), 1)), 3, 10);
            $key = $key . $addKey;
            $result = $this->con->query("INSERT into `temp_reset_password` (`email`, `hash_key`, `expiry_date`) VALUES ('$email', '$key', 
            '$expDate')");


            if ($result === TRUE) {
                $this->sendPasswordMail($email, $key);
                return TRUE;
            }

        } else {
            return FALSE;
        }
    }

    
    function sendPasswordMail($email, $key)
    {
        include 'db_class/SendMail.php';
        $mailObj = new SendMail();
        $mailObj->passwordMail($email, $key);
    }
}



