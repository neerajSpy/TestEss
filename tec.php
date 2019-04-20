<?php

/*
 * error_reporting(E_ALL);
 * ini_set('display_errors', 1);
 */
include "config/config.php";
include "db_class/Tec.php";

$action = $_POST['action'];

if (strtolower($action) == "delete") {
    if (!checkMandatoryParameter(array(
        'tec_entry_id',
        'user_id'
    ))
    ) {
        $tecEntryId = $_POST['tec_entry_id'];
        $userId = $_POST['user_id'];
        $db = new Tec();

        $response = array();
        $result = $db->deleteTecEntryById($tecEntryId, $userId);
        if ($result) {
            $response['error'] = FALSE;
            $response['message'] = "Succesfully Entry Deleted";
        } else {
            $response['error'] = TRUE;
            $response['message'] = "Entry not deleted";
        }
    }

    echo json_encode($response);
} else if (strtolower($action) == "update") {
    $entryJson = json_decode($_POST['tec_json']);
    $db = new Tec();
    $result = $db->updateTecEntry('file', $entryJson);
    // echo "result ".(int)$result;
    $response = array();
    if ($result) {
        $response['error'] = FALSE;
        $response['message'] = "Succesfully tec entry updated";
    } else {
        $response['error'] = TRUE;
        $response['message'] = "Tec entry not updated";
    }

    echo json_encode($response);
} else if (strtolower($action) == "submit tec") {
    $bookingJson = json_decode($_POST['booking_json']);
    $claimEndDate = $_POST['claim_end_date'];
    $tecId = $_POST['tec_id'];
    $tripId = $_POST['trip_id'];
    $userNote = $_POST['user_note'];
    $submitById = $_POST['submit_by_id'];
    $projectName = $_POST['project_name'];

    $db = new Tec();
    $result = $db->submitTec($submitById, $tecId, $claimEndDate, $userNote, $bookingJson);
    // echo "result ".(int)$result;
    $response = array();
    if ($result == QUERY_PROBLEM) {
        $response['error'] = TRUE;
        $response['message'] = "Tec status not changed";
    } else {
        $response['error'] = FALSE;
        $response['message'] = "Tec status changed";

        include "db_class/SendNotification.php";
        $notificationDb = new SendNotification();
        $notificationArr = array(
            "project_name" => $projectName,
            "trip id" => $tripId,
            "tec_id" => $tecId
        );
        $notificationDb->sendNotificationToAdmin($submitById, 'Submit Tec', $notificationArr);
    }

    echo json_encode($response);
} else if (strtolower($action) == "admin_update_tec") {
    $tecId = $_POST['tec_id'];
    $tripId = $_POST['trip_id'];
    $createdById = $_POST['created_by_id'];
    $projectName = $_POST['project_name'];
    $userId = $_POST['user_id'];
    $status = $_POST['status'];
    $remark = $_POST['remark'];

    $db = new Tec();
    $result = $db->updateTecByAdmin($userId, $tecId, $status, $remark);
    // echo "result ".(int)$result;
    $response = array();
    if ($result == QUERY_PROBLEM) {
        $response['error'] = TRUE;
        $response['message'] = "Tec status not changed";
    } else {
        $response['error'] = FALSE;
        $response['message'] = "Tec status changed";

        include "db_class/SendNotification.php";
        $notificationDb = new SendNotification();


        $tecStatus = "";
        if ($status == "draft") {
            $tecStatus = "Please recheck tec.";
        } else if ($status == "submit") {
            $tecStatus = "submit for checking";
        } else if ($status == "open") {
            $tecStatus = "tec approve";
        } else {
            $tecStatus = "tec payment done";
        }

        $notificationArr = array(
            "project_name" => $projectName,
            "trip id" => $tripId,
            "tec_id" => $tecId,
            "status" => $tecStatus
        );

        $notificationDb->sendNotificationToUser($createdById, $userId, 'Update Tec', $notificationArr);
    }

    echo json_encode($response);
} elseif (strtolower($action) == "insert") {
    $entryJson = json_decode($_POST['tec_json']);
    //if (! checkValidEntry('file', $entryJson)) {

    $db = new Tec();
    $result = $db->insertTecEntry('file', $entryJson);

    $response = array();
    if ($result) {
        $id = $db->getLastTecentryInsertId();
        $response['id'] = $id;
        $response['tec_id'] = $entryJson->tec_id;
        $response['error'] = FALSE;
        $response['message'] = "Succesfully tec entry saved";
    } else {
        $response['error'] = TRUE;
        $response['message'] = "Tec entry not updated";
    }

    echo json_encode($response);
    // }
} elseif (strtolower($action) == "insert tec") {
    $roleId = $_POST['role_id'];
    $tripId = $_POST['id'];
    $projectId = $_POST['project_id'];
    $claimStartDate = $_POST['claim_start_date'];
    $baseLocation = $_POST['base_location'];
    $siteLocation = $_POST['site_location'];
    $createdById = $_POST['created_by_id'];
    $tripBookingJson = json_decode($_POST['booking_json']);

    $db = new Tec();
    $result = $db->createTec($tripId, $createdById, $roleId, $projectId, $claimStartDate, $baseLocation, $siteLocation, $tripBookingJson);

    $response = array();
    if ($result == EXIST) {
        $response['id'] = "0";
        $response['eror'] = true;
        $response['message'] = "TCE id is already generated";
    } elseif ($result == QUERY_PROBLEM) {
        $response['id'] = "0";
        $response['error'] = TRUE;
        $response['message'] = "Tec entry not updated";
    } else {
        $response['id'] = $result;
        $response['eror'] = false;
        $response['message'] = "Successfully TCE id generated";
    }

    echo json_encode($response);
} elseif (strtolower($action) == "tec entry") {

    $tripId = $_POST['id'];
    $tecId = $_POST['tec_id'];

    $db = new Tec();
    $response = $db->fetchTecEntry($tecId, $tripId);
    // echo "result ".(int)$result;

    echo json_encode($response);
} else if (strtolower($action) == "admin_tec_entry") {
    $tripId = $_POST['id'];
    $tecId = $_POST['tec_id'];
    $date = $_POST['date'];

    $db = new Tec();
    $response = $db->fetchTecEntryOnDate($tecId, $tripId, $date);
    echo json_encode($response);
} else if (strtolower($action) == "link_booking_tec") {
    $tripBookingJson = json_decode($_POST['booking_json']);
    $tecId = $_POST['tec_id'];
    $createdById = $_POST['created_by_id'];

    $db = new Tec();
    $result = (int)$db->insertTecEntryFromBooking($tecId, $createdById, $tripBookingJson);

    $response = array();
    if ($result == EXIST) {
        $response['eror'] = true;
        $response['message'] = "TCE id is already generated";
    } elseif ($result == QUERY_PROBLEM) {
        $response['error'] = TRUE;
        $response['message'] = "Booking has not linked with tec";
    } else {
        $response['eror'] = false;
        $response['message'] = "Successfully booking link with tec";
    }

    echo json_encode($response);
} else if (strtolower($action) == "fetch_tec") {
    $page = $_POST['page'];
    $filterBy = $_POST['filter_by'];
    $searchText = $_POST['search_text'];

    $db = new Tec();
    $response = $db->fetchTec($page, $filterBy, $searchText);
    echo json_encode($response);
    
} else if (strtolower($action) == "fetch_user_tec") {
    $userId = $_POST['user_id'];
    $page = $_POST['page'];
    $filterBy = $_POST['filter_by'];
    $searchText = $_POST['search_text'];

    $db = new Tec();
    $response = $db->fetchUserTec($page,$userId, $filterBy, $searchText);
    echo json_encode($response);

} 

function checkValidEntry($file, $entryJson)
{
    $error = FALSE;

    if (!isset($_FILES[$file]["type"]) && ($entryJson->bill_amount > 100 && !($entryJson->entry_category == "Food - Boarding - Per Diem" || $entryJson->entry_category == "Local Travel - Public transport" || $entryJson->entry_category == "Fuel/Mileage Expenses - Own transport" || $entryJson->travel_mode == "Metro" || $entryJson->travel_mode == "Auto"))) {
        $error = TRUE;
    }

    $response = array();
    if ($error) {
        $response['error'] = TRUE;
        $response['message'] = 'Please attach bill';
        echo json_encode($response);
    }
    return $error;
}

function checkMandatoryParameter($requiredFields)
{
    $error = FALSE;
    $missingFiled = "";
    $response = array();

    foreach ($requiredFields as $requiredField) {
        if (strlen(trim($_POST[$requiredField])) < 1) {
            $missingFiled .= $requiredField . ", ";
            $error = TRUE;
        }
    }

    if ($error) {
        $response['error'] = TRUE;
        $response['message'] = 'Filed missing ' . $missingFiled;
        echo json_encode($response);
    }
    return $error;
}

?>