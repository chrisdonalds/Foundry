<?php

//  ------------------------------------------------------------------------------------
//  CREATE
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
		// sql fields and data lists. end both with ', '
		$sqlfields = "sectionid, code, itemtitle, start_date, start_time, description, otheraddress";
		$sqldata   = $_page->sectionid.", '$code', '$itemtitle', '$start_date', '$start_time', '$description', '$otheraddress'";
    	switch($_page->savebuttonpressed) {
    		case "save":
    			$_page->row_id = insertRec($db->table, $sqlfields."", $sqldata."");
                if($_page->row_id > 0){
    				if(updateRec($db->table, "rank = ".(getLastID($db->table, "rank", "") + 1), "id = '{$_page->row_id}'")){
                		addErrorStatMsg(SUCCESS_CREATE);
    				}else{
    					addErrorStatMsg(FAILURE_CREATE);
    				}
                }else{
                	addErrorStatMsg(FAILURE_CREATE);
                }
    			break;
    		case "savepub":
    			$_page->row_id = insertRec($db->table, $sqlfields.", published, date_published", $sqldata.", 1, NOW()");
                if($_page->row_id > 0){
    				if(updateRec($db->table, "rank = ".(getLastID($db->table, "rank", "") + 1), "id = '{$_page->row_id}'")){
                		addErrorStatMsg(SUCCESS_CREATE);
    				}else{
    					addErrorStatMsg(FAILURE_CREATE);
    				}
                }else{
                	addErrorStatMsg(FAILURE_CREATE);
                }
    			break;
    	}
	}
	if(getErrorStatMsg(SUCCESS_CREATE)) gotoEditPage();
}

$itemtitle = getRequestVar('itemtitle');
$start_time = getRequestVar('start_time');
$end_time = getRequestVar('end_time');
$description = getRequestVar('description');
$start_date = getRequestVar('start_date');
if($start_date == "" || $start_date == "0000-00-00") $start_date = date("Y-m-d");
$start_date_m = date("m", strtotime($start_date));
$start_date_d = date("j", strtotime($start_date));
$start_date_y = date("Y", strtotime($start_date));
$otheraddress = getRequestVar('otheraddress');

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
showEditorButtons(DEF_EDITBUT_PUB);
echo "</div>\n";

startEditorBlock("add_content");
showPageTitle("Create a New Event");
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
showFooter();
?>
