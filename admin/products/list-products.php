<?php

//  ------------------------------------------------------------------------------------
//  LIST
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."products";
$cat_id = intval(getRequestVar('cat_id'));
$parent_cat_rec = getrec($db->table."_cat", "cat_id, name", "id = '{$cat_id}'", "", "");
$cat_name = $parent_cat_rec[0]['name'];

startContentArea();

// build search query
$_page->prepSearch(array(
		"sort_by" => "rank",
		"sort_dir" => "ASC",
		"search_list" => array("itemtitle", "description")
));

// build query
if(USE_SECTIONS) $_page->where_clause .= $_page->concat." sectionid = ".$_page->sectionid;
$_page->where_clause .= $_page->concat." p.cat_id = '$_page->cat_id'";
$rowcount = getRecNumRows($db->table, "*", $_page->where_clause);
$db->savenextquery();
$recset = getRecJoin($db->table." p", DB_TABLE_PREFIX."products_cat c", "p.*, c.name as category", "p.cat_id = c.id", "LEFT JOIN", $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit);

$_page->ingroup = "products";
$_page->subject = "product";
showPageTitle("List of '".strtoupper($cat_name)."' Products");

// column names
$cols 		= array (	"product_id" => "Product ID",
						"itemtitle" => "Name",
						"price" => "Reg. Price",
						"sale_price" => "Sale Price",
						"case_price" => "Case Price",
						);
// column attributes
$colattr	= array (
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"product_id" => "12%",
						"itemtitle" => "30%",
						"price" => "10%",
						"sale_price" => "10%",
						"case_price" => "10%",
						);
$sortcols 	= array (	"product_id" => "Product ID",
						"itemtitle" => "Name",
						"price" => "Reg. Price",
						"sale_price" => "Sale Price",
						"case_price" => "Case Price",
						);
$searchcols = array (   "product_id" => "Product ID",
						"itemtitle" => "Name",
						"price" => "Reg. Price",
						"sale_price" => "Sale Price",
						"case_price" => "Case Price",
						"activated" => "Activated",
						"deleted" => "Deleted",
						"draft" => "Draft"
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( index1 => array(but1, but2...), index2 => array(but1, but2...) ... )
// aliased button labels in the form DEF_ACTION_LABEL."::alias"
$buttons 	= array (	DEF_ACTION_EDIT, DEF_ACTION_DELETE, DEF_ACTION_UNDELETE, DEF_ACTION_ACTIVATE, DEF_ACTION_DEACTIVATE );

startPageForm('list_form');
showSearch();
showPagination($rowcount, DEF_PAGEBUT_ADDNEW + DEF_PAGEBUT_GOBACK + DEF_PAGEBUT_ORGANIZER);
showList($db->table, array("cat_id" => $cat_id), $recset, "", "", ALLOW_SORT);
showPagination($rowcount);
showOrganizePanel($db->table, "itemtitle", "rank", "image", "cat_id = $cat_id", "");
endPageForm();
showFooter();
?>