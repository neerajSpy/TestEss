<?php

namespace App\Http\Controllers;
error_reporting(E_ALL);
ini_set('display_errors', 1);
use Illuminate\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\DB;
use App\Leave;
use PDF;
use Excel;
use Dompdf\Dompdf;
use Mail;
class LeaveController extends Controller
{

   function submitTec($userId,$tecId,$claimEndDate,$userNote,$bookingJson) {
        
        $this->insertTecEntryFromBooking($tecId, $userId, $bookingJson);
        
        $total_amount = DB::table('emp_tec_entry')
                       ->select(DB::raw("SUM(bill_amount) as amount"))
                       ->where(array('tec_id'  => $tecId))
		       ->get()->toArray();

      // echo "<pre>";print_r($data['data']['user']);exit;
	$result = DB::table('emp_main_tec')
            ->where('id', $tecId)
            ->update(['status' => 'submit','claim_end_date' => $claimEndDate,'user_note' => $userNote,'total_amount' => $total_amount[0]->amount,'submit_date' => date('Y-m-d h:i:s'),'submit_by_id' => $userId,'modified_date' => date('Y-m-d h:i:s'),'modified_by_id' => $userId]);
    
	if ($result == 1) {
	    $result = $this->createTecLog($tecId, $userId,$userNote,'submit',"");
	    if ($result == 1) {
            	return $result;
	    }else{

            	return false;
            }
        }else{

            return false;
        }
	}
    function getUnitPriceQtyOfBooking($bookingId, $travelType)
    {
        $data = array();
	$result = DB::table('emp_booking_member')
                       ->select('*')
                       ->where(array('booking_id'  => $bookingId,'is_active' => 0))
		       ->get()->toArray();

        
        $memberCount = count($result);

       // $res = $this->con->query("SELECT * from `emp_booking` where `id` = '$bookingId'");
	$res = DB::table('emp_booking')
                       ->select('*')
                       ->where(array('id'  => $bookingId))
		       ->get()->first();

        if (!empty($res)) {
            $row = $res;
            $data['quantity'] = (int) ($row ->quantity / $memberCount);
            $data['rate'] = $row->rate / $memberCount;
            $data['member'] = $memberCount;
        }

        return $data;
    }
    function insertTecIdIntoBooking($tecId,$bookingId){
	$result = DB::table('emp_booking')
            ->where('id', $bookingId)
            ->update(['tec_id' => $tecId]);
      
    }
    function insertTecEntryFromBooking($tecId, $createdById, $tripBookingJson)
    {
        
        $linkBookingsOnTecCount = 0;
        if(!empty($tripBookingJson)){
        foreach ($tripBookingJson as $value) {

            $bookingId = $value->id;
            $paymentId = $value->payment_id;
            $travelType = $value->travel_type;
            $adminBookngMode = $value->admin_booking_mode;
            $adminCityArea = $value->admin_city_area;
            $adminVendor = $value->admin_vendor;
            $adminVendorId = $value->admin_vendor_id;
            $adminSource = $value->admin_source;
            $adminDestination = $value->admin_destination;
            $adminCheckIn = $value->admin_check_in;
            $adminCheckOut = $value->admin_check_out;
            $adminDepartureDateTime = $value->admin_departure_date_time;
            $adminArrivalDateTime = $value->admin_arrival_date_time;
            $totalAmount = $value->total_amount;
            $adminBookngAttachment = $value->admin_booking_attachment;

            $departureDate = date('Y-m-d', strtotime($adminDepartureDateTime));
            $departureTime = date('H:i:s', strtotime($adminDepartureDateTime));
            $arrivalDate = date('Y-m-d', strtotime($adminArrivalDateTime));
            $arrivalTime = date('H:i:s', strtotime($adminArrivalDateTime));

            $data = $this->getUnitPriceQtyOfBooking($bookingId, $travelType);
          
            if (strtolower(trim($adminBookngMode)) == 'bus' || strtolower(trim($adminBookngMode)) == 'train' || strtolower(trim($travelType)) == 'flight') {

		$bill_date = DB::table('booking_payment')
                       ->select('bill_date','paid_by','reference_number','created_date','created_by_id')
                       ->where(array('id'  => $paymentId))
		       ->get()->first();

		$data = array(
					'tec_id' => $tecId,
					'booking_id' => $bookingId,
					'payment_id' => $paymentId,
					'entry_category' => 'Intercity Travel cost',
					'travel_mode' => $adminBookngMode,
					'from_location' => $adminSource,
					'to_location' => $adminDestination ,
					'deprt_date' => $departureDate ,
					'deprt_time' => $departureTime,
					'arrival_date' => $arrivalDate,
					'arrival_time' => $arrivalTime,
					'unit_price' => $data['rate'],
					'total_quantitty' => 1 ,
					'date' => $bill_date->bill_date,
					'paid_to' => $adminVendor,
					'paid_by' => $bill_date->paid_by,
					'bill_amount' => $totalAmount / $data['member'],
					'bill_num' => $bill_date->reference_number,
					'created_date' =>$bill_date->created_date,
					'created_by_id' => $bill_date->created_by_id,
					'paid_to_id' => $adminVendorId,
                    'attachment_path'=>$adminBookngAttachment
				);
	  	
               
            } else {
            

		$bill_date = DB::table('booking_payment')
                       ->select('bill_date','paid_by','reference_number','created_date','created_by_id')
                       ->where(array('id'  => $paymentId))
		       ->get()->first();

		$data = array(
					'tec_id' => $tecId,
					'booking_id' => $bookingId ,
					'payment_id' => $paymentId,
					'entry_category' => 'Lodging - Hotels',
					'travel_mode' => $adminBookngMode,
					'location' => $adminCityArea,
					'deprt_date' => $adminCheckIn ,
					'arrival_date' => $adminCheckOut,
					'unit_price' => $data['rate'] ,
					'total_quantitty' => $data['quantity'] ,
					'date' => $bill_date->bill_date,
					'paid_to' => $adminVendor,
					'paid_by' => $bill_date->paid_by,
					'bill_amount' => $totalAmount / $data['member'],
					'bill_num' => $bill_date->reference_number,
					'created_date' =>$bill_date->created_date,
					'created_by_id' => $bill_date->created_by_id,
					'paid_to_id' => $adminVendorId,
                    'attachment_path'=>$adminBookngAttachment
				);

	  	
            }

            $result = DB::table('emp_tec_entry')->insert($data);

            //$result = DB::insert('insert into `emp_tec_entry` (`entry_category`) values (?)', ['Dayle']);

            if ($result === TRUE) {
                $this->insertTecIdIntoBooking($tecId, $bookingId);
                $linkBookingsOnTecCount++;
            }
        }
        }
        if ($linkBookingsOnTecCount >0) {
            return 1;
        }else{
            return 0;
        }
    }

    function updateTecByAdmin($userId,$tecId,$status,$remark,$tecBlockValue){
       
        if (strtolower($status) == 'draft' || strtolower($status) == 'submit') {
		$result = DB::table('emp_main_tec')
            		->where('id', $tecId)
            		->update(['status' => $status , 'total_amount' => 0,'remark' => $remark,'modified_date' => date('Y-m-d h:i:s'),'modified_by_id' => $tecId]);
           
        }else {

		$result = DB::table('emp_main_tec')
            		->where('id', $tecId)
            		->update(['status' => $status , 'remark' => $remark,'modified_date' => date('Y-m-d h:i:s'),'modified_by_id' => $tecId]);
        }
        
        if ($result == 1) {
		$result = $this->createTecLog($tecId, $userId, $remark, $status,$tecBlockValue);
		if ($result == 1) {
			return $result;
		}else{
			 return false;
		}
            
        }else{
            return false;
        }
    }
    function sendNotificationToUser($userId, $sendById, $title, $notificationArray)
    {
        

        $sendBy = $notificationArray['user_name'];
        $userArr = array(
            'user_name' => $sendBy
        );
        $arr = array_merge($notificationArray, $userArr);

	$tokenResult = DB::table('user_fcm_token')
  
                   		->select('token')
      
               			->where(array('user_id'  => $userId))
				->get()->toArray();

        // echo "result ".$tokenResult->num_rows." error".$this->con->error;

	if (count($tokenResult) > 0) {
            foreach($tokenResult as $row) {

                $token = $row->token;
                // echo "token ".$token;
                $this->notification($token, $title, $arr);
            }
        }
    }
    function createTecLog($tecId,$userId,$comment,$tecStatus,$tecBlockValue){
	$data = array("tec_id" => $tecId,
			"comment" => $comment,
			"status" => $tecStatus,
			"created_by_id" => $userId,
            "tec_block_value" => $tecBlockValue,
			"created_date" => date('Y-m-d h:i:s')

			);

	$result = DB::table('emp_tec_log')->insert($data);

	if ($result == 1) {
            return $result;
        }else{
            return false;
        }
    }
    public function pdf(Request $request)
    {
	$post = $request->all();
	$id = $post['tec_id'];
   // DB::enableQueryLog();
	$user = DB::table('emp_main_tec')
					->join('user', 'user.id', '=', 'emp_main_tec.created_by_id')
					->join('master_zoho_project', 'master_zoho_project.id', '=', 'emp_main_tec.project_id')
                     ->select('user.name','user.email','emp_main_tec.*','emp_main_tec.created_date as create','emp_main_tec.id as tec_id','master_zoho_project.*')
                     ->where(array('emp_main_tec.id'  => $id))
                     ->first();
   
    if (strtolower($post['action']) == "admin_update_tec") {
    $tecId = $post['tec_id'];
    $tripId = $post['trip_id'];
    $createdById = $post['created_by_id'];
    $projectName = $post['project_name'];
    $userId = $post['user_id'];
    $status = $post['status'];
    $remark = ($post['remark'] != "" ) ? $post['remark'] : "";
    $tecBlockValue = ($post['tec_block_value'] != "" ) ? $post[',$tec_block_value'] : "0";

    
    $result = $this->updateTecByAdmin($userId, $tecId, $status, $remark,$tecBlockValue);
   
        $tecStatus = "";
        if ($status == "draft") {
            $tecStatus = "Please recheck tec.";
        } else if ($status == "submit") {
            $tecStatus = "submit for checking";
        } else if ($status == "open") {
            $tecStatus = "tec approve";
        } else {
            $tecStatus = "tec payment done";
        }
        
        $notificationArr = array(
            "project_name" => $projectName,
            "trip id" => $tripId,
            "tec_id" => $tecId,
            "status" =>$tecStatus,
	   "user_name" => $user->name  
        );
        
        $this->sendNotificationToUser($createdById, $userId,'Update Tec', $notificationArr);
    }else{
	

    
	$bookingJson = (!empty($post['booking_json'])) ? json_decode($post['booking_json']) : "";
    	$claimEndDate = $post['claim_end_date'];
    	$tecId = $post['tec_id'];
    	$tripId = $post['trip_id'];
    	$userNote = ($post['user_note'] != "" ) ? $post['user_note'] : "";
    	$submitById = $post['submit_by_id'];
    	$projectName = $post['project_name'];

	$result = $this->submitTec($submitById, $tecId, $claimEndDate, $userNote, $bookingJson);

	
	$notificationArr = array(
            "project_name" => $projectName,
            "trip id" => $tripId,
            "tec_id" => $tecId ,
	    "user_name" => $user->name       );
        $this->sendNotificationToAdmin($submitById, 'Submit Tec', $notificationArr);
	
    }

		
		$data['intercity_cost'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
                     ->select('emp_main_tec.*','emp_main_tec.id as emp_id','emp_tec_entry.*','emp_tec_entry.id as tes_id',
                        DB::raw('CONCAT(emp_tec_entry.deprt_date," ",emp_tec_entry.deprt_time) as full_time'))
                     ->where(array('emp_tec_entry.entry_category'  => 'Intercity Travel cost','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->orderBy('full_time','ASC')
                     ->get()->toArray();
		// DB::enableQueryLog();			 
		$data['intercity_cost_sum_user'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Employee','emp_tec_entry.entry_category'  => 'Intercity Travel cost','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
       // dd(DB::getQueryLog());
                //     print_r($data['intercity_cost_sum_user'] );exit;
		$data['intercity_cost_sum_account'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Accounts','emp_tec_entry.entry_category'  => 'Intercity Travel cost','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
					 
		//print_r($data['intercity_cost_sum']);exit;
		//dd(DB::getQueryLog());
		$data['lodging_cost'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
                     ->select('emp_main_tec.*','emp_main_tec.id as emp_id','emp_tec_entry.*','emp_tec_entry.id as tes_id')
                     ->where(array('emp_tec_entry.entry_category'  => 'Lodging - Hotels','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->toArray();
					 
		$data['lodging_cost_sum_user'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Employee','emp_tec_entry.entry_category'  => 'Lodging - Hotels','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
		$data['lodging_cost_sum_account'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Accounts','emp_tec_entry.entry_category'  => 'Lodging - Hotels','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
					 
					 
		$data['Per_Diem'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select('emp_main_tec.*','emp_main_tec.id as emp_id','emp_tec_entry.*','emp_tec_entry.id as tes_id')
                     ->where(array('emp_tec_entry.entry_category'  => 'Food - Boarding - Per Diem','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->toArray();
					 
		$data['Per_Diem_sum_user'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Employee','emp_tec_entry.entry_category'  => 'Food - Boarding - Per Diem','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
		$data['Per_Diem_sum_account'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Accounts','emp_tec_entry.entry_category'  => 'Food - Boarding - Per Diem','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
					 
					 
		$data['local_travel'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
                     ->select('emp_main_tec.*','emp_main_tec.id as emp_id','emp_tec_entry.*','emp_tec_entry.id as tes_id')
                     ->where(array('emp_tec_entry.entry_category'  => 'Local Travel - Public transport','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->toArray();
					 
		$data['local_travel_sum_user'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Employee','emp_tec_entry.entry_category'  => 'Local Travel - Public transport','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
		$data['local_travel_sum_account'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Accounts','emp_tec_entry.entry_category'  => 'Local Travel - Public transport','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
					 
					 
		$data['fual_miledge'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select('emp_main_tec.*','emp_main_tec.id as emp_id','emp_tec_entry.*','emp_tec_entry.id as tes_id')
                     ->where(array('emp_tec_entry.entry_category'  => 'Fuel/Mileage Expenses - Own transport','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->toArray();
					 
		$data['fual_miledge_sum_user'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Employee','emp_tec_entry.entry_category'  => 'Fuel/Mileage Expenses - Own transport','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
		$data['fual_miledge_sum_account'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Accounts','emp_tec_entry.entry_category'  => 'Fuel/Mileage Expenses - Own transport','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
					 
					 
		$data['fixed_assets'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select('emp_main_tec.*','emp_main_tec.id as emp_id','emp_tec_entry.*','emp_tec_entry.id as tes_id')
                     ->where(array('emp_tec_entry.entry_category'  => 'Fixed Asset','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->toArray();
					 
		$data['fixed_assets_sum_user'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Employee','emp_tec_entry.entry_category'  => 'Fixed Asset','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
		$data['fixed_assets_sum_account'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Accounts','emp_tec_entry.entry_category'  => 'Fixed Asset','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
					 
					 
					 
					 
		$data['repair_main'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select('emp_main_tec.*','emp_main_tec.id as emp_id','emp_tec_entry.*','emp_tec_entry.id as tes_id')
                     ->where(array('emp_tec_entry.entry_category'  => 'Repairs and Maintenance','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->toArray();
		
		$data['repair_main_sum_user'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Employee','emp_tec_entry.entry_category'  => 'Repairs and Maintenance','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
		$data['repair_main_sum_account'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Accounts','emp_tec_entry.entry_category'  => 'Repairs and Maintenance','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
					 
		$data['misc'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select('emp_main_tec.*','emp_main_tec.id as emp_id','emp_tec_entry.*','emp_tec_entry.id as tes_id')
                     ->where(array('emp_tec_entry.entry_category'  => 'Miscellaneous','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->toArray();
		$data['misc_sum_user'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Employee','emp_tec_entry.entry_category'  => 'Miscellaneous','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
		$data['misc_sum_account'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Accounts','emp_tec_entry.entry_category'  => 'Miscellaneous','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
					 
		$data['intl_travel'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select('emp_main_tec.*','emp_main_tec.id as emp_id','emp_tec_entry.*','emp_tec_entry.id as tes_id')
                     ->where(array('emp_tec_entry.entry_category'  => 'Intl Travel Insurance','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->toArray();
		
		$data['intl_travel_sum_user'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Employee','emp_tec_entry.entry_category'  => 'Intl Travel Insurance','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
		$data['intl_travel_sum_account'] = DB::table('emp_main_tec')
					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
                     ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
                     ->where(array('emp_tec_entry.paid_by'  => 'Accounts','emp_tec_entry.entry_category'  => 'Intl Travel Insurance','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
                     ->get()->first();
//DB::enableQueryLog();
		$data['attachment'] = DB::select("select `emp_tec_entry`.`bill_num`, `emp_tec_entry`.`attachment_path`,`emp_tec_entry`.`entry_category`,`emp_tec_entry`.`date` from `emp_main_tec` inner join `emp_tec_entry` on `emp_main_tec`.`id` = `emp_tec_entry`.`tec_id` where (`emp_main_tec`.`id` = ".$id." and `emp_tec_entry`.`is_active` = 0) and (`emp_tec_entry`.`entry_category` = 'Minutes of Meeting'  or `emp_tec_entry`.`entry_category` = 'Service Timesheet' or `emp_tec_entry`.`entry_category` = 'Feedback Form' or `emp_tec_entry`.`entry_category` = 'SAT Report' or `emp_tec_entry`.`entry_category` = 'Checklists')");
		
					
//					 dd(DB::getQueryLog());
		$user = DB::table('emp_main_tec')
					->join('user', 'user.id', '=', 'emp_main_tec.created_by_id')
					->join('master_zoho_project', 'master_zoho_project.id', '=', 'emp_main_tec.project_id')
                     ->select('user.name','user.email','user.temp_email','emp_main_tec.*','emp_main_tec.created_date as create','emp_main_tec.id as tec_id','master_zoho_project.*')
                     ->where(array('emp_main_tec.id'  => $id))
                     ->first();
		$data['user'] = $user;
		
		$log = DB::table('emp_tec_log')

                       ->select('*')
 
                       ->where(array('tec_id'  => $id))
			->orderBy('id', 'desc')
			->limit(10)
		       ->get()->toArray();

		$last_status = DB::table('emp_main_tec')

				->join('user', 'user.id', '=', 'emp_main_tec.created_by_id')
  
                   		->select('emp_main_tec.*')
      
               			->where(array('emp_main_tec.id'  => $id))

				->orderBy('emp_main_tec.id', 'desc')
				->limit(5)	
				->get()->toArray();
//echo "<pre>";print_r($last_status);exit;
		$bill_amount = DB::table('emp_main_tec')

				->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
	
				->join('master_zoho_vendor', 'master_zoho_vendor.id', '=', 'emp_tec_entry.paid_to_id')
	
				->join('master_zoho_project', 'master_zoho_project.id', '=', 'emp_main_tec.project_id')
 
                    		->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
 
                    		->where(array('emp_tec_entry.paid_by'  => 'Employee','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))

                     		->get()->first();
			
		$data = ['data' => $data];

        $filename = $data['data']["user"]->tec_id.' - '.date('dM',strtotime($data['data']["user"]->claim_start_date)).' - '.date('dM',strtotime($data['data']["user"]->claim_end_date)).'.pdf';
        $pdf = PDF::loadView('myPDF', $data);
        PDF::setOptions(['isPhpEnabled' => true]);
        $pdf->setPaper('A4', 'landscape');
       $pdf->save(storage_path('app/public/'.$filename));



$from_name = 'admin';

$from_mail = 'no-reply@technitab.com';

$replyto = $data['data']['user']->temp_email;

$mailto = $data['data']['user']->temp_email;

//$mailto = "bhumikajakasaniya@gmail.com";

$subject = 'TEC status change: '.$data['data']["user"]->status.' - '.$data['data']["user"]->tec_id.'';


$message = "Dear ".$data['data']['user']->name.",

<p></p>The TEC - ".$data['data']['user']->tec_id." has been changed by ".$data['data']['user']->name." on ".date('Y-m-d h:i:s')." as per the following information. For further details kindly refer the attached TEC from ESS.<br/><br/>


<b>Name: </b> ".$data['data']['user']->name."<br/>

<b>Employee id:</b> ".$data['data']['user']->created_by_id."<br/>

<b>Zoho bill ref:</b> ".$data['data']['user']->tec_id." - ".date('dM',strtotime($data['data']['user']->claim_start_date))." - ".date('dM',strtotime($data['data']['user']->claim_end_date))."<br/>

<b>TEC no:</b> ".$data['data']['user']->tec_id."<br/>

<b>Trip Id: </b>".$data['data']["user"]->trip_id."<br/>

<b>Project name:</b> ".$data['data']["user"]->project_name."<br/>

<b>Client name:</b> ".$data['data']["user"]->client_name."<br/>

<b>Claim start & end date:</b> ".$data['data']["user"]->claim_start_date." - ".$data['data']["user"]->claim_start_date."<br/>

<b>Base & site location:</b> ".$data['data']["user"]->base_location." - ".$data['data']["user"]->site_location."<br/>

<b>Date:</b> ".$data['data']["user"]->submit_date."<br/>

<b>TEC amount:</b> ".$bill_amount->count."<br/><br/>
Latest 10 status of TEC id ".$data['data']['user']->tec_id."<br/>";
foreach($log as $key => $val){

$message .= "<b>Comment: </b>".$val->comment." <b>Status:</b> ".$val->status."  <b>Date:</b> ".$val->created_date."<br/>";		
}

$message .= "<br/>
Latest 5 TEC status <br/>";

foreach($last_status as $key=>$val){
	
$message .= "<b>TEC id:</b>".$val->id. "<b> Status:</b>".$val->status."<br/>";


}
$message .= "<br/>Regards.<br/>

Ess App"; 
//echo $message;exit;
$content = file_get_contents(storage_path('app/public/'.$filename));
//print_r($content);exit;
$content = chunk_split(base64_encode($content));
$uid = md5(uniqid(time()));
$name = basename($filename);

	// header
$header = "From: ".$from_name." <".$from_mail.">\r\n";
$header .= "Cc: hr.technitab@gmail.com" . "\r\n";
//$header .='Cc: "bhumikajakasaniya@gmail.com"\r\n';
$header .= "Bcc: acc.technitab@gmail.com" . "\r\n";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";

	// message & attachment
$nmessage = "--".$uid."\r\n";
$nmessage .= "Content-type:text/html; charset=iso-8859-1\r\n";
$nmessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$nmessage .= $message."\r\n\r\n";
$nmessage .= "--".$uid."\r\n";
$nmessage .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n";
$nmessage .= "Content-Transfer-Encoding: base64\r\n";
$nmessage .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
$nmessage .= $content."\r\n\r\n";
$nmessage .= "--".$uid."--";


if (mail($mailto,$subject, $nmessage, $header)) {
	echo json_encode(array("error"=>false,"message"=>"PDF has sent on your mail."));
	
} else {
	echo json_encode(array("error"=>true,"message"=>"PDF has not generated."));
}


	
exit;
      
    }

	public function sendNotificationToAdmin($sendById, $title, $notificationArr)
    {
      
        $sendBy = $notificationArr['user_name'];
        $userArr = array(
            'user_name' => $sendBy
        );

        $arr = array_merge($notificationArr, $userArr);
	$tokenResult = DB::table('user')

				->join('user_fcm_token', 'user.id', '=', 'user_fcm_token.user_id')
  
                   		->select('user_fcm_token.token')
      
               			->where(array('user.access_control_id'  => 2))
				->get()->toArray();
       
        // echo "result ".$tokenResult->num_rows." error".$this->con->error;


        if (count($tokenResult) > 0) {
            foreach($tokenResult as $row) {

                $token = $row->token;
                // echo "token ".$token;
                $this->notification($token, $title, $arr);
            }
        }
    }

	public function notification($to, $title, $notificationArr)
    {
        $ch = curl_init("https://fcm.googleapis.com/fcm/send");
        $serverKey = "AIzaSyDJ0MiSBWBsQN5y-ybhWr2GNGFzTPsSfFQ";

        $notification = array(
            "body" => array(
                "module" => $title,
                "json_response" => $notificationArr
            )
        );

        $arrayToSend = array(
            'to' => $to,
            'data' => $notification
        );

        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: key= $serverKey";

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if ($result === false) {
             //echo 'Curl failed ' . curl_error($ch)."\n";
        }else{
           // echo "error\n";
            $this->deleteNotificationToken($to, $result);
           // echo "\n";
            
        }
        curl_close($ch);
    }
    
    public function deleteNotificationToken($token,$responseResult){
        $resultJson = json_decode($responseResult);
        $resultArray = $resultJson->results;
       // print_r($resultArray);
       // echo "\n";
        foreach ($resultArray as $arr){
            
            if(isset($arr->error) && $arr->error == 'NotRegistered'){
                //echo "error value".$arr->error."\n";
		DB::table('user_fcm_token')->where('token', $token)->delete();
                //$this->con->query("DELETE from `user_fcm_token` where `token` = '$token'");
               // echo $this->con->affected_rows." error ".$this->con->error."\n";
            }
        }
    }
	public function exportexcel(Request $request)
    {
		//echo $id;exit;
		//echo phpinfo();exit;
		$post = $request->all();
		$id = $post['id'];
		//DB::enableQueryLog();
		$data = DB::table('emp_main_tec')
					->leftjoin('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
					->leftjoin('master_zoho_vendor', 'master_zoho_vendor.id', '=', 'emp_tec_entry.paid_to_id')
					->leftjoin('master_zoho_project', 'master_zoho_project.id', '=', 'emp_main_tec.project_id')
                     ->select('emp_main_tec.*','emp_main_tec.id as emp_id','emp_tec_entry.*','emp_tec_entry.id as tes_id','master_zoho_project.*','master_zoho_project.id as proj_id','master_zoho_vendor.*','master_zoho_vendor.id as vend_id')
                     ->where(array('emp_main_tec.id' => $id,'emp_tec_entry.paid_by'=> 'Employee',"emp_tec_entry.is_active"=>'0'))
                     ->get()->toArray();
//dd(DB::getQueryLog());
		
		$data= json_decode( json_encode($data), true);
		$userdata = array();
		if(!empty($data)){
		foreach($data as $key=>$val){
//echo "<pre>"; print_R($val);exit;
			$v = array();
			$v['Bill Date'] =  $val['date'];
			$v['Bill Number'] =  $val['emp_id'].'-'.date('dM',strtotime($val['claim_start_date'])).'-'.date('dM',strtotime($val['claim_end_date']));
			$v['Purchase Order'] =  "";
			$v['Bill Status'] =  $val['status'];
			$v['Source Of Supply'] =  "DL";
			$v['Destination Of Supply'] =  $val['source_of_supply'];
			$v['GST Treatment'] =  $val['gst_treatment'];
			$v['GST Identification Number (GSTIN)'] =  $val['gst_num'];
			$v['Is Inclusive Tax'] =  'False';
			$v['TDS Percent'] =  0;
			$v['TDS Account'] =  "";
			$v['Vendor Name'] =  $val['paid_to'];
			$v['Due Date'] =   date("Y-m-d",strtotime("+10days",strtotime($val['date'])));
			$v['Currency Code'] =  'INR';
			$v['Exchange Rate'] =  "1";
			$v['Attachment ID'] =  "";
			$v['Attachment Preview ID'] =  "";
			$v['Attachment Name'] =  "";
			$v['Attachment Type'] =  "";
			$v['Attachment Size'] =  "";
			$v['Item Name'] =  $val['entry_category'];
			$v['Item Description'] =  $val['travel_mode']." ".$val['deprt_date']." ".$val['deprt_time']." ".$val['arrival_date']." ".$val['arrival_time']." ".$val['is_metro']." ".$val['location']." ".$val['from_location']." ".$val['to_location']." ".$val['kilo_meter']." ".$val['mileage']." ".$val['unit_price']." ".$val['total_quantitty']." ".$val['description']." ".$val['paid_to']." ";
			$v['Account'] =  "TEC:".$val['entry_category'];
			$v['Quantity'] =  $val['total_quantitty'];
			$v['Usage unit'] =  "";
			$v['Rate'] =  $val['unit_price'];
			$v['Tax Name'] =  "";
			$v['Tac Percentage'] =  "";
			$v['Tax Amount'] =  "";
			$v['Tax Type'] =  "";
			$v['Item Excemption Code'] =  1;
			$v['Reverce Charge Tax Name'] =  "";
			$v['Reverce Charge Tax Rate'] =  "";
			$v['Reverce Charge Tax Type'] =  "";
			$v['Item Total'] =  $val['bill_amount'];
			$v['Sub Total'] =  $val['bill_amount'];
			$v['Total'] =  $val['emp_total'];
			$v['Balance'] =  "";
			$v['Vendor Notes'] =  "";
			$v['Terms & Condition'] =  "";
			$v['Payment Terms'] =  10;
			$v['Payment Terms Label'] =  'Net 10';
			$v['Is Billable'] =  'False';
			$v['Customer Name'] =  $val['client_name'];
			$v['Project Name'] =  $val['project_name'];
			
			$userdata[] = $v;
			
		}
		}else{
			echo json_encode(array("error"=>true,"message"=>"Record Not found"));
			exit;

		}



		$filename = 'TEC_'.time();
		//echo "<pre>"; print_R($userdata);exit;

		Excel::create($filename, function($excel) use ($userdata) {

			$excel->sheet('mySheet', function($sheet) use ($userdata)
            {
                
			$sheet->fromArray($userdata);
            
		});
        })->store('xlsx');
		

		$user = DB::table('emp_main_tec')

				->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
	
				->join('user', 'user.id', '=', 'emp_main_tec.created_by_id')
		
				->join('master_zoho_project', 'master_zoho_project.id', '=', 'emp_main_tec.project_id')
 
                    		->select('user.name','user.email','user.temp_email','emp_main_tec.*','emp_main_tec.created_date as create','emp_main_tec.id as tec_id','master_zoho_project.*')
 
                    		->where(array('emp_main_tec.id'  => $id,'emp_tec_entry.paid_by'=> 'Employee','emp_tec_entry.is_active' =>0))

                     		->first();

		$log = DB::table('emp_tec_log')
 
                    ->select('*')
     
                    ->where(array('tec_id'  => $id))
		    ->orderBy('id', 'desc')
		    ->limit(5)
		    ->get()
		    ->toArray();
		$last_status = DB::table('emp_main_tec')

				->join('user', 'user.id', '=', 'emp_main_tec.created_by_id')
  
                   		->select('emp_main_tec.*')
      
               			->where(array('emp_main_tec.id'  => $id))

				->orderBy('emp_main_tec.id', 'desc')
				->limit(5)	
				->get()->toArray();
		$bill_amount = DB::table('emp_main_tec')

					->join('emp_tec_entry', 'emp_main_tec.id', '=', 'emp_tec_entry.tec_id')
	
					->join('master_zoho_vendor', 'master_zoho_vendor.id', '=', 'emp_tec_entry.paid_to_id')

					->join('master_zoho_project', 'master_zoho_project.id', '=', 'emp_main_tec.project_id')

		                        ->select(DB::raw("SUM(emp_tec_entry.bill_amount) as count"))
  
			                ->where(array('emp_tec_entry.paid_by'  => 'Employee','emp_main_tec.id'  => $id,"emp_tec_entry.is_active" =>'0'))
 
		                        ->get()->first();
                        

$from_name = 'admin';

$from_mail = 'no-reply@technitabb.com';

$replyto = $user->temp_email;

//$mailto = "bhumikajakasaniya@gmail.com";

$mailto = $user->temp_email;

$subject = 'TEC status change: '.$user->status.' - '.$user->tec_id.'';

$message = "Dear ".$user->name.",

<p></p>The TEC - ".$user->tec_id." has been changed by ".$user->name." on ".$user->create." as per the following information. For further details kindly refer the attached TEC from ESS.<br/><br/>


<b>Name:</b> ".$user->name."<br/>

<b>Employee id:</b> ".$user->role_id."<br/>

<b>Zoho bill  ref:</b> ".$user->tec_id." - ".date('dM',strtotime($user->claim_start_date))." - ".date('dM',strtotime($user->claim_end_date))."<br/>

<b>TEC no.:</b> ".$user->tec_id."<br/>

<b>Trip Id:</b> ".$user->trip_id."<br/>

<b>Project name:</b> ".$user->project_name."<br/>

<b>Client name:</b> ".$user->client_name."<br/>

<b>Claim start & end date:</b> ".$user->claim_start_date." - ".$user->claim_start_date."<br/>

<b>Base & site location:</b> ".$user->base_location." - ".$user->site_location."<br/>

<b>Date:</b> ".$user->submit_date."<br/>
<b>TEC amount:</b> ".$bill_amount->count."<br/>

Latest 5 status of TEC id ".$user->tec_id."<br/><br/>";

foreach($log as $key => $val){

$message .= "<b>Comment:</b> ".$val->comment." <b>Status:</b> ".$val->status." <b>Date:</b> ".$val->created_date."<br>";	



}

$message .= "<br/>
Latest 5 TEC status <br/>";

foreach($last_status as $key=>$val){
	
$message .= "<b>TEC id:</b>".$val->id. "<b> Status:</b>".$val->status."<br/>";


}
$message .= "<br/>Regards.<br/>

Ess App"; 
//echo $message;exit;
$content = file_get_contents(storage_path('exports/'.$filename.'.xlsx'));

$filename = $filename.".xlsx";
//print_r($content);exit;
$content = chunk_split(base64_encode($content));
$uid = md5(uniqid(time()));
$name = basename($filename);

	// header
$header = "From: ".$from_name." <".$from_mail.">\r\n";
$header .= "Reply-To: ".$replyto."\r\n";
//$header .= "Cc: bhumikajakasaniya@gmail.com" . "\r\n";
$header .='Cc: "hr.technitab@gmail.com"\r\n';
$header .= "Bcc: acc.technitab@gmail.com" . "\r\n";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";

	// message & attachment
$nmessage = "--".$uid."\r\n";
$nmessage .= "Content-type:text/html; charset=iso-8859-1\r\n";
$nmessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$nmessage .= $message."\r\n\r\n";
$nmessage .= "--".$uid."\r\n";
$nmessage .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n";
$nmessage .= "Content-Transfer-Encoding: base64\r\n";
$nmessage .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
$nmessage .= $content."\r\n\r\n";
$nmessage .= "--".$uid."--";

if (mail($mailto, $subject, $nmessage, $header)) {
			
				echo json_encode(array("error"=>false,"message"=>"Excel has sent on your mail."));

			
			
		}else{
			
				echo json_encode(array("error"=>true,"message"=>"Excel has not sent on your mail."));

		}

exit;
	
	}
	

    /*public function show($id)
    {
        if (!$id) {
           throw new HttpException(400, "Invalid id");
        }

        $book = Book::find($id);

        return response()->json([
            $book,
        ], 200);

    }

    public function store(Request $request)
    {
        $book = new Book;
        $book->title = $request->input('title');
        $book->price = $request->input('price');
        $book->author = $request->input('author');
        $book->editor = $request->input('editor');

        if ($book->save()) {
            return $book;
        }

        throw new HttpException(400, "Invalid data");
    }

    public function update(Request $request, $id)
    {
        if (!$id) {
            throw new HttpException(400, "Invalid id");
        }

        $book = Book::find($id);
        $book->title = $request->input('title');
        $book->price = $request->input('price');
        $book->author = $request->input('author');
        $book->editor = $request->input('editor');

        if ($book->save()) {
            return $book;
        }

        throw new HttpException(400, "Invalid data");
    }

    public function destroy($id)
    {
        if (!$id) {
            throw new HttpException(400, "Invalid id");
        }

        $book = Book::find($id);
        $book->delete();

        return response()->json([
            'message' => 'book deleted',
        ], 200);
    }*/
}
