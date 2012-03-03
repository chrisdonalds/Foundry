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
prepHeaderPluginsBlock();
?>

<link rel="sitemap" type="application/xml" title="Sitemap" href="<? echo WEB_URL ?>sitemap.xml" />
</head>

<body>
    <div id="wrapper" class="clearfix">
        <div id="header">
            <div id="logo"><a href="" title=""><img src="<?= WEB_URL.IMG_UPLOAD_FOLDER; ?>interface/" width="203" height="153" alt="" /></a></div>
            <div id="header-picture"></div>
        </div>

        <ul id="nav">
            <li class="nav-home"><a href="<?= WEB_URL; ?>" title=""><span>Home</span></a></li>
            <li class="nav-about"><a href="<?= WEB_URL; ?>" title=""><span>About</span></a></li>
            <li class="nav-gallery"><a href="<?= WEB_URL; ?>gallery/" title=""><span>Gallery</span></a></li>
            <li class="nav-products"><a href="<?= WEB_URL; ?>" title=""><span>Products</span></a>
                <ul>
                    <li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 01</a>
                        <ul>
                            <li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 01</a></li>
                            <li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 02</a></li>
                            <li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 03</a></li>
                            <li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 04</a></li>
                            <li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 05</a></li>
                            <li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 06</a></li>
                            <li class="bottom-nav"></li>
                        </ul>
                    </li>
                    <li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 02</a></li>
                    <li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 03</a></li>
                    <li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 04</a></li>
                    <li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 05</a></li>
                    <li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 06</a></li>
                    <li class="bottom-nav"></li>
                </ul>
            </li>
            <li class="nav-news"><a href="<?= WEB_URL; ?>news.php" title=""><span>Whats New</span></a></li>
            <li class="nav-contact"><a href="<?= WEB_URL; ?>contactus.php" title=""><span>Contact Us</span></a></li>
        </ul><!-- /nav -->

        <div class="aside floatLeft">
        <? include(SITE_PATH."aside.php"); ?>
        </div><!-- /aside-->

        <div id="container" class="floatRight">