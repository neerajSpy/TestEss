<?php
include "config/config.php";
date_default_timezone_set('Asia/Kolkata');

$name = $_POST['name'];

$is_active = 1;
//SELECT `id`, `access_control_id`, `role_id`, `user_role`, `company`, `mobile_number`, `name`, `email`, `password`, `create_date`, `update_date`, `is_active` FROM `user` WHERE 1

$response = array();
$result = $con->query("SELECT * FROM `user` WHERE LOWER(`name`) LIKE LOWER('%$name%') AND `is_active` = '$is_active'");

if ($result->num_rows >0) {
	while ($row = $result->fetch_array()) {
		array_push($response, array("id"=>$row['id'],"name"=>$row['name'],"is_selected"=>"false"));
	}
}

echo json_encode($response);


?>
