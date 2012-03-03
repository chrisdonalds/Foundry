<?php
/*
PAYPAL PLUG-IN
PayPal IPN and PaymentFlow processor
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================
*/

define('API_USERNAME', $api_username);		// username looks like an email without the @
define('API_PASSWORD', $api_password);		// NOT the login password
define('API_SIGNATURE', $api_signature);
include(SITE_PATH.PLUGINS_FOLDER."paypal/doDirectPaymentAPI.php");

/**
 * Setup Paypal parameters
 * @param <type> $golive
 */
function paypal_setup($golive = false){
	if($golive){
		// LIVE
		define('API_ENDPOINT', 'https://api-3t.paypal.com/nvp');
		define('API_URL', 'https://www.paypal.com/cgi-bin/webscr');
	}else{
		// SANDBOX - TEST
		define('API_ENDPOINT', 'https://api.sandbox.paypal.com/nvp/');
		define('API_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr');
	}
}

/**
 * Send form data to PayPal via cURL
 * @param <type> $data
 */
function paypal_senddata_silent($data){
	$url  = API_URL;
	$post = http_build_query($data);
	die("paypal_senddata => $post");

	$ch = curl_init();			// Initialize a CURL session.
	curl_setopt($ch, CURLOPT_URL, $url);  // Pass URL as parameter.
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Redirect to page where its
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // Return Page contents.
	curl_setopt($ch, CURLOPT_HEADER, 1);

	$result = curl_exec($ch);	// grab URL and pass it to the variable.
	curl_close($ch);			// close curl resource, and free up system resources.

	//header("Location:https://www.paypal.com/cgi-bin/webscr");
}

function paypal_senddata_formsim($data){
	$url  = API_URL;
	$post = http_build_query($data);
	header("location: {$url}?{$post}");
}
?>
