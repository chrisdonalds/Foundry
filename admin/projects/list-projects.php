<?php

//  ------------------------------------------------------------------------------------
//  LIST PAGES
//  ------------------------------------------------------------------------------------
$incl = "";
include ("../loader.php");

$db->table = DB_TABLE_PREFIX."projects";

showHeader();
startContentArea();

// build search query
$_page->prepSearch(array(
		"sort_by" => "lineage",
		"sort_dir" => "ASC",
		"search_list" => array("p.name")
));

// build query
//$_page->where_clause .= $_page->concat." ".((userIsAtleast(ADMLEVEL_DEVELOPER)) ? "" : "p.displayed = 1 AND ")."p.pagetypeid > 0";
$rowcount = getRecNumRows($db->table." p", "*", $_page->where_clause);
$db->savenextquery();
$recset = getRecJoin($db->table." p", $db->table." pp", "p.*, pp.name as parent", "p.cat_id = pp.id", "LEFT JOIN", $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit);

$_page->ingroup = "projects";
$_page->subject = "project";
$_page->titlefld = "name";
showPageTitle("List of Projects");

// col names
$cols 		= array (	"_chk" => "",
						"name" => "Project",
                        "parent" => "Parent",
						"published"	=> "Status"
					);
// column attributes
$colattr	= array (	"name" => "attr:indent; padstr:'&nbsp\;&nbsp\;&nbsp\;&nbsp\;'; checkfield:lineage; checkval:,",
						"published" => "attr:boolean; trueval:Published; falseval:Draft"
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"name" => "15%",
                        "parent" => "15%",
                    	"published" => "8%"
					);
$sortcols 	= array (	"name" => "Project",
                        "lineage" => "Hierarchy",
						"published" => "Status"
					);
$searchcols = array (	"p.name" => "Project",
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( index1 => array(but1, but2...), index2 => array(but1, but2...) ... )
// aliased button labels in the form DEF_ACTION_LABEL."::alias"
$buttons 	= array (DEF_ACTION_EDIT, DEF_ACTION_PUBLISH, DEF_ACTION_UNPUBLISH, DEF_ACTION_DELETE, DEF_ACTION_UNDELETE);

startPageForm('list_form');
showSearch();
showPagination($rowcount, DEF_PAGEBUT_ADDNEW);
showList($db->table, array("cat_id" => ""), $recset, "", "", ALLOW_SORT);
showPagination($rowcount);
endPageForm();
showFooter();
?>