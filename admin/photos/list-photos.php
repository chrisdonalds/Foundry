<?php

//  ------------------------------------------------------------------------------------
//  LIST PHOTOS
//  ------------------------------------------------------------------------------------

$incl = "";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."photos";
$_page->cat_id = intval(getRequestVar('cat_id'));

startContentArea();

// custom processing of POST data
//queueFunction('test', 'listaction', array('is_gallery' => true, 'allow_std_process' => true));
function test ($action, $id, $is_gallery){
	return "The parameter was '$action' for rec #$id. Is_gallery = ".(($is_gallery) ? 'true' : 'false');
}

// process POST DATA

if(getRequestVar('cmd') != '' && $cmd = getRequestVar('cmd')){
    switch($cmd) {
    	case DEF_ACTION_DELETE:
			if(FULL_DELETE) {
				$imgrec = getRec($db->table, "image, thumb", "id = '{$_page->row_id}'", "1", "");
				deleteImage(SITE_PATH.$imgrec[0]['image'], SITE_PATH.$imgrec[0]['thumb']);
				$retn = deleteRec($db->table, "id = '{$_page->row_id}'");
				//update xml file
				$gallarray = array(	"table" => DB_TABLE_PREFIX."photos_cat as pg",
									"flds" => "*",
									"crit" => "published = 1 and archived = 0",
									"order" => "rank",
									"limit" => "");
				$photoarray = array("table" => $db->table." as p",
									"flds" => "*",
									"crit" => "published = 1 and archived = 0",
									"order" => "rank",
									"limit" => "");
				$gflds = array("id" => "id", "code" => "code", "title" => "name", "descr" => "description", "thumb" => "(SELECT thumb FROM ".$db->table." WHERE gallery_def = 1 AND cat_id = pg.id) AS thumb");
				$pflds = array("file" => "image", "title" => "title", "caption" => "title");
				updateGalleryXML("images-new.xml", $gallarray, $photoarray, "cat_id=id", IMG_UPLOAD_FOLDER.$db->table."/", THM_UPLOAD_FOLDER.$db->table."/", $gflds, $pflds);
			} else {
				$retn = updateRec($db->table, "deleted = 1", "id = '{$_page->row_id}'");
			}
    		break;
    	case DEF_ACTION_UNPUBLISH:
			$retn = updateRec($db->table, "published = 0", "id = '{$_page->row_id}'");
			$rec = getRec($db->table, "cat_id, gallery_def", "id = '{$_page->row_id}'");
			if($rec[0]['gallery_def'] == 1){
				// this photo was the gallery default, unset all in gallery and set the first published one
				updateRec($db->table, "gallery_def = 0", "cat_id = '{$rec[0]['cat_id']}'");	// clear all defaults
				updateRec($db->table, "gallery_def = 1", "cat_id = '{$rec[0]['cat_id']}' AND published = 1 LIMIT 1");
			}
    		break;
    	default:
    		break;
    }
}

// build search query
$_page->prepSearch(array(
		"sort_by" => "gallery, rank",
		"sort_dir" => "ASC",
		"search_list" => array("title", "pg.name")
));

// build query
$_page->where_clause .= $_page->concat." p.cat_id = {$_page->cat_id}";
$rowcount = getRecJoinNumRows($db->table." AS p", DB_TABLE_PREFIX."photos_cat AS pg", "p.*, pg.name as gallery", "p.cat_id = pg.id", "LEFT JOIN", $_page->where_clause);
$db->savenextquery();
$recset = getRecJoin($db->table." AS p", DB_TABLE_PREFIX."photos_cat AS pg", "p.*, IFNULL(pg.name, '[Unattached]') as gallery", "p.cat_id = pg.id", "LEFT JOIN", $_page->where_clause, $_page->sort_by." ".$_page->sort_dir, $_page->offset.", ".$_page->limit);

$_page->ingroup = "photos";
$_page->subject = "photo";
$_page->titlefld = "title";
showPageTitle("List of Images in the '".getRecItem(DB_TABLE_PREFIX."photos_cat", "IFNULL(name, 'Unattached') as name", "id={$_page->cat_id}")."' Gallery");

// col names
$cols 		= array (	"_chk" => "",
						"gallery" => "Gallery",
						"title" => "Image",
						"published" => "Status"
					);
// column attributes
$colattr	= array (	"title" => "attr:image",
						"published" => "attr:boolean; trueval:Published; falseval:Draft"
					);
// adding a pipe (|) and number after size will limit cell contents to that many characters
$colsize 	= array (	"gallery" => "15%",
						"title" => "20%",
						"published" => "8%"
					);
$sortcols 	= array (	"title" => "Image",
						"rank" => "Rank"
					);
$searchcols = array (	"title" => "Image Title",
						"published" => "Published",
						"unpublished" => "Un-Published",
						"draft" => "Draft"
					);
// buttons may be further limited by "ALLOW_" constants in config.php
// single-dimensional button array: $buttons = array ( but1, but2, ... )
// multiple-dimensional button array: $buttons = array ( index1 => array(but1, but2...), index2 => array(but1, but2...) ... )
// aliased button labels in the form DEF_ACTION_LABEL."::alias"
$buttons = array    (	DEF_ACTION_EDIT, DEF_ACTION_PUBLISH, DEF_ACTION_UNPUBLISH, DEF_ACTION_DEFAULT."::Gallery Image", DEF_ACTION_DELETE, DEF_ACTION_UNDELETE );
startPageForm('list_form');
showSearch();
showPagination($rowcount, DEF_PAGEBUT_ADDNEW + DEF_PAGEBUT_GOBACK + DEF_PAGEBUT_ORGANIZER, "", $_page->cat_id);
showList($db->table, array("cat_id" => $_page->cat_id), $recset, "", "", ALLOW_SORT);
showPagination($rowcount);
endPageForm();
showFooter();
?>