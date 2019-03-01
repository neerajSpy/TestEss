<?php

// namespace db_opertion;
class User
{

    private $date;

    private $con;

    public function __construct()
    {
        include_once 'config/NewConfig.php';
        include_once 'config/constant.php';
        date_default_timezone_set('Asia/Kolkata');
        $db = new NewConfig();
        $this->con = $db->dbConnect();
        $this->date = date("Y-m-d H:i:s");
    }

    public function changePassword($userId, $email, $newPassword)
    {
        if ($this->isUserExist($email)) {
            $password = md5($newPassword);
            $result = $this->con->query("UPDATE `user` set `password` = '$password' where `id` = '$userId'");
            if ($result === TRUE) {
                return $this->con->affected_rows;
            } else {
                return QUERY_PROBLEM;
            }
        } else
            return UNAUTHORISED_USER;
    }

    public function login($email, $password, $token)
    {
        if ($this->isUserExist($email)) {
            
            
        } else {
            return UNAUTHORISED_USER;
        }
    }

    function getNameByUserId($userId)
    {
        $name = "";
        $result = $this->con->query("SELECT `name` from `user` where `id` = '$userId'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $name = $row['name'];
        }
        return $name;
    }
    
    function getEmailById($userId){
        $email = "";
        $result = $this->con->query("SELECT `email` from `user` where `id` = '$userId'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $email = $row['email'];
        }
        return $email;
    }
    
    function getTempEmailById($userId){
        $email = "";
        $result = $this->con->query("SELECT `temp_email` from `user` where `id` = '$userId'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $email = $row['temp_email'];
        }
        return $email;
    }
    
    function getRoleIdById($userId){
        $roleId = "";
        $result = $this->con->query("SELECT `role_id` from `user` where `id` = '$userId'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $roleId = $row['role_id'];
        }
        return $roleId;
    }
    
    function getUser($email,$password){
        $response = array();
        $password = md5($password);
        $result = $this->con->query("SELECT * from `user` where `email` = '$email' AND `password` = '$password'");
        if ($result->num_rows >0) {
            $row = $result->fetch_assoc();
            $response['error'] = false;
            $response['message'] = "Login successful!";
            $response['user_id'] = $row['id'];
            $response['user_role'] =  $row['user_role'];
            $response['role_id'] = $row['role_id'];
            $response['access_control_id'] = $row['access_control_id'];
            $response['name'] = $row['name'];
            $response['email'] = $row['email'];
            $response['mobile_number'] = $row['mobile_number'];
            $response['related_table'] = $row['related_table'];
            $baseLocation = $this->getBaseLocation($row['related_table'],$row['role_id']);
            /*echo "base_location ".$baseLocation;*/
            $response['base_office_location'] = $baseLocation;
            
        }
        return $response;
    }
    
    
    function getBaseLocation($related_table,$role_id){
        $baseLocation = "";
        $result = $this->con->query("SELECT * from $related_table where `id` = '$role_id'");
        if ($result->num_rows >0) {
            if ($row = $result->fetch_assoc()) {
                $baseLocation = $row['base_office_location'];
            }
        }
        return $baseLocation;
    }

    function getRelatedTableByUserId($userId)
    {
        $relatedTable = "";
        $result = $this->con->query("SELECT `related_table` from `user` where `id` = '$userId'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $relatedTable = $row['related_table'];
        }
        return $relatedTable;
    }

    function getRelatedTableByRoleId($roleId)
    {
        $relatedTable = "";
        $result = $this->con->query("SELECT * from `user` where `role_id` = '$roleId'");
        // echo "related table ".$result->num_rows." error ".$this->con->error;
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $relatedTable = $row['related_table'];
        }
        return $relatedTable;
    }

    function getOrgUnitId($roleId, $relatedTable)
    {
        $orgUnitId = 0;

        if (strlen(trim($relatedTable)) < 1) {
            $relatedTable = $this->getRelatedTableByRoleId($roleId);
        }
        $result = $this->con->query("SELECT * from $relatedTable where `id` = '$roleId'");
        // echo "user table row ".$result->num_rows." error ".$this->con->error;
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $orgUnitId = $row['org_unit_id'];
        }
        return $orgUnitId;
    }

    private function isUserExist($email)
    {
        $result = $this->con->query("SELECT `id` from `user` where `email` = '$email'");
        return $result->num_rows > 0;
    }
}

