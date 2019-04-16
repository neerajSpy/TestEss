<?php
include "config/config.php";

$user_id = $_POST['user_id'];
$response = array();

$project_type_list = $con->query("SELECT DISTINCT(`project_id`) FROM `activity_user` WHERE `user_id` = '$user_id'");

if ($project_type_list->num_rows >0) {
    while ($row = $project_type_list->fetch_array()) {
        $projectId = $row['project_id'];
        $projectArr = getProjectName($con,$projectId);
        $projectName = $projectArr['project_name'];
        $customerId = $projectArr['customer_id'];
        $customerName = $projectArr['customer_name'];
        $timeSheetDurationArr = getDuration($con,$projectId,$user_id);
       
        $tecClaim = getTotalTEC();
        $bookingClaim = getBookingClaims();
        $projectProfit = "1.2%";
        array_push($response,array("project_id"=>$projectId,"project_name"=>$projectName,"customer_name"=>$customerName,"total_timesheet_duration"=>$timeSheetDurationArr['timesheet_duration'],"total_billable_duration"=>$timeSheetDurationArr['billable_timesheet_duration'],"tec_claim_expense"=>$tecClaim,"booking_expense"=>$bookingClaim,"project_profile"=>$projectProfit));
    }
}

echo json_encode($response);

function getProjectName($con,$project_id){
    $response = array();
    $result = $con->query("SELECT `project_name`,`cust_id`,`customer_name` from `master_zoho_project` where  `id` = '$project_id'");
    //  echo "project result ".$result->num_rows. " ".$con->error;
    if ($result->num_rows >0){
        if ($row = $result->fetch_assoc()){
            $response['project_name'] = $row['project_name'];
            $response['customer_id'] = $row['cust_id'];
            $response['customer_name'] = $row['customer_name'];
        }
    }
    return $response;
}



function getTotalTEC(){
    return 100;
}

function getBookingClaims(){
return 100;
}

function getDuration($con,$projectId,$userId){
    $response = array();
    $timesheetDuration = "00:00";
    $billableTimesheetDuration = "00:00";
    $billableDay =0;
    $timesDurationResult  = $con->query("SELECT * from `zoho_timesheet` where `project_id` = '$projectId' AND `user_id` = '$userId'");
    if ($timesDurationResult->num_rows >0){
        while ($timesDurationRow = $timesDurationResult->fetch_assoc()){
            if ($timesDurationRow['Billable Status'] == 1){
                $billableDay++;
                $billableTimesheetDuration = addTimeDuration($billableTimesheetDuration,$timesDurationRow['time_spent']);

            }
            $timesheetDuration = addTimeDuration($timesheetDuration,$timesDurationRow['time_spent']);

        }

        $response['timesheet_duration'] = $timesheetDuration;
        $response['billable_timesheet_duration'] = $billableTimesheetDuration;
        $response['billable_days'] = $billableDay;
    }else {
        $response['timesheet_duration'] = "00:00";
        $response['billable_timesheet_duration'] = "00:00";
        $response['billable_days'] = "0";
    }
    return $response;
}

function addTimeDuration($time1, $time2) {
    $times = array($time1, $time2);
    $seconds = 0;
    foreach ($times as $time)
    {
        list($hour,$minute) = explode(':', $time);
        $seconds += $hour*3600;
        $seconds += $minute*60;
        /*$seconds += $second;*/
    }
    $hours = floor($seconds/3600);
    $seconds -= $hours*3600;
    $minutes  = floor($seconds/60);
    /*$seconds -= $minutes*60;*/
    /*if($seconds < 9)
    {
        $seconds = "0".$seconds;
    }*/
    if($minutes < 9)
    {
        $minutes = "0".$minutes;
    }
    if($hours < 9)
    {
        $hours = "0".$hours;
    }

    /*"{$hours}:{$minutes}:{$seconds}"*/
    return "{$hours}:{$minutes}";
}


?>