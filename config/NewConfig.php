<?php
//namespace config;

class NewConfig{

    public function __construct()
    {
        
    }
    
    public function dbConnect(){
        include "db_constant.php";
        $con = mysqli_connect(HOST_NAME,USERNAME,PASSWORD,DB_NAME);
        return $con;
    }
}

/* $db = new NewConfig();
 $con = $db->dbConnect();
if (!$con){
    echo "error ".mysqli_error($con)." code ".mysqli_connect_error();
    die("Database Connection Failed" . mysqli_error($con));
}else{
    echo "connected";
} */ 
?>
