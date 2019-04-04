<?php

// namespace db_opertion;
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
class TripBookings
{

    private $date;

    private $con;

    private $tripStatusArray;

    private $bookingStatusArray;

    private $bookingBasePath;
    private $bookingDirectoryPath;

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
        $this->bookingDirectoryPath = "/doc/trip_booking/";
        $this->bookingBasePath = "http://ess.technitab.in".$this->bookingDirectoryPath;
        $this->active = IS_ACTIVE;
        $this->deactive = DEACTIVE;

        $this->tripStatusArray = array(
            'on_going' => "ongoing",
            'initiated' => "initiated"
        );
        $this->bookingStatusArray = array(
            'booking_requested' => "Booking Requested",
            'booking_done' => "Booking Done",
            'payment_requested' => "Payment Requested",
            "payment_done" => "Payment Done",
            'cancel_request' => "Cancel Request",
            'cancel_done' => "Cancel Done"
        );
    }

    
    function bookingRequest($tripBookingJson){
        $createdById = $tripBookingJson->created_by_id;
        $tripId = $tripBookingJson->trip_id;
        $travelType = $tripBookingJson->travel_type;
        $userBookingMode = $tripBookingJson->user_booking_mode;
        $userCityArea = $tripBookingJson->user_city_area;
        $userCheckIn = $tripBookingJson->user_check_in;
        $userCheckOut = $tripBookingJson->user_check_out;
        $userRoom = $tripBookingJson->user_room;
        $userVendor = $tripBookingJson->user_vendor;
        $userVendorId = $tripBookingJson->user_vendor_id;
        $userRate = $tripBookingJson->user_rate;
        $userTotalAmount = $tripBookingJson->user_total_amount;
        $userInstruction = $tripBookingJson->user_instruction;
        $userSource = $tripBookingJson->user_source;
        $userDestination = $tripBookingJson->user_destination;
        $userTravelDate = $tripBookingJson->user_travel_date;
        $tripBookingMember = $tripBookingJson->trip_booking_member;
        
        $result = $this->con->query("INSERT into `emp_booking` (`trip_id`, `travel_type`,`user_booking_mode`,`user_vendor`,
        `user_source`,`user_destination`,`user_travel_date`,`user_instruction`,`user_city_area`,`user_rate`,
        `user_check_in`,`user_check_out`,`user_vendor_id`,`user_room`,`user_total_amount`,`trip_status`,`created_by_id`,
         `created_date`) VALUES ('$tripId','$travelType','$userBookingMode','$userVendor','$userSource',
        '$userDestination','$userTravelDate','$userInstruction','$userCityArea','$userRate',
        '$userCheckIn','$userCheckOut','$userVendorId','$userRoom','$userTotalAmount','Booking Requested','$createdById','$this->date')");
        
        if ($result === TRUE) {
            $bookingId = $this->con->insert_id;
            $this->insertTripBookingMember($tripBookingMember, $bookingId, $createdById);
            return $bookingId;
        }else{
            return QUERY_PROBLEM;
        }
    }
    
    function selfBookings($fileName, $tripBookingJson)
    {
        $status = $this->bookingStatusArray['booking_done'];
        $createdById = $tripBookingJson->created_by_id;
        $createdBy = $tripBookingJson->created_by;
        $tripId = $tripBookingJson->trip_id;
        $travelType = $tripBookingJson->travel_type;
        $adminBookingMode = $tripBookingJson->admin_booking_mode;
        $adminCityArea = $tripBookingJson->admin_city_area;
        $adminCheckIn = $tripBookingJson->admin_check_in;
        $adminCheckOut = $tripBookingJson->admin_check_out;
        $adminRoom = $tripBookingJson->admin_room;
        $adminVendor = $tripBookingJson->admin_vendor;
        $adminVendorId = $tripBookingJson->admin_vendor_id;
        $rate = $tripBookingJson->rate;
        $adminTotalAmount = $tripBookingJson->admin_total_amount;
        $adminSource = $tripBookingJson->admin_source;
        $adminDestination = $tripBookingJson->admin_destination;
        $adminDepartureDateTime = $tripBookingJson->admin_departure_date_time;
        $adminArrivalDateTime = $tripBookingJson->admin_arrival_date_time;
        $taxName = $tripBookingJson->tax_name;
        $taxPercent = $tripBookingJson->tax_percent;
        $taxAmount = $tripBookingJson->tax_amount;
        $taxType = $tripBookingJson->tax_type;
        $serviceTaxName = $tripBookingJson->service_tax_name;
        $serviceTaxPercent = $tripBookingJson->service_tax_percent;
        $serviceTaxAmount = $tripBookingJson->service_tax_amount;
        $serviceTaxType = $tripBookingJson->service_tax_type;
        $totalAmount = $tripBookingJson->total_amount;
        $quantity = $tripBookingJson->quantity;
        $tripBookingMember = $tripBookingJson->trip_booking_member;

        $usageUnit = "";
        if ($adminBookingMode == 'Hotel/PG/Lodge' || $adminBookingMode == 'Guesthouse') {
            $usageUnit = "Days";
        }

        $result = $this->con->query("INSERT into `emp_booking` (`trip_id`, `travel_type`, `admin_booking_mode`, `admin_vendor`, `admin_vendor_id`, `admin_source`, `admin_destination`, `admin_departure_date_time`,
          `admin_arrival_date_time`,`admin_city_area`,`rate`,
         `admin_check_in`,`admin_check_out`,`admin_room`,`admin_total_amount`,
         `trip_status`,`quantity`,`tax_name`,`tax_amount`,`tax_percent`,
         `tax_type`,`service_tax_name`,`service_tax_amount`,`service_tax_percent`,
         `service_tax_type`,`total_amount`,`usage_unit`,`created_by_id`,
         `created_date`,`created_by`) VALUES ('$tripId','$travelType','$adminBookingMode',
         '$adminVendor','$adminVendorId','$adminSource','$adminDestination',
         '$adminDepartureDateTime','$adminArrivalDateTime','$adminCityArea','$rate',
         '$adminCheckIn','$adminCheckOut','$adminRoom','$adminTotalAmount',
         '$status','$quantity','$taxName','$taxAmount','$taxPercent',
         '$taxType','$serviceTaxName','$serviceTaxAmount','$serviceTaxPercent',
         '$serviceTaxType','$totalAmount','$usageUnit','$createdById',
         '$this->date','$createdBy')");

        if ($result === TRUE) {
            $bookingId = $this->con->insert_id;

            // $this->insertAttachment()
            $this->updateTripStatus($tripId, $createdById, $this->tripStatusArray['on_going']);
            $this->updateBookingAttachment($bookingId, $createdById, $fileName);
            $this->insertTripBookingMember($tripBookingMember, $bookingId, $createdById);
            return $bookingId;
        } else {
            return QUERY_PROBLEM;
        }
    }

    function tripBookingOnRequest($fileName, $tripBookingJson)
    {
        $status = $this->bookingStatusArray['booking_done'];
        $bookingId = $tripBookingJson->id;
        $modifiedById = $tripBookingJson->modified_by_id;
        $tripId = $tripBookingJson->trip_id;
        $adminBookingMode = $tripBookingJson->admin_booking_mode;
        $adminCityArea = $tripBookingJson->admin_city_area;
        $adminCheckIn = $tripBookingJson->admin_check_in;
        $adminCheckOut = $tripBookingJson->admin_check_out;
        $adminRoom = $tripBookingJson->admin_room;
        $adminVendor = $tripBookingJson->admin_vendor;
        $adminVendorId = $tripBookingJson->admin_vendor_id;

        $rate = $tripBookingJson->rate;
        $adminTotalAmount = $tripBookingJson->admin_total_amount;
        $adminSource = $tripBookingJson->admin_source;
        $adminDestination = $tripBookingJson->admin_destination;
        $adminDepartureDateTime = $tripBookingJson->admin_departure_date_time;
        $adminArrivalDateTime = $tripBookingJson->admin_arrival_date_time;
        $taxName = $tripBookingJson->tax_name;
        $taxPercent = $tripBookingJson->tax_percent;
        $taxAmount = $tripBookingJson->tax_amount;
        $taxType = $tripBookingJson->tax_type;
        $serviceTaxName = $tripBookingJson->service_tax_name;
        $serviceTaxPercent = $tripBookingJson->service_tax_percent;
        $serviceTaxAmount = $tripBookingJson->service_tax_amount;
        $serviceTaxType = $tripBookingJson->service_tax_type;
        $totalAmount = $tripBookingJson->total_amount;
        $quantity = $tripBookingJson->quantity;
        $createdBy = $tripBookingJson->created_by;

        $usageUnit = "";
        if ($adminBookingMode == 'Hotel/PG/Lodge' || $adminBookingMode == 'Guesthouse') {
            $usageUnit = "Days";
        }

        $result = $this->con->query("UPDATE `emp_booking` set `admin_booking_mode` = '$adminBookingMode', 
        `admin_vendor` = '$adminVendor',`admin_vendor_id` = '$adminVendorId',`admin_source`= '$adminSource',
        `admin_destination` = '$adminDestination',`admin_departure_date_time` = '$adminDepartureDateTime',
        `admin_arrival_date_time`='$adminArrivalDateTime',`admin_city_area`= '$adminCityArea',`rate`='$rate',
        `admin_check_in`='$adminCheckIn',`admin_check_out`='$adminCheckOut',`admin_room`='$adminRoom',
        `admin_total_amount`='$adminTotalAmount',`trip_status`='$status',`quantity`='$quantity',
        `tax_name`='$taxName',`tax_amount`='$taxAmount',`tax_percent`='$taxPercent',`tax_type`='$taxType',
        `service_tax_name`='$serviceTaxName',`service_tax_amount`='$serviceTaxAmount',
        `service_tax_percent`='$serviceTaxPercent',`service_tax_type`='$serviceTaxType',
        `total_amount`='$totalAmount',`usage_unit`='$usageUnit',`modified_by_id`='$modifiedById',
        `modified_date`='$this->date',`created_by` = '$createdBy' WHERE `id` = '$bookingId'");

        if ($result === TRUE) {
            $this->updateBookingAttachment($bookingId, $modifiedById, $fileName);
            $this->updateTripStatus($tripId, $modifiedById, $this->tripStatusArray['on_going']);
        }
    }

    
    function paymentOnBooking($file,$paymentJson){
        $bookingId = $paymentJson->booking_id;
        $paymentMode = $paymentJson->payment_mode;
        $paidBy = $paymentJson->paid_by;
        $billDate = $paymentJson->bill_date;
        $paymentTerm = $paymentJson->payment_term;
        $paymentTermLabel = $paymentJson->payment_term_label;
        $paymentDate = $paymentJson->payment_date;
        $dueDate = $paymentJson->due_date;
        $paidAmount = $paymentJson->paid_amount;
        $referenceNumber = $paymentJson->reference_number;
        $notes = $paymentJson->notes;
        $createdById = $paymentJson->created_by_id;
        
        $result = $this->con->query("INSERT into `booking_payment` (`booking_id`,`payment_mode`,`paid_by`,`payment_date`,
        `bill_date`,`due_date`,`paid_amount`,`reference_number`,`notes`,`created_by_id`,`created_date`,`status`) VALUES 
        ('$bookingId','$paymentMode','$paidBy','$paymentDate','$billDate','$dueDate','$paidAmount','$referenceNumber',
        '$notes','$createdById','$this->date','Done')");
        
        
        if ($result === TRUE) {
            $paymentId = $this->con->insert_id;
           $this->updateBookingStatus($bookingId, $createdById,$this->bookingStatusArray['payment_done']);
           $this->updatePaymentAttachment($paymentId, $createdById, $file);
           return $paymentId; 
        }else{
            return QUERY_PROBLEM;
        }
    }
    
    function updateBookingAttachment($bookingId, $userId, $file)
    {
        // echo "attachment".$tecEntryId." ".$tecId." ".$entryCateory;
        if (isset($_FILES[$file]["type"])) {
            if (($_FILES[$file]["type"] == "application/pdf") && ($_FILES[$file]["size"] < 2097152)) {
                
                $fileName = "";
                $imagePath = "";
                
                $sourcePath = $_FILES[$file]['tmp_name'];
                
                $extension = $this->getFileExtension($_FILES[$file]["name"]);
                $fileName = $userId. '_' . $bookingId ."_" .$this->getMiliSecond()."_". rand().  '.' . $extension;
                
                $targetPath = $_SERVER['DOCUMENT_ROOT'] .$this->bookingDirectoryPath . $fileName;
                move_uploaded_file($sourcePath, $targetPath);
                $imagePath = $this->bookingBasePath . $fileName;
                // echo "image ".$imagePath;
                $this->con->query("UPDATE `emp_booking` set `admin_booking_attachment`= '$imagePath' where `id` = '$bookingId'");
            }
        }
    }
    
    function updatePaymentAttachment($paymentId, $userId, $file)
    {
        // echo "attachment".$tecEntryId." ".$tecId." ".$entryCateory;
        if (isset($_FILES[$file]["type"])) {
            if (($_FILES[$file]["type"] == "application/pdf") && ($_FILES[$file]["size"] < 2097152)) {
                
                $fileName = "";
                $imagePath = "";
                
                $sourcePath = $_FILES[$file]['tmp_name'];
                
                $extension = $this->getFileExtension($_FILES[$file]["name"]);
                $fileName = $userId. '_' . $paymentId ."_" .$this->getMiliSecond()."_". rand().  '.' . $extension;
                
                $targetPath = $_SERVER['DOCUMENT_ROOT'] .$this->bookingDirectoryPath . $fileName;
                move_uploaded_file($sourcePath, $targetPath);
                $imagePath = $this->bookingBasePath . $fileName;
                $result = $this->con->query("UPDATE `booking_payment` set `bill_attachment`= '$imagePath' where `id` = '$paymentId'");
                if ($result === TRUE) {
                    return $this->con->affected_rows;
                }else{
                    return QUERY_PROBLEM;
                }
                
            }
            return EXCEED_FILE_SIZE;
        }
        return INVALID_FILE;
    }
    
    
    function fetchTripBookingBasedOnDate($tripId,$claimEndDate){
        $response = array();
        $result = $this->con->query("SELECT eb.*, bp.`id` as payment_id, bp.`payment_date`,bp.`bill_attachment` from `emp_booking` eb left JOIN
        `booking_payment` bp ON eb.`id` = bp.`booking_id` AND  WHERE eb.`trip_id` = '$tripId' AND eb.`tec_id` = '0' AND eb.`is_active` = '$this->active' ORDER BY
        `created_date` ASC");
        
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if (strtolower(trim($row['trip_status'])) != strtolower('Booking Requested')) {
                    array_push($response,$this->getBookingArray($row));
                }
            }
        }
        return $response;
        
    }
    
    function fetchTripBookingForTec($tripId)
    {
        $response = array();
        $result = $this->con->query("SELECT eb.*, bp.`id` as payment_id,bp.`bill_attachment` from `emp_booking` eb JOIN 
        `booking_payment` bp ON eb.`id` = bp.`booking_id` WHERE eb.`trip_id` = '$tripId' AND eb.`tec_id` = '0' AND eb.`is_active` = '$this->active' ORDER BY 
        `created_date` ASC");

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if (strtolower(trim($row['trip_status'])) != strtolower('Booking Requested')) {
                    array_push($response,$this->getBookingArray($row));
                }
            }
        }
        return $response;
    }
    
    function getBookingDetailById($id){
        $bookingArray = array();
        $result = $this->con->query("SELECT * from `emp_booking` where `id` = '$id'");
        if($result->num_rows >0){
            $bookingArray = $result->fetch_assoc();
            
        }
        return $bookingArray;
    }
    
    function getBookingArray($row)
    {
        $paymentId = 0;
        $paymentDate = "";
        if ($row['payment_id'] != NULL) {
            $paymentId = $row['payment_id'];
            $paymentDate = $row['payment_date'];
        }
        
        $billAttachment = "";
        if ($row['bill_attachment'] != NULL) {
            $billAttachment = $row['bill_attachment'];
        }

        $response = array(
            "id" => $row['id'],
            "payment_id" => $paymentId,
            "payment_date"=>$paymentDate,
            "trip_id" => $row['trip_id'],
            "travel_type" => $row['travel_type'],
            "trip_status" => $row['trip_status'],
            "created_by"=>$row['created_by'],
            "admin_booking_mode" => $row['admin_booking_mode'],
            "admin_city_area" => $row['admin_city_area'],
            "admin_vendor" => $row['admin_vendor'],
            "admin_vendor_id" => $row['admin_vendor_id'],
            "admin_source" => $row['admin_source'],
            "admin_destination" => $row['admin_destination'],
            "admin_instruction" => $row['admin_instruction'],
            "admin_check_in" => $row['admin_check_in'],
            "admin_check_out" => $row['admin_check_out'],
            "admin_room" => $row['admin_room'],
            "admin_total_amount" => $row['admin_total_amount'],
            "admin_departure_date_time" => $row['admin_departure_date_time'],
            "admin_arrival_date_time" => $row['admin_arrival_date_time'],
            "total_amount" => $row['total_amount'],
            "admin_booking_attachment" => $billAttachment
        );
        return $response;
    }

    function paymentRequest($bookingId, $createdById)
    {
        if ($this->updateBookingStatus($bookingId, $createdById, $this->bookingStatusArray['payment_requested'])) {
            return 1; // updated
        } else {
            return QUERY_PROBLEM;
        }
    }

    function insertTripBookingMember($tripBookingMember, $bookingId, $createdById)
    {
        foreach ($tripBookingMember as $value) {
            $this->con->query("INSERT INTO `emp_booking_member` 
            (`user_id`,`booking_id`,`created_by_id`,`created_date`) 
            VALUES ('$value->member_id','$bookingId','$createdById',
            '$this->date')");
        }
    }

    function updateBookingStatus($bookingId, $createdById, $status)
    {
        $this->con->query("UPDATE `emp_booking` set 
        `trip_status` = '$status',`modified_by_id` = '$createdById',
        `modified_date` = '$this->date' where `id` = '$bookingId'");

        // echo "payment request ".$this->con->affected_rows." error ".$this->con->error."\n";
        return $this->con->affected_rows > 0;
    }
    

    function getTripMemberById($bookingId)
    {
        $response = array();
        $result = $this->con->query("SELECT * from `emp_booking_member` where `id` = '$bookingId' AND `is_active` = '$this->active'");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($response, array(
                    'user_id' => $row['user_id']
                ));
            }
        }
        return $response;
    }

    function updateTripStatus($tripId, $modifiedById, $status)
    {
        $this->con->query("UPDATE `expense_trip` set `status` = 
        '$status', `modified_by_id` = '$modifiedById',`modified_date` = 
        '$this->date' WHERE `id` = '$tripId'");
    }
    
    function getMiliSecond()
    {
        return round(microtime(true) * 1000);
    }
    
    function getFileExtension($file)
    {
        $path_parts = pathinfo($file);
        // get extension
        return $path_parts['extension'];
    }
    
    function getFileName($file)
    {
        $path_parts = pathinfo($file);
        return $path_parts['filename'];
    }
    
}

