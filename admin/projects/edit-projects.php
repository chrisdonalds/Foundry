<?php

//  ------------------------------------------------------------------------------------
//  EDIT CKEDITOR-BASED PAGE
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."projects";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
    $name = clean_text(getRequestVar('name', true));
    $code = codify($name);
    $cat_id = getRequestVar('cat_id');
    $description = getRequestVar('description', false, false);
    if(getRecNumRows($db->table, "id", "code = '$code' AND id != '$_page->row_id'") > 0 && $code != '') {
		addErrorMsg("The code `".strtoupper($code)."` already exists.");
    }

    if(!errorMsgExists()){
        // update lineages (hierarchical categorization)
        if($cat_id != getRecItem($db->table, "cat_id", "id = '$_page->row_id'")){
            $new_lineage = getNextLineage($db->table, $cat_id);
            updateLineages($db->table, $_page->row_id, $new_lineage);
        }

        $sqlbase = "code = '$code', name = '$name', cat_id = '$cat_id',";
        switch($_page->savebuttonpressed) {
            case DEF_POST_ACTION_SAVE:
                if(updateRec($db->table, $sqlbase."date_updated = NOW()", "id = $_page->row_id")){
                    addErrorStatMsg(SUCCESS_EDIT);
                    if(!isblank($new_lineage))
                        updateChildLineages($db->table, $_page->row_id);
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
    gotoPage("list-projects.php");
	exit;
}
$cat_array = getCategories($db->table, $_page->row_id, true, false);

// build javascript block
$js = new JSBlock();
$js->subject = "project";
$js->section = "projects";
$js->addCheckReqEntry('name', 'Please enter the title.');
$js->buildCheckFormFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Projects List" => "projects/list-projects.php"));
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit Project");
showReqdText();

startPageForm("edit_form", "", "POST", true);
$atts = array("label" => "Title*", "id" => "name", "value" => $name, "fldclass" => "bigfldtext");
showObject("text", $atts);
$atts = array("label" => "Parent", "id" => "cat_id", "valuearray" => $cat_array, "selectedvalue" => $cat_id, "disablevalue" => $_page->row_id);
showObject("list", $atts);
$atts = array("label" => "Content*", "id" => "description", "value" => $description, "dim" => array(0, 600));
showObject("textarea", $atts);
endPageForm();
showStats();
showFooter();
?>
