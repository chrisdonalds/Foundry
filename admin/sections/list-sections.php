<?php

//  ------------------------------------------------------------------------------------
//  LIST SECTIONS
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = "sections";

startContentArea();

// build search query
$_page->prepSearch(array(
		"sort_by" => "id",
		"sort_dir" => "ASC",
		"search_list" => null
));

// build query
$_page->where_clause .= $_page->concat." id > 0";
$rowcount = getRecNumRows($db->table, "*", $_page->where_clause);
$db->savenextquery();
$recset = getRec($db->table, "*", $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit);

$_page->ingroup = "sections";
$_page->subject = "section";
showPageTitle("List of Sections");

// column names
$cols 		= array (	"name" => "Section",
						"description" => "Description"
					);
// column attributes
$colattr	= array (	"description" => "attr:hover"
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"name" => "20%",
						"description" => "60%"
					);
$sortcols 	= array (	"name" => "Page"
					);
$searchcols = array (	"name" => "section",
						"description" => "Description"
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( index1 => array(but1, but2...), index2 => array(but1, but2...) ... )
// aliased button labels in the form DEF_ACTION_LABEL."::alias"
$buttons 	= array ( DEF_ACTION_OPEN );

startPageForm('list_form');
showPagination($rowcount);
showList($db->table, "", $recset, "", "", ALLOW_SORT);
showPagination($rowcount);
endPageForm();
showFooter();
?>