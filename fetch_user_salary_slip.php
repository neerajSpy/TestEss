<?php
include 'config/config.php';

$salary_slip_month = $_POST['salary_slip_month'];
$user_id = $_POST['user_id'];

$response = array();

//SELECT `id`; `user_id`; `salary_slip_month`; `pay_period`; `pay_day`; `name`; `emp_id`; `designation`; `pan_num`; `calc_month_days`; `lwp_nj_days`; `present_days`; `total_day_payable`; `available_leave`; `availed_el_month`; `balance_leave`; `current_balance`; `ctc_basic_sal`; `ctc_house_rent_allowance`; `ctc_medical_allowance`; `ctc_special_allowance`; `ctc_l_t_a`; `ctc_edu_allowance`; `ctc_other_allowance`; `ctc_arrears_payment`; `ctc_ot_payment`; `provident_fund`; `esic`; `advance_loan`; `insurance_medical_benefit`; `income_tax`; `p_f`; `total_deduction`; `ctc_gross_total`; `net_amount_payable`; `payable_basic_sal`; `payable_house_rent_allowance`; `payable_medical_allowance`; `payable_special_allowance`; `payable_l_t_a`; `payable_edu_allowance`; `payable_other_allowance`; `payable_arrears_payment`; `payable_ot_payment`; `payable_gross_total` FROM `emp_salary_slip` WHERE 1


$result = $con->query("SELECT * from `emp_salary_slip` WHERE `salary_slip_month`= '$salary_slip_month' AND `user_id` = '$user_id'");
if ($result->num_rows >0) {
	if ($row = $result->fetch_assoc()) {
		$response['id']=$row['id'];
		$response['pay_period']=$row['pay_period'];
		$response['pay_day']=$row['pay_day'];
		$response['name']=$row['name'];
		$response['emp_id']=$row['emp_id'];
		$response['designation']=$row['designation'];
		$response['pan_num']=$row['pan_num'];
		$response['esic_num']=$row['esic_num'];
		$response['pf_num']=$row['pf_num'];
		$response['calc_month_days']=$row['calc_month_days'];
		$response['lwp_nj_days']=$row['lwp_nj_days'];
		$response['present_days']=$row['present_days'];
		$response['total_day_payable']=$row['total_day_payable'];
		$response['available_leave']=$row['available_leave'];
		$response['availed_el_month']=$row['availed_el_month'];
		$response['balance_leave']=$row['balance_leave'];
		$response['current_balance']=$row['current_balance'];
		$response['ctc_basic_sal']=$row['ctc_basic_sal'];
		$response['ctc_house_rent_allowance']=$row['ctc_house_rent_allowance'];
		$response['ctc_medical_allowance']=$row['ctc_medical_allowance'];
		$response['ctc_special_allowance']=$row['ctc_special_allowance'];
		$response['ctc_l_t_a']=$row['ctc_l_t_a'];
		$response['ctc_edu_allowance']=$row['ctc_edu_allowance'];
		$response['ctc_other_allowance']=$row['ctc_other_allowance'];
		$response['ctc_arrears_payment']=$row['ctc_arrears_payment'];
		$response['ctc_ot_payment']=$row['ctc_ot_payment'];
		$response['provident_fund']=$row['provident_fund'];
		$response['esic']=$row['esic'];
		$response['advance_loan']=$row['advance_loan'];
		$response['insurance_medical_benefit']=$row['insurance_medical_benefit'];
		$response['income_tax']=$row['income_tax'];
		$response['professional_tax']=$row['professional_tax'];
		$response['total_deduction']=$row['total_deduction'];
		$response['ctc_gross_total']=$row['ctc_gross_total'];
		$response['net_amount_payable']=$row['net_amount_payable'];
		$response['payable_basic_sal']=$row['payable_basic_sal'];
		$response['payable_house_rent_allowance']=$row['payable_house_rent_allowance'];
		$response['payable_medical_allowance']=$row['payable_medical_allowance'];
		$response['payable_special_allowance']=$row['payable_special_allowance'];
		$response['payable_l_t_a']=$row['payable_l_t_a'];
		$response['payable_edu_allowance']=$row['payable_edu_allowance'];
		$response['payable_other_allowance']=$row['payable_other_allowance'];
		$response['payable_arrears_payment']=$row['payable_arrears_payment'];
		$response['payable_ot_payment']=$row['payable_ot_payment'];
		$response['payable_gross_total']=$row['payable_gross_total'];
		$response['ctc_ca']=$row['ctc_ca'];
		$response['gross_ca']=$row['gross_ca'];
	}
}
	  	
echo json_encode($response);

?>