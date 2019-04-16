<?php
include "config/config.php";

$project_id = $_POST['project_id'];
$project_type_id = $_POST['project_type_id'];

	  	// SELECT `id`, `project_id`, `project_type_id`, `activity_type_id`, `Description`, `estimated_hrs`, `Is Billable`, `Task Rate Per Hour`, `Task Budget Hours`, `created_by_id`, `modified_by_id`, `is_active`, `created_date`, `modified_date`, `planned_start`, `actual_start`, `planned_end`, `actual_end`, `budget_hours`, `spent_hours` FROM `activity` WHERE 1


		//SELECT `id`, `activity_type`, `project_type_id`, `is_default`, `created_by`, `modified_by`, `is_active`, `created`, `modified` FROM `project_type_activity_type` WHERE 1

$exist_response = array();
$non_exist_response = array();


$result = $con->query("SELECT * from `activity` WHERE `project_id` = '$project_id' AND `project_type_id` = '$project_type_id' AND `is_active` = '0'");

if ($result->num_rows >0) {
	while ($row = $result->fetch_array()) {
		$activity_type_id = $row['activity_type_id'];
		$activity_type = getActivityType($con,$activity_type_id);

		array_push($non_exist_response,array("id"=>$row['id'],"activity_type"=>$activity_type,"activity_type_id"=>$activity_type_id,"is_selected"=>"true"));
	}	

}
echo json_encode($non_exist_response);

 function getActivityType($con,$activity_type_id){
	
	$activity_type = "";
	$result = $con->query("SELECT * FROM `project_type_activity_type` WHERE `id`='$activity_type_id'");
	if ($result->num_rows >0) {
		if ($row = $result->fetch_array()) {
			$activity_type = $row['activity_type'];
		}
	}
	return $activity_type;
}
?>