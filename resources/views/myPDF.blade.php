<?php $html = '';

$html .= '<html>
<head><meta http-equiv=Content-Type content="text/html; charset=UTF-8">
<style type="text/css">

span.cls_003{font-family:Times,serif;font-size:11px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
div.cls_003{font-family:Times,serif;font-size:6.0px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
span.cls_010{font-family:Times,serif;font-size:5.0px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: underline}
div.cls_010{font-family:Times,serif;font-size:5.0px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
span.cls_008{font-family:Times,serif;font-size:6.0px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: underline}
div.cls_008{font-family:Times,serif;font-size:6.0px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
span.cls_002{font-family:Times,serif;font-size:18.1px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
div.cls_002{font-family:Times,serif;font-size:15.1px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none;border:1px solid black;padding: 0 7px 0 7px;}
span.cls_006{font-family:Times,serif;font-size:23.1px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
div.cls_006{font-family:Times,serif;font-size:13.1px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
span.cls_004{font-family:Times,serif;font-size:9px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
div.cls_004{font-family:Times,serif;font-size:5.0px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
span.cls_005{font-family:Times,serif;font-size:6.0px;color:rgb(0,0,0);font-weight:normal;font-style:italic;text-decoration: none}
div.cls_005{font-family:Times,serif;font-size:6.0px;color:rgb(0,0,0);font-weight:normal;font-style:italic;text-decoration: none}
span.cls_007{font-family:Times,serif;font-size:6.0px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: underline}
div.cls_007{font-family:Times,serif;font-size:6.0px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
span.cls_009{font-family:Times,serif;font-size:6.0px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
div.cls_009{font-family:Times,serif;font-size:6.0px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
span.cls_011{font-family:Times,serif;font-size:11.1px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: underline}
div.cls_011{font-family:Times,serif;font-size:11.1px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}

table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 2px;
}


.page-break {
    page-break-after: always;
}
</style>
</head>
<body>
<script type="text/php">

   if ( isset($pdf) ) {
        $y = 12;
  $x = 790;
        $text = "{PAGE_NUM} of {PAGE_COUNT}";
        $font = $fontMetrics->get_font("Arial", "bold");
        $size = 8;
        $color = array(0,0,0);
        $word_space = 0.0;  //  default
        $char_space = 0.0;  //  default
        $angle = 0.0;   //  default
        $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
    }

 
</script>

<div style="top:0px;width:100%;height:1400px;">
<div style="position:absolute;left: 86%;top:0px;" class="cls_008"><span class="cls_003"><img src="http://technitab.in/wp-content/uploads/thegem-logos/logo_ffeb66067296eb2202af8950c1f3131c_1x.png">
</span></div>
<div style="position:absolute;left:70.8px;top:33.68px" class="cls_002"><span class="cls_002">';
/*if($data["user"]->status = "submit"){
	echo "Submitted";
	
}else{
	echo "Not submitted";
}*/
$html .='Submitted</span></div>
<div style="position:absolute;left:340.36px;top: 34.84px;" class="cls_006"><span class="cls_006">Travel Expense Claim</span></div>
<div style="position:absolute;left: 94.28px;top: 66.96px;" class="cls_003"><span class="cls_003"><b>TEC # </b></span><span class="cls_004">'.$data["user"]->tec_id.'</span></div>
<div style="position:absolute;left:238.72px;top:66.96px" class="cls_003"><span class="cls_003"><b>Trip</b> </span><span class="cls_003"> #</span><span class="cls_004"> '.$data["user"]->trip_id.'</span></div>
<div style="position:absolute;left:529.12px;top:66.96px" class="cls_003"><span class="cls_003"><b>Zoho Bill Ref </b> :'.$data["user"]->tec_id.' - '.date('dM',strtotime($data["user"]->claim_start_date)).' - '.date('dM',strtotime($data["user"]->claim_end_date)).'</span></div>
<div style="position:absolute;left:755.12px;top:66.96px" class="cls_003"><span class="cls_003"><b>Date</b> : '.date('d-m-Y',strtotime($data["user"]->claim_end_date)).'</span></div>
<!--<div style="position:absolute;left:925.12px;top:66.96px" class="cls_003"><span class="cls_003"><b>Date</b> : '.date('d-m-Y',strtotime($data["user"]->claim_end_date)).'</span></div>-->
<div style="position:absolute;left:56.48px;top:83.6px" class="cls_003"><span class="cls_003"><b>Name</b> : '.$data["user"]->name.'</span></div>
<div style="position:absolute;left: 238.88px;top: 83.6px;" class="cls_003"><span class="cls_003"><b>Emp ID </b> :'.$data["user"]->role_id.'</span></div>
<div style="position:absolute;left:527.84px;top: 83.6px;" class="cls_003"><span class="cls_003"><b>Base location</b> : '.$data["user"]->base_location.'</span></div>
<div style="position:absolute;left: 725.6px;top: 83.06px;" class="cls_003"><span class="cls_003"><b>Project</b> : '.$data["user"]->project_name.'</span></div>
<div style="position:absolute;left:33.92px;top:99.56px" class="cls_003"><span class="cls_003"><b>Claim start date</b> : '.$data["user"]->claim_start_date.'</span></div>
<div style="position:absolute;left:236.8px;top:99.56px" class="cls_003"><span class="cls_003"><b>Claim end Date</b>  : '.$data["user"]->claim_end_date.'</span></div>
<div style="position:absolute;left:528.84px;top:99.56px" class="cls_003"><span class="cls_003"><b>Site Location</b> : '.$data["user"]->site_location.'</span></div>
<div style="position:absolute;left: 734.96px;top: 99.56px;" class="cls_003"><span class="cls_003"><b>Client</b> : '.$data["user"]->client_name.'</span></div>
<div style="left:23.96px;margin-top: 19%;" class="cls_003"><span class="cls_003"><b>Intercity travel cost</b></span></div>
<table style="left:18.44px;margin-top: 5px;margin-bottom: 5px;">
<tr>
<th class=""><span class="cls_003"><b>Bid</b></span></th>
<th class=""><span class="cls_003"><b>Inv date</b></span></th>
<th class=""><span class="cls_003"><b>Inv no</b></span></th>
<th class=""><span class="cls_003"><b>From</b></span></th>
<th class=""><span class="cls_003"><b>To</b></span></th>
<th class=""><span class="cls_003"><b>Mode</b></span></th>
<th class=""><span class="cls_003"><b>Vendor</b></span></th>
<th class=""><span class="cls_003"><b>GSTIN</b></span></th>
<th class=""><span class="cls_003"><b>Depr. TS</b></span></th>
<th class=""><span class="cls_003"><b>Arrival TS</b></span></th>
<th class=""><span class="cls_003"><b>Qt</b></span></th>
<th class=""><span class="cls_003"><b>Rate</b></span></th>
<th class=""><span class="cls_003"><b>Inv amount:</b></span></th>
<th class=""><span class="cls_003"><b>Pld</b></span></th>
<th class=""><span class="cls_003"><b>Atch</b></span></th>
<th class=""><span class="cls_003"><b>Paid by</b></span></th>
</tr>
<tbody>';
foreach($data['intercity_cost'] as $key=>$val){
	//echo "<pre>";print_R($val);exit;
	$html .='<tr>
<td><span class="cls_003">'.$val->booking_id.'</span></td>
<td><span class="cls_003">'.$val->date.'</span></td>
<td><span class="cls_003">'.$val->bill_num.'</span></td>
<td><span class="cls_003">'.$val->from_location.'</span></td>
<td><span class="cls_003">'.$val->to_location.'</span></td>
<td><span class="cls_003">'.$val->travel_mode.'</span></td>
<td><span class="cls_003">'.$val->paid_to.'</span></td>
<td><span class="cls_003">'.$val->gstin.'</span></td>
<td><span class="cls_003">'.$val->arrival_date.'  '.$val->arrival_time.'</span></td>
<td><span class="cls_003">'.$val->deprt_date.'  '.$val->deprt_time.'</span></td>
<td><span class="cls_003">'.$val->total_quantitty.'</span></td>
<td><span class="cls_003">'.$val->unit_price.'</span></td>
<td><span class="cls_003">'.$val->bill_amount.'</span></td>
<td><span class="cls_003">'.$val->payment_id.'</span></td>';
if($val->attachment_path  != ""){
 $html .= '<td><span class="cls_003"><a target="_blank" href="'.$val->attachment_path.'"><img src="'.URL::to("/paperclip.png").'" style="height: 15px;width: 20px;"></a></span></td>';

}else{
   $html .= '<td><span class="cls_003"></span></td>';

}

$html .= '<td><span class="cls_003">'.$val->paid_by.'</span></td>
</tr>';
}

$html .='<tr>
<td colspan="6"></td>
<td colspan="4"><span class="cls_003"><b>Subtotal Of Accounts</b> : '.$data["intercity_cost_sum_account"]->count.'</span></td>
<td colspan="6"><span class="cls_003" style="float:right;"><b>Subtotal Of User : '.$data["intercity_cost_sum_user"]->count.'</b></span></td>
</tr>
</tbody>
</table>

<div style="left:23.96px;margin-bottom:5px;" class="cls_003"><span class="cls_003"><b>Lodging cost</b></span></div>
<table style="left:18.44px;margin-bottom: 5px;">
<tr>
<th class=""><span class="cls_003"><b>Bid</b></span></th>
<th class=""><span class="cls_003"><b>Inv date</b></span></th>
<th class=""><span class="cls_003"><b>Inv to</b></span></th>
<th class=""><span class="cls_003"><b>Metro</b></span></th>
<th class=""><span class="cls_003"><b>City</b></span></th>
<th class=""><span class="cls_003"><b>Mode</b></span></th>
<th class=""><span class="cls_003"><b>Vendor</b></span></th>
<th class=""><span class="cls_003"><b>GSTIN</b></span></th>
<th class=""><span class="cls_003"><b>Check-In Dt</b></span></th>
<th class=""><span class="cls_003"><b>Check-out Dt</b></span></th>
<th class=""><span class="cls_003"><b>Nights</b></span></th>
<th class=""><span class="cls_003"><b>Rate</b></span></th>
<th class=""><span class="cls_003"><b>Inv amount:</b></span></th>
<th class=""><span class="cls_003"><b>Pld</b></span></th>
<th class=""><span class="cls_003"><b>Atch</b></span></th>
<th class=""><span class="cls_003"><b>Paid by</b></span></th>
</tr>
<tbody>';
foreach($data['lodging_cost'] as $key=>$val){
	//echo "<pre>";print_R($val);exit;
	$html .='<tr>
<td><span class="cls_003">'.$val->booking_id.'</span></td>
<td><span class="cls_003">'.$val->date.'</span></td>
<td><span class="cls_003">'.$val->bill_num.'</span></td>
<td><span class="cls_003">'.$val->is_metro.'</span></td>
<td><span class="cls_003">'.$val->location.'</span></td>
<td><span class="cls_003">'.$val->travel_mode.'</span></td>
<td><span class="cls_003">'.$val->paid_to.'</span></td>
<td><span class="cls_003">'.$val->gstin.'</span></td>
<td><span class="cls_003">'.$val->deprt_date.'</span></td>
<td><span class="cls_003">'.$val->arrival_date.'</span></td>
<td><span class="cls_003">'.$val->total_quantitty.'</span></td>
<td><span class="cls_003">'.$val->unit_price.'</span></td>
<td><span class="cls_003">'.$val->bill_amount.'</span></td>
<td><span class="cls_003">'.$val->payment_id.'</span></td>';
if($val->attachment_path  != ""){
 $html .= '<td><span class="cls_003"><a target="_blank" href="'.$val->attachment_path.'"><img src="'.URL::to("/paperclip.png").'" style="height: 15px;width: 20px;"></a></span></td>';

}else{
   $html .= '<td><span class="cls_003"></span></td>';

}
$html .= '<td><span class="cls_003">'.$val->paid_by.'</span></td>
</tr>';
}

$html .='<tr>
<td colspan="6"></td>
<td colspan="4"><span class="cls_003"><b>Subtotal Of Accounts</b> : '.$data["lodging_cost_sum_account"]->count.'</span></td>
<td colspan="6"><span class="cls_003" style="float:right;"><b>Subtotal Of User : '.$data["lodging_cost_sum_user"]->count.'</b></span></td>
</tr>
</tbody>
</table>

<div style="margin-bottom:5px;" class="cls_003"><span class="cls_003"><b>Per Diem as per Service time sheet</b></span></div>
<table style="margin-bottom:5px;">
<tr>
<th colspan=2><span class="cls_003"><b>Sign date</b></span></th>
<th class=""><span class="cls_003"><b>Billable</b></span></th>
<th class=""><span class="cls_003"><b>Metro</b></span></th>
<th class=""><span class="cls_003"><b>City</b></span></th>
<th class=""><span class="cls_003"><b>Mode</b></span></th>
<th class="" colspan=2><span class="cls_003"><b>User</b></span></th>
<th class=""><span class="cls_003"><b>STS start</b></span></th>
<th class=""><span class="cls_003"><b>STS end</b></span></th>
<th class=""><span class="cls_003"><b>Days</b></span></th>
<th class=""><span class="cls_003"><b>Per diem</b></span></th>
<th class="" colapan=2><span class="cls_003"><b>Inv amount:</b></span></th>
<th class=""><span class="cls_003"><b>Atch</b></span></th>
<th class=""><span class="cls_003"><b>Paid by</b></span></th>
</tr>
<tbody>';
foreach($data['Per_Diem'] as $key=>$val){
	//echo "<pre>";print_R($val);exit;
	$html .='<tr>
<td colspan=2><span class="cls_003">'.$val->date.'</span></td>
<td><span class="cls_003">'.$val->is_billable.'</span></td>
<td><span class="cls_003">'.$val->is_metro.'</span></td>
<td><span class="cls_003">'.$val->location.'</span></td>
<td><span class="cls_003">'.$val->travel_mode.'</span></td>
<td colspan=2><span class="cls_003">'.$val->paid_to.'</span></td>
<td><span class="cls_003">'.$val->deprt_date.'</span></td>
<td><span class="cls_003">'.$val->arrival_date.'</span></td>
<td><span class="cls_003">'.$val->total_quantitty.'</span></td>
<td><span class="cls_003">'.$val->unit_price.'</span></td>
<td colapan=2><span class="cls_003">'.$val->bill_amount.'</span></td>';
if($val->attachment_path  != ""){
 $html .= '<td><span class="cls_003"><a target="_blank" href="'.$val->attachment_path.'"><img src="'.URL::to("/paperclip.png").'" style="height: 15px;width: 20px;"></a></span></td>';

}else{
   $html .= '<td><span class="cls_003"></span></td>';

}

$html .= '<td><span class="cls_003">'.$val->paid_by.'</span></td>
</tr>';
}

$html .='<tr>
<td colspan="5"></td>
<td colspan="4"><span class="cls_003"><b>Subtotal Of Accounts</b> : '.$data["Per_Diem_sum_account"]->count.'</span></td>
<td colspan="6"><span class="cls_003" style="float:right;"><b>Subtotal Of User : '.$data["Per_Diem_sum_user"]->count.'</b></span></td>
</tr>
</tbody>
</table>

<div style="margin-bottom:5px;" class="cls_003"><span class="cls_003"><b>Loca1 Conveyance cost (if per diem 1s not c1aimed) </b></span></div>
<table style="margin-bottom: 5px;">
<tr>
<th colspan="2" class=""><span class="cls_003"><b>Inv date</b></span></th>
<th class=""><span class="cls_003"><b>Inv no</b></span></th>
<th class=""><span class="cls_003"><b>From</b></span></th>
<th class=""><span class="cls_003"><b>To</b></span></th>
<th class=""><span class="cls_003"><b>Mode</b></span></th>
<th class="" colspan=2><span class="cls_003"><b>User</b></span></th>
<th class=""><span class="cls_003"><b>From Dt</b></span></th>
<th class=""><span class="cls_003"><b>To Dt</b></span></th>
<th class=""><span class="cls_003"><b>Qty</b></span></th>
<th class=""><span class="cls_003"><b>Rate</b></span></th>
<th colspan=2 class=""><span class="cls_003"><b>Inv amount:</b></span></th>
<th class=""><span class="cls_003"><b>Atch</b></span></th>
<th class=""><span class="cls_003"><b>Paid by</b></span></th>
</tr>
<tbody>';
foreach($data['local_travel'] as $key=>$val){
	//echo "<pre>";print_R($val);exit;
	$html .='<tr>
<td colspan=2><span class="cls_003">'.$val->date.'</span></td>
<td><span class="cls_003">'.$val->bill_num.'</span></td>
<td><span class="cls_003">'.$val->from_location.'</span></td>
<td><span class="cls_003">'.$val->to_location.'</span></td>
<td><span class="cls_003">'.$val->travel_mode.'</span></td>
<td colspan=2><span class="cls_003">'.$val->paid_to.'</span></td>
<td><span class="cls_003">'.$val->deprt_date.'</span></td>
<td><span class="cls_003">'.$val->arrival_date.'</span></td>
<td><span class="cls_003">'.$val->total_quantitty.'</span></td>
<td><span class="cls_003">'.$val->unit_price.'</span></td>
<td colspan=2><span class="cls_003">'.$val->bill_amount.'</span></td>';
if($val->attachment_path  != ""){
 $html .= '<td><span class="cls_003"><a target="_blank" href="'.$val->attachment_path.'"><img src="'.URL::to("/paperclip.png").'" style="height: 15px;width: 20px;"></a></span></td>';

}else{
   $html .= '<td><span class="cls_003"></span></td>';

}

$html .= '<td><span class="cls_003">'.$val->paid_by.'</span></td>
</tr>';
}

$html .='<tr>
<td colspan="5"></td>
<td colspan="4"><span class="cls_003"><b>Subtotal Of Accounts</b> : '.$data["local_travel_sum_account"]->count.'</span></td>
<td colspan="7"><span class="cls_003" style="float:right;"><b>Subtotal Of User : '.$data["local_travel_sum_user"]->count.'</b></span></td>
</tr>
</tbody>
</table>
<div style="margin-bottom: 5px;" class="cls_003"><span class="cls_003"><b>Fuel / Expenses - own transport</b></span></div>
<table style="margin-bottom: 5px;">
<tr>
<th colspan=2 class=""><span class="cls_003"><b>Date</b></span></th>
<th class=""><span class="cls_003"><b>Vehicle</b></span></th>
<th class=""><span class="cls_003"><b>From</b></span></th>
<th class=""><span class="cls_003"><b>To</b></span></th>
<th class=""><span class="cls_003"><b>Mode</b></span></th>
<th class="" colspan=2><span class="cls_003"><b>User</b></span></th>
<th class=""><span class="cls_003"><b>From Dt</b></span></th>
<th class=""><span class="cls_003"><b>To Dt</b></span></th>
<th class=""><span class="cls_003"><b>Distance</b></span></th>
<th class=""><span class="cls_003"><b>Mileage</b></span></th>
<th colspan=2 class=""><span class="cls_003"><b>Inv amount:</b></span></th>
<th class=""><span class="cls_003"><b>Atch</b></span></th>
<th class=""><span class="cls_003"><b>Paid by</b></span></th>
</tr>
<tbody>';
foreach($data['fual_miledge'] as $key=>$val){
	//echo "<pre>";print_R($val);exit;
	$html .='<tr>
<td colspan=2><span class="cls_003">'.$val->date.'</span></td>
<td><span class="cls_003">'.$val->travel_mode.'</span></td>
<td><span class="cls_003">'.$val->from_location.'</span></td>
<td><span class="cls_003">'.$val->to_location.'</span></td>
<td><span class="cls_003">'.$val->travel_mode.'</span></td>
<td colspan=2><span class="cls_003">'.$val->paid_to.'</span></td>
<td><span class="cls_003">'.$val->deprt_date.'</span></td>
<td><span class="cls_003">'.$val->arrival_date.'</span></td>
<td><span class="cls_003">'.$val->kilo_meter.'</span></td>
<td><span class="cls_003">'.$val->mileage.'</span></td>
<td colspan=2><span class="cls_003">'.$val->bill_amount.'</span></td>';
if($val->attachment_path  != ""){
 $html .= '<td><span class="cls_003"><a target="_blank" href="'.$val->attachment_path.'"><img src="'.URL::to("/paperclip.png").'" style="height: 15px;width: 20px;"></a></span></td>';

}else{
   $html .= '<td><span class="cls_003"></span></td>';

}

$html .= '<td><span class="cls_003">'.$val->paid_by.'</span></td>
</tr>';
}

$html .='
</tbody>
</table>

<div style="margin-bottom: 5px;" class="cls_003"><span class="cls_003"><b>Fixed assets cost</b></span></div>
<table style="margin-bottom:5px;">
<tr>
<th class="" colspan=2><span class="cls_003"><b>Inv date</b></span></th>
<th class=""><span class="cls_003"><b>Inv to</b></span></th>
<th class="" colspan=3><span class="cls_003"><b>Description</b></span></th>
<th class="" colspan=2><span class="cls_003"><b>Vendor</b></span></th>
<th class=""><span class="cls_003"><b>GSTIN</b></span></th>
<th class=""><span class="cls_003"><b>Qty</b></span></th>
<th class=""><span class="cls_003"><b>Rate</b></span></th>
<th class=""><span class="cls_003"><b>Inv amount:</b></span></th>
<th class=""><span class="cls_003"><b>Atch</b></span></th>
<th class=""><span class="cls_003"><b>Paid by</b></span></th>
</tr>
<tbody>
<tbody>';
foreach($data['fixed_assets'] as $key=>$val){
	//echo "<pre>";print_R($val);exit;
	$html .='<tr>
<td colspan=2><span class="cls_003">'.$val->date.'</span></td>
<td><span class="cls_003">'.$val->bill_num.'</span></td>
<td colspan=3><span class="cls_003" >'.$val->description.'</span></td>
<td colspan=2><span class="cls_003">'.$val->paid_to.'</span></td>
<td><span class="cls_003">'.$val->gstin.'</span></td>
<td><span class="cls_003">'.$val->total_quantitty.'</span></td>
<td><span class="cls_003">'.$val->unit_price.'</span></td>
<td><span class="cls_003">'.$val->bill_amount.'</span></td>';
if($val->attachment_path  != ""){
 $html .= '<td><span class="cls_003"><a target="_blank" href="'.$val->attachment_path.'"><img src="'.URL::to("/paperclip.png").'" style="height: 15px;width: 20px;"></a></span></td>';

}else{
   $html .= '<td><span class="cls_003"></span></td>';

}

$html .= '<td><span class="cls_003">'.$val->paid_by.'</span></td>
</tr>';
}

$html .='<tr>
<td colspan="5"></td>
<td colspan="4"><span class="cls_003"><b>Subtotal Of Accounts</b> : '.$data["fixed_assets_sum_account"]->count.'</span></td>
<td colspan="7"><span class="cls_003" style="float:right;"><b>Subtotal Of User : '.$data["fixed_assets_sum_user"]->count.'</b></span></td>
</tr>
</tbody>
</table>

<div style="margin-bottom:5px;" class="cls_003"><span class="cls_003"><b>Repair & maintenance cost</b></span></div>
<table style="margin-bottom:5px;">
<tr>
<th class="" colspan=2><span class="cls_003"><b>Inv date</b></span></th>
<th class=""><span class="cls_003"><b>Inv to</b></span></th>
<th class="" colspan=3><span class="cls_003"><b>Description</b></span></th>
<th class="" colspan=2><span class="cls_003"><b>Vendor</b></span></th>
<th class=""><span class="cls_003"><b>GSTIN</b></span></th>
<th class=""><span class="cls_003"><b>Qty</b></span></th>
<th class=""><span class="cls_003"><b>Rate</b></span></th>
<th class=""><span class="cls_003"><b>Inv amount:</b></span></th>
<th class=""><span class="cls_003"><b>Atch</b></span></th>
<th class=""><span class="cls_003"><b>Paid by</b></span></th>
</tr>
<tbody>
<tbody>';
foreach($data['repair_main'] as $key=>$val){
	//echo "<pre>";print_R($val);exit;
	$html .='<tr>
<td colspan=2><span class="cls_003">'.$val->date.'</span></td>
<td><span class="cls_003">'.$val->bill_num.'</span></td>
<td colspan=3><span class="cls_003" >'.$val->description.'</span></td>
<td colspan=2><span class="cls_003">'.$val->paid_to.'</span></td>
<td><span class="cls_003">'.$val->gstin.'</span></td>
<td><span class="cls_003">'.$val->total_quantitty.'</span></td>
<td><span class="cls_003">'.$val->unit_price.'</span></td>
<td><span class="cls_003">'.$val->bill_amount.'</span></td>';
if($val->attachment_path  != ""){
 $html .= '<td><span class="cls_003"><a target="_blank" href="'.$val->attachment_path.'"><img src="'.URL::to("/paperclip.png").'" style="height: 15px;width: 20px;"></a></span></td>';

}else{
   $html .= '<td><span class="cls_003"></span></td>';

}

$html .= '<td><span class="cls_003">'.$val->paid_by.'</span></td>
</tr>';
}

$html .='<tr>
<td colspan="5"></td>
<td colspan="4"><span class="cls_003"><b>Subtotal Of Accounts</b> : '.$data["repair_main_sum_account"]->count.'</span></td>
<td colspan="7"><span class="cls_003" style="float:right;"><b>Subtotal Of User : '.$data["repair_main_sum_user"]->count.'</b></span></td>
</tr>
</tbody>
</table>

<div style="margin-bottom:5px;" class="cls_003"><span class="cls_003"><b>Misc Expenses (Visa fee , registration etc)</b></span></div>
<table style="margin-bottom:5px;">
<tr>
<th class="" colspan=2><span class="cls_003"><b>Inv date</b></span></th>
<th class=""><span class="cls_003"><b>Inv to</b></span></th>
<th class="" colspan=3><span class="cls_003"><b>Description</b></span></th>
<th class="" colspan=2><span class="cls_003"><b>Vendor</b></span></th>
<th class=""><span class="cls_003"><b>GSTIN</b></span></th>
<th class=""><span class="cls_003"><b>Qty</b></span></th>
<th class=""><span class="cls_003"><b>Rate</b></span></th>
<th class=""><span class="cls_003"><b>Inv amount:</b></span></th>
<th class=""><span class="cls_003"><b>Atch</b></span></th>
<th class=""><span class="cls_003"><b>Paid by</b></span></th>
</tr>
<tbody>
<tbody>';
foreach($data['misc'] as $key=>$val){
	//echo "<pre>";print_R($val);exit;
	$html .='<tr>
<td colspan=2><span class="cls_003">'.$val->date.'</span></td>
<td><span class="cls_003">'.$val->bill_num.'</span></td>
<td colspan=3><span class="cls_003" >'.$val->description.'</span></td>
<td colspan=2><span class="cls_003">'.$val->paid_to.'</span></td>
<td><span class="cls_003">'.$val->gstin.'</span></td>
<td><span class="cls_003">'.$val->total_quantitty.'</span></td>
<td><span class="cls_003">'.$val->unit_price.'</span></td>
<td><span class="cls_003">'.$val->bill_amount.'</span></td>';
if($val->attachment_path  != ""){
 $html .= '<td><span class="cls_003"><a target="_blank" href="'.$val->attachment_path.'"><img src="'.URL::to("/paperclip.png").'" style="height: 15px;width: 20px;"></a></span></td>';

}else{
   $html .= '<td><span class="cls_003"></span></td>';

}

$html .= '<td><span class="cls_003">'.$val->paid_by.'</span></td>
</tr>';
}

$html .='<tr>
<td colspan="5"></td>
<td colspan="4"><span class="cls_003"><b>Subtotal Of Accounts</b> : '.$data["misc_sum_account"]->count.'</span></td>
<td colspan="7"><span class="cls_003" style="float:right;"><b>Subtotal Of User : '.$data["misc_sum_user"]->count.'</b></span></td>
</tr>
</tbody>
</table>

<div style="margin-bottom:5px;" class="cls_003"><span class="cls_003"><b>International Travel Expenses</b></span></div>
<table style="margin-bottom:5px;">
<tr>
<th class="" colspan=2><span class="cls_003"><b>Inv date</b></span></th>
<th class=""><span class="cls_003"><b>Inv to</b></span></th>
<th class="" colspan=3><span class="cls_003"><b>Description</b></span></th>
<th class="" colspan=2><span class="cls_003"><b>Vendor</b></span></th>
<th class=""><span class="cls_003"><b>GSTIN</b></span></th>
<th class=""><span class="cls_003"><b>Qty</b></span></th>
<th class=""><span class="cls_003"><b>Rate</b></span></th>
<th class=""><span class="cls_003"><b>Inv amount:</b></span></th>
<th class=""><span class="cls_003"><b>Atch</b></span></th>
<th class=""><span class="cls_003"><b>Paid by</b></span></th>
</tr>
<tbody>
<tbody>';
foreach($data['intl_travel'] as $key=>$val){
	//echo "<pre>";print_R($val);exit;
	$html .='<tr>
<td colspan=2><span class="cls_003">'.$val->date.'</span></td>
<td><span class="cls_003">'.$val->bill_num.'</span></td>
<td colspan=3><span class="cls_003" >'.$val->description.'</span></td>
<td colspan=2><span class="cls_003">'.$val->paid_to.'</span></td>
<td><span class="cls_003">'.$val->gstin.'</span></td>
<td><span class="cls_003">'.$val->total_quantitty.'</span></td>
<td><span class="cls_003">'.$val->unit_price.'</span></td>
<td><span class="cls_003">'.$val->bill_amount.'</span></td>';
if($val->attachment_path  != ""){
 $html .= '<td><span class="cls_003"><a target="_blank" href="'.$val->attachment_path.'"><img src="'.URL::to("/paperclip.png").'" style="height: 15px;width: 20px;"></a></span></td>';

}else{
   $html .= '<td><span class="cls_003"></span></td>';

}

$html .= '<td><span class="cls_003">'.$val->paid_by.'</span></td>
</tr>';
}

$html .='<tr>
<td colspan="5"></td>
<td colspan="4"><span class="cls_003"><b>Subtotal Of Accounts</b> : '.$data["intl_travel_sum_account"]->count.'</span></td>
<td colspan="7"><span class="cls_003" style="float:right;"><b>Subtotal Of User : '.$data["intl_travel_sum_user"]->count.'</b></span></td>
</tr>
</tbody>
</table>

<div style="margin-bottom:5px;" class="cls_003"><span class="cls_003"><b>Attachments</b></span></div>
<table style="margin-bottom:5px;">
<tr>
<th class=""><span class="cls_003"><b>Sign date</b></span></th>
<th class=""><span class="cls_003"><b>S No</b></span></th>
<th colspan=4 class=""><span class="cls_003"><b>Description</b></span></th>
<th class="" colspan=5><span class="cls_003"><b>Attachment Type</b></span></th>
<th class="" ><span class="cls_003"><b>Atch</b></span></th>
</tr>
<tbody>';
foreach($data['attachment'] as $key=>$val){
$html .='<tr>
<td><span class="cls_003">'.$val->date.'</span></td>
<td ><span class="cls_003">'.$val->bill_num.'</span></td>
<td colspan=4><span class="cls_003"></span></td>
<td colspan=5><span class="cls_003">'.$val->entry_category.'</span></td>';
if($val->attachment_path  != ""){
 $html .= '<td><span class="cls_003"><a target="_blank" href="'.$val->attachment_path.'"><img src="'.URL::to("/paperclip.png").'" style="height: 15px;width: 20px;"></a></span></td>';

}else{
   $html .= '<td><span class="cls_003"></span></td>';

}

$html .= '</tr>';
}

$html .='</tbody>
</table>
<table style="">
<tr>

<td  width="50%" height="2%"><span class="cls_003" style="float:right;"><b>Total Accounts : '.($data["intl_travel_sum_account"]->count + $data["misc_sum_account"]->count + $data["repair_main_sum_account"]->count + $data["fixed_assets_sum_account"]->count + $data["local_travel_sum_account"]->count + $data["Per_Diem_sum_account"]->count + $data["lodging_cost_sum_account"]->count + $data["intercity_cost_sum_account"]->count).'</b></span></td>
<td  width="50%" height="2%"><span class="cls_003" style="float:right;"><b>Total Of User Payable : '.($data["intl_travel_sum_user"]->count + $data["misc_sum_user"]->count + $data["repair_main_sum_user"]->count + $data["fixed_assets_sum_user"]->count + $data["local_travel_sum_user"]->count + $data["Per_Diem_sum_user"]->count + $data["lodging_cost_sum_user"]->count + $data["intercity_cost_sum_user"]->count).'</b></span></td>
</tr>';



$html .= '</div>
</body></html>';

$html = preg_replace('/>\s+</', "><", $html);

echo $html;//exit;
?>