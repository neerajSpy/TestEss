<?php

//namespace db_opertion;

class GuesthouseBooking
{
    private $date;

    private $con;
    private $bookingStatusArray;
    private $roomAvailableStatusIdArray;
    private $active;
    private $deactive;


    function __construct()
    {
        include_once 'config/NewConfig.php';
        include_once 'config/constant.php';
        $db = new NewConfig();
        $this->con = $db->dbConnect();
        date_default_timezone_set('Asia/Kolkata');
        $this->date = date("Y-m-d H:i:s");
        $this->bookingDirectoryPath = "/web_service/ess_test/trip_booking/";
        $this->bookingBasePath = "http://ess.technitab.in" . $this->bookingDirectoryPath;
        $this->active = IS_ACTIVE;
        $this->deactive = DEACTIVE;


        $this->bookingStatusArray = array(
            'requested' => 1,
            'booked' => 2,
            'payment_done' => 3,
            "request_rejected" => 4,
            'cancelled' => 5,
            'leave' => 6
        );

        $this->roomAvailableStatusIdArray = array(
            'empty' => 1,
            'partial_full' => 2,
            'full' => 3,
        );
    }

    function bookingRequest($bookingJson)
    {
        $checkIn = $bookingJson->check_in;
        $checkOut = $bookingJson->check_out;
        $paidVia = $bookingJson->paid_via;
        $createdById = $bookingJson->created_by_id;
        $referenceNum = $bookingJson->reference_num;

        $result = $this->con->query("INSERT INTO `guest_bookings` (`check_in`,`check_out`,`paid_via`,`reference_num`,`booking_status_id`,`created_by_id`,`created_date`) 
            VALUES ('$checkIn','$checkOut','$paidVia','$referenceNum','".$this->bookingStatusArray['booked']."','$createdById','$this->date')");

        if ($result === TRUE) {
            return $this->con->insert_id;
        }
        return QUERY_PROBLEM;

        return EXIST;

    }

    function acceptRequestBooking($bookingId, $roomId, $userId)
    {
        if ($this->updateBookingStatus($bookingId, $userId, $this->bookingStatusArray['booked'])) {
            return TRUE;
        }
        return FALSE;
    }

    function fetchRequestBooking()
    {
        return $this->fetchBooking(NULL, $this->bookingStatusArray['requested']);
    }

    function fetchSelfBooking($userId)
    {
        return $this->fetchBooking($userId, NULL);
    }

    function fetchBooking($userId = NULL, $statusId = NULL)
    {
        $response = array();

        $sqlQuery = "";

        if ($userId != NULL && $statusId == NULL) {
            $sqlQuery = "SELECT gb.*, u.`name` from `guest_bookings` as gb join `user` as u on gb.`created_by_id` = u.`id` where gb.`created_by_id` = '$userId'";
        } else if ($userId == NULL && $statusId != NULL) {
            $sqlQuery = "SELECT gb.*, u.`name` from `guest_bookings` as gb join `user` as u on gb.`created_by_id` = u.`id` where gb.`booking_status_id` = '$statusId'";
        } else if ($userId != NULL && $statusId != NULL) {
            $sqlQuery = "SELECT gb.*, u.`name` from `guest_bookings` as gb join `user` as u on gb.`created_by_id` = u.`id` where gb.`created_by_id` = '$userId' AND gb.`booking_status_id` = '$statusId'";
        } else {
            $sqlQuery = "SELECT gb.*, u.`name` from `guest_bookings` as gb join `user` as u on gb.`created_by_id` = u.`id` where gb.`created_by_id` = '$userId' AND gb.`booking_status_id` = '$statusId'";
        }

        $result = $this->con->query($sqlQuery);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($response, $row);
            }
        }
        return $response;
    }

    function getUsersByRoomId($roomId)
    {
        $response = array();

        $result = $this->con->query("SELECT gb.`created_by_id`, u.`name`, u.`temp_email` from `guest_bookings` as gb 
        join `user` as u on gb.`created_by_id` = u.`id` where gb.`id` = '$roomId' AND (`booking_status_id` == 
        '" . $this->bookingStatusArray['booked'] . "' OR `booking_status_id` == 
        '" . $this->bookingStatusArray['payment_done'] . "') ");

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($response, $row);
            }
        }
        return $response;
    }

    function isRoomEmpty($checkIn, $checkOut, $gueshouseId = NULL)
    {

        if ($gueshouseId == NULL) {
            $gueshouseId = 1;
        }

        //SELECT `id`, `guesthouse_room_id`, `check_in`, `check_out`, `paid_via_id`, `booking_status_id`, `created_by_id`,
        //`created_date`, `modified_by_id`, `modified_date`, `is_active` FROM `guest_bookings` WHERE 1


        // SELECT `id`, `guesthouse_id`, `user_stay_count`, `created_date`, `created_by_id`, `modified_date`, 
        //`modified_by_id`, `is_active` FROM `guesthouse_rooms` WHERE 1

        $result = $this->con->query("SELECT `user_stay_count`,`id` from `guesthouse_rooms` where `guesthouse_id` = '$gueshouseId' AND `available_status_id` != '" . $this->roomAvailableStatusIdArray['full'] . "' AND `is_active` = '$this->active' ");
        return $result->num_rows > 0;
    }

    function updateBookingStatus($bookingId, $roomId = NULL, $userId, $statusId)
    {
        $sqlQuery = "";
        if (NULL == $roomId) {
            $sqlQuery = "UPDATE `guest_booking` set `booking_status_id` = '$statusId', `modified_by_id` = '$this->date' 
            where `id` = '$bookingId'";
        } else {
            $sqlQuery = "UPDATE `guest_booking` set `guesthouse_room_id` = '$roomId', `booking_status_id` = 
            '$statusId', `modified_by_id` = '$this->date' where `id` = '$bookingId'";
        }
        $this->con->query($sqlQuery);
        return $this->con->affected_rows > 0;
    }
}

