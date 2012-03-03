<?php
/**
 * NAVTWEET PLUG-IN
 * Web Template 3.0
 * ----------------
 * PROCESS 1
 */
/* Load required lib files. */
require_once('config.php');

checkPluginHome('Navtweet', 'admin');
checkPluginPHPVersion('Navtweet', 5.2);

if(session_id() == "") session_start();
require_once('twitteroauth/twitteroauth.php');

/**
 * Start a new Twitter session
 * @param <type> $twitter_section
 * @param <type> $twitter_op
 * @param <type> $twitter_content
 * @param <type> $twitter_link
 * @param <type> $twitter_linkname
 * @param <type> $return_url
 */
function createTwitterSession($twitter_section, $twitter_op, $twitter_content, $twitter_link, $twitter_linkname, $return_url){
	$nt_ssid = nt_insertRec("navtweet", "twitter_section, twitter_op, twitter_content, twitter_link, twitter_linkname, return_url", "'$twitter_section', '$twitter_op', '$twitter_content', '$twitter_link', '$twitter_linkname', '$return_url'");
	if($nt_ssid === false) die("There was a problem creating the NavTweet db record!");
	
	$url = WEB_URL.ADMIN_FOLDER.PLUGINS_FOLDER."navtweet/reauth.php?nt_ssid={$nt_ssid}";

	// create new window to contain all further twitter processes
	if(headers_sent()){
		print <<<EOT
<script type="text/javascript" language="javascript">
	alert('Twitter may ask you sign-in to your account to submit posts to Twitter.  This is required to complete this process.');
	window.location = '{$url}';
</script>
EOT;
	}else{
		header("location: ".$url);
	}
	exit;
}

/**
 * Initiate the Twitter OAuth class
 * @global <type> $access_token
 * @global <type> $connection
 * @global <type> $content
 * @global <type> $user
 * @global <type> $err
 * @return <type>
 */
function startTwitterOAuth(){
	global $access_token, $connection, $content, $user, $err;

	/* If access tokens are not available redirect to connect page. */
	if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
		/* No access tokens: persist parameters and, redirect and handle remaining operations in separate call */
		header("location: ./reauth.php?id=".$_SESSION['id']);
	}
	
	// reestablish function variables
	$twitter_section = $_SESSION['twitter_section'];
	$twitter_op = $_SESSION['twitter_op'];
	$twitter_content = $_SESSION['twitter_content'];
	$twitter_link = $_SESSION['twitter_link'];
	$twitter_linkname = $_SESSION['twitter_linkname'];

	/* Check limit rate */
	$content = $connection->get('account/rate_limit_status');
	if ($content->remaining_hits < 1) {
		echo "You have reached your Twitter account rate limit (number of posts per hour).  Try again later.";
		return false;
	}

	/* Get logged in user for methods. */
	$user = $connection->get('account/verify_credentials');

	/* Clean up database record */
	nt_deleteRec("navtweet", "id=".$_SESSION['id']);

	/* Prepare and process content */
	return processOAuth($twitter_section, $twitter_op, prepOauthMessage($twitter_content, $twitter_link, $twitter_linkname));
}

/**
 * Process an OAuth operation
 * @global  $access_token
 * @global  $connection
 * @global  $content
 * @global  $user
 * @param <type> $twitter_section
 * @param <type> $twitter_op
 * @param <type> $twitter_content
 * @return <type>
 */
function processOAuth($twitter_section, $twitter_op, $twitter_content = ""){
	global $access_token, $connection, $content, $user;

	if($twitter_section == 'help'){
		/**
		 * Help Methods.
		 */

		/* help/test */
		if($twitter_op == 'test') {
			$method = 'help/test';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}
	}

	if($twitter_section == 'timeline'){
		/**
		 * Timeline Methods.
		 */

		/* statuses/public_timeline */
		if($twitter_op == 'public_timeline') {
			$method = 'statuses/public_timeline';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* statuses/public_timeline */
		if($twitter_op == 'home_timeline') {
			$method = 'statuses/home_timeline';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* statuses/friends_timeline */
		if($twitter_op == 'friends_timeline') {
			$method = 'statuses/friends_timeline';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* statuses/user_timeline */
		if($twitter_op == 'user_timeline') {
			$method = 'statuses/user_timeline';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* statuses/mentions */
		if($twitter_op == 'mentions') {
			$method = 'statuses/mentions';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* statuses/retweeted_by_me */
		if($twitter_op == 'retweeted_by_me') {
			$method = 'statuses/retweeted_by_me';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* statuses/retweeted_to_me */
		if($twitter_op == 'retweeted_to_me') {
			$method = 'statuses/retweeted_to_me';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* statuses/retweets_of_me */
		if($twitter_op == 'retweets_of_me') {
			$method = 'statuses/retweets_of_me';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}
	}

	if($twitter_section == 'status'){
		/**
		 * Status Methods.
		 */

		/* statuses/update */
		if($twitter_op == 'update') {
			date_default_timezone_set('GMT');
			$parameters = array('status' => $twitter_content);
			$method = 'statuses/update';
			$status = $connection->post($method, $parameters);
			return prepresponse($method, $status, $connection->http_code, $parameters);
		}

		/* statuses/show */
		if($twitter_op == 'show') {
			$method = "statuses/show/{$status->id}";
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* statuses/destroy */
		if($twitter_op == 'destroy') {
			$method = "statuses/destroy/{$status->id}";
			return prepresponse($method, $connection->delete($method), $connection->http_code);
		}

		/* statuses/retweet */
		if($twitter_op == 'retweet') {
			$method = 'statuses/retweet/6242973112';
			return prepresponse($method, $connection->post($method), $connection->http_code);
		}

		/* statuses/retweets */
		if($twitter_op == 'retweets') {
			$method = 'statuses/retweets/6242973112';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}
	}

	if($twitter_section == 'user'){
		/**
		 * User Methods.
		 */

		/* users/show */
		if($twitter_op == 'show') {
			$method = 'users/show/27831060';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* users/search */
		if($twitter_op == 'search') {
			$parameters = array('q' => 'oauth');
			$method = 'users/search';
			return prepresponse($method, $connection->get($method, $parameters), $connection->http_code, $parameters);
		}

		/* statuses/friends */
		if($twitter_op == 'friends') {
			$method = 'statuses/friends/27831060';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* statuses/followers */
		if($twitter_op == 'followers') {
			$method = 'statuses/followers/27831060';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}
	}

	if($twitter_section == 'list'){
		/**
		 * List Methods.
		 */

		/* POST lists */
		if($twitter_op == 'post') {
			$parameters = array('name' => 'Oauth_tweet');
			$method = "{$user->screen_name}/lists";
			$list = $connection->post($method, $parameters);
			return prepresponse($method, $list, $connection->http_code, $parameters);
		}

		/* POST lists id */
		if($twitter_op == 'post_id') {
			$parameters = array('name' => 'Oauth_tweet List 2');
			$method = "{$user->screen_name}/lists/{$list->id}";
			$list = $connection->post($method, $parameters);
			return prepresponse($method, $list, $connection->http_code, $parameters);
		}

		/* GET lists */
		if($twitter_op == 'get') {
			$method = "{$user->screen_name}/lists";
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* GET lists id */
		if($twitter_op == 'get_id') {
			$method = "{$user->screen_name}/lists/{$list->id}";
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* DELETE list */
		if($twitter_op == 'delete') {
			$method = "{$user->screen_name}/lists/{$list->id}";
			return prepresponse($method, $connection->delete($method), $connection->http_code);
		}

		/* GET list statuses */
		if($twitter_op == 'status') {
			$method = "oauthlib/lists/4097351/statuses";
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* GET list members */
		if($twitter_op == 'members') {
			$method = "{$user->screen_name}/lists/memberships";
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* GET list subscriptions */
		if($twitter_op == 'subscriptions') {
			$method = "{$user->screen_name}/lists/subscriptions";
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}
	}

	if($twitter_section == 'members'){
		/**
		 * List Members Methods.
		 */

		/* Create temp list for list member methods. */
		if($twitter_op == 'create_temp') {
			$parameters = array('name' => 'Oauth_tweet Temp');
			$method = "{$user->screen_name}/lists";
			$list = $connection->post($method, $parameters);
		}

		/* POST list members */
		if($twitter_op == 'post') {
			$parameters = array('id' => 27831060);
			$method = "{$user->screen_name}/{$list->id}/members";
			return prepresponse($method, $connection->post($method, $parameters), $connection->http_code, $parameters);
		}

		/* GET list members */
		if($twitter_op == 'get') {
			$method = "{$user->screen_name}/{$list->id}/members";
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* GET list members id */
		if($twitter_op == 'get_id') {
			$method = "{$user->screen_name}/{$list->id}/members/27831060";
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* DELETE list members */
		if($twitter_op == 'delete') {
			$parameters = array('id' => 27831060);
			$method = "{$user->screen_name}/{$list->id}/members";
			return prepresponse($method, $connection->delete($method, $parameters), $connection->http_code, $parameters);
		}

		/* Delete the temp list */
		if($twitter_op == 'delete_temp') {
			$method = "{$user->screen_name}/lists/{$list->id}";
			$connection->delete($method);
		}
	}

	if($twitter_section == 'subscribers'){
		/**
		 * List Subscribers Methods.
		 */

		/* POST list subscribers */
		if($twitter_op == 'post') {
			$method = 'oauthlib/test-list/subscribers';
			return prepresponse($method, $connection->post($method), $connection->http_code);
		}

		/* GET list subscribers */
		if($twitter_op == 'subscribers') {
			$method = 'oauthlib/test-list/subscribers';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* GET list subscribers id */
		if($twitter_op == 'subscribers_id') {
			$method = 'oauthlib/test-list/subscribers/'.$user->id;
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* DELETE list subscribers */
		if($twitter_op == 'delete') {
			$method = 'oauthlib/test-list/subscribers';
			return prepresponse($method, $connection->delete($method), $connection->http_code);
		}
	}

	if($twitter_section == 'message'){
		/**
		 * Direct Message Methdos.
		 */

		/* direct_messages/new */
		if($twitter_op == 'new') {
			$parameters = array('user_id' => $user->id, 'text' => $twitter_content);
			$method = 'direct_messages/new';
			$dm = $connection->post($method, $parameters);
			return prepresponse($method, $dm, $connection->http_code, $parameters);
		}

		/* get direct_messages */
		if($twitter_op == 'get') {
			$method = 'direct_messages';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* direct_messages/sent */
		if($twitter_op == 'get_sent') {
			$method = 'direct_messages/sent';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* delete direct_message id */
		if($twitter_op == 'delete') {
			$method = 'direct_messages/destroy/'.$dm->id;
			return prepresponse($method, $connection->delete($method), $connection->http_code);
		}
	}

	if($twitter_section == 'friendship'){
		/**
		 * Friendships Methods.
		 */

		/* friendships/create */
		if($twitter_op == 'create') {
			$method = 'friendships/create/93915746';
			return prepresponse($method, $connection->post($method), $connection->http_code);
		}

		/* friendships/show */
		if($twitter_op == 'show') {
			$parameters = array('target_id' => 27831060);
			$method = 'friendships/show';
			return prepresponse($method, $connection->get($method, $parameters), $connection->http_code, $parameters);
		}

		/* friendships/destroy */
		if($twitter_op == 'delete') {
			$method = 'friendships/destroy/93915746';
			return prepresponse($method, $connection->post($method), $connection->http_code);
		}
	}

	if($twitter_section == 'social'){
		/**
		 * Social Graph Methods.
		 */

		/* friends/ids */
		if($twitter_op == 'get') {
			$method = 'friends/ids';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* friends/ids */
		if($twitter_op == 'get_id') {
			$method = 'friends/ids';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}
	}

	if($twitter_section == 'account'){
		/**
		 * Account Methods.
		 */

		/* account/verify_credentials */
		if($twitter_op == 'verify_cred') {
			$method = 'account/verify_credentials';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* account/rate_limit_status */
		if($twitter_op == 'rate_limit_status') {
			$method = 'account/rate_limit_status';
			return prepresponse($method, $connection->get($method), $connection->http_code);
		}

		/* account/update_profile_colors */
		if($twitter_op == 'update_profile_colors') {
			$parameters = array('profile_background_color' => $twitter_content);
			$method = 'account/update_profile_colors';
			return prepresponse($method, $connection->post($method, $parameters), $connection->http_code, $parameters);
		}

		/* account/update_profile */
		if($twitter_op == 'update_profile') {
			$parameters = array('location' => $twitter_content);
			$method = 'account/update_profile';
			return prepresponse($method, $connection->post($method, $parameters), $connection->http_code, $parameters);
		}
	}

	if($twitter_section == 'oauth'){
		/**
		 * OAuth Methods.
		 */

		/* oauth/request_token */
		if($twitter_op == 'request_token') {
			$oauth = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
			return prepresponse('oauth/request_token', $oauth->getRequestToken(), $oauth->http_code);
		}
	}
}

/**
 * Create a Bitly URL
 * @param <type> $url
 * @return <type>
 */
function get_bitly_url($url) {
	// short a url with bit
	if(BIT_USER != "" && BIT_API != ""){
		$api_call  = file_get_contents("http://api.bit.ly/shorten?version=2.0.1&longUrl=".$url."&login=".BIT_USER."&apiKey=".BIT_API);
		$bitlyinfo = json_decode(utf8_encode($api_call), true);
		if($bitlyinfo['errorCode'] == 0) {
			return $bitlyinfo['results'][urldecode($url)]['shortUrl'];
		}else{
			return $url;
		}
	}else{
		return $url;
	}
}

/**
 * Prepare an OAuth message
 * @param <type> $message
 * @param <type> $link
 * @return <type>
 */
function prepOAuthMessage($message, $link) {
	// load ShrinkURL code
	@include (SITE_PATH.INC_FOLDER."shrinkurl/shrinkurl.core.php");

	// if link is blank use the server root
	if($link == "") $link = $_SERVER['HTTP_HOST'];

	$msg_size = 140;				// max size for twitt msg
	//$link = get_bitly_url($link);	// make the link shorter if it's set
	if(defined("SURL_LOADED")) {
		$link = shrinkURL($link);
		$msg_size -= (strlen($link)+1);	// size that we have for the message after the link inserted +1 to leave an space between
		if(strlen($message) > $msg_size) $message = substr($message, 0, $msg_size); // crop the message then fits the url
		$message .= " ".$link;			// message with the link
	}else{
		if(strlen($message) > $msg_size) $message = substr($message, 0, $msg_size); // crop the message then fits the url
	}

	$message = stripslashes(trim(urldecode($message)));
	//$message = urlencode($message);

	return $message;
}

/**
 * Prepare the response
 * @param <type> $method
 * @param <type> $response
 * @param <type> $http_code
 * @param <type> $parameters
 * @return boolean
 */
function prepresponse($method, $response, $http_code, $parameters = ''){
	($http_code == '200') ? $status = true : $status = false;
	$ar = array("status" => $status, "method" => $method, "response" => $response, "http_code" => $http_code, "parameters" => $parameters);
	return $ar;
}
?>