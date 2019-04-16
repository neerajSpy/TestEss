<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 9/8/2018
 * Time: 12:54 PM
 */

include "config/config.php";
date_default_timezone_set('Asia/Kolkata');

$id = $_POST['id'];
$user_id = $_POST['user_id'];

$response = array();

$checkAutoriseArr = checkUserValidation($con,$user_id);
if ($checkAutoriseArr['error'] == false){
    //SELECT `id`, `policy_name`, `base_url`, `file_url`,
    // `created_date`, `created_by_id`, `modified_date`, `modified_by_id` FROM `privacy_policy` WHERE 1
    $result = $con->query("SELECT * from `privacy_policy` where `id` = '$id'");
    if ($result->num_rows >0){
        if ($row = $result->fetch_assoc()){
            $response['error'] = false;
            $response['message'] = $checkAutoriseArr['message'];
            $response['base_url'] = $row['base_url'];
            $response['file_url'] = $row['file_url'];
            $response['download_file_url'] = $row['download_fle_url'];

        }
    }else{
        $response['error'] = true;
        $response['message'] = "file not found";
        $response['base_url'] = "Null";
        $response['file_url'] = "Null";
    }


}else{
    $response['error'] = true;
    $response['message'] = $checkAutoriseArr['message'];
    $response['base_url'] = "Null";
    $response['file_url'] = "Null";
}

echo json_encode($response);

function checkUserValidation($con,$userId){
    $response = array();
    if ($userId != ""){

        $result = $con->query("SELECT * from `user` where `id` = '$userId'");

        if ($result->num_rows > 0 ){
            $response['error'] = false;
            $response['message'] = "Authorised used";
        }else{
            $response['error'] = true;
            $response['message'] = "Unauthorised user";
        }
    }else{
        $response['error'] = true;
        $response['message'] = "User id is required";

    }
    return $response;
}

