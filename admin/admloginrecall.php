<?php
$incl = "cipher";
include("loader.php");

$admloginquery = getRequestVar('admloginquery');
if($admloginquery == '') $admloginquery = $_SERVER['QUERY_STRING'];

$admerr = "";
$remoteip = md5($_SERVER['REMOTE_ADDR']);
$ready_to_reset_pwd = false;
if(getRequestVar('ready_to_reset_pwd') != '') $ready_to_reset_pwd = true;
$k = getRequestVar('k');

if (getRequestVar('admrequest') != ""){
    $useremail = $_POST['admuser'];
    if($useremail != ""){
        $acct = getRec("admin_accts", "*", "username = '$useremail' OR email = '$useremail'", "", "1");
        if (count($acct) > 0){
            if($acct[0]['activated'] == 1){
                if($acct[0]['email'] != ''){
                    $pkey = $acct[0]['pcle'];
                    $link = WEB_URL.ADMIN_FOLDER."admloginrecall.php?k=".$pkey;
                    $header = "From: ".ADMIN_EMAIL."\n";
                    $header.= "Reply-to: noreply@".$_SERVER['HTTP_HOST']."\n";
                    $msg = "Hello,\n\n";
                    $msg.= "We were pleased to help you reset your ".SITE_NAME." Admin account password. Follow the instructions below to set a new password.\n";
                    $msg.= "If you did not initiate this reset request, please disregard this email.\n\n";
                    $msg.= "Click the following link to set a new password:\n\n";
                    $msg.= $link."\n\n";
                    $msg.= "If clicking the link doesn't work you can copy it into your browser's address bar or type it there directly.\n\n";
                    $msg.= "Regards,\n\n";
                    $msg.= SITE_NAME." Admin System";
                    mail($acct[0]['email'], SITE_NAME." Admin Account - Password Reset", $msg, $header);
                    $admerr = "The password reset email has been sent.  Check your email in a moment.";
                }else{
                    $admerr = "Your account was found, but an email address was not recorded.";
                }
            }else{
                $admerr = "Your account has not been activated.  Please contact the system administrator.";
            }
        }else{
            $admerr = "Your username/email did not match account records!";
        }
    }else{
        $admerr = "Please provide a username or email address!";
    }
} elseif ($k != '') {
    $acct = getRec("admin_accts", "*", "pcle = '$k'", "", "1");
    if(count($acct) > 0){
        $ready_to_reset_pwd = true;
        if(getRequestVar('admpwdreset') != ''){
            // save new password
            $admerr = "";
            $user = getRequestVar('admuser');
            $pwd  = getRequestVar('admpwd');
            $pwdc = getRequestVar('admpwdconfirm');

            if($user == '' || $pwd == '' || $pwdc == '') {
                $admerr = "The username and both passwords are required.";
            } elseif($user != $acct[0]['username']) {
                $admerr = "The username is not the username for this account.";
            } elseif($pwd != $pwdc) {
                $admerr = "The passwords were not the same.";
            } else {
                $pcle = genPrivateKey();
				$phash = encrypt($pwd, $pcle);
				$pwd = md5($pwd);
                if(updateRec("admin_accts", "pcle = '$pcle', phash = '$phash', password = '$pwd'", "id = ".$acct[0]['id'])){
                    // log user into system
					$_SESSION['admlogin'] = true;
                    $_SESSION['admuserid'] = $acct[0]['id'];
                    $_SESSION['admuserlevel'] = $acct[0]['level'];
					setcookie('admlogin', date("Y-m-d H:i:s"), time()+3600*24*(2), '/');
					insertRec("session_login", "user_id, ip_hash, username, section, logged_in, logged_in_date", "'".$acct[0]['id']."', '$remoteip', '$user', 'admin', 1, '".date("Y-m-d H:i:s")."'");
					gotoPage (WEB_URL.ADMIN_FOLDER);	// return to home page
                }
            }
        }
    }else{
        $admerr = "The password reset link you clicked on or typed in is invalid.  Please try again.";
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?= BUSINESS?> Admin: Forgot Password</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="author" content="Navigator Multimedia Inc." />
<meta name="distribution" content="Global" />
<meta name="content-language" content="EN" />
<?php
addHeadPlugin("jquery");
addHeadPlugin("jqueryui", array("widgets" => "dialog"));
addHeadPlugin("basic");
showHeadlines(true);
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
				<h2>Create a New Login Password</h2>
                <? if(!$ready_to_reset_pwd){ ?>
				<p>Enter your username or email address so that a password renewal email can be sent with instructions on re-establishing your password.</p>
				<div id="admerror"></div>
				<form method="post" action="<?=$_SERVER['PHP_SELF']?>">
                    <input type="hidden" name="admloginquery" value="<?=$admloginquery;?>"/>
					<p><label for="admuser">Username or Email:</label><br />
                    <input type="text" name="admuser" id="admuser" size="15" value="" /></p>
					<div style="text-align: center; margin-top: 15px; clear: both;">
						<input type="submit" name="admrequest" id="admrequest" value="Send Instructions"/>
					</div>
				</form>
                <? } else { ?>
				<p>You're almost finished. Please enter your username and a new password below.</p>
				<div id="admerror"></div>
				<form method="post" action="<?=$_SERVER['PHP_SELF']?>">
                    <input type="hidden" name="ready_to_reset_pwd" value="<?=$ready_to_reset_pwd;?>"/>
                    <input type="hidden" name="k" value="<?=$k;?>"/>
					<p><label for="admuser">Username:</label><br />
                    <input type="text" name="admuser" id="admuser" size="15" value="" /></p>
					<p><label for="admpwd">Password:</label><br />
                    <input type="password" name="admpwd" id="admpwd" size="15" value="" autocomplete="off" /></p>
					<p><label for="admpwdconfirm">Password Confirmation:</label><br />
                    <input type="password" name="admpwdconfirm" id="admpwdconfirm" size="15" value="" autocomplete="off" /></p>
					<div style="text-align: center; margin-top: 15px; clear: both;">
						<input type="submit" name="admpwdreset" id="admpwdreset" value="Reset Password and Log Me In"/>
					</div>
				</form>
                <? } ?>
                <div id="admloginbox_login"><a href="admlogin.php">Try Logging In</a></div>
			</div>
			<div id="back2public">
                <a href="<?=WEB_URL?>">
                    <img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN?>images/icons/globe.png" alt="Go to website" title="Go to website" />
                    << Return to <?= SITE_NAME?> Home Page
                </a>
            </div>

<?
if (getIfSet($_SESSION['admlogin']) != true || $admerr != ""){
?>
            <script type="text/javascript" language="javascript">
	            jQuery('#admuser').focus();
	            jQuery('#admerror').html('<span style="color: red; font-weight: bolder;"><?=$admerr?></span>');
            </script>
<? }

showFooter();?>