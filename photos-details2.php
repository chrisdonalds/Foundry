<?php
/*
Title:			Generic/Dynamic Page
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
$incl				= "";
// UICore Plugin inclusion value -- eg. sortable,draggable,droppable,accordion,progressbar,dialog,datepicker
$uicore				= "";

startPage();
#-------------------------------------------------------------------------------
?>
	<div class="section">
		<h1 admin="page:<?=$_page->id?>"><? showTitle(); ?></h1>
		<? showContents(); ?>
	</div>
<?
showFooter();
?>