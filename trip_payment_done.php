<?php
include "config/config.php";
date_default_timezone_set('Asia/Kolkata');


$payment_json = json_decode($_POST['payment_json']);
$booking_id = $payment_json->booking_id;
$payment_mode = $payment_json->payment_mode;
$paid_by = $payment_json->paid_by;
$bill_date = $payment_json->bill_date;
$payment_term = $payment_json->payment_term;
$payment_term_label = $payment_json->payment_term_label;
$payment_date = $payment_json->payment_date;
$paid_amount = $payment_json->paid_amount;
$reference_number = $payment_json->reference_number;
$notes = $payment_json->notes;
$created_by_id = $payment_json->created_by_id;
$now = new DateTime();
$current_date =  $now->format('Y-m-d H:i:s');


// SELECT `id`, `booking_id`, `payment_mode`, `paid_by`, `payment_date`, `paid_amount`, `payment_term`, `payment_term_label`, `reference_number`, `notes`, `created_by_id`, `created_date`, `status`, `is_active` FROM `booking_payment` WHERE 1

$result = $con->query("INSERT into `booking_payment` (`booking_id`,`payment_mode`,`paid_by`,`payment_date`,`bill_date`,`paid_amount`,`reference_number`,`notes`,`created_by_id`,`created_date`,`status`) VALUES ('$booking_id','$payment_mode','$paid_by','$payment_date','$bill_date','$paid_amount','$reference_number','$notes','$created_by_id','$current_date','Done')");

$response = array();
if ($result === TRUE) {
    $response['error'] = false;
    $response['message'] = "Payment successfully saved";
    updateTripBooking($con,$booking_id);
    sendNotification($con,$booking_id,$created_by_id);
}else{
$response['error'] = true;
    $response['message'] = "Payment not saved".$con->error;
}

echo json_encode($response);


function updateTripBooking($con,$booking_id){
    $result = $con->query("UPDATE `emp_booking` set `trip_status` = 'Payment Done' WHERE `id` = '$booking_id'");
}

function getUserName($con,$user_id){
    $name = "";
    $result = $con->query("SELECT * from `user` WHERE `id` = '$user_id'");
    if ($result->num_rows >0) {
        $row = $result->fetch_assoc();
        $name = $row['name'];
    }
    return $name;
}

function sendNotification($con,$booking_id,$user_id){
    $userName = getUserName($con,$user_id);
    $token_query = "SELECT uft.`token`  from `emp_booking_member` as ebm JOIN `user_fcm_token` as uft on ebm.`user_id` = uft.`user_id` WHERE ebm.`booking_id` = '$booking_id' AND ebm.`is_active` = '0'";
    $ss= $con->query($token_query);

    $ch = curl_init("https://fcm.googleapis.com/fcm/send");
    $serverKey = "AIzaSyDJ0MiSBWBsQN5y-ybhWr2GNGFzTPsSfFQ";

    $notificationArr = array();
    array_push($notificationArr,array("booking_id"=>$booking_id,"user_name"=>$userName));

    $notification = array("body" => array("module"=>"Payment done","json_response"=>$notificationArr));

    while($r= ($ss->fetch_array())) {

        $f = $r['token'];
        $arrayToSend = array('to' => $f, 'data' => $notification);

        $json = json_encode($arrayToSend);
      // echo $json;
        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: key= $serverKey";

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if($result === false)
        {
            //echo  'Curl failed ' . curl_error($ch);
        }

    }
    curl_close($ch);
}




?>