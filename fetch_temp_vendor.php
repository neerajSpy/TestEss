    <?php
    
    include "config/config.php";
    date_default_timezone_set('Asia/Kolkata');

    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    $sql_query = "";
    if (strtolower(trim($action)) == "approve") {
        $sql_query = "SELECT tv.*, u.`name` from `temp_vendor` as tv JOIN `user` as u on tv.`created_by_id` = u.`id` where tv.`is_active` = '0' ORDER BY tv.`id` DESC";
    }else if(strtolower(trim($action)) == "submit"){
        $sql_query = "SELECT tv.*, u.`name` from `temp_vendor` as tv JOIN `user` as u on tv.`created_by_id` = u.`id` where tv.`created_by_id` = '$user_id' AND tv.`is_active` = '0' ORDER BY tv.`id` DESC";
    }
    
    $response = array();

    $result = $con->query($sql_query);

    if ($result->num_rows >0) {
        while ($row = $result->fetch_assoc()) {

             $gst_treatment = $row['gst_treatment'];
            if ($gst_treatment == "business_gst") {
                $gst_treatment = "Registered Business - Regular";
            }else {
                $gst_treatment = "Unregistered Business";
            }

            array_push($response,array("id"=>$row['id'],"vendor_type_id"=>"0","vendor_type"=>$row['vendor_type'],"first_name"=>$row['first_name'],"last_name"=>$row['last_name'],"contact_name"=>$row['contact_name'],"display_name"=>$row['display_name'],"company_name"=>$row['company_name'],"email"=>$row['email'],"contact"=>$row['mobile_phone'],"gst_treatment"=>$gst_treatment,"gst_num"=>$row['gst_num'],"district"=>$row['district'],"place_of_supply"=>$row['source_of_supply'],"payment_term"=>$row['payment_term'],"billing_address"=>$row['billing_address'],"billing_city"=>$row['billing_city'],"billing_state"=>$row['billing_state'],"billing_zipcode"=>$row['billing_zipcode'],"billing_country"=>$row['billing_country'],"billing_phone"=>$row['billing_phone'],"shipping_address"=>$row['shipping_address'],"shipping_city"=>$row['shipping_city'],"shipping_state"=>$row['shipping_state'],"shipping_zipcode"=>$row['shipping_zipcode'],"shipping_country"=>$row['shipping_country'],"shipping_phone"=>$row['shipping_phone'],"pan_number"=>$row['pan_number'],"service_tax_number"=>$row['service_tax_number'],"tax_number"=>$row['tax_number'],"adhaar_number"=>$row['adhaar_number'],"bank_name"=>$row['bank_name'],"bank_holder_name"=>$row['bank_holder_name'],"bank_address"=>$row['bank_address'],"ifsc"=>$row['ifsc'],"account_number"=>$row['account_number'],"bank_file"=>$row['bank_file'],"id_proof_file"=>$row['id_proof_file'],"created_by_id"=>$row['created_by_id'],"created_by" => $row['name'],"voter_id"=>$row['voter_id'],"rate"=>$row['rate'],"payment_mode"=>$row['payment_mode'],"bill_file"=>$row['bill_file'],"bill_number"=>$row['bill_number']));
        }
    }


    echo json_encode($response);

?>