<?php

include "config/config.php";

date_default_timezone_set('Asia/Kolkata');

$email = $_POST['email'];
$password = $_POST['password'];
$token = $_POST['token'];
$response = array();
$current_date = getAppliedDate();

if (isEmailValid($con,$email)) {
    if (isUserValid($con,$email,$password)) {
    
$sql_query = "SELECT * from `user` where `email` = '$email' AND `password` = '$password' AND `is_active` = '1'";

$result = $con->query($sql_query);

if($result->num_rows >0){

    if ($row = $result->fetch_array()) {
        $user_id = $row['id'];
        if ($token != "")

        $con->query("INSERT into `user_fcm_token` (`user_id`,`token`,`created_date`) VALUES ('$user_id','$token','$current_date')");
    	$response['error'] = false;
    	$response['message'] = "Login successful!";
        $response['user_id'] = $row['id'];
        $response['user_role'] =  $row['user_role'];
        $response['role_id'] = $row['role_id'];
        $response['access_control_id'] = $row['access_control_id'];
        $response['name'] = $row['name'];
        $response['email'] = $row['email'];
        $response['mobile_number'] = $row['mobile_number'];
        $response['related_table'] = $row['related_table'];
        $baseLocation = getBaseLocation($con,$row['related_table'],$row['role_id']);
        /*echo "base_location ".$baseLocation;*/
        $response['base_office_location'] = $baseLocation;
    }
}
}else{
    $response['error'] = true;
    $response['message'] = "Email or password incorrect!";
}

}else{
    $response['error'] = true;
    $response['message'] = "Unauthorised User!";

}

echo json_encode($response);
function getAppliedDate(){

    $now = new DateTime();

    return $now->format('Y-m-d H:i:s');

}


function getBaseLocation($con,$related_table,$role_id){
    $baseLocation = "";
    $result = $con->query("SELECT * from $related_table where `id` = '$role_id'");
    if ($result->num_rows >0) {
        if ($row = $result->fetch_assoc()) {
            $baseLocation = $row['base_office_location'];
        }
    }
    return $baseLocation;
}

function isEmailValid($con,$email){
    $result = $con->query("SELECT * from `user` where `email` = '$email'");
    return $result->num_rows >0?true:false;
}


function isUserValid($con,$email,$password){
    $result = $con->query("SELECT * from `user` where `email` = '$email' AND `password` = '$password'");
    return $result->num_rows >0?true:false;
}

?>