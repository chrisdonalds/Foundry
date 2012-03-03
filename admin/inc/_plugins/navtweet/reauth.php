<?php
/**
 * NAVTWEET PLUG-IN
 * Web Template 3.0
 * ----------------
 * PROCESS 2
 */

// THIS FILE REDIRECTS THE USER TO TWITTER FOR AUTHORIZATION //

/* Start session and load library. */
$nt_ssid = $_GET['nt_ssid'];
session_start();
require_once("twitteroauth/twitteroauth.php");
require_once("config.php");

/* Build TwitterOAuth object with client credentials. */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
 
/* Get temporary credentials. */
$request_token = $connection->getRequestToken(OAUTH_CALLBACK."?nt_ssid={$nt_ssid}");
if($request_token == '' || $request_token == null) die("There was a problem obtaining temporary credentials from Twitter!");

/* Save temporary credentials to session. */
$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $token_secret = $request_token['oauth_token_secret'];
$_SESSION['access_token'] = $request_token;

/* If last connection failed don't display authorization link. */
switch ($connection->http_code) {
	case 200:
		/* Build authorize URL and redirect user to Twitter so they can login manually.
		   Callback URL from Twitter is twitter-callback.php */
		if(nt_updateRec("navtweet", "oauth_token = '".$token."', oauth_token_secret = '".$token_secret."'", "id = '$nt_ssid'")){
			$url = $connection->getAuthorizeURL($token);
			header("Location: ".$url);
		}else{
			die("There was a problem updating NavTweet db record #$nt_ssid");
		}
		break;
	default:
		/* Show notification if something went wrong. */
		echo "Could not connect to Twitter.  Refresh the page or try again later.";
		exit;
}
?>
