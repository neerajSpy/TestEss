<?php

include "config/config.php";
include "config/constant.php";
include "db_class/Attendance.php";


$action = $_POST['action'];

if (strtolower($action) == 'punch_in') {
    if (! checkMandatoryParameter(array(
        'action','user_id','in_location','in_address','attendance','punch_status','punch_in'
    ))) {
        
        $userId = $_POST['user_id'];
        $location = $_POST['in_location'];
        $address = $_POST['in_address'];
        $attendance = $_POST['attendance'];
        $status = $_POST['punch_status'];
        $punchIn = $_POST['punch_in'];
        $date_parts = preg_split('/\s+/', $punchIn);
        $date = $date_parts[0];
        
        $db = new Attendance();
       $result = $db->punchIn($userId, $date, $punchIn, $attendance, $address, $location, $status);
        
        $response = array();
        if ($result == EXIST) {
            $response['error'] = TRUE;
            $response['message'] = "Attendance already exist";
        } else if ($result == QUERY_PROBLEM) {
            $response['error'] = TRUE;
            $response['message'] = "Punch has not done";
        }else if ($result == TIMESHEET_REQUIRED) {
            $response['error'] = TRUE;
            $response['message'] = "Fill your previous date timesheet.";
        }else {
            $response['error'] = FALSE;
            $response['message'] = "Successfully Saved";
            
        }
        
        echo json_encode($response);
    }
}


else if (strtolower($action) == 'punch_out') {
    if (! checkMandatoryParameter(array(
        'action','user_id','out_location','out_address','attendance','punch_status','punch_out','spent_time'
    ))) {
        
        $userId = $_POST['user_id'];
        $location = $_POST['out_location'];
        $address = $_POST['out_address'];
        $attendance = $_POST['attendance'];
        $status = $_POST['punch_status'];
        $spentTime = $_POST['spent_time'];
        $punchOut = $_POST['punch_out']; 
        $date_parts = preg_split('/\s+/', $punchOut);
        $date = $date_parts[0];
        
        $db = new Attendance();
        $result = $db->punchOut($userId, $date, $attendance, $punchOut, $spentTime, $location, $address, $status);
        
        $response = array();
         if ($result == QUERY_PROBLEM) {
            $response['error'] = TRUE;
            $response['message'] = "Punch has not done";
        }else {
            $response['error'] = FALSE;
            $response['message'] = "Successfully Saved";
            
        }
        
        echo json_encode($response);
    }
}


else if (strtolower($action) == 'manual_attendance') {
    if (! checkMandatoryParameter(array(
        'action','user_id','date','punch_out','attendance_duration','attendance','punch_in','remark'
    ))) {
        
        $userId = $_POST['user_id'];
        $date = $_POST['date'];
        $punchIn = $_POST['punch_in'];
        $punchOut = $_POST['punch_out'];
        $attendance = $_POST['attendance'];
        $attendanceDuration = $_POST['attendance_duration'];
        $remark = $_POST['remark'];
        
        $db = new Attendance();
        $result = $db->manualPunchIn($userId, $date, $punchIn, $punchOut, $attendance, $attendanceDuration, $remark);
        
        $response = array();
        if ($result == EXIST) {
            $response['error'] = TRUE;
            $response['message'] = "You have exceed time limit of manual punch in.";
        } else if ($result == QUERY_PROBLEM) {
            $response['error'] = TRUE;
            $response['message'] = "Attendance has not saved";
        }else if ($result == WEEKEND_HOLIDAY) {
            $response['error'] = TRUE;
            $response['message'] = $date.' is not weekday';
        }else {
            $response['error'] = FALSE;
            $response['message'] = "Attendance request successfully saved.";
            
            include "db_class/SendNotification.php";
            $notificationDb = new SendNotification();
            
            include_once 'db_class/User.php';
            $userDb = new User();
            $userName = $userDb->getNameByUserId($userId);
            
            $notificationArr = array(
                "date"=>$date,"user_name"=>$userName,"remark"=>$remark
            );
            
            $notificationDb->sendNotificationToAdmin($userId,'Attendance Request', $notificationArr);
            
        }
        
        echo json_encode($response);
    }
}




function checkMandatoryParameter($requiredFields)
{
    $error = FALSE;
    $errorField = "";
    
    foreach ($requiredFields as $requiredField) {
        if (strlen(trim($_POST[$requiredField])) < 1) {
            $errorField .= $requiredField . ", ";
            $error = TRUE;
        }
    }
    
    $response = array();
    if ($error) {
        $response['error'] = TRUE;
        $response['message'] = "missing required fields " . $errorField;
        
        echo json_encode($response);
    }
    
    return $error;
}

?>