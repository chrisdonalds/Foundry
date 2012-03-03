<?php

//  ------------------------------------------------------------------------------------
//  LIST
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."listings";
$member_id = getRequestVar('member_id');
$member = getRecItem(DB_TABLE_PREFIX."members", "company", "id='$member_id'");

startContentArea();

// build search query
$_page->prepSearch(array(
		"sort_by" => "itemtitle",
		"sort_dir" => "ASC",
		"search_list" => array("d.itemtitle", "d.address", "c.city", "d.email")
));

// build query
if(USE_SECTIONS) $_page->where_clause .= $_page->concat." sectionid = ".$_page->sectionid;
$_page->where_clause .= $_page->concat." member_id = '$member_id'";
$rowcount = getRecNumRows($db->table." as d", "d.*", $_page->where_clause);
$db->savenextquery();
$recset = getRecArrayJoin(array($db->table." as d", DB_TABLE_PREFIX."cities as c", DB_TABLE_PREFIX."categories p"), array("d.*", "c.name as city", "p.name as category"), array("", "d.city_id=c.id", "d.cat_id=p.id"), array("", "LEFT JOIN", "LEFT JOIN"), $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit, "");

$_page->ingroup = "listings";
$_page->subject = "listing";
$_page->parentgroup = "members";
showPageTitle("List of ".strtoupper($member)."'s Listings");

// col names
$cols 		= array (	"_chk" => "",
						"itemtitle" => "Title",
						"category" => "Category",
						"city" => "Community",
						"hits" => "Views",
						"published" => "Status"
					);
// column attributes
$colattr	= array (	"published" => "attr:boolean; trueval:Published; falseval:Draft"
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"itemtitle" => "25%",
						"category" => "25%",
						"city" => "20%",
						"hits" => "10%",
						"published" => "8%"
					);
$sortcols 	= array (	"itemtitle" => "Title",
						"category" => "Category",
						"city" => "Community",
						"hits" => "Views",
						"published" => "Status"
					);
$searchcols = array (	"d.itemtitle" => "Company",
						"p.category" => "category",
						"c.city" => "City",
						"d.hits" => "Views",
						"d.published" => "Published",
						"deleted" => "Deleted"
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( array (but1cond1, but1cond2...), array (but2cond1, but2cond2...) ... )
$buttons 	= array (	DEF_ACTION_EDIT, DEF_ACTION_DELETE, DEF_ACTION_UNDELETE, DEF_ACTION_PUBLISH, DEF_ACTION_UNPUBLISH );

startPageForm('list_form');
showSearch();
showPagination($rowcount, DEF_PAGEBUT_ADDNEW + DEF_PAGEBUT_GOBACK);
showList($db->table, array("member_id" => $member_id), $recset, "", "", ALLOW_SORT);
showPagination($rowcount);
endPageForm();
showFooter();
?>