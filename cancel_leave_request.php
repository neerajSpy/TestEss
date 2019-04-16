<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 8/11/2018
 * Time: 12:52 PM
 */


include "config/config.php";
date_default_timezone_set('Asia/Kolkata');

$user_id = $_POST['user_id'];
$leave_request_id = $_POST['leave_request_id'];
$applied_by_user_id = $_POST['applied_by_user_id'];
$status_value = $_POST['status_value'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

$response = array();

$status_date = getAppliedDate();
$leave_request_row = getLeaveRequestIdArray($con, $leave_request_id);
$totalLeaveDays = $leave_request_row['total_leaves'];
$leave_type_id = $leave_request_row['leave_type_id'];
$leave_type = getLeaveType($con,$leave_type_id);


$emailResponse = $arrayName = array('user_id' => $user_id,'applied_by_id'=>$applied_by_user_id, 'leave_request_id'=>$leave_request_id, 'total_leaves'=>$totalLeaveDays,'leave_type'=>$leave_type,'leave_type_id'=>$leave_type_id,'start_date'=>$start_date,'end_date'=>$end_date);



$leave_entitlement_id = getLeaveEntitlementId($con, $applied_by_user_id, $leave_type_id);
$update_leave_request = updateUserRequestStatus($con, $leave_request_id, $status_date, $status_value, $user_id);
if ($update_leave_request >0){
    $update_leave = updateUserLeaveStatus($con, $leave_request_id, $status_date, $status_value, $user_id);

    if ($status_value == 2) {
        $update_entitlement = $con->query("UPDATE `leave_entitlement` SET `used_leave`= (`used_leave`- $totalLeaveDays),`balance_leave`= (`balance_leave` + $totalLeaveDays),`modified_date`= '$applied_date',`modified_by_id`='$user_id' WHERE `user_id` = '$applied_by_user_id' AND `leave_type_id` = '$leave_type_id'");
    }

    $insert_entitlement_history = insertEntitlementHistory($con, $leave_request_id, $leave_type_id, $leave_entitlement_id, $applied_by_user_id, $totalLeaveDays, $status_date, $user_id);
}

if ($update_leave_request >0){
    $response['error'] = "false";
    $response['message'] = "cancel";
sendNotification($con,$applied_by_user_id,$leave_type);

}else{
    $response['error'] = "true";
    $response['message'] = "Not updated";
}

echo json_encode($response);

function updateUserRequestStatus($con, $leave_request_id, $status_date, $status_value, $user_id){

    //SELECT `id`,`user_id`, `leave_type_id`, `total_days`, `total_leaves`, `leave_location`, `reason`,
    // `description`, `leave_status_id`, `applied_by_id`, `applied_date`, `status_by_id`, `status_date`,
    // `start_date`, `end_date` FROM `user_leave_request` WHERE 1

    $con->query("UPDATE `user_leave_request` set `leave_status_id` = '$status_value', `status_date` = '$status_date', `status_by_id` = '$user_id' where `id` = '$leave_request_id' AND `leave_status_id` != '$status_value' ");
    return $con->affected_rows;
}


function updateUserLeaveStatus($con, $leave_request_id, $status_date, $status_value, $user_id){
    //SELECT `id`, `leave_request_id`, `user_id`, `leave_date`, `length_hours`, `length_days`, `duration_type`, `leave_status_id`,
    // `comments`, `leave_type_id`, `start_time`, `end_time`, `date_applied`, `applied_by_id`, `status_date`,
    // `status_by_id` FROM `user_leaves` WHERE 1
    $con->query("UPDATE `user_leaves` set `leave_status_id` = '$status_value', `status_date` = '$status_date', `status_by_id` = '$user_id' where `leave_request_id` = '$leave_request_id'");
    return $con->affected_rows;
}

function getLeaveRequestIdArray($con, $leave_request_id)
{
    $row = array();
    $result = $con->query("SELECT * from `user_leave_request` where `id` = '$leave_request_id'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_array();

    }
    return $row;
}


function insertEntitlementHistory($con, $leave_request_id, $leave_type_id, $leave_entitlement_id, $applied_by_user_id, $totalLeaveDays, $status_date, $user_id){
    //SELECT `id`, `leave_request_id`, `payroll_id`, `user_id`, `leave_entitlement_id`, `leave_type_id`, `length_day`,
    // `update_date`, `notes` FROM `leave_entitlement_history` WHERE 1

    $con->query("INSERT into `leave_entitlement_history` (`leave_request_id`,`user_id`,`leave_entitlement_id`,`leave_type_id`,`update_date`,`length_day`, `notes`) VALUES ('$leave_request_id','$applied_by_user_id','$leave_entitlement_id','$leave_type_id','$status_date','$totalLeaveDays','cancel leave')");
    return $con->insert_id;
}

function getAppliedDate(){
    $now = new DateTime();
    return $now->format('Y-m-d H:i:s');
}


function getLeaveEntitlementId($con, $user_id, $leave_type_id){
    $id = 1;
    $leave_type_result = $con->query("SELECT * from `leave_entitlement` WHERE `user_id`= '$user_id' AND `leave_type_id` = '$leave_type_id'");
    if ($row = $leave_type_result->fetch_array()) {
        $id = $row['id'];
    }
    return $id;
}

function getLeaveType($con,$leave_type_id){
    $leave_type = "";
    $result = $con->query("SELECT * from `leave_type` where `id` = '$leave_type_id'");
    if ($result->num_rows >0) {
        if ($row = $result->fetch_assoc()) {
            $leave_type = $row['name'];
        }
    }
    return $leave_type;
}


function getUserName($con,$user_id){
    $userName = "";
    $result = $con->query("SELECT * from `user` where `id` = '$user_id'");
    if ($result->num_rows >0) {
        if($row = $result->fetch_array()){
            $userName = $row['name'];

        }
    }
    return $userName;
}

function sendNotification($con,$user_id,$leave_type){
    $userName = getUserName($con,$user_id);

    $ss= $con->query("SELECT uft.`token` FROM `user_fcm_token` as uft JOIN `user_reporting_to` as urt ON uft.`user_id` = urt.`reporting_to` WHERE urt.`user_id` = '$user_id'");  

    $ch = curl_init("https://fcm.googleapis.com/fcm/send");

    $serverKey = "AIzaSyDJ0MiSBWBsQN5y-ybhWr2GNGFzTPsSfFQ";

    $notificationArr = array();
    array_push($notificationArr,array("user_name"=>$userName,"leave_type"=>$leave_type));

    $notification = array("body" => array("module"=>"cancel leave","json_response"=>$notificationArr));

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
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if($result === false)
        {
            //echo  'Curl failed ' . curl_error($ch);
        }

    }
    curl_close($ch);
}

function getUserName($con,$user_id){
    $userArray = array();
    $result = $con->query("SELECT * from `user` WHERE `id` = '$user_id'");
        if($row = $result->fetch_array()){
            $userArray = $row;
        }
        
    return $userArray;
}


function getLeaveArray($con,$user_id,$leave_type_id){
    $leaveArray = array();
    $result = $con->query("SELECT * from `leave_entitlement` WHERE `user_id`= '$user_id' AND `leave_type_id` = '$leave_type_id'");
        if ($row = $result->fetch_array()) {
            $leaveArray = $row;
        }
return $leaveArray;
}


function sendEmail($con,$emailResopnse){
$user_id = $emailResopnse['user_id'];
$leave_type = $emailResopnse['leave_type'];
$userArray = getUserName($con,$user_id);
$leave_type_id = getLeaveTypeId($con,$leave_type);
$leaveArray = getLeaveArray($con,$user_id,$leave_type_id);

// Multiple recipients
$to = 'ranju.kumari@technitab.in, kavinder.kohli@technitab.in'; // note the comma

// Subject
$subject = 'Leave request : '.$userArray['name'];

// Message
$message = '
<html>

<body>
<p>'.$userArray['name'].' has requested for '.$emailResopnse['leave_type'].' as per information below </p>
Employee Name : '.$userArray['name'].'<br>
Employee id : '.$userArray['role_id'].'<br>
No. off days for which leave applied : '.$emailResopnse['total_days'].'<br>
Start date : '.$emailResopnse['start_date'].'<br>
End date : '.$emailResopnse['end_date'].'<br>
Total leave available with employee till date : '.$leaveArray['entitled_leave'].'<br>
Leave taken till date : '.$leaveArray['used_leave'].'<br>
Leave balance : '.$leaveArray['balance_leave'].'<br>
<p>Description : '.$emailResopnse['description'].' </p>

<p>Please response to the the leave request on Ess app</p>

<p></p>
Regards.<br>
Ess App
</body>
</html>
';

// To send HTML mail, the Content-type header must be set
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-type: text/html; charset=iso-8859-1';

// Additional headers
$headers[] = 'To: '.$To;
$headers[] = 'From: Leave Reminder <no-reply@technitab.com>';
$headers[] = 'Bcc: neeraj.technitab@gmail.com';
/*$headers[] = 'Bcc: birthdaycheck@example.com';*/

// Mail it
mail($to, $subject, $message, implode("\r\n", $headers));

}

?>
