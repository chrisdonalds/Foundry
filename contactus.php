<?php
/*
Title:			Template Content/html Page
Author: 		Chris Satterthwaite, Chris Donalds
Updated: 		May.18.2010
Updated By: 	Chris Satterthwaite, Chris Donalds
 */

// Plugin inclusion value -- see inc/_plugins/plugins.htm for list of plugins and uses
$incl				= "validator(contact) form2email jquerymobile";
// UICore Plugin inclusion value -- eg. sortable,draggable,droppable,accordion,progressbar,dialog,datepicker
$uicore				= "";

include('inc/_core/loader.php');
startPage('header_1.php');
//$_page->show_properties();
#-------------------------------------------------------------------------------
?>
	<h1 admin="page:"><? showTitle(); ?></h1>
	<? showContents(); ?>

	<div class="info">
		<h3>Mailing Address</h3>
		<?=nl2br(BUS_ADDRESS);?>
		<h3>Contact Information</h3>
		<?=BUS_PHONE;?><br />
		<?//maskedEmail(OWNER_EMAIL, "at1.png");?>
	</div>

	<h2>Contact Form</h2>
	<?
	$_page->formTemplate = <<<EOT
		<div class="row">
			<div class="left">{LABEL}</div>
			<div class="mid">{REQFLAG}</div>
			<div class="right">{FIELD}</div>
		</div>
EOT;

startForm("contact-form", "", "post", "", "name|email", "contact-thankyou.php", "Contact Form Submission", "divbox", "contact-form");
showErrorBox();

addFormField("", "", "reqd");
addFormField("name", "name", "text", "Name", getIfSet($name), "", true);
addFormField("email", "email", "email", "Email", getIfSet($email), "", true);
addFormField("phone", "phone", "text", "Phone", getIfSet($phone), "", false);
addFormField("address", "address", "text", "Address", getIfSet($address), "", false);
addFormField("city", "city", "text", "City", getIfSet($city), "", false);
addFormField("comments", "comments", "textarea", "Message", getIfSet($comments), "", true);
addFormField("submit", "submit", "submit", "", "Submit", "button black", false);

endForm();
showFooter();
?>