<?php
date_default_timezone_set('Asia/Kolkata');
include "config/config.php";

$action = $_POST['action'];
$member_id = $_POST['created_by_id'];

$query = "";
$response = array();
$drafTripResponse = array();
$submitTripResponse = array();
$bookedTripReponse = array();
$cancelledTripResponse = array();


//SELECT `id`, `project_id`, `project_name`, `source`, `destination`, `start_date`, `status`, `status_note`, `created_by_id`, `created_date`, `is_active`, `modified_by_id`, `modified_date` FROM `expense_trip` WHERE 1

if (strtolower($action) == "submit") {
	$query = "SELECT et.`id`, et.`project_id`, mzp.`project_name`,et.`end_date`, et.`source`, et.`destination`, et.`start_date`, et.`status`, et.`status_note`, et.`created_by_id`, u.`name`, et.`created_date` from `expense_trip` et jOIN `master_zoho_project` mzp on et.`project_id` = mzp.`id` jOIN `user` u on et.`created_by_id` = u.`id` JOIN `expense_trip_member` as etm on et.`id` = etm.`trip_id` AND etm.`user_id` = '$member_id' AND etm.`is_active` = '0' where et.`is_active` = '0' ORDER BY et.`id` ASC";
}else{
	$query = "SELECT et.`id`,et.`project_id`,mzp.`project_name`,et.`end_date`, et.`source`,et.`destination`,et.`start_date`,et.`status`, et.`status_note`, et.`created_by_id`, u.`name`, et.`created_date` from `expense_trip` et jOIN `master_zoho_project` mzp on et.`project_id` = mzp.`id` jOIN `user` u on et.`created_by_id` = u.`id` WHERE et.`is_active` = '0' ORDER BY et.`id` ASC";
}


$result  = $con->query($query);
if ($result->num_rows >0) {
	while ($row = $result->fetch_assoc()) {
		
		$trip_id = $row['id'];
		// SELECT `id`, `trip_id`, `user_id`, `is_active` FROM `expense_trip_member` WHERE 1
		$result_member = $con->query("SELECT etm.`id`,etm.`user_id`, u.`name` from `expense_trip_member` etm jOIN `user` u on etm.`user_id` = u.`id` WHERE etm.`trip_id` = '$trip_id' AND etm.`is_active` = '0'");
		$memberResponse = array();
		if ($result_member->num_rows >0) {
			while ($subRow = $result_member->fetch_assoc()) {
				array_push($memberResponse, array("id"=>$subRow['id'],"member_id"=>$subRow['user_id'],"memberName"=>$subRow['name'],"is_selected"=>true));
			}
		}
		//$tempArray = getTempArray($row,$memberResponse);
		if (strtolower($row['status'] == "initiated")) {
			array_push($drafTripResponse,array("id"=>$row['id'],"project_id"=>$row['project_id'],"project_name"=>$row['project_name'],"source"=>$row['source'], "destination"=>$row['destination'],"start_date"=>$row['start_date'],"end_date"=>$row['end_date'],"member_json"=>$memberResponse,"status"=>$row['status'],"remark"=>$row['status_note'],"comment"=>$row['status_note'],"created_date"=>$row['created_date'],"created_by_id"=>$row['created_by_id'],"created_by"=>$row['name']));

		}else if (strtolower($row['status'] == "ongoing")){
			array_push($submitTripResponse,array("id"=>$row['id'],"project_id"=>$row['project_id'],"project_name"=>$row['project_name'],"source"=>$row['source'], "destination"=>$row['destination'],"start_date"=>$row['start_date'],"end_date"=>$row['end_date'],"member_json"=>$memberResponse,"status"=>$row['status'],"remark"=>$row['status_note'],"comment"=>$row['status_note'],"created_date"=>$row['created_date'],"created_by_id"=>$row['created_by_id'],"created_by"=>$row['name']));

		}else if (strtolower($row['status'] == "complete")){
			array_push($bookedTripReponse,array("id"=>$row['id'],"project_id"=>$row['project_id'],"project_name"=>$row['project_name'],"source"=>$row['source'], "destination"=>$row['destination'],"start_date"=>$row['start_date'],"end_date"=>$row['end_date'],"member_json"=>$memberResponse,"status"=>$row['status'],"remark"=>$row['status_note'],"comment"=>$row['status_note'],"created_date"=>$row['created_date'],"created_by_id"=>$row['created_by_id'],"created_by"=>$row['name']));
			
		}
	}
}

if (sizeof($drafTripResponse) >0) {
array_push($response,array("status"=>"Initiated","response"=>$drafTripResponse));
}

if (sizeof($submitTripResponse) >0) {
array_push($response,array("status"=>"Ongoing","response"=>$submitTripResponse));	
}

if (sizeof($bookedTripReponse) >0) {
array_push($response,array("status"=>"Complete","response"=>$bookedTripReponse));	
}

echo json_encode($response);

function getUserName($con,$user_id){
	$userName = "";
	$result = $con->query("SELECT * from `user` where `id` = '$user_id'");
	if ($result->num_rows >0) {
		if($row = $result->fetch_array()){
			$userName = $row['name'];
		}
	}
	return $userName;
}

function getTempArray($row,$memberResponse){
	$arr = array();
	array_push($arr, array("id"=>$row['id'],"project_id"=>$row['project_id'],"project_name"=>$row['project_name'],"source"=>$row['source'], $row['destination']=>$row['destination'],"start_date"=>$row['start_date'],"member_json"=>$memberResponse,"status"=>$row['status'],"remark"=>$row['status_note'],"comment"=>$row['status_note'],"created_date"=>$row['created_date'],"created_by_id"=>$row['created_by_id'],"created_by"=>$row['name']));

return $arr;
}

?>