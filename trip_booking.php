<?php
/*  error_reporting(E_ALL);
 ini_set('display_errors', 1); */
include "config/constant.php";
include "db_class/SendNotification.php";

$action = $_POST['action'];

if (strtolower($action) == 'booking_request') {
    if (! checkMandatoryParameter(array('action'))) {
        $tripBookingJson = json_decode($_POST['booking_json']);

        include_once 'db_class/TripBookings.php';

        $dbBooking = new TripBookings();
        $result = $dbBooking->bookingRequest($tripBookingJson);
        $response = array();
        if ($result == QUERY_PROBLEM) {
            $response['error'] = TRUE;
            $response['message'] = "Self booking not done";
        } else {
            $response['error'] = FALSE;
            $response['message'] = "Self booking Saved";
            
            include_once 'db_class/User.php';
            $userDb = new User();
            $userName = $userDb->getNameByUserId($tripBookingJson->created_by_id);
            $notificationDb = new SendNotification();
            $notificationArr = array(
                "user_name" => $userName
            );
            $notificationDb->sendNotificationToAdmin($tripBookingJson->created_by_id, 'Booking Request', $notificationArr);
            
            include_once 'db_class/SendMail.php';
            $mailDb = new SendMail();
            $mailDb->bookingRequestMail($result, $tripBookingJson);
        }

        echo json_encode($response);
    }
}

if (strtolower($action) == 'self booking') {
    if (! checkMandatoryParameter(array(
        'action'
    ))) {

        include_once 'db_class/TripBookings.php';

        $dbBooking = new TripBookings();
        $tripBookingJson = json_decode($_POST['booking_json']);
        $result = $dbBooking->selfBookings('file', $tripBookingJson);
        $response = array();
        if ($result == QUERY_PROBLEM) {
            $response['id'] = 0;
            $response['error'] = TRUE;
            $response['message'] = "Self booking not done";
        } else {
            $response['id'] = $result;
            $response['error'] = FALSE;
            $response['message'] = "Self booking Saved";
        }

        echo json_encode($response);
    }
} 
else if (strtolower($action) == 'payment request') {
    if (! checkMandatoryParameter(array(
        'action',
        'booking_id',
        'created_by_id'
    ))) {

        include 'db_class/TripBookings.php';
        $bookingId = $_POST['booking_id'];
        $createdById = $_POST['created_by_id'];
        $db = new TripBookings();
        $result = $db->paymentRequest($bookingId, $createdById);
        $response = array();
        if ($result == QUERY_PROBLEM) {
            $response['error'] = TRUE;
            $response['message'] = "Payment Request has not done";
        } else {
            $response['error'] = FALSE;
            $response['message'] = "Payment Request Saved";

            include_once 'db_class/User.php';
            $userDb = new User();
            $userName = $userDb->getNameByUserId($createdById);
            $notificationDb = new SendNotification();
            $notificationArr = array(
                "user_name" => $userName
            );
            $notificationDb->sendNotificationToAdmin($createdById, 'Payment Request', $notificationArr);
            
            $bookingDetail = $db->getBookingDetailById($bookingId);
            
            include_once 'db_class/Vendor.php';
            $vendorDb = new Vendor();
            
            $vendorDetail = $vendorDb->getVendorDetailByBookingId($bookingId);
            
            include_once 'db_class/SendMail.php';
            $mailDb = new SendMail();
            
            $mailDb->bookingPaymentRequest($createdById,$bookingId, $bookingDetail, $vendorDetail);
           
        }

        echo json_encode($response);
    }
} 
else if (strtolower($action) == 'booking on request') {
    if (! checkMandatoryParameter(array(
        'action'
    ))) {

        include_once 'db_class/TripBookings.php';
        $dbBooking = new TripBookings();
        $tripBookingJson = json_decode($_POST['booking_json']);
        $result = $dbBooking->tripBookingOnRequest('file', $tripBookingJson);
        $response = array();
        if ($result == QUERY_PROBLEM) {
            $response['error'] = TRUE;
            $response['message'] = "Self booking not done";
        } else {
            $response['error'] = FALSE;
            $response['message'] = "Self booking Saved";

            $members = $dbBooking->getTripMemberById($tripBookingJson->id);
            // echo "members array \n";
            // print_r($members);
            // echo "\n";
            $notificationDb = new SendNotification();
            $notificationArr = array(
                'booking_mode' => $tripBookingJson->admin_booking_mode
            );

            foreach ($members as $member) {
                // echo "member ".$member['user_id']."\n";
                $notificationDb->sendNotificationToUser($member['user_id'], $tripBookingJson->modified_by_id, 'Booking Done', $notificationArr);
            }
        }

        echo json_encode($response);
    }
} 
else if (strtolower($action) == 'trip bookings') {
    if (! checkMandatoryParameter(array(
        'action',
        trip_id
    ))) {

        $tripId = $_POST['trip_id'];
        include_once 'db_class/TripBookings.php';
        $dbBooking = new TripBookings();
        $response = $dbBooking->fetchTripBookingForTec($tripId);
        echo json_encode($response);
    }
} 
else if (strtolower($action) == 'booking payment') {
    if (! checkMandatoryParameter(array(
        'action'
    ))) {

        $paymentJson = json_decode($_POST['payment_json']);
        include_once 'db_class/TripBookings.php';
        $dbBooking = new TripBookings();
        $result = $dbBooking->paymentOnBooking('file', $paymentJson);
        $response = array();
        if ($result == QUERY_PROBLEM) {
            $response['error'] = TRUE;
            $response['message'] = "Booking payment has not done";
        } else {
            $response['error'] = FALSE;
            $response['message'] = "Booking payment Saved";

            include_once 'db_class/User.php';
            $userDb = new User();
            $bookingId = $paymentJson->booking_id;
            $notificationDb = new SendNotification();
            $notificationArr = array(
                "booking_id" => $bookingId
            );
            $notificationDb->sendNotificationForBooking($bookingId, $paymentJson->created_by_id, 'Payment Done', $notificationArr);
            
            $bookingDetail = $dbBooking->getBookingDetailById($bookingId);
            
            include_once 'db_class/Vendor.php';
            $vendorDb = new Vendor();
            
            $vendorDetail = $vendorDb->getVendorDetailByBookingId($bookingId);
            include_once 'db_class/SendMail.php';
            $mailDb = new SendMail();
            
            $mailDb->bookingPaymentEntryMail($bookingId, $paymentJson, $result, $bookingDetail, $vendorDetail);
        }

        echo json_encode($response);
    }
} 
else if (strtolower($action) == 'attach bill') {
    if (! checkMandatoryParameter(array(
        'action'
    ))) {

        $paymentId = $_POST['id'];
        $createdById = $_POST['created_by_id'];
        include_once 'db_class/TripBookings.php';
        $dbBooking = new TripBookings();
        $result = (int) $dbBooking->updatePaymentAttachment($paymentId, $createdById, 'file');
        $response = array();
        if ($result == QUERY_PROBLEM) {
            $response['error'] = TRUE;
            $response['message'] = "Booking payment has not done";
        } else if ($result == INVALID_FILE) {
            $response['error'] = TRUE;
            $response['message'] = "Invalid file type";
        } else if ($result == EXCEED_FILE_SIZE) {
            $response['error'] = TRUE;
            $response['message'] = "Invalid file size";
        } else {
            $response['error'] = FALSE;
            $response['message'] = "Booking payment Saved";

            include_once 'db_class/User.php';
            $userDb = new User();
            $bookingId = $paymentJson->booking_id;
            $notificationDb = new SendNotification();
            $notificationArr = array(
                "booking_id" => $bookingId
            );
            $notificationDb->sendNotificationForBooking($bookingId, $paymentJson->created_by_id, 'Payment Done', $notificationArr);
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