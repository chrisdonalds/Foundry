<?php

//  ------------------------------------------------------------------------------------
//  CREATE NEW PAGE
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
	$lastID = getLastID($db->table, "id");

	if (!errorMsgExists()) {
		// sql fields and data lists. end both with ', '
		$sqlfields = "code, itemtitle, start_date, content, url";
		$sqldata   = "'$code', '$itemtitle', '$start_date', '$content', '$url'";
    	switch($_page->savebuttonpressed) {
    		case "save":
    			$_page->row_id = insertRec($db->table, $sqlfields, $sqldata);
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
    		case "savedraft":
    			$_page->row_id = insertRec($db->table, $sqlfields, $sqldata);
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

	// Twitter post
	if (getErrorStatMsg(SUCCESS_CREATE) && strpos($incl, "twitter") !== false){
		//createTwitterSession("status", "update", $shortdescr, WEB_URL, "News Article", WEB_URL.ADMIN_FOLDER."whatsnew/list-whatsnew.php");
	}

	if (getErrorStatMsg(SUCCESS_CREATE)) gotoEditPage();
}

$itemtitle = getRequestVar('itemtitle');
$start_date = getRequestVar('start_date');
$content = getRequestVar('content');
$url = getRequestVar('url');
if($start_date == "" || $start_date == "0000-00-00") $start_date = date("Y/m/d");
$start_date_m = date("m", strtotime($start_date));
$start_date_d = date("j", strtotime($start_date));
$start_date_y = date("Y", strtotime($start_date));

// build arrays
$years_array = array();
for($i = date("Y") - 6; $i <= date("Y") + 1; $i++) $years_array[$i] = $i;

// build javascript block
$js = new JSBlock();
$js->subject = "newsletter";
$js->section = "newsletters";
$js->addCheckReqEntry('itemtitle', 'Please provide a title for this newsletter.');
$js->addCheckReqEntry('start_date', 'Please enter the newsletter date.');
$js->addCheckReqCKEditorEntry('content', 'Please provide the newsletter content.');
$js->buildCheckFormFunc();
$js->buildCheckDateFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Newsletters List" => "newsletters/list-newsletters.php"));
showEditorButtons(DEF_EDITBUT_PUB);
echo "</div>\n";

startEditorBlock("add_content");
showPageTitle("Create Newsletter");
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
showFooter();
?>
