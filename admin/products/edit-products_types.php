<?php

//  ------------------------------------------------------------------------------------
//  EDIT
//  ------------------------------------------------------------------------------------

$incl = "filehandler";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."products_types";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$_page->sectionid = intval(getRequestVar('sectionid'));
    $name = getQuotedRequestVar('name', true);
	$code = codify($name);
	if(getRecItem($db->table, "id", "name = '$name' AND id != $cat_id AND sectionid = ".$_page->sectionid) != ""){
		addErrorMsg(sprintf(DUPLICATE_RECORD, "Type named `".strtoupper(getRequestVar('name'))."`", "", "name"));
	}

	if (!errorMsgExists()) {
		$sqlbase = "code = '$code', name = $name";
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
    gotoPage("list-products_types.php");
	exit;
}

// build javascript block
$js = new JSBlock();
$js->subject = "type";
$js->section = "products_types";
$js->addCheckReqEntry('name', 'Please enter the product type name.');
$js->buildCheckFormFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Product Types" => "products/list-products_types.php"));
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit '$name' Product Type");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Type Name (limited to 50 characters)*", "name", $name, 60, 50);
endPageForm();
showStats();
showFooter();
?>
