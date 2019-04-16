<?php
include "config/config.php";

$user_id = $_POST['user_id'];

$response = array();

//SELECT `id`, `user_id`, `document_name`, `scanned`, `original`, `file_path` FROM `employee_docs` WHERE 1

$response = array();

$result = $con->query("SELECT * FROM `employee_docs` WHERE `user_id` = '$user_id'");
if ($result->num_rows >0) {
	while ($row = $result->fetch_assoc()) {
		array_push($response,array("id"=>$row['id'],"document_name"=>$row['document_name'],"scanned"=>$row['scanned'],"original"=>$row['original'],"file_path"=>$row['file_path']));
	}
}

echo json_encode($response);
?>