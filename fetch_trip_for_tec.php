<?php
date_default_timezone_set('Asia/Kolkata');
include "config/config.php";


$member_id = $_POST['created_by_id'];

$query = "";
$response = array();



//SELECT `id`, `project_id`, `project_name`, `source`, `destination`, `start_date`, `status`, `status_note`, `created_by_id`, `created_date`, `is_active`, `modified_by_id`, `modified_date` FROM `expense_trip` WHERE 1

	$query = "SELECT et.`id`, et.`project_id`, mzp.`project_name`,et.`end_date`, et.`source`, et.`destination`, et.`start_date`, et.`status`, et.`status_note`, et.`created_by_id`, u.`name`, et.`created_date` from `expense_trip` et jOIN `master_zoho_project` mzp on et.`project_id` = mzp.`id` jOIN `user` u on et.`created_by_id` = u.`id` JOIN `expense_trip_member` as etm on et.`id` = etm.`trip_id` AND etm.`user_id` = '$member_id' AND etm.`is_active` = '0' where et.`is_active` = '0'";

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
			array_push($response,array("id"=>$row['id'],"project_id"=>$row['project_id'],"project_name"=>$row['project_name'],"source"=>$row['source'], "destination"=>$row['destination'],"start_date"=>$row['start_date'],"end_date"=>$row['end_date'],"member_json"=>$memberResponse,"status"=>$row['status'],"remark"=>$row['status_note'],"comment"=>$row['status_note'],"created_date"=>$row['created_date'],"created_by_id"=>$row['created_by_id'],"created_by"=>$row['name']));

	}
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

?>