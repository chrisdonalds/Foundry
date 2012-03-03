<?php

//  ------------------------------------------------------------------------------------
//  LIST
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."subscribers";

startContentArea();

// build search query
$_page->prepSearch(array(
		"sort_by" => "lastname, firstname",
		"sort_dir" => "ASC",
		"search_list" => array("firstname", "lastname", "email")
));

// build query
if(USE_SECTIONS) $_page->where_clause .= $_page->concat." sectionid = ".$_page->sectionid;
$rowcount = getRecNumRows($db->table." as d", "d.*", $_page->where_clause);
$db->savenextquery();
$recset = getRecJoin($db->table." as d", DB_TABLE_PREFIX."newsletters as n", "d.*, n.itemtitle as newsletter", "d.newsletter = n.id", "LEFT JOIN", $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit, "");

$_page->ingroup = "subscribers";
$_page->subject = "subscriber";
showPageTitle("List of Newsletter Subscribers");

// col names
$cols 		= array (	"_chk" => "",
						"lastname" => "Last Name",
						"firstname" => "First Name",
						"newsletter" => "Newsletter",
						"email" => "Email",
						"activated" => "Activated?"
					);
// column attributes
$colattr	= array (	"activated" => "attr:boolean; trueval:yes; falseval:no"
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"lastname" => "15%",
						"firstname" => "15%",
						"newsletter" => "20%",
						"email" => "25%",
						"activated" => "8%"
					);
$sortcols 	= array (	"lastname" => "Last Name",
						"firstname" => "First Name",
						"newsletter" => "Newsletter",
						"email" => "Email",
						"activated" => "Activated"
					);
$searchcols = array (	"firstname" => "First Name",
						"lastname" => "Last Name",
						"newsletter" => "Newsletter",
						"email" => "Email",
						"activated" => "Activated",
						"deleted" => "Deleted"
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( array (but1cond1, but1cond2...), array (but2cond1, but2cond2...) ... )
$buttons 	= array (	DEF_ACTION_EDIT, DEF_ACTION_DELETE, DEF_ACTION_UNDELETE, DEF_ACTION_ACTIVATE, DEF_ACTION_DEACTIVATE );

startPageForm('list_form');
showSearch();
showPagination($rowcount);
showList($db->table, "", $recset, "", "", ALLOW_SORT);
showPagination($rowcount);
endPageForm();
WshowFooter();
?>