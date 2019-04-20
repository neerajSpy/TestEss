<?php

// namespace db_opertion;
class Project
{

    private $date;

    private $con;

    private $isActive;

    private $billingTypeArray;

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

        $this->billingTypeArray = array(
            'based_on_staff_hours' => 'Based on Staff Hours',
            'fixed_cost_for_project' => 'Fixed Cost for Project',
            'based_on_project_hours' => 'Based on Project Hours',
            "based_on_task_hours" => 'Based on Task Hours'
        );
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


        if (!$this->isProjectExist($projectJson->project_name, $projectJson->project_type_id)) {

            $result = $this->con->query("INSERT into `master_zoho_project` (`project_name`,`address`,
            `district`,`location`, `description`,`client_name`,`customer_name`, `cust_id`,
            `currency_code`,`project_type_id`,`estimated_days`,`planned_start_date`,
            `planned_end_date`,`created_by_id`,`created_date`,`country`,`state`,`is_international`,
            `billing_type`,`budget_type`,`project_budget_hours`,`budget_amount`,`tl_ts_approver_id`)
            VALUES ('$projectJson->project_name','$projectJson->address','$projectJson->district',
            '$projectJson->location','$projectJson->description','$projectJson->client_name','$projectJson->customer_name',
            '$projectJson->cust_id','$currencyCode','$projectJson->project_type_id',
            '$projectJson->estimated_days','$projectJson->planned_start_date','$projectJson->planned_end_date',
            '$projectJson->created_by_id','$this->date','$projectJson->country','$state','$isInternational',
             '$projectJson->billing_type','$budgetType','$projectJson->project_budget_hours','$budgetAmount',$tlTsApproverId)");

            // echo "result " . $result->num_rows . " insert error " . $this->con->error . "\n";
            if ($result === TRUE) {
                $projectId = $this->con->insert_id;
                //$this->insertProjectActivity($projectId, $projectJson->project_type_id, $projectJson->created_by_id);
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

        if (!$this->isProjectExist($projectJson->project_name, $projectJson->project_type_id)) {

            $result = $this->con->query("INSERT into `master_zoho_project` (`project_name`,`address`,
            `district`,`location`, `description`,`client_name`,`customer_name`, `cust_id`,
            `currency_code`,`project_type_id`,`estimated_days`,`planned_start_date`,
            `planned_end_date`,`created_by_id`,`created_date`,`modified_by_id`,`modified_date`,`country`,`state`,`is_international`,
            `billing_type`,`budget_type`,`project_budget_hours`,`budget_amount`,`tl_ts_approver_id`)
            VALUES ('$projectJson->project_name','$projectJson->address','$projectJson->district',
            '$projectJson->location','$projectJson->description','$projectJson->client_name','$projectJson->customer_name',
            '$projectJson->cust_id','$currencyCode','$projectJson->project_type_id',
            '$projectJson->estimated_days','$projectJson->planned_start_date','$projectJson->planned_end_date',
            '$projectJson->created_by_id','$this->date','$projectJson->modified_by_id','$this->date','$projectJson->country','$state','$isInternational',
             '$projectJson->billing_type','$budgetType','$projectJson->project_budget_hours','$budgetAmount',$tlTsApproverId)");

            // echo "result " . $result->num_rows . " insert error " . $this->con->error . "\n";
            if ($result === TRUE) {
                $projectId = $this->con->insert_id;
                //$this->insertProjectActivity($projectId, $projectJson->project_type_id, $projectJson->created_by_id);
                // $this->insertProjectUser($projectId, $projectJson->created_by_id, $projectJson->modified_by_id);
                $this->approveTempProject($projectJson->id, $projectJson->modified_by_id);

                //$this->assignProjectToUser($projectId, $projectJson->project_type_id, $projectJson->created_by_id, $projectJson->modified_by_id);
                //  echo "activity user Id ".$activityUserId;
                return $projectId;
            } else {
                return QUERY_PROBLEM;
            }
        } else {
            return EXIST;
        }
    }

    function fetchProject($pageNumber,$searchText){

        $limit = 25;
        if ((isset($pageNumber)) && ($pageNumber > 0)) {
            $offest = ($pageNumber - 1) * $limit;
        } else {
            $offest = 0;
        }

        $response = array();

        if ($searchText != ''){
            $result = $this->con->query("SELECT  mzp.*, u.`name` FROM `master_zoho_project` as mzp JOIN `user` as u on mzp.`created_by_id` = u.`id` WHERE 
        (mzp.`id` LIKE '%$searchText%' OR LOWER(mzp.`project_name`) like LOWER('%$searchText%') OR LOWER(u.`name`) LIKE LOWER('%$searchText%'))
        AND  mzp.`is_active` = '$this->active' ORDER BY mzp.`id` DESC limit $offest , $limit");

        }else {
            $result = $this->con->query("SELECT  mzp.*, u.`name` FROM `master_zoho_project` as mzp JOIN `user` as u
        on mzp.`created_by_id` = u.`id` WHERE mzp.`is_active` = '$this->active' ORDER BY mzp.`id` DESC limit $offest , $limit");
        }

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($response, $row);
            }
        }

        return $response;

    }

    function insertProjectActivity($projectId, $projectTypeId, $activityJson, $userId, $createdById)
    {
        $lastId = 0;
        foreach ($activityJson as $value) {
            $result = $this->con->query("INSERT INTO `activity` (`project_id`,`project_type_id`,`activity_type_id`, `name`,`budget_hour`,
                `planned_start_date`, `planned_end_date`,`created_by_id`,`created_date`) VALUES('$projectId','$projectTypeId','$value->activity_type_id',
                 '$value->activity_type','$value->project_budget_hours', '$value->planned_start_date', '$value->planned_end_date','$createdById','$this->date')");

            if ($result === TRUE) {
                $activityId = $this->con->insert_id;
                $lastId = $activityId;
                $this->assignProjectToUser($projectId, $activityId, $value->activity_type_id, $userId, $createdById);
            }
        }

        $this->insertProjectUser($projectId, $userId, $createdById);
        
        return $lastId > 0;
    }


    function assignProjectToUser($projectId, $activityId, $activityTypeId, $userId, $createdById)
    {
        $this->con->query("INSERT INTO `activity_user` (`project_id`,`activity_id`,`activity_type_id`,`user_id`,`created_by_id`,
                    `created_date`) VALUES ('$projectId','$activityId','$activityTypeId','$userId','$createdById','$this->date')");
        
    }

    function insertProjectUser($projectId, $userId, $createdById)
    {
        $result = $this->con->query("SELECT `id` from `project_user` WHERE `project_id` = '$projectId' AND `user_id` = '$userId'");

        if ($result->num_rows < 1) {
            $this->con->query("INSERT into `project_user` (`project_id`,`user_id`,`created_by_id`,
        `created_date`) VALUES ('$projectId','$userId','$createdById','$this->date')");
        }

        //echo "insert project User ".$this->con->insert_id." error ".$this->con->error."\n";
    }

    function fetchRequestedProject()
    {
        $response = array();
        $result = $this->con->query("SELECT rap.*, u.`name`, mzp.`project_name`, mzp.`project_type_id` from `request_assign_project` as rap JOIN 
                  `user` u ON rap.`created_by_id` = u.`id` JOIN `master_zoho_project` as mzp on mzp.`id` = rap.`project_id` 
                  WHERE rap.`is_active` = '$this->isActive' ORDER BY rap.`created_date` DESC");

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($response, array(
                    "id" => $row['id'],
                    "project_id" => $row['project_id'],
                    "project_name" => $row['project_name'],
                    "project_type_id" => $row['project_type_id'],
                    "created_by_id" => $row['created_by_id'],
                    "created_by" => $row['name']
                ));
            }
        }

        return $response;
    }

    function fetchActivityType($projectId, $projectTypeId, $billingType)
    {
        $response = array();
        if (strtolower($billingType) == strtolower($this->billingTypeArray['based_on_staff_hours'])) {

            $result = $this->con->query("SELECT `id`, `activity_type` FROM `project_type_activity_type` WHERE `project_type_id` = '$projectTypeId'");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    array_push($response, array("id" => 0, "activity_type_id" => $row['id'], "activity_type" => $row['activity_type'], "project_budget_hour" => 0,
                        "planned_start_date" => "", "planned_end_date" => ""));
                }
            }


        } else {
            $result = $this->con->query("SELECT `id`,`activity_type_id`, `name`, `budget_hour`, `planned_start_date` , `planned_end_date` 
            from `activity` WHERE `project_id` = '$projectId' AND `project_type_id` = '$projectTypeId'");

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    array_push($response, array("id" => $row['id'], "activity_type_id" => $row['activity_type_id'], "activity_type" => $row['name'], "project_budget_hour" => $row['budget_hour'],
                        "planned_start_date" => $row['planned_start_date'], "planned_end_date" => $row['planned_end_date']));
                }
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


    function isProjectAssignToUser($projectId, $userId)
    {
        $result = $this->con->query("SELECT * FROM `activity_user` WHERE `project_id` = '$projectId' AND `user_id` = '$userId' AND `is_active` = '$this->isActive'");
        return $result->num_rows > 0;
    }

    function approveTempProject($projectId, $modifiedById)
    {
        $this->con->query("UPDATE `temp_project` set `is_active` = '$this->deactive', `modified_by_id` = '$modifiedById', `modified_date` = '$this->date' where `id` = '$projectId'");

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

        if (!$this->isTempProjectExist($projectJson->project_name, $projectJson->project_type_id)) {

            $result = $this->con->query("INSERT into `temp_project` (`project_name`,`address`,
            `district`,`location`, `description`,`client_name`,`customer_name`, `cust_id`,
            `currency_code`,`project_type_id`,`estimated_days`,`planned_start_date`,
            `planned_end_date`,`created_by_id`,`created_date`,`country`,`state`,`is_international`)
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

        if (!$this->isUpdateTempExist($projectJson->id, $projectJson->project_name, $projectJson->project_type_id)) {

            $result = $this->con->query("UPDATE `temp_project` set `project_name` = '$projectJson->project_name',
            `address` = '$projectJson->address',`district` = '$projectJson->district',
            `location` = '$projectJson->location', `Description`= '$projectJson->description',
            `client_name`= '$projectJson->client_name',`customer_name` = '$projectJson->customer_name', 
            `cust_id` = '$projectJson->cust_id',`currency_code` = '$currencyCode',
            `project_type_id` = '$projectJson->project_type_id',`estimated_days` = '$projectJson->estimated_days',
            `planned_start_date`= '$projectJson->planned_start_date',`planned_end_date` = '$projectJson->planned_end_date',
            `modified_by_id`= '$projectJson->modified_by_id',`modified_date` = '$this->date',
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

    function getProjectNameById($id)
    {
        $projectName = "";
        $result = $this->con->query("SELECT `project_name` from `master_zoho_project` where `id` = '$id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $projectName = $row['project_name'];
        }
        return $projectName;
    }

    function getProjectNameByTripId($id)
    {
        $projectName = "";
        $result = $this->con->query("SELECT mzp.`project_name` from `expense_trip` as et JOIN `master_zoho_project` as mzp on et.`project_id` = mzp.`id` where et.`id` = '$id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $projectName = $row['project_name'];
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
        `modified_date` = '$this->date' where `id` = '$requestId' ");
    }

    function isUpdateTempExist($projectId, $projectName, $projectTypeId)
    {
        $result = $this->con->query("SELECT `id` From `temp_project` WHERE `id` != '$projectId' AND LOWER(`project_name`) = LOWER('$projectName') AND `project_type_id` = '$projectTypeId' AND `is_active` = '$this->isActive'");
        // echo "temp project ".$result->num_rows." temp project error ".$this->con->error;
        return $result->num_rows > 0;
    }

    function isTempProjectExist($projectName, $projectTypeId)
    {
        $result = $this->con->query("SELECT `id` From `temp_project` WHERE LOWER(`project_name`) = LOWER('$projectName') AND `project_type_id` = '$projectTypeId' AND `is_active` = '$this->isActive'");
        // echo "temp project ".$result->num_rows." temp project error ".$this->con->error;
        return $result->num_rows > 0;
    }

    function isProjectExist($projectName, $projectTypeId)
    {
        $result = $this->con->query("SELECT `id` From `master_zoho_project` WHERE LOWER(`project_name`) = LOWER('$projectName') AND `project_type_id` = '$projectTypeId' AND `is_active` = '$this->isActive'");
        // echo "project ".$result->num_rows." project error ".$this->con->error;
        return $result->num_rows > 0;
    }

    function getProjectId($projectName, $projectTypeId)
    {
        $projectId = 0;
        $result = $this->con->query("SELECT `id` From `master_zoho_project` WHERE `project_name` ='$projectName' AND `project_type_id` = '$projectTypeId' AND `is_active` = '$this->isActive'");
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

    private
    function getProjectTypeId($projectId)
    {
        $projectTypeId = 0;
        $result = $this->con->query("SELECT `project_type_id` From `master_zoho_project` WHERE `id` ='$projectId'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $projectTypeId = $row['project_type_id'];
        }
        return $projectTypeId;
    }
}

?>