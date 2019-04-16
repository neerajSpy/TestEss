<?php
date_default_timezone_set('Asia/Kolkata');
include "config/config.php";

$project_id = $_POST['project_id'];

$result = $con->query("SELECT pu.`user_id`,u.`name` FROM `activity_user` pu JOIN `user` u ON  pu.`user_id` = u.`id` WHERE pu.`project_id` = '$project_id'");

$response = array();
$addedUser = array();

if ($result->num_rows >0) {
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['user_id'],$addedUser)) {
            array_push($response,$row);
            array_push($addedUser,$row['user_id']);
        } 
    }
}

echo json_encode($response);
?>