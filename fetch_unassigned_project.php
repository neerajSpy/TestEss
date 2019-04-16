<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Kolkata');
include "config/config.php";

$customer_id = $_POST['customer_id'];
$user_id = $_POST['user_id'];
$role_id = $_POST['role_id'];
$country = $_POST['country'];
$state = $_POST['state'];

$userProjectArray = getUserProject($con,$user_id);
$related_table = getRelatedTable($con,$user_id);
$userProjectTypeIdArray = getUserProjectTypeId($con,$role_id,$related_table);

$response = array();

$result = $con->query("SELECT mzp.*, u.`name`, pt.`type` from `master_zoho_project` as mzp join `project_type` as pt on mzp.`project_type_id` = pt.`id` join user as u on mzp.`created_by_id` = u.`id` WHERE mzp.`cust_id` = '$customer_id' AND mzp.`state` = '$state'");
if ($result->num_rows >0) {
	while ($row = $result->fetch_assoc()) {
		
		if (!in_array($row['id'],$userProjectArray,true)) {
			if (in_array($row['project_type_id'],$userProjectTypeIdArray,true)) {
				array_push($response,array("id"=>$row['id'],"project_name"=>$row['project_name'],"project_type_id"=>$row['project_type_id'],"project_type"=>$row['type'],"description"=>$row['description'],"billing_type"=>$row['billing_type'],"project_cost"=>$row['project_cost'],"customer_name"=>$row['customer_name'],"cust_id"=>$row['cust_id'],"client_name"=>$row['client_name'],"currency_code"=>$row['currency_code'],"budget_type"=>$row['budget_type'],"budget_amount"=>$row['budget_amount'],"project_budget_hours"=>$row['project_budget_hours'],"estimated_days"=>$row['estimated_days'],"tl_ts_approver_id"=>$row['tl_ts_approver_id'],"location"=>$row['location'],"address"=>$row['address'],"district"=>$row['district'],"is_job_allocation_sheet"=>$row['is_job_allocation_sheet'],"is_system_backup"=>$row['is_system_backup'],"planned_start_date"=>$row['planned_start_date'],"planned_end_date"=>$row['planned_end_date'],"created_by_id"=>$row['created_by_id'],"name"=>$row['name'],"modified_by_id"=>$row['modified_by_id']));	
			}
		}
	}
}

echo json_encode($response);

function getUserProject($con,$user_id){
	$response  = array();
	$result = $con->query("SELECT DISTINCT(`project_id`) from `activity_user` WHERE `user_id` = '$user_id'");
	if ($result->num_rows >0) {
		while ($row = $result->fetch_assoc()) {
			array_push($response,$row['project_id']);
		}
	}
	return $response;
}


function getRelatedTable($con,$user_id){
	$related_table = "";
	$result = $con->query("SELECT * from `user` WHERE `id` = '$user_id'");
	if ($result->num_rows >0) {
		$row = $result->fetch_assoc();
		$related_table = $row['related_table'];
	}
	return $related_table;
}

function getUserProjectTypeId($con,$role_id,$related_table){
	$projectTypeArray = array();
	$result = $con->query("SELECT pt.`id` from $related_table as me join `project_type` as pt  on me.`org_unit_id` = pt.`org_unit_id` WHERE me.`id` = '$role_id'");

	if ($result->num_rows >0) {
		while($row = $result->fetch_assoc()){
			array_push($projectTypeArray,$row['id']);
		}
	}
	return $projectTypeArray;
}


?>