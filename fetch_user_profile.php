<?php
date_default_timezone_set('Asia/Kolkata');
include "config/config.php";

$user_id = $_POST['user_id'];
$role_id = $_POST['role_id'];
$related_table = $_POST['related_table'];

$response = array();

if ($related_table == '') {
    $related_table = getRelatedTable($con,$user_id);
}


// SELECT `emp_id`, `org_unit_id`, `name`, `designation`, `emergency_number`, `mobile_number`, `whatsapp_number`, `official_email_id`, `personal_email_id`, `current_full_addres`, `permanent_full_address`, `blood_group`, `birth_date`, `marital_status`, `marriage_date`, `father`, `spouse`, `nationality`, `religion`, `gender`, `base_office_location`, `reporting_to`, `pay_grade`, `status`, `joining_date`, `appointment_date`, `last_working_day`, `pan_number`, `passport_number`, `aadhar_number`, `driving_license_number`, `voter_id_number`, `bank_name`, `bank_address`, `account_numer`, `ifsc_code`, `10th_year`, `10th_school`, `10th_board`, `10th_percentage`, `12th_year`, `12th_school`, `12th_board`, `12th_percentage`, `diploma_year`, `diploma_college`, `diploma_board`, `diploma_percentage`, `grad_year`, `grad_college`, `grad_board`, `grad_percentage`, `post_grad_year`, `post_grad_college`, `post_grad_board`, `post_grad_percentage`, `create_dated`, `created_by`, `modified_date`, `modified_by`, `is_active` FROM `master_get` WHERE 1

$result = $con->query("SELECT * from $related_table where `emp_id` = '$role_id'");
if ($result->num_rows >0) {
    if ($row = $result->fetch_assoc()) {
        $response = $row;    
    }
}

echo json_encode($response);


function getRelatedTable($con,$user_id){
    $related_table = "";
    $result = $con->query("SELECT * from `user` where `id` = '$user_id'");
    if ($result->num_rows >0) {
        if ($row = $result->fetch_assoc()) {
            $related_table = $row['related_table'];
        }
    }
return $related_table;
}
?>