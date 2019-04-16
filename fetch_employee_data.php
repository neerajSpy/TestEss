<?php
include "config/config.php";

$user_id = $_POST['user_id'];

$attendance_response = array();
$leave_response = array();


//SELECT `id`, `user_id`, `date`, `update_date`, `attendance`, `hrs`, `punch_in`, `punch_out`, `manual_punch_in`, `manual_punchout`,
// `manual_punch_in_sys_time`, `manual_punch_out_sys_time`, `punch_in_photo_path`, `punch_out_photo_path`, `punch_in_address`, `punch_in_location`, `punch_out_address`, `punch_out_location`, `summery` FROM `attendance` WHERE 1

$sql_query = "SELECT * from `attendance` WHERE `user_id` = '$user_id' ORDER By `date` ASC";

$result = $con->query($sql_query);
if($result->num_rows >0) {
    while ($row = $result->fetch_array()) {

        $punch_in = $row['punch_in'];
        $punch_out = $row['punch_out'];

        $manual_punch_in = $row['manual_punch_in'];
        $manual_punchout = $row['manual_punchout'];
        array_push($attendance_response,array("date"=>$row['date'],"attendance"=>$row['attendance'],"spent_time"=>$row['attendance_duration'],"punch_in"=>$punch_in,"punch_out"=>$punch_out,"manual_punch_in"=>$manual_punch_in,"manual_punch_out"=>$manual_punchout,"summery"=>$row['summery'],"manual_punch_sys_in"=>$row['manual_punch_in_sys_time'],"manual_punch_sys_out"=>$row['manual_punch_out_sys_time'],"in_location"=>$row['punch_in_location'],"out_location"=>$row['punch_out_location'],"in_address"=>$row['punch_in_address'],"out_address"=>$row['punch_out_address'],"out_photo_path"=>$row['punch_out_photo_path'],"in_photo_path"=>$row['punch_in_photo_path'],"punch_status"=>$row['status'],"timesheet_hours"=>$row['timesheet_duration']));
    }
}

echo json_encode(array("attendance"=>$attendance_response,"leave_response"=>$leave_response));
?>
