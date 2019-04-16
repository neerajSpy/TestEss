<?php
include "config.php";
include "module_constant.php";

$hospital_id = $_POST['hospital_id'];

$response = array();

$profileArray = array(HOSPITAL_PROFILE,HOSPITAL_BUILDING_DETAIL);
$hazardArray = array(HOSPITAL_HAZARD_PROFILE);
$committeeArray = array(HOSPITAL_COMMITTEE);
$hsiArray = array(STRUCTURAL_SAFETY,NONSTRUCTURAL_SAFETY,EMERGENCY_AND_DISASTER_MANAGEMENT);
$iscHomeArray = array(ICS,JOB_ACTION_SHEET);
$icsArray = array(ICS);
$jasArray = array(JOB_ACTION_SHEET);
$sopArray = array(SOP);

$statusArray = getStatus($con,$hospital_id,$profileArray,$hazardArray,$committeeArray,$hsiArray,$iscHomeArray,$icsArray,$jasArray,$sopArray);

$response = array("error"=>false,"module_flag_status"=>$statusArray);
echo json_encode($response);



function getStatus($con,$hospital_id,$profileArray,$hazardArray,$committeeArray,$hsiArray,$iscHomeArray,$icsArray,$jasArray,$sopArray){
	$responseArray = array();
	$responseArrayForHSI = array();
	$result = $con->query("SELECT * from `module_flag` WHERE `hospital_id` = '$hospital_id'");
	if ($result->num_rows >0) {
		while ($row = $result->fetch_assoc()) {
			array_push($responseArrayForHSI,$row);
		 	array_push($responseArray,$row['module_id']);
		 } 
	}
	
	$response = array();
	$response['hospital_profile'] = count(array_intersect($profileArray, $responseArray));
	$response['hospital_hazard_profile'] = count(array_intersect($hazardArray, $responseArray));
	$response['hospital_safety_committee'] = count(array_intersect($committeeArray, $responseArray));

	$response['hospital_safety_index'] = getHospitalSafetyIndexFlag($responseArrayForHSI,$responseArray,$hsiArray);
	$response['isc_main'] = count(array_intersect($iscHomeArray, $responseArray));
	$response['ics_sub'] = count(array_intersect($icsArray, $responseArray));
	$response['job_action_sheet'] = count(array_intersect($jasArray, $responseArray));
	$response['sop'] = count(array_intersect($sopArray, $responseArray));
	return $response;

}

function getHospitalSafetyIndexFlag($responseArr,$moduleResponse,$hsiArr){
	$flagCount = -1;
	$result = array_intersect($hsiArr, $moduleResponse);
	print_r($responseArr);
	foreach ($responseArr as $row){
		if ($row['module_id'] == 5 && $row['flag'] == 1){
			$flagCount ++;
		}else if ($row['module_id'] == 6 && $row['flag'] == 1){
			$flagCount ++;
		}else if ($row['module_id'] == 7 && $row['flag'] == 1){
			$flagCount ++;
		}

	}

	if ($flagCount == -1) {
		$count = count(array_intersect($hsiArr, $moduleResponse));

		$flagCount = $count >0 ? 1: 0;
	}
	return $flagCount;
}

?>