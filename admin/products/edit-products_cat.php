<?php

//  ------------------------------------------------------------------------------------
//  EDIT
//  ------------------------------------------------------------------------------------

$incl = "imgedit fileuploader filehandler";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."products_cat";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$_page->sectionid = intval(getRequestVar('sectionid'));
	$cat_id = intval(getRequestVar('cat_id'));
    $name = getQuotedRequestVar('name', true);
	$code = codify($name);
	$description = getQuotedRequestVar('description');
	if(getRecItem($db->table, "id", "name = '$name' AND id != $cat_id AND sectionid = ".$_page->sectionid) != ""){
		addErrorStatMsg(sprintf(DUPLICATE_RECORD, "Category named `".strtoupper(getRequestVar('name'))."`", "", "name"));
	}else{
		$image = getRequestVar('image_fld');
		$image_mod = getRequestVar('image_mod');
		if($image != "" && $image_mod != ""){
			list($saveimg, $savethm) = FU_MoveTempFile($image_mod, $image, IMG_UPLOAD_FOLDER.$db->table, THM_UPLOAD_FOLDER.$db->table, true, false);
		}elseif($image_mod != 'deleted'){
			$saveimg = getRequestVar('lastimg');
			$savethm = getRequestVar('lastthm');
		}
	}

	if (!errorMsgExists()) {
		$sqlbase = "code = '$code', name = $name, description = $description, cat_id = $cat_id, image = '$saveimg', thumb = '$savethm'";
    	switch($_page->savebuttonpressed) {
    		case "save":
                if(updateRec($db->table, $sqlbase, "id = '{$_page->row_id}'")){
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
}else{
    gotoPage("list-products_cat.php");
	exit;
}

// build javascript block
$js = new JSBlock();
$js->subject = "category";
$js->section = "products";
$js->addCheckReqEntry('name', 'Please enter the category name.');
$js->buildCheckFormFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Categories" => "products/list-products_cat.php"));
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit '$name' Category");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Category Name (limited to 50 characters)*", "name", $name, 60, 50);
showTextareaField("Description", "description", $description, 75, 5);
showImageField("Image", array("image", "lastimg", "lastthm", "delimg"), array($image, $thumb), array(false, false), $db->table);
endPageForm();
showStats();
showImgEditBox("Image", "image");
showFileUploaderScript(IMGEDITOR_TEMPFOLDER, 0, 100);
attachFileUploader(array('image'), array($image), array(IMAGE_TYPES));
showFooter();
?>
