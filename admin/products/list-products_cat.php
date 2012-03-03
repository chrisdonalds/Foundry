<?php

//  ------------------------------------------------------------------------------------
//  LIST CAT
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."products_cat";

startContentArea();

// build search query
$_page->prepSearch(array(
		"sort_by" => "c.name",
		"sort_dir" => "ASC",
		"search_list" => array("c.name"),
		"custom_query" => array("product" => " c.id IN (SELECT cat_id FROM data_products_cat WHERE id IN (SELECT cat_id FROM data_products WHERE itemtitle LIKE '%{$_page->search_text}%'))",
								"name" => " (c.id IN (SELECT cat_id FROM data_products_cat WHERE name LIKE '%{$_page->search_text}%') OR c.name LIKE '%{$_page->search_text}%')")
));

// build query
if(USE_SECTIONS) $_page->where_clause = $_page->concat." sectionid = ".$_page->sectionid;
$_page->where_clause .= $_page->concat." c.cat_id = 0";
$rowcount = getRecJoinNumRows("$db->table AS c", DB_TABLE_PREFIX."products_cat AS p", "c.*, COUNT(p.id) AS numitems", "c.id = p.cat_id", "LEFT JOIN", $_page->where_clause, "c.name");
$db->savenextquery();
$recset = getRecJoin("$db->table AS c", DB_TABLE_PREFIX."products_cat AS p", "c.*, COUNT(p.id) AS numitems", "c.id = p.cat_id", "LEFT JOIN", $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit, "c.name");

$_page->ingroup = "products";
$_page->subject = "category";
showPageTitle("List of Product Categories");

// column names
$cols 		= array (	"name" => "Category",
						"numitems" => "# Sub-Categories"
					);
// column attributes
$colattr	= array (
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"name" => "30%",
						"numitems" => "15%"
					);
$sortcols 	= array (	"name" => "Category",
						"numitems" => "Sub-Categories Count"
					);
$searchcols = array (	"name" => "Category/Sub-Category",
                        "product" => "Product Name",
						"archived" => "Archived",
						"deleted" => "Deleted",
						"published" => "Published"
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( index1 => array(but1, but2...), index2 => array(but1, but2...) ... )
// aliased button labels in the form DEF_ACTION_LABEL."::alias"
$buttons = array    (	DEF_ACTION_EDIT, DEF_ACTION_VIEWRECS."::Sub Categories", DEF_ACTION_ADD."::Sub Category", DEF_ACTION_DELETE, DEF_ACTION_UNDELETE, DEF_ACTION_ACTIVATE, DEF_ACTION_DEACTIVATE );

startPageForm('list_form');
showSearch();
showPagination($rowcount, DEF_PAGEBUT_ADDNEW);
showList($db->table, "", $recset, "", "", ALLOW_SORT);
showPagination($rowcount);
endPageForm();
showFooter();
?>