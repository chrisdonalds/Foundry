<?php
/*
 * SUBSCRIBER-MAILER
 * 
 * Copyright: 2009 Navigator Multimedia
 * Created By: Chris Donalds, chrisd@navigatormm.com
 * --------------------------------------------------
 * 
 * Works with Formtoemailpro script to send a subscription email to recipient
 * 
 * Input: 		Name [optional]
 * 				Recipient-Email [required]
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
include("mimemail.inc.php");
ini_set('sendmail_from', $s_senderemail);

// generate variables from $_REQUEST
$confcount++;
$fields_to_ignore = array('action','external_handler','email_template_filename','error_output_type','send','required_fields_dyn','redirect_url','Submit','submit','recaptcha_challenge_field','recaptcha_response_field','thankyou');

foreach($_REQUEST as $key => $value) { if(!in_array($key, $fields_to_ignore)) ${strtolower($key)} = $value; }
$s_emails 			= $emails;
$s_names			= $names;
$s_subject 			= $sendmsg_subj;
$s_body				= $sendmsg_content;
$s_attachment 		= $sendmsg_file;
$s_unsubscribelink 	= $unsubscribelink;
	
// die if email not provided
	
if(!isset($s_emails)) die('Subscriber-handler: Missing required field data.');

// if name is not supplied, use the username part of the email value
foreach($s_emails as $key => $s_email){
	$s_name = $s_names[$key];

	if($s_email != ""){
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
		$body .= "<h3>".$s_subject."</h3>\n";
		$body .= "<p>$s_body</p>\n\n";
		$body .= "<hr>\n";
		$body .= "<p>This email was sent to $s_email.  If you have received it in error, please contact <a href=\"mailto: $s_senderemail\">$s_senderemail</a>.  If you wish to un-subscribe from future mailings, visit <a href=\"".$s_unsubscribelink."?e=".$s_email."\">".$s_unsubscribelink."?e=".$s_email."</a></p>\n\n";
		$body .= "<p>Thank you,<br />".$s_sender."\n";
		$body .= "<br>".$s_senderemail."</p>\n";
		$body .= "</body>\n";
		$body .= "</html>\n";

		// send the email
		send_email($s_email, $s_subject, $s_sender, $s_senderemail, $body, $s_attachment);
	}
}

function send_email($s_email, $s_subject, $s_sender, $s_senderemail, $body, $s_attachment){
	/* send MIME email to subscriber
	 * this email informs subscriber that they will need to activate their membership
	 */
	
	// include the class
	
	$mail = new MIMEMAIL("HTML");
	
	// set the header values
	
	$mail->senderName 	= $s_sender;
	$mail->senderMail 	= $s_senderemail;
	$mail->subject 		= $s_subject;
	if($s_attachment != "") 
		$mail->attachments[] = $s_attachment;

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
