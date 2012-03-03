<?php

//  ------------------------------------------------------------------------------------
//  ADD CATEGORY
//  ------------------------------------------------------------------------------------

$incl = "filehandler";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."products_types";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
    $name = getQuotedRequestVar('name', true);
	$code = codify($name);
	$lastID = getLastID($db->table, "id");
	if(getRecItem($db->table, "id", "name = '$name' AND sectionid = ".$_page->sectionid) != ""){
		addErrorMsg(sprintf(DUPLICATE_RECORD, "Type named `".strtoupper(getRequestVar('name'))."`", "", "name"));
	}

	if (!errorMsgExists()) {
		$sqlfields = "sectionid, code, name";
		$sqldata   = $_page->sectionid.", '$code', $name";
    	switch($_page->savebuttonpressed) {
    		case "save":
                if(insertRec($db->table, $sqlfields, $sqldata)){
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

// build javascript block
$js = new JSBlock();
$js->subject = "type";
$js->section = "products_types";
$js->addCheckReqEntry('name', 'Please enter the product type name.');
$js->buildCheckFormFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Product Types" => "products/list-products_types.php"));
showEditorButtons(DEF_EDITBUT_SAVE);
echo "</div>\n";

startEditorBlock("add_content");
showPageTitle("Create New Product Type");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Type Name (limited to 50 characters)*", "name", $name, 60, 50);
endPageForm();
showFooter();
?>
