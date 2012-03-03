<?php
/**
 * NAVTWEET PLUG-IN
 * Web Template 3.0
 * ----------------
 * PROCESS 3
 */

// THIS FILE IS CALLED BY TWITTER AFTER SUCCESSFUL AUTHENTICATION //
// NORMAL TWITTER OPERATION CONTINUES NOW //

/* load the application core */
session_start();
include ("navtweet.core.php");

/* start error log */
nt_initErrorMsg();

$nt_ssid = $_GET['nt_ssid'];
$dbsess = nt_getRec("navtweet", "*", "id='$nt_ssid'", "", "1");
if(count($dbsess) == 0) die("There was a problem recalling NavTweet db record #$nt_ssid in callback!");

$_SESSION = $dbsess[0];
$_SESSION['access_token']['oauth_token'] = $dbsess[0]['oauth_token'];
$_SESSION['access_token']['oauth_token_secret'] = $dbsess[0]['oauth_token_secret'];

/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

/* Request access tokens from twitter */
$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

/* Save the access tokens. Normally these would be saved in a database for future use. */
$_SESSION['access_token'] = $access_token;

/* Remove no longer needed request tokens */
unset($_SESSION['oauth_token']);
unset($_SESSION['oauth_token_secret']);

/* If HTTP response is 200 continue otherwise send to connect page to retry */
if ($connection->http_code == 200) {
	/* The user has been verified and the access tokens can be saved for future use */
	$_SESSION['status'] = 'verified';

	/* continue with previous process */
	$result = startTwitterOAuth();
	if($result !== false) {
		header("location: ".$_SESSION['return_url']);
	}
} else {
	/* authorization error */
	echo "Unable to obtain authorization from Twitter.<hr>";
	var_dump($dbsess);
	//var_dump($connection);
	//header("location: ./reauth.php?id=".$_SESSION['id']);
	exit;
}
?>
