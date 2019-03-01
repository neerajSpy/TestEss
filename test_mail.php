<?php
$from_name = 'ESS';
$message = "crone job";
$from = "no-reply@technitab.com";
$to = "neeraj.technitab@gmail.com";
$CC = "neeraj.kumar@technitab.com";
$subject = "Test Crone job";

// header
$header = "From: " . $from_name . " <" . $from . ">\r\n";
$header .= "Cc: " . $CC . "\r\n";
/* $header .= "Bcc: " . $BCC . "\r\n"; */
// $header .= "Reply-To: ".$replyto."\r\n";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-Type: text/html; charset=iso-8859-1\r\n\r\n";
$nmessage .= $message . "\r\n\r\n";

if(mail($to, $subject, $nmessage, $header)){
    echo "true";
}else{
    echo "false";
}

?>