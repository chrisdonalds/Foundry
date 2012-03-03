<?php
/*
LOGIN PLUG-IN
Log-in processor
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================
*/

session_start();

define("LOGIN_TABLE", "session_login");
define("LOGIN_NO", false);
define("LOGIN_YES", true);
define("LOGIN_ON_OTHER_PC", 2);

function authenticate_login($table, $section, $activefld, $successpage, $checkotherpc = true, $timeout = 3600){
	global $_page;

	$username = cgi_get('username', true);
	$password = cgi_get('password');
	if($username != "" && $password != ""){
		$username = strtolower($username);
		$password = md5($password);
		$recset = getRec($table, "*", "username = '$username' AND password = '$password'", "", "1");
		if(count($recset) > 0){
			if(is_loggedin($username) != LOGIN_ON_OTHER_PC || !$checkotherpc){
				if($activefld != ""){
					// check activation status
					if($recset[0][$activefld] == 1){
						if(login($username, $section)){
							$_page->redirect($successpage);
						}
					}else{
						$error_content = "Your account has not been activated.";
					}
				}else{
					login($recset[0]['id'], $section);
				}
			}else{
				logout();
				$error_content = "Someone else has already logged in using your username.  Please try again.";
			}
		}else{
			$error_content = "Either your username and/or password does not match our records.";
		}
	}else{
		$error_content = "Please enter both your username and password to login.";
	}
	if ($error_content != "") $error_content = "<h2>There was an error while trying to login</h2>\n".$error_content;
	return $error_content;
}

function login($username, $section){
	// new login
	$_SESSION["username"] = $username;
	insertRec(LOGIN_TABLE, "logged_in, ip_hash, username, section", "1, '".get_iphash()."', '{$username}', '{$section}'");
	return true;
}

function logout($section = "public"){
	// kill login session
	deleteRec(LOGIN_TABLE, "ip_hash='".get_iphash()."' AND section = '$section'");
	unset($_SESSION);
	$_SESSION = array();
	session_unset();
	return false;
}

function is_loggedin($section = "public"){
	//if($username == "") $username = $_SESSION['username'];
	$prev_iphash = get_db_login_acct($section);
	if($prev_iphash != null){
		// login found
		return LOGIN_YES;
	}else{
		// no previous login
		return LOGIN_NO;
	}
}

function get_iphash(){
	return md5($_SERVER['REMOTE_ADDR']);
}

function get_db_login_username($section = "public"){
	return getRecItem(LOGIN_TABLE, "username", "ip_hash='".get_iphash()."' AND logged_in=1 AND section = '$section'");
}

function get_db_login_acct($section = "public"){
	return getRecItem(LOGIN_TABLE, "id", "ip_hash='".get_iphash()."' AND logged_in=1 AND section = '$section'");
}

function get_loginid(){
	return $_SESSION["site_loginid"];
}
?>
