<?php
date_default_timezone_set('Asia/Kolkata');
include "config/config.php";

$project_id = $_POST['project_id'];
$project_name = $_POST['project_name'];
$source = $_POST['source'];
$destination = $_POST['destination'];
$trip_start_date = $_POST['trip_start_date'];
$created_by_id = $_POST['created_by_id'];
$member_json = json_decode($_POST['member_json']);
$current_date = date("Y-m-d h:m:s");

//SELECT `id`, `project_id`, `project_name`, `start_date`, `status`, `status_note`, `created_by_id`, `created_date`, `is_active`, `modified_by_id`, `modified_date` from `expense_trip` WHERE 1

$last_insert_id = -1;

$exitingTripResult = $con->query("SELECT * from `expense_trip` WHERE `project_id` = '$project_id' AND `start_date` = '$trip_start_date' AND `created_by_id` = '$created_by_id' AND `status` = '0'");

if ($exitingTripResult->num_rows < 1) {

$query = "INSERT into `expense_trip` (`project_id`,`project_name`,`source`,`destination`,`start_date`,`status`,`created_by_id`,`created_date`) VALUES ('$project_id','$project_name','$source','$destination','$trip_start_date','initiated','$created_by_id','$current_date')";


if ($con->query($query) === TRUE) {
	$last_insert_id = $con->insert_id;
	foreach ($member_json as $value) {
		$result = $con->query("INSERT into `expense_trip_member` (`trip_id`,`user_id`) VALUES ('$last_insert_id','$value->member_id')");
	}
}
}else{
	$last_insert_id = 0;
}


	$response = array();

	if ($last_insert_id >0) {
		$response['id'] = $last_insert_id;
		$response['error'] = false;
		$response['message'] = "Successfully Trip updated.";
	}else if ($last_insert_id == 0){
		$response['id'] = $last_insert_id;
		$response['error'] = true;
		$response['message'] = "Trip is not created.";
	}else {
		$response['id'] = $last_insert_id;
		$response['error'] = true;
		$response['message'] = "Trip already exist.";
	}

	echo json_encode($response);
	?>