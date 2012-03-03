<?php
/* SEND-TO-FRIEND ALTMAILER 
 * Author: Chris Donalds
 * Date: October 20, 2010
 */

/**
 * Send mail to friend
 * @param <type> $subject
 * @return <type>
 */
function altmail($subject){
	// two name and two email elements in $_REQUEST array
	// look for any name or email element other than 'name' and 'email'

	foreach($_REQUEST as $key => $data){
		$lkey = strtolower($key);
		if(strpos($lkey, 'email') !== false){
			if($lkey == 'email' || $lkey == 'your_email') {
				$sender_email = $data;
			}else{
				$friend_email = $data;
			}
		}elseif(strpos($lkey, 'name') !== false){
			if($lkey == 'name' || $lkey == 'your_name') {
				$sender_name = $data;
			}else{
				$friend_name = $data;
			}
		}elseif(strpos($lkey, 'message') !== false || strpos($lkey, 'comment') !== false){
			$comment = $data;
		}elseif(strpos($lkey, 'url' && $lkey != 'redirect_url') !== false){
			$url = $data;
		}else{
			$$lkey = $data;
		}
	}

	if($sender_email != '' && $sender_name != '' && $friend_email != '' && $friend_name != ''){
		$htmlmessage = "<html><head><title>{$subject}</title></head><body>";
		$htmlmessage.= "<h2>{$subject}</h2>\n";
		$htmlmessage.= "<p>{$friend_name},</p>\n";
		$htmlmessage.= "<p>Your friend, ".$sender_name.", recommended that you visit {$url}.</p>\n";
		$htmlmessage.= "<p>From: ".$sender_name." <a href=\"mailto:{$sender_email}\">".$sender_email."</a></p>\n";
		if($comment != '') $htmlmessage.= "Message: ".$comment."<br/>\n";
		if($url != ''){
			$htmlmessage.= "<p>Check out this link: <a href=\"{$url}\">{$url}</a></p>\n";
		}else{
			$url = WEB_URL;
			$htmlmessage.= "<p>Check out this site: <a href=\"{$url}\">{$url}</a></p>\n";
		}
		$htmlmessage.= "--<br/>\nThis mail was sent via a send-to-friend form on ".SITE_NAME." <a href=\"".WEB_URL."\">".WEB_URL."</a>";
		$htmlmessage.= "</body></html>";

		$headers = "From: $sender_name <$sender_email>\n";
		$headers.= "To: $friend_name <$friend_email>\n";
		$headers.= "Subject: $subject\n";
		$headers.= "MIME-Version: 1.0\n";
		$headers.= "Content-Type: text/html; charset=\"ISO-8859-1\"\n";
		$ok = mail($friend_email, $subject, $htmlmessage, $headers);
	}
	return $ok;
}
?>
