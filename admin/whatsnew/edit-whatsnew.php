<?php

//  ------------------------------------------------------------------------------------
//  EDIT
//  ------------------------------------------------------------------------------------

$incl = "imgedit fileuploader filehandler";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."whatsnew";

startContentArea();

// process POST DATA


if(formDataIsReadyForSaving()){
	$itemtitle = clean_text(getRequestVar('itemtitle'));
	$code = codify($itemtitle);
	$start_date = date("Y/m/d", strtotime(getRequestVar('start_date')));
	$description = str_replace("&#34;", "\"", getRequestVar('description'));
	$shortdescr = clean_text($description);
	$image = getRequestVar('image_fld');
	$image_mod = getRequestVar('image_mod');
	if($image != "" && $image_mod != ""){
		list($saveimg, $savethm) = FU_MoveTempFile($image_mod, $image, IMG_UPLOAD_FOLDER.$db->table, THM_UPLOAD_FOLDER.$db->table, true, false);
	}elseif($image_mod != 'deleted'){
		$saveimg = getRequestVar('lastimg');
		$savethm = getRequestVar('lastthm');
	}

	if (!errorMsgExists()) {
		// SQL fields to be updated.
		// end list with ', '
		$sqlbase = "code = '$code', itemtitle = '$itemtitle', start_date = '$start_date', shortdescr = '$shortdescr', description = '$description', image = '$saveimg', thumb = '$savethm', ";
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
    gotoPage("list-whatsnew.php");
	exit;
}

// build arrays
$years_array = array();
for($i = date("Y") - 6; $i <= date("Y") + 1; $i++) $years_array[$i] = $i;

// build javascript block
$js = new JSBlock();
$js->subject = "event";
$js->section = "whatsnew";
$js->addCheckReqEntry('itemtitle', 'Please provide a title for this event.');
$js->addCheckReqEntry('start_date', 'Please enter the event date.');
$js->addCheckReqCKEditorEntry('description', 'Please enter a short description.');
$js->buildCheckFormFunc();
$js->buildCheckDateFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("What's New Articles List" => "whatsnew/list-whatsnew.php"));
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit What's New Article");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Title*", "itemtitle", $itemtitle);
showLabel("Date*");
showRowStart();
showMenu("", "start_date_m", prepStandardMenu(MENU_SHORTMONTH), $start_date_m, 1, false, "onchange=\"updateDate('start_date');\"", "", "", "", "", FLD_DATA);
showMenu("", "start_date_d", prepStandardMenu(MENU_DAY), $start_date_d, 1, false, "onchange=\"updateDate('start_date');\"", "", "", "", "", FLD_DATA);
showMenu("", "start_date_y", prepStandardMenu(MENU_YEAR), $start_date_y, 1, false, "onchange=\"updateDate('start_date');\"", "", "", "", "", FLD_DATA);
showHiddenField("start_date", $start_date);
showRowEnd();
showHTMLEditorField("Description*", "description", $description);
showImageField("Image", array("image", "lastimg", "lastthm", "delimg"), array($image, $thumb), array(false, false), $db->table);
endPageForm();

showStats();
showImgEditBox("Image", "image");
showFileUploaderScript(IMGEDITOR_TEMPFOLDER, 0, 100);
attachFileUploader(array('image'), array($image), array(IMAGE_TYPES));

showFooter();
?>
