<?php
/*
Title:			Template Content/html Page
Author: 		Chris Satterthwaite, Chris Donalds
Updated: 		May.18.2010
Updated By: 	Chris Satterthwaite, Chris Donalds
 */

$_page->metatitle = "Site Offline";
startPage();
?>
	<div class="section">
		<h1 admin="page:">The Site is Down for Maintenance</h1>
		<? echo SITEOFFLINE_MSG ?>
	</div>
<?
showFooter();
?>