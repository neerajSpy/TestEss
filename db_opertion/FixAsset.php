<?php

// namespace db_opertion;

/*
 *
 * SELECT `id`, `fix_asset_id`, `user_id`, `assign_date`, `asset_name`, `unit`,
 * `* description`, `created_by_id`, `created_date`, `modified_by_id`, `modified_date`,
 * `* fix_asset_status_id` FROM `fix_asset_allocation` WHERE 1
 *
 * SELECT `id`, `fix_asset_allocation_id`, `fix_asset_status_id`, `remark`, `reason`,
 * created_date`, `created_by_id` FROM `fix_asset_allocation_log` WHERE 1
 *
 * SELECT `id`, `org_unit_fix_asset_id`, `fix_asset_status_id`, `remark`,
 * `created_by_id`, `created_date`, `modified_by_id`, `modified_date`, `is_active`,
 * `comment` FROM `fix_asset_request` WHERE 1
 *
 * SELECT `id`, `fix_asset_request_id`, `fix_asset_status_id`, `data`, `created_by_id`,
 * `created_date` FROM `fix_asset_request_log` WHERE 1
 *
 * SELECT `id`, `name` FROM `fix_asset_status` WHERE 1
 */
class FixAsset
{

    private $con;

    private $date;

    private $basePath;

    private $orgType;

    private $fixAsset;

    private $statusIdArray;

    public function __construct()
    {
        include_once 'config/NewConfig.php';
        include_once 'config/constant.php';
        $db = new NewConfig();
        $this->con = $db->dbConnect();
        date_default_timezone_set('Asia/Kolkata');
        $this->date = date("Y-m-d H:i:s");
        $this->basePath = "http://ess.technitab.in/web_service/ESS/";
        $this->orgType = $this->basePath . "org_asset/";
        $this->fixAsset = $this->basePath . "fix_asset/";

        $this->setFixAssetStatus();
    }

    private function setFixAssetStatus()
    {
        $this->statusIdArray = array(
            'request_id' => 1,
            'approve_id' => 2,
            'reject_id' => 3,
            'cancelled_id' => 4,
            'reutrn_id' => 5,
            'approve_return_id' => 6
        );
    }

    function requestFixAsset($orgUnitAssetId, $remark, $userId)
    {
        $statusId = $this->statusIdArray[request_id];
        $result = $this->con->query("INSERT INTO `fix_asset_allocation`(`org_unit_fix_asset_id`, 
          `fix_asset_status_id`, `description`, `created_by_id`, `created_date`) 
          VALUES ('$orgUnitAssetId','$this->statusIdArray[request_id]','$remark','$userId','$this->date')");

        if ($result === TRUE) {
            $fixAssetRequestId = $this->con->insert_id;
            $this->createAssetRequestLog($fixAssetRequestId, $statusId, $remark, $userId);
            return $fixAssetRequestId;
        } else {
            return QUERY_PROBLEM;
        }
    }

    private function createAssetRequestLog($fixAssetRequestId, $fixAssetStatusId, $data, $createdById)
    {
        $this->con->query("INSERT INTO `fix_asset_allocation_log`(`fix_asset_allocation_id`, 
        `fix_asset_status_id`, `data`, `created_by_id`, `created_date`) VALUES ('$fixAssetRequestId', 
        '$fixAssetStatusId','$data','$createdById','$this->date')");
    }

    function getFixAssetType($roleId, $relatedTable)
    {
        $response = array();
        include_once 'db_class/User.php';
        $db = new User();

        $orgUnitId = $db->getOrgUnitId($roleId, $relatedTable);

        // SELECT `id`, `fix_asset`, `description`, `primary_org`, `automation_org`, `control_relay_org`, `it_admin_org`, `office_guest_house_org`, `image_path` FROM `org_unit_fix_assets` WHERE 1
        $result = $this->con->query("SELECT `primary_org`,`automation_org`, `control_relay_org`, `it_admin_org`, `image_path`,`fix_asset`,`description`,`id` from `org_unit_fix_assets`");
        // echo " num rows ".$result->num_rows." error ".$this->con->error;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array()) {
                if ($row[0] == 1 || $row[$orgUnitId - 1] == 1) {
                    array_push($response, array(
                        "id" => $row['id'],
                        "fix_asset" => $row['fix_asset'],
                        "description" => $row['description'],
                        "image_path" => $this->orgType . $row['image_path']
                    ));
                }
            }
        }
        return $response;
    }

    function getAssignAssets($userId)
    {
        $response = array();

        include_once 'db_class/User.php';
        $db = new User();

        $name = $db->getNameByUserId($userId);

        $sql = "";
        if ($userId == '0') {
            $sql = "SELECT  faa.`assign_date`,faa.`id`,faa.`user_id`,fa.`type_id`,fa.`asset_type`, 
            fa.`description`, fa.`make`,fa.`model`,fa.`attachment_path` FROM `fix_assests` fa JOIN 
            `fix_asset_allocation` faa on fa.`id` = faa.`fix_asset_id` AND faa.`fix_asset_status_id` = 
            '$this->statusIdArray[approve_id]'";
        } else {
            $sql = "SELECT  faa.`assign_date`,faa.`id`,faa.`user_id`,fa.`type_id`,fa.`asset_type`, 
            fa.`description`, fa.`make`,fa.`model`,fa.`attachment_path` FROM `fix_assests` fa JOIN 
            `fix_asset_allocation` faa on fa.`id` = faa.`fix_asset_id` AND faa.`user_id` = '$userId' AND 
            faa.`fix_asset_status_id` = '$this->statusIdArray[approve_id]'";
        }
        $result = $this->con->query($sql);
        // echo "fix allocation result ".$result->num_rows." error ".$this->con->error;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($response, array(
                    "id" => $row['id'],
                    "user_id" => $row['user_id'],
                    "name" => $name,
                    "assign_date" => $row['assign_date'],
                    "type_id" => $row['type_id'],
                    "asset_type" => $row['asset_type'],
                    "description" => $row['description'],
                    "model" => $row['model'],
                    "attachment_path" => $this->fixAsset . $row['attachment_path']
                ));
            }
        }
        return $response;
    }

    function fetchRequestedFixAssets()
    {
        
    }

   

    function getFixAssetByOrgId($orgUnitId)
    {
        $respnose = array();
        $result = $this->con->query("SELECT `id`, `type_id`, `asset_type`, `description`, `make`, `model`, `serial_number`, `supplier` from `fix_assets` where `type_id` = '$orgUnitId'");
        // echo "org result ".$result->num_rows." error ".$this->con->error;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($respnose, $row);
            }
        }
        return $respnose;
    }
}

