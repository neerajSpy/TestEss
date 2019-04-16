<?php
include "config/config.php";
date_default_timezone_set('Asia/Kolkata');

$leave_type = $_POST['leave_type'];
$user_id = $_POST['user_id'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$leave_duration = $_POST['duration'];
$half_days = $_POST['half_days'];
$description = $_POST['description'];
$reason = $_POST['reason'];
$leave_location = $_POST['leave_location'];
$status = "pending";

$response = array();

//SELECT `id`, `user_id`, `start_date`, `start_date_leave_type`, `end_date`, `end_date_leave_type`, `status`, `request_date`, `approve_date`, `description`, `leave_location`, `reason` FROM `employee_leave_request` WHERE 1

$isLeaveRequestValid = "SELECT * FROM `user_leaves` WHERE  `user_id`= '$user_id' AND `leave_status_id` = '4' AND `leave_date` BETWEEN '$start_date' AND '$end_date'";

$result = $con->query($isLeaveRequestValid);

//echo $result->num_rows;

if ($result->num_rows < 1) {
	
  $emailResopnse = array('user_id'=>$user_id,'leave_type'=>$leave_type,'start_date'=>$start_date,'end_date'=>$end_date,'description'=>$description);

	$applied_date = getAppliedDate();

	$begin = new DateTime($start_date);
	$end   = new DateTime($end_date);
	//echo "dates ".$begin->format("Y-m-d")." ".$end->format("Y-m-d");
	
	$leave_type_id = getLeaveTypeId($con,$leave_type);
	$leave_entitlement_id = getLeaveEntitlementId($con,$user_id,$leave_type_id);
	$leave_duration_id = getLeaveDurationId($con,$leave_duration);

	$leave_request_id  = getleaveRequestId($con,$user_id,$leave_type_id,$leave_location,$reason,$description,$applied_date);

	
	//echo "applied_date ".$applied_date." leave req id ".$leave_request_id." leave type ".$leave_type_id." leave_entitlement ".$leave_entitlement_id;
	
	$totalDay = 0; $totalLeaveDays = 0;
	for($i = $begin; $i <= $end; $i->modify('+1 day')){
		//echo "inside loop";
		$totalDay = $totalDay +1;
		$durationValue = 1;
		$durationShiftValue = 1;

		$date_value = $i->format("Y-m-d");
		# SELECT `id`, `Date`, `Day`, `State`, `Occasion` FROM `holiday_calendar` WHERE 1
		$isWeekEnd = $con->query("SELECT * from `holiday_calendar` WHERE `Date` = '$date_value' AND `State` = 'weekday'");
		if ($isWeekEnd->num_rows <1) {
			$durationShiftValue =0;
		}else{

			if ($half_days == "") {
				$durationValue = getLeaveDurationId($con,$leave_duration);
				$durationShiftValue = getLeaveShiftValue($con,$leave_duration_id);
			}
			if ($half_days == "None") {
				$durationValue = 1;$durationShiftValue = 1;
			}elseif ($half_days == "All Days") {
				$durationValue = getLeaveDurationId($con,$leave_duration);
				$durationShiftValue = getLeaveShiftValue($con,$leave_duration_id);
			}elseif ($half_days == "Start Day Only" && $i->format("Y-m-d") == $start_date ) {
				$durationValue = getLeaveDurationId($con,$leave_duration);
				$durationShiftValue = getLeaveShiftValue($con,$leave_duration_id);

			}elseif ($half_days == "End Day Only" && $i->format("Y-m-d") == $end_date ) {
				$durationValue = getLeaveDurationId($con,$leave_duration);
				$durationShiftValue = getLeaveShiftValue($con,$leave_duration_id);
			}elseif ($half_days == "Start and End Day Only") {
				if ($i->format("Y-m-d") == $start_date) {
					$durationValue = getLeaveDurationId($con,$leave_duration);
					$durationShiftValue = getLeaveShiftValue($con,$leave_duration_id);
				}elseif ($i->format("Y-m-d") == $end_date) {
					$durationValue = getLeaveDurationId($con,$leave_duration);
					$durationShiftValue = getLeaveShiftValue($con,$leave_duration_id);
				}

			}

		}
		$totalLeaveDays = $totalLeaveDays + $durationShiftValue;

		//echo "shift value ".$durationShiftValue." duration ".$durationValue;

       	# SELECT `id`, `leave_request_id`, `user_id`, `leave_date`, `length_hours`, `length_days`, `duration_type`, `leave_status_id`, `comments`, `leave_type_id`, `start_time`, `end_time`, `date_applied`, `applied_by_id`, `status_date`, `status_by_id` FROM `employee_leave` WHERE 1


		$insert_leave = $con->query("INSERT into `user_leaves` (`leave_request_id`,`user_id`,`leave_date`,`length_hours`, `length_days`,`duration_type`,`leave_status_id`,`comments`,`leave_type_id`,`date_applied`,`applied_by_id`) VALUES ('$leave_request_id','$user_id','$date_value','08:00:00','$durationShiftValue','$durationValue','3','$description','$leave_type_id','$applied_date','$user_id')");

	}

    $emailResopnse['total_days'] = $totalDay;
    $emailResopnse['total_leaves'] = $totalLeaveDays;


	
	# SELECT `id`, `user_id`, `leave_type_id`, `total_days`, `total_leaves`, `leave_location`, `reason`, `description`, `leave_status_id`, `applied_by_id`, `applied_date`, `status_by_id`, `status_date`, `start_date`, `end_date` FROM `employee_leave_request` WHERE 1

	$insert_leave_request = $con->query("UPDATE `user_leave_request` set `total_days` = '$totalDay',`total_leaves`='$totalLeaveDays' WHERE `id` = '$leave_request_id'");

	if ($leave_request_id >0) {
     	$insert_entitlment = $con->query("UPDATE `leave_entitlement` SET `used_leave`= (`used_leave`+ $totalLeaveDays),`balance_leave`= (`balance_leave`- $totalLeaveDays),`modified_date`= '$applied_date',`modified_by_id`='$user_id' WHERE `user_id` = '$user_id' AND `leave_type_id` = '$leave_type_id'");

	    $insert_entitlment_history = $con->query("INSERT into `leave_entitlement_history` (`leave_request_id`,`user_id`,`leave_entitlement_id`,`leave_type_id`,`update_date`,`length_day`, `notes`) VALUES ('$leave_request_id','$user_id','$leave_entitlement_id','$leave_type_id','$applied_date','$totalLeaveDays','leave request')");
	
	}
	
	if ($leave_request_id >0) {
		sendNotification($con,$user_id,$leave_type);
		$response['error'] = false;
		$response['message'] = "Successfully Saved.";
		sendEmail($con,$emailResopnse);
	}else{
		$response['error'] = true;
		$response['message'] = "Leave not saved.";
	}
}else{
	$response['error'] = true;
	$response['message'] = "already requested.";	
}

echo json_encode($response);

function getleaveRequestId($con,$user_id,$leave_type_id,$leave_location,$reason,$description,$applied_date){
	$leave_request_id = 0;
	$leave_request_query = "INSERT into `user_leave_request` (`user_id`,`leave_type_id`,`total_days`,`total_leaves`,`leave_location`,`reason`,`description`,`leave_status_id`,`applied_by_id`,`applied_date`) VALUES ('$user_id','$leave_type_id','0','0','$leave_location','$reason','$description','3','$user_id','$applied_date')";

	if ($con->query($leave_request_query) === TRUE) {
		$leave_request_id = $con->insert_id;
	}
	return $leave_request_id;
}



function getAppliedDate(){
	$now = new DateTime(); 
	return $now->format('Y-m-d H:i:s');
}

function getLeaveShiftValue($con,$leave_duration_id){
	$durationShiftValue = 1;
	$shift_result = $con->query("SELECT * from `leave_shift` WHERE `id` = '$leave_duration_id'");
	if ($row = $shift_result->fetch_array()) {
		$durationShiftValue = $row['value'];
	}
	return $durationShiftValue;
}

function getLeaveDurationId($con,$leave_duration){
	$duration_id = 1;
	$shift_result = $con->query("SELECT * from `leave_shift` WHERE `name` = '$leave_duration'");
	if ($row = $shift_result->fetch_array()) {
		$duration_id = $row['id'];
	}
	return $duration_id;
}
function getLeaveTypeId($con,$leave_type){
	$leave_type_id = 1;
	$leave_type_result = $con->query("SELECT * from `leave_type` WHERE `name`= '$leave_type'");
	if ($row = $leave_type_result->fetch_array()) {
		$leave_type_id = $row['id'];
	}
	return $leave_type_id;
}

function getLeaveEntitlementId($con,$user_id,$leave_type_id){
	$leave_type_id = 1;
	$leave_type_result = $con->query("SELECT * from `leave_entitlement` WHERE `user_id`= '$user_id' AND `leave_type_id` = '$leave_type_id'");
	if ($row = $leave_type_result->fetch_array()) {
		$leave_type_id = $row['id'];
	}
	return $leave_type_id;	
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

    $notification = array("body" => array("module"=>"request_leave","json_response"=>$notificationArr));

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



function getUserData($con,$user_id){
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
$userArray = getUserData($con,$user_id);
$leave_type_id = getLeaveTypeId($con,$leave_type);
$leaveArray = getLeaveArray($con,$user_id,$leave_type_id);

// Multiple recipients
$To = 'ranju.kumari@technitab.in, kavinder.kohli@technitab.in'; // note the comma

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
$headers[] = 'From: Leave Reminder <no_reply@technitab.com>';
//$headers[] = 'Cc: neeraj.technitab@gmail.com';
$headers[] = 'Bcc: neeraj.kumar@technitab.in';

// Mail it
mail($To, $subject, $message, implode("\r\n", $headers));

}


?>
