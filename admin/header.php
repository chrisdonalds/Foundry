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
if ((getIfSet($_SESSION['admlogin']) != true) && !isset($rurl)) {
	gotoPage (WEB_URL.ADMIN_FOLDER."admlogin.php?rurl=".urlencode($_SERVER['REQUEST_URI']));
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?= SITE_NAME.' Admin: '.ucwords_smart($_page->menu['section']) ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="copyright" content="Copyright 2011 Navigator Multimedia"/>
<meta name="author" content="Navigator Multimedia Inc." />
<meta name="distribution" content="Global" />
<meta name="content-language" content="EN" />
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="Expires" content="-1"/>
<?php
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

        <?php include (SITE_PATH.ADMIN_FOLDER."menu.php"); ?>
        <div id="content-wrapper">

