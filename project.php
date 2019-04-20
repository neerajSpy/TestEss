<?php

include "config/constant.php";
include "db_class/Project.php";
include "db_class/SendNotification.php";
include "db_class/SendMail.php";

$action = $_POST['action'];

if (strtolower($action) == 'submit') {
    if (!checkMandatoryParameter(array('action'))) {
        $projectJson = json_decode($_POST['project_json']);
        $db = new Project();
        $result = $db->insertTempProject($projectJson);
        // echo "result ".$result."\n";
        $response = array();
        if ($result == EXIST) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Project already exist";

        } else if ($result == QUERY_PROBLEM) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Project not saved";
        } else {
            $response['id'] = $result;
            $response['error'] = FALSE;
            $response['message'] = "Successfully project Saved";

            $notificationDb = new SendNotification();
            $notificationArr = array("project_name" => $projectJson->project_name);
            $notificationDb->sendNotificationToAdmin($projectJson->created_by_id, 'Request Project', $notificationArr);

            $sendMailObj = new SendMail();
            $sendMailObj->submitProject($projectJson);

        }

        echo json_encode($response);
    }
} else if (strtolower($action) == 'update') {
    if (!checkMandatoryParameter(array('action'))) {
        $projectJson = json_decode($_POST['project_json']);
        $db = new Project();
        $result = $db->updateTempProject($projectJson);
        // echo "result ".$result."\n";
        $response = array();

        if ($result == EXIST) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Project already exist";

        } else if ($result == QUERY_PROBLEM) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Project not saved";
        } else {
            $response['id'] = $result;
            $response['error'] = FALSE;
            $response['message'] = "Successfully project Saved";

            $notificationDb = new SendNotification();
            $notificationArr = array("project_name" => $projectJson->project_name);
            $notificationDb->sendNotificationToAdmin($projectJson->created_by_id, 'Request Project', $notificationArr);
            $sendMailObj = new SendMail();
            $sendMailObj->submitProject($projectJson);
        }

        echo json_encode($response);
    }
} else if (strtolower($action) == 'add') {
    if (!checkMandatoryParameter(array('action'))) {
        $projectJson = json_decode($_POST['project_json']);
        $db = new Project();
        $result = $db->insertProject($projectJson);
        // echo "result ".$result."\n";
        $response = array();

        if ($result == EXIST) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Project already exist";

        } else if ($result == QUERY_PROBLEM) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Project not saved";
        } else {
            $response['id'] = $result;
            $response['error'] = FALSE;
            $response['message'] = "Successfully project Saved";
        }

        echo json_encode($response);
    }
} else if (strtolower($action) == 'approve') {
    if (!checkMandatoryParameter(array('action'))) {
        $projectJson = json_decode($_POST['project_json']);
        $db = new Project();
        $result = $db->approveProject($projectJson);
        // echo "result ".$result."\n";
        $response = array();

        if ($result == EXIST) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Project already exist";

        } else if ($result == QUERY_PROBLEM) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Project not saved";
        } else {
            $response['id'] = $result;
            $response['error'] = FALSE;
            $response['message'] = "Successfully project Saved";

            $notificationDb = new SendNotification();
            $notificationArr = array("project_name" => $projectJson->project_name);
            $notificationDb->sendNotificationToUser($projectJson->modified_by_id, $projectJson->created_by_id, 'Assign Project', $notificationArr);

            $sendMailObj = new SendMail();
            $sendMailObj->approveProject($projectJson, $result);
        }

        echo json_encode($response);
    }
} else if (strtolower($action) == 'fetch_project_activity') {
    if (!checkMandatoryParameter(array('action'))) {

        $projectId = $_POST['project_id'];
        $projectTypeId = $_POST['project_type_id'];
        $billingType = $_POST['billing_type'];

        $db = new Project();
        $response = $db->fetchActivityType($projectId, $projectTypeId, $billingType);
        echo json_encode($response);
    }

} else if (strtolower($action) == 'fetch_project') {
    if (!checkMandatoryParameter(array('action'))) {

        $searchText = $_POST['search_text'];
        $pageNumber = $_POST['page_number'];
        $db = new Project();
        
        $response = $db->fetchProject($pageNumber,$searchText);
        echo json_encode($response);
    }

}else if (strtolower($action) == 'assign_project_activity') {
    if (!checkMandatoryParameter(array('action'))) {

        $projectId = $_POST['project_id'];
        $projectTypeId = $_POST['project_type_id'];
        $createdById = $_POST['created_by_id'];
        $activityJson = json_decode($_POST['activity_json']);
        $userId = $_POST['user_id'];

        $db = new Project();
        $result = $db->insertProjectActivity($projectId,$projectTypeId,$activityJson,$userId,$createdById);

        $response = array();
        if ($result){
            $response['error'] = FALSE;
            $response['message'] = "Successfully assigned to user";
        }else{
            $response['error'] = TRUE;
            $response['message'] = "Project not saved";
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