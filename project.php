<?php

include "config/constant.php";
include "db_class/Project.php";
include "db_class/SendNotification.php";
include "db_class/SendMail.php";

$action = $_POST['action'];

if (strtolower($action) == 'submit' ) {
    if (!checkMandatoryParameter(array('action'))){
    $projectJson = json_decode($_POST['project_json']);
    $db = new Project();
    $result = $db->insertTempProject($projectJson);
   // echo "result ".$result."\n";
    $response = array();
    if ($result == EXIST) {
        $response['id'] = 0;
        $response['error'] = TRUE;
        $response['message'] = "Project already exist";
        
    }else if($result == QUERY_PROBLEM){
        $response['id'] = 0;
        $response['error'] = TRUE;
        $response['message'] = "Project not saved";
    }else{
        $response['id'] = $result;
        $response['error'] = FALSE;
        $response['message'] = "Successfully project Saved";
        
        $notificationDb = new SendNotification();
        $notificationArr = array("project_name"=>$projectJson->project_name);
        $notificationDb->sendNotificationToAdmin($projectJson->created_by_id,'Request Project', $notificationArr);
        
        $sendMailObj = new SendMail();
        $sendMailObj->submitProject($projectJson);
        
    }
    
    echo json_encode($response);
    }
}


else if (strtolower($action) == 'update') {
    if (!checkMandatoryParameter(array('action'))){
    $projectJson = json_decode($_POST['project_json']);
    $db = new Project();
    $result = $db->updateTempProject($projectJson);
   // echo "result ".$result."\n";
    $response = array();
   
    if ($result == EXIST) {
        $response['id'] = 0;
        $response['error'] = TRUE;
        $response['message'] = "Project already exist";
        
    }else if($result == QUERY_PROBLEM){
        $response['id'] = 0;
        $response['error'] = TRUE;
        $response['message'] = "Project not saved";
    }else{
        $response['id'] = $result;
        $response['error'] = FALSE;
        $response['message'] = "Successfully project Saved";
        
        $notificationDb = new SendNotification();
        $notificationArr = array("project_name"=>$projectJson->project_name);
        $notificationDb->sendNotificationToAdmin($projectJson->created_by_id,'Request Project', $notificationArr);
        $sendMailObj = new SendMail();
        $sendMailObj->submitProject($projectJson);
    }
    
    echo json_encode($response);
    }
}


else if (strtolower($action) == 'add') {
    if (!checkMandatoryParameter(array('action'))){
    $projectJson = json_decode($_POST['project_json']);
    $db = new Project();
    $result = $db->insertProject($projectJson);
    // echo "result ".$result."\n";
    $response = array();
    
    if ($result == EXIST) {
        $response['id'] = 0;
        $response['error'] = TRUE;
        $response['message'] = "Project already exist";
        
    }else if($result == QUERY_PROBLEM){
        $response['id'] = 0;
        $response['error'] = TRUE;
        $response['message'] = "Project not saved";
    }else{
        $response['id'] = $result;
        $response['error'] = FALSE;
        $response['message'] = "Successfully project Saved";
    }
    
    echo json_encode($response);
    }
}

else if (strtolower($action) == 'approve') {
    if (!checkMandatoryParameter(array('action'))){
    $projectJson = json_decode($_POST['project_json']);
    $db = new Project();
    $result = $db->approveProject($projectJson);
    // echo "result ".$result."\n";
    $response = array();
    
    if ($result == EXIST) {
        $response['id'] = 0;
        $response['error'] = TRUE;
        $response['message'] = "Project already exist";
        
    }else if($result == QUERY_PROBLEM){
        $response['id'] = 0;
        $response['error'] = TRUE;
        $response['message'] = "Project not saved";
    }else{
        $response['id'] = $result;
        $response['error'] = FALSE;
        $response['message'] = "Successfully project Saved";
        
        $notificationDb = new SendNotification();
        $notificationArr = array("project_name"=>$projectJson->project_name);
        $notificationDb->sendNotificationToUser($projectJson->modified_by_id, $projectJson->created_by_id,'Assign Project', $notificationArr);
        
        $sendMailObj = new SendMail();
        $sendMailObj->approveProject($projectJson, $result);
    }
    
    echo json_encode($response);
    }
}

else if (strtolower($action) == 'request project') {
    if (!checkMandatoryParameter(array('action'))){
    $projectId = $_POST['id'];
    $projectName = $_POST['project_name'];
    $userId = $_POST['user_id'];
    $db = new Project();
    $result = $db->requestProjectToAssign($projectId, $userId);
    $response = array();
    
    if($result == QUERY_PROBLEM){
        $response['id'] = 0;
        $response['error'] = TRUE;
        $response['message'] = "Project not saved";
    }else{
        $response['id'] = $result;
        $response['error'] = FALSE;
        $response['message'] = "Successfully request generated";
        
        $notificationDb = new SendNotification();
        $notificationArr = array("project_name"=>$projectName);
        $notificationDb->sendNotificationToAdmin($userId,'Request Assign Project', $notificationArr);
    }
    
    echo json_encode($response);
    }
        
}

else if (strtolower($action) == 'requested project') {
    if (!checkMandatoryParameter(array('action'))){
        
        $db = new Project();
        $response = $db->fetchRequestedProject();
        echo json_encode($response);
    }
    
}

else if (strtolower($action) == 'assign requested project') {
    if (!checkMandatoryParameter(array('action'))){
        $requestId = $_POST['id'];
        $projectId = $_POST['project_id'];
        $projectName = $_POST['project_name'];
        $projectTypeId = $_POST['project_type_id'];
        $userId = $_POST['created_by_id'];
        $modifiedById = $_POST['modified_by_id'];
        
        $db = new Project();
        $result = $db->assignUserOnRequestedProject($projectId,$requestId, $projectTypeId, $userId, $modifiedById);
        $response = array();
        
        if($result == EXIST){
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Project already assigned";
        }
        else if($result == QUERY_PROBLEM){
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Project not saved";
        }else{
            $response['id'] = $result;
            $response['error'] = FALSE;
            $response['message'] = "Successfully assigned to user";
            
            $notificationDb = new SendNotification();
            $notificationArr = array("project_name"=>$projectName);
            $notificationDb->sendNotificationToUser($userId,$modifiedById,'Assign Project', $notificationArr);
        }
        
        echo json_encode($response);
    }
    
}

else if (strtolower($action) == 'assign project') {
    if (!checkMandatoryParameter(array('action'))){
        
        $projectId = $_POST['project_id'];
        $projectName = $_POST['project_name'];
        $projectTypeId = $_POST['project_type_id'];
        $userId = $_POST['created_by_id'];
        $modifiedById = $_POST['modified_by_id'];
        
        $db = new Project();
        $result = $db->assignProjectToUser($projectId, $projectTypeId, $userId, $modifiedById);
        $response = array();
        
        if($result == EXIST){
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Project already assigned";
        }
        else if($result == QUERY_PROBLEM){
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Project not saved";
        }else{
            $response['id'] = $result;
            $response['error'] = FALSE;
            $response['message'] = "Successfully assigned to user";
            
            $notificationDb = new SendNotification();
            $notificationArr = array("project_name"=>$projectName);
            $notificationDb->sendNotificationToUser($userId,$modifiedById,'Assign Project', $notificationArr);
        }
        
        echo json_encode($response);
    }
    
}



function checkMandatoryParameter($requiredFields){
    $error = FALSE;
    $errorField = "";
    
    foreach ($requiredFields as $requiredField){
        if(strlen(trim($_POST[$requiredField])) <1){
            $errorField .= $requiredField.", ";
            $error = TRUE;
        }
    }
    
    $response = array();
    if ($error) {
        $response['error'] = TRUE;
        $response['message'] = "missing required fields ".$errorField;
        
        echo json_encode($response);
    }
    
    return $error;
}




?>