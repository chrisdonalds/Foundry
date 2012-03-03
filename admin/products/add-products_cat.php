<?php

//  ------------------------------------------------------------------------------------
//  ADD CATEGORY
//  ------------------------------------------------------------------------------------

$incl = "imgedit fileuploader filehandler";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."products_cat";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$cat_id = intval(getRequestVar('cat_id'));
    $name = getQuotedRequestVar('name', true);
	$code = codify($name);
	$description = getQuotedRequestVar('description');
	$lastID = getLastID($db->table, "id");
	if(getRecItem($db->table, "id", "name = '$name' AND sectionid = ".$_page->sectionid) != ""){
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
		$sqlfields = "sectionid, code, name, description, cat_id, image, thumb";
		$sqldata   = $_page->sectionid.", '$code', $name, $description, $cat_id, '$saveimg', '$savethm'";
    	switch($_page->savebuttonpressed) {
    		case "save":
                if(insertRec($db->table, $sqlfields, $sqldata)){
                	addErrorStatMsg(SUCCESS_CREATE);
                }else{
                	addErrorStatMsg(FAILURE_CREATE);
                }
    			break;
    		case "savepub":
                if(insertRec($db->table, $sqlfields.", published, date_published", $sqldata.", 1, NOW()")){
                	addErrorStatMsg(SUCCESS_CREATE);
                }else{
                	addErrorStatMsg(FAILURE_CREATE);
                }
    			break;
    	}
	}

	if(getErrorStatMsg(SUCCESS_CREATE)) gotoEditPage();
}

// build query

$name = getRequestVar('name');
$cat_id = getRequestVar('cat_id');
$description = getRequestVar('description');

// build category list
if($cat_id > 0){
	$catrec = getRec(DB_TABLE_PREFIX."products_cat", "*", "", "name", "");
	array_unshift($catrec, array("id" => "", "name" => "-- Select a Category --", "cat_id" => 0));
}

// build javascript block
$js = new JSBlock();
$js->subject = "category";
$js->section = "products_cat";
$js->addCheckReqEntry('name', 'Please enter the category name.');
$js->buildCheckFormFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Categories" => "products/list-products_cat.php"));
showEditorButtons(DEF_EDITBUT_SAVE);
echo "</div>\n";

startEditorBlock("add_content");
showPageTitle("Create New Category");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Category Name (limited to 50 characters)*", "name", $name, 60, 50);
showTextareaField("Description", "description", $description, 75, 5);
showImageField("Image", array("image", "lastimg", "lastthm", "delimg"), array($image, $thumb), array(false, false), $db->table);
endPageForm();
showImgEditBox("Image", "image");
showFileUploaderScript(IMGEDITOR_TEMPFOLDER, 0, 100);
attachFileUploader(array('image'), array($image), array(IMAGE_TYPES));
showFooter();
?>
