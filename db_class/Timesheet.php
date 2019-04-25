<?php

/**
 * Created by PhpStorm.
 * User: AC
 * Date: 4/24/2019
 * Time: 11:12 AM
 */
class Timesheet
{
    private $date;

    private $con;

    private $active;

    private $deactive;

    function __construct()
    {
        include_once 'config/NewConfig.php';
        include_once 'config/constant.php';
        $db = new NewConfig();
        $this->con = $db->dbConnect();
        date_default_timezone_set('Asia/Kolkata');
        $this->date = date("Y-m-d H:i:s");

        $this->active = IS_ACTIVE;
        $this->deactive = DEACTIVE;

    }

    function addTimesheet($userId, $createdById, $date, $timesheetHours, $projectJson)
    {

        if (!$this->isTimesheetExist($userId,$date)){
            $lastInsertId = 0;
            foreach ($projectJson as $value) {
                if($value->time_spent != "00:00") {
                    $result = $this->con->query("INSERT INTO `zoho_timesheet` (`project_id`,`project_type_id`,`project_name`,
                    `activity_id`,`activity_type_id`,`task_name`,`user_id`,`staff_name`,`email`,
                    `time_spent`, `notes`,`date`,`create_date`,`create_by_id`) VALUES 
                    ('$value->project_id','$value->project_type_id', '$value->project','$value->activity_id',
                    '$value->activity_type_id','$value->activity','$userId',(SELECT `name` from `user` where `id` = '$userId'),
                    (SELECT `email` from `user` where `id` = '$userId'),'$value->time_spent',
                    '$value->description','$date','$this->date','$createdById')");

                    if ($result === TRUE) {
                        $lastInsertId = $this->con->insert_id;
                    }
                }
            }

            if ($lastInsertId !=0){
                $this->updateTimesheetDuration($userId,$date,$timesheetHours);
                $this->sendTimesheetMail($userId,$date,$timesheetHours,$projectJson);

                return $lastInsertId;
            }else{
                return QUERY_PROBLEM;
            }
        }else{
            return EXIST;
        }
    }

    function isTimesheetExist($userId, $date)
    {
        $result = $this->con->query("SELECT * from `zoho_timesheet` where `date` = '$date' AND `user_id` = '$userId'");
        return $result->num_rows > 0;
    }

    function updateTimesheetDuration($userId,$date,$timesheetDuration){
        include_once 'db_class/Attendance.php';
        $attendance = new Attendance();
        $attendance->updateTimesheetDuration($userId,$date,$timesheetDuration);
    }

    function sendTimesheetMail($userId,$date,$timesheetDuration,$projectJson){
        include_once 'db_class/SendMail.php';
        $mailObj = new SendMail();
        $attendanceData = $this->fetchAttendanceData($userId,$date);
        $mailObj->timesheetMail($userId,$date,$timesheetDuration,$projectJson,$attendanceData);
    }

    function fetchAttendanceData($userId,$date){
        $response = array();
        $result = $this->con->query("SELECT `attendance_duration`, `punch_in`,`punch_out`, `manual_punch_in`,
        `manual_punchout` as `manual_punch_out` from `attendance` WHERE `user_id` = '$userId' AND  `date` = '$date'");

        if ($result->num_rows >0){
            $response = $result->fetch_assoc();
        }

        return $response;
    }
}