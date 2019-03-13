<?php

class SendNotification
{

    private $con;
    private $date;
    private $isActive;
    private $deactive;

    public function __construct()
    {
        include_once 'config/NewConfig.php';
        include_once 'config/constant.php';

        $this->isActive = IS_ACTIVE;
        $this->deactive = DEACTIVE;

        $db = new NewConfig();
        $this->con = $db->dbConnect();
        date_default_timezone_set('Asia/Kolkata');
        $this->date = date("Y-m-d H:i:s");
    }
    
    function insertNewToken($userId,$token){
        $this->con->query("INSERT into `user_fcm_token` (`user_id`,`token`,`created_date`) VALUES ('$userId','$token',
        '$this->date')");
    }

    function sendNotificationToUser($userId, $sendById, $title, $notificationArray)
    {
        include_once 'db_class/User.php';
        $userDb = new User();

        $sendBy = $userDb->getNameByUserId($sendById);
        $userArr = array(
            'user_name' => $sendBy
        );
        $arr = array_merge($notificationArray, $userArr);
        $tokenResult = $this->con->query("SELECT `token` from `user_fcm_token` where `user_id` = '$userId'");
        // echo "result ".$tokenResult->num_rows." error".$this->con->error;
        if ($tokenResult->num_rows > 0) {
            while ($row = $tokenResult->fetch_assoc()) {
                $token = $row['token'];
                $this->notification($token, $title, $arr);
            }
        }
    }
    
    function sendNotificationForBooking($booking_id,$userId,$title,$notificationArr){
        include_once 'db_class/User.php';
        $userDb = new User();
        
        $sendBy = $userDb->getNameByUserId($userId);
        $userArr = array(
            'user_name' => $sendBy
        );
        
        $arr = array_merge($notificationArr, $userArr);
        
        $tokenResult = $this->con->query("SELECT uft.`token`  from `emp_booking_member` as ebm JOIN `user_fcm_token` as uft on ebm.`user_id` = uft.`user_id` WHERE ebm.`booking_id` = '$booking_id' AND ebm.`is_active` = '0'");
        // echo "result ".$tokenResult->num_rows." error".$this->con->error;
        if ($tokenResult->num_rows > 0) {
            while ($row = $tokenResult->fetch_assoc()) {
                
                $token = $row['token'];
                // echo "token ".$token;
                $this->notification($token, $title, $arr);
            }
        }
    }

    function sendNotificationToTeamLead($sendById, $title, $notificationArr){
        include_once 'db_class/User.php';
        $userDb = new User();
        
        $sendBy = $userDb->getNameByUserId($sendById);
        $userArr = array(
            'user_name' => $sendBy
        );
        
        $arr = array_merge($notificationArr, $userArr);
        
        $tokenResult = $this->con->query("SELECT uft.`token` FROM `user_fcm_token` as uft JOIN `user_reporting_to` 
        as urt ON uft.`user_id` = urt.`reporting_to` WHERE urt.`user_id` = '$sendById'");
        if ($tokenResult->num_rows > 0) {
            while ($row = $tokenResult->fetch_assoc()) {
                $token = $row['token'];
                $this->notification($token, $title, $arr);
            }
        }
        
        
    }
    
    function sendNotificationToAdmin($sendById, $title, $notificationArr)
    {
        include_once 'db_class/User.php';
        $userDb = new User();

        $sendBy = $userDb->getNameByUserId($sendById);
        $userArr = array(
            'user_name' => $sendBy
        );

        $arr = array_merge($notificationArr, $userArr);

        $tokenResult = $this->con->query("SELECT ufc.`token` from `user` as u join `user_fcm_token` as ufc on u.`id` = ufc.`user_id` where u.`access_control_id` = '2'");
        // echo "result ".$tokenResult->num_rows." error".$this->con->error;
        if ($tokenResult->num_rows > 0) {
            while ($row = $tokenResult->fetch_assoc()) {

                $token = $row['token'];
                // echo "token ".$token;
                $this->notification($token, $title, $arr);
            }
        }
    }

    private function notification($to, $title, $notificationArr)
    {
        $ch = curl_init("https://fcm.googleapis.com/fcm/send");
        $serverKey = "AIzaSyDJ0MiSBWBsQN5y-ybhWr2GNGFzTPsSfFQ";

        $notification = array(
            "body" => array(
                "module" => $title,
                "json_response" => $notificationArr
            )
        );

        $arrayToSend = array(
            'to' => $to,
            'data' => $notification
        );

        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: key= $serverKey";

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if ($result === false) {
             //echo 'Curl failed ' . curl_error($ch)."\n";
        }else{
           // echo "error\n";
            $this->deleteNotificationToken($to, $result);
           // echo "\n";
            
        }
        curl_close($ch);
    }
    
    private function deleteNotificationToken($token,$responseResult){
        $resultJson = json_decode($responseResult);
        $resultArray = $resultJson->results;
        foreach ($resultArray as $arr){
            if(isset($arr->error) && $arr->error == 'NotRegistered'){
                $this->con->query("DELETE from `user_fcm_token` where `token` = '$token'");
            }
        }
    }
}

