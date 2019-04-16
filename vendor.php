<?php

/* error_reporting(E_ALL);
ini_set('display_errors', 1); */

include "config/constant.php";
include "db_class/Vendor.php";
include "db_class/SendNotification.php";

$action = $_POST['action'];

if (strtolower($action) == 'submit') {
    if (! checkMandatoryParameter(array(
        'action'
    ))) {
        $vendorJson = json_decode($_POST['vendor_json']);
        $db = new Vendor();
        $result = $db->addTempVendor('bank_name_attachment', 'id_proof_attachment', 'bill_attachment', $vendorJson);
        // echo "result ".$result."\n";
        $response = array();
        if ($result == EXIST) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Vendor already exist";
        } else if ($result == QUERY_PROBLEM) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Vendor not saved";
        } else {
            $response['id'] = $result;
            $response['error'] = FALSE;
            $response['message'] = "Successfully vendor created";

            $notificationDb = new SendNotification();

            $contactName = "";
            if (strlen(trim($vendorJson->company_name)) < 1) {
                $contactName = $vendorJson->first_name . " " . $vendorJson->last_name . " - " . sprintf("%04d", $result);
            } else {
                $contactName = $vendorJson->company_name . " - " . sprintf("%04d", $result);
            }
            $notificationArr = array(
                "display_contact_name" => $contactName
            );
            $notificationDb->sendNotificationToAdmin($vendorJson->created_by_id, 'Vendor Request', $notificationArr);
        }

        echo json_encode($response);
    }
} 
else if (strtolower($action) == 'update') {
    if (! checkMandatoryParameter(array(
        'action'
    ))) {
        $vendorJson = json_decode($_POST['vendor_json']);
        $db = new Vendor();
        $result = $db->updateTempVendor('bank_name_attachment', 'id_proof_attachment', 'bill_attachment', $vendorJson);
        // echo "result ".$result."\n";
        $response = array();
        if ($result == EXIST) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Vendor already exist";
        } else if ($result == QUERY_PROBLEM) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Vendor not saved";
        } else {
            $response['id'] = $result;
            $response['error'] = FALSE;
            $response['message'] = "Successfully vendor updated";

            $notificationDb = new SendNotification();

            $contactName = "";
            if (strlen(trim($vendorJson->company_name)) < 1) {
                $contactName = $vendorJson->first_name . " " . $vendorJson->last_name . " - " . sprintf("%04d", $vendorJson->id);
            } else {
                $contactName = $vendorJson->company_name . " - " . sprintf("%04d", $vendorJson->id);
            }
            $notificationArr = array(
                "display_contact_name" => $contactName
            );
            $notificationDb->sendNotificationToAdmin($vendorJson->created_by_id, 'Vendor Request', $notificationArr);
        }

        echo json_encode($response);
    }
} 
else if (strtolower($action) == 'add') {
    if (! checkMandatoryParameter(array(
        'action'
    ))) {
        $vendorJson = json_decode($_POST['vendor_json']);
        $db = new Vendor();
        $result = $db->addMainVendor('bank_name_attachment', 'id_proof_attachment', 'bill_attachment', $vendorJson);
        // echo "result ".$result."\n";
        $response = array();
        if ($result == EXIST) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Vendor already exist";
        } else if ($result == QUERY_PROBLEM) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Vendor not saved";
        } else {
            $response['id'] = $result;
            $response['error'] = FALSE;
            $response['message'] = "Successfully vendor created";
        }

        echo json_encode($response);
    }
} 
else if (strtolower($action) == 'approve') {
    if (! checkMandatoryParameter(array('action'))) {
        $vendorJson = json_decode($_POST['vendor_json']);
        $db = new Vendor();
        $result = $db->approveVendor($vendorJson);
        $response = array();
        if ($result == EXIST) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Vendor already exist";
        } else if ($result == QUERY_PROBLEM) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Vendor not saved";
        } else {
            $response['id'] = $result;
            $response['error'] = FALSE;
            $response['message'] = "Successfully vendor created";

            $notificationDb = new SendNotification();

            $contactName = "";
            if (strlen(trim($vendorJson->company_name)) < 1) {
                $contactName = $vendorJson->first_name . " " . $vendorJson->last_name . " - " . sprintf("%04d", $result);
            } else {
                $contactName = $vendorJson->company_name . " - " . sprintf("%04d", $result);
            }
            $notificationArr = array(
                "display_contact_name" => $contactName
            );
            $notificationDb->sendNotificationToUser($vendorJson->created_by_id, $vendorJson->modified_by_id,'Approve Vendor', $notificationArr);
        }

        echo json_encode($response);
    }
}
else if (strtolower($action) == 'fetch_vendor') {
    if (! checkMandatoryParameter(array('action'))) {
        $page = $_POST['page'];
        $db = new Vendor();
        $response = $db->fetchVendor($page);
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