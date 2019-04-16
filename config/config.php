<?php
include_once 'db_constant.php';
$con = mysqli_connect(HOST_NAME,USERNAME,PASSWORD,DB_NAME);
if (!$con){
    die("Database Connection Failed" . mysqli_error($connection));
}
/*$select_db = mysqli_select_db($connection, 'f9e7n3i6_hospitalsafety');
if (!$select_db){
    die("Database Selection Failed" . mysqli_error($connection));
}*/

?>