<?php

//  ------------------------------------------------------------------------------------
//  EDIT CKEDITOR-BASED PAGE
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = "pages";
$db->child_table = "editor_userpages";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
    $pagetitle = clean_text(getRequestVar('pagetitle', true));
    $pagename = getRequestVar('pagename');
    $pagealias = getRequestVar('pagealias');
    if($pagename == '') $pagename = codify($pagetitle);
    $content = getRequestVar('content', false, false);
    $ppage_id = getRequestVar('ppage_id');
    $locked = intval(getRequestVar('locked'));
    if(getRecNumRows($db->table, "id", "pagename = '$pagename' AND id != '$_page->row_id'") > 0 && $pagename != '') {
		addErrorMsg("The page alias `".strtoupper($pagename)."` already exists.");
    }

    if(!errorMsgExists()){
        // update lineages (hierarchical categorization)
        if($ppage_id != getRecItem($db->table, "ppage_id", "id = '$_page->row_id'")){
            $new_lineage = getNextLineage($db->table, $ppage_id);
            updateLineages($db->table, $_page->row_id, $new_lineage);
        }

        $sqlbase = "sectionid = ".$_page->sectionid.", pagename = '$pagename', pagetitle = '$pagetitle', pagealias = '$pagealias', language = '".DEF_LANGUAGE."', ppage_id = '$ppage_id', locked = '$locked', ";
        switch($_page->savebuttonpressed) {
            case DEF_POST_ACTION_SAVE:
                if(updateRec($db->table, $sqlbase."date_updated = NOW()", "id = $_page->row_id")){
                    addErrorStatMsg(SUCCESS_EDIT);
                }else{
                    addErrorStatMsg(FAILURE_EDIT);
                }
                break;
        }
    	if($pagealias == 'index') switchHomePageTo($pagetitle);

        // update child lineages and page content in 'editor_userpages' table
        if(getErrorStatMsg(SUCCESS_EDIT)){
            initErrorMsg();
            if(!isblank($new_lineage))
                updateChildLineages($db->table, $_page->row_id);

            deleteRec($db->child_table, "pageid = '$_page->row_id'");
            if(insertRec($db->child_table, "content, pageid", "'$content', '$_page->row_id'")) {
                addErrorStatMsg(SUCCESS_EDIT);
                if(function_exists('sm_start')){
                    $_GET['chgfreq'] = "monthly"; $_GET['priority'] = 1; $_GET['gensitepage'] = 1; $_GET['dbfile'] = 1; $_GET['verbose'] = 0;
                    sm_start();
                }
            }else{
                addErrorStatMsg(FAILURE_EDIT);
            }
        }
    }
}

// build query

$recset = getRec($db->table, "*", "id = '{$_page->row_id}'", "", "");
if(count($recset) == 1) {
    extractVariables($recset[0]);
	$pageset = getRec($db->child_table, "*", "pageid = '{$_page->row_id}'", "", "");
	if(count($pageset) > 0) extractVariables($pageset[0]);
}else{
    gotoPage("list-pages.php");
	exit;
}

$ppage = getRecItem($db->table, "pagetitle", "id = {$ppage_id}");
if($ppage == "") $ppage = "Main";
$ppage_array = getCategories($db->table, $_page->row_id, true, false);

// build javascript block
$js = new JSBlock();
$js->subject = "page";
$js->section = "pages";
$js->addCheckReqEntry('pagetitle', 'Please enter the page title.');
$js->addCheckReqCKEditorEntry('content', 'Please provide the page content.');
$js->buildCheckFormFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Pages List" => "pages/list-pages.php"));
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_PREVIEW+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit Page");
showReqdText();

startPageForm("edit_form", "", "POST", true);
$atts = array("label" => "Title*", "id" => "pagetitle", "value" => $pagetitle, "fldclass" => "bigfldtext");
showObject("text", $atts);
if(userIsAtleast(ADMLEVEL_DEVELOPER)) {
	$after = 'after:'.(($pagealias != 'index' && !file_exists(SITE_PATH."index.php") && $ppage_id == 0) ? '<input type="button" id="editpage_setashome" value="Set as Home Page" />' : '');
	$after .= "&nbsp;<a href=\"#\" class=\"editor_button_preview\">Preview it</a>";
	$atts = array("label" => "Page Alias", "id" => "pagename", "value" => (($pagealias != 'index') ? $pagename : ''), "wrappertext" => $after.", before:'".WEB_URL."', help:'Tip: folder paths are accepted.'");
	showObject("text", $atts);
}else{
	$atts = array("id" => "pagename", "value" => $pagename);
	showObject("hidden", $atts);
}
$atts = array("id" => "pagealias", "value" => $pagealias);
showObject("hidden", $atts);
$atts = array("label" => "Parent Page", "id" => "ppage_id", "valuearray" => $ppage_array, "selectedvalue" => $ppage_id, "wrappertext" => "help:'Tip: only pages with no parent can be the home page.'", "disablevalue" => $_page->row_id);
showObject("list", $atts);
if(userIsAtleast(ADMLEVEL_DEVELOPER)) {
	$atts = array("label" => "Page is Locked?", "id" => "locked", "value" => 1, "chkstate" => $locked, "text" => "If checked, this page will be locked");
	showObject("checkbox", $atts);
}else{
	$atts = array("id" => "locked", "value" => $locked);
	showObject("hidden", $atts);
}
$atts = array("label" => "Content*", "id" => "content", "value" => $content, "dim" => array(0, 600));
showObject("editor", $atts);
endPageForm();
showStats();
showFooter();
?>
