<?php
include "config/config.php";

date_default_timezone_set('Asia/Kolkata');

$employee_id = $_POST['employee_id'];
$task_json = json_decode($_POST['task_json']);
$date = date("Y-m-d");

$response = array();


//SELECT `id`, `employee_id`, `task_name`, `create_date` FROM `task` WHERE 1

foreach ($task_json->task as $value) {
    $sql_query = "INSERT into `task` (`employee_id`,`task_name`,`create_date`) VALUES('$employee_id','$value->task_name','$date')";
    $resul = $con->query($sql_query);
}

if (mysqli_affected_rows($con)>0) {
    $response['error'] = false;
    $response['message'] = "Successfully Saved";
}else{
    $response['error'] = true;
    $response['message'] = mysqli_error($con);
}

echo json_encode($response);
?>