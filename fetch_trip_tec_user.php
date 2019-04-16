<?php
include "config/config.php";

$trip_id = $_POST['trip_id'];
$response = array();

$tripMemberArray = getTripMember ($con,$trip_id);
print_r($tripMemberArray);
for ($i=0; $i <sizeof($tripMemberArray) ; $i++) { 
	$user_id = $tripMemberArray[$i];

	echo "user_id ".$user_id;

	$result = $con->query("SELECT emc.*,mzp.`Project Name`, u.`name` from `emp_main_tec` as emc join `master_zoho_project` as mzp on emc.`project_id` = mzp.`id` JOIN `user` as u ON emc.`created_by_id` = u.`id`  WHERE emc.`trip_id` = '$trip_id' AND emc.`created_by_id` = '$user_id' AND LOWER(emc.`status`) != LOWER('draft')");

    $tripTecUserArray = array();
	if ($result->num_rows >0) {
		while ($row = $result->fetch_assoc()) {
			array_push($tripTecUserArray,array("id"=>$row['id'],"name"=>$row['name'],"project_id"=>$row['project_id'],"project_name"=>$row['Project Name'],"claim_start_date"=>$row['claim_start_date'],"claim_end_date"=>$row['claim_end_date'],"base_location"=>$row['base_location'],"site_location"=>$row['site_location'],"status"=>$row['status'],"total_amount"=>$total_amount,"description"=>$row['description'],"created_by_id"=>$row['created_by_id'],"created_date"=>$row['created_date'],"user_note"=>$row['user_note'],"remark"=>$row['remark'],"submit_by_id"=>$row['submit_by_id'],"submit_date"=>$row['submit_date'],"modified_date"=>$row['modified_date'],"modified_by_id"=>$row['modified_by_id']));
		}
	}

    $userName = getUserName($con,$user_id);	
	array_push($response,array("name"=>$userName,"user_tecs"=>$tripTecUserArray));
}


echo json_encode($response);


function getUserName($con,$user_id){
	// SELECT `id`, `trip_id`, `user_id`, `is_active` FROM `expense_trip_member` WHERE 1
	$name = "";
	$result = $con->query("SELECT * from `user` WHERE `id` = '$user_id'");
	if ($result->num_rows >0) {
		if ($row = $result->fetch_assoc()) {
			$name = $row['name'];
		}
	}
	return $name;
}

function getTripMember ($con,$trip_id){
	// SELECT `id`, `trip_id`, `user_id`, `is_active` FROM `expense_trip_member` WHERE 1
	$response = array();
	$result = $con->query("SELECT `user_id` from `expense_trip_member` WHERE `trip_id` = '$trip_id' AND `is_active` = '0'");
	if ($result->num_rows >0) {
		while ($row = $result->fetch_assoc()) {
			array_push($response,$row['user_id']);
		}
	}
	return $response;
}

?>