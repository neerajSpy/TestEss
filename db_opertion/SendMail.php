<?php


  /* error_reporting(- 1);
  ini_set('display_errors', 'On');
  set_error_handler("var_dump"); */

class SendMail
{

    function sendPasswordToUser($email, $password)
    {}
    
    function attendanceMail($attendanceDataArray,$userId){
        include_once 'db_class/User.php';
        $userDb = new User();
        
        
        $userEmail = $userDb->getTempEmailById($userId);
        $userName = $userDb->getNameByUserId($userId);
        
        // Multiple recipients
        $from = 'no-reply@technitab.com';
        $CC = 'hr.technitab@gmail.com'; // note the comma
        $to =  $userEmail;
        $BCC = '';
        // Subject
        $subject = 'Attendance: ' . date("d M, y", strtotime($attendanceDataArray['date']));
        
        
        $message = '<html>
                      <body>
                        Dear '.$userName.'<p></p>
                        <b>Date:</b> ' .$attendanceDataArray['date'] . '<br>
                        <b>Punch in:</b> ' . $attendanceDataArray['punch_in'] . '<br>
                        <b>Punch out:</b> ' . $attendanceDataArray['punch_out'] . '<br>
                        <b>Attendance duration:</b> ' . $attendanceDataArray['attendance_duration'] . '<br>
                        <b>Attendance:</b> ' . $attendanceDataArray['attendance'] . ' <br>
                        
                        <p>In case you have punch-out by mistake,Please request a change within 20 hours via manual attendance.</p>
                        <p></p>
                        Regards.<br>
                        Ess App
                        </body>
                        </html>
                        ';
        return $this->sendEmail($from, $to, $CC, $BCC, $message, $subject);
    }

    function leaveRequest($leaveJson, $totalDays, $entitledLeave, $usedLeave, $balanceLeave)
    {
        include_once 'db_class/User.php';
        $userDb = new User();

        $userId = $leaveJson->user_id;
        $userEmail = $userDb->getTempEmailById($userId);
        $userName = $userDb->getNameByUserId($userId);
        $roleId = $userDb->getRoleIdById($userId);

        // Multiple recipients
        $from = 'no-reply@technitab.com';
        $to = 'hr.technitab@gmail.com'; // note the comma
        $CC =  $userEmail;
        $BCC = '';
        // Subject
        $subject = 'Leave request: ' . $userName;

        // Message
        $message = '<html>
                      <body>
                        <p>' . $userName . ' has requested for ' . $leaveJson->leave_type . ' as per information below </p>
                        <b>Employee Name:</b> ' . $userName . '<br>
                        <b>Employee id:</b> ' . $roleId . '<br>
                        <b>Start date:</b> ' . $leaveJson->start_date . '<br>
                        <b>End date:</b> ' . $leaveJson->end_date . '<br>
                        <b>Leave duration:</b> ' . $leaveJson->leave_duration . ' <br>
                        <b>No. off days for which leave applied:</b> ' . $totalDays . '<br>
                        <b>Reason:</B> ' . $leaveJson->reason . ' <br>
                        <b>Description:</b> ' . $leaveJson->description . ' <br>
                        <b>Total leave available with employee till date:</b> ' . $entitledLeave . '<br>
                        <b>Leave taken till date:</b> ' . $usedLeave . '<br>
                        <b>Leave balance:</b> ' . $balanceLeave . '<br>
                        <p>Please response to the the leave request on Ess app</p>
                        <p></p>
                        Regards.<br>
                        Ess App
                        </body>
                        </html>
                        ';
        return $this->sendEmail($from, $to, $CC, $BCC, $message, $subject);
    }

    function leaveAssignedMail($leaveJson, $totalDays, $entitledLeave, $usedLeave, $balanceLeave)
    {
        include_once 'db_class/User.php';
        $userDb = new User();

        $userId = $leaveJson->user_id;
        $userEmail = $userDb->getTempEmailById($userId);
        $userName = $userDb->getNameByUserId($userId);
        $roleId = $userDb->getRoleIdById($userId);

        $assignById = $leaveJson->created_by_id;
        $assignUserEmail = $userDb->getTempEmailById($assignById);
        $assignUserName = $userDb->getNameByUserId($assignById);

        // Multiple recipients
        $from = 'no-reply@technitab.com';
        $to = $userEmail; // note the comma
        $CC = 'hr.technitab@gmail.com';
        $CC .= ',' . $assignUserEmail;
        $BCC = '';
        // Subject
        $subject = 'Leave assigned: ' . $userName;

        // Message
        $message = '<html>
                      <body>
                        <p>' . $assignUserName . ' has assigned for ' . $leaveJson->leave_type . ' as per information below </p>
                        <b>Employee Name:</b> ' . $userName . '<br>
                        <b>Employee id:</b> ' . $roleId . '<br>
                        <b>Start date:</b> ' . $leaveJson->start_date . '<br>
                        <b>End date:</b> ' . $leaveJson->end_date . '<br>
                        <b>Leave duration:</b> ' . $leaveJson->duration . ' <br>
                        <b>No. off days for which leave applied:</b> ' . $totalDays . '<br>
                        <b>Reason:</B> ' . $leaveJson->reason . ' <br>
                        <b>Description:</b> ' . $leaveJson->description . ' <br>
                        <b>Total leave available with employee till date:</b> ' . $entitledLeave . '<br>
                        <b>Leave taken till date:</b> ' . $usedLeave . '<br>
                        <b>Leave balance:</b> ' . $balanceLeave . '<br>
                        <p>Please response to the the leave request on Ess app</p>
                        <p></p>
                        Regards.<br>
                        Ess App
                        </body>
                        </html>
                        ';
        return $this->sendEmail($from, $to, $CC, $BCC, $message, $subject);
    }

    function approveLeave($leaveDataArr, $approveById, $entitleLeave, $usedLeave, $balanceLeave)
    {
        include_once 'db_class/User.php';
        $userDb = new User();

        $userId = $leaveDataArr['created_by_id'];
        $userEmail = $userDb->getTempEmailById($userId);
        $userName = $userDb->getNameByUserId($userId);
        $roleId = $userDb->getRoleIdById($userId);

        $approveUserName = $userDb->getNameByUserId($approveById);
        $approveEmail = $userDb->getTempEmailById($approveById);

        // Multiple recipients
        $from = 'no-reply@technitab.com';
        $to = $userEmail; // note the comma
        $CC = 'hr.technitab@gmail.com';
        $CC .= ', ' . $approveEmail;
        $BCC = '';

        $subject = 'Response to leave request: ' . $approveUserName;

        // Message
        $message = '
                    <html>
                     <body>
                      <p>' . $approveUserName . ' has responded to your leave request as per information below. </p>
                      <b>Employee Name:</b> ' . $userName . '<br>
                      <b>Employee id:</b> ' . $roleId . '<br>
                      <b>No. off days for which leave applied:</b> ' . $leaveDataArr['total_leaves'] . '<br>
                      <b>Start date:</b> ' . $leaveDataArr['start_date'] . '<br>
                      <b>End date:</b> ' . $leaveDataArr['end_date'] . '<br>
                      <b>Duration:</b> ' . $leaveDataArr['duration'] . '<br>
                      <b>Description:</b> ' . $leaveDataArr['description'] . ' <br>
                      <b>Total leave available with employee till date:</b> ' . $entitleLeave . '<br>
                      <b>Leave taken till date:</b> ' . $usedLeave . '<br>
                      <b>Leave balance:</b> ' . $balanceLeave . '<br>
                      
    
                      <p>For more information access the leaves status on your ESS App</p>
                      <p></p>
                      Regards<br>
                      ESS App
                      </body>
                      </html>
                      ';

        return $this->sendEmail($from, $to, $CC, $BCC, $message, $subject);
    }

    function submitProject($projectDataArray)
    {
        include_once 'db_class/User.php';
        $userDb = new User();

        $userName = $userDb->getNameByUserId($projectDataArray->created_by_id);
        $userEmail = $userDb->getTempEmailById($projectDataArray->created_by_id);
        $systemBack = "No";
        if ($projectDataArray->is_system_backup == 1) {
            $systemBack = "Yes";
        }

        $jasReport = "No";
        if ($projectDataArray->is_job_allocation_sheet == 1) {
            $jasReport = "Yes";
        }

        $from = 'no-reply@technitab.com';
        $to = 'hr.technitab@gmail.com'; // note the comma
        $CC = $userEmail;
        $BCC = '';
        // Subject
        $subject = 'Project Approval request: ' . $projectDataArray->project_name;

        // Message
        $message = '<html>
                      <body>
                       <p>Dear Admin</p>
                       Please approve the submitted project as per detail below.<br><br>
                       <b>Name of Employee:</b> ' . $userName . '<br>
                       <b>Project Name:</b> ' . $projectDataArray->project_name . '<br>
                       <b>Project Type:</b> ' . $projectDataArray->project_type . '<br>
                       <b>Start Date - End Date:</b> ' . $projectDataArray->planned_start_date . ' - ' . $projectDataArray->planned_end_date . '<br>
                       <b>JAS recevied:</b> ' . $jasReport . '<br>
                       <b>System backup file received:</b> ' . $systemBack . '<br>
                       <p></p>
                       Regards.<br>
                       Ess App
                       </body>
                       </html>
                       ';

        return $this->sendEmail($from, $to, $CC, $BCC, $message, $subject);
    }
    

    function approveProject($projectDataArray, $projectId)
    {
        include_once 'db_class/User.php';
        $userDb = new User();

        $userName = $userDb->getNameByUserId($projectDataArray->created_by_id);
        $sendTo = $userDb->getTempEmailById($projectDataArray->created_by_id);
        $approveUserEmail = $userDb->getTempEmailById($projectDataArray->modified_by_id);

        $systemBack = "No";
        if ($projectDataArray->is_system_backup == 1) {
            $systemBack = "Yes";
        }

        $jasReport = "No";
        if ($projectDataArray->is_job_allocation_sheet == 1) {
            $jasReport = "Yes";
        }

        $from = 'no-reply@technitab.com';
        $to = $sendTo; // note the comma
        $CC = 'hr.technitab@gmail.com';
        $CC .= ',' . $approveUserEmail;
        $BCC = '';
        // Subject
        $subject = 'Project Approved: ' . $projectDataArray->project_name . ' - ' . $projectId;

        // Message
        $message = '<html>
                      
                      <body>
                       <p>Dear ' . $userName . '</p>
                       Your submitted project has been approved as per detail below.<br><br>
                       <b>Project Id:</b> ' . $projectId . '<br>
                       <b>Name of Employee:</b> ' . $userName . '<br>
                       <b>Project Name:</b> ' . $projectDataArray->project_name . '<br>
                       <b>Project Type:</b> ' . $projectDataArray->project_type . '<br>
                       <b>Project Location:</b> ' . $projectDataArray->location . '<br>
                       <b>Start Date - End Date:</b> ' . $projectDataArray->planned_start_date . ' - ' . $projectDataArray->planned_end_date . '<br>
                       <b>JAS recevied:</b> ' . $jasReport . '</br>
                       <b>System backup file received:</b> ' . $systemBack . '</br>
                       
                          <p></p>
                          Regards.<br>
                          Ess App
                          </body>
                          </html>
                          ';

        return $this->sendEmail($from, $to, $CC, $BCC, $message, $subject);
    }

    function submitVendorMail($vendorJson, $displayName)
    {
        include_once 'db_class/User.php';
        $userDb = new User();

        $userName = $userDb->getNameByUserId($vendorJson->created_by_id);
        $sendTo = $userDb->getTempEmailById($vendorJson->created_by_id);

        $vendor = $vendorJson->company_name;
        if (strlen(trim($vendor)) < 1) {
            $vendor = $vendorJson->first_name . ' ' . $vendorJson->last_name;
        }

        $from = 'no-reply@technitab.com';
        $to = 'hr.technitab@gmail.com'; // note the comma
        $CC =  $sendTo;
        $BCC = '';
        // Subject
        $subject = 'Add Vendor Request: ' . $displayName;

        // Message
        $message = '<html>
                      <head>
                       <title>The following vendor details are personally verified by ' . $userName . ' and scanned copies from the submitted ESS form are attached in this email for further vendor approval. </title>
                      </head>
                      <body>
                       
                       <b>Vendor:</b> ' . $vendor . '<br>
                       <b>Vendor Type:</b> ' . $vendorJson->vendor_type . '<br>
                       <b>Vendor location:</b> ' . $vendorJson->billing_country . ', ' . $vendorJson->billing_state . ', ' . $vendorJson->district . '<br>
                       <b>Agreed Rate:</b> ' . $vendorJson->rate . '<br>
                       <b>Payment Mode:</b> ' . $vendorJson->payment_mode . '<br>
                       <b>Bank Account holder name:</b> ' . $vendorJson->bank_holder_name . '<br>
                       <b>Bank Name:</b> ' . $vendorJson->bank_name . '<br>
                       <b>Bank Account Number:</b> ' . $vendorJson->account_number . '<br>
                       <b>IFSC:</b>' . $vendorJson->ifsc . '<br>
                       
                          <p></p>
                          Regards.<br>
                          Ess App
                          </body>
                          </html>
                          ';
            
        return $this->sendEmail($from, $to, $CC, $BCC, $message, $subject);
    }

    function approveVendorMail($vendorJson, $displayName)
    {
        include_once 'db_class/User.php';
        $userDb = new User();

        $userName = $userDb->getNameByUserId($vendorJson->created_by_id);
        $sendTo = $userDb->getTempEmailById($vendorJson->created_by_id);

        
        $approveById = $userDb->getTempEmailById($vendorJson->modified_by_id);

        $vendor = $vendorJson->company_name;
        if (strlen(trim($vendor)) < 1) {
            $vendor = $vendorJson->first_name . ' ' . $vendorJson->last_name;
        }

        $from = 'no-reply@technitab.com';
        $to = $sendTo; // note the comma
        $CC = 'hr.technitab@gmail.com';
        $CC .= ',' . $approveById;
        $BCC = '';
        // Subject
        $subject = 'Vendor approved: ' . $displayName;

        // Message
        $message = '<html>
                      <head>
                       <title>Dear ' . $userName . '</title><p></p>
                       Your submitted vendor has been approved as per the details below:
                      </head>
                      <body>
                         
                         <b>Vendor:</b> ' . $displayName . '<br>
                         <b>Vendor Type:</b> ' . $vendorJson->vendor_type . '<br>
                         <b>Vendor location:</b> ' . $vendorJson->billing_country . ', ' . $vendorJson->billing_state . ', ' . $vendorJson->district . '<br>
                         <b>Agreed Rate:</b> ' . $vendorJson->rate . '<br>
                         <b>Payment Mode:</b> ' . $vendorJson->payment_mode . '<br>
                         <b>Bank Account holder name:</b> ' . $vendorJson->bank_holder_name . '<br>
                         <b>Bank Name:</b> ' . $vendorJson->bank_name . '<br>
                         <b>Bank Account Number:</b> ' . $vendorJson->account_number . '<br>
                         <b>IFSC:</b> ' . $vendorJson->ifsc . '<br>
                         <p></p>
                          Regards.<br>
                          Ess App
                        </body>
                       </html>
                          ';

        return $this->sendEmail($from, $to, $CC, $BCC, $message, $subject);
    }

    function bookingRequestMail($bookingId,$bookingJson)
    {
        include_once 'db_class/User.php';
        $userDb = new User();

        $userName = $userDb->getNameByUserId($bookingJson->created_by_id);

        $members = "";
        $memberEmail = "";
        foreach ($bookingJson->trip_booking_member as $value) {

            if (strlen($members) < 1) {
                $members = $userDb->getNameByUserId($value->member_id);
                $memberEmail = $userDb->getTempEmailById($value->member_id);
            } else {
                $members .= ',' . $userDb->getNameByUserId($value->member_id);
                $memberEmail .= ',' . $userDb->getTempEmailById($value->member_id);
            }
        }

        include 'db_class/Project.php';
        $projectDb = new Project();

        $projectName = $projectDb->getProjectNameByTripId($bookingJson->trip_id);

        $from = 'no-reply@technitab.com';
        $to = 'acc.technitab@gmail.com'; // note the comma
        $CC = 'kavinder.technitab@gmail.com, hr.technitab@gmail.com';
        $CC .= ',' . $memberEmail;
        $BCC = '';
        // Subject
        $subject = 'Booking Request: ' . $bookingJson->trip_id . ',' . $bookingJson->user_booking_mode;

        $message = 'Please process the following booking request by ' . $userName . ' and update in ESS for intimation <p></p>
                   Members ' . $members . '<br>' . $userName . ' shall attach the related invoice billpayment receipt on this booking accordingly in ESS';



        if (strtolower($bookingJson->user_booking_mode) == strtolower('Hotel/PG/Lodge')) {
            $dates = $this->converDateToDM($bookingJson->user_check_in) . ' - ' . $this->converDateToDM($bookingJson->user_check_out);
            $location = $bookingJson->user_city_area;

        } else {
            $dates = $this->converDateToDM($bookingJson->user_travel_date);
            $location = "From : ".$bookingJson->user_source." To: ".$bookingJson->user_destination;

        }


        $message = '<html>
                      <head>
                       <title>Payment Request of booking by ' . $userName . '</title>
                      </head>
                      <body>
                       <p>Dear Admin</p>
                       Please process the following booking request by ' . $userName . ' and update in ESS for intimation <p></p>
                   Members ' . $members . '<br>' . $userName . ' shall attach the related invoice billpayment receipt on this booking accordingly in ESS
                       
                       <b>Trip id:</b> ' . $bookingJson->trip_id.'<br>
                       <b>Booking id:</b> ' . $bookingId . '<br>
                       <b>Project Name:</b> ' . $projectName . '<br>
                       <b>Booking Mode:</b> ' . $bookingJson->user_booking_mode . '<br>
                       <b>Booking Detail:</b><br>
                       <b>Dates:</b> ' . $dates . '<br>
                       <b>Location:</b> ' . $location . '<br>                     
                       <b>Vendor:</b> ' . $bookingJson->user_vendor . '<br>                     
                       <b>Preference: </b>'.$bookingJson->user_instruction.'<br>
                       
                       <p></p>
                       Regards.<br>
                       Ess App
                       </body>
                       </html>
                       ';
        return $this->sendEmail($from, $to, $CC, $BCC, $message, $subject);
    }
    
    function bookingPaymentRequest($userId, $bookingId, $bookingArr, $vendorArr)
    {
        include_once 'db_class/User.php';
        $userDb = new User();

        $userName = $userDb->getNameByUserId($userId);
        $userEmail = $userDb->getTempEmailById($userId);

        include 'db_class/Project.php';
        $projectDb = new Project();

        $projectName = $projectDb->getProjectNameByTripId($bookingArr['trip_id']);

        $dates = "";
        $location = "";

        if (strtolower($bookingArr['admin_booking_mode']) == strtolower('Hotel/PG/Lodge')) {
            $dates = $this->converDateToDM($bookingArr['admin_check_in']) . ' - ' . $this->converDateToDM($bookingArr['admin_check_out']);
            $location = $bookingArr['admin_city_area'];
        } else {
            $dates = $this->converDateToDM($bookingArr['admin_departure_date_time']) . ' - ' . $this->converDateToDM($bookingArr['admin_arrival_date_time']);
            $location = $bookingArr['admin_source'] . ' - ' . $bookingArr['admin_destination'];
        }
        
        $vendorId = sprintf("%04d", $vendorArr['id']);
        $reference = 'V'.$vendorId.' '.$dates.' '.$userName;

        $from = 'no-reply@technitab.com';
        $to = 'acc.technitab@gmail.com'; // note the comma
        $CC = 'hr.technitab@gmail.com';
        $CC .= ',' . $userEmail;
        $BCC = '';
        // Subject
        $subject = 'Booking Payment Request: by '.$userName. ' for '.$vendorArr['contact_name'];

        // Message
        $message = '<html>
                      <head>
                       <title>Payment Request of booking by ' . $userName . '</title>
                      </head>
                      <body>
                       <p>Dear Admin</p>
                       Please procced payment as per below detail<p></p>
                       
                       <b>Trip id:</b> ' . $bookingArr['trip_id'].'<br>
                       <b>Booking id:</b> ' . $bookingId . '<br>
                       <b>Project Name:</b> ' . $projectName . '<br>
                       <b>Booking Mode:</b> ' . $bookingArr['admin_booking_mode'] . '<br>
                       <b>Rate:</b> ' . $bookingArr['rate'] .'<br>
                       <b>Amount:</b> ' . $bookingArr['total_amount'] .'<br>
                       <b>Dates:</b> ' . $dates . '<br>
                       <b>Location:</b> ' . $location . '<br>
                       <b> Reference:</b> '.$reference.'<br>                       
                       <b>Vendor Detail</b><br>
                       <b>Vendor name & id:</b> ' . $vendorArr['contact_name'] . '<br>
                       <b>Vendor payment mode:</b> ' . $vendorArr['payment_mode'] . '<br>
                       <b>Bank A/c holder name:</b> ' . $vendorArr['bank_holder_name']. '<br>
                       <b>Account No.:</b>'.$vendorArr['account_number'].'<br>
                       <b>IFSC code:</b>'.$vendorArr['ifsc'].'<br>
                       <b>Address:</b>'.$vendorArr['bank_address'].'<br>
                       
                       <p></p>
                       Regards.<br>
                       Ess App
                       </body>
                       </html>
                       ';

        return $this->sendEmail($from, $to, $CC, $BCC, $message, $subject);
    }

    function bookingPaymentEntryMail($bookingId,$paymentJson,$paymentId, $bookingArr, $vendorArr)
    {
        include_once 'db_class/User.php';
        $userDb = new User();
        
        $approverName = $userDb->getNameByUserId($paymentJson->created_by_id);
        $approverEmail = $userDb->getTempEmailById($paymentJson->created_by_id);
        
        $userName = $userDb->getNameByUserId($bookingArr['created_by_id']);
        $userEmail = $userDb->getTempEmailById($bookingArr['created_by_id']);
       
        $dates = "";
        
        
        if (strtolower($bookingArr['admin_booking_mode']) == strtolower('Hotel/PG/Lodge')) {
            $dates = $this->converDateToDM($bookingArr['admin_check_in']) . ' - ' . $this->converDateToDM($bookingArr['admin_check_out']);
           
        } else {
            $dates = $this->converDateToDM($bookingArr['admin_departure_date_time']) . ' - ' . $this->converDateToDM($bookingArr['admin_arrival_date_time']);
           
        }
        
        $from = 'no-reply@technitab.com';
        $to = $userEmail; // note the comma
        $CC = 'hr.technitab@gmail.com,acc.technitab@gmail.com';
        $CC .= ',' . $approverEmail;
        $BCC = '';
        // Subject
        $subject = 'Payment Made: '.$vendorArr['contact_name'].' Booking id '.$bookingArr['id'];
        
        // Message
        $message = '<html>
                      <body>
                       <p>Dear User</p>
                       Following payment request by ' . $approverName .' is processed and updated in ESS as per details below.<br> 
                       '.$userName. ' shall attach the related invoice / bill / payment receipt on this booking accordingly in ESS.<p></p>
                           
                       <b>Vendor & vendor code:</b> ' . $vendorArr['contact_name'].'<br>
                       Vendor type: ' . $vendorArr['CF.Vendor type'] . '<br>
                       Vendor location: ' . $vendorArr['district'] .', '.$vendorArr['billing_country'].', '.$vendorArr['billing_state']. '<br>
                       Vendor phone: ' . $vendorArr['mobile_phone'] . '<br>
                       <b>Booking id:</b> ' . $bookingArr['id'] . '<br>
                       Dates: ' . $dates . '<br>
                       Nights: ' . $bookingArr['quantity'] . '<br>
                       <b>Booking rate per night:</b> '.$bookingArr['rate'].'<br>
                       
                       Amount paid to vendor: ' . $paymentJson->paid_amount. '<br>
                       Payment reference: ' . $paymentJson->reference_number. '<br>
                       <b>Payment id:</b>'.$paymentId.'<br>
                           
                       <p></p>
                       Regards.<br>
                       Ess App
                       </body>
                       </html>
                       ';
        
        return $this->sendEmail($from, $to, $CC, $BCC, $message, $subject);
    }
    
    
    function tecMail($tecDataArr,$date,$latestTecStatusArr = NULL,$latestTecsStatusArr = NULL,$totalExpense,$userId,$adminUserId = NULL){
        include_once 'db_class/User.php';
        $userDb = new User();
        
        $userName = $userDb->getNameByUserId($userId);
        $userEmail = $userDb->getTempEmailById($userId);
        
        
        $to = "";
        $CC = "";
        $subjectUserName = "";
        $greetingUserName = "";
        
        
        if(null === $adminUserId){
            $to = 'acc.technitab@gmail.com';
            $CC = 'hr.technitab@gmail.com';
            $CC .= ',' . $userEmail;
            
            $greetingUserName = "ESS Admin";
            
            $subjectUserName = $userName;
        }else{
            $to = $userEmail;
            $CC = 'acc.technitab@gmail.com, hr.technitab@gmail.com';
            $CC .= $userDb->getTempEmailById($adminUserId);
            
            $subjectUserName = $userDb->getNameByUserId($adminUserId);
            $greetingUserName = $userDb->getNameByUserId($userId);
        }
        
        $from = 'no-reply@technitab.com';
        $BCC = '';
        // Subject
        $subject = 'TEC status change: '.$tecDataArr['status']. ' - '.$tecDataArr['id'];
        
         $tecStatusHTML = "";
        foreach ($latestTecStatusArr as $value) {
            $tecStatusHTML .= ' <b>Comment:</b> '.$value['comment'].' <b> Status:</b> '.$value['status'].' <b> Date:</b> '.$value['created_date'].'<br>';
        }
        
       $tecsStatusHTML = "";
        foreach ($latestTecsStatusArr as $value) {
            $tecsStatusHTML.= '<b> TEC id:</b> '.$value['id'].'<b> Status:</b> '.$value['status'].'<br>';
        }
        
        
        // Message
        $message = '<html>
                      <body>
                       <p>Dear '.$greetingUserName.'</p>
                       The TEC id '.$tecDataArr['id'].' has been changed by '.$subjectUserName.' on '.$date.' as per the following information. For further details kindly refer the attached TEC from ESS.<p></p>
                           
                       <b>Name:</b> ' . $tecDataArr['name'].'<br>
                       <b>Employee Id:</b> ' . $tecDataArr['role_id'] . '<br>
                       <b>Zoho bill ref:</b> ' . $tecDataArr['id'].'-'.$this->converDateToDM($tecDataArr['claim_start_date']).'-'.$this->converDateToDM($tecDataArr['claim_end_date']). '<br>
                       <b>TEC no.:</b> ' . $tecDataArr['id'] . '<br>
                       <b>Trip id:</b> ' . $tecDataArr['trip_id']. '<br>
                       <b>Project Name:</b> ' . $tecDataArr['project_name'] . '<br>
                       <b>Client Name:</b> ' . $tecDataArr['client_name'] .'<br>
                       <b>Claim start & end date:</b> ' . $tecDataArr['claim_start_date'].' - '.$tecDataArr['claim_end_date'] . '<br>
                       <b>Base & site location:</b> ' .$tecDataArr['base_location'].'-'.$tecDataArr['site_location'] . '<br>
                       <b>Submitted Date:</b> ' . $tecDataArr['submit_date'] . '<br>
                       <b>TEC amount:</b> ' . $totalExpense . '
                       <p></p> Latest 5 status of TEC id '.$tecDataArr['id'].' <br>'.
                       $tecStatusHTML.'<p> </p> Latest 5 TEC status <br>'.$tecsStatusHTML.'<p></p>
                           
                       Regards.<br>
                       Ess App
                       </body>
                       </html>
                       ';
        return $this->sendEmail($from, $to, $CC, $BCC, $message, $subject);
    }

    function sendEmail($from, $to, $CC, $BCC, $message, $subject)
    {
        $from_name = 'ESS';
        $nmessage = "";

        // header
        $header = "From: " . $from_name . " <" . $from . ">\r\n";
        $header .= "Cc: " . $CC . "\r\n";
        $header .= "Bcc: " . $BCC . "\r\n";
        // $header .= "Reply-To: ".$replyto."\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: text/html; charset=iso-8859-1\r\n\r\n";
        $nmessage .= $message . "\r\n\r\n";

        return mail($to, $subject, $nmessage, $header);
    }
    
    function converDateToDM($date){
        return date("dM", strtotime($date));
    }
    
   
}


