<?php
/* error_reporting(E_ALL);
ini_set('display_errors', 1);*/

include_once 'config/constant.php';
include_once 'db_class/Leave.php';

$action = $_POST['action'];

if (strtolower($action) == 'leave_balance') {
    if (! checkMandatoryParameter(array('action','user_id','leave_type'))) {

        $userId = $_POST['user_id'];
        $leaveType = $_POST['leave_type'];
        $daysValue = $_POST['day_value'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];

        $db = new Leave();
        $response = array();
        $response = $db->fetchLeaveBalance($userId, $leaveType, $startDate, $endDate, $daysValue);
        echo json_encode($response);
    }
}

else if (strtolower($action) == 'leave_request') {
    if (! checkMandatoryParameter(array('action','leave_json'))) {
        
        $leaveJson = json_decode($_POST['leave_json']);
        $db = new Leave();
        $response = array();
        $result = $db->leaveRequest($leaveJson);
        
     
        $response = array();
        if ($result == EXIST) {
            $response['error'] = TRUE;
            $response['message'] = "Duplicate leave";
        } else if ($result == QUERY_PROBLEM || $result == 0) {
            $response['error'] = TRUE;
            $response['message'] = "Leave request not saved";
        }else if ($result == INSUFFICIENT_LEAVE ) {
            $response['error'] = TRUE;
            $response['message'] = "Insufficient leave";
        }else {
            $response['error'] = FALSE;
            $response['message'] = "Successfully Saved";
            
            include "db_class/SendNotification.php";
            $notificationDb = new SendNotification();
            $notificationArr = array(
                "leave_type" => $leaveJson->leave_type
                
            );
            $notificationDb->sendNotificationToAdmin($leaveJson->user_id, 'Leave Request', $notificationArr);
        }
        
        echo json_encode($response);
    }
}

else if (strtolower($action) == 'assign_leave') {
    if (! checkMandatoryParameter(array('action','leave_json'))) {
        
        $leaveJson = json_decode($_POST['leave_json']);
        $db = new Leave();
        $response = array();
        $result = $db->assignLeave($leaveJson);
        
        
        $response = array();
        if ($result == EXIST) {
            $response['error'] = TRUE;
            $response['message'] = "Duplicate leave";
        } else if ($result == QUERY_PROBLEM || $result == 0) {
            $response['error'] = TRUE;
            $response['message'] = "Leave request not saved";
        }else if ($result == INSUFFICIENT_LEAVE ) {
            $response['error'] = TRUE;
            $response['message'] = "Insufficient leave";
        }else {
            $response['error'] = FALSE;
            $response['message'] = "Successfully Saved";
            
            include "db_class/SendNotification.php";
            $notificationDb = new SendNotification();
            $notificationArr = array(
                "leave_type" => $leaveJson->leave_type
                
            );
            $notificationDb->sendNotificationToUser($leaveJson->user_id, $leaveJson->created_by_id,'Assign Leave', $notificationArr);
        }
        
        echo json_encode($response);
    }
}

else if (strtolower($action) == 'approve_leave') {
    if (! checkMandatoryParameter(array('action','leave_json'))) {
        
        $leaveJson = json_decode($_POST['leave_json']);
        $db = new Leave();
        $response = array();
        $result = $db->assignLeave($leaveJson);
        
        
        $response = array();
        if ($result == EXIST) {
            $response['error'] = TRUE;
            $response['message'] = "Duplicate leave";
        } else if ($result == QUERY_PROBLEM || $result == 0) {
            $response['error'] = TRUE;
            $response['message'] = "Leave request not saved";
        }else if ($result == INSUFFICIENT_LEAVE ) {
            $response['error'] = TRUE;
            $response['message'] = "Insufficient leave";
        }else {
            $response['error'] = FALSE;
            $response['message'] = "Successfully Saved";
            
            include "db_class/SendNotification.php";
            $notificationDb = new SendNotification();
            $notificationArr = array(
                "leave_type" => $leaveJson->leave_type
                
            );
            $notificationDb->sendNotificationToUser($leaveJson->user_id, $leaveJson->created_by_id,'Assign Leave', $notificationArr);
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