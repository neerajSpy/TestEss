<?php
include "config/config.php";
include_once 'db_class/Timesheet.php';

$userId = $_POST['user_id'];
$createdById = $_POST['created_by_id'];
$timesheetHours = $_POST['timesheet_hours'];
$date = $_POST['date'];
$projectJson = json_decode($_POST['project_json']);
$response = array();

$timesheetObj = new Timesheet();
$result = $timesheetObj->addTimesheet($userId,$createdById,$date,$timesheetHours,$projectJson);


$response = array();
if ($result == EXIST) {
    $response['error'] = TRUE;
    $response['message'] = "Data not saved, Duplicate timesheet";
} else if ($result == QUERY_PROBLEM || $result == 0) {
    $response['error'] = TRUE;
    $response['message'] = "Timesheet not saved";
}else {
    $response['error'] = FALSE;
    $response['message'] = "Successfully Saved";
    
}

echo json_encode($response);


?>