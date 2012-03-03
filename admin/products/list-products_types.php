<?php

//  ------------------------------------------------------------------------------------
//  LIST CAT
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."products_types";

startContentArea();

// build search query
$_page->prepSearch(array(
		"sort_by" => "c.name",
		"sort_dir" => "ASC",
		"search_list" => array("name")
));

// build query
if(USE_SECTIONS) $_page->where_clause = $_page->concat." sectionid = ".$_page->sectionid;
$rowcount = getRecNumRows("$db->table AS c", "c.*", $_page->where_clause, "c.name");
$db->savenextquery();
$recset = getRec("$db->table AS c", "c.*", $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit, "c.name");

$_page->ingroup = "products_types";
$_page->subject = "type";
showPageTitle("List of Product Types");

// column names
$cols 		= array (	"name" => "Type",
					);
// column attributes
$colattr	= array (
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"name" => "50%",
					);
$sortcols 	= array (	"name" => "Type",
					);
$searchcols = array (	"name" => "Type",
						"deleted" => "Deleted",
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( index1 => array(but1, but2...), index2 => array(but1, but2...) ... )
// aliased button labels in the form DEF_ACTION_LABEL."::alias"
$buttons = array    (	DEF_ACTION_EDIT, DEF_ACTION_DELETE, DEF_ACTION_UNDELETE );

startPageForm('list_form');
showSearch();
showPagination($rowcount, DEF_PAGEBUT_ADDNEW);
showList($db->table, "", $recset, "", "", ALLOW_SORT);
showPagination($rowcount);
endPageForm();
showFooter();
?>