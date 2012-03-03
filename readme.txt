Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================

--------------------------- PAGE BUILDING -----------------------------------

To start a page:
================

Method 1: Automatic
-------------------

Just one call:

<? startPage({$headerfilepath}); ?>

This will output the <HEAD> block (which initiates the plugins, scripts, and styles) and
start the <BODY> tag.

You can control aspects of the HEAD by assigning values to the $_page properties, such as:
    $_page->metatitle
    $_page->metakeywords
    $_page->metadescr

    (More properties can be found by calling $_page->showProperties();)

Your header file is assumed to be called 'header.php' and located in the root folder.  If it
is something or somewhere else, provide the full path to it when calling startPage().

The header file can contain:
    - the full <HEAD> (see Method 2), opening <BODY> tag and initial HTML;
    - the full <HEAD>, and no <BODY> tag nor initial HTML;
    - no <HEAD>, but has opening <BODY> tag and initial HTML;
    - no <HEAD> or <BODY>, but has initial HTML, or;
    - is not in the filesystem, in which case a default head and body will be prepared

Method 2: Pseudo-Automatic
--------------------------

Include the HEAD code, as in the example below, in the header file (the <BODY... is optional
and up to you; startPage() will skip automatic starting of the body tag if it is already in
your header file):

<?php ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><? echo formatMetaTitle() ?></title>
<meta name="keywords" content="<? echo $_page->metakeywords ?>"/>
<meta name="description" content="<? echo $_page->metadescr ?>" />
<meta name="copyright" content="Copyright <? echo date("Y") ?> <? echo BUSINESS ?>. All Rights Reserved" />
<meta name="designer" content="Web design by <? echo COPYRIGHT_NAME ?> (<? echo COPYRIGHT_WEB ?>)" />
<meta name="author" content="<? echo COPYRIGHT_NAME ?>" />
<meta name="Robots" content="INDEX, FOLLOW" />
<meta name="Revisit-after" content="7 Days" />

<?
// REMEMBER TO CALL...
prepHeaderPluginsBlock();
?>

<link rel="sitemap" type="application/xml" title="Sitemap" href="<? echo WEB_URL ?>sitemap.xml" />
</head>

<body... ...>

And add this to the output php files:

<? startPage({$headerfilepath}); ?>

To Build a Simple Page:
=======================

Here is a sample of the shortest page among commercial platforms:

	<div class="section">
		<h1 admin="page:<?=$_page->id?>"><? showTitle(); ?></h1>
		<? showContent(); ?>
	</div>

That's it.

- The 'admin="page:id"' attribute, added to any HTML tag, turns that object into a link
  to that page in the Admin system.

To Build a Form:
================

	$page->formTemplate = <<<EOT
		<div class="row">
			<div class="left">{LABEL}</div>
			<div class="mid">{REQFLAG}</div>
			<div class="right">{FIELD}</div>
		</div>
EOT;

	$page->startForm("contact", "name|email", "contact-thankyou.php", "Contact Form Submission");
	$page->showErrorBox();
	$page->addFormField("", "", "reqd", "", "", "", false);
	$page->addFormField("name", "name", "text", "Name", $name, "", true);
	$page->addFormField("email", "name", "email", "Email", $email, "", true);
	$page->addFormField("phone", "phone", "text", "Phone", $phone, "", false);
	$page->addFormField("address", "address", "text", "Address", $address, "", false);
	$page->addFormField("city", "city", "text", "City", $city, "", false);
	$page->addFormField("comments", "comments", "textarea", "Message", $comments, "", true);
	$page->addFormField("radioopt", "radioopt", "checkbox", "Option", $radioopt, "", true, "1", null, "Yes");
	$page->addFormField("submit", "submit", "submit", "", "Submit", "button black", false);
	$page->endForm();

To Output Footer:
=================

<? showFooter() ?>

--------------------------- GENERAL CONSTANTS -------------------------------

- BUSINESS                      => Name of business
- SITE_NAME                     => Name of website
- OWNER_EMAIL                   => The client's email address
- BUS_ADDRESS                   => Mailing address
- BUS_PHONE                     => Phone
- BUS_FAX                       => Fax
- $GLOBALS['THEME']             => Name of current website theme

--------------------------- FOLDER CONSTANTS --------------------------------

 Use the following predesigned constants to help construct URLs

- SERVER                        => Domain name without www. or http://
- SITE_PATH                     => Document root used when including, eg. /vars/www/domain.com/httpdocs/
- WEB_URL                       => Web host root, eg. http://www.domain.com/
- WEB_URL.CSS_FOLDER            => CSS folder
- WEB_URL.JS_FOLDER             => Scripts (JS) folder
- WEB_URL.RSS_FOLDER            => RSS folder
- WEB_URL.IMG_UPLOAD_FOLDER     => Image upload folder
- WEB_URL.THM_UPLOAD_FOLDER     => Thumb upload folder
- WEB_URL.FILE_UPLOAD_FOLDER    => File upload folder
- WEB_URL.THEME_FOLDER          => Site theme CSS folder (theme.css)

--------------------------- PLUGINS -----------------------------------------

- Plugins are managed in the Admin.  Only plugins designated for the 'front' or 'both'
zones are applicable.

--------------------------- NAV ON STATES -----------------------------------

 $navon[] is a single item array representing the page the browser is on
		ie. if url is http://www.domain.com/folder/greatscott.php, the item is 'greatscott'

 to override the automatic naming of the item (eg. for index.php pages), include:
		$navname = '{pagename}'; line on specific page
        or if $navname is not provided, the page name (ie. greatscott) without extension will be used

 specify the 'on' state css code for each nav button
		.otherclass .selected for specific buttons, or
		.selected for all buttons

 add <?=$navon['{navname}']?> to each nav button in script:
		<li class="<?=$navon['greatscott']?>">text</li>, or
		<li class="otherclass<?=$navon['greatscott']?>">text</li>, or
		<a href="..." class="otherclass<?=$navon['greatscott']?>"><text</a>, etc.

