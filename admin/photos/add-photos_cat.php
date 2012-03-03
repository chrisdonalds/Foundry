<?php

//  ------------------------------------------------------------------------------------
//  ADD PHOTO GALLERY
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."photos_cat";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$cat_id = intval(getRequestVar('cat_id'));
    $name = getQuotedRequestVar('name', true);
	$code = codify($name);
	$description = getQuotedRequestVar('description');
	$lastID = getLastID($db->table, "id");
	if(getRecItem($db->table, "id", "name = '$name' AND sectionid = ".$_page->sectionid) != ""){
		addErrorStatMsg(sprintf(DUPLICATE_RECORD, "Gallery named `".strtoupper(getRequestVar('name'))."`", "", "name"));
	}

	if (!errorMsgExists()) {
		$sqlfields = "sectionid, code, name, description, cat_id";
		$sqldata   = $_page->sectionid.", '$code', $name, $description, $cat_id";
    	switch($_page->savebuttonpressed) {
    		case "save":
                $_page->row_id = insertRec($db->table, $sqlfields, $sqldata);
                if($_page->row_id > 0){
                    if (updateRec($db->table, "rank = ".(getLastID($db->table, "rank", "") + 1), "id = '{$_page->row_id}'")){
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
                    if (updateRec($db->table, "rank = ".(getLastID($db->table, "rank", "") + 1), "id = '{$_page->row_id}'")){
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
	if(getErrorStatMsg(SUCCESS_CREATE)) {
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
		gotoEditPage();
	}
}

// build query

$name = getRequestVar('name');
$cat_id = getRequestVar('cat_id');
$description = getRequestVar('description');

// build gallery list
$gallrec = getRec(DB_TABLE_PREFIX."photos_cat", "*", "", "name", "");
array_concat($gallrec, array("id" => "", "name" => "-- Select a Gallery --", "cat_id" => 0));

// build javascript block
$js = new JSBlock();
$js->subject = "gallery";
$js->section = "photos_cat";
$js->addCheckReqEntry('name', 'Please enter the gallery name.');
$js->buildCheckFormFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Galleries" => "photos/list-photos_cat.php"));
showEditorButtons(DEF_EDITBUT_SAVE);
echo "</div>\n";

startEditorBlock("add_content");
showPageTitle("Create New Gallery");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Gallery Name (limited to 50 characters)*", "name", $name, 60, 50);
showTextareaField("Description", "description", $description, 75, 5);
endPageForm();
showFooter();
?>
