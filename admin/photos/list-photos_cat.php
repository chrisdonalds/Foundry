<?php

//  ------------------------------------------------------------------------------------
//  LIST PHOTOS photos_cat
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."photos_cat";

startContentArea();

// build search query
$_page->prepSearch(array(
		"sort_by" => "pg.name",
		"sort_dir" => "ASC",
		"search_list" => array("name")
));

// build query
if(USE_SECTIONS) $_page->where_clause = $_page->concat." sectionid = ".$_page->sectionid;
$rowcount = getRecJoinNumRows("$db->table AS pg", DB_TABLE_PREFIX."photos AS p", "pg.*, COUNT(p.id) AS numpics", "pg.id = p.cat_id", "LEFT JOIN", $_page->where_clause, "pg.name");
$db->savenextquery();
$recset = getRecJoin("$db->table AS pg", DB_TABLE_PREFIX."photos AS p", "pg.*, COUNT(p.id) AS numpics, (SELECT thumb FROM data_photos WHERE gallery_def = 1 AND cat_id = pg.id) AS thumb", "pg.id = p.cat_id", "LEFT JOIN", $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit, "pg.name");

$_page->ingroup = "photos_cat";
$_page->subject = "gallery";
$_page->childsubject = "photos";
$_page->titlefld = "name";
showPageTitle("List of Galleries");

// column names
$cols 		= array (	"_chk" => "",
						"name" => "Gallery",
						"numpics" => "# Images",
						"published" => "Status"
					);
// column attributes
$colattr	= array (	"name" => "attr:image",
						"published" => "attr:boolean; trueval:Published; falseval:Draft"
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"name" => "30%",
						"numpics" => "10%",
						"published" => "8%"
					);
$sortcols 	= array (	"name" => "Gallery",
						"numpics" => "Image Count",
						"published" => "Status"
					);
$searchcols = array (	"name" => "Gallery",
						"archived" => "Archived",
						"deleted" => "Deleted",
						"published" => "Published"
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( index1 => array(but1, but2...), index2 => array(but1, but2...) ... )
// aliased button labels in the form DEF_ACTION_LABEL."::alias"
$buttons = array    (	DEF_ACTION_EDIT, DEF_ACTION_VIEWRECS."::Images", DEF_ACTION_ADD."::Image", DEF_ACTION_PUBLISH, DEF_ACTION_UNPUBLISH, DEF_ACTION_DELETE, DEF_ACTION_UNDELETE );

startPageForm('list_form');
showSearch();
showPagination($rowcount, DEF_PAGEBUT_ADDNEW);
showList($db->table, "", $recset, "", "", ALLOW_SORT);
showPagination($rowcount);
endPageForm();
showFooter();
?>