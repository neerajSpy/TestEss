<?php
include "config/config.php";
date_default_timezone_set('Asia/Kolkata');
$project_id = $_POST['project_id'];
$project_activity_json = json_decode($_POST['project_activity_json']);
$user_id = $_POST['user_id'];
$created_by_id = $_POST['created_by_id'];

$response = array();

$now = new DateTime();
$current_date = $now->format('Y-m-d H:i:s');

//SELECT `id`, `project_id`, `activity_id`, `activity_type_id`, `user_id`, `created_by_id`, `modified_by_id`, `is_active`, `created_date`, `modified_date` FROM `activity_user` WHERE 1


$activity_user_id = 0;

$inv_row = $con->query("SELECT * FROM `activity_user` WHERE `project_id` = '$project_id' AND `user_id` = '$user_id' AND `is_active` = '1'");
if ($inv_row->num_rows < 1) {

    $assign_project = $con->query("INSERT INTO `project_user` (`project_id`,`user_id`,`create_by_id`,`create_date`) VALUES ('$project_id','$user_id','$created_by_id','$current_date')");
    foreach ($project_activity_json as $value) {

        $insert_query = "INSERT INTO `activity_user` (`project_id`,`activity_id`,`activity_type_id`,`user_id`,`created_by_id`,`created_date`) VALUES ('$project_id','$value->id','$value->activity_type_id','$user_id','$created_by_id','$current_date')";
        if ($con->query($insert_query) === TRUE) {
            $activity_user_id = $con->insert_id;
        }
    }
} else {
    $response['error'] = true;
    $response['message'] = "Project already assigned.";
}

if ($activity_user_id > 0) {
    $response['error'] = false;
    $response['message'] = "Successfully Saved.";

sendNotification($con,$user_id,$project_id);

} else {
    $response['error'] = true;
    $response['message'] = "Duplicate entries found for same user and project, hence project not saved.";
}

echo json_encode($response);



function getUserName($con,$user_id){
    $userName = "";
    $result = $con->query("SELECT * from `user` where `id` = '$user_id'");
    if ($result->num_rows >0) {
        if($row = $result->fetch_array()){
            $userName = $row['name'];

        }
    }
    return $userName;
}


function getProjectName($con,$project_id){
    $projectName = "";
    $result = $con->query("SELECT * from `master_zoho_project` WHERE  `id` = '$project_id'");
    if ($result->num_rows >0) {
        if ($row = $result->fetch_assoc()) {
            $projectName = $row['Project Name'];
        }
    }
return $projectName;
}

function sendNotification($con,$user_id,$project_id){
    $userName = getUserName($con,$user_id);
    $projectName =getProjectName($con,$project_id);
    
    $ss= $con->query("SELECT * FROM `user_fcm_token` WHERE `user_id` = '$user_id'");  

    $ch = curl_init("https://fcm.googleapis.com/fcm/send");

    $serverKey = "AIzaSyDJ0MiSBWBsQN5y-ybhWr2GNGFzTPsSfFQ";

    $notificationArr = array();
    array_push($notificationArr,array("project_name"=>$projectName));

    $notification = array("body" => array("module"=>'Assign Project',"json_response"=>$notificationArr));

    while($r= ($ss->fetch_array())) {
        
        $f = $r['token'];
        $arrayToSend = array('to' => $f, 'data' => $notification);

        $json = json_encode($arrayToSend);
      // echo $json;
        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: key= $serverKey";
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if($result === false)
        {
            //echo  'Curl failed ' . curl_error($ch);
        }

    }
    curl_close($ch);
}

?>