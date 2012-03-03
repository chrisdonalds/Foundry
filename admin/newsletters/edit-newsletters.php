<?php

//  ------------------------------------------------------------------------------------
//  EDIT
//  ------------------------------------------------------------------------------------

$incl = "filehandler";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."newsletters";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$itemtitle = clean_text(getRequestVar('itemtitle'));
	$code = codify($itemtitle);
	$start_date = date("Y/m/d", strtotime(getRequestVar('start_date')));
	$content = str_replace("&#34;", "\"", getRequestVar('content'));
	$url = str_replace("&#34;", "\"", getRequestVar('url'));

	if (!errorMsgExists()) {
		// SQL fields to be updated.
		// end list with ', '
		$sqlbase = "code = '$code', itemtitle = '$itemtitle', start_date = '$start_date', content = '$content', url = '$url', ";
    	switch($_page->savebuttonpressed) {
    		case "save":
                if(updateRec($db->table, $sqlbase."date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    		case "savedraft":
                if(updateRec($db->table, $sqlbase."draft = 1, date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    		case "savepub":
                if(updateRec($db->table, $sqlbase."draft = 0, published = 1, date_published = NOW(), date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    		case "savearc":
                if(updateRec($db->table, $sqlbase."archived = 1, date_updated = NOW()", "id = '{$_page->row_id}'")){
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
    gotoPage("list-newsletters.php");
	exit;
}

// build arrays
$years_array = array();
for($i = date("Y") - 6; $i <= date("Y") + 1; $i++) $years_array[$i] = $i;

// build javascript block
$js = new JSBlock();
$js->subject = "newsletter";
$js->section = "newsletters";
$js->addCheckReqEntry('itemtitle', 'Please provide a title for this newsletter.');
$js->addCheckReqEntry('start_date', 'Please enter the newsletter date.');
$js->addCheckReqEntry('url', 'Please enter the web address.');
$js->buildCheckFormFunc();
$js->buildCheckDateFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Newsletters List" => "newsletters/list-newsletters.php"));
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit Newsletter");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Title*", "itemtitle", $itemtitle);
showLabel("Date*");
showRowStart();
showMenu("", "start_date_m", prepStandardMenu(MENU_SHORTMONTH), $start_date_m, 1, false, "onchange=\"updateDate('start_date');\"", "", "", "", FLD_DATA);
showMenu("", "start_date_d", prepStandardMenu(MENU_DAY), $start_date_d, 1, false, "onchange=\"updateDate('start_date');\"", "", "", "", FLD_DATA);
showMenu("", "start_date_y", $years_array, $start_date_y, 1, false, "onchange=\"updateDate('start_date');\"", "", "", "", FLD_DATA);
showHiddenField("start_date", $start_date);
showRowEnd();
//showTextareaField("Short url (Seen on list page. Limited to 200 characters)", "shortdescr", $shortdescr, 75, 5, 200);
showHTMLEditorField("Content*", "content", $content, 20, 15, array(0, 400));
showTextareaField("Web Address", "url", $url, 75, 3);
endPageForm();
showStats();
showFooter();
?>
