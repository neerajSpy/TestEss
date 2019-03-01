<?php
/* error_reporting(E_ALL);
 ini_set('display_errors', 1); */

include_once 'config/constant.php';
include_once 'db_class/GuesthouseBooking.php';

$action = $_POST['action'];

if (strtolower($action) == 'booking_request') {
    if (! checkMandatoryParameter(array('action','created_by_id','check_in','check_out','paid_via_id'))) {
        
        $createdById = $_POST['created_by_id'];
        $checkIn = $_POST['check_in'];
        $checkOut = $_POST['check_out'];
        $paidViaId = $_POST['paid_via_id'];
        
        
        $db = new GuesthouseBooking();
        $response = array();
        $result = $db->bookingRequest($createdById, $checkIn, $checkOut, $paidViaId);
        if ($result == EXIST) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Guesthouse full";
        }else if ($result == QUERY_PROBLEM){
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Problem";
        }else{
            $response['id'] = $result;
            $response['error'] = FALSE;
            $response['message'] = "Booking request successfully saved";
        }
        echo json_encode($response);
    }
}

else if (strtolower($action) == 'self_booking') {
    if (! checkMandatoryParameter(array('action','created_by_id'))) {
        
        $createdById = $_POST['created_by_id'];
        
        $db = new GuesthouseBooking();
        $response = array();
        $response = $db->fetchSelfBooking($createdById);
        echo json_encode($response);
    }
}

else if (strtolower($action) == 'assign_room') {
    if (! checkMandatoryParameter(array('action','id','modified_by_id','room_id','paid_via_id'))) {
        
        $bookingId = $_POST['id'];
        $modifiedById = $_POST['modified_by_id'];
        $roomId = $_POST['room_id'];
        $paidViaId = $_POST['paid_via_id'];
        
        
        $db = new GuesthouseBooking();
        $response = array();
        $response = $db->acceptRequestBooking($bookingId, $roomId, $modifiedById);
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