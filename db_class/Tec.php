<?php


/* error_reporting(E_ALL);
ini_set('display_errors', 1); */

class Tec
{

    private $date;

    private $con;
    private $active;
    private $deactive;
    private $basePath;
    private $directoryPath;
    private $tecStatusArray;

    private $intercityTravel, $lodgingHotel;


    function __construct()
    {
        include_once 'config/NewConfig.php';
        include_once 'config/constant.php';
        $db = new NewConfig();
        $this->con = $db->dbConnect();
        date_default_timezone_set('Asia/Kolkata');
        $this->date = date("Y-m-d H:i:s");
        $this->intercityTravel = 'Intercity Travel cost';
        $this->lodgingHotel = 'Lodging - Hotels';
        $this->directoryPath = "/doc/tec/";
        $this->basePath = "http://ess.technitab.in" . $this->directoryPath;
        $this->active = IS_ACTIVE;
        $this->deactive = DEACTIVE;

        $this->tecStatusArray = array("draft"=> 'draft',
            'submit'=> 'submit',
            'open' => 'open',
            'paid'=> 'paid');
    }

    function createTec($tripId, $createdById, $roleId, $projectId, $claimStartDate, $baseLocation, $siteLocation, $tripBookingJson)
    {
        if (!$this->isTecExist($createdById, $projectId, $claimStartDate)) {

            $result = $this->con->query("INSERT into `emp_main_tec` (`trip_id`,`role_id`, `project_id`,`claim_start_date`,
            `base_location`,`site_location`,`status`, `created_date`, `created_by_id`) VALUES ('$tripId',
            '$roleId','$projectId','$claimStartDate','$baseLocation','$siteLocation','draft','$this->date',
            '$createdById')");

            if ($result === TRUE) {
                $tecId = $this->con->insert_id;
                $this->insertTecEntryFromBooking($tecId, $createdById, $tripBookingJson);
                return $tecId;
            } else {
                return QUERY_PROBLEM;
            }
        } else {
            return EXIST;
        }
    }

    function fetchTecEntryOnDate($tecId, $tripId, $date)
    {
        include_once 'db_class/TripBookings.php';
        $dbBooking = new TripBookings();
        $tripBookingArray = $dbBooking->fetchTripBookingBasedOnDate($tripId, $date);
        $tecEntryArray = $this->getTecEntry($tecId);

        $response = array();
        $response['trip_bookings'] = $tripBookingArray;
        $response['tec_entries'] = $tecEntryArray;
        return $response;
    }

    function fetchTecEntry($tecId, $tripId)
    {
        include_once 'db_class/TripBookings.php';
        $dbBooking = new TripBookings();
        $tripBookingArray = $dbBooking->fetchTripBookingForTec($tripId);
        $tecEntryArray = $this->getTecEntry($tecId);

        $response = array();
        $response['trip_bookings'] = $tripBookingArray;
        $response['tec_entries'] = $tecEntryArray;
        return $response;
    }

    function insertTecEntryFromBooking($tecId, $createdById, $tripBookingJson)
    {

        $linkBookingsOnTecCount = 0;

        foreach ($tripBookingJson as $value) {

            $bookingId = $value->id;
            $paymentId = $value->payment_id;
            $travelType = $value->travel_type;
            $adminBookngMode = $value->admin_booking_mode;
            $adminCityArea = $value->admin_city_area;
            $adminVendor = $value->admin_vendor;
            $adminVendorId = $value->admin_vendor_id;
            $adminSource = $value->admin_source;
            $adminDestination = $value->admin_destination;
            $adminCheckIn = $value->admin_check_in;
            $adminCheckOut = $value->admin_check_out;
            $adminDepartureDateTime = $value->admin_departure_date_time;
            $adminArrivalDateTime = $value->admin_arrival_date_time;
            $totalAmount = $value->total_amount;
            $adminBookngAttachment = $value->admin_booking_attachment;

            $departureDate = date('Y-m-d', strtotime($adminDepartureDateTime));
            $departureTime = date('H:i:s', strtotime($adminDepartureDateTime));
            $arrivalDate = date('Y-m-d', strtotime($adminArrivalDateTime));
            $arrivalTime = date('H:i:s', strtotime($adminArrivalDateTime));


            $data = $this->getUnitPriceQtyOfBooking($bookingId, $travelType);

            $sqlQuery = "";
            if (strtolower(trim($adminBookngMode)) == 'bus' || strtolower(trim($adminBookngMode)) == 'train' || strtolower(trim($travelType)) == 'flight') {
                $sqlQuery = "INSERT into `emp_tec_entry` (`tec_id`,`booking_id`,`payment_id`,`entry_category`,`travel_mode`,`from_location`,
                               `to_location`,`deprt_date`,`deprt_time`,`arrival_date` 
                               ,`arrival_time`,`unit_price`,`total_quantitty`,`date`,
                               `paid_to`,`paid_by`,`bill_amount`,`bill_num`,`created_date`,`created_by_id`,
                               `paid_to_id`,`attachment_path`) VALUES ('$tecId','$bookingId',
                              '$paymentId','$this->intercityTravel','$adminBookngMode','$adminSource','$adminDestination',
                               '$departureDate','$departureTime','$arrivalDate','$arrivalTime','" . $data['rate'] . "',
                                '1',(SELECT `bill_date` from `booking_payment` where `id` = '$paymentId'),'$adminVendor',
                                (SELECT `paid_by` from `booking_payment` where `id` = '$paymentId'),'" . $totalAmount / $data['member'] . "',
                                (SELECT `reference_number` from `booking_payment` where `id` = '$paymentId'),(SELECT `created_date` from `booking_payment` where `id` = '$paymentId'),
                                (SELECT `created_by_id` from `booking_payment` where `id` = '$paymentId'),'$adminVendorId',
                                '$adminBookngAttachment')";
            } else {
                $sqlQuery = "INSERT into `emp_tec_entry` (`tec_id`,`booking_id`,`payment_id`,`entry_category`,`travel_mode`,
                               `location`,`deprt_date`,`arrival_date`,`unit_price`,`total_quantitty`,`date`,
                               `paid_to`,`paid_by`,`bill_amount`,`bill_num`,`created_date`,`created_by_id`,
                               `paid_to_id`,`attachment_path`) VALUES ('$tecId','$bookingId',
                              '$paymentId','$this->lodgingHotel','$adminBookngMode','$adminCityArea',
                               '$adminCheckIn','$adminCheckOut','" . $data['rate'] . "','" . $data['quantity'] . "',
                               (SELECT `bill_date` from `booking_payment` where `id` = '$paymentId'),'$adminVendor',
                                (SELECT `paid_by` from `booking_payment` where `id` = '$paymentId'),'" . $totalAmount / $data['member'] . "',
                                (SELECT `reference_number` from `booking_payment` where `id` = '$paymentId'),(SELECT `created_date` from `booking_payment` where `id` = '$paymentId'),
                                (SELECT `created_by_id` from `booking_payment` where `id` = '$paymentId'),'$adminVendorId',
                                '$adminBookngAttachment')";
            }

            $result = $this->con->query($sqlQuery);
            if ($result === TRUE) {
                $this->insertTecIdIntoBooking($tecId, $bookingId);
                $linkBookingsOnTecCount++;
            }
        }

        if ($linkBookingsOnTecCount > 0) {
            return 1;
        } else {
            return QUERY_PROBLEM;
        }
    }


    function insertTecIdIntoBooking($tecId, $bookingId)
    {
        $this->con->query("UPDATE `emp_booking` set `tec_id` = '$tecId' where `id` = '$bookingId'");
    }

    function getUnitPriceQtyOfBooking($bookingId, $travelType)
    {
        $data = array();

        $result = $this->con->query("SELECT * from `emp_booking_member` where `booking_id` = '$bookingId' AND `is_active` = '0'");
        $memberCount = $result->num_rows;

        $res = $this->con->query("SELECT * from `emp_booking` where `id` = '$bookingId'");
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $data['quantity'] = (int)($row['quantity'] / $memberCount);
            $data['rate'] = $row['rate'] / $memberCount;
            $data['member'] = $memberCount;
        }

        return $data;
    }

    function insertTecEntry($tecFile, $entryJson)
    {
        $tec_id = $entryJson->tec_id;
        $from_location = $entryJson->from_location;
        $to_location = $entryJson->to_location;
        $kilo_meter = $entryJson->kilo_meter;
        $mileage = $entryJson->mileage;
        $departure_date = $entryJson->deprt_date;
        $departure_time = $entryJson->deprt_time;
        $arrival_time = $entryJson->arrival_time;
        $arrival_date = $entryJson->arrival_date;
        $entry_category = $entryJson->entry_category;
        $travel_mode = $entryJson->travel_mode;
        $location = $entryJson->location;
        $unit_price = $entryJson->unit_price;
        $total_quantitty = $entryJson->total_quantitty;
        $description = $entryJson->description;
        $date = $entryJson->date;
        $paid_to = $entryJson->paid_to;
        $paid_by = $entryJson->paid_by;
        $gstin = $entryJson->gstin;
        $bill_amount = $entryJson->bill_amount;
        $bill_num = $entryJson->bill_num;
        $created_by_id = $entryJson->created_by_id;
        $is_metro = $entryJson->is_metro;
        $paid_to_id = $entryJson->paid_to_id;
        $isBillable = $entryJson->is_billable;

        $result = $this->con->query("INSERT into `emp_tec_entry` (`tec_id`,`entry_category`,`travel_mode`,
                               `from_location`,`to_location`,`kilo_meter`,`mileage`,`deprt_date`,
                               `deprt_time`,`arrival_date`,`arrival_time`,`location`,`unit_price`,
                               `total_quantitty`,`description`,`date`,`paid_to`,`paid_by`,`gstin`,
                               `bill_amount`,`bill_num`,`created_date`,`created_by_id`,
                               `is_billable`,`is_metro`, `paid_to_id`) VALUES 
                               ('$tec_id','$entry_category','$travel_mode','$from_location','$to_location',
                                '$kilo_meter','$mileage','$departure_date','$departure_time','$arrival_date',
                                '$arrival_time','$location','$unit_price','$total_quantitty',
                                 '$description','$date','$paid_to','$paid_by','$gstin','$bill_amount',
                                 '$bill_num','$this->date','$created_by_id','$isBillable',
                                 '$is_metro','$paid_to_id')");

        if ($result === TRUE) {
            $tecEntryId = $this->con->insert_id;
            $this->updateAttachment($tecFile, $tecEntryId, $tec_id, $entry_category);
            $prevBillAmount = $this->getTecAmount($tec_id);
            $this->insertTecAmount($tec_id, $created_by_id, $bill_amount, $prevBillAmount);
            $this->createTecEntryLog($tecEntryId, $created_by_id, 'Created', json_decode($entryJson));
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function updateAttachment($tecFile, $tecEntryId, $tecId, $entryCateory)
    {

        if (isset($_FILES[$tecFile]["type"])) {
            if (($_FILES[$tecFile]["type"] == "application/pdf") && ($_FILES[$tecFile]["size"] < 2097152)) {

                $sourcePath = $_FILES[$tecFile]['tmp_name'];

                $extension = $this->getFileExtension($_FILES[$tecFile]["name"]);
                $fileName = $tecId . '_' . $tecEntryId . '_' . $entryCateory . '.' . $extension;

                $targetPath = $_SERVER['DOCUMENT_ROOT'] . $this->directoryPath . $fileName;
                move_uploaded_file($sourcePath, $targetPath);
                $imagePath = $this->basePath . $fileName;
                // echo "image ".$imagePath;
                $this->con->query("UPDATE `emp_tec_entry` set `attachment_path`= '$imagePath' where `id` = '$tecEntryId'");
            }
        }
    }

    function updateTecEntry($tecFile, $entryJson)
    {
        $tec_id = $entryJson->tec_id;
        $entry_id = $entryJson->id;
        $from_location = $entryJson->from_location;
        $to_location = $entryJson->to_location;
        $kilo_meter = $entryJson->kilo_meter;
        $mileage = $entryJson->mileage;
        $departure_date = $entryJson->deprt_date;
        $departure_time = $entryJson->deprt_time;
        $arrival_time = $entryJson->arrival_time;
        $arrival_date = $entryJson->arrival_date;
        $entry_category = $entryJson->entry_category;
        $travel_mode = $entryJson->travel_mode;
        $location = $entryJson->location;
        $unit_price = $entryJson->unit_price;
        $total_quantitty = $entryJson->total_quantitty;
        $description = $entryJson->description;
        $date = $entryJson->date;
        $paid_to = $entryJson->paid_to;
        $paid_by = $entryJson->paid_by;
        $gstin = $entryJson->gstin;
        $bill_amount = $entryJson->bill_amount;
        $bill_num = $entryJson->bill_num;
        $modified_by_id = $entryJson->modified_by_id;
        $is_metro = $entryJson->is_metro;
        $paid_to_id = $entryJson->paid_to_id;
        $isBillable = $entryJson->is_billable;

        $prevBillAmount = $this->getPrevBillAmount($tec_id, $entry_id);
        $prevImagePath = $this->getPreviousTecPath($tec_id, $entry_id);

        if (isset($_FILES[$tecFile]["type"])) {
            if (($_FILES[$tecFile]["type"] == "application/pdf") && ($_FILES[$tecFile]["size"] < 2097152)) {

                $sourcePath = $_FILES[$tecFile]['tmp_name'];

                $extension = $this->getFileExtension($_FILES[$tecFile]["name"]);
                $fileName = $tec_id . '_' . $entry_id . '_' . $entry_category . '.' . $extension;

                $targetPath = $_SERVER['DOCUMENT_ROOT'] . $this->directoryPath . $fileName;
                move_uploaded_file($sourcePath, $targetPath);
                $imagePath = $this->basePath . $fileName;
            } else
                return FALSE;
        } else {
            $imagePath = $prevImagePath;
        }

        $result = $this->con->query("UPDATE `emp_tec_entry` set `entry_category`='$entry_category',
                               `travel_mode`='$travel_mode',`from_location`='$from_location',
                                `to_location`='$to_location',`kilo_meter`='$kilo_meter',
                                `mileage`= '$mileage',`deprt_date`='$departure_date',
                               `deprt_time`='$departure_time',`arrival_date`='$arrival_date',
                               `arrival_time`='$arrival_time',`location`='$location',
                               `unit_price`='$unit_price', `total_quantitty`='$total_quantitty',
                               `description`='$description',`date`='$date',`paid_to`='$paid_to',
                               `paid_by`='$paid_by',`gstin`='$gstin',`bill_amount`='$bill_amount',
                               `bill_num`='$bill_num',`attachment_path` = '$imagePath',
                               `modified_date` = '$this->date', 
                               `modified_by_id` = '$modified_by_id',`is_billable` = '$isBillable',
                               `is_metro` = '$is_metro', `paid_to_id` = '$paid_to_id' 
                               WHERE `tec_id` = '$tec_id' AND `id` = '$entry_id'");

        if ($result === TRUE) {
            $this->updateTecAmount($tec_id, $modified_by_id, $bill_amount, $prevBillAmount);
            $this->createTecEntryLog($entry_id, $modified_by_id, 'Modified', json_decode($entryJson));
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function getTecEntry($tecId)
    {

        //SELECT `id`, `tec_id`, `entry_category`, `travel_mode`, `deprt_date`, `deprt_time`, `arrival_date`, `arrival_time`, `location`, `from_location`, `to_location`, `kilo_meter`, `mileage`, `unit_price`, `vendor`, `total_quantitty`, `description`, `date`, `paid_to`, `paid_by`, `gstin`, `bill_amount`, `bill_num`, `attachment_path` FROM `emp_tec_entry` WHERE 1

        $entryCategoryArray = array('Intercity Travel cost', 'Food - Boarding - Per Diem', 'Local Travel - Public transport', 'Miscellaneous', 'Repairs and Maintenance', 'Intl Travel Insurance', 'Fuel/Mileage Expenses - Own transport', 'Lodging - Hotels', 'Fixed Asset', 'Minutes of Meeting', 'Service Timesheet', 'Feedback Form', 'SAT Report', 'Checklists');

        $response = array();
        $intercityTravelCostResponse = array();
        $FoodBoardingResponse = array();
        $LocalTravelResponse = array();
        $MiscellaneousResponse = array();
        $RepairsResponse = array();
        $IntlTravelInsuranceResponse = array();
        $FuelMileageResponse = array();
        $LodgingHotelsResponse = array();
        $FixedAssetResponse = array();
        $MOMResponse = array();
        $ServiceTimesheetResponse = array();
        $FeedbackResponse = array();
        $SATResponse = array();
        $ChecklistResponse = array();

        $sub_result = $this->con->query("SELECT * FROM `emp_tec_entry` WHERE `tec_id` = '$tecId' AND `is_active` = '$this->active' ORDER By `entry_category`, CONCAT(`deprt_date`,' ',`deprt_time`) ASC");
        if ($sub_result->num_rows > 0) {

            $intercityTravelCost = 0;
            $FoodBoarding = 0;
            $LocalTravel = 0;
            $Miscellaneous = 0;
            $Repairs = 0;
            $IntlTravelInsurance = 0;
            $FuelMileage = 0;
            $LodgingHotels = 0;
            $FixedAsset = 0;

            // employee Paid
            $intercityTravelCostEmp = 0;
            $FoodBoardingEmp = 0;
            $LocalTravelEmp = 0;
            $MiscellaneousEmp = 0;
            $RepairsEmp = 0;
            $IntlTravelInsuranceEmp = 0;
            $FuelMileageEmp = 0;
            $LodgingHotelsEmp = 0;
            $FixedAssetEmp = 0;

            // Account Paid
            $intercityTravelCostAC = 0;
            $FoodBoardingAC = 0;
            $LocalTravelAC = 0;
            $MiscellaneousAC = 0;
            $RepairsAC = 0;
            $IntlTravelInsuranceAC = 0;
            $FuelMileageAC = 0;
            $LodgingHotelsAC = 0;
            $FixedAssetAC = 0;

            while ($subRow = $sub_result->fetch_assoc()) {

                if (strtolower($subRow['entry_category']) == strtolower("Intercity Travel cost")) {
                    array_push($intercityTravelCostResponse, $subRow);
                    $intercityTravelCost = $intercityTravelCost + $subRow['bill_amount'];

                    if (strtolower($subRow['paid_by']) == "employee")
                        $intercityTravelCostEmp = $intercityTravelCostEmp + $subRow['bill_amount'];
                    else
                        $intercityTravelCostAC = $intercityTravelCostAC + $subRow['bill_amount'];


                } else if (strtolower($subRow['entry_category']) == strtolower("Food - Boarding - Per Diem")) {
                    array_push($FoodBoardingResponse, $subRow);
                    $FoodBoarding = $FoodBoarding + $subRow['bill_amount'];
                    if (strtolower($subRow['paid_by']) == "employee")
                        $FoodBoardingEmp = $FoodBoardingEmp + $subRow['bill_amount'];
                    else
                        $FoodBoardingAC = $FoodBoardingAC + $subRow['bill_amount'];
                } else if (strtolower($subRow['entry_category']) == strtolower("Local Travel - Public transport")) {
                    array_push($LocalTravelResponse, $subRow);
                    $LocalTravel = $LocalTravel + $subRow['bill_amount'];

                    if (strtolower($subRow['paid_by']) == "employee")
                        $LocalTravelEmp = $LocalTravelEmp + $subRow['bill_amount'];
                    else
                        $LocalTravelAC = $LocalTravelAC + $subRow['bill_amount'];
                } else if (strtolower($subRow['entry_category']) == strtolower("Miscellaneous")) {
                    array_push($MiscellaneousResponse, $subRow);
                    $Miscellaneous = $Miscellaneous + $subRow['bill_amount'];
                    if (strtolower($subRow['paid_by']) == "employee")
                        $MiscellaneousEmp = $MiscellaneousEmp + $subRow['bill_amount'];
                    else
                        $MiscellaneousAC = $MiscellaneousAC + $subRow['bill_amount'];
                } else if (strtolower($subRow['entry_category']) == strtolower("Repairs and Maintenance")) {
                    array_push($RepairsResponse, $subRow);
                    $Repairs = $Repairs + $subRow['bill_amount'];
                    if (strtolower($subRow['paid_by']) == "employee")
                        $RepairsEmp = $RepairsEmp + $subRow['bill_amount'];
                    else
                        $RepairsAC = $RepairsAC + $subRow['bill_amount'];
                } else if (strtolower($subRow['entry_category']) == strtolower("Intl Travel Insurance")) {
                    array_push($IntlTravelInsuranceResponse, $subRow);
                    $IntlTravelInsurance = $IntlTravelInsurance + $subRow['bill_amount'];
                    if (strtolower($subRow['paid_by']) == "employee")
                        $IntlTravelInsuranceEmp = $IntlTravelInsuranceEmp + $subRow['bill_amount'];
                    else
                        $IntlTravelInsuranceAC = $IntlTravelInsuranceAC + $subRow['bill_amount'];
                } else if (strtolower($subRow['entry_category']) == strtolower("Fuel/Mileage Expenses - Own transport")) {
                    array_push($FuelMileageResponse, $subRow);
                    $FuelMileage = $FuelMileage + $subRow['bill_amount'];
                    if (strtolower($subRow['paid_by']) == "employee")
                        $FuelMileageEmp = $FuelMileageEmp + $subRow['bill_amount'];
                    else
                        $FuelMileageAC = $FuelMileageAC + $subRow['bill_amount'];
                } else if (strtolower($subRow['entry_category']) == strtolower("Lodging - Hotels")) {
                    array_push($LodgingHotelsResponse, $subRow);
                    $LodgingHotels = $LodgingHotels + $subRow['bill_amount'];
                    if (strtolower($subRow['paid_by']) == "employee")
                        $LodgingHotelsEmp = $LodgingHotelsEmp + $subRow['bill_amount'];
                    else
                        $LodgingHotelsAC = $LodgingHotelsAC + $subRow['bill_amount'];
                } else if (strtolower($subRow['entry_category']) == strtolower("Fixed Asset")) {
                    array_push($FixedAssetResponse, $subRow);
                    $FixedAsset = $FixedAsset + $subRow['bill_amount'];
                    if (strtolower($subRow['paid_by']) == "employee")
                        $FixedAssetEmp = $FixedAssetEmp + $subRow['bill_amount'];
                    else
                        $FixedAssetAC = $FixedAssetAC + $subRow['bill_amount'];
                } else if (strtolower($subRow['entry_category']) == strtolower("Minutes of Meeting")) {
                    array_push($MOMResponse, $subRow);
                } else if (strtolower($subRow['entry_category']) == strtolower("Service Timesheet")) {
                    array_push($ServiceTimesheetResponse, $subRow);
                } else if (strtolower($subRow['entry_category']) == strtolower("Feedback Form")) {
                    array_push($FeedbackResponse, $subRow);
                } else if (strtolower($subRow['entry_category']) == strtolower("SAT Report")) {
                    array_push($SATResponse, $subRow);
                } else if (strtolower($subRow['entry_category']) == strtolower("Checklists")) {
                    array_push($ChecklistResponse, $subRow);
                }

            }
        }


        if (sizeof($intercityTravelCostResponse) > 0) {
            array_push($response, array("category" => $entryCategoryArray[0], "total_amount" => $intercityTravelCost, "emp_amount" => $intercityTravelCostEmp, "ac_amount" => $intercityTravelCostAC, "response" => $intercityTravelCostResponse));

        }
        if (sizeof($FoodBoardingResponse) > 0) {
            array_push($response, array("category" => $entryCategoryArray[1], "total_amount" => $FoodBoarding, "emp_amount" => $FoodBoardingEmp, "ac_amount" => $FoodBoardingAC, "response" => $FoodBoardingResponse));

        }
        if (sizeof($LocalTravelResponse) > 0) {
            array_push($response, array("category" => $entryCategoryArray[2], "total_amount" => $LocalTravel, "emp_amount" => $LocalTravelEmp, "ac_amount" => $LocalTravelAC, "response" => $LocalTravelResponse));

        }
        if (sizeof($MiscellaneousResponse) > 0) {
            array_push($response, array("category" => $entryCategoryArray[3], "total_amount" => $Miscellaneous, "emp_amount" => $MiscellaneousEmp, "ac_amount" => $MiscellaneousAC, "response" => $MiscellaneousResponse));

        }
        if (sizeof($RepairsResponse) > 0) {
            array_push($response, array("category" => $entryCategoryArray[4], "total_amount" => $Repairs, "emp_amount" => $RepairsEmp, "ac_amount" => $RepairsAC, "response" => $RepairsResponse));

        }
        if (sizeof($IntlTravelInsuranceResponse) > 0) {
            array_push($response, array("category" => $entryCategoryArray[5], "total_amount" => $IntlTravelInsurance, "emp_amount" => $IntlTravelInsuranceEmp, "ac_amount" => $IntlTravelInsuranceAC, "response" => $IntlTravelInsuranceResponse));

        }
        if (sizeof($FuelMileageResponse) > 0) {
            array_push($response, array("category" => $entryCategoryArray[6], "total_amount" => $FuelMileage, "emp_amount" => $FuelMileageEmp, "ac_amount" => $FuelMileageAC, "response" => $FuelMileageResponse));

        }
        if (sizeof($LodgingHotelsResponse) > 0) {
            array_push($response, array("category" => $entryCategoryArray[7], "total_amount" => $LodgingHotels, "emp_amount" => $LodgingHotelsEmp, "ac_amount" => $LodgingHotelsAC, "response" => $LodgingHotelsResponse));

        }
        if (sizeof($FixedAssetResponse) > 0) {
            array_push($response, array("category" => $entryCategoryArray[8], "total_amount" => $FixedAsset, "emp_amount" => $FixedAssetEmp, "ac_amount" => $FixedAssetAC, "response" => $FixedAssetResponse));
        }
        if (sizeof($MOMResponse) > 0) {
            array_push($response, array("category" => $entryCategoryArray[9], "total_amount" => 0, "emp_amount" => 0, "ac_amount" => 0, "response" => $MOMResponse));
        }
        if (sizeof($ServiceTimesheetResponse) > 0) {
            array_push($response, array("category" => $entryCategoryArray[10], "total_amount" => 0, "emp_amount" => 0, "ac_amount" => 0, "response" => $ServiceTimesheetResponse));
        }
        if (sizeof($FeedbackResponse) > 0) {
            array_push($response, array("category" => $entryCategoryArray[11], "total_amount" => 0, "emp_amount" => 0, "ac_amount" => 0, "response" => $FeedbackResponse));
        }
        if (sizeof($SATResponse) > 0) {
            array_push($response, array("category" => $entryCategoryArray[12], "total_amount" => 0, "emp_amount" => 0, "ac_amount" => 0, "response" => $SATResponse));
        }
        if (sizeof($ChecklistResponse) > 0) {
            array_push($response, array("category" => $entryCategoryArray[13], "total_amount" => 0, "emp_amount" => 0, "ac_amount" => 0, "response" => $ChecklistResponse));
        }

        return $response;

    }


    function submitTec($userId, $tecId, $claimEndDate, $userNote, $bookingJson)
    {

        $this->insertTecEntryFromBooking($tecId, $userId, $bookingJson);

        $result = $this->con->query("UPDATE `emp_main_tec` set `status`= 'submit',`claim_end_date`='$claimEndDate',`user_note`='$userNote',
         `total_amount` = (SELECT SUM(`bill_amount`) FROM `emp_tec_entry` WHERE `tec_id` = '$tecId' 
          AND `is_active` = '$this->active'),`submit_date`='$this->date',`submit_by_id`='$userId', 
          `modified_date` = '$this->date', `modified_by_id` = '$userId' WHERE `id` = '$tecId'");

        if ($result === TRUE) {
            $this->createTecLog($tecId, $userId, $userNote, 'submit');
            $this->sendMail($tecId, $userId);
            return $this->con->affected_rows;
        } else {
            return QUERY_PROBLEM;
        }
    }

    function updateTecByAdmin($userId, $tecId, $status, $remark)
    {
        $sqlQuery = "";
        if (strtolower($status) == 'draft' || strtolower($status) == 'submit') {
            $sqlQuery = "UPDATE `emp_main_tec` set `status`= '$status',`total_amount` = '0',`remark` = '$remark',
            `modified_date` = '$this->date', `modified_by_id` = '$userId' WHERE `id` = '$tecId'";
        } else {
            $sqlQuery = "UPDATE `emp_main_tec` set `status`= '$status',`remark` = '$remark', 
            `modified_date` = '$this->date', `modified_by_id` = '$userId' WHERE `id` = '$tecId'";
        }

        if ($this->con->query($sqlQuery) === TRUE) {
            $this->createTecLog($tecId, $userId, $remark, $status);
            $this->sendMail($tecId, NULL, $userId);
            return $this->con->affected_rows;
        } else {
            return QUERY_PROBLEM;
        }
    }

    function getLastTecentryInsertId()
    {
        $id = 0;
        $result = $this->con->query("SELECT id from `emp_tec_entry` ORDER BY `id` DESC limit 1");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id = $row['id'];
        }
        return $id;
    }

    function getTecAmount($tecId)
    {
        $amount = 0;
        $result = $this->con->query("SELECT `total_amount` from `emp_main_tec` WHERE `id` = '$tecId'");
        if ($result->num_rows > 0) {
            if ($row = $result->fetch_assoc()) {
                $amount = $row['total_amount'];
            }
        }
        return $amount;
    }

    function getPrevBillAmount($tecId, $entryId)
    {
        $amount = 0;
        $result = $this->con->query("SELECT `bill_amount` from `emp_tec_entry` WHERE `id` = '$entryId' AND `tec_id` = '$tecId'");
        if ($result->num_rows > 0) {
            if ($row = $result->fetch_assoc()) {
                $amount = $row['bill_amount'];
            }
        }
        return $amount;
    }

    function getPreviousTecPath($tecId, $entryId)
    {
        $path = "";
        $result = $this->con->query("SELECT `attachment_path` from `emp_tec_entry` WHERE `id` = '$entryId' AND `tec_id` = '$tecId'");
        if ($result->num_rows > 0) {
            if ($row = $result->fetch_assoc()) {
                $path = $row['attachment_path'];
            }
        }
        return $path;
    }

    function insertTecAmount($tecId, $created_by_id, $bill_amount, $prevBillAmount)
    {
        $totalAmount = $bill_amount + $prevBillAmount;
        $this->con->query("UPDATE `emp_main_tec` set `total_amount` = '$totalAmount' WHERE `id` = '$tecId'");
    }

    function updateTecAmount($tecId, $userId, $newAmount, $previousAmount)
    {
        $totalAmount = 0;
        $result = $this->con->query("SELECT `total_amount` from `emp_main_tec` WHERE `id` = '$tecId'");
        if ($result->num_rows > 0) {
            if ($row = $result->fetch_assoc()) {
                $totalAmount = $row['total_amount'];

                // echo "\n new_amount ".$new_amount." total_amount ".$total_amount."\n";
                $diffAmount = 0;
                if ($previousAmount > $newAmount) {
                    $diffAmount = $previousAmount - $newAmount;
                    $totalAmount = $totalAmount - $diffAmount;
                } else {
                    $diffAmount = $newAmount - $previousAmount;
                    $totalAmount = $totalAmount + $diffAmount;
                }

                // echo " update amount ".$total_amount;
                $this->con->query("UPDATE `emp_main_tec` set `total_amount` = '$totalAmount' WHERE `id` = '$tecId'");
            }
        }
    }


    function fetchTec($page,$filterBy,$searchText)
    {

        /*if (!isset($orderType) || (sizeof(trim($orderType)) > 1) || !in_array($orderType, ["ASC", "DESC"])) {
            $orderType = "ASC";
        }

        if (!isset($orderBy) || (sizeof(trim($orderBy)) > 1) || !in_array($orderBy, ["project_id", "created_by_id"])) {
            $orderBy = "id";
        }*/

        $limit = 25;
        if ((isset($page)) && ($page > 0)) {
            $offest = ($page - 1) * $limit;
        } else {
            $offest = 0;
        }

        $response = array();

        if ($searchText != ''){
            $result = $this->con->query("SELECT emt.*, mzp.`project_name`, u.`name` FROM `emp_main_tec` as emt JOIN `master_zoho_project` as mzp ON 
        emt.`project_id` = mzp.`id` JOIN `user` as u on emt.`created_by_id` = u.`id` WHERE 
        (emt.`id` LIKE '%$searchText%' OR mzp.`project_name` like LOWER('%$searchText%') OR LOWER(u.`name`) LIKE LOWER('%$searchText%'))
        AND emt.`status` != 'draft' AND emt.`is_active` = '$this->active' ORDER BY emt.`id` DESC limit $offest , $limit");

        }else if($filterBy != ''){
            $result = $this->con->query("SELECT emt.*, mzp.`project_name`, u.`name` FROM `emp_main_tec` as emt JOIN `master_zoho_project` as mzp ON 
        emt.`project_id` = mzp.`id` JOIN `user` as u on emt.`created_by_id` = u.`id` WHERE emt.`status` = LOWER('$filterBy') 
        ORDER BY emt.`id` DESC limit $offest , $limit");

        }else {
            $result = $this->con->query("SELECT emt.*, mzp.`project_name`, u.`name` FROM `emp_main_tec` as emt JOIN `master_zoho_project` as mzp ON 
        emt.`project_id` = mzp.`id` JOIN `user` as u on emt.`created_by_id` = u.`id` WHERE emt.`status` != 'draft' ORDER BY emt.`id` DESC 
        limit $offest , $limit");
        }

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $totalAmount = $this->getTotalTecAmount($row['id']);
                $row['total_amount'] = $totalAmount;
                array_push($response, $row);
            }
        }

        return $response;
    }


    function fetchUserTec($page,$userId,$filterBy,$searchText)
    {

        $limit = 25;
        if ((isset($page)) && ($page > 0)) {
            $offest = ($page - 1) * $limit;
        } else {
            $offest = 0;
        }

        $response = array();

        if ($searchText != ''){
            $result = $this->con->query("SELECT emt.*, mzp.`project_name`, u.`name` FROM `emp_main_tec` as emt JOIN `master_zoho_project` as mzp ON 
        emt.`project_id` = mzp.`id` JOIN `user` as u on emt.`created_by_id` = u.`id` WHERE 
        (emt.`id` LIKE '%$searchText%' OR mzp.`project_name` like LOWER('%$searchText%') OR LOWER(u.`name`) LIKE LOWER('%$searchText%'))
        AND emt.`created_by_id` = '$userId' AND emt.`is_active` = '$this->active' ORDER BY emt.`id` DESC limit $offest , $limit");

        }else if($filterBy != ''){
            $result = $this->con->query("SELECT emt.*, mzp.`project_name`, u.`name` FROM `emp_main_tec` as emt JOIN `master_zoho_project` as mzp ON 
        emt.`project_id` = mzp.`id` JOIN `user` as u on emt.`created_by_id` = u.`id` WHERE emt.`created_by_id` = '$userId' 
        AND emt.`status` = LOWER('$filterBy') 
        ORDER BY emt.`id` DESC limit $offest , $limit");

        }else {
            $result = $this->con->query("SELECT emt.*, mzp.`project_name`, u.`name` FROM `emp_main_tec` as emt JOIN `master_zoho_project` as mzp ON 
        emt.`project_id` = mzp.`id` JOIN `user` as u on emt.`created_by_id` = u.`id` WHERE  emt.`created_by_id` = '$userId' ORDER BY emt.`id` DESC 
        limit $offest , $limit");
        }

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $totalAmount = $this->getTotalTecAmount($row['id']);
                $row['total_amount'] = $totalAmount;
                array_push($response, $row);
            }
        }

        return $response;
    }


    function getLatestTecsStatus($userId, $limit = NULL)
    {
        if (NULL === $limit) {
            $limit = 5;
        }

        $response = array();
        $result = $this->con->query("SELECT * from `emp_main_tec` where `created_by_id` = '$userId' ORDER BY `id` DESC LIMIT $limit");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc())
                array_push($response, $row);
        }
        return $response;
    }

    function getLatestTecComments($tecId, $limit = NULL)
    {
        if (NULL === $limit) {
            $limit = 5;
        }

        $response = array();
        $result = $this->con->query("SELECT * from `emp_tec_log` where `tec_id` = '$tecId' ORDER BY `id` DESC LIMIT $limit");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc())
                array_push($response, $row);
        }
        return $response;
    }

    function getTotalTecAmount($tecId, $paidBy = NULL)
    {
        if (NULL === $paidBy) {
            $paidBy = 'Employee';
        }
        $totalExpense = 0;
        $result = $this->con->query("SELECT SUM(`bill_amount`) as bill_amount from `emp_tec_entry` where `tec_id` = '$tecId' AND `paid_by` = '$paidBy' AND `is_active` = '$this->active'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $totalExpense = $row['bill_amount'];
        }
        return $totalExpense;

    }

    function sendMail($tecId, $userId = NULL, $adminId = NULL)
    {
        include 'SendMail.php';

        $mailObj = new SendMail();
        $tecDataArr = $this->getTecDataById($tecId);

        if (NULL === $userId) {
            $userId = $tecDataArr['created_by_id'];
        }

        $tecCommentsArr = $this->getLatestTecComments($tecId);
        $tecsStatusArr = $this->getLatestTecsStatus($userId);
        $totalExpense = $this->getTotalTecAmount($tecId);
        $mailObj->tecMail($tecDataArr, $this->date, $tecCommentsArr, $tecsStatusArr, $totalExpense, $userId);
    }

    function getCreatedByIdByTec($tecId)
    {
        $createdById = 0;
        $result = $this->con->query("SELECT `created_by_id` from `emp_main_tec` where `id` = '$tecId'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $createdById = $row['created_by_id'];
        }
        return $createdById;
    }


    function getTecDataById($tecId)
    {
        $dataArr = array();
        $result = $this->con->query("SELECT emc.*, mzp.`project_name`, mzp.`client_name`, u.`name` from `emp_main_tec` as emc join `master_zoho_project` as mzp on emc.`project_id` = mzp.`id` join `user` as u on emc.`created_by_id` = u.`id`  where emc.`id` = '$tecId'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $dataArr = $row;
        }
        return $dataArr;
    }

    function deleteTecEntryById($tecEntryId, $userId)
    {
        // SELECT `id`, `tec_id`, `booking_id`, `payment_id`, `entry_category`, `travel_mode`, `deprt_date`, `arrival_date`, `deprt_time`, `arrival_time`, `is_metro`, `is_billable`, `location`, `from_location`, `to_location`, `kilo_meter`, `mileage`, `unit_price`, `total_quantitty`, `description`, `date`, `paid_to`, `paid_to_id`, `paid_by`, `gstin`, `bill_amount`, `sub_total`, `bill_num`, `attachment_path`, `created_by_id`, `created_date`, `modified_date`, `is_active`, `modified_by_id` FROM `emp_tec_entry` WHERE 1
        $result = $this->con->query("UPDATE `emp_tec_entry` set `is_active` = '$this->deactive' WHERE `id` = '$tecEntryId'");
        if ($result === TRUE) {
            $this->createTecEntryLog($tecEntryId, $userId, 'Delete');
            return TRUE;
        }
        return FALSE;
    }

    function createTecEntryLog($tecEntryId, $userId, $status)
    {
        // SELECT `id`, `tec_entry_id`, `user_id`, `status`, `date` FROM `emp_tec_entry_log` WHERE 1
        $this->con->query("INSERT into `emp_tec_entry_log` (`tec_entry_id`,`user_id`,`status`,`date`) VALUES ('$tecEntryId','$userId','$status','$this->date')");
    }

    function createTecLog($tecId, $userId, $comment, $tecStatus)
    {
        $this->con->query("INSERT into `emp_tec_log` (`tec_id`, `comment`, `status`, `created_by_id`,`created_date`) 
        VALUES ('$tecId','$comment','$tecStatus','$userId','$this->date')");
    }

    private function isTecExist($createdById, $projectId, $claimStartDate)
    {
        $result = $this->con->query("SELECT `id` from `emp_main_tec` WHERE `created_by_id` = '$createdById' AND 
        `project_id` = '$projectId' AND `claim_start_date` = '$claimStartDate'");

        return $result->num_rows > 0;
    }

    function getFileExtension($file)
    {
        $path_parts = pathinfo($file);
        return $path_parts['extension'];
    }
}

