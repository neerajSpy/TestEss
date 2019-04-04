<?php

// namespace db_class;
/* error_reporting(E_ALL);
ini_set('display_errors', 1);*/

class Leave
{

    private $date;

    private $con;

    private $active;

    private $deactive;

    private $durationValueArray;

    private $leaveStatusIdArray;

    private $shiftValueArray;

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

        $this->durationValueArray = array(
            'none' => "None",
            'all_days' => "All Days",
            'start_days_only' => "Start Day Only",
            'end_days_only' => "End Day Only",
            'start_and_end_days_only' => "Start and End Day Only"
        );

        $this->shiftValueArray = array(
            'first_half' => "1st Half",
            'second_half' => "2nd Half"
        );

        $this->leaveStatusIdArray = array(
            'rejected' => "1",
            'cancelled' => "2",
            'pending' => "3",
            'approved' => "4",
            'weekend' => "5",
            'holiday' => "6"
        );
    }

    function fetchLeaveBalance($userId, $leaveType, $startDate, $endDate, $durationValue)
    {
        $totalBalnceLave = $this->getTotalLeaveBalance($userId, $leaveType);
        $leaveDaysCount = $this->countUserLeaveAquireDays($startDate, $endDate, $durationValue);
        $response = array();
        $response['error'] = false;
        $response['balance_leave'] = $totalBalnceLave;
        $response['leave_count'] = $leaveDaysCount;
        $response['remaining_leaves'] = $totalBalnceLave - $leaveDaysCount;
        return $response;
    }


    function approveLeave($leaveRequestId, $approvedById, $status, $comment = null)
    {

        if (strtolower($status) == "approve") {
            $statusId = $this->leaveStatusIdArray['approved'];
        } else {
            $statusId = $this->leaveStatusIdArray['rejected'];
        }

        if ($comment == null) {
            $comment = "";
        }

        $result = $this->updateLeaveRequestStatus($leaveRequestId, $approvedById, $statusId, $comment);
        if ($result === TRUE && $statusId = $this->leaveStatusIdArray['approved']) {
            $entitledResult = $this->updateEntitlement($leaveRequestId, $approvedById);
            if ($entitledResult === TRUE) {
                $this->insertLeaveEntitlementLog($leaveRequestId, $approvedById);
            }
        }
        return $result;
    }

    function leaveRequest($leaveJson)
    {
        return $this->proceedLeave($leaveJson, $this->leaveStatusIdArray['pending']);
    }

    function assignLeave($leaveJson)
    {
        $result = $this->proceedLeave($leaveJson, $this->leaveStatusIdArray['approved']);
        if ($result > 0) {
            $this->approveLeave($result, $leaveJson->created_by_id, "approve", null);
        }
        return $result;
    }

    function proceedLeave($leaveJson, $status)
    {
        $userId = $leaveJson->user_id;
        $leaveType = $leaveJson->leave_type;
        $startDate = $leaveJson->start_date;
        $endDate = $leaveJson->end_date;
        $duration = $leaveJson->leave_duration;
        $shift = $leaveJson->shift;
        $reason = $leaveJson->reason;
        $description = $leaveJson->description;
        $leaveLocation = $leaveJson->leave_location;
        $createdById = $leaveJson->created_by_id;

        if (!($this->isLeavePresent($userId, $leaveType, $startDate, $endDate, $shift))) {

            if ($this->isSufficientLeave($userId, $leaveType, $startDate, $endDate, $duration)) {
                $leaveTypeId = $this->getLeaveTypeId($leaveType);
                $leaveRequestId = $this->generateLeaveRequestId($userId, $leaveTypeId, $startDate, $endDate, $leaveLocation, $reason, $description, $createdById, $duration, $status);
                if ($leaveRequestId != QUERY_PROBLEM) {
                    $id = $this->insertLeave($leaveRequestId, $userId, $startDate, $endDate, $leaveTypeId, $shift, $duration, $description, $createdById, $status);

                    if ($id > 0) {
                        $this->sendLeaveMail($leaveJson, $userId, $createdById, $startDate, $endDate, $duration, $leaveTypeId);
                    }

                    return $id;
                } else {
                    return QUERY_PROBLEM;
                }
            } else {
                return INSUFFICIENT_LEAVE;
            }
        } else {
            return EXIST;
        }
    }

    function updateEntitlement($leaveRequestId, $modifiedById)
    {
        $this->con->query("UPDATE `leave_entitlement` as et, (SELECT * FROM `user_leave_request` WHERE `id` = '$leaveRequestId') 
      as ulr SET et.`used_leave` = et.`used_leave`+ ulr.`total_leaves`, et.`balance_leave` =  
      et.`balance_leave` - ulr.`total_leaves`, `modified_by_id` = '$modifiedById', `modified_date` = '$this->date'
      WHERE et.`user_id` = ulr.`applied_by_id` AND et.`leave_type_id` = ulr.`leave_type_id`");

        return $this->con->affected_rows > 0;
    }

    function insertLeaveEntitlementLog($leaveRequestId, $modifiedById)
    {
        $this->con->query("INSERT into `leave_entitlement_history` (`leave_request_id`,`leave_entitlement_id`,
        `length_day`,`update_date`) VALUES ('$leaveRequestId',(SELECT le.`id` from `leave_entitlement` as le join `user_leave_request` 
        as ulr on le.`user_id` = ulr.`user_id` AND le.`leave_type_id` = ulr.`leave_type_id` AND 
        ulr.`id` = '$leaveRequestId'),(SELECT `total_leaves` from `user_leave_request` where 
        `id` = '$leaveRequestId'),'$this->date')");
    }

    function sendLeaveMail($leaveJson, $userId, $createdById, $startDate, $endDate, $duration, $leaveTypeId)
    {
        include_once 'SendMail.php';
        $mailObj = new SendMail();

        $totalLeave = $this->countUserLeaveAquireDays($startDate, $endDate, $duration);
        $entitleUserData = $this->getEntitledUserData($userId, $leaveTypeId);
        if ($userId != $createdById) {
            $mailObj->leaveAssignedMail($leaveJson, $totalLeave, $entitleUserData['entitled_leave'], $entitleUserData['used_leave'], $entitleUserData['balance_leave']);
        } else {
            $mailObj->leaveRequest($leaveJson, $totalLeave, $entitleUserData['entitled_leave'], $entitleUserData['used_leave'], $entitleUserData['balance_leave']);
        }
    }


    function approveLeaveMail($approveById, $leaveRequestId)
    {
        include_once 'SendMail.php';
        $mailObj = new SendMail();

        $response = $this->getLeaveDataById($leaveRequestId);
        $entitleUserData = $this->getEntitledUserData($response['user_id'],$response['leave_type_id']);

        $mailObj->approveLeaveMail($response,$approveById,$entitleUserData['entitled_leave'], $entitleUserData['used_leave'], $entitleUserData['balance_leave']);


    }

    function updateLeaveRequestStatus($leaveRequestId, $approvedById, $statusId, $comment)
    {
        $this->con->query("UPDATE `user_leave_request` set `leave_status_id` = '$statusId', `status_by_id` = '$approvedById',`status_description` = '$comment', `status_date` = '$this->date' where `id` = '$leaveRequestId' ");

        return $this->con->affected_rows > 0;
    }

    function insertLeave($leaveRequestId, $userId, $startDate, $endDate, $leaveTypeId, $shift, $duration, $description, $createdById, $status)
    {
        $begin = new DateTime($startDate);
        $end = new DateTime($endDate);

        $lastInsertId = 0;

        for ($i = $begin; $i <= $end; $i->modify('+1 day')) {

            $converDate = $i->format('Y-m-d');

            $durationValue = "Full Day";

            if (strlen(trim($duration)) < 1 || strtolower(trim($duration)) == "none" || strtolower(trim($duration)) == "all days") {
                $durationValue = $shift;
            } elseif (strtolower(trim($duration)) == "start day only" && $converDate == $startDate) {
                $durationValue = $shift;
            } elseif (strtolower(trim($duration)) == "end day only" && $converDate == $endDate) {
                $durationValue = $shift;
            } elseif (strtolower(trim($duration)) == "start and end day only") {
                if ($converDate == $startDate || $converDate == $endDate) {
                    $durationValue = $shift;
                }
            }

            $leaveShiftDataArr = $this->getLeaveShiftDataByName($durationValue);

            $result = $this->con->query("INSERT into `user_leaves` (`leave_request_id`,`user_id`,`leave_date`,
       `length_hours`, `length_days`,`duration_type`,`leave_status_id`,`comments`,`leave_type_id`,`date_applied`,
       `applied_by_id`) VALUES ('$leaveRequestId','$userId','$converDate','" . $leaveShiftDataArr['hour'] . "',
       '" . $leaveShiftDataArr['value'] . "','" . $leaveShiftDataArr['id'] . "','$status',
       '$description','$leaveTypeId','$this->date','$createdById')");

            if ($result === TRUE) {
                $lastInsertId = $this->con->insert_id;
            }
        }

        return $lastInsertId;
    }

    function generateLeaveRequestId($userId, $leaveTypeId, $startDate, $endDate, $leaveLocation, $reason, $description, $createdById, $durationValue, $status)
    {

        $totalLeaves = $this->countUserLeaveAquireDays($startDate, $endDate, $durationValue);
        $totalDays = $this->getTotalLeaveDays($startDate, $endDate);

        $result = $this->con->query("INSERT into `user_leave_request` (`user_id`,`leave_type_id`,`total_days`,`total_leaves`,`start_date`,`end_date`,
        `duration`,`leave_location`,`reason`,`description`,`leave_status_id`,`applied_by_id`,`applied_date`) VALUES ('$userId','$leaveTypeId',
        '$totalDays','$totalLeaves','$startDate','$endDate','$durationValue','$leaveLocation','$reason','$description','$status','$createdById',
        '$this->date')");

        if ($result === TRUE) {
            return $this->con->insert_id;
        } else {
            return QUERY_PROBLEM;
        }
    }

    function isLeavePresent($userId, $leaveType, $startDate, $endDate, $shift)
    {
        $isPresentLeave = FALSE;
        while (strtotime($startDate) <= strtotime($endDate)) {
            if ($this->canTakeLeave($userId, $startDate, $shift) === FALSE) {
                $isPresentLeave = TRUE;
                break;
            }
            $startDate = date('Y-m-d', strtotime($startDate . ' +1 day'));
        }
        return $isPresentLeave;
    }

    function canTakeLeave($userId, $date, $shift)
    {
        // SELECT `id`, `leave_request_id`, `user_id`, `leave_date`, `length_hours`, `length_days`, `duration_type`,
        // `leave_status_id`, `comments`, `leave_type_id`, `start_time`, `end_time`, `date_applied`,
        // `applied_by_id`, `status_date`, `status_by_id` FROM `user_leaves` WHERE 1
        $canTake = TRUE;

        $leaveShiftId = $this->getShiftId($shift);
        $result = $this->con->query("SELECT `duration_type` from `user_leaves` where `user_id` = '$userId' AND `leave_date` = '$date' AND `leave_status_id` = '" . $this->leaveStatusIdArray['approved'] . "'");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $durationTypeId = $row['duration_type'];
                if ($durationTypeId == 1 || $durationTypeId == $leaveShiftId) {
                    $canTake = FALSE;
                    break;
                }
            }
        }

        return $canTake;
    }

    function getLeaveDataById($requestId)
    {
        $response = array();
        $result = $this->con->query("SELECT * from `user_leave_request` WHERE `id` = '$requestId'");
        if ($result->num_rows > 0)
            $response = $result->fetch_assoc();
        return $response;
    }

    function getShiftId($shift)
    {
        $id = 1;
        if (strtolower(trim($shift)) == strtolower($this->shiftValueArray['first_half'])) {
            $id = 2;
        } else if (strtolower(trim($shift)) == strtolower($this->shiftValueArray['second_half'])) {
            $id = 3;
        }
        return $id;
    }

    private function isSufficientLeave($userId, $leaveType, $startDate, $endDate, $durationValue)
    {
        $balanceLeave = $this->getTotalLeaveBalance($userId, $leaveType);
        $aquireLeaveDays = $this->countUserLeaveAquireDays($startDate, $endDate, $durationValue);
        return $balanceLeave >= $aquireLeaveDays;
    }

    function getTotalLeaveBalance($userId, $leaveType)
    {
        $leaveBalance = 0;
        $result = $this->con->query("SELECT `balance_leave` FROM `leave_entitlement` where `user_id`='$userId' AND 
        `leave_type_id` = (SELECT `id` FROM `leave_type` WHERE `name` = '$leaveType')");

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $leaveBalance = $row['balance_leave'];
        }
        return $leaveBalance;
    }

    function countUserLeaveAquireDays($startDate, $endDate, $durationValue)
    {
        $count = 0;
        $totalWeekDays = $this->getTotalWeekDays($startDate, $endDate);

        if (strtolower(trim($durationValue)) == strtolower($this->durationValueArray['none'])) {
            $count = $totalWeekDays;
        } else if (strtolower(trim($durationValue)) == strtolower($this->durationValueArray['all_days'])) {
            $count = $totalWeekDays / 2;
        } else if (strtolower(trim($durationValue)) == strtolower($this->durationValueArray['start_and_end_days_only'])) {
            $count = $totalWeekDays - 1;
        } else {
            $count = $totalWeekDays - 0.5;
        }
        return $count;
    }

    function getLeaveTypeId($leaveType)
    {
        $leaveTypeId = 0;
        $result = $this->con->query("SELECT `id` from `leave_type` WHERE `name`= '$leaveType'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $leaveTypeId = $row['id'];
        }
        return $leaveTypeId;
    }

    function getLeaveShiftDataByName($shiftName)
    {
        $shiftArr = array();
        $result = $this->con->query("SELECT * from `leave_shift` WHERE `name` = '$shiftName'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $shiftArr = $row;
        }
        return $shiftArr;
    }

    function getLeaveShiftValue($shiftId)
    {
        $durationShiftValue = 1;
        $result = $this->con->query("SELECT `value` from `leave_shift` WHERE `id` = '$shiftId'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $durationShiftValue = $row['value'];
        }
        return $durationShiftValue;
    }

    function getLeaveShiftId($shiftValue)
    {
        $id = 1;
        $result = $this->con->query("SELECT `id` from `leave_shift` WHERE `name` = '$shiftValue'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id = $row['id'];
        }
        return $id;
    }

    function getEntitledUserData($userId, $leaveTypeId)
    {
        $data = array();
        $result = $this->con->query("SELECT * from `leave_entitlement` WHERE `user_id`= '$userId' AND `leave_type_id` = '$leaveTypeId'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $data = $row;
        }
        return $data;
    }

    function getTotalWeekDays($startDate, $endDate)
    {
        $result = $this->con->query("SELECT * FROM `holiday_calendar` WHERE `State` = 'weekday' AND `Date` 
        BETWEEN '$startDate' AND '$endDate'");
        return $result->num_rows;
    }

    function getLeaveEntitlementId($userId, $leaveTypeId)
    {
        $entitledId = 1;
        $result = $this->con->query("SELECT `id` from `leave_entitlement` WHERE `user_id`= '$userId' AND `leave_type_id` = '$leaveTypeId'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $entitledId = $row['id'];
        }
        return $entitledId;
    }

    function getTotalLeaveDays($startDate, $endDate)
    {
        $first = new DateTime($startDate);

        $second = new DateTime($endDate);

        $difference = $first->diff($second);

        /*
         * echo 'Difference: '.$difference->y.' years, '
         * .$difference->m.' months, '
         * .$difference->d.' days';
         */
        return $difference->d + 1;
    }

    function isWeekDays($date)
    {
        $result = $this->con->query("SELECT * from `holiday_calender` where `Date` = '$date' AND `State` = 'weekday'");
        return $result->num_rows > 0;
    }

    /*
     * function getTotalWeekDays($startDate,$endDate){
     * $days = 0;
     * while (strtotime($startDate) <= strtotime($endDate)) {
     * if ($this->isWeekDays($startDate) === TRUE) {
     * $days ++;
     * }
     * $startDate = date('Y-m-d', strtotime($startDate . ' +1 day'));
     * }
     * return $days;
     * }
     */
}

