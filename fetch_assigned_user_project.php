
<?php
include "config/config.php";

$user_id = $_POST['user_id'];
$response = array();


$sql_query = "SELECT au.`project_id`, mzp.`project_name`, mzp.`project_type_id`, a.`id`, au.`activity_type_id`, ptat.`activity_type` FROM `activity_user` au LEFT JOIN `project_type_activity_type` ptat ON au.`activity_type_id` = ptat.`id` LEFT JOIN `master_zoho_project` mzp ON au.`project_id` = mzp.`id` LEFT JOIN `activity` a on au.`project_id` = a.`project_id` AND au.`activity_type_id` = a.`activity_type_id` WHERE au.`user_id` = '$user_id' AND au.`is_active` = '1' ORDER By au.`project_id` DESC" ;
$result = $con->query($sql_query);

if($result->num_rows >0){
	while ($row = $result->fetch_array()) {
		array_push($response,array("project_id" =>$row['project_id'],"project"=>$row['project_name'],"project_type_id"=>$row['project_type_id'],"activity_id"=>$row['id'],"activity_type_id"=>$row['activity_type_id'],"activity"=>$row['activity_type'],"time_spent"=>"","is_billable"=>"0"));
	}
}

echo json_encode($response);
?>