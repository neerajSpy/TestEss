<?php

// namespace db_opertion;
class Project
{

    private $date;

    private $con;

    private $isActive;

    private $deactive;

    public function __construct()
    {
        include_once 'config/NewConfig.php';
        include_once 'config/constant.php';

        $this->isActive = IS_ACTIVE;
        $this->deactive = DEACTIVE;

        $db = new NewConfig();
        $this->con = $db->dbConnect();
        date_default_timezone_set('Asia/Kolkata');
        $this->date = date("Y-m-d H:i:s");
    }

    function insertProject($projectJson)
    {
        $budgetAmount = 0;
        if (strlen(trim($projectJson->budget_amount)) > 0) {
            $budgetAmount = $projectJson->budget_amount;
        }

        $tlTsApproverId = 3;
        $currencyCode = "INR";
        $budgetType = "No Budget";

        $currencyCode = "INR";
        $isInternational = 0;
        if (strtolower($projectJson->country) != "india") {
            $state = "INT";
            $isInternational = 1;
        } else {
            $state = $projectJson->state;
        }

        
        if (! $this->isProjectExist($projectJson->project_name, $projectJson->project_type_id)) {

            $result = $this->con->query("INSERT into `master_zoho_project` (`Project Name`,`address`,
            `district`,`location`, `Description`,`client_name`,`Customer Name`, `cust_id`,
            `Currency Code`,`project_type_id`,`CF.Estimated Days`,`planned_start_date`,
            `planned_end_date`,`created_by`,`created_date`,`country`,`state`,`is_international`,
            `Billing Type`,`Budget Type`,`Project Budget Hours`,`Budget Amount`,`tl_ts_approver_id`)
            VALUES ('$projectJson->project_name','$projectJson->address','$projectJson->district',
            '$projectJson->location','$projectJson->description','$projectJson->client_name','$projectJson->customer_name',
            '$projectJson->cust_id','$currencyCode','$projectJson->project_type_id',
            '$projectJson->estimated_days','$projectJson->planned_start_date','$projectJson->planned_end_date',
            '$projectJson->created_by_id','$this->date','$projectJson->country','$state','$isInternational',
             '$projectJson->billing_type','$budgetType','$projectJson->project_budget_hours','$budgetAmount',$tlTsApproverId)");

            // echo "result " . $result->num_rows . " insert error " . $this->con->error . "\n";
            if ($result === TRUE) {
                $projectId = $this->con->insert_id;
                $this->insertProjectActivity($projectId, $projectJson->project_type_id, $projectJson->created_by_id);
                return $projectId;
            } else {
                return QUERY_PROBLEM;
            }
        } else {
            return EXIST;
        }
    }

    function approveProject($projectJson)
    {
        $budgetAmount = 0;
        if (strlen(trim($projectJson->budget_amount)) > 0) {
            $budgetAmount = $projectJson->budget_amount;
        }

        $tlTsApproverId = 3;
        $currencyCode = "INR";
        $budgetType = "No Budget";

        $currencyCode = "INR";
        $isInternational = 0;
        if (strtolower($projectJson->country) != "india") {
            $state = "INT";
            $isInternational = 1;
        } else {
            $state = $projectJson->state;
        }

        if (! $this->isProjectExist($projectJson->project_name, $projectJson->project_type_id)) {

            $result = $this->con->query("INSERT into `master_zoho_project` (`Project Name`,`address`,
            `district`,`location`, `Description`,`client_name`,`Customer Name`, `cust_id`,
            `Currency Code`,`project_type_id`,`CF.Estimated Days`,`planned_start_date`,
            `planned_end_date`,`created_by`,`created_date`,`modified_by`,`modified_date`,`country`,`state`,`is_international`,
            `Billing Type`,`Budget Type`,`Project Budget Hours`,`Budget Amount`,`tl_ts_approver_id`)
            VALUES ('$projectJson->project_name','$projectJson->address','$projectJson->district',
            '$projectJson->location','$projectJson->description','$projectJson->client_name','$projectJson->customer_name',
            '$projectJson->cust_id','$currencyCode','$projectJson->project_type_id',
            '$projectJson->estimated_days','$projectJson->planned_start_date','$projectJson->planned_end_date',
            '$projectJson->created_by_id','$this->date','$projectJson->modified_by_id','$this->date','$projectJson->country','$state','$isInternational',
             '$projectJson->billing_type','$budgetType','$projectJson->project_budget_hours','$budgetAmount',$tlTsApproverId)");

            // echo "result " . $result->num_rows . " insert error " . $this->con->error . "\n";
            if ($result === TRUE) {
                $projectId = $this->con->insert_id;
                $this->insertProjectActivity($projectId, $projectJson->project_type_id, $projectJson->created_by_id);
                $this->insertProjectUser($projectId, $projectJson->created_by_id, $projectJson->modified_by_id);
                $this->approveTempProject($projectJson->id, $projectJson->modified_by_id);
               
                $this->assignProjectToUser($projectId, $projectJson->project_type_id, $projectJson->created_by_id, $projectJson->modified_by_id);
               //  echo "activity user Id ".$activityUserId;
                return $projectId;
            } else {
                return QUERY_PROBLEM;
            }
        } else {
            $projectId = $this->getProjectId($projectJson->project_name, $projectJson->project_type_id);
            $this->approveTempProject($projectId, $projectJson->modified_by_id);
             
            $this->assignProjectToUser($projectId, $projectJson->project_type_id, $projectJson->created_by_id, $projectJson->modified_by_id);
            // echo "activity user Id ".$activityUserId;
            return $projectId;
        }
    }

    function insertProjectActivity($projectId, $projectTypeId, $userId)
    {
        $projectTypeResult = $this->con->query("SELECT * from `project_type_activity_type` WHERE `project_type_id` = '$projectTypeId'");
        if ($projectTypeResult->num_rows > 0) {

            while ($row = $projectTypeResult->fetch_array()) {
                $activityTypeId = $row['id'];

                $this->con->query("INSERT INTO `activity` (`project_id`,
                `project_type_id`,`activity_type_id`,`created_by_id`,`created_date`) 
                VALUES('$projectId','$projectTypeId','$activityTypeId',
                '$userId','$this->date')");

               //  echo " insert activity " . $this->con->insert_id . " error " . $this->con->error."\n";
            }
        }
    }

    function insertProjectUser($projectId, $userId, $createdById)
    {
        $this->con->query("INSERT into `project_user` (`project_id`,`user_id`,`created_by_id`,
        `created_date`) VALUES ('$projectId','$userId','$createdById','$this->date')");

         //echo "insert project User ".$this->con->insert_id." error ".$this->con->error."\n";
    }

    function fetchRequestedProject()
    {
        $response = array();
        $result = $this->con->query("SELECT rap.*, u.`name`, mzp.`Project Name`, mzp.`project_type_id` from `request_assign_project` as rap JOIN 
                  `user` u ON rap.`created_by_id` = u.`id` JOIN `master_zoho_project` as mzp on mzp.`id` = rap.`project_id` 
                  WHERE rap.`is_active` = '$this->isActive' ORDER BY rap.`created_date` DESC");

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($response, array(
                    "id" => $row['id'],
                    "project_id" => $row['project_id'],
                    "project_name" => $row['Project Name'],
                    "project_type_id" => $row['project_type_id'],
                    "created_by_id" => $row['created_by_id'],
                    "created_by" => $row['name']
                ));
            }
        }
        
        return $response;
    }

    function assignUserOnRequestedProject($projectId, $requestId, $projectTypeId, $userId, $modifiedById)
    {
        $result = $this->assignProjectToUser($projectId, $projectTypeId, $userId, $modifiedById);
        if ($result > 0) {
            $this->updateRequestedProjectStatus($requestId, $modifiedById);
        }
        return $result;
    }

    function assignProjectToUser($projectId, $projectTypeId, $userId, $createdById)
    {
        if (! $this->isProjectAssignToUser($projectId, $userId)) {

            $activityUserId = 0;
            $activityResult = $this->con->query("SELECT DISTINCT `id`,`activity_type_id` 
          from `activity` WHERE `project_id` = '$projectId' AND `project_type_id` = 
          '$projectTypeId'");

            if ($activityResult->num_rows > 0) {
                while ($row = $activityResult->fetch_assoc()) {

                    $id = $row['id'];
                    $activityTypeId = $row['activity_type_id'];

                    $result = $this->con->query("INSERT INTO `activity_user` (`project_id`,
                    `activity_id`,`activity_type_id`,`user_id`,`created_by_id`,
                    `created_date`) VALUES ('$projectId','$id','$activityTypeId',
                    '$userId','$createdById','$this->date')");

                   // echo "insert activity User ".$this->con->insert_id." error ".$this->con->error."\n";
                    if ($result === TRUE) {
                        $activityUserId = $this->con->insert_id;
                    }
                }
            }
            

            if ($activityUserId > 0) {
                return $activityUserId;
            } else {
                return QUERY_PROBLEM;
            }
        } else {
            return EXIST;
        }
    }

    function isProjectAssignToUser($projectId, $userId)
    {
        $result = $this->con->query("SELECT * FROM `activity_user` WHERE `project_id` = '$projectId' AND `user_id` = '$userId' AND `is_active` = '$this->isActive'");
        return $result->num_rows > 0;
    }

    function approveTempProject($projectId, $modifiedById)
    {
         $this->con->query("UPDATE `temp_project` set `is_active` = '$this->deactive', `modified_by` = '$modifiedById', `modified_date` = '$this->date' where `id` = '$projectId'");
        
        // echo "temp approve project ".$this->con->affected_rows." temp project error ".$this->con->error."\n";
        
    }

    function insertTempProject($projectJson)
    {
        $currencyCode = "INR";
        $isInternational = 0;
        if (strtolower($projectJson->country) != "india") {
            $state = "INT";
            $isInternational = 1;
        } else {
            $state = $projectJson->state;
        }

        if (! $this->isTempProjectExist($projectJson->project_name, $projectJson->project_type_id)) {

            $result = $this->con->query("INSERT into `temp_project` (`Project Name`,`address`,
            `district`,`location`, `Description`,`client_name`,`Customer Name`, `cust_id`,
            `Currency Code`,`project_type_id`,`CF.Estimated Days`,`planned_start_date`,
            `planned_end_date`,`created_by`,`created_date`,`country`,`state`,`is_international`)
            VALUES ('$projectJson->project_name','$projectJson->address','$projectJson->district',
            '$projectJson->location','$projectJson->description','$projectJson->client_name','$projectJson->customer_name',
            '$projectJson->cust_id','$currencyCode','$projectJson->project_type_id',
            '$projectJson->estimated_days','$projectJson->planned_start_date','$projectJson->planned_end_date',
            '$projectJson->created_by_id','$this->date','$projectJson->country','$state','$isInternational')");

            // echo "result ".$result->num_rows." insert error ".$this->con->error."\n";
            if ($result === TRUE) {
                return $this->con->insert_id;
            } else {
                return QUERY_PROBLEM;
            }
        } else {
            return EXIST;
        }
    }

    function updateTempProject($projectJson)
    {
        $currencyCode = "INR";
        $isInternational = 0;
        if (strtolower($projectJson->country) != "india") {
            $state = "INT";
            $isInternational = 1;
        } else {
            $state = $projectJson->state;
        }

        if (! $this->isUpdateTempExist($projectJson->id, $projectJson->project_name, $projectJson->project_type_id)) {

            $result = $this->con->query("UPDATE `temp_project` set `Project Name` = '$projectJson->project_name',
            `address` = '$projectJson->address',`district` = '$projectJson->district',
            `location` = '$projectJson->location', `Description`= '$projectJson->description',
            `client_name`= '$projectJson->client_name',`Customer Name` = '$projectJson->customer_name', 
            `cust_id` = '$projectJson->cust_id',`Currency Code` = '$currencyCode',
            `project_type_id` = '$projectJson->project_type_id',`CF.Estimated Days` = '$projectJson->estimated_days',
            `planned_start_date`= '$projectJson->planned_start_date',`planned_end_date` = '$projectJson->planned_end_date',
            `modified_by`= '$projectJson->modified_by_id',`modified_date` = '$this->date',
            `country` = '$projectJson->country',`state` = '$state',
            `is_international` = '$isInternational' where `id` = '$projectJson->id'");

            // echo "insert error ".$this->con->error."\n";
            if ($result === TRUE) {
                return $this->con->affected_rows;
            } else {
                return QUERY_PROBLEM;
            }
        } else {
            return EXIST;
        }
    }
    
    function getProjectNameById($id){
        $projectName = "";
        $result = $this->con->query("SELECT `Project Name` from `master_zoho_project` where `id` = '$id'");
        if($result->num_rows >0){
            $row = $result->fetch_assoc();
            $projectName = $row['Project Name'];
        }
        return $projectName;
    }
    
    function getProjectNameByTripId($id){
        $projectName = "";
        $result = $this->con->query("SELECT mzp.`Project Name` from `expense_trip` as et JOIN `master_zoho_project` as mzp on et.`project_id` = mzp.`id` where et.`id` = '$id'");
        if($result->num_rows >0){
            $row = $result->fetch_assoc();
            $projectName = $row['Project Name'];
        }
        return $projectName;
    }

    // request exiting project to assign
    function requestProjectToAssign($projectId, $userId)
    {
        $result = $this->con->query("INSERT INTO `request_assign_project` (`project_id`, `created_by_id`,
        `created_date`) VALUES ('$projectId','$userId','$this->date')");

        if ($result === TRUE) {
            return $this->con->insert_id;
        } else {
            return QUERY_PROBLEM;
        }
    }

    function updateRequestedProjectStatus($requestId, $modifiedById)
    {
        $this->con->query("UPDATE `request_assign_project` set `is_active` = '$this->deactive', `modified_by_id` = '$modifiedById',
        `created_date` = '$this->date' where `id` = '$requestId' ");
    }

    function isUpdateTempExist($projectId, $projectName, $projectTypeId)
    {
        $result = $this->con->query("SELECT * From `temp_project` WHERE `id` != '$projectId' AND LOWER(`Project Name`) = LOWER('$projectName') AND `project_type_id` = '$projectTypeId' AND `is_active` = '$this->isActive'");
        // echo "temp project ".$result->num_rows." temp project error ".$this->con->error;
        return $result->num_rows > 0;
    }

    function isTempProjectExist($projectName, $projectTypeId)
    {
        $result = $this->con->query("SELECT * From `temp_project` WHERE LOWER(`Project Name`) = LOWER('$projectName') AND `project_type_id` = '$projectTypeId' AND `is_active` = '$this->isActive'");
        // echo "temp project ".$result->num_rows." temp project error ".$this->con->error;
        return $result->num_rows > 0;
    }

    function isProjectExist($projectName, $projectTypeId)
    {
        $result = $this->con->query("SELECT * From `master_zoho_project` WHERE LOWER(`Project Name`) = LOWER('$projectName') AND `project_type_id` = '$projectTypeId' AND `is_active` = '$this->isActive'");
        // echo "project ".$result->num_rows." project error ".$this->con->error;
        return $result->num_rows > 0;
    }

    function getProjectId($projectName, $projectTypeId)
    {
        $projectId = 0;
        $result = $this->con->query("SELECT * From `master_zoho_project` WHERE `Project Name` ='$projectName' AND `project_type_id` = '$projectTypeId' AND `is_active` = '$this->isActive'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $projectId = $row['id'];
        }
        return $projectId;
    }

    function getProjectArray($projectId)
    {
        $response = array();
        $result = $this->con->query("SELECT * from `master_zoho_project` where `id` = '$projectId' AND `is_active` = '$this->isActive'");
        if ($result->num_rows) {
            $row = $result->fetch_assoc();
            array_push($response, $row);
        }
        return $response;
    }

    private function getProjectTypeId($projectId)
    {
        $projectTypeId = 0;
        $result = $this->con->query("SELECT * From `master_zoho_project` WHERE `id` ='$projectId'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $projectTypeId = $row['projectTypeId'];
        }
        return $projectTypeId;
    }
}

?>