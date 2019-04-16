<?php
include "config/config.php";
date_default_timezone_set('Asia/Kolkata');


$userId = $_POST['user_id'];
$action = $_POST['action'];

//SELECT `id`, `project_name`, `Description`, `Billing Type`, `Project Cost`, `Customer Name`, `cust_id`, `Currency Code`, `project_type_id`, `Budget Type`, `Budget Amount`, `Project Budget Hours`, `CF.Estimated Days`, `tl_ts_approver_id`, `planned_start_date`, `planned_end_date`, `actual_start_date`, `actual_end_date`, `created_by`, `modified_by`, `is_active`, `created_date`, `modified_date`, `project_status` FROM `master_zoho_project` WHERE 1

//
$response = array();
$sql_query = "";
if (strtolower(trim($action)) == "submit") {
	$sql_query = "SELECT tp.*, u.`name`, pt.`type` FROM `temp_project` as tp JOIN `user` as u ON tp.`created_by_id` = u.`id` JOIN `project_type` as pt ON tp.`project_type_id` = pt.`id` WHERE tp.`is_active` = '0' AND tp.`created_by_id` = '$userId' ORDER BY tp.`id` DESC";
}else if (strtolower(trim($action)) == "approve") {
	$sql_query = "SELECT tp.*, u.`name`, pt.`type` FROM `temp_project` as tp JOIN `user` as u ON tp.`created_by_id` = u.`id` JOIN `project_type` as pt ON tp.`project_type_id` = pt.`id` WHERE tp.`is_active` = '0' ORDER BY tp.`id` DESC";
}else if (strtolower(trim($action)) == "add") {
	$sql_query = "SELECT mzp.*, u.`name`, pt.`type` FROM `master_zoho_project` as mzp JOIN `user` as u ON mzp.`created_by_id` = u.`id` JOIN `project_type` as pt ON mzp.`project_type_id` = pt.`id` WHERE mzp.`is_active` = '0' ORDER BY mzp.`id` DESC ";
} 

$result = $con->query($sql_query);
if ($result->num_rows >0) {
	while ($row = $result->fetch_assoc()) {

		array_push($response, array("id"=>$row['id'],"project_name"=>$row['project_name'],"project_type_id"=>$row['project_type_id'],"project_type"=>$row['type'],"description"=>$row['description'],"billing_type"=>$row['billing_type'],"project_cost"=>$row['project_cost'],"customer_name"=>$row['customer_name'],"cust_id"=>$row['cust_id'],"client_name"=>$row['client_name'],"currency_code"=>$row['currency_code'],"budget_type"=>$row['budget_type'],"budget_amount"=>$row['budget_amount'],"project_budget_hours"=>$row['project_budget_hours'],"estimated_days"=>$row['estimated_days'],"tl_ts_approver_id"=>$row['tl_ts_approver_id'],"location"=>$row['location'],"address"=>$row['address'],"district"=>$row['district'],"country"=>$row['country'],"state"=>$row['state'],"is_job_allocation_sheet"=>$row['is_job_allocation_sheet'],"is_system_backup"=>$row['is_system_backup'],"planned_start_date"=>$row['planned_start_date'],"planned_end_date"=>$row['planned_end_date'],"created_by_id"=>$row['created_by_id'],"name"=>$row['name'],"modified_by_id"=>$row['modified_by_id']));
	}
}

echo json_encode($response);
?>
