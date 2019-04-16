<?php
include "config/config.php";
date_default_timezone_set('Asia/Kolkata');

$user_id = $_POST['user_id'];

$response = array();

//echo "user id ".$user_id;
//SELECT `id`, `user_id`, `start_date`, `start_date_leave_type`, `end_date`, `end_date_leave_type`, `status`, `request_date`, `approve_date`, `description`, `leave_location`, `reason` FROM `employee_leave_request` WHERE 1

$leave_exist = $con->query("SELECT elr.`id`, lt.`name`,elr.`total_days`,elr.`total_leaves`,elr.`leave_location`, elr.`reason`, elr.`description`, ls.`status` from `user_leave_request` elr
	JOIN `leave_type` lt ON elr.`leave_type_id`= lt.`id` 
	JOIN `leave_status` ls ON elr.`leave_status_id` = ls.`id`
	WHERE `user_id` = '$user_id' ORDER BY `applied_date` DESC");

//echo "leaves ".$leave_exist->num_rows;

if ($leave_exist->num_rows >0) {
	while ($row = $leave_exist->fetch_array()) {

		$id = $row['id'];
		//echo "id ".$id;
		$leaves_response = array();

		# SELECT `id`, `leave_request_id`, `user_id`, `leave_date`, `length_hours`, `length_days`, `duration_type`, `leave_status_id`, `comments`, `leave_type_id`, `start_time`, `end_time`, `date_applied`, `applied_by_id`, `status_date`, `status_by_id` FROM `user_leaves` WHERE 1
		
		$leave_query = $con->query("SELECT `leave_date`,`length_hours`,`length_days`,`duration_type`,`comments` from `user_leaves` WHERE `leave_request_id` = '$id' ORDER BY `leave_date` ASC");

		if ($leave_query->num_rows >0) {

			while ($l_row = $leave_query->fetch_array()) {
				array_push($leaves_response,array("date"=>$l_row['leave_date'],"length_hours"=>$l_row['length_hours'],"length_days"=>$l_row['length_days'],"duration_type"=>$l_row['duration_type'],"comments"=>$l_row['comments']));
			}	
		}
		array_push($response,array("id"=>$id,"leave_type"=>$row['name'],"total_days"=>$row['total_days'],"total_leaves"=>$row['total_leaves'],"leave_location"=>$row['leave_location'],"reason"=>$row['reason'],"description"=>$row['description'],"status"=>$row['status'],"leave_array"=>$leaves_response));
	}
}
echo json_encode($response);
?>
