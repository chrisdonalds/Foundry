<?php
//--------------------------- FORMTOEMAILPRO ----------------------------------
setcookie('formtoemailpro', 1);

function form2email_headerprep(){
	$err = addHeadLine(
		'style',				// script, style, rss, or favicon
		WEB_URL.ADMIN_FOLDER.PLUGINS_FOLDER.'mail/',					// path to the file
		'formtoemailpro.css',				// the name of the js, css, rss, ico file
		'screen'				// the output media (screen, all, print...)
	);
}

function processmail(){
	global $_system, $_page, $_data;
	
	//  Formtoemailpro divbox/popup post collection script called by calling file
	if(getRequestVar('submit') != '') {
		$timein = getRequestVar('time');
		$timeout = time();
		if(getRequestVar('sec') != "" || ($timeout - $timein < 4)) {
			$securityerror = "<p style=\"font-weight: bold; color: red\">This page must be filled out by a person not automated script!  Thank you.</p>";
		}else{
			foreach($_POST as $key => $value) $$key = $value;
			if($subject == "formsubject") {
				// stuff to do
				printr($_POST);
				die;
			}
			include(SITE_PATH.PLUGINS_FOLDER."mail/formtoemailpro.php");
		}
	}
}
?>
