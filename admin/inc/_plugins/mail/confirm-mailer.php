<?php
/*
 * CONFIRMATION-MAILER
 * 
 * Copyright: 2009 Navigator Multimedia
 * Created By: Chris Donalds, chrisd@navigatormm.com
 * --------------------------------------------------
 * 
 * Works with Formtoemailpro script to send a confirmation email to recipient
 * 
 * Input: 		Name [optional]
 * 				Confirm-Email [required]
 * 
 * Typically called from formtoemailpro.php in the $external_handler variable
 */    

// control variables

if (defined('BUSINESS')) { 
	// for use with Admin Standard scripts
	$s_sender = BUSINESS; 
	$s_senderemail = OWNER_EMAIL; 
} else { 
	// update the following default info to reflect the current website
	$s_sender = "businessname"; 
	$s_senderemail = "info@website.com"; 
} 

// generate variables from $_REQUEST
$confcount++;
foreach($_REQUEST as $key => $value) { if(!in_array($key, $fields_to_ignore)) ${strtolower($key)} = $value; }
$s_email 			= $confirm_email;
$s_name				= $name;
$s_subject 			= $subject." Confirmation";
if($s_email == "") $s_email = $email;
	
// die if email not provided
	
if($s_email == "") die('Confirm-handler: Missing required field data.');

// if name is not supplied, use the username part of the email value

if($s_name == "") {
	$emailparts = explode("@", $s_email);
	$s_name = $emailparts[0];
}

// prepare confirmation email
$body  = "<html>\n";
$body .= "<head>\n";
$body .= "<title>".$subject."</title>\n";
$body .= "</head>\n";
$body .= "<body>\n";
$body .= "<h2>".$s_sender."</h2>\n";
$body .= "<h3>".$subject."</h3>\n";
$body .= "<p>Below is the information you provided to us:</p>\n\n";
$body .= "<table cellpadding=\"5\" cellspacing=\"1\" bgcolor=\"#000000\">\n";
foreach($_REQUEST as $key => $value) {
	if(!in_array(strtolower($key), $fields_to_ignore) && trim($value) != "" && $key != "email") {
		$body .= "<tr>";
		$body .= "<td valign=\"top\" bgcolor=\"#ececec\" nowrap>".ucwords(ereg_replace("_", " ", $key))."</td>";
		if(!is_array($value)){
			$body .= "<td bgcolor=\"#ffffff\">".ucwords(trim($value))."</td>";
		}else{
			$body .= "<td bgcolor=\"#ffffff\">";
			foreach($value as $subkey => $subvalue) $body .= $subvalue."<br>\n";
			$body .= "</td>";			
		}
		$body .= "</tr>\n";
	}
}
$body .= "</table>\n";
$body .= "<p>Our staff will follow up with you within one business day</p>\n\n";
$body .= "<p>Thank you,<br />".$s_sender."\n";
$body .= "<br>".$s_senderemail."</p>\n";
$body .= "</body>\n";
$body .= "</html>\n";

// send the email
send_email();

function send_email(){
	/* send MIME email to subscriber
	 * this email informs subscriber that they will need to activate their membership
	 */
	
	global $s_sender, $s_senderemail, $s_subject, $s_email, $body;
	
	// include the class
	
	include("mimemail.inc.php");
	$mail = new MIMEMAIL("HTML");
	
	// set the header values
	
	$mail->senderName 	= $s_sender;
	$mail->senderMail 	= $s_senderemail;
	$mail->subject 		= $s_subject;

	ini_set('sendmail_from', $s_senderemail);

	// set the body content
	
	$mail->body 		= $body;
	
	// create the MIME email
	
	$mail->create();
	if(!$mail->created) die ( "Mail not created!" );

	// send the email
	
	$recipients 		= $s_email;
	if(!$mail->send($recipients)) die( $mail->error );
}
?>
