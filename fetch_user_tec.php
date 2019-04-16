<?php
include "config/config.php";

date_default_timezone_set('Asia/Kolkata');

$created_by_id = $_POST['created_by_id'];


$query ="";

if ($created_by_id > 0 ) {
	//echo "user";
	$query = "SELECT emc.*, mzp.`project_name`, mzp.`is_international`, u.`name` from `emp_main_tec` as emc join `master_zoho_project` as mzp on emc.`project_id` = mzp.`id` join user as u on emc.`created_by_id` = u.`id` where emc.`created_by_id` = '$created_by_id' AND emc.`is_active` = '0' ORDER BY FIELD(emc.`status`,'draft', 'submit', 'open','paid'),emc.`id` DESC";
}else {
	//echo "admin";
$query = "SELECT emc.*, mzp.`project_name`, mzp.`is_international`, u.`name` from `emp_main_tec` as emc join `master_zoho_project` as mzp on emc.`project_id` = mzp.`id` join user as u on emc.`created_by_id` = u.`id` WHERE emc.`status` != 'draft' AND emc.`is_active` = '0' ORDER BY FIELD(emc.`status`, 'submit', 'open','paid'),emc.`id` DESC";	
}


$response = array();
$draftResponse = array();
$submitResponse = array();
$openResponse = array();
$paidResponse = array();

$result = $con->query($query);

//echo "num ".$result->num_rows;
if ($result->num_rows >0) {
	while ($main_row = $result->fetch_assoc()) {
		$tec_id = $main_row['id'];
		$total_amount = getTecClaimAmount($con,$tec_id);
		if (strtolower($main_row['status']) == 'draft') {

		array_push($draftResponse,array("id"=>$tec_id,"trip_id"=>$main_row['trip_id'],"name"=>$main_row['name'],"project_id"=>$main_row['project_id'],"project_name"=>$main_row['project_name'],"claim_start_date"=>$main_row['claim_start_date'],"claim_end_date"=>$main_row['claim_end_date'],"base_location"=>$main_row['base_location'],"site_location"=>$main_row['site_location'],"status"=>$main_row['status'],"total_amount"=>$total_amount,"description"=>$main_row['description'],"created_by_id"=>$main_row['created_by_id'],"created_date"=>$main_row['created_date'],"user_note"=>$main_row['user_note'],"remark"=>$main_row['remark'],"submit_by_id"=>$main_row['submit_by_id'],"submit_date"=>$main_row['submit_date'],"modified_date"=>$main_row['modified_date'],"modified_by_id"=>$main_row['modified_by_id'],"is_international"=>$main_row['is_international']));

		}else if(strtolower($main_row['status']) == 'submit'){
			array_push($submitResponse,array("id"=>$tec_id,"trip_id"=>$main_row['trip_id'],"name"=>$main_row['name'],"project_id"=>$main_row['project_id'],"project_name"=>$main_row['project_name'],"claim_start_date"=>$main_row['claim_start_date'],"claim_end_date"=>$main_row['claim_end_date'],"base_location"=>$main_row['base_location'],"site_location"=>$main_row['site_location'],"status"=>$main_row['status'],"total_amount"=>$total_amount,"description"=>$main_row['description'],"created_by_id"=>$main_row['created_by_id'],"created_date"=>$main_row['created_date'],"user_note"=>$main_row['user_note'],"remark"=>$main_row['remark'],"submit_by_id"=>$main_row['submit_by_id'],"submit_date"=>$main_row['submit_date'],"modified_date"=>$main_row['modified_date'],"modified_by_id"=>$main_row['modified_by_id'],"is_international"=>$main_row['is_international']));
		
		}else if(strtolower($main_row['status']) == 'open'){
			array_push($openResponse,array("id"=>$tec_id,"trip_id"=>$main_row['trip_id'],"name"=>$main_row['name'],"project_id"=>$main_row['project_id'],"project_name"=>$main_row['project_name'],"claim_start_date"=>$main_row['claim_start_date'],"claim_end_date"=>$main_row['claim_end_date'],"base_location"=>$main_row['base_location'],"site_location"=>$main_row['site_location'],"status"=>$main_row['status'],"total_amount"=>$total_amount,"description"=>$main_row['description'],"created_by_id"=>$main_row['created_by_id'],"created_date"=>$main_row['created_date'],"user_note"=>$main_row['user_note'],"remark"=>$main_row['remark'],"submit_by_id"=>$main_row['submit_by_id'],"submit_date"=>$main_row['submit_date'],"modified_date"=>$main_row['modified_date'],"modified_by_id"=>$main_row['modified_by_id'],"is_international"=>$main_row['is_international']));
		
		}else if(strtolower($main_row['status']) == 'paid'){
			array_push($paidResponse,array("id"=>$tec_id,"trip_id"=>$main_row['trip_id'],"name"=>$main_row['name'],"project_id"=>$main_row['project_id'],"project_name"=>$main_row['project_name'],"claim_start_date"=>$main_row['claim_start_date'],"claim_end_date"=>$main_row['claim_end_date'],"base_location"=>$main_row['base_location'],"site_location"=>$main_row['site_location'],"status"=>$main_row['status'],"total_amount"=>$total_amount,"description"=>$main_row['description'],"created_by_id"=>$main_row['created_by_id'],"created_date"=>$main_row['created_date'],"user_note"=>$main_row['user_note'],"remark"=>$main_row['remark'],"submit_by_id"=>$main_row['submit_by_id'],"submit_date"=>$main_row['submit_date'],"modified_date"=>$main_row['modified_date'],"modified_by_id"=>$main_row['modified_by_id'],"is_international"=>$main_row['is_international']));
		}
	}
}

if (sizeof($draftResponse) >0) {
array_push($response,array("status"=>"Draft","response"=>$draftResponse));
}

if (sizeof($submitResponse) >0) {
array_push($response,array("status"=>"Submit","response"=>$submitResponse));	
}

if (sizeof($openResponse) >0) {
array_push($response,array("status"=>"Open","response"=>$openResponse));	
}

if (sizeof($paidResponse) >0) {
array_push($response,array("status"=>"Paid","response"=>$paidResponse));	
}
echo json_encode($response);	



function getTecClaimAmount($con,$tec_id){
	$total_amount = 0;
	$result = $con->query("SELECT * from `emp_tec_entry` WHERE `tec_id` = '$tec_id' AND `paid_by` = 'Employee' AND `is_active` = '0'");
	if ($result->num_rows >0) {
		while ($row = $result->fetch_assoc()) {
			$total_amount = $total_amount + $row['bill_amount'];
		}
	}
	return $total_amount;
}


?>