<?php

//  ------------------------------------------------------------------------------------
//  EDIT PHOTO GALLERY
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."photos_cat";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$_page->sectionid = intval(getRequestVar('sectionid'));
	$cat_id = intval(getRequestVar('cat_id'));
    $name = getRequestVar('name');
	$code = codify($name);
	$description = getQuotedRequestVar('description');
	if(getRecItem($db->table, "id", "name = '$name' AND id != $cat_id AND sectionid = ".$_page->sectionid) != ""){
		addErrorStatMsg(sprintf(DUPLICATE_RECORD, "Gallery named `".strtoupper(getRequestVar('name'))."`", "", "name"));
	}

    if (!errorMsgExists()) {
		$sqlbase = "code = '$code', name = '$name', description = $description, cat_id = $cat_id, ";
    	switch($_page->savebuttonpressed) {
    		case "save":
                if(updateRec($db->table, $sqlbase."date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    		case "savedraft":
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
		$photoarray = array("table" => DB_TABLE_PREFIX."photos as p",
							"flds" => "*",
							"crit" => "published = 1 and archived = 0",
							"order" => "rank",
							"limit" => "");
		$gflds = array("id" => "id", "code" => "code", "title" => "name", "descr" => "description", "thumb" => "(SELECT thumb FROM data_photos WHERE gallery_def = 1 AND cat_id = pg.id) AS thumb");
		$pflds = array("file" => "image", "title" => "title", "caption" => "title");
		updateGalleryXML("images-new.xml", $gallarray, $photoarray, "cat_id=id", IMG_UPLOAD_FOLDER.DB_TABLE_PREFIX."photos/", THM_UPLOAD_FOLDER.DB_TABLE_PREFIX."photos/", $gflds, $pflds);
	}
}

// build query

$recset = getRec($db->table, "*", "id = '{$_page->row_id}'", "", "");
if(count($recset) == 1) {
    extractVariables($recset[0]);
}else{
    gotoPage("list-photos_cat.php");
	exit;
}

// build gallery queries
$gallrec = getRec(DB_TABLE_PREFIX."photos_cat", "*", "", "name", "");
array_unshift($gallrec, array("id" => "", "name" => "-- Select a Gallery --", "cat_id" => 0));

// build contained photo query
$photorec = getRec(DB_TABLE_PREFIX."photos", "*", "cat_id = '{$_page->row_id}'", "", "");

// build javascript block
$js = new JSBlock();
$js->subject = "gallery";
$js->section = "photos_cat";
$js->addCheckReqEntry('name', 'Please enter the gallery name.');
$js->buildCheckFormFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Galleries" => "photos/list-photos_cat.php"));
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit '$name' Gallery");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Gallery Name (limited to 50 characters)*", "name", $name, 60, 50);
showTextareaField("Description", "description", $description);
showLabel("Contained Photos (click photo to edit)");
showRowStart();
if($photorec) {
	$col = 0;
	foreach($photorec as $photo) {
		$photo_pic	= checkThumbPath($photo['thumb'], THM_UPLOAD_FOLDER.DB_TABLE_PREFIX."photos/", "");
    	list($width, $height) = constrainImage(SITE_PATH.$photo_pic, 0, THM_MAX_HEIGHT);
		echo "<div class=\"gallery_photos clearfix\">";
		echo "<a href=\"".WEB_URL.ADMIN_FOLDER."photos/edit-photos.php?row_id=".$photo['id']."\"><img src=\"".WEB_URL.$photo_pic."\" border=\"1\" height=\"".$height."\" title=\"".basename($photo_pic)."\"></a><br/>".$photo['title'];
		echo "</div>\n";
		$col++;
		if ($col & 3 == 2) {
			echo "<div class=\"clear\">&nbsp;</div>\n";
		}
	}
}else{
	echo "None... <a href=\"add-photos.php?cat_id={$_page->row_id}\">Add a photo to this gallery</a> now";
}
showRowEnd();
endPageForm();
showStats();
showFooter();
?>
