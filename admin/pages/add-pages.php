<?php

//  ------------------------------------------------------------------------------------
//  CREATE CKEDITOR-BASED PAGE
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = "pages";
$db->child_table= "editor_userpages";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$_page->sectionid = intval(getRequestVar('sectionid'));
    $pagetitle = clean_text(getRequestVar('pagetitle', true));
	$pagename = codify($pagetitle);
    $pagealias = getRequestVar('pagealias');
    if($pagename == '') $pagename = codify($pagetitle);
	$content = getRequestVar('content', false, false);
    $ppage_id = getRequestVar('ppage_id');
	$language = DEF_LANGUAGE;
    $lineage = getNextLineage($db->table, $ppage_id);
    $locked = intval(getRequestVar('locked'));
    if(getRecNumRows($db->table, "id", "pagename = '$pagename'") > 0 && $pagename != '') {
		addErrorMsg("The page alias `".strtoupper($pagename)."` already exists.");
    }

	$sqlfields = "sectionid, ppage_id, pagetitle, pagename, pagealias, description, locked, language, lineage";
	$sqldata = $_page->sectionid.", $ppage_id, '$pagetitle', '$pagename', '$pagealias', '', 0, '$language', '$lineage'";
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

	// update page content in 'editor_userpages' table
	if(getErrorStatMsg(SUCCESS_CREATE)){
		initErrorMsg();
		if(insertRec($db->child_table, "content, pageid", "'$content', '{$_page->row_id}'")) {
			addErrorStatMsg(SUCCESS_CREATE);
			if(function_exists('sm_start')){
				$_GET['chgfreq'] = "monthly"; $_GET['priority'] = 1; $_GET['gensitepage'] = 1; $_GET['dbfile'] = 1; $_GET['verbose'] = 0;
				sm_start();
			}
		}else{
			addErrorStatMsg(FAILURE_CREATE);
		}
	}

	if(getErrorStatMsg(SUCCESS_CREATE)) gotoEditPage();
}

// build query
$pagetitle = getRequestVar('pagetitle');
$pagename = getRequestVar('pagename');
$pagealias = getRequestVar('pagealias');
$content = getRequestVar('content');
$ppage_id = getRequestVar('ppage_id');
$locked = getRequestVar('locked');
$ppage_array = getCategories($db->table, $_page->row_id, true);

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
showEditorButtons(DEF_EDITBUT_SAVE);
echo "</div>\n";

startEditorBlock("add_content");
showPageTitle("Create New Page");
showReqdText();

startPageForm("edit_form", "", "POST", true);
$atts = array("label" => "Title*", "id" => "pagetitle", "value" => $pagetitle, "fldclass" => "bigfldtext");
showObject("text", $atts);
if(userIsAtleast(ADMLEVEL_DEVELOPER)) {
	$atts = array("label" => "Page Alias", "id" => "pagename", "value" => (($pagealias != 'index') ? $pagename : ''), "wrappertext" => "before:".WEB_URL."', help:'Tip: folder paths are accepted.'");
	showObject("text", $atts);
}else{
	$atts = array("id" => "pagename", "value" => $pagename);
	showObject("hidden", $atts);
}
$atts = array("id" => "pagealias", "value" => $pagealias);
showObject("hidden", $atts);
$atts = array("label" => "Parent Page", "id" => "ppage_id", "valuearray" => $ppage_array, "selectedvalue" => $ppage_id, "wrappertext" => "help:'Tip: only pages with no parent can be the home page.'");
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
showFooter();
?>
