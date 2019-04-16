<?php
include "config/config.php";
date_default_timezone_set('Asia/Kolkata');

$customer_name = $_POST['customer_name'];


# SELECT `id`, `Created Time`, `Last Modified Time`, `Contact ID`, `Contact Type`, `Place Of Contact`, `Place of Contact(With State Code)`, `Taxable`, `Tax Name`, `Tax Percentage`, `Exemption Reason`, `Contact Name`, `Display Name`, `Company Name`, `Salutation`, `First Name`, `Last Name`, `EmailID`, `Phone`, `MobilePhone`, `Skype Identity`, `Facebook`, `Twitter`, `Price List`, `Payment Terms`, `Currency Code`, `GST Treatment`, `GST Identification Number (GSTIN)`, `PAN Number`, `Notes`, `Website`, `Contact Address ID`, `Billing Attention`, `Billing Address`, `Billing Street2`, `Billing City`, `Billing State`, `Billing Country`, `Billing Code`, `Billing Phone`, `Billing Fax`, `Shipping Attention`, `Shipping Address`, `Shipping Street2`, `Shipping City`, `Shipping State`, `Shipping Country`, `Shipping Code`, `Shipping Phone`, `Shipping Fax`, `Source`, `Last Sync Time`, `Status`, `Vendor Payment`, `Owner Name`, `CF.PAN No`, `CF.Service Tax No`, `CF.TAN No`, `CF.ADHAAR No`, `CF.Name of Vendor's Bank`, `CF.Bank's Address`, `CF.IFSC Code`, `CF.Bank Account number` FROM `master_zoho_customer` WHERE 1

$response = array();
$result = $con->query("SELECT * FROM `master_zoho_customer` WHERE LOWER(`display_name`) LIKE LOWER('%$customer_name%')");

if ($result->num_rows >0) {
	while ($row = $result->fetch_array()) {
		array_push($response, array("id"=>$row['id'],"customer_name"=>$row['display_name']));
	}
}

echo json_encode($response);


?>
