<?php
// XML SITEMAP GENERATOR - DBFILE.INC file
//
// Author: Chris Donalds <navtemplate@navigatormm.com>
// Date: July 1, 2010
// Version: 3.0
// License: GPL
// ----------------------------------------------
// REQUIRES: xmlsitemapgen.php

// -------------------------
// ---- EDIT BELOW HERE ----
// -------------------------

if($ver != 3.4) die("DB File Processor for Sitemapgen: version mismatch! Calling file version: $ver");

if(defined('SYS_NAME') && SYS_NAME == 'Foundry'){
	define('TEMPLATE_TYPE', 'foundry');			// uncomment if using Foundry Template (preferred)
}else{
	//define('TEMPLATE_TYPE', 'tiilib');		// uncomment if using Yasar's (Tii::$DB->) CMS
	//define('TEMPLATE_TYPE', 'dbclass');		// uncomment if using custom DB-Class ($db->) CMS
	//define('TEMPLATE_TYPE', '');				// uncomment if using generic CMS
}

//define('CAT_TYPE', 'sections');			// uncomment if using section-based category groups
define('CAT_TYPE', 'freeset');				// uncomment if using your own category group setup

// category arrays
// "class" => array("table" => cat_table_name, "titlefld" => title_field, "catfld" => category_index_field, "crit" => search_criteria, "codefld" => codified_title_field, "sortby" => "sorting", "pageurl" => "URL file");
if(CAT_TYPE == "sections"){
	$catset  = array(
						"site map" => array("table" => "sections",
											"titlefld" => "alias",
											"catfld" => "id",
											"crit" => "display=1",
											"codefld" => "code",
											"sortby" => "id",
											"pageurl" => "code"),
					);
}elseif(CAT_TYPE == "freeset"){
	$catset	 = array(
						"Cat 1" => "Category 1",
						"Cat 2" => "Category 2"
					);
}

// data arrays
// "class" => array("table" => data_table_name, "titlefld" => title_field, "catfld" => category_index_field, "itemfld" => item_index_field, "crit" => search_criteria, "sortby" => "sorting", "pageurl" => "URL file");
// COMMA-SEPARATE MULTIPLE TABLES
$dataset = array(
					"Cat 1" => array("table" => "pages",
										"titlefld" => "pagetitle",
										"catfld" => "sectionid",
										"itemfld" => "id",
										"crit" => "ppage_id=(select id from pages where pagename='page2')",
										"sortby" => "pagetitle",
										"pageurl" => "pagename",
										"destpage" => "{$http}/folder/page/{pageurl}.php",
										"addafter" => "index.php"),
					"Cat 2" => array("table" => DB_TABLE_PREFIX."whatsnew",
										"titlefld" => "pagetitle",
										"catfld" => "sectionid",
										"itemfld" => "id",
										"crit" => "published=1",
										"sortby" => "itemtitle",
										"pageurl" => "id",
										"destpage" => "{$http}/folder/page/{pageurl}",
										"addafter" => "whats-new.php"),
				);

$maintable = "sections";
$catpage = $http."/{pageurl}";		// SET TO BLANK STRING IF NOT USING CATEGORIES
$data_select_type = "select";		// options: select (single dataset table), union (multiple tables)

// --------------------------------
// ---- DO NOT EDIT BELOW HERE ----
// --------------------------------
if($verbose == 1) print "STARTING DATABASE PROCESSING...<br><br>";
if(TEMPLATE_TYPE == "foundry"){
	if(!defined("CODE_VER") && !defined("VALID_LOAD")){
		die("Foundry not initiated or SiteMapGen not properly registered.");
	}
}

function getDBData(){
	global $http, $dir, $catpage, $verbose, $catset, $dataset, $datalist, $uses_cd_template, $maintable, $data_select_type;

	$rtn = array();
	if(substr(strtolower($catpage), 0, 4) != "http" && $catpage != "") $catpage = "http://".$catpage;

	//debugger();
	foreach ($catset as $catkey => $thiscat){
		#if($verbose == 1) print "<b>".strtoupper($catkey)."</b><br>";
		$datalist[]['page'] = $catkey;
		if(is_array($thiscat)){
			if($thiscat['table'] != ""){
				if(TEMPLATE_TYPE == "navtemplate"){
					$catrec = getRec($thiscat['table'], $thiscat['titlefld']." AS titledat, ".$thiscat['catfld']." AS catdat, ".$thiscat['codefld']." AS codedat, ".$thiscat['pageurl']." AS pagedat", $thiscat['crit'], $thiscat['sortby'], "");
					$pageid = getRecItem($maintable, "id", "pagename = '$catkey'");
				}elseif(TEMPLATE_TYPE == "tiilib" || TEMPLATE_TYPE == "dbclass"){
					$catrec = getRec($thiscat['table'], $thiscat['titlefld']." AS titledat, ".$thiscat['catfld']." AS catdat, ".$thiscat['codefld']." AS codedat, ".$thiscat['pageurl']." AS pagedat", $thiscat['crit'], $thiscat['sortby'], "");
				}else{
					$catrec = array();
					$sql = "SELECT ".$thiscat['titlefld']." AS titledat";
					if($thiscat['catfld'] != "") $sql .= ", ".$thiscat['catfld']." AS catdat";
					if($thiscat['codefld'] != "") $sql .= ", ".$thiscat['codefld']." AS codedat";
					if($thiscat['pageurl'] != "") $sql .= ", ".$thiscat['pageurl']." AS pagedat";
					$sql.= " FROM ".$thiscat['table'];
					if($thiscat['crit'] != "") $sql.= " WHERE ".$thiscat['crit'];
					$sql.= " ORDER BY ".$thiscat['sortby'];
					$rs = mysql_query($sql);
					while ($row = mysql_fetch_array($rs)) $catrec[] = $row;
				}
			}else{
				$catrec = array("");
			}
		}else{
			// empty cat or cat not used
			$catrec = array("");
		}

		if(count($catrec) > 0){
			foreach($catrec as $cat){
				// output category
				$title = str_replace(" ", "-", $thiscat['titlefld']);
				$title = str_replace("#", "", $title);
				if($catpage != "") {
					if($cat['titledat'] != ''){
						// cat array provided
						$thispage = str_replace("{codefld}", $cat['codedat'], $catpage);
						$thispage = str_replace("{pageurl}", $cat['pagedat'], $thispage);
						$thispage = str_replace("{titlefld}", $title, $thispage);
						if($verbose == 1) print "<b><a href=\"".$thispage."\">".$cat['titledat']."</a> (".$cat['pagedat'].")</b><br>";
						$datalist[] = array("url" => $thispage, "cat" => $cat['titledat']);
					}
				}else{
					// no cat array provided
					$thispage = "";
					if($verbose == 1) print "<b>".$catkey."</b><br>";
					$datalist[] = array("url" => $thispage, "cat" => $catkey);
				}
				$thisdata = $dataset[$catkey];

				if($thisdata['catfld'] != "" && $cat['catdat'] != ""){
					// use cat data to construct crit
					$crit = $thisdata['catfld']."=".$cat['catdat'];
					if($thisdata['crit'] != "") $crit .= " AND ".$thisdata['crit'];
				}else{
					$crit = $thisdata['crit'];
				}

				if(TEMPLATE_TYPE == "navtemplate"){
					if($data_select_type == "union"){
						// we need to build a set of arrays to pass to getRecUnion
						$fields = array();
						$crits = array();
						$tables = explode(", ", $thisdata['table']);
						$titleflds = explode(", ", $thisdata['titlefld']);
						$itemflds = explode(", ", $thisdata['itemfld']);
						$catflds = explode(", ", $thisdata['catfld']);
						$pageflds = explode(", ", $thisdata['pageurl']);
						$critflds = explode(", ", $thisdata['crit']);
						$destpages = explode(", ", $thisdata['destpage']);
						foreach($tables as $key=>$table){
							$fields[] = $itemflds[$key]." as itemdat, ".$titleflds[$key]." as titledat, ".$catflds[$key]." as catfld, ".$pageflds[$key]." as pagedat, '".$destpages[$key]."' as destpage";
							$crits[] = $catflds[$key]."=".$cat['catdat']." AND ".$critflds[$key];
						}
						$sortflds = explode(", ", $thisdata['sortby']);

						$datarec = getRecUnion($tables, $fields, $crits, $sortflds, "");
					}else{
						($thisdata['itemfld'] != "") ? $itemfld = $thisdata['itemfld']." AS itemdat, " : $itemfld = "";
						($thisdata['titlefld'] != "") ? $titlefld = $thisdata['titlefld']." AS titledat, " : $titlefld = "";
						($thisdata['catfld'] != "") ? $catfld = $thisdata['catfld']." AS catdat, " : $catfld = "";
						($thisdata['pageurl'] != "") ? $pagefld = $thisdata['pageurl']." AS pagedat" : $pagefld = "";
						$datarec = getRec($thisdata['table'], $itemfld.$titlefld.$catfld.$pagefld, $crit, $thisdata['sortby'], "");
						($pageid > 0) ? $destpage = $thisdata['destpage'].$pageid."/" : $destpage = $thisdata['destpage'];
					}
				}elseif(TEMPLATE_TYPE == "tiilib" || TEMPLATE_TYPE == "dbclass"){
					$datarec = getRec($thisdata['table'], $thisdata['itemfld']." AS itemdat, ".$thisdata['titlefld']." AS titledat, ".$thisdata['catfld']." AS catdat, ".$thisdata['pageurl']." AS pagedat", $crit, $thisdata['sortby'], "");
					$destpage = $thisdata['destpage'];
				}else{
					$datarec = array();
					$sql = "SELECT ".$thisdata['titlefld']." AS titledat, ".$thisdata['itemfld']." AS itemdat";
					if($thisdata['catfld'] != "") $sql .= ", ".$thisdata['catfld']." AS catdat";
					if($thisdata['pageurl'] != "") $sql .= ", ".$thisdata['pageurl']." AS pagedat";
					$sql.= " FROM ".$thisdata['table'];
					if($crit != "") $sql.= " WHERE ".$crit;
					$sql.= " ORDER BY ".$thisdata['sortby'];
					$rs = mysql_query($sql);
					while ($row = mysql_fetch_array($rs)) $datarec[] = $row;
					$destpage = $thisdata['destpage'];
				}

				// output data
				if($data_select_type != "union")
					if(substr(strtolower($destpage), 0, 4) != "http") $destpage = "http://".$destpage;

				foreach($datarec as $data){
					// scan through items
					if($data_select_type == "union"){
						$destpage = $data['destpage'];
						if(substr(strtolower($destpage), 0, 4) != "http") $destpage = "http://".$destpage;
					}
					$title = str_replace(" ", "-", $data['titledat']);
					$title = str_replace("#", "", $title);
					$thispage = str_replace("{itemfld}", $data['itemdat'], $destpage);
					$thispage = str_replace("{codefld}", $data['codedat'], $thispage);
					$thispage = str_replace("{pageurl}", htmlspecialchars($data['pagedat']), $thispage);
					$thispage = str_replace("{catcode}", $cat['codedat'], $thispage);
					$thispage = str_replace("{titlefld}", $title, $thispage);
					$thispage = str_replace(" ", "-", $thispage);
					if($verbose == 1) print "...<a href=\"".$thispage."\">".$data['titledat']."</a> ".(($thisdata['addafter'] != '') ? "(after ".$thisdata['addafter'].")" : '')."<br>";
					$datalist[] = array("url" => $thispage, "item" => $data['titledat'], "addafter" => $thisdata['addafter']);
				}
			}
		}
		if($verbose == 1) print "<br>\n";
	}
	if($verbose == 1) print "<hr>\n";
}
?>
