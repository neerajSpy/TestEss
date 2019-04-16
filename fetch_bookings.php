<?php
include "config/config.php";
date_default_timezone_set('Asia/Kolkata');

$trip_id = $_POST['trip_id'];


$bookingRequestResponse = array();
$bookingDoneResponse = array();
$paymentRequestResponse = array();
$paymentDoneResponse = array();
$cancellationRequestResponse = array();
$cancelledResponse = array();
$response = array();

$result = $con->query("SELECT eb.*,bp.`id` as `payment_id` from `emp_booking` as eb LEFT JOIN `booking_payment` as bp on eb.`id` = bp.`booking_id` WHERE eb.`trip_id` = '$trip_id' AND eb.`is_active` = '0' ORDER BY FIELD(eb.`trip_status`,'Booking Requested','Booking Done','Payment Requested','Payment Done','Cancellation Requested','Cancelled'), eb.`created_date` ASC");

if ($result->num_rows >0 ) {
	while ($row = $result->fetch_assoc()) {
		$booking_id = $row['id'];

		$memberRes = $con->query("SELECT ebm.*,u.`name` from `emp_booking_member` as ebm JOIN `user` as u on ebm.`user_id` = u.`id` WHERE ebm.`booking_id` = '$booking_id'");
		$memberArray = array();
		if ($memberRes->num_rows >0) {
			while ($subRow = $memberRes->fetch_assoc()) {
				array_push($memberArray,array("id"=>$subRow['id'],"member_id"=>$subRow['user_id'],"memberName"=>$subRow['name']));
			}
		}

		if (strtolower($row['trip_status']) == strtolower('Booking Requested'))  {
			$rowResponse = getArray($row,$memberArray);	
			array_push($bookingRequestResponse,$rowResponse);
		}else if(strtolower($row['trip_status']) == strtolower('Booking Done')){
			$rowResponse = getArray($row,$memberArray);	
			array_push($bookingDoneResponse,$rowResponse);
		}else if(strtolower($row['trip_status']) == strtolower('Payment Requested')){
			$rowResponse = getArray($row,$memberArray);	
			array_push($paymentRequestResponse,$rowResponse);
		}else if (strtolower($row['trip_status']) == strtolower('Payment Done')){
			$rowResponse = getArray($row,$memberArray);	
			array_push($paymentDoneResponse,$rowResponse);
		}else if(strtolower($row['trip_status']) == strtolower('Cancellation Requested')){
			$rowResponse = getArray($row,$memberArray);	
			array_push($cancellationRequestResponse,$rowResponse);
		}else if(strtolower($row['trip_status']) == strtolower('Cancelled')){
			$rowResponse = getArray($row,$memberArray);	
			array_push($cancelledResponse,$rowResponse);
		}
	}
}



	// SELECT `id`, `user_id`, `booking_id`, `is_active`, `created_by_id`, `created_date`, `modified_date`, `modified_by_id` FROM `emp_booking_member` WHERE 1

if(sizeof($bookingRequestResponse) >0){
	array_push($response,array("status"=>"Booking Requested","response"=>$bookingRequestResponse));
}if(sizeof($bookingDoneResponse) >0){
	array_push($response,array("status"=>"Booking Done","response"=>$bookingDoneResponse));
}if(sizeof($paymentRequestResponse) >0){
	array_push($response,array("status"=>"Payment Requested","response"=>$paymentRequestResponse));
}if(sizeof($paymentDoneResponse) >0){
	array_push($response,array("status"=>"Payment Done","response"=>$paymentDoneResponse));
}if(sizeof($cancellationRequestResponse) >0){
	array_push($response,array("status"=>"Cancellation Requested","response"=>$cancellationRequestResponse));
}if(sizeof($cancelledResponse) >0){
	array_push($response,array("status"=>"Cancelled","response"=>$cancelledResponse));
}


echo json_encode($response);

function getArray($row,$memberArray){

	$paymentId = 0;
	if ($row['payment_id'] != NULL) {
		$paymentId = $row['payment_id'];
	}

	$response = array("id"=>$row['id'],"trip_id"=>$row['trip_id'],"travel_type"=>$row['travel_type'],"user_booking_mode"=>$row['user_booking_mode'],"user_vendor"=>$row['user_vendor'],"user_city_area"=>$row['user_city_area'],"user_vendor_id"=>$row['user_vendor_id'],"user_source"=>$row['user_source'],"user_destination"=>$row['user_destination'],"user_travel_date"=>$row['user_travel_date'],"user_instruction"=>$row['user_instruction'],"user_check_in"=>$row['user_check_in'],"user_check_out"=>$row['user_check_out'],"user_room"=>$row['user_room'],"user_total_amount"=>$row['user_total_amount'],"trip_status"=>$row['trip_status'],"admin_booking_mode"=>$row['admin_booking_mode'],"admin_city_area"=>$row['admin_city_area'],"admin_vendor"=>$row['admin_vendor'],"admin_vendor_id"=>$row['admin_vendor_id'],"admin_source"=>$row['admin_source'],"admin_destination"=>$row['admin_destination'],"admin_instruction"=>$row['admin_instruction'],"admin_check_in"=>$row['admin_check_in'],"admin_check_out"=>$row['admin_check_out'],"admin_room"=>$row['admin_room'],"admin_total_amount"=>$row['admin_total_amount'],"admin_departure_date_time"=>$row['admin_departure_date_time'],"admin_arrival_date_time"=>$row['admin_arrival_date_time'],"tax_name"=>$row['tax_name'],"tax_percent"=>$row['tax_percent'],"tax_amount"=>$row['tax_amount'],"tax_type"=>$row['tax_type'],"service_tax_name"=>$row['service_tax_name'],"service_tax_percent"=>$row['service_tax_percent'],"service_tax_amount"=>$row['service_tax_amount'],"service_tax_type"=>$row['service_tax_type'],"total_amount"=>$row['total_amount'],"payment_id"=>$paymentId,"admin_booking_attachment"=>$row['admin_booking_attachment'],"trip_booking_member"=>$memberArray);
	return $response;
}

function getUserData($con,$user_id){
	$userName = "";
	$result = $con->query("SELECT * from `user` WHERE `id` = '$user_id'");
	if ($result->num_rows >0) {
		if ($row = $result->fetch_assoc()) {
			$userName = $row['name'];
		}
	}
	return $userName;
}


function sendNotification($con,$user_id){
	$userName = getUserData($con,$user_id);
	$token_query = "SELECT uft.`token`  from `user` as u JOIN `user_fcm_token` as uft on u.`id` = uft.`user_id` WHERE u.`access_control_id` = 2";
	$ss= $con->query($token_query);

	$ch = curl_init("https://fcm.googleapis.com/fcm/send");
	$serverKey = "AIzaSyDJ0MiSBWBsQN5y-ybhWr2GNGFzTPsSfFQ";

	$notificationArr = array();
	array_push($notificationArr,array("user_name"=>$userName));

	$notification = array("body" => array("module"=>"Booking Request","json_response"=>$notificationArr));

	while($r= ($ss->fetch_array())) {

		$f = $r['token'];
		$arrayToSend = array('to' => $f, 'data' => $notification);

		$json = json_encode($arrayToSend);
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


function getUserArray($con,$user_id){
	$userArray = array();
	$result = $con->query("SELECT * from `user` WHERE `id` = '$user_id'");
	if($row = $result->fetch_array()){
		$userArray = $row;
	}

	return $userArray;
}

function getProjectType($con,$type_id){
	$type = "";
	$result = $con->query("SELECT * From `project_type` WHERE `id` = '$type_id'");
	if ($result->num_rows >0) {
		if ($row = $result->fetch_assoc()) {
			$type = $row['type'];
		}
	}
	return $type;
}


?>