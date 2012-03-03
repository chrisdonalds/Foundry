<?php

//  ------------------------------------------------------------------------------------
//  LIST
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."testimonials";

startContentArea();

// build search query
$_page->prepSearch(array(
		"sort_by" => "client_name",
		"sort_dir" => "ASC",
		"search_list" => array("client_name", "client_company")
));

// build query
if(USE_SECTIONS) $_page->where_clause .= $_page->concat." sectionid = ".$_page->sectionid;
$rowcount = getRecNumRows($db->table, "*", $_page->where_clause);
$db->savenextquery();
$recset = getRec($db->table, "*", $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit);

$_page->ingroup = "testimonials";
$_page->subject = "testimonial";
showPageTitle("List of Testimonials");

// column names
$cols 		= array (	"client_name" => "Name",
						"client_company" => "Company",
						"testimonial" => "Testimonial"
					);
// column attributes
$colattr	= array (	"testimonial" => "attr:hover"
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"client_name" => "20%",
						"client_company" => "20%",
						"testimonial" => "25%"
					);
$sortcols 	= array (	"client_name" => "Name",
						"client_company" => "Company",
						"testimonial" => "Testimonial"
					);
$searchcols = array (   "client_name" => "Name",
						"client_company" => "Company",
						"testimonial" => "Testimonial",
						"published" => "Published",
						"archived" => "Archived",
						"deleted" => "Deleted",
						"draft" => "Draft"
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( index1 => array(but1, but2...), index2 => array(but1, but2...) ... )
// aliased button labels in the form DEF_ACTION_LABEL."::alias"
$buttons 	= array (	DEF_ACTION_EDIT, DEF_ACTION_DELETE, DEF_ACTION_UNDELETE, DEF_ACTION_PUBLISH, DEF_ACTION_UNPUBLISH, DEF_ACTION_ARCHIVE, DEF_ACTION_UNARCHIVE );

startPageForm('list_form');
showSearch();
showPagination($rowcount, DEF_PAGEBUT_ADDNEW);
showList($db->table, "", $recset, "", "", ALLOW_SORT);
showPagination($rowcount);
endPageForm();
showFooter();
?>