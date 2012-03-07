<?php
// ---------------------------
//
// FOUNDRY COMMON FUNCTIONS
// - Front End Form & Page
//
// ---------------------------
//
// list of tool functions
// - Uses: MySQL 4.2+

define ("FORMLOADED", true);

if(!defined("VALID_LOAD")) die("This file cannot be accessed directly!");

// instantiate classes
include (SITE_PATH.ADMIN_FOLDER.LIB_FOLDER."front.class.php");
$_page  = PageClass::init();
$_data  = DataClass::init();
$_rss   = RPCClass::init();

define('FRM_ERR_NODATA', 'Dataset empty');
define('FRM_ERR_PATHNOTFOUND', 'URI path not found.');

// ----------- PREPARATION FUNCTIONS ---------------

/**
 * Core: Start of the Page Alias mechanism.
 * Prepares the $_page object with database page data.
 * @param string $as404
 * @return boolean
 * @todo hook here
 */
function setupPage($as404 = false){
	global $_page;

	// Search for either a dynamically-created database page or 404 (page not found) page
    $_page->found = false;
    if(!$as404){
        if($_page->name != "") {
            // first, search for a database page represented by the alias
            $dbrec = getRec("pages", "*", "pagename = '".$_page->name."' OR pagealias = '".$_page->name."'", "", "1", "", true);
            if(is_array($dbrec) && isset($dbrec['id'])){
                // page data found
                $_page->id = $dbrec['id'];
                $_page->code = $dbrec['pagename'];
                $_page->alias = $_page->name;
                $_page->title = getIfSet($dbrec['pagetitle']);
                $_page->parenttitle = ((intval($dbrec['ppage_id']) > 0) ? getRecItem("pages", "pagetitle", "id = '".$dbrec['ppage_id']) : '');
                $_page->metatitle = getIfSet($dbrec['metatitle']);
                $_page->metadescr = getIfSet($dbrec['metadescr']);
                $_page->metakeywords = getIfSet($dbrec['metakeywords']);
                $_page->content = getRecItem("editor_userpages", "content", "pageid = ".$_page->id);
                $_page->dbrec = $dbrec;
                $_page->contenttype = 'page';
                $_page->ishomepage = ($_page->name == "index");
                $_page->islocked = ($dbrec['locked'] == 1);
                $_page->issearchable = ($dbrec['searchable'] == 1);
                $_page->isprotected = ($dbrec['protected'] == 1);
                $_page->ispublished = ($dbrec['published'] == 1);
                $_page->isdraft = ($dbrec['draft'] == 1);
                $_page->created = $dbrec['date_created'];
                $_page->updated = $dbrec['date_updated'];
                $_page->published = $dbrec['date_published'];
                $_page->error = '';
                $_page->found = true;
            }
        }
    }else{
        // search for the database 404 page data
        $dbrec = getRec("pages", "*", "pagename = '404'", "", "1", "", true);
        if(is_array($dbrec) && isset($dbrec['id'])){
            // database 404 page data found
            $_page->id = $dbrec['id'];
            $_page->code = $dbrec['pagename'];
            $_page->alias = $_page->name;
            $_page->title = $dbrec['pagetitle'];
            $_page->parenttitle = "";
            $_page->metatitle = "";
            $_page->metadescr = "";
            $_page->metakeywords = "";
            $_page->content = getRecItem("editor_userpages", "content", "pageid = ".$_page->id);
            $_page->dbrec = $dbrec;
            $_page->contenttype = 'page';
            $_page->ishomepage = false;
            $_page->islocked = ($dbrec['locked'] == 1);
            $_page->issearchable = ($dbrec['searchable'] == 1);
            $_page->isprotected = ($dbrec['protected'] == 1);
            $_page->ispublished = ($dbrec['published'] == 1);
            $_page->isdraft = ($dbrec['draft'] == 1);
            $_page->created = $dbrec['date_created'];
            $_page->updated = $dbrec['date_updated'];
            $_page->published = $dbrec['date_published'];
            $_page->error = "";
            $_page->found = true;
        }
    }
    return $_page->found;
}

/**
 * Core: Start of the Page Alias mechanism.
 * Prepares the $_page object with directly loaded file data.
 * @param string $filename
 * @return boolean
 * @todo hook here
 */
function setupDirectPage($filename){
	global $_page;

    $_page->id = 0;
    $_page->code = '';
    $_page->name = $filename;
    $_page->alias = basename($filename);
    $_page->title = basename($filename, ".php");
    $_page->parenttitle = '';
    $_page->metatitle = '';
    $_page->metadescr = '';
    $_page->metakeywords = '';
    $_page->content = '';
    $_page->dbrec = array();
    $_page->contenttype = 'directpage';
    $_page->ishomepage = ($_page->name == "index.php");
    $_page->islocked = false;
    $_page->issearchable = false;
    $_page->isprotected = false;
    $_page->ispublished = false;
    $_page->isdraft = false;
    $_page->created = date(PHP_DATE_FORMAT, filectime(SITE_PATH.$filename));
    $_page->updated = BLANK_DATE;
    $_page->published = BLANK_DATE;
    $_page->error = '';
    $_page->found = true;
}

/**
 * Core: Start of the Data Alias mechanism.
 * Prepares the $_data object with record data.
 * @return boolean
 * @todo hook here
 */
function setupPageData(){
	global $_page, $_data, $_system;

    $_data->found = false;
    $err = '';
	if($_page->name != "") {
        // first, break URI at / to get the table segment
        $uri = ltrim(strtolower($_page->name), "/");
        $url_parts = explode("/", $uri);
        //printr($url_parts);

        // now, see if there is a data alias prepared for this table
        $dataaliases = $_system->dataaliases;
        if(isset($dataaliases[$url_parts[0]])){
            // ... next, try to match the uri with the regex pattern
            $dataalias = $dataaliases[$url_parts[0]];
            $pattern = ltrim($dataalias['pattern'], "/");
            $pattern = str_replace("~", "\/", str_replace("/", "\/", str_replace("\/", "~", $pattern)));
            if(preg_match("/".$pattern."/i", strtolower($_page->name), $url_matches)){
                // ... and then, populate the qvars array with the elements
                $qvars = array();
                foreach($dataalias['parameters'] as $type => $index){
                    switch($type){
                        case "d":
                        case "ad":
                        case "sd":
                            $qvars[$type] = preg_replace('/\$([0-9]+)/e', '$url_parts["$1"]', $index);
                            break;
                        case "t":
                        case "at":
                            $qvars[$type] = preg_replace('/\$([0-9]+)/e', '$url_parts["$1"]', $index);;
                            break;
                        default:
                            $qvars[$type] = getIfSet($url_matches[substr($index, 1)]);
                            break;
                    }
                }

                // using qvars, et al, create the sql query to grab the data
                $crit = "";
                $table = DB_TABLE_PREFIX.$dataalias['db_table'];
                foreach($qvars as $type => $elem){
                    switch($type){
                        case "c":
                            $crit .= (($crit != "") ? " AND " : "")."t.`code` = '$elem'";
                            break;
                        case "id":
                            $crit .= (($crit != "") ? " AND " : "")."t.`id` = '$elem'";
                            break;
                        case "cc":
                            if(isset($qvars['c'])){
                                // parent-child
                                $crit .= (($crit != "") ? " AND " : "")."t.`cat_id` IN (SELECT `id` FROM `".DB_TABLE_PREFIX.$dataalias['db_table']."_cat` WHERE `code` = '$elem')";
                            }else{
                                // parent only
                                $crit .= (($crit != "") ? " AND " : "")."t.`code` = '$elem'";
                            }
                            break;
                        case "mc":
                            $elems = explode("/", $elem);
                            if(isset($qvars['c'])) array_push ($elems, $qvars['c']);
                            list($ancestors, $highest_parent, $treetop_reached) = getRecAncestors($table, 0, $elems);

                            if($treetop_reached){
                                if(isset($qvars['c'])){
                                    // parent-child
                                    $crit .= (($crit != "") ? " AND " : "")."t.`cat_id` IN (SELECT `id` FROM `".DB_TABLE_PREFIX.$dataalias['db_table']."_cat` WHERE `code` = '$elem')";
                                }else{
                                    // parent only
                                    $crit .= (($crit != "") ? " AND " : "")."t.`code` = '".$elems[count($elems) - 1]."'";
                                }
                            }else{
                                $err = FRM_ERR_PATHNOTFOUND;
                            }
                            break;
                        case "d":
                            $crit .= (($crit != "") ? " AND " : "")."DATE(t.`date_published`) LIKE '%$elem%'";
                            break;
                        case "t":
                            $crit .= (($crit != "") ? " AND " : "")."TIME(t.`date_published`) LIKE '%$elem%'";
                            break;
                        case "ad":
                            $crit .= (($crit != "") ? " AND " : "")."DATE(t.`date_activated`) LIKE '%$elem%'";
                            break;
                        case "at":
                            $crit .= (($crit != "") ? " AND " : "")."TIME(t.`date_activated`) LIKE '%$elem%'";
                            break;
                        case "sd":
                            $crit .= (($crit != "") ? " AND " : "")."DATE(t.`start_date`) LIKE '%$elem%'";
                            break;
                    }
                }

                if($err == ""){
                    $dbrec = getRec("`".$table."` t", "*", $crit, "", "", "", true);
                    if(count($dbrec) > 0){
                        $_data->id = $dbrec['id'];
                        $_data->table = $table;
                        $_data->metabase = $url_parts[0];
                        $_data->dbrec = $dbrec;
                        $_data->queryvars = $qvars;
                        $_data->iscategory = $dataalias['iscategory'];
                        $_data->pattern = $dataalias['pattern'];
                        $_data->query = "SELECT * FROM `$table` t WHERE $crit";
                        $_data->error = "";
                        $_data->found = true;
                        $_data->numrows = count($dbrec);

                        $possible_fields = array('name', 'itemtitle', 'title', 'code');
                        $_page->title = ucwords(getFirstMatch($dbrec, $possible_fields));
                        if(!$_data->iscategory || getIntValIfSet($dbrec['cat_id']) > 0){
                            // get the title of the parent cat (self-cat or parent of child)
                            $cattable = $table.((!$_data->iscategory) ? "_cat" : "");
                            $catrec = getRec($cattable, "*", "id = '".getIntValIfSet($dbrec['cat_id'])."'", "", "", "", true);
                            $_page->parenttitle = getFirstMatch($catrec, $possible_fields);
                        }else{
                            // a category dataset with no parent
                            $_page->parenttitle = $_data->metabase;
                        }
                        $_page->contenttype = 'data';
                    }else{
                        $err = FRM_ERR_NODATA;
                    }
                }
                if($err != ""){
                    $_data->table = "";
                    $_data->metabase = "";
                    $_data->dbrec = array();
                    $_data->queryvars = null;
                    $_data->iscategory = false;
                    $_data->pattern = $dataalias['pattern'];
                    $_data->query = null;
                    $_data->error = $err;
                    $_data->found = false;
                    $_data->numrows = 0;

                    $_page->title = "";
                    $_page->contenttype = "";
                }
                $_page->found = $_data->found;
            }
        }
    }
    return $_data->found;
}

/**
 * Core: Start of the Controller mechanism.
 * Prepares the $_data object with controller class data.
 * @return boolean
 * @todo hook here
 */
function setupPageController(){

}

/**
 * Core: Start of the Data Alias mechanism.
 * Prepares the $_data object with record data.
 * @return boolean
 * @todo hook here
 */
function setupRSSData(){
	global $_page, $_rss, $_system;

    $_rss->found = false;
    $err = '';
	if($_page->name != "") {
        // first, break URI at / to get the table segment
        $uri = ltrim(strtolower($_page->name), "/");
        $url_parts = explode("/", $uri);
        //printr($url_parts);
    }
    return $_rss->found;
}

// ----------- PAGE FUNCTIONS ---------------

/**
 * Output <head> component (Method 1: automatic)
 * or call header file (Method 2: pseudo-automatic)
 * @todo hook here
 */
function startPage($headerfile = "header.php"){
	global $_page;

    // does the headerfile exist?
    $hf_exists = (@file_exists(SITE_PATH.$headerfile));

    // does the header file already contain </HEAD>?
    $hf_contents = '';
    if($hf_exists) $hf_contents = @file_get_contents(SITE_PATH.$headerfile);
    if(strpos(strtolower($hf_contents), "</head>") !== false){
        // it does, load it.
        // Note: it should contain everything to start the page including body
        // Method 2: pseudo-automatic
        @include(SITE_PATH.$headerfile);
        if(strpos(strtolower($hf_contents), "<body") === false) startBody();
    }else{
        // it doesn't, so continue...
        $bus = BUSINESS;
        $year = date("Y");
        $web_url = WEB_URL;
        $copywrite = COPYRIGHT_NAME;
        $copywrite_web = COPYRIGHT_WEB;
        $metatitle = formatMetaTitle();
        $metakeywd = $_page->metakeywords;
        $metadescr = $_page->metadescr;
        print<<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$metatitle}</title>
<meta name="keywords" content="{$metakeywd}"/>
<meta name="description" content="{$metadescr}" />
<meta name="copyright" content="Copyright {$year} {$bus}. All Rights Reserved" />
<meta name="designer" content="Web design by {$copywrite} ({$copywrite_web})" />
<meta name="author" content="{$copywrite}" />
<meta name="Robots" content="INDEX, FOLLOW" />
<meta name="Revisit-after" content="7 Days" />

EOT;
    prepHeaderPluginsBlock();
        print<<<EOT
<link rel="sitemap" type="application/xml" title="Sitemap" href="{$web_url}sitemap.xml" />
</head>

EOT;
        if(strpos(strtolower($hf_contents), "<body") === false) startBody();
        if($hf_exists) @include(SITE_PATH.$headerfile);
    }
}

/**
 * Prepare meta title
 * @param string $sep Separator string. Defaults to ' | '
 * @todo hook here
 */
function formatMetaTitle($sep = " | "){
	global $_page;

	$metatitle = $_page->metatitle;
	if($metatitle == '') $metatitle = $_page->title;
	$metatitle = SITE_NAME.$sep.$metatitle;
	return $metatitle;
}

/**
 * Output <body> tag
 * @param string $attr
 * @param string $wrapper
 * @todo hook here
 */
function startBody($attr = "", $wrapper = ""){
	global $incl;

	if($attr != '') $attr = " ".$attr;
	$body = "<body{$attr}>";
	if($wrapper != "") $body = str_replace("{BODY}", $body, $wrapper);
	print<<<EOT
{$body}

EOT;
	//prepAdminister();
}

/**
 * Show the pagination section
 * @global <type> $section
 * @global <type> $search_by
 * @global <type> $search_text
 * @global <type> $sort_by
 * @global <type> $sort_dir
 * @global <type> $page
 * @global <type> $offset
 * @global <type> $limit
 * @global <type> $max_num_pages_shown
 * @param <type> $total
 * @param <type> $thispage
 * @param <type> $thispagequery
 * @param <type> $querydelim
 * @return <type>
 * @todo hook here
 */
function showPagination($total, $thispage, $thispagequery = "", $querydelim = "&"){
	global $section, $search_by, $search_text, $sort_by, $sort_dir, $page, $offset, $limit, $max_num_pages_shown;

	$total_count = $total;
	$num_of_pages = 0;
	$display_pages = "";

	if ($total_count > $limit){
		$num_of_pages = ceil($total_count/$limit);

		$prev = "";
		$next = "";
		$count = 0;
		//$thispage = $_SERVER['PHP_SELF'];

		if(isset($_REQUEST['start']) && $_REQUEST['start'] != "")
			$start = $_REQUEST['start'];
		else
			$start = 1;

		//populate page numbers
		if ($max_num_pages_shown != 0){
			for($i=$start; $i<=$num_of_pages; $i++){
				//bold the current page
				if($i == $page){
					$the_page = "<li><span><a href=\"#\" id=\"page_current\">$i</a></span></li>";
					$display_pages .= $the_page." ";
				}else{
					$the_page = "<li><a href=\"".WEB_URL.$thispage;
					if($querydelim == "&"){
						$the_page .= "?";
						if($search_by != "" || $search_text != "" || $sort_by != "" || $sort_dir != "") $the_page .= "search_by=$search_by&search_text=$search_text&sort_by=$sort_by&sort_dir=$sort_dir&";
						$the_page .= "start=$start&page=$i&limit=$limit{$thispagequery}\">$i</a></li>";
					}elseif($querydelim == "/"){
						if(substr($the_page, 0, -1) != "/") $the_page .= "/";
						if($search_by != "" || $search_text != "" || $sort_by != "" || $sort_dir != "") $the_page .= "$search_by/$search_text/$sort_by/$sort_dir/";
						$the_page .= "$start/$i/$limit{$thispagequery}\">$i</a></li>";
					}
					$display_pages .= $the_page." ";
				}

				$count ++;

				if ($count >= $max_num_pages_shown)
					$i = $num_of_pages + 1;  //exit the 'for' loop
			}
		}

		//generate previous button
		if ($page > 1){
			if ($start > ($page - 1)){
				$new_start = $page-$max_num_pages_shown;
			}else{
				$new_start = $start;
			}
			$prev = "<li><a href=\"".WEB_URL.$thispage;
			if($querydelim == "&"){
				$prev .= "?";
				if($search_by != "" || $search_text != "" || $sort_by != "" || $sort_dir != "") $prev .= "search_by=$search_by&search_text=$search_text&sort_by=$sort_by&sort_dir=$sort_dir&";
				$prev .= "start=$new_start&page=".($page-1)."&limit=$limit{$thispagequery}\">Prev</a></li>";
			}elseif($querydelim == "/"){
				if(substr($prev, 0, -1) != "/") $prev .= "/";
				if($search_by != "" || $search_text != "" || $sort_by != "" || $sort_dir != "") $prev .= "$search_by/$search_text/$sort_by/$sort_dir/";
				$prev .= "$new_start/".($page-1)."/$limit{$thispagequery}\">Prev</a></li>";
			}
			$display_pages = $prev."&nbsp;&nbsp;".$display_pages;
		}

		//generate next button
		if ($page != $num_of_pages){
			if ((($page+1) - $start) == $max_num_pages_shown){
				$new_start = $page+1;
			}else{
				$new_start = $start;
			}
			$next = "<li><a href=\"".WEB_URL.$thispage;
			if($querydelim == "&"){
				$next .= "?";
				if($search_by != "" || $search_text != "" || $sort_by != "" || $sort_dir != "") $next .= "search_by=$search_by&search_text=$search_text&sort_by=$sort_by&sort_dir=$sort_dir&";
				$next .= "start=$new_start&page=".($page+1)."&limit=$limit{$thispagequery}\">Next</a></li>";
			}elseif($querydelim == "/"){
				if(substr($next, 0, -1) != "/") $next .= "/";
				if($search_by != "" || $search_text != "" || $sort_by != "" || $sort_dir != "") $next .= "$search_by/$search_text/$sort_by/$sort_dir/";
				$next .= "$new_start/".($page+1)."/$limit{$thispagequery}\">Next</a></li>";
			}
			$display_pages = $display_pages."&nbsp;&nbsp;".$next;
		}
	}

	if($total_count == 0) $offset = -1;

	$lastrow = $offset+$limit;
	if($lastrow > $total_count) $lastrow=$total_count;
	print $display_pages;

	return $display_pages;
}

/**
 * Return page title
 * @return string
 */
function getTitle(){
    global $_page;

    return $_page->title;
}

/**
 * Output page title
 * @param boolean $incl_h1      True to include <H1> tag in output
 * @param boolean $incl_parent  True to include the parent category in output (if present)
 * @todo hook here
 */
function showTitle($incl_h1 = false, $incl_parent = false){
	global $_page;

	if($incl_h1){
		echo "<h1>".(($incl_parent) ? $_page->parenttitle.": " : "").getTitle()."</h1>".PHP_EOL;
	}else{
		echo (($incl_parent) ? $_page->parenttitle.": " : "").getTitle().PHP_EOL;
	}
}

/**
 * Return content of page or data
 * @param integer $id
 * @param string $code
 */
function getContents($id = 0, $code = ''){
    global $_page, $_data;

    $contents = '';
    if($_page->contenttype == 'page'){
        $contents = clean_cke_text($_page->content);
    }elseif($_page->contenttype == 'data'){
        $possible_fields = array('content', 'contents', 'descr', 'desc', 'description');
        foreach($possible_fields as $field){
            if(isset($_data[0][$field])){
                $contents = $_data[0][$field];
            }
        }
    }
    if($contents != ''){
        // parse out any macros ({func name=val, name=val} in the contents
        preg_match_all("/{(([a-z0-9]+)(.*))+}/i", $contents, $macros);
        if(count($macros) > 1){
            array_shift($macros);   // get rid of entire contents
            array_shift($macros);   // get rid of {...}
            $triggercode = getIfSet($macros[0][0]);
            if(!isblank($triggercode)){
                $argstr = trim(getIfSet($macros[1][0]));
                if(!isblank($argstr)){
                    $argstr = str_replace(" ", "", preg_replace("/(,|, )/i", "&", $argstr));
                    parse_str($argstr, $args);
                }else{
                    $args = array();
                }
                $retn = executeMacro($triggercode, $args);
                if(!isblank($retn)){
                    $contents = preg_replace("/{(([a-z0-9]+)(.*))+}/i", $retn, $contents);
                }
            }
        }
    }
    return $contents;
}

/**
 * Output page content
 * @todo hook here
 */
function showContents(){
    echo getContents().PHP_EOL;
}

/**
 * Return brief version of contents
 * @param string $content
 * @param integer $length [optional]
 * @param string $url [optional]
 * @param string $finish [optional]
 */
function getShortContent($content, $length = 20, $url = '', $finish = '...') {
    // Clean and explode our content, Strip all HTML tags, and special charactors.
    $words = explode(' ', strip_tags(preg_replace('/[^(\x20-\x7F)]*/','', $content)));

    // Get a count of all words, and check we have less/more than our required amount of words.
    $count = count($words);
    $limit = ($count > $length) ? $length : $count;

    // if we have more words than we want to show, add our ...
    $end   = ($count > $length && $url != '') ? ' [<a href="'.$url.'">'.$finish.'</a>]' : '';

    // create output
    for($w = 0; $w <= $limit; $w++) {
        $output .= $words[$w];
        if($w < $limit) $output .= ' ';
    }

    // return end result.
    return $output.$end;
}

/**
 * Output brief version of contents
 * @param string $content
 * @param integer $length [optional]
 * @param string $url [optional]
 * @param string $finish [optional]
 * @todo hook here
 */
function showShortContents($content, $length = 20, $url = '', $finish = '...'){
    echo getShortContents($content, $length, $url, $finish).PHP_EOL;
}

/**
 * Output start of contact form block
 * @param string $formname
 * @param string $reqflds
 * @param string $thankyoupage
 * @param string $subject
 * @param string $errorboxtype
 * @todo hook here
 */
function startForm($formname, $id = null, $method = "post", $destpage = "",
				   $reqflds = "name|email", $thankyoupage = "thankyou.php",
				   $subject = "", $errorboxtype = "divbox", $class = null,
				   $hiddenflds = null){
	$self = $_SERVER['PHP_SELF'];
	$time = time();
	if($id != null) $id = " id=\"{$id}\"";
	if($class != null) $class = " class=\"{$class}\"";
	if($method == "") $method = "post";
	if($destpage == "") $destpage = $self;

	print <<<EOT

	<a name="{$formname}"></a>
<form action="{$destpage}" method="{$method}" name="{$formname}"{$id}{$class}>
	<input type="text" name="sec" id="sec" value="" class="fsec" />
	<input type="text" name="time" id="time" value="{$time}" class="fsec" />

EOT;
	if($reqflds != "") echo "<input type=\"hidden\" name=\"required_fields_dyn\" value=\"{$reqflds}\">\n";
	if($thankyoupage != "") echo "\t\t<input type=\"hidden\" name=\"redirect_url\" value=\"{$thankyoupage}\" />\n";
	if($subject != "") echo "\t\t<input type=\"hidden\" name=\"subject\" value=\"{$subject}\" />\n";
	if($errorboxtype != "") echo "\t\t<input type=\"hidden\" name=\"error_output_type\" value=\"{$errorboxtype}\" />\n";
	if(is_array($hiddenflds)) foreach($hiddenflds as $name => $value) echo "\t\t<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\" />\n";
}

/**
 * Outputs form field in template block
 * @param str $name
 * @param str $id
 * @param str $type
 * @param str $label
 * @param type $value
 * @param bool $required
 * @param str $default
 * @param str $options
 * @param str $literal
 * @param int $minlen
 * @param int $maxlen
 * @param int $min
 * @param int $max
 * @todo hook here
 */
function addFormField($name, $id, $type, $label = "", $value = "",
					  $class = null, $required = false, $default = null,
					  $options = null, $literal = null,
					  $minlen = 0, $maxlen = 0, $min = 0, $max = 0){
    global $_page, $_data;

	$reqdlabel = "";
	if($label != '') $label .= ":";
	if($required || $required == "*") { $class = (($class != '') ? " ".$class." " : "")."required"; $reqdlabel = "*"; }
	if($max > $min && $min > 0) list($min, $max) = swap($min, $max);
	if($maxlen > $minlen && $minlen > 0) list($minlen, $maxlen) = swap($minlen, $maxlen);
	($minlen > 0) ? $maxlen = " minlength=\"{$minlen}\"" : $minlen = "";
	($maxlen > 0) ? $maxlen = " maxlength=\"{$maxlen}\"" : $maxlen = "";
	($min > 0) ? $maxlen = " min=\"{$min}\"" : $min = "";
	($max > 0) ? $maxlen = " max=\"{$max}\"" : $max = "";

	switch ($type){
		case "text":
		case "email":
		case "url":
		case "date":
		case "number":
		case "digits":
		case "creditcard":
			if($type != "text") $class .= (($class != "") ? " " : "").$type;
			if($class != "") $class = " class=\"{$class}\"";
			$control = "<input type=\"text\"{$class}{$minlen}{$maxlen}{$min}{$max} name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" />";
			break;
		case "password":
			if($class != "") $class = " class=\"{$class}\"";
			$control = "<input type=\"password\"{$class}{$minlen}{$maxlen} name=\"{$name}\" id=\"{$id}\" value=\"\" />";
			break;
		case "hidden":
			$control = "<input type=\"hidden\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" />";
			break;
		case (substr($type, 0, 8) == "textarea"):
			if($class != "") $class = " class=\"{$class}\"";
			$control = "<textarea name=\"{$name}\" id=\"$id\"{$class}{$minlen}{$maxlen}{$min}{$max}>{$value}</textarea>";
			break;
		case "radio":
			if($class != "") $class = " class=\"{$class}\"";
			($value == $default) ? $checked = " checked=\"checked\"": $checked = "";
			$control = "<input type=\"radio\" name=\"{$name}\" id=\"$id\"{$class}{$checked} value=\"{$default}\" />{$literal}";
			break;
		case "checkbox":
			if($class != "") $class = " class=\"{$class}\"";
			($value == $default) ? $checked = " checked=\"checked\"": $checked = "";
			$control = "<input type=\"checkbox\" name=\"{$name}\" id=\"$id\"{$class}{$checked} value=\"{$default}\" />{$literal}";
			break;
		case "select":
			if($class != "") $class = " class=\"{$class}\"";
			$control = "<select name=\"{$name}\" id=\"$id\"{$class}>\n";
			if(is_array($options)){
				foreach($options as $key=>$option){
					$control .= "<option value=\"{$key}\"".(($key == $value) ? " selected=\"selected\"" : "").">{$option}</option>\n";
				}
			}
			$control .= "</select>";
			break;
		case "file":
			if($class != "") $class = " class=\"{$class}\"";
			$control = "<input type=\"file\" name=\"{$name}\" id=\"$id\"{$class} />";
			break;
		case "submit":
			if($class != "") $class = " class=\"{$class}\"";
			$control = "<input type=\"submit\" value=\"{$value}\" name=\"{$name}\" id=\"$id\"{$class} />";
			break;
		case "button":
			if($class != "") $class = " class=\"{$class}\"";
			$control = "<input type=\"button\"{$class}{$minlen}{$maxlen} name=\"{$name}\" id=\"{$id}\" value=\"{$value}\"{$options} />";
			break;
		case "reqd":
			$control = "* Required Entry";
			break;
		default:
			die("Invalid form field type '{$type}'!");
			break;
	}
	$template = $_page->formTemplate;
	//$template = str_replace(array(self::LABEL, self::REQFLAG, self::FIELD), array($label, $reqdlabel, $control), $template);
	echo $template."\n";
}

/**
 * End a form
 * @todo hook here
 */
function endForm(){
	print <<<EOT
</form>

EOT;
}

/**
 * Output error box
 * @todo hook here
 */
function showErrorBox(){
	global $_page;

	// Formtoemailpro divbox
	if($_page->error != "") {
		echo '<div class="errormsg regbox">';
		echo $_page->error;
		echo '</div>';
	}
}

/**
 * Output custom footer file and system footer
 * @param string $footerfile [optional] If not provided, 'footer.php' will be assumed
 * @todo hook here
 */
function showFooter($footerfile = "footer.php"){
	global $_page;

	include(SITE_PATH.$footerfile);
	?>
	<script type="text/Javascript" language="javascript">
	<?
	showErrorMsg(CORE_ERR);
	showErrorMsg(DEBUGGER_ERR+RUNTIME_ERR);
	echo PHP_EOL;
	?>
	</script>

	<?
	showHeadlines(true, false);
	?>
</body>
</html>

<head>
	<meta http-equiv="Pragma" content="no-cache"/>
	<meta http-equiv="Expires" content="-1"/>
</head>
<?
}
?>