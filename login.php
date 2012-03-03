<?php
/*
Title:			Template Content/html Page
Author: 		Chris Satterthwaite, Chris Donalds
Updated: 		May.18.2010
Updated By: 	Chris Satterthwaite, Chris Donalds
 */

// META tags -- Leave blank to use the values set in the Admin system
$metatitle			= "";
$metakeywords 		= "";
$metadescription 	= "";
// navon states (name of page, same as css classname for nav object)
$navname			= "";
// Plugin inclusion value -- eg. lightbox, form2email, googlemap, cdcal, login... (space separated list)
$incl				= "validator() login";
// UICore Plugin inclusion value -- eg. sortable,draggable,droppable,accordion,progressbar,dialog,datepicker
$uicore				= "";

startPage();
logout();
#-------------------------------------------------------------------------------
//  Handle login
if(cgi_get('login') != '') {
    $timein = $_REQUEST['time'];
    $timeout = time();
    if($_REQUEST['sec'] != "" || ($timeout - $timein < 4)) {
    	$error_content = "This page must be filled out by a person not automated script!";
	}else{
		$error_content = authenticate_login("data_users", "", "activated", "login-index.php", false);
	}
}
#-------------------------------------------------------------------------------
?>
	<h1 admin="page:"><? showTitle(); ?></h1>
	<p>If you are a member, please login below.</p>
	<?
$formTemplate = <<<EOT
		<div class="row">
			<div class="left">{LABEL}</div>
			<div class="mid">{REQFLAG}</div>
			<div class="right">{FIELD}</div>
		</div>
EOT;

startForm("login-form", "username|password", "", "");
showErrorBox();
addFormField("", "", "reqd", "", "", "", false);
addFormField("username", "username", "text", "Username", $username, "", true);
addFormField("password", "password", "password", "Password", "", "", true, "", "", "", 6, 30);
addFormField("login", "login", "submit", "", "Login", "button black", false);

endForm();

showFooter();
?>