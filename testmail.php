<?php


$to = "someone@gmail.com";
$bodypagl = "Test BCC";
$header  = 'MIME-Version: 1.0' . "\r\n";
$header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$header .= "From: someone@hotmail.com";
$header .= "/r/n";
$header .="Bcc: someone@yahoo.com";
$header .= "/r/n";
$header .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
;

if(mail($to, "Some Subject", $bodypagl, $header)){
	echo 'Send..';           
}else{
	print_r(error_get_last());            
}
?>