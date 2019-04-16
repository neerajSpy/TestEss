<?php
date_default_timezone_set('Asia/Kolkata');
include "config/config.php";


$trip_id = $_POST['trip_id'];
$created_by_id = $_POST['created_by_id'];
$current_date = date("Y-m-d h:m:s");

//SELECT `id`, `project_id`, `project_name`, `start_date`, `status`, `status_note`, `created_by_id`, `created_date`, `is_active`, `modified_by_id`, `modified_date` from `expense_trip` WHERE 1

$last_insert_id = 0;

$query = "UPDATE `expense_trip` set `is_active` = '1',`modified_by_id` = '$created_by_id',`modified_date` = '$current_date' WHERE `id` = '$trip_id'";


if ($con->query($query) === TRUE) {
		$last_insert_id = $con->affected_rows;
		$result = $con->query("UPDATE `expense_trip_member` set `is_active` = '1' WHERE `trip_id` = '$trip_id'");
}

	$response = array();

	if ($last_insert_id >0) {
		$response['error'] = false;
		$response['message'] = "Successfully Trip deleted.";
	}else{
		$response['id'] = $last_insert_id;
		$response['error'] = true;
		$response['message'] = "Trip is not deleted.";
	}

	echo json_encode($response);
	?>