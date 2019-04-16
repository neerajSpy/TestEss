<?php
include "config/config.php";

$user_id = $_POST['user_id'];
$response = array();

$exist_response = array();
$non_exist_response = array();

$project_type_list = $con->query("SELECT DISTINCT(`project_id`) as `id` FROM `activity_user` WHERE `user_id` = '$user_id'");

if ($project_type_list->num_rows >0) {
    while ($row = $project_type_list->fetch_array()) {
        $projectId = $row['id'];
        $resResponse = getProjectName($con, $projectId);
        if (isset($resResponse)) {
        array_push($response,$resResponse);  	
        }
      

    }
}

echo json_encode($response);

function getProjectName($con,$project_id){
    $response = array();
    $result = $con->query("SELECT mzp.*, u.`name`, pt.`type` from `master_zoho_project` as mzp join `project_type` as pt on mzp.`project_type_id` = pt.`id` join user as u on mzp.`created_by_id` = u.`id` where  mzp.`id` = '$project_id'");

    if ($result->num_rows >0){
        if ($row = $result->fetch_assoc()){
             $response['id']=$row['id'];
             $response['project_name']=$row['project_name'];
             $response['project_type_id']=$row['project_type_id'];
             $response['project_type']=$row['type'];
             $response['Description']=$row['description'];
             $response['billing_type']=$row['billing_type'];
             $response['project_cost']=$row['project_cost'];
             $response['customer_name']=$row['customer_name'];
             $response['cust_id']=$row['cust_id'];
             $response['client_name']=$row['client_name'];
             $response['currency_code']=$row['currency_code'];
             $response['budget_type']=$row['budget_type'];
             $response['budget_amount']=$row['budget_amount'];
             $response['project_budget_hours']=$row['project_budget_hours'];
             $response['estimated_days']=$row['estimated_days'];
             $response['tl_ts_approver_id']=$row['tl_ts_approver_id'];
             $response['location']=$row['location'];
             $response['address']=$row['address'];
             $response['district']=$row['district'];
             $response['state'] = $row['state'];
             $response['country'] = $row['country'];
             $response['is_international'] = $row['is_international'];
             $response['is_job_allocation_sheet']=$row['is_job_allocation_sheet'];
             $response['is_system_backup']=$row['is_system_backup'];
             $response['planned_start_date']=$row['planned_start_date'];
             $response['planned_end_date']=$row['planned_end_date'];
             $response['created_by_id']=$row['created_by_id'];
             $response['name']=$row['name'];
             $response['modified_by_id']=$row['modified_by_id'];


        }
    }
    return $response;
}


?>