<?php
/**
 * Created by PhpStorm.
 * User: AC
 * Date: 4/4/2019
 * Time: 2:01 PM
 */

include "config/constant.php";
include "db_class/User.php";


$action = $_POST['action'];

if (strtolower($action) == 'fetch' ) {
    if (!checkMandatoryParameter(array('action'))){

        $db = new User();
        $response = $db->fetchUser();
        // echo "result ".$result."\n";
        echo json_encode($response);
    }
}





function checkMandatoryParameter($requiredFields){
    $error = FALSE;
    $errorField = "";

    foreach ($requiredFields as $requiredField){
        if(strlen(trim($_POST[$requiredField])) <1){
            $errorField .= $requiredField.", ";
            $error = TRUE;
        }
    }

    $response = array();
    if ($error) {
        $response['error'] = TRUE;
        $response['message'] = "missing required fields ".$errorField;

        echo json_encode($response);
    }

    return $error;
}




?>