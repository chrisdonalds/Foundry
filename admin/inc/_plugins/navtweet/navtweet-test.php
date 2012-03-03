<?php
/* ---------------------------------------- */
// Change these parameters with your Twitter 
// user name and Twitter password.
/* ---------------------------------------- */

/* Don't change the code below
/* ---------------------------------------- */
if(isset($_POST['twitter_msg'])){
	$twitter_message = $_POST['twitter_msg'];
	if(strlen($twitter_message) < 1){
		$error = 1;
	} else {
		include ("./navtweet.core.php");
		createTwitterSession("status", "update", $twitter_message, WEB_URL, "test article", WEB_URL.ADMIN_FOLDER.PLUGINS_FOLDER."navtweet/navtweet-test.php");
	}
}
/* ---------------------------------------- */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Send a message to Twitter using PHP/oAuth</title>
<style type="text/css">
	body{font-family:'Lucida Grande', Verdana, sans-serif;; font-size:14px; color:#666666;}
	h2{color:#000000;}
	h3{color:#000000; font-size:14px;}
	p{font-size:12px; color:#333333;}
	input{font-size:18px; color:#444444;}
	a:link, a:visited, a:hover{color:#0033CC;}
	a:hover{text-decoration:none;}
	div.footer{padding:6px; border-top:solid 1px #DEDEDE; font-size:10px;}
	div.msg{background:#FFFFCC; margin-bottom:10px; padding:4px;}
	div.code{padding:10px; background:#FFFFCC; font-size:11px; color:#000000; margin-bottom:20px; width:300px; border:solid 1px #CCCCCC;}
</style>
</head>

<body>
<h2>Post a message on Twitter</h2>
<p>This page uses Twitter API to send an message with postToTwitter() function.</p>
<!-- This is the form that you can reuse in your site -->
<?php if(isset($_POST['twitter_msg']) && !isset($error)){?>
<div class="msg"><?php echo $twitter_status ?></div>
<?php } else if(isset($error)){?>
<div class="msg">Error: please insert a message!</div>
<?php } ?>

<p><strong>What are you doing now?</strong></p>
<form action="navtweet-test.php" method="post">
	<input name="twitter_msg" type="text" id="twitter_msg" size="40" maxlength="140" />
	<input type="submit" name="button" id="button" value="post" />
</form>
<!-- END -->

</body>
</html>
