<?php
date_default_timezone_set('Asia/Kolkata');
include "config/config.php";

$trip_id = $_POST['trip_id'];
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


$updateTripResult = $con->query("SELECT * from `expense_trip` WHERE `project_id` = '$project_id' AND `start_date` = '$trip_start_date' AND `created_by_id` = '$created_by_id' AND `status` = '0'");

if ($updateTripResult->num_rows < 1) {
 $query = "UPDATE `expense_trip` set `project_id` = '$project_id',`project_name` = '$project_name',`source` = '$source',`destination`= '$destination',`start_date` = '$trip_start_date',`modified_by_id` = '$created_by_id',`modified_date` = '$current_date' WHERE `id` = '$trip_id'";


if ($con->query($query) === TRUE) {
	$last_insert_id = $con->affected_rows;
	foreach ($member_json as $value) {

		$is_active = 0;
		if (!$value->is_selected) {
			$is_active = 1;
		}
		$result = $con->query("UPDATE `expense_trip_member` set `is_active` = '$is_active' WHERE `id` = '$value->id'");

	}
}

}else{
	$last_insert_id = 0;
}

	$response = array();

	if ($last_insert_id >0) {
		$response['id'] = $last_insert_id;
		$response['error'] = false;
		$response['message'] = "Successfully Trip created.";
	}else{
		$response['id'] = $last_insert_id;
		$response['error'] = true;
		$response['message'] = "Trip is not created.";
	}

	echo json_encode($response);
	?>