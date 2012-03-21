<?php
// ---------------------------
//
// FOUNDRY COMMON FUNCTIONS
//  - Admin Form Functions
//
// ---------------------------
//
// list of tool functions
// - Uses: MySQL 4.2+

define ("FORMLOADED", true);

if(!defined("VALID_LOAD")) die ("This file cannot be accessed directly!");

define ("DEF_ACTION_ACTIVATE", "activate");         // bulk eligible
define ("DEF_ACTION_ADD", "add");
define ("DEF_ACTION_ARCHIVE", "archive");           // bulk eligible
define ("DEF_ACTION_BULK", "bulk");
define ("DEF_ACTION_CLONE", "clone");
define ("DEF_ACTION_DEACTIVATE", "deactivate");     // bulk eligible
define ("DEF_ACTION_DEFAULT", "default");
define ("DEF_ACTION_DELETE", "delete");             // bulk eligible
define ("DEF_ACTION_DEMOTE", "demote");
define ("DEF_ACTION_EDIT", "edit");
define ("DEF_ACTION_EDITFORM", "editfrm");
define ("DEF_ACTION_EDITMETA", "editmeta");
define ("DEF_ACTION_EXPORT", "export");
define ("DEF_ACTION_LIST", "list");
define ("DEF_ACTION_OPEN", "open");
define ("DEF_ACTION_PROMOTE", "promote");
define ("DEF_ACTION_PUBLISH", "publish");           // bulk eligible
define ("DEF_ACTION_REPLY", "reply");
define ("DEF_ACTION_SAVEORG", "saveorganize");
define ("DEF_ACTION_SEND", "send");                 // bulk eligible
define ("DEF_ACTION_SUBSCRIBE", "subscribe");       // bulk eligible
define ("DEF_ACTION_UNARCHIVE", "unarchive");       // bulk eligible
define ("DEF_ACTION_UNDELETE", "undelete");         // bulk eligible
define ("DEF_ACTION_UNPUBLISH", "unpublish");       // bulk eligible
define ("DEF_ACTION_UNSUBSCRIBE", "unsubscribe");   // bulk eligible
define ("DEF_ACTION_VIEW", "view");
define ("DEF_ACTION_VIEWPAGES", "viewpages");
define ("DEF_ACTION_VIEWRECS", "viewrecs");

define ("DEF_EDITBUT_SAVE", 1);
define ("DEF_EDITBUT_SAVEADD", 2);
define ("DEF_EDITBUT_UPDATE", 4);
define ("DEF_EDITBUT_DRAFT", 8);
define ("DEF_EDITBUT_PUB", 16);
define ("DEF_EDITBUT_ARC", 32);
define ("DEF_EDITBUT_ACT", 64);
define ("DEF_EDITBUT_REPLY", 128);
define ("DEF_EDITBUT_BACK", 256);
define ("DEF_EDITBUT_DELETE", 512);
define ("DEF_EDITBUT_STATS", 1024);
define ("DEF_EDITBUT_INFO", 2048);
define ("DEF_EDITBUT_LASTINFO", 4096);
define ("DEF_EDITBUT_PREVIEW", 8192);

define ("DEF_POST_ACTION_SAVE", "save");
define ("DEF_POST_ACTION_SAVEDRAFT", "savedraft");
define ("DEF_POST_ACTION_SAVEPUB", "savepub");
define ("DEF_POST_ACTION_SAVEARC", "savearc");
define ("DEF_POST_ACTION_SAVEACT", "saveact");
define ("DEF_POST_ACTION_SAVEREPLY", "savereply");
define ("DEF_POST_ACTION_SAVEADD", "saveadd");
define ("DEF_POST_ACTION_DELETE", "delete");

define ("DEF_PAGEBUT_ADDNEW", 1);
define ("DEF_PAGEBUT_ORGANIZER", 2);
define ("DEF_PAGEBUT_GOBACK", 4);

// include supplementary core functions
require_once(SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_formfields.php");
if(!defined("FORMFIELDSLOADED")) die("Common_formfields is required!");

// instantiate classes
include (SITE_PATH.ADMIN_FOLDER.LIB_FOLDER."page.class.php");
$_page  = PageClass::init();
$_js  	= JSBlock::init();

/**
 * Return the contents of the header file
 */
function showHeader(){
	foreach($GLOBALS as $key => $val){
		if(!in_array($key, array('GLOBALS', '_SERVER', '_COOKIE', '_SESSION', '_GET', '_POST', '_FILES', '_REQUEST', '_ENV'))){
			$$key = $val;
		}
	}
	include (SITE_PATH.ADMIN_FOLDER."header.php");
}

/**
 * Return the contents of the footer file
 */
function showFooter(){
	foreach($GLOBALS as $key => $val){
		if(!in_array($key, array('GLOBALS', '_SERVER', '_COOKIE', '_SESSION', '_GET', '_POST', '_FILES', '_REQUEST', '_ENV'))){
			$$key = $val;
		}
	}
	include (SITE_PATH.ADMIN_FOLDER."footer.php");
}

// ----------- FORM FUNCTIONS ---------------

/**
 * Return whether or not the user clicked on a 'save...' button
 * (Prelude to data saving function or handling code)
 */
function formDataIsReadyForSaving(){
	global $_page;

	return ($_page->savebuttonpressed);
}

/**
 * Start page form wrapper
 * @param string $formid
 * @param string $action
 * @param string $method
 * @param boolean $enctype
 * @param array $additional_hidden_fields
 */
function startPageForm($formid, $action = "", $method = "post", $enctype = false, $additional_hidden_fields = null){
	global $_page;

	if (isBlank($formid)) { $formid = 'list_form'; die('form'); }
	if (isBlank($action)) $action = $_SERVER['PHP_SELF'];
	($enctype) ? $enctype_code = ' enctype="multipart/form-data"' : $enctype_code = "";
	print "<form name=\"{$formid}\" id=\"{$formid}\" action=\"{$action}\" method=\"{$method}\"{$enctype_code}>\n";
	showHiddenField('_n', $_page->nonce);
	showHiddenField("page", $_page->pagenum);
	showHiddenField("page_url", $_SERVER['REQUEST_URI']);
	showHiddenField("page_subject", $_page->subject);
	showHiddenField("page_childsubject", $_page->childsubject);
	showHiddenField("page_ingroup", $_page->ingroup);
	showHiddenField("page_parentgroup", $_page->parentgroup);
	showHiddenField("row_id", $_page->row_id);
	showHiddenField("sectionid", $_page->sectionid);
	showHiddenField("full_delete", FULL_DELETE);
	showHiddenField("x_data", "");
	showHiddenField("cmd", "");
	if($_SESSION['root'] != "") showHiddenField("root", $_SESSION['root']);
	if(is_array($additional_hidden_fields)){
		foreach($additional_hidden_fields as $field => $value){
			showHiddenField($field, $value);
		}
	}
	if($formid == 'edit_form' || $formid == 'add_form'){
		showHiddenField("_savebuttonpressed", "");
		startTable("", "edittable");
	}
}

/**
 * End page form wrapper
 */
function endPageForm(){
	endTable();
	echo "</form>\n";
	echo "</div>\n";
}

/**
 * Output instructions block (between search and list areas)
 * @param str $text
 */
function showInstructions($text, $inclhead = true){
    if($text != ''){
		print "<div id=\"instruct_content\">\n";
        print $text;
		print "</div>\n";
    }
}

/**
 * Output list block
 * @param string $formname
 * @param array $hiddenfields
 * @param array $recset
 * @param string $buttoncondindex [optional]
 * @param string $buttontagfield [optional]
 * @param boolean $allowsort [optional]
 * @tutorial        // hover col (attr:hover)<br/>
                    // file existence test (attr:fileexists)<br/>
                    // quick edit col (attr:quickedit)<br/>
                    // simple boolean test (attr:bool; trueval:yes; falseval:no)<br/>
                    // conditional expression (attr:expr; compareusing:{=,>,<,>=,<=,!=}; compareval:value)<br/>
                    // conditional expression (attr:expr; compareusing:{=,>,<,>=,<=,!=}; compareval:value; trueval:action; falseval:action; style:style; wrap:true|false)<br/>
                    // image (attr:image; {thumbfield:name;} titlefield:name;)<br/>
                    // indentation (attr:indent; {padstr:str}{countfield:name; }{checkfield:name; }{checkval:val;})<br/>
 */
function showList($formname, $hiddenfields, $recset, $buttoncondindex = "", $buttontagfield = "", $allowsort = true) {
	global $db;
	global $_page;
	global $cols, $colattr, $colsize, $sortcols, $buttons, $totalcols;

	if(is_bool($buttoncondindex)) die(__FUNCTION__.": Argument mismatch at \$buttoncondindex!");
	if(is_bool($buttontagfield)) die(__FUNCTION__.": Argument mismatch at \$buttontagfield!");

	if(is_array($cols)) {
		// button action setup
		if (!is_array($buttons)) $buttons = array(DEF_ACTION_EDIT, DEF_ACTION_DELETE);
		//$button_array = getListButtonArray($buttons, $buttoncondindex);

		// columns setup
		$cols['actions'] = "";
		$colsize['actions'] = "";
        $attr = array();

        // persist list generation data in register
		$persist = mysql_escape_string(json_encode(array(
						'query' => $db->lastquery,
						'cols' => $cols,
						'colattr' => $colattr,
						'colsize' => $colsize,
						'totalcols' => $totalcols,
						'buttons' => $buttons,
						'buttontagfield' => $buttontagfield,
						'buttoncondindex' => $buttoncondindex,
						'altparams' => $_page->altparams,
						'altgroups' => $_page->altgroups,
						'addqueries' => $_page->addqueries,
						'titlefld' => $_page->titlefld,
						'imagefld' => $_page->imagefld,
						'thumbfld' => $_page->thumbfld
		)));
		replaceRec("register", "fileurl = '{$_page->uri}', parameters = '{$persist}', `type` = 'showlist'", "`type` = 'showlist' AND fileurl = '{$_page->uri}'");

		// custom hidden fields
		if (isset($hiddenfields) && is_array($hiddenfields)) {
			foreach($hiddenfields as $key => $value) {
				if($key != "") {
					print "	<input type=\"hidden\" name=\"$key\" id=\"$key\" value=\"$value\" />\n";
				}
			}
		}

        // move checkbox column (if present) to first element position
        if(isset($cols['_chk'])) {
        	unset($cols['_chk']);
        	$cols = array('_chk' => '') + $cols;
        }

		// table start
        print "<div class=\"listtable\">\n";

		// column header row
		print "<div class=\"listheader\">\n";
		$colattr = prepColAttr();

        foreach ($cols as $key => $value) {
			$size = explode("|", getIfSet($colsize[$key]));
			$width = ((!isBlank($size[0])) ? "width: ".$size[0] : "");
			$width = preg_replace("/;(.*);/i", "", $width);
			print "<div style=\"$width;\" class=\"listheader-cell\">";
			if($allowsort) {
    			if($_page->sort_by == $key) {
    				if(strtolower($_page->sort_dir) == "asc" || $_page->sort_dir == "") {
    					$imgsrc = WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/icons/arrow-up.png";
    					$imgdir = "desc";
    				} else {
    					$imgsrc = WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/icons/arrow-dn.png";
    					$imgdir = "asc";
    				}
    				print $value;
    				print "&nbsp;<input type='image' src='$imgsrc' border='0' alt='Re-sort this column' title='Re-sort this column' rel='$imgdir' class='listcol-sort' />";
    			}elseif(array_key_exists($key, $sortcols)){
    				print "<a href=\"Javascript: $('#sort_by').val('$key'); $('#list_form').submit();\">$value</a>";
    			}elseif($key == "_chk"){
    				print "<img id=\"listrow-check-act\" src=\"".WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/check.png\" alt=\"Click for bulk actions\" title=\"Click for bulk actions\" />&nbsp;";
            		print "<div id=\"listrow-check-optdiv\">
	            			<select id=\"listrow-check-opt\">
	            				<option value=\"\">--</option>
	            				<option value=\"select_all\">Select All</option>
	            				<option value=\"deselect_all\">Deselect All</option>
	            				<option value=\"-\">--</option>";

            		$blended_button_array = multiarray_unique($buttons);
            		foreach($blended_button_array as $label){
            			$actions = explode("::", $label);
            			if(in_array($actions[0], array(DEF_ACTION_ACTIVATE, DEF_ACTION_ARCHIVE, DEF_ACTION_CLONE, DEF_ACTION_DEACTIVATE, DEF_ACTION_DELETE, DEF_ACTION_EXPORT, DEF_ACTION_PUBLISH, DEF_ACTION_SEND, DEF_ACTION_SUBSCRIBE, DEF_ACTION_UNARCHIVE, DEF_ACTION_UNPUBLISH, DEF_ACTION_UNSUBSCRIBE)) || ($actions[0] == DEF_ACTION_UNDELETE && !FULL_DELETE)){
		            		print "<option value=\"{$actions[0]}\">".ucwords($actions[0])."</option>\n";
            			}
            		}
            		print "	</select>
	            		</div>\n";
    			}else{
    				print $value;
				}
			}
			print "</div>\n";
		}
		print "</div>\n";

		// data rows
		print "<div id=\"listbody\">\n";
		$hoverdivs = showListDataRows($recset, $cols, $colsize, $totalcols, $colattr, $buttons, $buttoncondindex, $buttontagfield);
		print "</div>\n";

		// table end
		print "</div>\n";

		if($hoverdivs != '') print $hoverdivs;
	}
}

function getListButtonArray($rec, $buttons, $buttoncondindex){
	$stack = debug_backtrace();
	if(strpos($stack[0]['file'], "_core") === false) die('Calling '.__FUNCTION__.' in '.$stack[0]['file'].' not allowed.');

	$button_keys = array_keys($buttons);

	// assign button array to working array
	if($buttoncondindex != ""){
		// conditional index is set
		$recbuttonindex = getIfSet($rec[$buttoncondindex]);
		if(!isBlank($recbuttonindex)){
			// record includes value for conditional index column
			if(is_array($buttons[$recbuttonindex])){
				// conditional array prepared properly
				$button_array = $buttons[$recbuttonindex];
			}else{
				// conditional array not an array
				$button_array = array($buttons[$recbuttonindex]);
			}
		}elseif(is_array($buttons[0])){
			// record does not include conditional index value, use the first button array
			$button_array = $buttons[0];
		}else{
			$button_array = $buttons;
		}
	}else{
		// no conditional index set (single dimensional array)
		$button_array = $buttons;
	}
	return $button_array;
}

function prepColAttr(){
	global $cols, $colattr;

	foreach ($cols as $key => $value) {
		if(isset($colattr[$key])) {
			// break colattr element value by semi-colons
			$attrstr = str_replace("\;", "¦", trim($colattr[$key]));
			$attrs = preg_split("(;|; )", $attrstr);
			foreach($attrs as $attrkey => $attrpair){
				// break attrpair (attr:hover) by colon
				$s = explode(":", $attrpair);
				$left_elem = $s[0];
				if(count($s) > 2){
					array_shift($s);
					$right_elem = join(":", $s);
				}else{
					$right_elem = $s[1];
				}
				$attr[trim($left_elem)] = str_replace("¦", ";", $right_elem);
			}

			$colattr[$key] = array('orig' => $colattr[$key]);
			if($attr['attr'] == "hover") {
				// hover col (attr:hover)
				$colattr[$key]['hover'] = true;
			}elseif($attr['attr'] == "fileexists") {
				// file existence test (attr:fileexists)
				$colattr[$key]['fileexist'] = true;
			}elseif($attr['attr'] == "quickedit") {
				// quick edit col (attr:quickedit)
				$colattr[$key]['xtra'] = DEF_ACTION_EDIT;
			}elseif($attr['attr'] == "boolean" || $attr['attr'] == "bool") {
				// simple boolean test (attr:bool; trueval:yes; falseval:no)
				// true if not zero, null or blank
				$colattr[$key]['boolean'] = true;
				$colattr[$key]['trueval'] = getIfSet($attr['trueval']);
				$colattr[$key]['falseval'] = getIfSet($attr['falseval']);
			}elseif($attr['attr'] == "expr" || $attr['attr'] == "if") {
				// conditional expression (attr:expr/if; compareusing:{=,>,<,>=,<=,!=}; compareval:value)
				$colattr[$key]['expr'] = true;
				$colattr[$key]['compareusing'] = getIfSet($attr['compareusing']);
				$colattr[$key]['compareval'] = getIfSet($attr['compareval']);
			}elseif($attr['attr'] == "advexpr" || $attr['attr'] == "advif") {
				// conditional expression (attr:advexpr/advif; compareusing:{=,>,<,>=,<=,!=}; compareval:value; trueval:action; falseval:action; style:style; wrap:true|false)
				$colattr[$key]['advexpr'] = true;
				$colattr[$key]['compareusing'] = getIfSet($attr['compareusing']);
				$colattr[$key]['compareval'] = getIfSet($attr['compareval']);
				$colattr[$key]['trueval'] = getIfSet($attr['trueval']);
				$colattr[$key]['falseval'] = getIfSet($attr['falseval']);
				$colattr[$key]['style'] = getIfSet($attr['style']);
				$colattr[$key]['wrap'] = getIfSet($attr['wrap']);
			}elseif($attr['attr'] == "image") {
				// image (attr:image; {thumbfield:name;} titlefield:name;)
				$colattr[$key]['image'] = true;
				$colattr[$key]['thumbfield'] = ((isBlank($attr['thumbfield'])) ? 'thumb' : $attr['thumbfield']);
				$colattr[$key]['titlefield'] = ((isBlank($attr['titlefield'])) ? $key : $attr['titlefield']);
			}elseif($attr['attr'] == "indent") {
				// indentation (attr:indent; {padstr:str}{countfield:name; }{checkfield:name; }{checkval:val;})
				$colattr[$key]['indent'] = true;
				$colattr[$key]['padstr'] = ((!isBlank($attr['padstr'])) ? str_replace("'", "", $attr['padstr']) : ' ');
				$colattr[$key]['countfield'] = getIfSet($attr['countfield']);
				$colattr[$key]['checkfield'] = getIfSet($attr['checkfield']);
				$colattr[$key]['checkval'] = getIfSet($attr['checkval']);
			}
		}
	}
	return $colattr;
}

function getListActionLabel($butlabel){
	/* button label can be:
	 * 	a. single value such as DEF_ACTION_EDIT, or
	 *  b. aliased value appended to label with ::
	*/
	$stack = debug_backtrace();
	if(strpos($stack[0]['file'], "_core") === false) die('Calling '.__FUNCTION__.' in '.$stack[0]['file'].' not allowed.');

	$aliaspos  = strpos($butlabel, "::");
	$alias     = "";
	$buttontag = "";
	if ($aliaspos !== false){
		// aliased label
		$label = substr($butlabel, 0, $aliaspos);
		$alias = substr($butlabel, $aliaspos+2);
		if(strpos($alias, "|") !== false){
			$buttontag = substr($alias, strpos($alias, "|") + 1);
			$alias = substr($alias, 0, strpos($alias, "|"));
		}
	}else{
		$label = $butlabel;
	}
	return array($label, $buttontag, $alias);
}

/**
 * Output all list data rows from data record set
 * @param array $recset
 * @param array $cols
 * @param array $colsize
 * @param array $totalcols
 * @param array $coltotal
 * @param array $colattr
 * @param array $buttons
 * @param string $buttontagfield
 * @param object $_altpage (optional)
 */
function showListDataRows($recset, $cols, $colsize, $totalcols, $colattr,
					      $buttons, $buttoncondindex, $buttontagfield,
					      $_altpage = null){

	global $_page, $db;
	$stack = debug_backtrace();
	if(strpos($stack[0]['file'], "_core") === false) die('Calling '.__FUNCTION__.' in '.$stack[0]['file'].' not allowed.');

	$coltotal = array();
	$rowcolor = LIST_ROWCOLOR1;
	$hoverdivs = '';

	if(is_null($_altpage)){
		$pagedata = $_page;
	} else {
		$pagedata = (object) $_altpage;
	}

	if(isset($recset)) {
		foreach($recset as $i => $rec) {
			// row field data
			$row_id = $rec['id'];
			$listhighlight = ((isBlank($listhighlight)) ? " listrow-highlight" : "");
			print "<div class=\"listrow{$listhighlight}\" id=\"listrow_{$row_id}\">\n";

			foreach($cols as $key => $value) {
				$size = explode("|", getIfSet($colsize[$key]));
				$width = (($size[0] != "") ? "width: ".$size[0]."; " : "");
				if(!isset($size[1])) $size[1] = "";
				$kval = getIfSet($rec[$key]);

				// calc totals (optional)
				if(isset($totalcols[$key])){
					$coltotal[$key] += floatval($kval);
				}

				if($key == "_chk"){
					// checkbox field
					print "<div style=\"$width\" class=\"listrow-cell\">";
					print "<input type=\"checkbox\" class=\"listrow-check\" name=\"listrow_check[]\" id=\"listrow-check-$i\" value=\"{$rec['id']}\" />";
					print "</div>\n";

				}elseif(isset($colattr[$key]['hover']) && !isBlank($kval)) {
					// hover cell
					// (attr:hover)
					print "<div style=\"$width\" class=\"listrow-cell\">";
					print "<a class=\"listrow-hoverbox\" rel=\"".$key.$i."\" ";
					print "href=\"Javascript: editrec()\" rel=\"{$row_id}\">";
					print "Hover to View</a>";
					print "</div>\n";
					$hoverdivs .= "<div id=\"".$key.$i."\" class=\"box\">".strip_tags($kval, '<br/><p><a><h1><h2><h3><h4><h5><h6><img><em><strong><b><i><u>')."</div>";

				}elseif(isset($colattr[$key]['fileexist'])) {
					// file existence test
					// (attr:fileexists)
					$path = pathinfo($kval);
					$file = $path['basename'];
					if(!isBlank($kval)) {
						if(@file_exists($kval)){
							$dataexists = "Yes";
						}else{
							$dataexists = "Invalid";
						}
					}else{
						$dataexists = "No";
					}
					print "<div style=\"$width\" class=\"listrow-cell\">".$dataexists."</div>\n";

				}elseif(isset($colattr[$key]['boolean'])) {
					// simple boolean test
					// (attr:bool; trueval:yes; falseval:no)
					if(($kval != 0 && is_numeric($kval)) || ($kval != "" && !is_numeric($kval))) {
						$dataexists = $colattr[$key]['trueval'];
					}else{
						$dataexists = $colattr[$key]['falseval'];
					}
					print "<div style=\"$width\" class=\"listrow-cell\">".$dataexists."</div>\n";

				}elseif(isset($colattr[$key]['expr'])) {
					// expression test 1
					// conditional expression (attr:expr; compareusing:{=,>,<,>=,<=,!=}; compareval:value)
					$flag = "";
					$value1 = $kval;
					$value2 = str_replace("'", "", getIfSet($colattr[$key]['compareval']));
					switch (getIfSet($colattr[$key]['compareusing'])){
						case ">":
							if($value1 > $value2) $flag = '*';
							break;
						case "<":
							if($value1 < $value2) $flag = '*';
							break;
						case ">=":
							if($value1 >= $value2) $flag = '*';
							break;
						case "<=":
							if($value1 <= $value2) $flag = '*';
							break;
						case "!=":
							if($value1 != $value2) $flag = '*';
							break;
						default:
							if($value1 == $value2) $flag = '*';
							break;
					}
					print "<div style=\"$width\" class=\"listrow-cell\">{$value1} $flag</div>\n";

				}elseif(isset($colattr[$key]['advexpr'])) {
					// expression test 2
					// conditional expression (attr:advexpr; compareusing:{=,>,<,>=,<=,!=}; compareval:value; trueval:action; falseval:action; style:style; wrap:true|false)
					$flag = "";
					$value1 = $kval;
					$value2 = str_replace("'", "", getIfSet($colattr[$key]['compareval']));
					$fldcompareok  = false;
					$cellstyle = "";
					$wrap = "";
					switch (getIfSet($colattr[$key]['compareusing'])){
						case ">":
							if($value1 > $value2) $fldcompareok = true;
							break;
						case "<":
							if($value1 < $value2) $fldcompareok = true;
							break;
						case ">=":
							if($value1 >= $value2) $fldcompareok = true;
							break;
						case "<=":
							if($value1 <= $value2) $fldcompareok = true;
							break;
						case "!=":
							if($value1 != $value2) $fldcompareok = true;
							break;
						default:
							if($value1 == $value2) $fldcompareok = true;
							break;
					}

					$setting = (($fldcompareok) ? getIfSet($colattr[$key]['trueval']) : getIfSet($colattr[$key]['falseval']));

					if ($colattr[$key]['style'] != ''){
						$cellstyle = " ".$colattr[$key]['style'].";";
					}
					if (strpos(strtolower(getIfSet($colattr[$key]['wrap'])), 'val') !== false){
						$setting = str_replace('val', $setting, strtolower($colattr[$key]['wrap']));
					}
					if (substr($setting, 0, 2) == ">>"){
						$kval = substr($setting, 2);
					}else{
						$kval = $setting;
					}
					$cell_text = (($size[1] != "" && strlen($kval) > intval($size[1])) ? substr($kval, 0, intval($size[1]))."..." : $kval);
					print "<div style=\"".$width.$cellstyle."\" class=\"listrow-cell\">".$cell_text."</div>\n";

				}elseif(isset($colattr[$key]['image'])) {
					// show image instead of link
					// image (attr:image; {thumbfield:name;} titlefield:name;)
					if(isBlank($colattr[$key]['thumbfield'])) $colattr[$key]['thumbfield'] = 'thumb';
					if(isBlank($colattr[$key]['titlefield'])) $colattr[$key]['titlefield'] = $key;
					$title = $rec[$colattr[$key]['titlefield']];
					if(!isBlank($kval)){
						$folder = $db->table;
						if($folder == DB_TABLE_PREFIX."photos_cat") $folder = DB_TABLE_PREFIX."photos";
						$imgpath = checkImagePath($kval, IMG_UPLOAD_FOLDER.$folder, "");
						$thmpath = checkThumbPath($rec[$colattr[$key]['thumbfield']], THM_UPLOAD_FOLDER.$folder, "");
						if($thmpath != "" && $thmpath != THM_UPLOAD_FOLDER){
							list($imgwidth, $imgheight) = constrainImage(SITE_PATH.$thmpath, THM_MAX_WIDTH, THM_MAX_HEIGHT);
							$imgname = basename($imgpath);
							$img = "<a href=\"#\" class=\"action_edit\" rel=\"".$row_id."\">".$title."</a><br/>";
							$img.= "<div class=\"listimage\" style=\"width: ".($imgwidth+6)."px\">";
							$img.= "<a href=\"#\" class=\"action_edit\" rel=\"".$row_id."\">";
							$img.= "<img src=\"".WEB_URL.$thmpath."\" width=\"$imgwidth\" height=\"$imgheight\" alt=\"$imgname\" title=\"$imgname\" /></a>";
							$img.= "</div>";
						}else{
							$img = "<a href=\"#\" class=\"action_edit\" rel=\"".$row_id."\">".$title."</a><br/>";
						}
					}else{
						$img = "<div style=\"border: 1px solid #bbb; padding: 2px; width: 102px;\">$title</span>";
					}
					print "<div style=\"$width\" class=\"listrow-cell\">".$img."</div>\n";

				}elseif(isset($colattr[$key]['indent'])) {
					// indent data by various means
					// (attr:indent; {padstr:str}{countfield:name; }{checkfield:name; }{checkval:val;})
					$count = 0;
					$indent = '';
					if(!isBlank($colattr[$key]['countfield'])){
						$count = intval($rec[$colattr[$key]['countfield']]);
					}elseif(!isBlank($colattr[$key]['checkfield']) && !isBlank($colattr[$key]['checkval'])){
						$count = preg_match_all("/".$colattr[$key]['checkval']."/i", $rec[$colattr[$key]['checkfield']], $matches);
					}
					if($count > 0) $indent = str_repeat($colattr[$key]['padstr'], $count);
					print "<div style=\"$width\" class=\"listrow-cell\">".$indent.$kval."</div>\n";

				}elseif($key != "actions" && $key != "rank") {
					// normal content
					($size[1] != "" && strlen($kval) > intval($size[1])) ? $cell_text = substr($kval, 0, intval($size[1]))."..." : $cell_text = $kval;
					if(!isBlank($colattr[$key]['xtra'])) {
						// quick edit link
						// (attr:quickedit)
						$cell_text = "<a href=\"javascript: ".$colattr[$key]['xtra']."rec()\" rel=\"{$row_id}\">".$cell_text."</a>";
					}
					print "<div style=\"$width\" class=\"listrow-cell\">$cell_text</div>\n";

				}else{
					// row actions
					$altparams = $pagedata->altparams;
					$altgroups = $pagedata->altgroups;
					$addqueries = $pagedata->addqueries;
					$action = "";
					$buttontag = "";

					//$firstkey = current(array_keys($button_array));
					if(substr(getIfSet($cellstyle), 0, 2) == "; ") $cellstyle = substr($cellstyle, 2);

					// get the button array appropriate for this recset
					$button_array = getListButtonArray($rec, $buttons, $buttoncondindex);

					print "<div class=\"list-actions\">";
					foreach($button_array as $butlabel) {
						list($label, $buttontag, $alias) = getListActionLabel($butlabel);
						$stub = "";
						$labeltask = preg_replace('/^(de(?!l)|un)/', '', $label);
						$rel = " rel=\"{$row_id}\" id=\"action_{$labeltask}_{$row_id}\"";
						$rel.= ((!isBlank($altgroups[$label])) ? " altgroup=\"{$altgroups[$label]}\"" : "");
						$rel.= ((!isBlank($altparams[$label])) ? " altparam=\"{$altparams[$label]}\"" : "");
						$rel.= ((!isBlank($addqueries[$label])) ? " addquery=\"{$addqueries[$label]}\"" : "");
						$tag = (($buttontag != '') ? " tag=\"$buttontag\"" : "");

						$is_deleted = (getIntValIfSet($rec['deleted']) > 0);
						$is_published = (getIntValIfSet($rec['published']) > 0);
						$is_archived = (getIntValIfSet($rec['archived']) > 0);
						$is_activated = (getIntValIfSet($rec['activated']) > 0);
						$is_locked = (getIntValIfSet($rec['locked']) > 0);
						$is_protected = (getIntValIfSet($rec['protected']) > 0);
						$is_subscribed = (getIntValIfSet($rec['subscribed']) > 0);
						$is_replied = (getIntValIfSet($rec['replied']) > 0);
						$is_sent = (getIntValIfSet($rec['sent']) > 0);
						$is_gallerydef = (getIntValIfSet($rec['gallery_def']) > 0);
						switch ($label) {
							case DEF_ACTION_ADD:
								$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">Add ".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_EDIT:
								if(!$is_deleted) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">Edit".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_EDITFORM:
								if(!$is_deleted) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">Edit".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_EDITMETA:
								if(!$is_deleted) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">Edit ".(($alias == "") ? "Meta-Data" : $alias)."</span></a>";
								break;
							case DEF_ACTION_DELETE:
								if(!$is_deleted && !$is_locked && !$is_protected && ALLOW_DELETE) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"red\">Delete".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_UNDELETE:
								if($is_deleted && !$is_locked && ALLOW_DELETE) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">Un-Delete".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_PUBLISH:
								if(!$is_published && !$is_deleted && !$is_archived && ALLOW_PUBLISH) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue bold\">Publish".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_UNPUBLISH:
								if($is_published && !$is_deleted && !$is_archived && ALLOW_PUBLISH) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue bold\">Un-Publish".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_ACTIVATE:
								if(!$is_activated && !$is_deleted && !$is_archived && ALLOW_PUBLISH) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue bold\">Activate".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_DEACTIVATE:
								if($is_activated && !$is_deleted && !$is_archived && ALLOW_PUBLISH) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue bold\">Deactivate".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_ARCHIVE:
								if(!$is_archived && !$is_deleted && ALLOW_ARCHIVE) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">Archive".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_UNARCHIVE:
								if($is_archived && !$is_deleted && ALLOW_ARCHIVE) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">Un-Arc".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_SUBSCRIBE:
								if(!$is_subscribed && !$is_deleted) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue bold\">Subscribe".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_UNSUBSCRIBE:
								if($is_subscribed && !$is_deleted) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue bold\">Un-Subscribe".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_VIEW:
								$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">View ";
								$stub .= (!isBlank($butlabelstr[1])) ? $butlabelstr[1] : (($alias == "") ? "" : $alias);
								$stub.= "</a>";
								break;
							case DEF_ACTION_VIEWRECS:
								$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">View ";
								$stub .= (!isBlank($butlabelstr[1])) ? $butlabelstr[1] : (($alias == "") ? "List" : $alias);
								$stub.= "</span></a>";
								break;
							case DEF_ACTION_VIEWPAGES:
								$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">View ";
								$stub .= (!isBlank($butlabelstr[1])) ? $butlabelstr[1] : (($alias == "") ? "Pages" : $alias);
								$stub.= "</span></a>";
								break;
							case DEF_ACTION_OPEN:
								$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">Switch To".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_REPLY:
								if(!$is_replied) {
									$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">".(($alias == "") ? "View/Reply" : $alias)."</span></a>";
								}else{
									$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">".(($alias == "") ? "View" : $alias)."</span></a>";
								}
								break;
							case DEF_ACTION_SEND:
								if(!$is_archived && !$is_deleted && ($is_published || $is_activated)) {
									if (!$is_sent) {
										$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"bold blue\">".(($alias == "") ? "Send Now" : $alias)."</span></a>";
									}else{
										$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"bold blue\">".(($alias == "") ? "Sent" : $alias)."</span></a>";
									}
								}
								break;
							case DEF_ACTION_CLONE:
								$tag = " tag=\"".str_replace(array("&#34;","&#39;"), array("", ""), $rec[$pagedata->titlefld])."\"";
								$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">Clone</span></a>";
								break;
							case DEF_ACTION_EXPORT:
								$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">Export".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_DEFAULT:
								if($is_published && !$is_deleted) {
									if(!$is_gallerydef) {
										$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">Set as ".(($alias == "") ? " Default" : $alias)."</span></a>";
									}else{
										$stub = "<b>Is ".(($alias == "") ? "Default" : $alias)."</b>";
									}
								}
								break;
						}
						if($action != "" && $stub != "") $stub = " | ".$stub;
						$action .= $stub;
					}
					print "$action</div>\n";
				}
			}
			print "</div>\n";
			$rowcolor = (($rowcolor == LIST_ROWCOLOR1) ? LIST_ROWCOLOR2 : LIST_ROWCOLOR1);
		}
	}

	// show totals (optional)
	if(count($totalcols) > 0){
		$colnum = 0;
		print "<div id=\"listrow_totals\" class=\"listrow\">\n";
		foreach ($cols as $key => $value) {
			$size = explode("|", $colsize[$key]);
			$width = (($size[0] != "") ? "width: ".$size[0] : "");
			$width = preg_replace("/;(.*);/i", "", $width);
			print "<div style=\"$width\" class=\"listrow-cell\">";
			if($colnum == 0){
				print "Total:";
			}elseif($coltotal[$key] != 0){
				print number_format($coltotal[$key], 2);
			}
			print "</div>\n";
			$colnum++;
		}
		print "</div>\n";
	}

	return $hoverdivs;
}

/**
 * Output search block
 * @param array $searchcols
 */
function showSearch($allow_sort_override = null) {
    global $searchcols, $sortcols, $_page;

    $search_text = $_page->search_text;
    $search_by = $_page->search_by;
    $sort_by = $_page->sort_by;
    $sort_dir= $_page->sort_dir;
    if(is_null($allow_sort_override)) $allow_sort_override = ALLOW_SORT;

    if(!ALLOW_SEARCH) return;

	if(is_array($searchcols) && is_array($sortcols)) {
		$searchcols = array("" => "- No filter -", "all" => "All") + $searchcols;
		$sortcols = array("" => "- Not Sorted -") + $sortcols;

        print "<div id=\"search_header\">Search Tools<div id=\"search_content_toggle\" class=\"toggleclose\">&nbsp;</div></div>\n";
		print " <div id=\"search_content\">\n";
		print "     <div class=\"search_leftdiv\">Search for:</div>";
		print "     <div class=\"search_rightdiv\">";
		print "         <input type=\"text\" id=\"search_text\" name=\"search_text\" value=\"$search_text\" size=\"70\" /> in ";
		print "         <select id=\"search_by\" name=\"search_by\" size=\"1\">";
		foreach($searchcols as $key => $value) {
			if($search_by == $key) {
				print "<option value=\"$key\" selected>$value</option>";
			}else{
				print "<option value=\"$key\">$value</option>";
			}
		}
		print "         </select>";
		print "     </div>\n";

		if (ALLOW_SORT && $allow_sort_override) {
    		print "<div class=\"search_leftdiv\">Sort by:</div>";
    		print "<div class=\"search_rightdiv\">";
    		print "<select id=\"sort_by\" name=\"sort_by\" size=\"1\">";
    		foreach($sortcols as $key => $value) {
    			if($sort_by == $key) {
    				print "<option value=\"$key\" selected>$value</option>";
    			}else{
    				print "<option value=\"$key\">$value</option>";
    			}
    		}
    		print "</select>\n";
    		print "<select id=\"sort_dir\" name=\"sort_dir\" size=\"1\">";
    		$sortdircols = array("asc" => "Ascending", "desc" => "Descending");
    		foreach($sortdircols as $key => $value) {
    			if($sort_dir == $key) {
    				print "<option value=\"$key\" selected>$value</option>";
    			}else{
    				print "<option value=\"$key\">$value</option>";
    			}
    		}
    		print "</select>";
    		print "</div>\n";
			print "<div id=\"search_buttons\">";
			print "<input id=\"page_search\" name=\"page_search\" type=\"submit\" value=\"Search/Sort Now\" />\n";
		}else{
			print "<div id=\"search_buttons\">";
			print "<input id=\"page_search\" name=\"page_search\" type=\"submit\" value=\"Search Now\" />\n";
		}

		print "</div>\n";
		print "</div>\n";
	}
}

/**
 * Output pagination block
 * @param integer $total
 * @param boolean $showrecbuttons [optional]
 * @param array $extrabuttons [optional] (array("id", "value", "JS"[, "alt"][, "title"][, "class"]))
 * @param string $addnewbutname [optional]
 * @param string $addnewbutparams [optional]
 * @param string $appendquery [option]
 */
function showPagination($total, $recbuttons = 0, $extrabuttons = "", $addnewbutparams = "", $appendquery = ""){
    global $_page;

    $total_count = $total;
    $num_of_pages = 0;
    $display_pages = "";

    if ($total_count > LIST_ROWLIMIT){
        $num_of_pages = ceil($total_count/LIST_ROWLIMIT);

        $prev = "";
        $next = "";
        $count = 0;

        if(isset($_REQUEST['start']) && $_REQUEST['start'] != "")
        	$start = $_REQUEST['start'];
        else
        	$start = 1;

        //populate page numbers for easy page surfing
        if(LIST_PAGESSHOWN > 0){
            for($i = $start; $i <= $num_of_pages; $i++){
            	//bold the current page
            	if($i == $_page->pagenum){
            		$page_uri = "<span class='page_current'>$i</span>";
            		$display_pages .= $page_uri." ";
            	}else{
            		$page_uri = "<a href=\"?";
					if($appendquery != "") $page_uri .= $appendquery."&";
					$page_uri .= "start=$start&page=$i\">$i</a>";
            		$display_pages .= $page_uri." ";
            	}

            	$count ++;
            	if ($count >= LIST_PAGESSHOWN) $i = $num_of_pages + 1;  //exit the 'for' loop
            }
        }

        //generate previous button
        if ($_page->pagenum > 1){
            if ($start > ($_page->pagenum - 1)){
            	$new_start = $_page->pagenum - LIST_PAGESSHOWN;
            }else{
            	$new_start = $start;
            }
			$prev = "<input type='button' class='list_button_page_prev' value='<' rel='".($_page->pagenum - 1)."' />";
            $display_pages = $prev."&nbsp;&nbsp;".$display_pages;
        }

        //generate next button
        if ($_page->pagenum != $num_of_pages){
            if ((($_page->pagenum + 1) - $start) == LIST_PAGESSHOWN){
            	$new_start = $_page->pagenum +1 ;
            }else{
            	$new_start = $start;
            }
			$next = "<input type='button' class='list_button_page_next' value='>' rel='".($_page->pagenum + 1)."' />";
            $display_pages = $display_pages."&nbsp;&nbsp;".$next;
        }
    }

	if($total_count == 0) $_page->offset = -1;

	$lastrow = $_page->offset + LIST_ROWLIMIT;
	if($lastrow > $total_count) $lastrow = $total_count;
    print "<div class=\"paging_content\">\n";
    print "<div class=\"paging_leftdiv\">";
	print "Displaying ".($_page->offset + 1)." to ".($lastrow)." of $total_count Item".(($total_count != 1) ? "s" : "");
	print "</div>";
	print "<div class=\"paging_rightdiv\">";
	print $display_pages;
	print "</div>";

	showPaginationAreaButtons($recbuttons, $extrabuttons, $addnewbutparams);

	print "</div>\n\n";
    return $display_pages;
}

/**
 * Output pagination buttons such as "add new", "go back" or "organize"
 * @param integer $recbuttons
 * @param array $extrabuttons
 * @param string $addnewbutparams
 */
function showPaginationAreaButtons($recbuttons = 0, $extrabuttons = "", $addnewbutparams = ""){
    global $_page;

	// display add record button here
    if($recbuttons > 0 || is_array($extrabuttons)) {
        print "<div id=\"paging_recbuttons\">\n";
        if(($recbuttons & DEF_PAGEBUT_ADDNEW) > 0){
        	if(isBlank($addnewbutparams)) $addnewbutparams = $_page->cat_id;
        	print "<input type=\"button\" name=\"addbutton\" id=\"addbutton\" value=\"Add New ".ucwords_smart($_page->subject)."\" class=\"action_add\" rel=\"$addnewbutparams\" />\n";
        }
        if(($recbuttons & DEF_PAGEBUT_GOBACK) > 0){
        	print "<input type=\"button\" name=\"gobackbutton\" id=\"gobackbutton\" value=\"Go Back to Parent\" class=\"action_goback\" />\n";
        }
        if(($recbuttons & DEF_PAGEBUT_ORGANIZER) > 0){
        	print "<input type=\"button\" name=\"organizebutton\" id=\"organizebutton\" value=\"Organize\" class=\"action_organize\" />\n";
        }

        if(is_array($extrabuttons)) {
            foreach($extrabuttons as $key => $value) {
                /* 0 = id/name (req)
                   1 = value (req)
                   2 = javascript function (req)
                   3 = alt tag
                   4 = title tag
                   5 = class
                */
                if($value[0] != "" && $value[1] != ""){
                    print "&nbsp;&nbsp;<input type=\"button\" name=\"".$value[0]."\" id=\"".$value[0]."\" value=\"".$value[1]."\"";
                    if($value[2] != "") print " onclick=\"javascript: ".$value[2]."\"";
                    if($value[3] != "") print " alt=\"".$value[3]."\"";
                    if($value[4] != "") print " title=\"".$value[4]."\"";
                    if($value[5] != "") print " class=\"".$value[5]."\"";
                    print " />\n";
                }
            }
        }
        print "</div>";
    }
}

/**
 * Output editor action buttons
 * @param integer $butset [optional]
 * @param string $backquery [optional]
 */
function showEditorButtons($butset = 0) {
	global $db;

	$the_page = str_replace("data_", "", $db->table);
	$pad = "&nbsp;";

    // save button
	if (($butset & DEF_EDITBUT_SAVE) > 0) echo '<input type="button" name="'.DEF_POST_ACTION_SAVE.'" value="Save" class="editor_button_save greenbutton bold" />';
    // save & add new button
	if (($butset & DEF_EDITBUT_SAVEADD) > 0) echo '<input type="button" name="'.DEF_POST_ACTION_SAVEADD.'" value="Save & Add New" class="editor_button_save greenbutton bold" />';
    // update button
	if (($butset & DEF_EDITBUT_UPDATE) > 0) echo $pad.'<input type="button" name="'.DEF_POST_ACTION_SAVE.'" value="Update" class="editor_button_save greenbutton bold" />';
	// save to draft button
    if (ALLOW_DRAFT && ($butset & DEF_EDITBUT_DRAFT) > 0) echo $pad.'<input type="button" name="'.DEF_POST_ACTION_SAVEDRAFT.'" id="savedraft" value="Save as Draft" class="editor_button_save greenbutton" />';
	// save & publish button
	if (ALLOW_PUBLISH && ($butset & DEF_EDITBUT_PUB) > 0) echo $pad.'<input type="submit" name="'.DEF_POST_ACTION_SAVEPUB.'" value="Save & Publish" class="editor_button_save greenbutton bold" />';
	// save & activate button
    if (ALLOW_PUBLISH && ($butset & DEF_EDITBUT_ACT) > 0) echo $pad.'<input type="button" name="'.DEF_POST_ACTION_SAVEACT.'" value="Save & Activate" class="editor_button_save greenbutton bold" />';
	// save & archive button
    if (ALLOW_ARCHIVE && ($butset & DEF_EDITBUT_ARC) > 0) echo $pad.'<input type="button" name="'.DEF_POST_ACTION_SAVEARC.'" value="Archive" class="editor_button_save greenbutton bold" />';
	// save & reply button
    if (($butset & DEF_EDITBUT_REPLY) > 0) echo $pad.'<input type="button" name="'.DEF_POST_ACTION_SAVEREPLY.'" value="Save & Reply" class="editor_button_save greenbutton bold" />';
	// delete button
    if (ALLOW_DELETE && ($butset & DEF_EDITBUT_DELETE) > 0) echo $pad.'<input type="button" name="'.DEF_POST_ACTION_DELETE.'" value="Delete" class="editor_button_delete redbutton bold" />';
	// preview button
    if (($butset & DEF_EDITBUT_PREVIEW) > 0) echo $pad.'<input type="button" class="editor_button_preview" value="Preview" />';
	// status button
    if (($butset & DEF_EDITBUT_STATS) > 0) echo $pad.'<input type="button" class="editor_button_status" value="View Stats" />';
	// info button
    if (($butset & DEF_EDITBUT_INFO) > 0) {
		echo $pad.'<input type="button" class="editor_button_info" value="View Info" />';
		$GLOBALS['infodialogtype'] = DEF_EDITBUT_INFO;
	}
    if (($butset & DEF_EDITBUT_LASTINFO) > 0) {
		echo $pad.'<input type="button" class="editor_button_info" value="Last Added" />';
		$GLOBALS['infodialogtype'] = DEF_EDITBUT_LASTINFO;
	}
	// go back button
    if (($butset & DEF_EDITBUT_BACK) > 0) echo $pad.'<br/><br/><input type="button" class="editor_button_goback" value="Go Back" rel="'.$the_page.'" />';
}

/**
 * Output previous page buttons
 * @param array $buttonlist
 */
function showPrevPageButtons($buttonlist){
	if(is_array($buttonlist)){
		print "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" class=\"prev_page_form\">\n";
		$count = 0;
		foreach($buttonlist as $key => $button){
			$count++;
			print "<input type=\"button\" rel=\"".WEB_URL.ADMIN_FOLDER.$button."\" class=\"editor_button_prev_page\" value=\"Back to ".$key."\" />\n";
		}
		print "</form>";
	}
}

/**
 * Output status box/block
 * @param string $extraclass [optional]
 */
function showStats($extraclass = "") {
	global $date_created, $date_published, $date_activated, $date_subscribed, $date_updated;
	global $draft, $published, $activated, $subscribed, $archived, $deleted;

	if ($extraclass != "") $extraclass = " class=\"$extraclass\"";
	print<<<EOT
	<div id="stats_content" title="Statistics"{$extraclass}>
EOT;

	if ($date_created != "" && $date_created != "0000-00-00 00:00:00") {
	print<<<EOT
	<div class="label">Created:</div>
	<div class="item">{$date_created}</div>
EOT;
	}
	if ($date_updated != "" && $date_updated != "0000-00-00 00:00:00") {
	print<<<EOT
	<div class="label">Last Updated:</div>
	<div class="item">{$date_updated}</div>
EOT;
	}
	if ($published == 1 && $date_published != "" && $date_published != "0000-00-00 00:00:00") {
	print<<<EOT
	<div class="label">Date Published:</div>
	<div class="item">{$date_published}</div>
EOT;
	}
	if ($activated == 1 && $date_activated != "" && $date_activated != "0000-00-00 00:00:00") {
	print<<<EOT
	<div class="label">Date Activated:</div>
	<div class="item">{$date_activated}</div>
EOT;
	}
	if ($subscribed == 1 && $date_subscribed != "" && $date_subscribed != "0000-00-00 00:00:00") {
	print<<<EOT
	<div class="label">Date Subscribed:</div>
	<div class="item">{$date_subscribed}</div>
EOT;
	}
	print<<<EOT
	<div class="label">Current Status:</div>
	<div class="item">
EOT;
	if($archived == 1){
		print "<span class=\"graybutton bold\">Archived</span> (not visible to public)";
	}elseif($published == 1){
		print "<span class=\"greenbutton bold\">Published</span> (visible to public)";
	}elseif($activated == 1){
		print "<span class=\"greenbutton bold\">Activated</span> (accessible)";
	}elseif($subscribed == 1){
		print "<span class=\"greenbutton bold\">Subscribed</span>";
	}elseif($deleted == 1){
		print "<span class=\"redbutton bold\">Deleted</span> (not editable or visible to public)";
	}elseif(isset($activated) && $activated == 0){
		print "<span class=\"redbutton bold\">NOT Activated</span>";
	}elseif(isset($published) || isset($draft) || isset($archived)){
		print "<span class=\"graybutton bold\">Updated</span> (not visible to public)";
	}elseif(isset($published) && $published == 0){
		print "<span class=\"redbutton bold\">Draft</span> (not visible to public)";
	}elseif(isset($subscribed) && $subscribed == 0){
		print "<span class=\"redbutton bold\">NOT Subscribed</span>";
	}else{
		print "Updated";
	}
	print<<<EOT
	</div>
	</div>
EOT;
}

/**
 * Output info box/block
 * @param string $itemval
 * @param string $extraclass [optional]
 */
function showInfo($extraclass = "") {
	$row_id = $GLOBALS['lastitemid'];
	$itemval = $GLOBALS['lastitemval'];
	if($extraclass != "") $extraclass = " class=\"$extraclass\"";
	print<<<EOT
	<div id="info_content" title="Information"{$extraclass}>
EOT;
	if($GLOBALS['infodialogtype'] == DEF_EDITBUT_LASTINFO){
		if(getErrorStatMsg(SUCCESS_CREATE)){
			if(intval($row_id)>0){
			print<<<EOT
	<div class="label">Last ID:</div>
	<div class="item">{$row_id}</div>
EOT;
			}
			if($itemval != ''){
			print<<<EOT
	<div class="label">Last Item:</div>
	<div class="item">'{$itemval}'</div>
EOT;
			}
		}else{
			print<<<EOT
	<div class="label">Last Item:</div>
	<div class="item">Not available</div>
EOT;
		}
	}else{
		if($itemval != ''){
		print<<<EOT
	<div class="label">Item:</div>
	<div class="item">'{$itemval}'</div>
EOT;
		}
	}
	print<<<EOT
	</div>
EOT;
}

/**
 * Output send email block
 * @param string $table
 * @param string $msgtype
 * @param string $msgfld
 * @param string $crit
 * @param string $choosesubs
 */
function showSendPanel($table, $msgtype, $msgfld, $crit="", $choosesubs=false){
	if($table != "" && $msgtype != ""){
		//print "<input type=\"hidden\" name=\"orgranks\" id=\"orgranks\" value=\"\"/n>\n";
		$msgrec = getRec($table, "*", $crit, $msgfld, "");
		print "<input type=\"hidden\" name=\"sendmsg_subj\" id=\"sendmsg_subj\" value=\"\" />\n";
		print "<input type=\"hidden\" name=\"sendmsg_content\" id=\"sendmsg_content\" value=\"\" />\n";
		print "<input type=\"hidden\" name=\"sendmsg_file\" id=\"sendmsg_file\" value=\"\" />\n";
		print "<input type=\"hidden\" name=\"sendmsg_items\" id=\"sendmsg_items\" value=\"\" />\n";
		print "<div id=\"sendpanel\" title=\"Send Message\" style=\"display: none;\">\n";
		print "<p>Note: Select the topic or newsletter that you want to send to your subscribers.  Click ";
		print "<input type=\"button\" id=\"sendbut\" name=\"sendbut\" value=\"Send Now\" onclick=\"javascript: sendmessage('$msgtype');\"/>\n";
		print ".</p>\n";
		print "<div class=\"sendmsg_label\">Subject: *</div>";
		print "<div class=\"sendmsg_text\"><input type=\"text\" name=\"send_subj\" id=\"send_subj\" size=\"57\" value=\"\" /></div>\n";
		print "<div class=\"sendmsg_label\">Message to Send: *</div>";
		print "<div class=\"sendmsg_text\"><textarea name=\"send_content\" id=\"send_content\" rows=\"5\" cols=\"55\"></textarea></div>\n";
		print "<div class=\"sendmsg_label\">File (optional):</div>";
		print "<div class=\"sendmsg_text\"><input type=\"file\" name=\"send_file\" id=\"send_file\" size=\"40\" value=\"\" /></div>\n";
		print "<div class=\"sendmsg_label\">".ucwords($msgtype)."s:</div>";
		print "<div class=\"sendmsg_text\">\n";
		foreach($msgrec as $msgitem){
			(isset($msgitem['date_lastsent']) && substr($msgitem['date_lastsent'], 0, 10) != "0000-00-00") ? $lastsent = " (last sent: ".date("M j, Y", strtotime($msgitem['date_lastsent'])).")" : $lastsent = " (Never sent)";
			print "<input type=\"checkbox\" class=\"send_item\" name=\"send_item[]\" value=\"".$msgitem['id']."\" />&nbsp;".$msgitem[$msgfld].$lastsent."<br/>\n";
		}
		print "</div>\n";
		print "</div>\n";

		print<<<EOT
<script type="text/javascript" language="Javascript">
	function sendmessage(msgtype){
		// store data in sendmsg fields (which is outside hidden panel)
		var num = 0;
		var rtn = "";
		for(var i = 0; i < senditems.length; i++){
		$('.send_item').each(function(){
			if ($(this).is(':checked')) {
				num = $(this).val();
				if (rtn != "")
					rtn += ",";
				rtn += num;
			}
		});
		// validate now
		$('#sendmsg_subj').val($('#send_subj').val());
		$('#sendmsg_content').val($('#send_content').val());
		$('#sendmsg_file').val($('#send_file').val());
		$('#sendmsg_items').val(rtn);
		if(!checkRequiredField('sendmsg_subj', 'Please enter the subject.'))
			return false;

		if(!checkRequiredField('sendmsg_content', 'Please enter the message content.'))
			return false;

		if(!checkRequiredField('sendmsg_items', 'Please choose at least one '+msgtype+' to include.'))
			return false;

		$('#cmd').val('send');
		//document.list_form.submit();
		return false;
	}
</script>

EOT;
	}
}

/**
 * Create CSV from recordset
 * @param string $title
 * @param string $table
 * @param string $crit
 * @param string $sort
 * @param string $headings
 * @param string $fields
 * @param string $sums
 */
function exportData($title, $table, $crit, $sort, $headings, $fields, $sums = ""){
	if($table != "" && $headings != "" && $fields != ""){
		$heading_array = explode(",", $headings);
		$field_array   = explode(",", $fields);
		$sum_array     = explode(",", $sums);

		if(is_array($heading_array) && is_array($field_array)){
			$datarec = getRec($table, $fields, $crit, $sort, "");

			$out  = '"'.$title.'"';
			$out .= "\n\r";

			// heading row
			foreach ($heading_array as $key => $cell) $out .= '"'.trim($cell).'",';
			$out .= "\n\r";

			// content rows
			$sumval_array = array();
			foreach($datarec as $dkey => $item){
				$row = "";
				foreach($field_array as $fkey => $field){
					$fld = strtolower(trim($field));
					if(strpos($fld, ' as ') !== false) {
						$as_fld = split(' as ', $fld);
						$fld = $as_fld[1];
					}
					if(is_array($sum_array)){
						if(in_array($fld, $sum_array)) $sumval_array[$fld] += floatval($item[$fld]);
					}

					if($row != "") $row .= ",";
					$row .= '"'.$item[$fld].'"';
				}
				$out .= $row."\n";
			}

			$out .= "\n";

			// optional sum row
			if(is_array($sum_array) && $sum_array[0] != ''){
				$row = "";
				$out .= '"TOTALS --->",';
				foreach($field_array as $field){
					$fld = trim($field);
					if(in_array($fld, $sum_array)){
						if($row != "") $row .= ",";
						$row .= '"'.$sumval[$fld].'"';
					}
				}
				$out .= $row."\n\r";
			}

			// finalize file
			$out .= "--END OF FILE--";
			$out .= "\n\r";

			$file = SITE_PATH.FILE_UPLOAD_FOLDER."export.csv";
			if(file_exists($file)) unlink($file);

			// Open file export.csv.
			$f = fopen ($file, 'w');

			// Put all values from $out to export.csv.
			fwrite($f, $out);
			fclose($f);

			// Call the CSV Exporter file which will output the CSV content
			echo "<script language=\"Javascript\">window.open('".WEB_URL.ADMIN_FOLDER.PLUGINS_FOLDER."exporters/exportcsv.php?title={$title}');</script>\n";
		}
	}
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

// ----------- ADMIN MENU FUNCTIONS ---------------

/**
 * Return a specific admin menu array element or all admin menus as a collection array
 * @param string $menucode
 * @return array
 */
function getAdminMenus($menucode = ''){
    $menus_json = getRec("settings", "value", "`name` = 'ADMIN_MENUS'", "", "1", "", true);
    if(isBlank($menus_json['value'])){
        $menus = array(
            "pages" => array("table" => "pages", "title" => "Pages", "tocategory" => false, "topage" => false, "alias" => "", "restricted" => false, "childmenus" => null),
            "events" => array("table" => "data_events", "title" => "Events", "tocategory" => false, "topage" => false, "alias" => "", "restricted" => false, "childmenus" => null),
            "photo_gallery" => array("table" => "data_photos_cat", "title" => "Photo Gallery", "tocategory" => true, "topage" => false, "alias" => "", "restricted" => false, "childmenus" => array(
                    "photos" => array("table" => "photos", "title" => "Galleries", "tocategory" => true, "topage" => false, "alias" => "", "restricted" => false)
                )),
            "projects" => array("table" => "data_projects", "title" => "Projects", "tocategory" => false, "topage" => false, "alias" => "", "restricted" => false, "childmenus" => null),
            "whats_new" => array("table" => "data_whatsnew", "title" => "What's New", "tocategory" => false, "topage" => false, "alias" => "", "restricted" => false, "childmenus" => null)
        );
    }else{
        $menus = json_decode($menus_json['value'], true);
    }
    $menus = setupAdminMenusTargets($menus);

    if($menucode != ''){
        if(isset($menus[$menucode])){
            return $menus[$menucode];
        }else{
            foreach($menus as $key => $menu){
                if(isset($menu[$menucode])) return $menu[$menucode];
            }
        }
    }
    return $menus;
}

/**
 * Generate target file paths for each admin menu and submenu
 * @param array $menus
 * @return array $menus
 */
function setupAdminMenusTargets($menus){
    foreach($menus as $key => $menu){
        $folder = preg_replace("/(^".DB_TABLE_PREFIX."|_cat)/i", "", $menu['table']);
        $filesuffix = preg_replace("/^".DB_TABLE_PREFIX."/i", "", $menu['table']);
        $fileprefix = 'list-';
        if(getIfSet($menu['tocategory']) == true && !preg_match("/_cat$/i", $menu['table'])) $filesuffix .= '_cat';
        if(!isblank($menu['alias'])) $filesuffix = $menu['alias'];
        if(getIfSet($menu['topage']) == true) $fileprefix = 'edit-';
        $menus[$key]['target'] = $folder."/".$fileprefix.$filesuffix.".php";
        if(!isblank($menu['childmenus'])){
            $menus[$key]['childmenus'] = setupAdminMenusTargets($menus[$key]['childmenus']);
        }
    }
    return $menus;
}

/**
 * Return the target URL for the specified menu
 * @param string $table
 * @param string $alias
 * @param string $targettype
 * @return string
 */
function getAdminMenuTarget($table, $alias, $targettype){
    if($table != ''){
        $folder = preg_replace("/(^".DB_TABLE_PREFIX."|_cat)/i", "", $table);
        $filesuffix = preg_replace("/^".DB_TABLE_PREFIX."/i", "", $table);
        $fileprefix = 'list-';
        if($targettype == 'tocategory' && !preg_match("/_cat$/i", $table)) $filesuffix .= '_cat';
        if(!isblank($alias)) $filesuffix = $alias;
        if($targettype == 'topage') $fileprefix = 'edit-';
        return $folder."/".$fileprefix.$filesuffix.".php";
    }else{
        return '';
    }
}

/**
 * Create a new admin menu and return parent menu list HTML
 * @param string $level
 * @param string $parentmenukey
 * @return string HTML output
 */
function addAdminMenu($level, $parentmenukey){
    $html = '';
    if(($level == 'top' && $parentmenukey == '') || ($parentmenukey != '' && $level == 'sub')){
        if(defined('IN_AJAX')) {
            $menus = getAdminMenus();
        }else{
            $menus = $_page->menus;
        }
        if(!isBlank($menus)){
            if($level == 'top'){
                foreach($menus as $key => $menu){
                    $menutitle = $menu['title'];
                    $html .= "<li class=\"unchosen\" id=\"setmenu_{$key}\"><a href=\"#\" class=\"adminmenu_topelem\" rel=\"{$key}\" title=\"Click to edit; drag to re-order\">{$menutitle}</a></li>\n";
                }
                $html .= "<li class=\"chosen\" id=\"setmenu_000\"><a href=\"#\" class=\"adminmenu_topelem\" rel=\"000\" title=\"Click to edit; drag to re-order\">- ... -</a></li>\n";
            }else{
                $topmenu = getIfSet($menus[$parentmenukey]);
                if(is_array($topmenu)){
                    if(is_array($topmenu['childmenus'])){
                        foreach($topmenu['childmenus'] as $skey => $menu){
                            $menutitle = $menu['title'];
                            $html .= "<li class=\"unchosen\" id=\"setsubmenu_{$skey}\"><a href=\"#\" class=\"adminmenu_subelem\" rel=\"{$skey}\" title=\"Click to edit; drag to re-order\">{$menutitle}</a></li>\n";
                        }
                    }
                    $html .= "<li class=\"unchosen\" id=\"setsubmenu_000\"><a href=\"#\" class=\"adminmenu_subelem\" rel=\"000\" title=\"Click to edit; drag to re-order\">- ... -</a></li>\n";
                }
            }
        }
    }
    return $html;
}

/**
 * Save admin menus layout and settings to database (triggered by menu sorting)
 * @param string $newlayout
 * @return boolean
 */
function updateAdminMenusLayout($newlayout){
    $ok = false;
    if(!isBlank($newlayout)){
        // get the currently saved layout
        $menus = getAdminMenus();

        // new layout is a comma-separated list of menu ids describing the
        // order set by the user. create an array out of it
        $newmenus = explode(",", $newlayout);

        // create new associations by assigning current menu elements to updmenus
        // in the order set by newmenus
        $updmenus = array();
        foreach($newmenus as $key){
            if(isset($menus[$key])) $updmenus[$key] = $menus[$key];
        }

        // save updmenus to database as json
        $updmenus_json = str_replace("'", "\'", json_encode($updmenus));
        $ok = replaceRec("settings", "`name` = 'ADMIN_MENUS', `value` = '".$updmenus_json."'", "`name` = 'ADMIN_MENUS'");

        if(!defined('IN_AJAX')) $_page->menus = $updmenus;
    }
    return $ok;
}

/**
 * Return Admin Menu Editor HTML form fields
 * @global type $_system
 * @global type $_page
 * @param type $menukey
 * @param type $parentmenukey
 * @param type $level
 * @return string
 */
function getAdminMenuEditorHTML($menukey, $parentmenukey, $level){
    global $_system, $_page;

    $outp = '';
    if(in_array($level, array("top", "sub"))){
        if(defined('IN_AJAX')) {
            $menus = getAdminMenus();
        }else{
            $menus = $_page->menus;
        }
        if(!isBlank($menus)){
            // prepare fields from saved data
            $menutables = array();
            $table = '';
            foreach($menus as $tkey => $topmenu){
                $menutables[] = $topmenu['table'];
                if($parentmenukey == '' && $tkey == $menukey) $table = $topmenu['table'];
                if(is_array($topmenu['childmenus'])){
                    foreach($topmenu['childmenus'] as $skey => $submenu){
                        $menutables[] = $submenu['table'];
                        if($parentmenukey != '' && $skey == $menukey) $table = $submenu['table'];
                    }
                }
            }
            $menutables = array_unique($menutables);
            $key = array_search($table, $menutables);
            if($key > 0) unset($menutables[$key]);

            $datatables = $_system->datatables;
            array_unshift($datatables, 'pages');
            $datatables = array_diff($datatables, $menutables);
            array_unshift($datatables, '- Unknown -');

            if(!isblank($menukey)){
                if($level == "top"){
                    $menu = getIfSet($menus[$menukey]);
                }else{
                    $menu = getIfSet($menus[$parentmenukey]['childmenus'][$menukey]);
                }
                if(count($menu) == 0) $menu = array("title" => "", "table" => "", "alias" => "", "tocategory" => 0, "topage" => 0, "restricted" => 0, "target" => "");
            }else{
                $menu = array("title" => "", "table" => "", "alias" => "", "tocategory" => 0, "topage" => 0, "restricted" => 0, "target" => "");
            }

            if($level == "top"){
                // table, title, tocategory, file alias, topage
                $outp = '<h3 class="header">Edit Top-Level Menu<input type="hidden" id="adminmenu_dirty" value="" /></h3>'.PHP_EOL;
                $outp.= '<div class="setlabel">Menu Title: <span class="hovertip" alt="The unique text displayed on the menu bar">[?]</span></div><div class="setdata">';
                $outp.= '<input type="text" id="adminmenu_title" name="adminmenu_title" value="'.$menu['title'].'" /><input type="hidden" id="adminmenu_code" name="adminmenu_code" value="'.$menukey.'" />';
                $outp.= '</div>'.PHP_EOL;

                if($menu['table'] != 'pages') {
                    $outp.= '<div class="setlabel">Table this Menu is Bound to<span class="red">*</span>: <span class="hovertip" alt="These are the available data tables">[?]</span></div><div class="setdata">';
                    $outp .= '<select id="adminmenu_table" name="adminmenu_table">'.PHP_EOL;
                    foreach($datatables as $table){
                        $sel = (($table == $menu['table']) ? ' selected="selected"' : '');
                        $key = (($table != '- Unknown -') ? $table : '');
                        $outp.= '<option value="'.$table.'"'.$sel.'>'.$table.'</option>'.PHP_EOL;
                    }
                    $outp.= '</select></div>'.PHP_EOL;
                    $outp.= '<div class="setlabel">File Alias: <span class="hovertip" alt="The optional file alias allows you to specify an alternate target URL for the menu.  If left blank, the system will use the data table to determine the target URL.<br/><br/>Just remember to leave off the \'list-\', \'edit-\', \'add-\', and \'_cat\' parts.">[?]</span></div><div class="setdata"><input type="text" id="adminmenu_filealias" name="adminmenu_filealias" value="'.getIfSet($menu['alias']).'" /></div>'.PHP_EOL;
                    $outp.= '<div class="setlabel">Menu Target Type: <span class="hovertip" alt="Choose whether clicking a menu goes to a data list, category list, or edit page">[?]</span></div><div class="setdata"><select id="adminmenu_target" name="adminmenu_target">'.PHP_EOL;
                    $outp.= '<option value=""'.(($menu['tocategory'] == 0 && $menu['topage'] == 0) ? ' selected="selected"' : '').'>To a data list [default]</option>'.PHP_EOL;
                    $outp.= '<option value="tocategory"'.(($menu['tocategory'] != 0) ? ' selected="selected"' : '').'>To a category list</option>'.PHP_EOL;
                    $outp.= '<option value="topage"'.(($menu['topage'] != 0) ? ' selected="selected"' : '').'>To an edit page</option>'.PHP_EOL;
                    $outp.= '</select></div>'.PHP_EOL;
                    $delbtn = '&nbsp;&nbsp;<a href="#" class="adminmenu_deltop">Delete Menu</a>';
                }else{
                    $outp.= '<div class="setlabel">Table this Menu is Bound to: <span class="hovertip" alt="The \'Page\' menu is always bound to the \'pages\' table">[?]</span></div><div class="setdata">';
                    $outp .= $menu['table'].'</div>'.PHP_EOL;
                    $delbtn = '';
                }
                $outp.= '<div class="setlabel">Derived Menu Target Path: <span class="hovertip" alt="This is the target URL based on the bound table, file alias, and target type settings">[?]</span></div><div class="setdata adminmenu_targeturl">'.$menu['target'].'</div>'.PHP_EOL;
                $outp.= '<div class="setlabel">Restricted Access?: <span class="hovertip" alt="If set, users must be allowed to \'view restricted menus\' to access it.">[?]</span></div><div class="setdata"><input type="checkbox" id="adminmenu_restricted" name="adminmenu_restricted" value="1"'.((getIfSet($menu['restricted'])) ? ' checked="checked"' : '').' /> Yes, viewable only to users allowed to \'view restricted menus\'</div>'.PHP_EOL;
                $outp.= '<div class="setlabel"></div><div class="setdata"><input type="button" id="adminmenu_savetop" value="Save Changes" />'.$delbtn.'</div>'.PHP_EOL;
            }else{
                // parenttable, table, title, file alias
                $outp = '<h3 class="header">Edit Sub-Level Menu<input type="hidden" id="adminmenu_dirty" value="" /></h3>'.PHP_EOL;
                $outp.= '<div class="setlabel">Menu Title: <span class="hovertip" alt="The unique text displayed on the menu bar">[?]</span></div><div class="setdata">';
                $outp.= '<input type="text" id="adminmenu_title" name="adminmenu_title" value="'.$menu['title'].'" /><input type="hidden" id="adminmenu_code" name="adminmenu_code" value="'.$menukey.'" /><input type="hidden" id="adminmenu_parent" name="adminmenu_parent" value="'.$parentmenukey.'" />';
                $outp.= '</div>'.PHP_EOL;
                $outp.= '<div class="setlabel">Table this Menu is Bound to<span class="red">*</span>: <span class="hovertip" alt="These are the available data tables">[?]</span></div><div class="setdata">';
                $outp .= '<select id="adminmenu_table" name="adminmenu_table">'.PHP_EOL;
                foreach($datatables as $table){
                    $sel = (($table == $menu['table']) ? ' selected="selected"' : '');
                    $key = (($table != '- Unknown -') ? $table : '');
                    $outp.= '<option value="'.$table.'"'.$sel.'>'.$table.'</option>'.PHP_EOL;
                }
                $outp.= '</select></div>'.PHP_EOL;
                $outp.= '<div class="setlabel">File Alias: <span class="hovertip" alt="The optional file alias allows you to specify an alternate target URL for the menu.  If left blank, the system will use the data table to determine the target URL.<br/><br/>Just remember to leave off the \'list-\', \'edit-\', \'add-\', and \'_cat\' parts.">[?]</span></div><div class="setdata"><input type="text" id="adminmenu_filealias" name="adminmenu_filealias" value="'.getIfSet($menu['alias']).'" /></div>'.PHP_EOL;
                $outp.= '<div class="setlabel">Derived Menu Target Path: <span class="hovertip" alt="This is the target URL based on the bound table, file alias, and target type settings">[?]</span></div><div class="setdata adminmenu_targeturl">'.$menu['target'].'</div>'.PHP_EOL;
                $outp.= '<div class="setlabel">Restricted Access?: <span class="hovertip" alt="If set, users must be allowed to \'view restricted menus\' to access it.">[?]</span></div><div class="setdata"><input type="checkbox" id="adminmenu_restricted" name="adminmenu_restricted" value="1"'.((getIfSet($menu['restricted'])) ? ' checked="checked"' : '').' /> Yes, viewable only to users allowed to \'view restricted menus\'</div>'.PHP_EOL;
                $outp.= '<div class="setlabel"></div><div class="setdata"><input type="button" id="adminmenu_savesub" value="Save Changes" />&nbsp;&nbsp;<a href="#" class="adminmenu_delsub">Delete Menu</a></div>'.PHP_EOL;
            }
        }else{
            // prepare fields with no data
        }
    }
    return $outp;
}

/**
 * Return the sub-level menu listitems (<ul>...</ul>) for a top-level menu
 * @param string $menukey
 * @return string
 */
function getAdminMenuEditorSubMenu($menukey){
    global $_system, $_page;

    $outp = '';
    if(!isblank($menukey)){
        if(defined('IN_AJAX')) {
            $menu = getAdminMenus($menukey);
        }else{
            $menus = $_page->menus;
            $menu = getIfSet($menus[$menukey]);
        }
        if(!isBlank($menu['childmenus'])){
            $outp = "";
            foreach($menu['childmenus'] as $key => $submenu){
                $submenutitle = $submenu['title'];
                if($submenu['restricted']) $submenutitle = '['.$submenutitle.']';
                $chosen = (($outp == "") ? "chosen" : "unchosen");
                $outp .= "<li class=\"{$chosen}\" id=\"setsubmenu_".$key."\"><a href=\"#\" class=\"adminmenu_subelem\" rel=\"{$menukey}:{$key}\" title=\"Click to edit; drag to re-order\">{$submenutitle}</a></li>\n";
            }
        }
    }
    return $outp;
}

/**
 * Save menu data to database
 * @param string $level
 * @param string $key
 * @param string $parent
 * @param string $title
 * @param string $table
 * @param string $targettype
 * @param string $alias
 * @param boolean $restricted
 * @return boolean
 */
function saveAdminMenu($level, $key, $parent, $title, $table, $targettype, $alias, $resticted){
    global $_system, $_page;

    $ok = false;
    if(!isblank($level) && !isblank($table) && !isblank($title)){
        if(defined('IN_AJAX')) {
            $menus = getAdminMenus();
        }else{
            $menus = $_page->menus;
        }
        $updated = false;

        if(!isblank($key)){
            if($level == "top"){
                if(isset($menus[$key])){
                    $childmenus = $menus[$key]['childmenus'];
                    $target = $menus[$key]['target'];
                    $menus[$key] = array(
                        "table" => $table,
                        "title" => $title,
                        "tocategory" => ($targettype == "tocategory"),
                        "topage" => ($targettype == "topage"),
                        "alias" => $alias,
                        "target" => $target,
                        "restricted" => (bool)$resticted,
                        "childmenus" => $childmenus
                    );
                    $updated = true;
                }
            }elseif(!isblank($parent)){
                if(isset($menus[$parent]['childmenus'][$key])){
                    $target = $menus[$parent]['childmenus'][$key]['target'];
                    $menus[$parent]['childmenus'][$key] = array(
                        "table" => $table,
                        "title" => $title,
                        "alias" => $alias,
                        "target" => $target,
                        "restricted" => (bool)$resticted
                    );
                    $updated = true;
                }
            }
        }else{
            if($level == "top"){
                $target = getAdminMenuTarget($table, $alias, $targettype);
                $key = codify($title);
                $indx = "";
                while(isset($menus[$key.$indx])) {
                    $indx = (($indx == "") ? 1 : $indx + 1);
                }
                $key .= $indx;
                $menus[$key] = array(
                    "table" => $table,
                    "title" => $title,
                    "tocategory" => ($targettype == "tocategory"),
                    "topage" => ($targettype == "topage"),
                    "alias" => $alias,
                    "target" => $target,
                    "restricted" => (bool)$resticted,
                    "childmenus" => null
                );
                $updated = true;
            }elseif(!isblank($parent)){
                if(isset($menus[$parent])){
                    $target = getAdminMenuTarget($table, $alias, $targettype);
                    $key = codify($title);
                    $indx = "";
                    while(isset($menus[$key]['childmenus'][$key.$indx])) {
                        $indx = (($indx == "") ? 1 : $indx + 1);
                    }
                    $key .= $indx;
                    $menus[$parent]['childmenus'][$key] = array(
                        "table" => $table,
                        "title" => $title,
                        "alias" => $alias,
                        "target" => $target,
                        "restricted" => (bool)$resticted
                    );
                    $updated = true;
                }
            }
        }

        if($updated){
            $menus_json = str_replace("'", "\'", json_encode($menus));
            $ok = updateRec("settings", "`value` = '".$menus_json."'", "`name` = 'ADMIN_MENUS'");
        }
    }
    return array($ok, $key);
}

function deleteAdminMenu($key, $level, $parent){
    global $_system, $_page;

    $ok = false;
    if(!isblank($level)){
        if(defined('IN_AJAX')) {
            $menus = getAdminMenus();
        }else{
            $menus = $_page->menus;
        }

        if(!isblank($key)){
            if($level == 'top'){
                if(isset($menus[$key])){
                    unset($menus[$key]);
                    $ok = true;
                }
            }elseif(!isblank($parent)){
                if(isset($menus[$parent]['childmenus'][$key])){
                    unset($menus[$parent]['childmenus'][$key]);
                    $ok = true;
                }
            }
        }

        if($ok){
            $menus_json = str_replace("'", "\'", json_encode($menus));
            $ok = updateRec("settings", "`value` = '".$menus_json."'", "`name` = 'ADMIN_MENUS'");
        }
    }
    return $ok;
}
?>