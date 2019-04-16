<?php
include "config/config.php";

$user_id = $_POST['user_id'];
$year = $_POST['year'];
$month = $_POST['month'];

$response = array();


# SELECT `id`, `project_id`, `project_activity_user_id`, `user_id`, `Project Name`, `Task Name`, `Staff Name`, `Email`, `Notes`, `Time Spent`, `Begin time`, `End time`, `Date`, `Billable Status` FROM `zoho_timesheet` WHERE 1

$dateWiseResponse = getCalendarArray($con,$user_id,$month,$year);
$projectWiseResponse = getProjectArray($con,$user_id,$month,$year);
array_push($response, array("date_response"=>$dateWiseResponse,"project_response"=>$projectWiseResponse));
echo json_encode($response);


function getCalendarArray($con,$user_id,$month,$year){

	$sql_query = "SELECT * FROM `zoho_timesheet` WHERE YEAR(`date`) = '$year' AND MONTH(`date`) = '$month' AND `user_id` = '$user_id' ORDER BY `date`, `project_name` ASC";

	$rowResponse = array();
	$prevProjectDate = "";
	$spentTime = "00:00";

	$result = $con->query($sql_query);
	if($result->num_rows >0){
		while ($row = $result->fetch_array()) {

			$date = $row['date'];

			if ($date != $prevProjectDate) {
				$prevProjectDate = $date;
				$spentTime = $row['time_spent'];
				array_push($rowResponse,array("date"=>$date,"spent_time"=>$spentTime));
			}else{
                $spentTime = sum_the_time($spentTime,$row['time_spent']) ;
				$arraySize = sizeof($rowResponse) - 1;
				$rowResponse[$arraySize]['date'] = $date;
				$rowResponse[$arraySize]['spent_time'] = $spentTime;	
			}

		}
	}
	return $rowResponse;
}



function getProjectArray($con,$user_id,$month,$year){

$sql_query = "SELECT DISTINCT(`project_name`) FROM `zoho_timesheet` WHERE YEAR(`date`) = '$year' AND MONTH(`date`) = '$month' AND `user_id` = '$user_id' ORDER BY  `project_name`,`task_name`,`date` ASC";
	$result = $con->query($sql_query);
	$rowResponse = array();
	if($result->num_rows >0){
		while ($row = $result->fetch_array()) {

			$projectName = $row['project_name'];

			$particularRowResult = $con->query("SELECT * FROM `zoho_timesheet` WHERE `project_name` = '$projectName' AND `user_id` = '$user_id' AND YEAR(`date`) = '$year' AND MONTH(`date`) = '$month' ORDER BY `task_name`,`Date` ASC");

			$spentTime = "00:00";
			$prevProjectActivity = "";

			if ($particularRowResult->num_rows >0) {
				while ($rowResult = $particularRowResult->fetch_array()) {
					$projectActivity = $rowResult['task_name'];

					if ($projectActivity != $prevProjectActivity) {
						$prevProjectActivity = $projectActivity;
						$spentTime = $rowResult['time_spent'];
						array_push($rowResponse,array("project"=>$projectName,"activity_name"=>$projectActivity,"spent_time"=>$spentTime));
					}else{
						$spentTime = sum_the_time($spentTime,$rowResult['time_spent']) ;
						$arraySize = sizeof($rowResponse) - 1;
						$rowResponse[$arraySize]['project'] = $projectName;
						$rowResponse[$arraySize]['activity_name'] = $projectActivity;
						$rowResponse[$arraySize]['spent_time'] = $spentTime;

					}

				}
			}
		}
	}
	return $rowResponse;

}

// only work below 24 hours
function addTimeDuration($firstTime,$secondTime){
    $secs = strtotime($firstTime)-strtotime("00:00");
    echo "second ".$secs." first ".$firstTime." second ".$secondTime;
    $result = date("H:i",strtotime($secondTime)+$secs);
    echo " result ".$result ." ";
    return $result;
}


// also work above 24 hours

function sum_the_time($time1, $time2) {
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