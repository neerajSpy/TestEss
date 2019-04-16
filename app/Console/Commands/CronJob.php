<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class CronJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CronJob:cronjob';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User Name Change Successfully';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

		\DB::table('leave_assigned_to_user as a')
   
	->join('leave_entitlement as b', 'a.user_id', '=', 'b.user_id')
    
	->where('a.leave_type_id', DB::raw('b.leave_type_id'))
   
	->update([ 'b.entitled_leave' => DB::raw('b.entitled_leave + a.assigned_to_user_leave') ,'b.balance_leave' => DB::raw('b.balance_leave + a.assigned_to_user_leave'),'b.modified_date' => date('Y-m-d h:i:s')]);
    
	  $userdata = DB::table('leave_assigned_to_user as a')->join('leave_entitlement as b', 'a.user_id', '=', 'b.user_id')
					->where('a.leave_type_id', DB::raw('b.leave_type_id'))->get();
					
				
	  foreach($userdata as $key => $val){ 

		$user = DB::table('leave_assigned_to_user as a')
					
			->where('a.leave_type_id', $val->leave_type_id)->get()->first();
			  $data = array(
					'user_id' => $val->user_id ,
					'leave_entitlement_id' => $val->id ,
					'leave_type_id' => $val->leave_type_id,
					'update_date' => date('Y-m-d h:i:s'),
					'length_day' => $user->assigned_to_user_leave,
					'notes' => 'leave request',
					);
	  DB::table('leave_entitlement_history')->insert($data);
	
	  $paydata = array(
					'user_id' => $val->user_id ,
					'payroll_id' => DB::getPdo()->lastInsertId(),
					'created_date' => date('Y-m-d h:i:s'),
					
					);
	  DB::table('payroll_history')->insert($paydata);
		
	   } 
	

    $this->info('Leave added Successfully!');
    }
}
