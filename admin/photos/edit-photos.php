<?php

//  ------------------------------------------------------------------------------------
//  EDIT PHOTO
//  ------------------------------------------------------------------------------------

$incl = "imgedit fileuploader";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."photos";
//$_page->cat_id = intval(getRequestVar('cat_id'));
$_page->cat_id = getRecCatID($db->table, $_page->row_id);

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
    $phototitle = ucwords_smart(getRequestVar('phototitle', true));
    $code = codify($phototitle);
	(getRequestVar('gallery_def') != "" && (getRecItem($db->table, "published", "id = '{$_page->row_id}'") == 1 || $_page->savebuttonpressed== "savepub")) ? $gallery_def = 1 : $gallery_def = 0;

	$image = getRequestVar('image_fld');
	$image_mod = getRequestVar('image_mod');
	if($image != "" && $image_mod != ""){
		list($saveimg, $savethm) = FU_MoveTempFile($image_mod, $image, IMG_UPLOAD_FOLDER.$db->table, THM_UPLOAD_FOLDER.$db->table, true);
	}elseif($image_mod != 'deleted'){
		$saveimg = getRequestVar('lastimg');
		$savethm = getRequestVar('lastthm');
	}

	if($_page->cat_id != getRecItem($db->table, "cat_id", "id = '{$_page->row_id}'")) {
		updateRec($db->table, "rank = ".(getLastID($db->table, "rank", "cat_id = ".$_page->cat_id) + 1), "id = '{$_page->row_id}'");
	}
	if($gallery_def == 1) updateRec($db->table, "gallery_def=0", "cat_id=".$_page->cat_id);

	if(!errorMsgExists()) {
		$sqlbase = "title = '$phototitle', code = '$code', cat_id = ".$_page->cat_id.", gallery_def = $gallery_def, image = '$saveimg', thumb = '$savethm', ";
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
    		case "savearc":
                if(updateRec($db->table, $sqlbase."archived = 1, date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    	}
	}
	if(getErrorStatMsg(SUCCESS_EDIT) && function_exists('updateGalleryXML')) {
		//update xml file
		$gallarray = array(	"table" => DB_TABLE_PREFIX."photos_cat as pg",
							"flds" => "*",
							"crit" => "published = 1 and archived = 0",
							"order" => "rank",
							"limit" => "");
		$photoarray = array("table" => $db->table." as p",
							"flds" => "*",
							"crit" => "published = 1 and archived = 0",
							"order" => "rank",
							"limit" => "");
		$gflds = array("id" => "id", "code" => "code", "title" => "name", "descr" => "description", "thumb" => "(SELECT thumb FROM ".$db->table." WHERE gallery_def = 1 AND cat_id = pg.id) AS thumb");
		$pflds = array("file" => "image", "title" => "title", "caption" => "title");
		//updateGalleryXML("images-new.xml", $gallarray, $photoarray, "cat_id=id", IMG_UPLOAD_FOLDER.DB_TABLE_PREFIX."photos/", THM_UPLOAD_FOLDER.DB_TABLE_PREFIX."photos/", $gflds, $pflds);
	}
}

// build query

$recset = getRec($db->table, "*", "id = '{$_page->row_id}'", "", "");
if(count($recset) == 1) {
    extractVariables($recset[0]);
}else{
    gotoPage("list-photos.php?cat_id=".$_page->cat_id);
	exit;
}
$phototitle = $title;

// build gallery queries
$gallrec = getRec(DB_TABLE_PREFIX."photos_cat", "*", "", "name", "");
array_unshift($gallrec, array("id" => "", "name" => "-- Select a Gallery --", "cat_id" => 0));

// build javascript block
$js = new JSBlock();
$js->subject = "image";
$js->section = "photos";
$js->addCheckReqEntry('phototitle', 'Please enter the image title.');
$js->addCheckReqEntry('cat_id', 'Please select the gallery.');
$js->addCheckReqEntry('image_fld', 'Please provide the image file.');
$js->buildCheckFormFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Images" => "photos/list-photos.php?cat_id=".$_page->cat_id));
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit Image");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Image Title (limited to 255 characters)*", "phototitle", $phototitle, 60, 255);
showLabel("Gallery*");
echo "<tr><td><select name=\"cat_id\" id=\"cat_id\" size=\"1\" class=\"selectmenu\">\n";
if(is_array($gallrec)) {
	for($i = 0; $i < count($gallrec); $i++) {
		if ($gallrec[$i]['cat_id'] > 0) {
			$parentgall = getRecItem(DB_TABLE_PREFIX."photos_cat", "name", "id = ".$gallrec[$i]['cat_id']);
			$parentgall .= " > ";
		}else{
			$parentgall = "";
		}
		if($gallrec[$i]['id'] == $cat_id) {
			print "<option value=\"".$gallrec[$i]['id']."\" selected>".$parentgall.$gallrec[$i]['name']."</option>\n";
		}else{
			print "<option value=\"".$gallrec[$i]['id']."\">".$parentgall.$gallrec[$i]['name']."</option>\n";
		}
	}
}
echo "</select></td></tr>\n";
showImageField("Image (thumbnail created automatically)", array("image", "lastimg", "lastthm", "delimg"), array($image, $thumb), array(false, false), $db->table);
showCheckbox("Set as Gallery Image?", "gallery_def", "1", $gallery_def, " Yes, this image will be used as the gallery image (The image MUST be published)");
endPageForm();
showStats();
if(function_exists('showImgEditBox')) showImgEditBox("Image", "image");
showFileUploaderScript(FU_TEMPFOLDER, THM_MAX_WIDTH, THM_MAX_HEIGHT);
attachFileUploader(array('image'), array($image), array($thumb), array(IMAGE_TYPES));
showFooter();
?>
