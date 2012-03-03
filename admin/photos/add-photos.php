<?php

//  ------------------------------------------------------------------------------------
//  CREATE PHOTO
//  ------------------------------------------------------------------------------------

$incl = "imgedit fileuploader";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."photos";
$_page->cat_id = intval(getRequestVar('cat_id'));

startContentArea();

// process POST DATA

$phototitle = getRequestVar('phototitle');
$code = codify($phototitle);
$image = getRequestVar('image_fld');
$image_mod = getRequestVar('image_mod');

if(formDataIsReadyForSaving()){
	(getRequestVar('gallery_def') != "" && (getRecItem($db->table, "published", "id = '{$_page->row_id}'") == 1 || $_page->savebuttonpressed== "savepub")) ? $gallery_def = 1 : $gallery_def = 0;
	$lastID = getLastID($db->table, "id");

	list($saveimg, $savethm) = FU_MoveTempFile($image_mod, $image, IMG_UPLOAD_FOLDER.$db->table, THM_UPLOAD_FOLDER.$db->table, true);

	if (!errorMsgExists()) {
		if($gallery_def == 1) updateRec($db->table, "gallery_def=0", "cat_id=".$_page->cat_id);
		$sqlfields = "title, code, cat_id, gallery_def, image, thumb";
		$sqldata   = "'$phototitle', '$code', ".$_page->cat_id.", $gallery_def, '$saveimg', '$savethm'";
    	switch($_page->savebuttonpressed) {
    		case "save":
    			$_page->row_id = insertRec($db->table, $sqlfields, $sqldata, $phototitle);
                if($_page->row_id > 0){
                	if (updateRec($db->table, "rank = ".(getLastID($db->table, "rank", "cat_id = ".$_page->cat_id) + 1), "id = '{$_page->row_id}'")){
            			addErrorStatMsg(SUCCESS_CREATE);
					}else{
						addErrorStatMsg(FAILURE_CREATE);
					}
				}else{
					addErrorStatMsg(FAILURE_CREATE);
				}
    			break;
    		case "savepub":
    			$_page->row_id = insertRec($db->table, $sqlfields.", published, date_published", $sqldata.", 1, NOW()", $phototitle);
                if($_page->row_id > 0){
                	if (updateRec($db->table, "rank = ".(getLastID($db->table, "rank", "cat_id = ".$_page->cat_id) + 1), "id = '{$_page->row_id}'")){
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

    //update xml file
	if(getErrorStatMsg(SUCCESS_CREATE)) {
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
		gotoEditPage();
	}
}

$phototitle = getRequestVar('phototitle');
$gallery_def = getRequestVar('gallery_def');
$image = getRequestVar('image_fld');
$thumb = getRequestVar('thumb');

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
showEditorButtons(DEF_EDITBUT_PUB+DEF_EDITBUT_LASTINFO);
echo $_page->cat_id."</div>\n";

startEditorBlock("add_content");
showPageTitle("Create an Image");
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
		if($gallrec[$i]['id'] == $_page->cat_id) {
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
showInfo();
if(function_exists('showImgEditBox')) showImgEditBox("Image", "image");
showFileUploaderScript(FU_TEMPFOLDER, THM_MAX_WIDTH, THM_MAX_HEIGHT);
attachFileUploader(array('image'), array($image), array($thumb), array(IMAGE_TYPES));
showFooter();
?>
