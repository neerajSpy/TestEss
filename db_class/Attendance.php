<?php

/* error_reporting(E_ALL);
ini_set('display_errors', 1);*/

class Attendance
{

    private $date;
    private $dateTime;

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
        $this->date = date("Y-m-d");
        $this->dateTime = date("Y-m-d H:i:s");
        $this->bookingBasePath = "http://ess.technitab.in/web_service/ESS/trip_booking/";

        $this->active = IS_ACTIVE;
        $this->deactive = DEACTIVE;
    }

    function punchIn($userId, $punchDate, $punchIn, $attendance, $address, $location, $status)
    {
        $previousDate = $this->getPreviousDate($punchDate);

        if ($this->canPunchIn($previousDate, $userId)) {

            if (!$this->isAttendanceExist($userId, $punchDate)) {
                $result = $this->con->query("INSERT into `attendance` (`user_id`,`punch_in`,`attendance`,`date`,`update_date`,
        `punch_in_address`,`punch_in_location`,`status`) VALUES('$userId','$punchIn','$attendance','$punchDate',
        '$this->date','$address','$location','$status')");

                if ($result === TRUE) {
                    return $this->con->insert_id;
                } else {
                    return QUERY_PROBLEM;
                }
            } else {
                return EXIST;
            }
        } else {
            return TIMESHEET_REQUIRED;
        }
    }

    function punchOut($userId, $punchDate, $attendance, $punchOut, $spentTime, $location, $address, $status)
    {
        $result = $this->con->query("UPDATE `attendance` set  `attendance` = '$attendance',`punch_out` = '$punchOut', 
        `attendance_duration` = '$spentTime', `punch_out_location`= '$location',`punch_out_address` = '$address', 
        `status` = '$status' WHERE `date`= '$punchDate' AND `user_id` = '$userId'");

        if ($result === TRUE) {
            $this->sendMail($userId, $punchDate,false);
            return $this->con->affected_rows;
        } else {
            return QUERY_PROBLEM;
        }

    }


    function manualPunchIn($userId, $date, $punchIn, $punchOut, $attendance, $attendanceDuration, $remark)
    {
        if ($this->canManualPunchIn($date)) {

            if (!$this->isPunchInDone($userId, $date)) {
                $result = $this->con->query("INSERT INTO `attendance` (`date`,`attendance`,`attendance_duration`,`manual_punch_in`,
                    `manual_punchout`,`manual_punch_out_sys_time`,`manual_punch_in_sys_time`,`user_id`,`status`,
                    `remark`) VALUES('$date','$attendance','$attendanceDuration','$punchIn','$punchOut',
                    '$this->dateTime','$this->dateTime','$userId','2','$remark')");

                if ($result === TRUE) {
                    $this->sendMail($userId, $date,true);
                    return $this->con->insert_id;

                } else {
                    return QUERY_PROBLEM;
                }
            } else {
                $result = $this->con->query("UPDATE `attendance` set `date` = '$date',`attendance_duration`
                    = '$attendanceDuration',`punch_in` = '$punchIn', `punch_out` = '$punchOut' , 
                   `attendance` ='$attendance',`remark`='$remark' WHERE `date` ='$date' AND `user_id` = '$userId'");

                if ($result === TRUE) {
                    $this->sendMail($userId, $date,true);
                    return $this->con->affected_rows;
                } else {
                    return QUERY_PROBLEM;
                }
            }

        } else {
            return EXIST;
        }
    }

    function updateTimesheetDuration($userId, $date, $timesheetDuration)
    {
        $this->con->query("UPDATE `attendance` set `timesheet_duration` = '$timesheetDuration' 
        WHERE `date` ='$date' AND `user_id` = '$userId'");
    }

    function isAttendanceExist($userId, $date)
    {
        $result = $this->con->query("SELECT * from `attendance` WHERE `user_id` = '$userId' AND 
        `date` = '$date' AND `status` = '2'");

        return $result->num_rows > 0;
    }

    function isPunchInDone($userId, $date)
    {
        $result = $this->con->query("SELECT * from `attendance` WHERE `user_id` = '$userId' AND `date` = '$date'");
        return $result->num_rows > 0;
    }

    function canPunchIn($previousDate, $userId)
    {
        $punchPossible = 0;
        $canPunchIn = 0;

        while ($punchPossible == 0) {
            if ($this->isFirstDay($userId) < 1) {
                $canPunchIn = 1;
                $punchPossible = 1;
                break;
            } else if ($this->isTimesheetPresent($previousDate, $userId) > 0) {

                $canPunchIn = 1;
                $punchPossible = 1;
                break;
            } else if ($this->isUserOnLeave($previousDate, $userId) > 0 || $this->isWeekDay($previousDate) < 1) {
                $previousDate = $this->getPreviousDate($previousDate);
            } else {

                $canPunchIn = 0;
                $punchPossible = 2;
                break;
            }
        }
        // echo "punchPossible 1 yes 2 not poosible ".$punchPossible." canPunchIn ".$canPunchIn."\n";
        return $canPunchIn;
    }

    function canManualPunchIn($punchDate)
    {
        /*$newDate = date('Y-m-d', strtotime($punchDate . ' +1 day'));*/
        $date2 =  date_create($punchDate);
        $date1 = date_create($this->date);
        $dateDiff = date_diff($date1,$date2);

        return $dateDiff->format("%a") <= 1;

    }


    function sendMail($userId, $date,$isManual)
    {

        include 'SendMail.php';
        $mailObj = new SendMail();
        
        if ($isManual){
            $mailSubject = "Manual Attendance ";
        }else{
            $mailSubject = "Attendance ";
        }
        
        $attendanceData = $this->getAttendanceByDate($userId, $date);
        $mailObj->attendanceMail($attendanceData, $userId,$mailSubject);


    }

    function getAttendanceByDate($userId, $date)
    {
        $response = array();
        $result = $this->con->query("SELECT * from `attendance` WHERE `user_id` = '$userId' AND `date` = '$date'");
        if ($result->num_rows > 0) {
            $response = $result->fetch_assoc();
        }
        return $response;
    }

    function isFirstDay($userId)
    {
        $result = $this->con->query("SELECT * from `attendance` WHERE `user_id` = '$userId'");
        return $result->num_rows;
    }

    function isWeekDay($previousDate)
    {
        $result = $this->con->query("SELECT * from `holiday_calendar` WHERE `Date` = '$previousDate' 
        AND `State` = 'weekday'");
        return $result->num_rows;
    }

    function isUserOnLeave($previousDate, $userId)
    {
        $result = $this->con->query("SELECT * from `user_leaves` WHERE `user_id` = '$userId' AND 
        `leave_date` = '$previousDate' AND `leave_status_id` = '4'");
        return $result->num_rows;
    }

    function isTimesheetPresent($previousDate, $userId)
    {
        $result = $this->con->query("SELECT * from `zoho_timesheet` WHERE `Date` = '$previousDate' AND 
        `user_id` = '$userId'");

        return $result->num_rows;
    }

    function getPreviousDate($date)
    {
        return date('Y-m-d', strtotime($date . ' -1 day'));
    }


}



