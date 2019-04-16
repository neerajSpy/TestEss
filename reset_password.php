<?php
/**
 * Created by PhpStorm.
 * User: AC
 * Date: 4/15/2019
 * Time: 3:40 PM
 */
include "db_class/Password.php";

$email = $_POST['email'];
$db = new Password();
$result = $db->insertTempKey($email);

// echo "result ".$result."\n";
$response = array();
if ($result === FALSE) {
    $response['error'] = TRUE;
    $response['message'] = "Project already exist";

} else {
    $response['error'] = FALSE;
    $response['message'] = "Please check your mail";
}

echo json_encode($response);

?>