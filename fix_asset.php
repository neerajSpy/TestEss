<?php
include_once 'config/constant.php';
include_once 'db_class/SendNotification.php';

$action = $_POST['action'];



/* $headers = apache_request_headers();

foreach ($headers as $header => $value) {
echo "$header: $value <br />\n";
}
*/

if (strtolower($action) == 'asset type') {
    if (!checkMandatoryParameter(array('action','role_id'))){
        include_once 'db_class/FixAsset.php';
        
        $roleId = $_POST['role_id'];
        $relatedTable = $_POST['related_table'];
        $db = new FixAsset();
        $response = $db->getFixAssetType($roleId, $relatedTable);
        echo json_encode($response);
    }
}

else if(strtolower($action) == 'assigned assets'){
    if (!checkMandatoryParameter(array('action','user_id'))){
        include_once 'db_class/FixAsset.php';
        $userId = $_POST['user_id'];
        
        $db = new FixAsset();
        $response = $db->getAssignAssets($userId);
        echo json_encode($response);
    }
}


else if (strtolower($action) == 'request'){
    if (!checkMandatoryParameter(array('action','user_id','org_unit_asset_id','remark'))){
        include_once 'db_class/FixAsset.php';
        
        $db = new FixAsset();
        $userId = $_POST['user_id'];
        $orgUnitAssetId = $_POST['org_unit_asset_id'];
        $orgUnitAsset = $_POST['org_unit_asset'];
        $remark = $_POST['remark'];
        $result = $db->requestFixAsset($orgUnitAssetId, $remark, $userId);
        if ($result == QUERY_PROBLEM) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Something problem";
        }else{
            $response['id'] = $result;
            $response['error'] = FALSE;
            $response['message'] = "Successfully request saved";
            
            $notificationDb = new SendNotification();
            $notificationArr = array('org_unit_asset'=>$orgUnitAsset);
            $notificationDb->sendNotificationToAdmin($userId,'Fix Asset Request', $notificationArr);
            
        }
        echo json_encode($response);
    }
}


else if (strtolower($action) == 'fix assets'){
    if (!checkMandatoryParameter(array('action','org_unit_asset_id'))){
        include_once 'db_class/FixAsset.php';
        $db = new FixAsset();
        $orgUnitAssetId = $_POST['org_unit_asset_id'];
        $response = $db->getFixAssetByOrgId($orgUnitAssetId);
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