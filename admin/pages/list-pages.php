<?php

//  ------------------------------------------------------------------------------------
//  LIST PAGES
//  ------------------------------------------------------------------------------------
$incl = "";
include ("../loader.php");

$db->table = "pages";
$db->child_table = "editor-userpages";

addErrorMsg(RUNTIME_ERR, "some text");
setPageHelp("test help contents<br/><ul><li>Item 1</li><li>Item 2</li></ul><a href=\"#\">Link</a>");
showHeader();

startContentArea();

// build search query
$_page->prepSearch(array(
		"sort_by" => "lineage",
		"sort_dir" => "ASC",
		"search_list" => array("p.pagename", "p.pagetitle")
));

// build query
$_page->where_clause .= $_page->concat." ".((userIsAtleast(ADMLEVEL_DEVELOPER)) ? "" : "p.displayed = 1 AND ")."p.pagetypeid > 0";
$rowcount = getRecNumRows($db->table." p", "*", $_page->where_clause);
$db->savenextquery();
$recset = getRecJoin($db->table." p", $db->table." pp", "p.*, pp.pagetitle as parentpage", "p.ppage_id = pp.id", "LEFT JOIN", $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit);

$_page->ingroup = "pages";
$_page->subject = "page";
$_page->titlefld = "pagetitle";
$_page->addqueries = array(DEF_ACTION_PUBLISH => "pub_id=1", DEF_ACTION_UNPUBLISH => "unpub_id=1");
$_page->altgroups = array(DEF_ACTION_DELETE => "test");
$_page->altparams = array(DEF_ACTION_EDIT => "test_id");
showPageTitle("List of Pages");

// col names
$cols 		= array (	"_chk" => "",
						"pagetitle" => "Page",
                        "parentpage" => "Parent",
						"metatitle" => "Meta Title",
						"metakeywords" => "Keywords",
						"metadescr" => "Meta Description",
						"pagealias" => "Homepage?",
						"published"	=> "Status"
					);
// column attributes
$colattr	= array (	"pagetitle" => "attr:indent; padstr:'&nbsp\;&nbsp\;&nbsp\;&nbsp\;'; checkfield:lineage; checkval:,",
                        "metatitle" => "attr:boolean; trueval:Completed; falseval:Incomplete",
						"metakeywords" => "attr:boolean; trueval:Completed; falseval:Incomplete",
						"metadescr" => "attr:boolean; trueval:Completed; falseval:Incomplete",
						"pagealias" => "attr:advexpr; compareusing:=; compareval:index; trueval:Yes; falseval:",
						"published" => "attr:boolean; trueval:Published; falseval:Draft"
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"pagetitle" => "15%",
                        "parentpage" => "15%",
						"metatitle" => "10%",
						"metakeywords" => "10%",
						"metadescr" => "10%",
						"pagealias" => "10%",
						"published" => "8%"
					);
$sortcols 	= array (	"pagetitle" => "Page",
                        "lineage" => "Hierarchy",
						"published" => "Status"
					);
$searchcols = array (	"p.pagetitle" => "Page",
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( index1 => array(but1, but2...), index2 => array(but1, but2...) ... )
// aliased button labels in the form DEF_ACTION_LABEL."::alias"
$buttons 	= array ( "1" => array (DEF_ACTION_EDIT.":: Page", DEF_ACTION_EDITMETA, DEF_ACTION_PUBLISH, DEF_ACTION_UNPUBLISH, DEF_ACTION_CLONE, DEF_ACTION_DELETE, DEF_ACTION_UNDELETE),
					  "2" => array (DEF_ACTION_EDIT.":: Page", DEF_ACTION_EDITMETA, DEF_ACTION_PUBLISH, DEF_ACTION_UNPUBLISH, DEF_ACTION_DELETE, DEF_ACTION_UNDELETE),
					  "3" => array (DEF_ACTION_EDIT.":: Page", DEF_ACTION_EDITMETA, DEF_ACTION_PUBLISH, DEF_ACTION_UNPUBLISH, DEF_ACTION_DELETE, DEF_ACTION_UNDELETE)
					);

startPageForm('list_form');
showSearch();
showPagination($rowcount, DEF_PAGEBUT_ADDNEW);
showList($db->table, array("ppage_id" => ""), $recset, "pagetypeid", "pagename", ALLOW_SORT);
showPagination($rowcount);
endPageForm();
showFooter();
?>