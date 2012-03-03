<?php

//  ------------------------------------------------------------------------------------
//  LIST
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."whatsnew";

startContentArea();

// build search query
$_page->prepSearch(array(
		"sort_by" => "start_date",
		"sort_dir" => "DESC",
		"search_list" => array("itemtitle", "description")
));

// build query
if(USE_SECTIONS) $_page->where_clause .= $_page->concat." sectionid = ".$_page->sectionid;
$rowcount = getRecNumRows($db->table, "*", $_page->where_clause);
$db->savenextquery();
$recset = getRec($db->table." w", "w.*", $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit);

$_page->ingroup = "whatsnew";
$_page->subject = "article";
$_page->titlefld = 'itemtitle';
showPageTitle("List of What's New Articles");

// column names
$cols 		= array (	"start_date" => "Date",
						"itemtitle" => "Title",
						"description" => "Description"
					);
// column attributes
$colattr	= array (	"description" => "attr:hover"
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"start_date" => "15%",
						"itemtitle" => "15%",
						"description" => "30%"
					);
$sortcols 	= array (	"start_date" => "Date",
						"itemtitle" => "Title",
						"description" => "Description"
					);
$searchcols = array (   "start_date" => "Date",
						"itemtitle" => "Title",
						"description" => "Description",
						"published" => "Published",
						"deleted" => "Deleted",
						"draft" => "Draft"
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( index1 => array(but1, but2...), index2 => array(but1, but2...) ... )
// aliased button labels in the form DEF_ACTION_LABEL."::alias"
$buttons 	= array (	DEF_ACTION_EDIT, DEF_ACTION_CLONE, DEF_ACTION_DELETE, DEF_ACTION_UNDELETE, DEF_ACTION_PUBLISH, DEF_ACTION_UNPUBLISH );

startPageForm('list_form');
showSearch();
showPagination($rowcount, DEF_PAGEBUT_ADDNEW);
showList($db->table, "", $recset, "", "", ALLOW_SORT);
showPagination($rowcount);
endPageForm();
showFooter();
?>