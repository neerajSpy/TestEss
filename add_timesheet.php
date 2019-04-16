<?php
include "config/config.php";

$user_id = $_POST['user_id'];
$created_by_id = $_POST['created_by_id'];
$timesheet_hours = $_POST['timesheet_hours'];
$staff_name = $_POST['staff_name'];
$email = $_POST['email'];
$date = $_POST['date'];
$project_json = json_decode($_POST['project_json']);
$response = array();


if ($email == ""){
    $email = getUserEmail($con,$user_id);
}

	// SELECT `id`, `project_id`, `project_type_id`, `activity_type_id`, `activity_id`, `Project Name`,
// `Task Name`, `user_id`, `Staff Name`, `Email`, `Notes`, `Time Spent`, `Begin time`, `End time`, `Date`, `Billable Status`,
// `approve_status`, `approved_date`, `approve_by_id`, `create_date`, `create_by_id` FROM `zoho_timesheet` WHERE 1

$last_insert_id = -1;
$current_date = getAppliedDate();

$sql_query = "SELECT * from `zoho_timesheet` where `date` = '$date' AND `email` = '$email'";
$result = $con->query($sql_query);

if($result->num_rows < 1){

    $last_insert_id = 0;
    $res = $con->query("UPDATE `attendance` set `timesheet_duration` = '$timesheet_hours' where `date`= '$date' AND `user_id` = '$user_id'");

	foreach ($project_json as $value) {

		/*echo "project_id ".$value->project_id."type id ".$value->project_type_id."project ".$value->project."activity id ".$value->activity_id." a type id ".$value->activity_type_id','$value->activity','$user_id',$staff_name','$email','$value->time_spent','$value->description'*/
        if($value->time_spent != "00:00") {
            $insert_query = "INSERT INTO `zoho_timesheet` (`project_id`,`project_type_id`,`project_name`,`activity_id`,`activity_type_id`,`task_name`,`user_id`,`staff_name`,`email`,`time_spent`, `notes`,`date`,`create_date`,`create_by_id`) VALUES ('$value->project_id','$value->project_type_id', '$value->project','$value->activity_id','$value->activity_type_id','$value->activity','$user_id','$staff_name','$email','$value->time_spent','$value->description','$date','$current_date','$created_by_id')";

            if ($con->query($insert_query) === TRUE) {
                $last_insert_id = $con->insert_id;
            }
        }
	}

}

if ($last_insert_id >0) {
    $response['error'] = false;
    $response['message'] = "Successfully Saved.";
}else if ($last_insert_id == -1){
    $response['error'] = true;
    $response['message'] = "Data not saved, Duplicate timesheet";
}else{
    $response['error'] = true;
    $response['message'] = "Timesheet not saved";
}

echo json_encode($response);


function getAppliedDate(){
    $now = new DateTime();
    return $now->format('Y-m-d H:i:s');
}

function getUserEmail($con,$user_id){
    $email = "";
    $result = $con->query("SELECT * from `user` where `id` = '$user_id'");
    if ($result->num_rows >0){
        if ($row = $result->fetch_assoc()){
            $email = $row['email'];
        }
    }
    return $email;
}

?>