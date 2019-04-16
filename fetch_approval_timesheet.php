<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 8/13/2018
 * Time: 12:15 PM
 */

include "config/config.php";
$project_id = $_POST['project_id'];

$date = $_POST['date'];
$start_week_date = getStartWeekDate($date);
$end_week_date = getEndWeekDate($date);

/*echo "start_date ".$start_week_date."\n";
echo "end_date ".$end_week_date."\n";*/
$response = array();
//SELECT `id`, `project_id`, `project_type_id`, `activity_type_id`, `activity_id`, `Project Name`, `Task Name`, `user_id`,
// `Staff Name`, `Email`, `Notes`, `Time Spent`, `Begin time`, `End time`, `Date`, `Billable Status`,
// `approve_status`, `approved_date`, `approve_by_id`, `create_date`, `create_by_id` FROM `zoho_timesheet` WHERE 1

$result = $con->query("SELECT * from `zoho_timesheet` WHERE `project_id` = '$project_id' AND `date` BETWEEN '$start_week_date' AND '$end_week_date' ORDER BY `user_id` ASC");

if ($result->num_rows >0){
    while ($row = $result->fetch_array()){
        array_push($response,array("id"=>$row['id'],"name"=>$row['staff_name'],"date"=>$row['date'],"activity"=>$row['task_name'],"time_spent"=>$row['time_spent'],"description"=>$row['notes'],"is_billable"=>$row['billable_status']));
    }
}

echo json_encode($response);

//fetch_assigned_user_project

function getStartWeekDate($signUpWeek){
    $startWeekDate = "";
    for($i = 0; $i <7 ; $i++) {
        $date = date('Y-m-d', strtotime("-".$i."days", strtotime($signUpWeek)));
        $dayName = date('D', strtotime($date));
        if($dayName == "Sun") {
            $startWeekDate = $date;
        }
    }
    return $startWeekDate;
}


function getEndWeekDate($signUpWeek){
    $endWeekDate = "";
    for($i = 0; $i <7 ; $i++) {
        $date = date('Y-m-d', strtotime("+".$i."days", strtotime($signUpWeek)));
        $dayName = date('D', strtotime($date));
        if($dayName == "Sat") {
            $endWeekDate = $date;
        }
    }
    return $endWeekDate;
}


function getUserName($con,$user_id){
    $user_name = "Unauthorised";
    $result = $con->query("SELECT * FROM `user` WHERE `id` = '$user_id'");
    if ($result->num_rows >0) {
        if ($row = $result->fetch_array()) {
            $user_name = $row['name'];
        }
    }
    return $user_name;
}




?>