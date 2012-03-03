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
queueMacro('testmacro');
function testmacro($args){
    return "<div>".join(", ", $args)."</div>";
}
#-------------------------------------------------------------------------------
?>
	<div class="section">
		<h1 admin="page:<?=$_page->id?>"><? showTitle(); ?></h1>
		<? showContents(); ?>
	</div>
<?
showFooter();
?>