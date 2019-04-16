<?php
include "config/config.php";
date_default_timezone_set('Asia/Kolkata');
$role_id = $_POST['role_id'];
$related_table = $_POST['related_table'];

if ($related_table == "") {
    $related_table = getRelatedTable($con,$role_id);
}

$response = array();


// SELECT `id`, `org_unit_id`, `name`, `designation`, `emergency_number`, `mobile_number`, `whatsapp_number`, `official_email_id`, `personal_email_id`, `current_full_addres`, `permanent_full_address`, `blood_group`, `birth_date`, `marital_status`, `marriage_date`, `father`, `spouse`, `nationality`, `religion`, `gender`, `base_office_location`, `reporting_to`, `pay_grade`, `status`, `joining_date`, `appointment_date`, `last_working_day`, `pan_number`, `passport_number`, `aadhar_number`, `driving_license_number`, `voter_id_number`, `bank_name`, `bank_address`, `account_numer`, `ifsc_code`, `10th_year`, `10th_school`, `10th_board`, `10th_percentage`, `12th_year`, `12th_school`, `12th_board`, `12th_percentage`, `diploma_year`, `diploma_college`, `diploma_board`, `diploma_percentage`, `grad_year`, `grad_college`, `grad_board`, `grad_percentage`, `post_grad_year`, `post_grad_college`, `post_grad_board`, `post_grad_percentage`, `create_dated`, `created_by`, `modified_date`, `modified_by`, `is_active` FROM `master_get` WHERE 1

$sql_query = "SELECT * from $related_table where `id` = '$role_id'";
$result = $con->query($sql_query);

if($result->num_rows >0){
    if ($row = $result->fetch_array()) {

        echo json_encode(array("name"=>$row['name'],"designation"=>$row['designation'],"emergency_number"=>$row['emergency_number'],"mobile_number"=>$row['mobile_number'],"official_email_id"=>$row['official_email_id'],"personal_email_id"=>$row['personal_email_id'],"current_full_addres"=>$row['current_full_addres'],"permanent_full_address"=>$row['permanent_full_address'],"blood_group"=>$row['blood_group'],"birth_date"=>$row['birth_date'],"marital_status"=>$row['marital_status'],"marriage_date"=>$row['marriage_date'],"father"=>$row['father'],"spouse"=>$row['spouse'],"nationality"=>$row['nationality'],"religion"=>$row['religion'],"gender"=>$row['gender'],"base_office_location"=>$row['base_office_location'],"reporting_to"=>$row['reporting_to'],"joining_date"=>$row['joining_date'],"appointment_date"=>$row['appointment_date'],"pan_number"=>$row['pan_number'],"passport_number"=>$row['passport_number'],"aadhar_number"=>$row['aadhar_number'],"driving_license_number"=>$row['driving_license_number'],"voter_id_number"=>$row['voter_id_number'],"bank_name"=>$row['bank_name'],"bank_address"=>$row['bank_address'],"account_numer"=>$row['account_numer'],"ifsc_code"=>$row['ifsc_code'],"10th_year"=>$row['10th_year'],"10th_school"=>$row['10th_school'],"10th_board"=>$row['10th_board'],"10th_percentage"=>$row['10th_percentage'],"12th_year"=>$row['12th_year'],"12th_school"=>$row['12th_school'],"12th_board"=>$row['12th_board'],"12th_percentage"=>$row['12th_percentage'],"diploma_year"=>$row['diploma_year'],"diploma_college"=>$row['diploma_college'],"diploma_college"=>$row['diploma_college'],"diploma_board"=>$row['diploma_board'],"diploma_percentage"=>$row['diploma_percentage'],"grad_year"=>$row['grad_year'],"grad_college"=>$row['grad_college'],"grad_board"=>$row['grad_board'],"grad_percentage"=>$row['grad_percentage'],"post_grad_year"=>$row['post_grad_year'],"post_grad_college"=>$row['post_grad_college'],"post_grad_board"=>$row['post_grad_board'],"post_grad_percentage"=>$row['post_grad_percentage']));
    }
}


function getAppliedDate(){
    $now = new DateTime();
    return $now->format('Y-m-d H:i:s');
}


function getRelatedTable($con,$role_id){
    $tableName = "";
    $result = $con->query("SELECT * FROM `user` WHERE `role_id` = '$role_id'");
    if ($result->num_rows >0) {
        if ($row = $result->fetch_array()) {
        $tableName = $row['related_table'];    
        }
        
    }
    return $tableName;
}

?>