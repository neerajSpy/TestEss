<?php
date_default_timezone_set('Asia/Kolkata');
include "config/config.php";

$state = $_POST['state'];
$response = array();

$sql = "";
if (strtolower($state) == strtolower('International')) {
	$sql = "SELECT * from `master_zoho_customer` where `is_international` = '1'";
}else{
   $sql = "SELECT * from `master_zoho_customer` where LOWER(`state`) LIKE LOWER('%$state%')";
}

$result = $con->query($sql);

if ($result->num_rows >0) {
	while ($row = $result->fetch_assoc()) {
		array_push($response,array("id"=>$row['id'],"customer_name"=>$row['display_name']));
	}
}

echo json_encode($response);

?>