<?php
include "config/config.php";

$user_id = $_POST['user_id'];
$year = $_POST['year'];
$month = $_POST['month'];

$response = array();


# SELECT `id`, `project_id`, `project_activity_user_id`, `user_id`, `Project Name`, `Task Name`, `Staff Name`, `Email`, `Notes`, `time_spent`, `Begin time`, `End time`, `Date`, `Billable Status` FROM `zoho_timesheet` WHERE 1

$sql_query = "";

$sql_query = "SELECT DISTINCT(`project_name`) FROM `zoho_timesheet` WHERE YEAR(`date`) = '$year' AND MONTH(`date`) = '$month' AND `user_id` = '$user_id' ORDER BY  `project_name`,`date`  ASC";

$result = $con->query($sql_query);

if($result->num_rows >0){

	while ($row = $result->fetch_array()) {
		
		$projectName = $row['project_name'];

		$particularRowResult = $con->query("SELECT * FROM `zoho_timesheet` WHERE `project_name` = '$projectName' AND `user_id` = '$user_id' AND YEAR(`date`) = '$year' AND MONTH(`date`) = '$month' ORDER BY `date` ASC");

		$spentTime = 0;
		$rowResponse = array();
		$prevProjectDate = "";
		if ($particularRowResult->num_rows >0) {
			while ($rowResult = $particularRowResult->fetch_array()) {
				$projectDate = $rowResult['date'];
				
				if ($projectDate != $prevProjectDate) {
					$prevProjectDate = $projectDate;
					$spentTime = $rowResult['time_spent'];
					array_push($rowResponse,array("date"=>$projectDate,"spent_time"=>$spentTime));
				}else{
                    $spentTime = sum_the_time($spentTime,$rowResult['time_spent']) ;
					$arraySize = sizeof($rowResponse) - 1;
					$rowResponse[$arraySize]['date'] = $projectDate;
					$rowResponse[$arraySize]['spent_time'] = $spentTime;
					
				}

			}
		}
		array_push($response,array("project"=>$projectName,"project_response"=>$rowResponse));
	}
}

echo json_encode($response);


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

// query for date wise timesheet 
/*SELECT * FROM `zoho_timesheet` WHERE YEAR(`Date`) = '2018' AND MONTH(`Date`) = '06' AND `user_id` = '1' ORDER BY `Date`, `project_name` ASC*/
?>