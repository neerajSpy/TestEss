<?php

// namespace db_opertion;
 /*error_reporting(E_ALL);
ini_set('display_errors', 1); */

class Vendor
{

    private $con;

    private $date;

    private $basePath;

    private $isActive;

    private $deactive;

    private $mainVendorTable, $tempVendorTable;

    public function __construct()
    {
        include_once 'config/NewConfig.php';
        include_once 'config/constant.php';
        $db = new NewConfig();
        $this->con = $db->dbConnect();
        date_default_timezone_set('Asia/Kolkata');
        $this->date = date("Y-m-d H:i:s");
        $this->basePath = "http://ess.technitab.in/web_service/ESS/";
        $this->isActive = IS_ACTIVE;
        $this->deactive = DEACTIVE;

        $this->mainVendorTable = "master_zoho_vendor";
        $this->tempVendorTable = "temp_vendor";
    }

    function addMainVendor($bankFile, $idProofFile, $billFile, $vendorJson)
    {
        if (! $this->isVendorExist($vendorJson->company_name,$vendorJson->first_name,$vendorJson->last_name, $vendorJson->vendor_type)) {
            return $this->addVendor($this->mainVendorTable, $bankFile, $idProofFile, $billFile, $vendorJson);
        } else {
            return EXIST;
        }
    }

    function addTempVendor($bankFile, $idProofFile, $billFile, $vendorJson)
    {
        if (! $this->isTempVendorExist($vendorJson->company_name,$vendorJson->first_name,$vendorJson->last_name, $vendorJson->vendor_type)) {
            $vendorId = $this->addVendor($this->tempVendorTable, $bankFile, $idProofFile, $billFile, $vendorJson);
            
            if($vendorId != QUERY_PROBLEM){
                $this->sendVendorMail($vendorId,$vendorJson);
            }
            
            return $vendorId; 
        } else {
            return EXIST;
        }
    }

    
    function sendVendorMail($vendorId,$vendorJson){
        include 'db_class/SendMail.php';
        
        $mailObj = new SendMail();
        
        $contactName = "";
        if (strlen(trim($vendorJson->company_name)) < 1) {
            $contactName = $vendorJson->first_name . " " . $vendorJson->last_name;
        } else {
            $contactName = $vendorJson->company_name;
        }
        
        $contactName = $this->getFormettedContactName($contactName, $vendorId);
        
        if ($vendorJson->modified_by_id == '0') {
            $mailObj->submitVendorMail($vendorJson, $contactName);
          
        }else{
            $mailObj->approveVendorMail($vendorJson, $contactName);
        }
        
    }
    
    function addVendor($table, $bankFile, $idProofFile, $billFile, $vendorJson)
    {
        $firstName = $vendorJson->first_name;
        $lastName = $vendorJson->last_name;
        $companyName = $vendorJson->company_name;
        $email = $vendorJson->email;
        $contact = $vendorJson->contact;
        $gstTreatment = $vendorJson->gst_treatment;
        $gstNum = $vendorJson->gst_num;
        $district = $vendorJson->district;
        $placeOfSupply = $vendorJson->place_of_supply;
        $paymentTerm = $vendorJson->payment_term;
        $billingCity = $vendorJson->billing_city;
        $billingState = $vendorJson->billing_state;
        $billingCountry = $vendorJson->billing_country;
        $billingZipcode = $vendorJson->billing_zipcode;
        $billingAddress = $vendorJson->billing_address;
        $billingPhone = $vendorJson->billing_phone;
        $shippingCity = $vendorJson->shipping_city;
        $shippingState = $vendorJson->shipping_state;
        $shippingZipcode = $vendorJson->shipping_zipcode;
        $shippingCountry = $vendorJson->shipping_country;
        $shippingAddress = $vendorJson->shipping_address;
        $shippingPhone = $vendorJson->shipping_phone;
        $panNumber = $vendorJson->pan_number;
        $serviceTaxNumber = $vendorJson->service_tax_number;
        $taxNumber = $vendorJson->tax_number;
        $adhaarNumber = $vendorJson->adhaar_number;
        $bankName = $vendorJson->bank_name;
        $bankAddress = $vendorJson->bank_address;
        $ifsc = $vendorJson->ifsc;
        $vendorType = $vendorJson->vendor_type;
        $accountNumber = $vendorJson->account_number;
        $bankHolderName = $vendorJson->bank_holder_name;
        $createdById = $vendorJson->created_by_id;
        $rate = $vendorJson->rate;
        $voterId = $vendorJson->voter_id;
        $paymentMode = $vendorJson->payment_mode;
        $billNumber = $vendorJson->bill_number;

        if ($vendorType == "") {
            $vendorType = "Hotel";
        }

        if (strtolower($gstTreatment) == 'registered business - regular') {
            $gstTreatment = "business_gst";
        } else if (strtolower($gstTreatment) == "unregistered business") {
            $gstTreatment = "business_none";
        }

        $idFileName = "";
        $idProofPath = "";
        if (isset($_FILES[$idProofFile]['tmp_name'])) {
            $idFileName = $createdById . '_' . $this->getMiliSecond() . "_" . rand() . '.' . $this->getFileExtension($_FILES['id_proof_attachment']['name']);
            $idProofPath = $this->basePath . 'vendor_doc_pic/' . $idFileName;
        }

        $bankFileName = "";
        $bankPath = "";
        if (isset($_FILES[$bankFile]['tmp_name'])) {
            $bankFileName = $createdById . '_' . $this->getMiliSecond() . "_" . rand() . '.' . $this->getFileExtension($_FILES['bank_name_attachment']['name']);
            $bankPath = $this->basePath . 'vendor_doc_pic/' . $bankFileName;
        }

        $billFileName = "";
        $billFilePath = "";
        if (isset($_FILES[$billFile]['tmp_name'])) {

            $billFileName = $createdById . '_' . $this->getMiliSecond() . "_" . rand() . '.' . $this->getFileExtension($_FILES['bill_attachment']['name']);
            $billFilePath = $this->basePath . 'vendor_doc_pic/' . $billFileName;
        }

        $placeOfSupply = $this->getZohoStateCode($billingState);
        
        $result = $this->con->query("INSERT INTO $table (`created_date`, `source_of_supply`, `vendor_type`, `company_name`, `display_name`, `first_name`, `last_name`, `email`, `mobile_phone`,`gst_treatment`,
                    `gst_num`, `pan_number`, `payment_term`, `billing_address`, `district`, `billing_city`, `billing_state`, `billing_country`, 
                    `billing_zipcode`, `billing_phone`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_country`, `shipping_zipcode`,`shipping_phone`, `voter_id`, `rate`, `status`, `service_tax_number`, `tax_number`, `adhaar_number`, `bank_name`, `bank_address`,`bank_holder_name` ,`account_number`,`ifsc`, `bank_file`, `id_proof_file`,`payment_mode`,`bill_number`,`bill_file`,`created_by_id`)
                    VALUES ('$this->date', '$placeOfSupply', '$vendorType', '$companyName', '$companyName', '$firstName', '$lastName', '$email','$contact', '$gstTreatment', '$gstNum', '$panNumber', '$paymentTerm', '$billingAddress', '$district', '$billingCity', '$billingState','$billingCountry','$billingZipcode','$billingPhone','$shippingAddress','$shippingCity','$shippingState',
                    '$shippingCountry','$shippingZipcode','$shippingPhone','$voterId','$rate','Active','$serviceTaxNumber','$taxNumber','$adhaarNumber','$bankName', '$bankAddress','$bankHolderName', '$accountNumber', '$ifsc','$bankPath', '$idProofPath','$paymentMode','$billNumber','$billFilePath','$createdById')");

        if ($result === TRUE) {
            $vendorId = $this->con->insert_id;
            if ($idProofPath != "") {
                $this->moveFile($_FILES[$idProofFile]['tmp_name'], $idFileName);
            }

            if ($bankPath != "") {
                $this->moveFile($_FILES[$bankFile]['tmp_name'], $bankFileName);
            }
            if ($billFilePath != "") {
                $this->moveFile($_FILES[$billFile]['tmp_name'], $billFileName);
            }
            
            $contactName = "";
            if (strlen(trim($companyName)) < 1) {
                $contactName = $firstName." ".$lastName;
            }else{
                $contactName = $companyName;
            }
            $this->updateContactDisplayName($table, $contactName, $vendorId);
            return $vendorId;
        } else {
            return QUERY_PROBLEM;
        }
    }

    function approveVendor($vendorJson)
    {
        $id = $vendorJson->id;
        $firstName = $vendorJson->first_name;
        $lastName = $vendorJson->last_name;
        $companyName = $vendorJson->company_name;
        $email = $vendorJson->email;
        $contact = $vendorJson->contact;
        $gstTreatment = $vendorJson->gst_treatment;
        $gstNum = $vendorJson->gst_num;
        $district = $vendorJson->district;
        $placeOfSupply = $vendorJson->place_of_supply;
        $paymentTerm = $vendorJson->payment_term;
        $billingCity = $vendorJson->billing_city;
        $billingState = $vendorJson->billing_state;
        $billingCountry = $vendorJson->billing_country;
        $billingZipcode = $vendorJson->billing_zipcode;
        $billingAddress = $vendorJson->billing_address;
        $billingPhone = $vendorJson->billing_phone;
        $shippingCity = $vendorJson->shipping_city;
        $shippingState = $vendorJson->shipping_state;
        $shippingZipcode = $vendorJson->shipping_zipcode;
        $shippingCountry = $vendorJson->shipping_country;
        $shippingAddress = $vendorJson->shipping_address;
        $shippingPhone = $vendorJson->shipping_phone;
        $panNumber = $vendorJson->pan_number;
        $serviceTaxNumber = $vendorJson->service_tax_number;
        $taxNumber = $vendorJson->tax_number;
        $adhaarNumber = $vendorJson->adhaar_number;
        $bankName = $vendorJson->bank_name;
        $bankAddress = $vendorJson->bank_address;
        $ifsc = $vendorJson->ifsc;
        $vendorType = $vendorJson->vendor_type;
        $accountNumber = $vendorJson->account_number;
        $bankHolderName = $vendorJson->bank_holder_name;
        $createdById = $vendorJson->created_by_id;
        $rate = $vendorJson->rate;
        $voterId = $vendorJson->voter_id;
        $paymentMode = $vendorJson->payment_mode;
        $billNumber = $vendorJson->bill_number;
        $idProofPath = $vendorJson->id_proof_file;
        $bankPath = $vendorJson->bank_file;
        $billFilePath = $vendorJson->bill_file;
        $modifiedById = $vendorJson->modified_by_id;

        if ($vendorType == "") {
            $vendorType = "Hotel";
        }

        if (strtolower($gstTreatment) == 'registered business - regular') {
            $gstTreatment = "business_gst";
        } else if (strtolower($gstTreatment) == "unregistered business") {
            $gstTreatment = "business_none";
        }

        if (! $this->isVendorExist($companyName,$firstName,$lastName, $vendorType)) {

            $result = $this->con->query("INSERT INTO `master_zoho_vendor` (`created_date`,`modified_date`,`source_of_supply`,`vendor_type`,
                    `company_name`, `display_name`,`first_name`, `last_name`, `email`, `mobile_phone`,`gst_treatment`,
                    `gst_num`, `pan_number`, `payment_term`,`billing_address`,
                    `district`,`billing_city`, `billing_state`, `billing_country`, `billing_zipcode`,
                    `billing_phone`,`shipping_address`,`shipping_city`, `shipping_state`, `shipping_country`,
                    `shipping_zipcode`,`shipping_phone`,`voter_id`,`rate`,`Status`,
                    `service_tax_number`, `tax_number`,`adhaar_number`,`bank_name`, `bank_address`,
                    `bank_holder_name` ,`account_number`,`ifsc`, `bank_file`,
                    `id_proof_file`,`payment_mode`,`bill_number`,`bill_file`,`created_by_id`,`modified_by_id`)
                    VALUES ('$this->date','$this->date','$placeOfSupply','$vendorType','$companyName','$companyName',
                    '$firstName','$lastName','$email','$contact','$gstTreatment','$gstNum','$panNumber',
                    '$paymentTerm','$billingAddress','$district','$billingCity','$billingState','$billingCountry',
                    '$billingZipcode','$billingPhone','$shippingAddress','$shippingCity','$shippingState',
                    '$shippingCountry','$shippingZipcode','$shippingPhone','$voterId','$rate','Active',
                    '$serviceTaxNumber','$taxNumber','$adhaarNumber','$bankName', '$bankAddress',
                    '$bankHolderName', '$accountNumber', '$ifsc','$bankPath', '$idProofPath','$paymentMode',
                    '$billNumber','$billFilePath','$createdById','$modifiedById')");

            if ($result === TRUE) {
                $vendorId = $this->con->insert_id;
                
                $contactName = "";
                if (strlen(trim($companyName)) < 1) {
                    $contactName = $firstName." ".$lastName;
                }else{
                    $contactName = $companyName;
                }
                
                $this->updateTempVendorStatus($id, $modifiedById, $this->deactive);
                $this->updateContactDisplayName($this->mainVendorTable, $contactName, $vendorId);
                $this->sendVendorMail($vendorId, $vendorJson);
                return $vendorId;
            } else {
                return QUERY_PROBLEM;
            }
        } else {
            return EXIST;
        }
    }

   
    function updateTempVendor($bankFile, $idProofFile, $billFile, $vendorJson)
    {
        $id = $vendorJson->id;
        $firstName = $vendorJson->first_name;
        $lastName = $vendorJson->last_name;
        $companyName = $vendorJson->company_name;
        $email = $vendorJson->email;
        $contact = $vendorJson->contact;
        $gstTreatment = $vendorJson->gst_treatment;
        $gstNum = $vendorJson->gst_num;
        $district = $vendorJson->district;
        $placeOfSupply = $vendorJson->place_of_supply;
        $paymentTerm = $vendorJson->payment_term;
        $billingCity = $vendorJson->billing_city;
        $billingState = $vendorJson->billing_state;
        $billingCountry = $vendorJson->billing_country;
        $billingZipcode = $vendorJson->billing_zipcode;
        $billingAddress = $vendorJson->billing_address;
        $billingPhone = $vendorJson->billing_phone;
        $shippingCity = $vendorJson->shipping_city;
        $shippingState = $vendorJson->shipping_state;
        $shippingZipcode = $vendorJson->shipping_zipcode;
        $shippingCountry = $vendorJson->shipping_country;
        $shippingAddress = $vendorJson->shipping_address;
        $shippingPhone = $vendorJson->shipping_phone;
        $panNumber = $vendorJson->pan_number;
        $serviceTaxNumber = $vendorJson->service_tax_number;
        $taxNumber = $vendorJson->tax_number;
        $adhaarNumber = $vendorJson->adhaar_number;
        $bankName = $vendorJson->bank_name;
        $bankAddress = $vendorJson->bank_address;
        $ifsc = $vendorJson->ifsc;
        $vendorType = $vendorJson->vendor_type;
        $accountNumber = $vendorJson->account_number;
        $bankHolderName = $vendorJson->bank_holder_name;
        $createdById = $vendorJson->created_by_id;
        $modifiedById = $vendorJson->modified_by_id;
        $rate = $vendorJson->rate;
        $voterId = $vendorJson->voter_id;
        $paymentMode = $vendorJson->payment_mode;
        $billNumber = $vendorJson->bill_number;

        if ($vendorType == "") {
            $vendorType = "Hotel";
        }

        if (strtolower($gstTreatment) == 'registered business - regular') {
            $gstTreatment = "business_gst";
        } else if (strtolower($gstTreatment) == "unregistered business") {
            $gstTreatment = "business_none";
        }

        $contactName = "";
        if (strlen(trim($companyName)) < 1) {
            $contactName = $firstName . " " . $lastName;
        } else {
            $contactName = $companyName;
        }
        
        $contactName = $this->getFormettedContactName($contactName, $id);

        $idProofPath = "";
        if (isset($_FILES[$idProofFile]['tmp_name'])) {
            $fileName = $createdById . '_' . $this->getMiliSecond() . "_" . rand() . '.' . $this->getFileExtension($_FILES['id_proof_attachment']['name']);
            $idProofPath = $this->basePath . 'vendor_doc_pic/' . $fileName;
            $this->moveFile($_FILES[$idProofFile]['tmp_name'], $fileName);
        } else {
            $idProofPath = $vendorJson->id_proof_file;
        }

        $bankPath = "";
        if (isset($_FILES[$bankFile]['tmp_name'])) {
            $fileName = $createdById . '_' . $this->getMiliSecond() . "_" . rand() . '.' . $this->getFileExtension($_FILES['bank_name_attachment']['name']);
            $bankPath = $this->basePath . 'vendor_doc_pic/' . $fileName;
            $this->moveFile($_FILES[$bankFile]['tmp_name'], $fileName);
        } else {
            $bankPath = $vendorJson->bank_file;
        }

        $billFilePath = "";
        if (isset($_FILES[$billFile]['tmp_name'])) {

            $fileName = $createdById . '_' . $this->getMiliSecond() . "_" . rand() . '.' . $this->getFileExtension($_FILES['bill_attachment']['name']);
            $billFilePath = $this->basePath . 'vendor_doc_pic/' . $fileName;
            $this->moveFile($_FILES[$billFile]['tmp_name'], $fileName);
        } else {
            $billFilePath = $vendorJson->bill_file;
        }

        $result = $this->con->query("UPDATE `temp_vendor` SET `source_of_supply` ='$placeOfSupply',
       `vendor_type`='$vendorType', `contact_name` = '$contactName', `company_name`='$companyName', `display_name` = '$contactName',
       `first_name` = '$firstName', `last_name` = '$lastName', `email` = '$email', `mobile_phone`= '$contact',
       `gst_treatment` = '$gstTreatment', `gst_num`= '$gstNum', `pan_number`='$panNumber',
       `payment_term`='$paymentTerm',`billing_address` = '$billingAddress',`district`='$district',
       `billing_city` = '$billingCity', `billing_state` = '$billingState', `billing_country`='$billingCountry', 
       `billing_zipcode`='$billingZipcode',`billing_phone`='$billingPhone',`shipping_address`='$shippingAddress',
       `shipping_city`='$shippingCity', `shipping_state`='$shippingState', `shipping_country`='$shippingCountry',
       `shipping_zipcode`='$shippingZipcode',`shipping_phone`='$shippingPhone',`voter_id`='$voterId',`rate`='$rate',
       `Status`='Active', `service_tax_number` = '$serviceTaxNumber', `tax_number`='$taxNumber',
       `adhaar_number`='$adhaarNumber',`bank_name`='$bankName', `bank_address`='$bankAddress',
       `bank_holder_name`='$bankHolderName' ,`account_number`='$accountNumber',`ifsc`='$ifsc', 
       `bank_file`='$bankPath', `id_proof_file`='$idProofPath',`payment_mode`='$paymentMode',
       `bill_number` = '$billNumber',`bill_file`='$billFilePath',`modified_by_id` = '$modifiedById',
       `modified_date` = '$this->date' WHERE `id` = '$id'");

        if ($result === TRUE) {
            return $this->con->affected_rows;
        } else {
            return QUERY_PROBLEM;
        }
    }

    function updateContactDisplayName($tableName, $contactName, $vendorId)
    {
        $contactName = $this->getFormettedContactName($contactName, $vendorId);
        
        $this->con->query("UPDATE $tableName set `contact_name` = '$contactName' , `display_name` = '$contactName'
        where `id` = '$vendorId'");
    }

    function getFormettedContactName($name, $id)
    {
        return $name . " - " . sprintf("%04d", $id);
    }

    private function moveFile($attachment, $fileName)
    {
        $targetPath = $_SERVER['DOCUMENT_ROOT'] . "/web_service/ESS/vendor_doc_pic/" . $fileName;
        if (move_uploaded_file($attachment, $targetPath)) {
            // echo "tax_number attached";
        }
    }

    function getVendorDetailByBookingId($id){
        $vendorData = array();
        $result = $this->con->query("SELECT mzv.* from `emp_booking` as eb JOIN `master_zoho_vendor` as mzv on eb.`admin_vendor_id` = mzv.`id` where eb.`id` = '$id'");
        
        if($result->num_rows >0){
            $vendorData =  $result->fetch_assoc();
            
        }
        return $vendorData;
    }
    
    function isTempVendorExist($companyName,$firstName,$lastName, $vendorType)
    {
        $dispayName = $companyName;
        if (strlen(trim($companyName)) <1) {
            $dispayName = $firstName." ".$lastName;
        }
        $result = $this->con->query("SELECT * from `temp_vendor` where `display_name` like '$dispayName' 
        AND `vendor_type` = '$vendorType' AND `is_active` = '$this->isActive'");

        return $result->num_rows > 0;
    }

    function isVendorExist($companyName,$firstName,$lastName, $vendorType)
    {
        $dispayName = $companyName;
        if (strlen(trim($companyName)) <1) {
            $dispayName = $firstName." ".$lastName;
        }
            
        $result = $this->con->query("SELECT * from `master_zoho_vendor` where `display_name` like '$dispayName%'
        AND `vendor_type` = '$vendorType' AND `is_active` = '$this->isActive'");

        return $result->num_rows > 0;
    }

    function updateTempVendorStatus($id, $modifiedById, $status)
    {
       
        $this->con->query("UPDATE `temp_vendor` set `is_active` = '$status', `modified_by_id` = '$modifiedById' where `id` = '$id'");
        
    }
    
    function getZohoStateCode($state){
        $code = "";
        $result = $this->con->query("SELECT `code` from `zoho_valid_state` where `name` = '$state'");
        if ($result->num_rows >0) {
            $row = $result->fetch_assoc();
            $code = $row['code'];
        }
        return $code;
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

