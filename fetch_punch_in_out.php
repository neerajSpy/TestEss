<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 8/16/2018
 * Time: 11:28 AM
 */

include "config/config.php";
$user_id = $_POST['user_id'];
$date = $_POST['date'];

$response = array();

$punchInOutArray = getPunchInOutArray($con, $user_id, $date);
$activityUserArray = fetchActivityUserArray($con, $user_id);

/*array_push($response,array("punch_in"=>$punchInOutArray['punch_in'],"punch_out"=>$punchInOutArray['punch_out'],"activity_user_array"=>$activityUserArray));*/
echo json_encode(array("punch_in"=>$punchInOutArray['punch_in'],"punch_out"=>$punchInOutArray['punch_out'],"activity_user_array"=>$activityUserArray));


function getPunchInOutArray($con, $user_id, $date){
    $arr = array();
    $result = $con->query("SELECT * from  `attendance` where `user_id` = '$user_id' AND `date` = '$date'");
    if ($result->num_rows > 0) {
        if ($row = $result->fetch_assoc()) {
            /*$arr['punch_in'] = $row['punch_in'];
            $arr['punch_out'] = $row['punch_out'];*/
            $arr = $row;
        }
    }
    return $arr;
}

function fetchActivityUserArray($con, $user_id){
    $arr = array();
    $sql_query = "SELECT au.`project_id`,mzp.`project_name`,mzp.`project_type_id`,a.`id`,au.`activity_type_id`, ptat.`activity_type`  FROM `activity_user` au LEFT JOIN `project_type_activity_type` ptat ON au.`activity_type_id` = ptat.`id` LEFT JOIN `master_zoho_project` mzp ON au.`project_id` = mzp.`id`
LEFT JOIN `activity` a on au.`project_id` = a.`project_id` AND au.`activity_type_id` = a.`activity_type_id` WHERE au.`user_id` = '$user_id' AND au.`is_active` = '1' ORDER By au.`project_id` DESC";
    $result = $con->query($sql_query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            array_push($arr, array("project_id" => $row['project_id'], "project" => $row['project_name'], "project_type_id" => $row['project_type_id'], "activity_id" => $row['id'], "activity_type_id" => $row['activity_type_id'], "activity" => $row['activity_type'], "time_spent" => "", "is_billable" => "0"));
        }
    }
    return $arr;
}

?>