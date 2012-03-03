<?php

//  ------------------------------------------------------------------------------------
//  LIST
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."members";

startContentArea();

// build search query
$_page->prepSearch(array(
		"sort_by" => "company",
		"sort_dir" => "ASC",
		"search_list" => array("d.company", "c.contact", "d.city", "d.email", "affiliate_code")
));

// build query
if(USE_SECTIONS) $_page->where_clause .= $_page->concat." sectionid = ".$_page->sectionid;
$rowcount = getRecNumRows($db->table." as d", "d.*", $where_clause);
$db->savenextquery();
$recset = getRecJoin($db->table." as d", DB_TABLE_PREFIX."affiliates as a", "d.*, concat(d.contact, '<br/>', d.day_phone, '<br/>', d.email) as contactinfo, a.affiliate_code", "d.affiliate_id=a.id", "LEFT JOIN", $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit, "");

$_page->ingroup = "members";
$_page->subject = "member";
$_page->childsubject = "listings";
$_page->titlefld = "company";
showPageTitle("List of Members");

// col names
$cols 		= array (	"_chk" => "",
						"company" => "Company",
						"affiliate_code" => "Promo Code",
						"status" => "Status",
					);
// column attributes
$colattr	= array (	"contactinfo" => "hover"
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"company" => "20%",
						"affiliate_code" => "15%",
						"status" => "10%",
					);
$sortcols 	= array (	"company" => "Company",
						"affiliate_code" => "Promo Code",
						"status" => "Status",
					);
$searchcols = array (	"d.company" => "Company",
						"affiliate_code" => "Promo Code",
						"status" => "Status",
						"d.email" => "Email",
						"d.city" => "City",
						"d.activated" => "Activated",
						"deleted" => "Deleted"
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( array (but1cond1, but1cond2...), array (but2cond1, but2cond2...) ... )
$buttons 	= array (	DEF_ACTION_EDIT, DEF_ACTION_VIEWRECS."::Listings|listings", DEF_ACTION_VIEWRECS."::Account|account", DEF_ACTION_DELETE, DEF_ACTION_UNDELETE, DEF_ACTION_ACTIVATE, DEF_ACTION_DEACTIVATE );

startPageForm('list_form');
showSearch();
showPagination($rowcount, DEF_PAGEBUT_ADDNEW);
showList($db->table, "", $recset, "", "", ALLOW_SORT);
showPagination($rowcount);
endPageForm();
showFooter();
?>