<?php
$rurl = urldecode((isset($_REQUEST['rurl']) ? $_REQUEST['rurl'] : ''));
include("loader.php");

// admin login
$admerr = "";
if(VHOST != "/") $rurl = str_replace(VHOST, "", $rurl);
if($rurl == "") $rurl = "/".ADMIN_FOLDER;
$remoteip = md5($_SERVER['REMOTE_ADDR']);
if (getRequestVar('admsubmit') == "Enter"){
    $pwd = getRequestVar('admpwd');
    $user = getRequestVar('admuser');
    unset($_SESSION['admlogin']);
    if($pwd != "" && $user != ""){
        $pwd = md5($pwd);
        $acct = getRec("admin_accts", "*", "username = '$user' AND password = '$pwd'", "", "1", "", true);
        if(count($acct) > 0){
            if($acct['activated'] == 1){
                // log user into system
                $_SESSION['admlogin'] = true;
                $_SESSION['admuserid'] = $acct['id'];
                $_SESSION['admuserlevel'] = $acct['level'];
                setcookie('admlogin', date("Y-m-d H:i:s"), time()+3600*24*2, '/');

                // set user persistence data
                $_SESSION['timestamp'] = time();
                $_SESSION['userdata'] = $acct;
                insertRec("session_login", "user_id, ip_hash, username, section, logged_in, logged_in_date", "'".$acct['id']."', '$remoteip', '$user', 'admin', 1, '".date("Y-m-d H:i:s")."'");
                if(getRequestVar('admremember') == 1){
                    setcookie("admuser", getRequestVar('admuser'), time() + 60*60*24*365);
                    setcookie("admpwd", getRequestVar('admpwd'), time() + 60*60*24*365);
                    setcookie("admremember", getRequestVar('admremember'), time() + 60*60*24*365);
                }else{
                    setcookie("admuser", "", -1);
                    setcookie("admpwd", "", -1);
                    setcookie("admremember", "", -1);
                }

                // rebuild admin url that got us here
                $rurl = ltrim($rurl, "/");
                $row_id = intval(getRequestVar('row_id'));
                $cat_id = intval(getRequestVar('cat_id'));
                if($row_id > 0) $rurl .= '?row_id'.$row_id;

                // return to admin page
                gotoPage (WEB_URL.$rurl);	// return to calling page
            }else{
                $admerr = "Your account has not been activated.  Please contact the system administrator.";
            }
        }else{
            $admerr = "The password you entered for $user is not correct!";
        }
    }else{
        $admerr = "Please provide a valid username and password!";
    }
}elseif (getRequestVar('admsubmit') == "Logout"){
    // log user out of system
	unset($_SESSION['admlogin']);
    $_users->isloggedin = false;
	setcookie('admlogin', '', time()+3600*24*(-100), '/');
	deleteRec("session_login", "ip_hash = '$remoteip' AND section = 'admin'");
	$admerr = "You are now logged out.";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?= BUSINESS?> Admin: Login</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="author" content="Navigator Multimedia Inc." />
<meta name="distribution" content="Global" />
<meta name="content-language" content="EN" />
<?php
addHeadPlugin("jquery");
addHeadPlugin("jqueryui", array("widgets" => "dialog"));
addHeadPlugin("basic");
showHeadLines(true);
$rm_admuser = getIfSet($_COOKIE["admuser"]);
$rm_admpwd = getIfSet($_COOKIE["admpwd"]);
$rm_check = ((isset($_COOKIE["admremember"])) ? ' checked="checked"' : '');
?>
</head>

<body class="login">
	<div id="wrapper">
		<div id="display_core_msg"></div>
		<div id="display_runtime_msg"></div>
		<div id="content-wrapper" class="login-wrap">
			<div id="admloginbox" class="clearfix">
            	<? if(IMG_LOGIN_LOGO != ''){?>
				<img class="logo" src="<?=WEB_URL.ADMIN_FOLDER.IMG_UPLOAD_FOLDER?>logo/<?=IMG_LOGIN_LOGO?>" alt="logo" />
				<? } ?>
				<h2>Welcome to the <?= SITE_NAME ?> Admin System</h2>
				<p style="display: none;">Please enter your username and password to log-in...</p>
				<div id="admerror"></div>
				<form method="post" action="admlogin.php">
                    <input type="hidden" name="rurl" value="<?=$rurl;?>"/>
					<p><label for="admuser">Username:</label><br />
                    <input type="text" name="admuser" id="admuser" size="15" value="<?=$rm_admuser?>" /></p>
					<p><label for="admpwd">Password:</label><br />
                    <input type="password" name="admpwd" id="admpwd" size="15" value="<?=$rm_admpwd?>" autocomplete="off" /></p>
					<div style="margin: 15px 0px; clear: both;">
                        <input type="checkbox" name="admremember" id="admremember" value="1"<?=$rm_check?> /> Remember my Login
						<input type="submit" name="admsubmit" id="admsubmit" value="Enter"/>
					</div>
				</form>
                <div id="admloginbox_recall"><a href="admloginrecall.php?<?=$_SERVER['QUERY_STRING']?>">I forgot my password.  Please help.</a></div>
			</div>
			<div id="back2public">
                <a href="<?=WEB_URL?>">
                    <img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN?>images/icons/globe.png" alt="Go to website" title="Go to website" />
                    << Return to <?= SITE_NAME?> Home Page
                </a>
            </div>

<?
if (getIfSet($_SESSION['admlogin']) != true || $admerr != "" || isset($rurl)){
?>
            <script type="text/javascript" language="javascript">
                jQuery('#admuser').focus();
                jQuery('#admerror').html('<span style="color: red; font-weight: bolder;"><?=$admerr?></span>');
            </script>
<? }

showFooter();?>

