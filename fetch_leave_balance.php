<?php
include "config/config.php";

$user_id = $_POST['user_id'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$leave_type = $_POST['leave_type'];

$response = array();
$weekday = 0;

//SELECT `id`, `user_id`, `entitled_leave`, `leave_type_id`, `used_leave`, `balance_leave`, `note`, `credited_date`, `created_by_id`, `modified_by_date`, `modified_by_id` FROM `leave_entitlement` WHERE 1

$sql_query = "SELECT * FROM `leave_entitlement` where `user_id`='$user_id' AND `leave_type_id` = (SELECT `id` FROM `leave_type` WHERE `name` = '$leave_type')";

if ($start_date != "" && $end_date != "") {
	$query = "SELECT * FROM `holiday_calendar` WHERE `State` = 'weekday' AND `Date` BETWEEN '$start_date' AND '$end_date'";

	$result = $con->query($query);
	$weekday = $result->num_rows;
}


$result = $con->query($sql_query);
if($result->num_rows >0){
    if ($row = $result->fetch_array()) {
		$response['error'] = false;
		$response['balance_leave'] = $row['balance_leave'];
		$response['remaining_leaves'] = $row['balance_leave'] - $weekday;
	}
}else {
	$response['error'] = true;
		$response['balance_leave'] = 0;
	$response['remaining_leaves'] = 0;	
}


	//SELECT `id`, `Date`, `Day`, `State`, `Occasion` FROM `holiday_calendar` WHERE 1

echo json_encode($response);

?>
