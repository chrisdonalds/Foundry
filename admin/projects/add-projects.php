<?php

//  ------------------------------------------------------------------------------------
//  CREATE CKEDITOR-BASED PAGE
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."projects";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
    $name = clean_text(getRequestVar('name', true));
	$code = codify($name);
	$description = getRequestVar('description', false, false);
    $cat_id = getRequestVar('cat_id');
    $lineage = getNextLineage($db->table, $cat_id);
    if(getRecNumRows($db->table, "id", "code = '$code'") > 0 && $code != '') {
		addErrorMsg("The code `".strtoupper($code)."` already exists.");
    }

	$sqlfields = "cat_id, name, code, description, lineage";
	$sqldata = "$cat_id, '$name', '$code', '$description', '$lineage'";
	switch($_page->savebuttonpressed) {
		case DEF_POST_ACTION_SAVE:
		case DEF_POST_ACTION_SAVEDRAFT:
			$_page->row_id = insertRec($db->table, $sqlfields, $sqldata);
			break;
		case DEF_POST_ACTION_SAVEPUB:
			$_page->row_id = insertRec($db->table, $sqlfields.", published, date_published", $sqldata.", 1, NOW()");
			break;
	}
	if($_page->row_id > 0){
		addErrorStatMsg(SUCCESS_CREATE);
	}else{
		addErrorStatMsg(FAILURE_CREATE);
	}

	if(getErrorStatMsg(SUCCESS_CREATE)) gotoEditPage();
}

// build query
$name = getRequestVar('name');
$description = getRequestVar('description');
$cat_id = getRequestVar('cat_id');
$cat_array = getCategories($db->table, $_page->row_id, true);

// build javascript block
$js = new JSBlock();
$js->subject = "project";
$js->section = "projects";
$js->addCheckReqEntry('name', 'Please enter the title.');
$js->buildCheckFormFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Projects List" => "projects/list-projects.php"));
showEditorButtons(DEF_EDITBUT_SAVE);
echo "</div>\n";

startEditorBlock("add_content");
showPageTitle("Create New Project");
showReqdText();

startPageForm("edit_form", "", "POST", true);
$atts = array("label" => "Title*", "id" => "name", "value" => $name, "fldclass" => "bigfldtext");
showObject("text", $atts);
$atts = array("label" => "Parent", "id" => "cat_id", "valuearray" => $cat_array, "selectedvalue" => $cat_id);
showObject("list", $atts);
$atts = array("label" => "Content*", "id" => "description", "value" => $description, "dim" => array(0, 600));
showObject("textarea", $atts);
endPageForm();
showFooter();
?>
