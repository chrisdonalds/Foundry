<?php
/*
Title:			Generic/Dynamic Page
Author: 		Chris Satterthwaite, Chris Donalds
Updated: 		May.18.2010
Updated By: 	Chris Satterthwaite, Chris Donalds
 */

// Plugin inclusion value -- eg. lightbox, form2email, googlemap, cdcal, login... (space separated list)
$incl				= "";
// UICore Plugin inclusion value -- eg. sortable,draggable,droppable,accordion,progressbar,dialog,datepicker
$uicore				= "";

startPage();
//$_page->show_properties();
//$_data->show_properties();
$rec = getRec("data_photos", "*", "", "", "5");
$d = setupData($rec, true);
$d->show_properties();
#-------------------------------------------------------------------------------
?>
	<div class="section">
		<h1 admin="page:<?=$_page->id?>">Data for <? showTitle(false, true); ?></h1>
		<? showContents(); ?>
	</div>
<?
showFooter();
?>