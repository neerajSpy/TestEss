<?php
include "config/config.php";
date_default_timezone_set('Asia/Kolkata');

$project_name = $_POST['project_name'];


//SELECT `id`, `Project Name`, `Description`, `Billing Type`, `Project Cost`, `Customer Name`, `cust_id`, `Currency Code`, `project_type_id`, `Budget Type`, `Budget Amount`, `Project Budget Hours`, `CF.Estimated Days`, `tl_ts_approver_id`, `planned_start_date`, `planned_end_date`, `actual_start_date`, `actual_end_date`, `created_by`, `modified_by`, `is_active`, `created_date`, `modified_date`, `project_status` FROM `master_zoho_project` WHERE 1

$response = array();
$result = $con->query("SELECT * FROM `master_zoho_project` WHERE LOWER(`project_name`) LIKE LOWER('%$project_name%')");

if ($result->num_rows >0) {
	while ($row = $result->fetch_array()) {
		array_push($response, array("id"=>$row['id'],"project_name"=>$row['project_name'],"project_type_id"=>$row['project_type_id']));
	}
}

echo json_encode($response);


?>
