<?php
include "config/config.php";

$user_id = $_POST['user_id'];
$project_json = json_decode($_POST['project_json']);
$response = array();

//SELECT `id`, `project_id`, `project_type_id`, `activity_type_id`, `activity_id`, `Project Name`, `Task Name`, `user_id`,
// `Staff Name`, `Email`, `Notes`, `Time Spent`, `Begin time`, `End time`, `Date`, `Billable Status`,
// `approve_status`, `approved_date`, `approve_by_id`, `create_date`, `create_by_id` FROM `zoho_timesheet` WHERE 1

$approve_date =  getAppliedDate();

foreach ($project_json as $value) {
    $insert_query = "UPDATE `zoho_timesheet` set `time_spent` = '$value->time_spent',`billable_status` = '$value->is_billable',`approve_status`= '2',`approved_date` = '$approve_date', `approve_by_id` = '$user_id' where `id` = '$value->id'";
    $res = $con->query($insert_query);
    }

if ($con->affected_rows >0) {
    $response['error'] = false;
    $response['message'] = "Successfully Saved.";
}else{
    $response['error'] = true;
    $response['message'] = "Timesheet not updated.";
}

echo json_encode($response);


function getAppliedDate(){
    $now = new DateTime();
    return $now->format('Y-m-d H:i:s');
}
?>