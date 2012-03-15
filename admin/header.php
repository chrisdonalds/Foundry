<?php
// ---------------------------
//
// HEADER
//
// ---------------------------
if(!defined('VALID_LOAD')){
	// allows backward-compatibility with files that call header.php directly
	// the preferred method is:
    //    include('loader.php');
    //    getHeader();
	define("VALID_LOAD", true);
    define("VHOST", substr(str_replace("\\", "/", realpath(__DIR__."/../")), strlen($_SERVER['DOCUMENT_ROOT']))."/");

	// required - starts Foundry
	include ($_SERVER['DOCUMENT_ROOT'].VHOST."admin/inc/_core/getinc.php");
}

// handle admin system re-login and redirection
if ((!$_users->isloggedin) && !isset($rurl)) {
	gotoPage (WEB_URL.ADMIN_FOLDER."admlogin.php?rurl=".urlencode($_SERVER['REQUEST_URI']));
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?= SITE_NAME.' Admin: '.ucwords_smart($_page->title) ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="copyright" content="Copyright <?=date("Y")." ".COPYRIGHT_NAME?>"/>
<meta name="author" content="<?=COPYRIGHT_NAME?>" />
<meta name="distribution" content="Global" />
<meta name="content-language" content="EN" />
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="Expires" content="-1"/>
<?
prepHeaderPluginsBlock();
?>
</head>

<body>
    <div id="wrapper">
        <div id="header" style="background-color: <?= HEADER_BGCOLOR ?>">
            <h1>
            <?
            (BUSINESS == "businessname" || BUSINESS == "") ? print "Site Admin" : print SITE_NAME." Admin";
            ?>
            </h1>
        </div>

        <?
        include (SITE_PATH.ADMIN_FOLDER."menu.php");
        showHiddenField("base_url", WEB_URL.ADMIN_FOLDER);
        showHiddenField("admin_folder", WEB_URL.ADMIN_FOLDER.CORE_FOLDER);
        ?>
        <div id="content-wrapper">

