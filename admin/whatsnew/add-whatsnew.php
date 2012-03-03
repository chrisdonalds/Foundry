<?php

//  ------------------------------------------------------------------------------------
//  CREATE NEW PAGE
//  ------------------------------------------------------------------------------------

$incl = "";
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
	$lastID = getLastID($db->table, "id");

	list($saveimg, $savethm) = FU_MoveTempFile($image_mod, $image, IMG_UPLOAD_FOLDER.$db->table, THM_UPLOAD_FOLDER.$db->table, true, false);

    if (!errorMsgExists()) {
		// sql fields and data lists. end both with ', '
		$sqlfields = "code, itemtitle, start_date, shortdescr, description, image, thumb";
		$sqldata   = "'$code', '$itemtitle', '$start_date', '$shortdescr', '$description', '$saveimg', '$savethm'";
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
		createTwitterSession("status", "update", $shortdescr, WEB_URL, "News Article", WEB_URL.ADMIN_FOLDER."whatsnew/list-whatsnew.php");
	}

	if (getErrorStatMsg(SUCCESS_CREATE)) gotoEditPage();
}

$itemtitle = getRequestVar('itemtitle');
$start_date = getRequestVar('start_date');
$description = getRequestVar('description');
$image = getRequestVar('image');
$thumb = getRequestVar('thumb');
if($start_date == "" || $start_date == "0000-00-00") $start_date = date("Y/m/d");
$start_date_m = date("m", strtotime($start_date));
$start_date_d = date("j", strtotime($start_date));
$start_date_y = date("Y", strtotime($start_date));

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
showEditorButtons(DEF_EDITBUT_PUB);
echo "</div>\n";

startEditorBlock("add_content");
showPageTitle("Create What's New Article");
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

showImgEditBox("Image", "image");
showFileUploaderScript(IMGEDITOR_TEMPFOLDER, 0, 100);
attachFileUploader(array('image'), array($image), array(IMAGE_TYPES));
showFooter();
?>
