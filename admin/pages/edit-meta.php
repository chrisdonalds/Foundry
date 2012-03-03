<?php

//  ------------------------------------------------------------------------------------
//  EDIT CKEDITOR-BASED PAGE
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db = new DB_wrapper();
$db->table = "pages";
$db->child_table= "editor_userpages";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$metatitle = clean_text(getRequestVar('metatitle'));
	$metakeywords = clean_text(getRequestVar('metakeywords'));
	$metadescr = clean_text(getRequestVar('metadescr'));
	$language = DEF_LANGUAGE;

	$sqlbase = "metatitle = '$metatitle', metakeywords = '$metakeywords', metadescr = '$metadescr', ";
	switch($_page->savebuttonpressed) {
		case DEF_POST_ACTION_SAVE:
            if(updateRec($db->table, $sqlbase."date_updated = NOW()", "id = '{$_page->row_id}'")){
            	addErrorStatMsg(SUCCESS_EDIT);
            }else{
            	addErrorStatMsg(FAILURE_EDIT);
            }
			break;
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

// build javascript block
$js = new JSBlock();
$js->subject = "page";
$js->section = "pages";
$js->buildCheckFormFunc();
$js->buildCopyDataFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Pages" => "pages/list-pages.php"));
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit '".strtoupper($pagename)."' Meta-Data");
showReqdText();

startPageForm("edit_form", "", "POST", true, array("pagetitle" => $pagetitle, "content" => strip_tags($content)));
$atts = array();
showBlock("Meta-data (metatitle, metakeywords, and metadescription) are used by search engines such as Google and Yahoo to list the page and help aid with search-engine optimization (SEO).  If this data is not provided, the page will be supplied with the site's default data.", "", "", BLOCK_P);
$atts = array("label" => "Meta Title", "id" => "metatitle", "value" => $metatitle, "displaytype" => FLD_OPENROW);
showObject("text", $atts);
$atts = array("id" => "metatitle_copy", "value" => "Same as Page title", "fldclass" => "editpage_copydata", "js" => "rel=\"pagetitle,metatitle\"", "wrappertext" => "help: 'The site name is already provided'", "displaytype" => FLD_CLOSEROW);
showObject("button", $atts);
$atts = array("label" => "Meta Description", "id" => "metadescr", "value" => $metadescr, "displaytype" => FLD_OPENROW);
showObject("textarea", $atts);
$atts = array("id" => "metadescr_copy", "value" => "Same as Description", "fldclass" => "editpage_copydata", "js" => "rel=\"content,metadescr\"", "wrappertext" => "help: 'Max. 255 characters only.'", "displaytype" => FLD_CLOSEROW);
showObject("button", $atts);
$atts = array("label" => "Meta Keywords", "id" => "metakeywords", "value" => $metakeywords, "wrappertext" => "help: 'Comma-separated. Up to 512 characters.'");
showObject("textarea", $atts);
endPageForm();
showStats();
showFooter();
?>
