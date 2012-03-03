<?php

//  ------------------------------------------------------------------------------------
//  LIST
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."newsletters";

startContentArea();

// build search query
$_page->prepSearch(array(
		"sort_by" => "start_date",
		"sort_dir" => "DESC",
		"search_list" => array("itemtitle", "date")
));

// build query
if(USE_SECTIONS) $_page->where_clause .= $_page->concat." sectionid = ".$_page->sectionid;
$rowcount = getRecNumRows($db->table, "*", $_page->where_clause);
$db->savenextquery();
$recset = getRec($db->table." w", "w.*", $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit);

$_page->ingroup = "newsletters";
$_page->subject = "newsletter";
showPageTitle("List of Newsletters");

// column names
$cols 		= array (	"_chk" => "",
						"start_date" => "Date",
						"itemtitle" => "Title",
						"content" => "Content",
						"published" => "Status"
					);
// column attributes
$colattr	= array (	"content" => "hover",
						"published" => "attr:boolean; trueval:Published; falseval:Draft"
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"start_date" => "15%",
						"itemtitle" => "25%",
						"content" => "20%",
						"published" => "8%"
					);
$sortcols 	= array (	"start_date" => "Date",
						"itemtitle" => "Title",
						"content" => "Content",
						"published" => "Status"
					);
$searchcols = array (   "start_date" => "Date",
						"itemtitle" => "Title",
						"content" => "Content",
						"published" => "Published",
						"deleted" => "Deleted",
						"draft" => "Draft"
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( index1 => array(but1, but2...), index2 => array(but1, but2...) ... )
// aliased button labels in the form DEF_ACTION_LABEL."::alias"
$buttons 	= array (	DEF_ACTION_EDIT, DEF_ACTION_VIEWRECS."::Subscribers", DEF_ACTION_CLONE, DEF_ACTION_PUBLISH, DEF_ACTION_UNPUBLISH, DEF_ACTION_DELETE, DEF_ACTION_UNDELETE );

startPageForm('list_form');
showSearch();
showPagination($rowcount, DEF_PAGEBUT_ADDNEW);
showList($db->table, "", $recset, "", "", ALLOW_SORT);
showPagination($rowcount);
endPageForm();
showFooter();
?>