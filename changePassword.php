<?php
//use db_opertion\User;

include "config/config.php";
include "config/constant.php";
include "db_class/User.php";


$userId = $_POST['user_id'];
$email = $_POST['email'];
$newPassword = $_POST['new_password'];

$db = new User($con);
$result = $db->changePassword($userId, $email, $newPassword);
$intResult = (int)$result;

$response = array();
if ($intResult == 0) {
    $response['error'] = FALSE;
    $response['message'] = "Successfully password changed.";
}elseif ($intResult == 1){
    $response['error'] = TRUE;
    $response['message'] = "Password not changed.";
}elseif ($intResult == 2){
    $response['error'] = TRUE;
    $response['message'] = "Unauthorised user.";
}
echo json_encode($response);
?>