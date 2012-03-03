<?php

//  ------------------------------------------------------------------------------------
//  EDIT
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."events";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$itemtitle = getRequestVar('itemtitle');
	$code = codify($title);
	$start_date = date("Y-m-d", strtotime(getRequestVar('start_date')));
	$start_time = date("H:i", strtotime(getRequestVar('start_time')));
	$description = str_replace("&#34;", "\"", getRequestVar('description'));
	$otheraddress = clean_text(getRequestVar('otheraddress'));
	$lastID = getLastID($db->table, "id");

	if (!errorMsgExists()) {
		// SQL fields to be updated.
		// end list with ', '
		$sqlbase = "code = '$code', itemtitle = '$itemtitle',
					start_date = '$start_date',
					description = '$description', otheraddress = '$otheraddress', ";
    	switch($_page->savebuttonpressed) {
    		case "save":
                if(updateRec($db->table, $sqlbase."date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    		case "savepub":
                if(updateRec($db->table, $sqlbase."published = 1, date_published = NOW(), date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    	}
	}
}

// build query

$recset = getRec($db->table, "*", "id = '{$_page->row_id}'", "", "");
if(count($recset) == 1) {
    extractVariables($recset[0]);
    tokenizeDateVar('start_date');
}else{
    gotoPage("list-events.php");
	exit;
}

// build javascript block
$js = new JSBlock();
$js->subject = "event";
$js->section = "events";
$js->addCheckReqEntry('itemtitle', 'Please enter the event title.');
$js->addCheckReqEntry('start_date', 'Please complete the event start date.');
$js->addCheckReqCKEditorEntry('description', 'Please provide a brief description.');
$js->buildCheckFormFunc();
$js->buildCheckDateFunc();
$js->buildJQueryCode("#start_date_y, #start_date_m, #start_date_d", "change", "updateDate('start_date');");
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Events" => "events/list-events.php"));
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit Event");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Title*", "itemtitle", $itemtitle);
showLabel("Event Date*");
showRowStart();
showMenu("", "start_date_m", prepStandardMenu(MENU_SHORTMONTH), $start_date_m, 1, false, "onchange=\"updateDate('start_date');\"", "", "", "", "", FLD_DATA);
showMenu("", "start_date_d", prepStandardMenu(MENU_DAY), $start_date_d, 1, false, "onchange=\"updateDate('start_date');\"", "", "", "", "", FLD_DATA);
showMenu("", "start_date_y", prepStandardMenu(MENU_YEAR), $start_date_y, 1, false, "onchange=\"updateDate('start_date');\"", "", "", "", "", FLD_DATA);
showHiddenField("start_date", $start_date);
showRowEnd();
showTextField("Location*", "otheraddress", $otheraddress);
showHTMLEditorField("Description*", "description", $description);
endPageForm();
showStats();
showFooter();
?>
