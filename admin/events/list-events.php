<?php

//  ------------------------------------------------------------------------------------
//  LIST
//  ------------------------------------------------------------------------------------

include ("../header.php");
$db->table = DB_TABLE_PREFIX."events";

startContentArea();

// build search query
$_page->prepSearch(array(
		"sort_by" => "itemtitle",
		"sort_dir" => "ASC",
		"search_list" => array("itemtitle", "start_date")
));

// build query
$_page->where_clause .= $_page->concat." eventtype = 'event'";
if(USE_SECTIONS) $_page->where_clause .= $_page->concat." sectionid = ".$_page->sectionid;
$rowcount = getRecNumRows($db->table." as d", "d.*", $_page->where_clause);
$db->savenextquery();
$recset = getRec($db->table." as d", "*", $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit);

$_page->ingroup = "events";
$_page->subject = "event";
$_page->titlefld = "itemtitle";
showPageTitle("List of Events");

// column names
$cols 		= array (	"_chk" => "",
						"itemtitle" => "Title",
						"start_date" => "Start Date",
						"description" => "Description",
						"published" => "Status"
					);
// column attributes
$colattr	= array (	"description" => "attr:hover",
						"published" => "attr:boolean; trueval:Published; falseval:Draft"
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"itemtitle" => "30%",
						"start_date" => "15%",
						"description" => "15%",
						"published" => "8%"
					);
$sortcols 	= array (	"itemtitle" => "Title",
						"start_date" => "Start Date",
						"published" => "Status"
					);
$searchcols = array (	"itemtitle" => "Title",
						"start_date" => "Start Date",
						"published" => "Published",
						"deleted" => "Deleted"
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( array (but1cond1, but1cond2...), array (but2cond1, but2cond2...) ... )
$buttons 	= array (	DEF_ACTION_EDIT, DEF_ACTION_CLONE."::title", DEF_ACTION_PUBLISH, DEF_ACTION_UNPUBLISH, DEF_ACTION_DELETE, DEF_ACTION_UNDELETE );

startPageForm('list_form');
showSearch();
showPagination($rowcount, DEF_PAGEBUT_ADDNEW);
showList($db->table, "", $recset, "", "", ALLOW_SORT);
showPagination($rowcount);
endPageForm();
showFooter();
?>