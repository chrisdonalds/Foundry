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

define ("FLD_DATA", 0);
define ("FLD_LABEL", 1);
define ("FLD_OPENROW", 2);
define ("FLD_CLOSEROW", 4);
define ("FLD_ALL", 8);
define ("FLD_SIMPLE", 16);

define ("BLOCK_DIV", 1);
define ("BLOCK_SPAN", 2);
define ("BLOCK_P", 3);

define ("MENU_DAY", "day");
define ("MENU_DOW", "dow");
define ("MENU_SHORTDOW", "sdow");
define ("MENU_MONTH", "month");
define ("MENU_SHORTMONTH", "smonth");
define ("MENU_YEAR", "year");
define ("MENU_ALPHA", "alpha");
define ("MENU_NUM", "number");
define ("MENU_ONOFF", "onoff");
define ("MENU_YESNO", "yesno");
define ("MENU_STATUS", "status");

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
	showHiddenField("base_url", WEB_URL.ADMIN_FOLDER);
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
								$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}>Add ".(($alias == "") ? "" : $alias)."</a>";
								break;
							case DEF_ACTION_EDIT:
								if(!$is_deleted) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">Edit".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_EDITFORM:
								if(!$is_deleted) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"blue\">Edit".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_EDITMETA:
								if(!$is_deleted) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}>Edit ".(($alias == "") ? "Meta-Data" : $alias)."</a>";
								break;
							case DEF_ACTION_DELETE:
								if(!$is_deleted && !$is_locked && !$is_protected && ALLOW_DELETE) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"red\">Delete".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_UNDELETE:
								if($is_deleted && !$is_locked && ALLOW_DELETE) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}>Un-Delete".(($alias == "") ? "" : $alias)."</a>";
								break;
							case DEF_ACTION_PUBLISH:
								if(!$is_published && !$is_deleted && !$is_archived && ALLOW_PUBLISH) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"bold\">Publish".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_UNPUBLISH:
								if($is_published && !$is_deleted && !$is_archived && ALLOW_PUBLISH) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"bold\">Un-Publish".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_ACTIVATE:
								if(!$is_activated && !$is_deleted && !$is_archived && ALLOW_PUBLISH) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"bold\">Activate".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_DEACTIVATE:
								if($is_activated && !$is_deleted && !$is_archived && ALLOW_PUBLISH) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"bold\">Deactivate".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_ARCHIVE:
								if(!$is_archived && !$is_deleted && ALLOW_ARCHIVE) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}>Archive".(($alias == "") ? "" : $alias)."</a>";
								break;
							case DEF_ACTION_UNARCHIVE:
								if($is_archived && !$is_deleted && ALLOW_ARCHIVE) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}>Un-Arc".(($alias == "") ? "" : $alias)."</a>";
								break;
							case DEF_ACTION_SUBSCRIBE:
								if(!$is_subscribed && !$is_deleted) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"bold\">Subscribe".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_UNSUBSCRIBE:
								if($is_subscribed && !$is_deleted) $stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}><span class=\"bold\">Un-Subscribe".(($alias == "") ? "" : $alias)."</span></a>";
								break;
							case DEF_ACTION_VIEW:
								$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}>View ";
								$stub .= (!isBlank($butlabelstr[1])) ? $butlabelstr[1] : (($alias == "") ? "" : $alias);
								$stub.= "</a>";
								break;
							case DEF_ACTION_VIEWRECS:
								$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}>View ";
								$stub .= (!isBlank($butlabelstr[1])) ? $butlabelstr[1] : (($alias == "") ? "List" : $alias);
								$stub.= "</a>";
								break;
							case DEF_ACTION_VIEWPAGES:
								$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}>View ";
								$stub .= (!isBlank($butlabelstr[1])) ? $butlabelstr[1] : (($alias == "") ? "Pages" : $alias);
								$stub.= "</a>";
								break;
							case DEF_ACTION_OPEN:
								$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}>Switch To".(($alias == "") ? "" : $alias)."</a>";
								break;
							case DEF_ACTION_REPLY:
								if(!$is_replied) {
									$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}>".(($alias == "") ? "View/Reply" : $alias)."</a>";
								}else{
									$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}>".(($alias == "") ? "View" : $alias)."</a>";
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
								$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}>Clone</a>";
								break;
							case DEF_ACTION_EXPORT:
								$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}>Export".(($alias == "") ? "" : $alias)."</a>";
								break;
							case DEF_ACTION_DEFAULT:
								if($is_published && !$is_deleted) {
									if(!$is_gallerydef) {
										$stub = "<a href=\"#\" class=\"action_$label\"{$rel}{$tag}>Set as ".(($alias == "") ? " Default" : $alias)."</a>";
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

// ----------- FIELD FUNCTIONS ---------------

/**
 * Begin the main area in which all other editor and button content is displayed
 */
function startContentArea(){
	showBlock("", "contentarea", "", BLOCK_DIV, "", "", FLD_OPENROW);
}

/**
 * Begin the button area which contains back, save, preview and status buttons
 */
function startButtonBlock(){
	showBlock("", "", "editor_buttonbox", BLOCK_DIV, "", "", FLD_OPENROW);
}

/**
 * Begin the editor area where user record data is entered
 */
function startEditorBlock($id){
	showBlock("", $id, "fullwidth", BLOCK_DIV, "", "", FLD_OPENROW);
}

/**
 * Displays a DIV, SPAN or P block.  Options include: style, javascript, and whether or not to close block
 * @param string $text
 * @param string $id
 * @param string $class
 * @param string $blocktype
 * @param string $style
 * @param string $js
 * @param integer $displaytype
 */
function showBlock($text, $id="", $class="", $blocktype = BLOCK_DIV, $style="", $js="", $displaytype = FLD_ALL){
	if($style != "") $style = " style=\"{$style}\"";
	if($id != "") $id = " id=\"{$id}\"";
	if($class != "") $class = " class=\"{$class}\"";
	if($blocktype == BLOCK_SPAN){
		print "<span{$id}{$class}{$style}{$js}>\n";
		if($text != "") print $text."\n";
		if($displaytype == FLD_ALL) print "</span>\n";
	}elseif($blocktype == BLOCK_DIV){
		print "<div{$id}{$class}{$style}{$js}>\n";
		if($text != "") print $text."\n";
		if($displaytype == FLD_ALL) print "</div>\n";
	}elseif($blocktype == BLOCK_P){
		print "<p{$id}{$class}{$style}{$js}>\n";
		if($text != "") print $text."\n";;
		if($displaytype == FLD_ALL) print "</p>\n";
	}else{
		print $text;
	}
}

/**
 * NEW: Display field using parameter array
 * @param string $type Field type
 * @param array $atts Field parameters presented in array format
 * @version 3.9.5
 */
function showObject($type, $atts){
	foreach($atts as $key => $attr) ${strtolower($key)} = $attr;

	// defaults

	$label = getIfSet($label);
	$id = getIfSet($id);
	$class = getIfSet($class);
	$js = getIfSet($js);
	$style = getIfSet($style);
	$text = getIfSet($text);
	$selectedvalue = getIfSet($selectedvalue);
	$wrappertext = getIfSet($wrappertext);
	$fldclass = getIfSet($fldclass);
	$optjs = getIfSet($optjs);
	$spanclass = getIfSet($spanclass);
	$spanclassarray = getIfSet($spanclassarray);
	$jsarray = getIfSet($jsarray);
	$labelclass = getIfSet($labelclass);
    $disablevalue = getIfSet($disablevalue);

    // defaults
	if(!isset($displaytype)) $displaytype = FLD_ALL;
	if(!isset($maxlen)) $maxlen = 0;
	if(!isset($blocktype)) $blocktype = BLOCK_DIV;
	if(!isset($separator)) $separator = "&nbsp;";
	if(!isset($separator)) $separator = "&nbsp;";
	if(!isset($maxlen)) $maxlen = 0;
	if(!isset($multiple)) $multiple = false;
	if(!isset($size)) $size = 1;
	if(!isset($titlerowid)) $titlerowid = "maptitlerow";
	if(!isset($boxrowid)) $boxrowid = "mapboxrow";
	if(!isset($inclhead)) $inclhead = true;
	if(!isset($toolbar)) $toolbar = "FormatOnly";
	if(!isset($noname)) $noname = false;
	if(!isset($dim)) $dim = "";
	if((!isset($fldclass) || strpos($fldclass, "fldsize") === false) && $type != 'button' && $type != 'checkbox') {
		if($type != "textarea"){
			$fldclass .= " widefldsize";
		}else{
			$fldclass .= " fullfldsize";
		}
	}

	switch (strtolower($type)){
		case "address":
			showAddressBox($label, $colids, $collabels, $colvals, $js, $labelclass, $fldclass, $displaytype);
			break;
		case "block":
			showBlock($text, $id, $class, $blocktype, $style, $js, $displaytype);
			break;
		case "button":
			showButtonField($label, $id, $value, $wrappertext, $js, $labelclass, $fldclass, $displaytype);
			break;
		case "checkbox":
			showCheckbox($label, $id, $value, $chkstate, $text, $separator, $wrappertext, $js, $labelclass, $fldclass, $spanclass, $displaytype);
			break;
		case "checkboxlist":
			showCheckboxList($label, $id, $selectionarray, $chosenvaluesarray, $separator, $jsarray, $labelclass, $fldclass, $spanclassarray, $displaytype);
			break;
		case "custom":
			showCustomField($label, $contents, $wrappertext, $labelclass, $displaytype);
			break;
		case "file":
			showFileField($label, $ids, $value, $folder, $type, $wrappertext, $js, $labelclass, $fldclass, $displaytype);
			break;
		case "hidden":
			showHiddenField($id, $value, $noname);
			break;
		case "htmleditor":
		case "html":
		case "editor":
			if(!isset($cols)) $cols = 20;
			if(!isset($rows)) $rows = 15;
			showHTMLEditorField($label, $id, $value, $cols, $rows, $dim, $toolbar, $maxlen, $wrappertext, $js, $labelclass, $fldclass, $displaytype);
			break;
		case "image":
			showImageField($label, $ids, $values, $displayed, $folder, $wrappertext, $js, $labelclass, $fldclass, $displaytype);
			break;
		case "instructions":
			showInstructions($text, $inclhead);
			break;
		case "label":
			showLabel($label, $labelclass, $text);
			break;
		case "map":
		case "mapbox":
			$lat = floatval($lat);
			$lon = floatval($lon);
			showMapBox($label, $lat, $lon, $titlerowid, $boxrowid, $coordhint, $js, $displaytype);
			break;
		case "menu":
		case "list":
		case "select":
		case "selectmenu":
			showMenu($label, $id, $valuearray, $selectedvalue, $size, $multiple, $disablevalue, $wrappertext, $js, $optjs, $labelclass, $fldclass, $displaytype);
			break;
		case "pagetitle":
			showPageTitle($title);
			break;
		case "password":
			$fldclass .= " passwordclass";
			showPasswordField($label, $id, $value, $maxlen, $wrappertext, $js, $labelclass, $fldclass, $displaytype);
			break;
		case "radiolist":
			showRadioList($label, $id, $selectionarray, $chosenvaluesarray, $textarray, $separator, $js, $labelclass, $fldclass, $spanclassarray, $displaytype);
			break;
		case "textarea":
			if(!isset($cols)) $cols = 75;
			if(!isset($rows)) $rows = 7;
			showTextareaField($label, $id, $value, intval($cols), intval($rows), intval($maxlen), $wrappertext, $js, $labelclass, $fldclass, $displaytype);
			break;
		case "text":
		case "textbox":
			showTextField($label, $id, $value, intval($maxlen), $wrappertext, $js, $labelclass, $fldclass, $displaytype);
			break;
		default:
			if(function_exists("show".$type)) $more = "  Perhaps you should try just 'show{$type}'.";
			addErrorMsg("ShowObject: Unknown type '$type'.{$more}");
			break;
	}
}

/**
 * Displays a Page Title DIV block
 * @param string $text
 */
function showPageTitle($title){
	global $_page;

	$_page->title = $title;
	print "<div id=\"title\">$title</div>\n";
	showErrorMsg();
}

/**
 * Starts the page table
 * @param string $id
 * @param string $class
 * @param string $style
 * @param string $js
 */
function startTable($id = "", $class = "", $style = "", $js = ""){
	if($class != '') $class = " class=\"{$class}\"";
	if($id != '') $id = " id=\"{$id}\"";
	if($style != '') $style = " style=\"{$style}\"";
	echo "<div{$id}{$class}{$style}{$js}>\n";
}

/**
 * Ends last table
 */
function endTable(){
	echo "</div>\n";
}

/**
 * Displays Admin label row.  Usually followed with showRowStart or other show{Field} call
 * @param string $label
 * @param string $labelclass
 */
function showLabel($label, $labelclass = "", $text = ""){
    if($labelclass != "") $labelclass = " {$labelclass}";
    (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";

    print "<div class=\"editlabel{$labelclass}\">{$label}</div>";
    if($text != "") {
        print "<div class=\"editfield\">$text</div>\n";
    }
}
/**
 * Displays the 'Required Entry' text
 */
function showReqdText(){
	echo REQD_ENTRY." = Required Entry\n";
}

/**
 * Displays Admin field row start tags (<div class=\"editfield\">)
 */
function showRowStart(){
	print "<div class=\"editfield\">\n";
}

/**
 * Closes a previously opened Admin field row (</div>)
 */
function showRowEnd(){
	print "</div>\n";
}

/**
 * Displays Admin text input field
 * @param string $label
 * @param string $id
 * @param string $value
 * @param integer $maxlen
 * @param string $aftertext
 * @param string $js
 * @param string $labelclass
 * @param string $fldclass
 * @param string $displaytype
 */
function showTextField($label, $id, $value, $maxlen = 0, $wrappertext = "", $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL){
	if(strpos($label, "*") !== false) $fldclass = trim("required ".$fldclass);
	if($label != '') (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";
	if($labelclass != "") $labelclass = " class=\"{$labelclass}\"";
	if($fldclass != "") $fldclass = " class=\"{$fldclass}\"";
	if($js != "" && substr($js, 0, 1) != " ") $js = " ".$js;
	if($maxlen > 0) {
		$maxlen = " maxlength=\"{$maxlen}\" onKeyUp=\"countChars('{$id}', '{$id}_count', false, {$maxlen})\" onFocus=\"countChars('{$id}', '{$id}_count')\"";
		$maxlen_countfld = "<input type=\"text\" id=\"{$id}_count\" size=\"2\" value=\"".strlen($value)."\" disabled=\"disabled\" />\n";
	}elseif($maxlen < 0) {
		$maxlen = " maxlength=\"".abs($maxlen)."\"";
		$maxlen_countfld = "";
	}else{
		$maxlen = "";
		$maxlen_countfld = "";
	}
	$wrapperElems = parseAttributes($wrappertext, array("before", "after", "help"));

	if($displaytype != FLD_DATA && $displaytype != FLD_CLOSEROW) print "<div class=\"editlabel {$labelclass}\">{$label}</div>\n";
	if($displaytype == FLD_ALL || $displaytype == FLD_OPENROW) print "<div class=\"editfield\">";
	if($displaytype != FLD_LABEL) {
		print $wrapperElems['before'];
		if(strpos($id, "price") !== false) print "$ ";
		print "<input type=\"text\" id=\"{$id}\" name=\"{$id}\" value=\"{$value}\"{$maxlen}{$fldclass}{$js} />\n";
		print $maxlen_countfld.$wrapperElems['after'];
	}
	if($displaytype == FLD_ALL || $displaytype == FLD_CLOSEROW) print "</div>\n";
	if($wrapperElems['help'] != '') print "<div class=\"edithelp\">".$wrapperElems['help']."</div>\n";
}

/**
 * Displays Admin textarea input field
 * @param string $label
 * @param string $id
 * @param string $value
 * @param integer $cols
 * @param integer $rows
 * @param integer $height
 * @param string $toolbar
 * @param integer $maxlen
 * @param string $js
 * @param string $labelclass
 * @param string $fldclass
 */
function showTextareaField($label, $id, $value, $cols = 75, $rows = 7, $maxlen = 0, $wrappertext = "", $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL){
	if(strpos($label, "*") !== false) $fldclass = trim("required ".$fldclass);
	if($labelclass != "") $labelclass = " class=\"{$labelclass}\"";
	if($fldclass != "") $fldclass = " class=\"{$fldclass}\"";
	if($js != "" && substr($js, 0, 1) != " ") $js = " ".$js;
	if($maxlen > 0) {
		$maxlen = " onKeyUp=\"countChars('{$id}', '{$id}_count', false, {$maxlen})\" onFocus=\"countChars('{$id}', '{$id}_count')\"";
		$maxlen_countfld = "<input type=\"text\" id=\"{$id}_count\" size=\"2\" value=\"".strlen($value)."\" disabled=\"disabled\" />";
	}else{
		$maxlen = "";
		$maxlen_countfld = "";
	}
	if($label != '') (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";
	$wrapperElems = parseAttributes($wrappertext, array("before", "after", "help"));

	if($displaytype != FLD_DATA && $displaytype != FLD_CLOSEROW) print "<div class=\"editlabel {$labelclass}\">{$label}</div>\n";
	if($displaytype == FLD_ALL || $displaytype == FLD_OPENROW) print "<div class=\"editfield\">";
	if($displaytype != FLD_LABEL) print<<<EOT
{$wrapperElems['before']}<textarea id="{$id}" name="{$id}" cols="{$cols}" rows="{$rows}"{$maxlen}{$fldclass}{$js}>{$value}</textarea>{$wrapperElems['after']}
{$maxlen_countfld}
EOT;
	if($displaytype == FLD_ALL || $displaytype == FLD_CLOSEROW) print "</div>\n";
	if($wrapperElems['help'] != '') print "<div class=\"edithelp\">".$wrapperElems['help']."</div>\n";
}

/**
 * Displays CKEditor-powered Admin CMS HTML editor box
 * @param string $label
 * @param string $id
 * @param string $value
 * @param integer $cols
 * @param integer $rows
 * @param array $dim
 * @param string $toolbar
 * @param integer $maxlen
 * @param string $aftertext
 * @param string $js
 * @param string $labelclass
 * @param string $fldclass
 * @param integer $displaytype
 */
function showHTMLEditorField($label, $id, $value, $cols = 20, $rows = 15, $dim = "", $toolbar = "FormatOnly", $maxlen = 0, $wrappertext = "", $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL){
	if(strpos($label, "*") !== false) $fldclass = trim("required ".$fldclass);
	if($labelclass != "") $labelclass = " class=\"{$labelclass}\"";
	if($fldclass != "") $fldclass = " class=\"{$fldclass}\"";
	if($js != "" && substr($js, 0, 1) != " ") $js = " ".$js;
	if($label != '') (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";
	if($_SESSION['admuserlevel'] <= ADMLEVEL_SYSADMIN && strpos($toolbar, "WithSource") === false) $toolbar .= "WithSource";
	($maxlen > 0) ? $maxlentext = "	var cktimer = setInterval(\"countChars(CKEDITOR.instances.{$id}, '{$id}count', false, {$maxlen})\", 500);" : $maxlentext = "";
	$ht = "";
	$wd = "";
	if(is_array($dim)) {
		$ht = (($dim[1] > 0) ? 'height  : '.$dim[1].(($dim[0] > 0) ? ','.PHP_EOL : '') : '');
		$wd = (($dim[0] > 0) ? 'width   : '.$dim[0].PHP_EOL : '');
	}
	$toolbar = "'{$toolbar}'".(($ht != '' || $wd != '') ? ','.PHP_EOL : '');
	$wrapperElems = parseAttributes($wrappertext, array("before", "after", "help"));

	if($displaytype != FLD_DATA && $displaytype != FLD_CLOSEROW) print "<div class=\"editlabel {$labelclass}\">{$label}</div>\n";
	if($displaytype == FLD_ALL || $displaytype == FLD_OPENROW) print "<div class=\"editfield\">";
	if($displaytype != FLD_LABEL) print<<<EOT
{$wrapperElems['before']}<textarea id="{$id}" name="{$id}" cols="{$cols}" rows="{$rows}"{$fldclass}{$js}>{$value}</textarea>{$wrapperElems['after']}
<script type="text/javascript">
    //<![CDATA[
    	CKEDITOR.replace( '{$id}',
		{
    		toolbar : {$toolbar}{$ht}{$wd}
		});
	//]]>
	{$maxlentext}
</script>
<noscript>
    <p><strong>The editor requires JavaScript to run</strong>. In a browser with no JavaScript
    support, like yours, you should still see the contents (HTML data).  However, you will
    only be able to edit it normally, without the benefits of rich editor features.</p>
</noscript>
EOT;
	if($displaytype == FLD_ALL || $displaytype == FLD_CLOSEROW) print "</div>\n";
	if($wrapperElems['help'] != '') print "<div class=\"edithelp\">".$wrapperElems['help']."</div>\n";
}

/**
 * Displays Admin password input field
 * @param string $label
 * @param string $id
 * @param string $value
 * @param integer $maxlen
 * @param string $aftertext
 * @param string $js
 * @param string $labelclass
 * @param string $fldclass
 * @param integer $displaytype
 */
function showPasswordField($label, $id, $value, $maxlen = 0, $wrappertext = "", $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL){
	if(strpos($label, "*") !== false) $fldclass = trim("required ".$fldclass);
	if($labelclass != "") $labelclass = " class=\"{$labelclass}\"";
	if($fldclass != "") $fldclass = " class=\"{$fldclass}\"";
	if($js != "" && substr($js, 0, 1) != " ") $js = " ".$js;
	($maxlen > 0) ? $maxlen = " maxlength=\"{$maxlen}\"" : $maxlen = "";
	if($label != '') (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";
	$wrapperElems = parseAttributes($wrappertext, array("before", "after", "help"));

	if($displaytype != FLD_DATA && $displaytype != FLD_CLOSEROW) print "<div class=\"editlabel {$labelclass}\">{$label}</div>\n";
	if($displaytype == FLD_ALL || $displaytype == FLD_OPENROW) print "<div class=\"editfield\">";
	if($displaytype != FLD_LABEL) {
		(strpos($id, "*") !== false) ? $imgid = "pass" : $imgid = "cpass";
		$id = str_replace("*", "", $id);
		print $wrapperElems['before'];
		print "<input type=\"password\" id=\"{$id}\" name=\"{$id}\" value=\"{$value}\"{$maxlen}{$fldclass}{$js} onchange=\"showpswdicon('{$id}')\" />";
		print "&nbsp;<img id=\"{$imgid}ok\" src=\"".WEB_URL.ADMIN_FOLDER.IMG_FOLDER."icons/passok.png\" height=\"16\" width=\"16\" alt=\"\" /> <img id=\"{$imgid}bad\" src=\"".WEB_URL.ADMIN_FOLDER.IMG_FOLDER."icons/passbad.png\" height=\"16\" width=\"16\" alt=\"\" />";
		print $wrapperElems['after']."\n";
	}
	if($displaytype == FLD_ALL || $displaytype == FLD_CLOSEROW) print "</div>\n";
	if($wrapperElems['help'] != '') print "<div class=\"edithelp\">".$wrapperElems['help']."</div>\n";
}

/**
 * Displays Admin button
 * @param string $label
 * @param string $id
 * @param string $value
 * @param string $js
 * @param string $labelclass
 * @param string $fldclass
 * @param integer $displaytype
 */
function showButtonField($label, $id, $value, $wrappertext = "", $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_DATA){
	if(strpos($label, "*") !== false) $fldclass = trim("required ".$fldclass);
	if($labelclass != "") $labelclass = " class=\"{$labelclass}\"";
	if($fldclass != "") $fldclass = " class=\"{$fldclass}\"";
	if($js != "" && substr($js, 0, 1) != " ") $js = " ".$js;
	if($label != '') (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";
	$wrapperElems = parseAttributes($wrappertext, array("before", "after", "help"));

	if($displaytype != FLD_DATA && $displaytype != FLD_CLOSEROW) print "<div class=\"editlabel {$labelclass}\">{$label}</div>\n";
	if($displaytype == FLD_ALL || $displaytype == FLD_OPENROW) print "<div class=\"editfield\">";
	if($displaytype != FLD_LABEL) print $wrapperElems['before']."<input type=\"button\" id=\"{$id}\" name=\"{$id}\" value=\"{$value}\"{$fldclass}{$js} />".$wrapperElems['after']."\n";
	if($displaytype == FLD_ALL || $displaytype == FLD_CLOSEROW) print "</div>\n";
	if($wrapperElems['help'] != '') print "<div class=\"edithelp\">".$wrapperElems['help']."</div>\n";
}

/**
 * Displays Admin image field.  Prepares field as a FileUploader image field if FileUploader plugin initialized
 * @param string $label
 * @param string $ids
 * @param array $values
 * @param array $displayed
 * @param string $folder
 * @param string $aftertext
 * @param string $js
 * @param string $labelclass
 * @param string $fldclass
 * @param integer $displaytype
 */
function showImageField($label, $ids, $values, $displayed, $folder, $wrappertext = "", $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL){
	global $incl;

	if(strpos($label, "*") !== false) $fldclass = trim("required ".$fldclass);
	if($labelclass != "") $labelclass = " class=\"{$labelclass}\"";
	if($fldclass != "") $fldclass = " class=\"{$fldclass}\"";
	if($js != "" && substr($js, 0, 1) != " ") $js = " ".$js;
	$sublabel = str_replace("*", "", $label);
	$label .= " (JPG, GIF, PNG Only)";
	if($label != '') (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";
	$fs_image = $values[0];
	$fs_thumb = $values[1];
	$wrapperElems = parseAttributes($wrappertext, array("before", "after", "help"));

	if($displaytype != FLD_SIMPLE){
		if($displaytype != FLD_DATA && $displaytype != FLD_CLOSEROW) print "<div class=\"editlabel {$labelclass}\">{$label}</div>\n";
		if($displaytype == FLD_ALL || $displaytype == FLD_OPENROW) print "<div class=\"editfield\">";
		if($displaytype != FLD_LABEL) {
			print "<input type=\"hidden\" id=\"{$ids[1]}\" name=\"{$ids[1]}\" value=\"{$fs_image}\" />\n";
			print "<input type=\"hidden\" id=\"{$ids[2]}\" name=\"{$ids[2]}\" value=\"{$fs_thumb}\" />\n";

			if ($fs_image != "" && $displayed[0]) {
				if(substr($folder, -1, 1) != "/") $folder .= "/";
				$photo_pic	= checkImagePath($fs_image, IMG_UPLOAD_FOLDER.$folder, "");
				list($width, $height, $origwidth, $origheight) = constrainImage(SITE_PATH.$photo_pic, 480);
				print " - Existing {$sublabel}: ".basename($fs_image);
				if($ids[3] != "") print "&nbsp;&nbsp;<input type=\"checkbox\" id=\"{$ids[3]}\" name=\"{$ids[3]}\" /> Delete (Cannot be undeleted)";
				print "<br/>- Size: (".intval($origwidth)." x ".intval($origheight)." pixels)";
				print "<div id=\"gallery\">";
				print "<a href=\"".WEB_URL.$photo_pic."\" title=\"$fs_image\">";
				print "<img src=\"".WEB_URL."$photo_pic\" border=\"1\" width=\"$width\" height=\"$height\" />";
				print "</a>";
				print "</div>\n";
			}
			if ($fs_thumb != "" && $displayed[1]) {
				$photo_pic	= checkThumbPath($fs_thumb, THM_UPLOAD_FOLDER.$folder, "");
				list($width, $height) = @getimagesize(SITE_PATH.$photo_pic);
				print "<br/> - Thumbnail";
				if($ids[3] != "" && !$displayed[0]) print "&nbsp;&nbsp;<input type=\"checkbox\" id=\"{$ids[3]}\" name=\"{$ids[3]}\" /> Delete (Cannot be undeleted)";
				print "<br/><img src=\"".WEB_URL.$photo_pic."\" border=\"1\" width=\"{$width}\" height=\"{$height}\" />";
			}

			$inputfld = '<input type="file" id="'.$ids[0].'" name="'.$ids[0].'"'.$fldclass.$js.' /><br/>'.IMG_WARNING;
			if(strpos($incl, "fileuploader") === false){
				// normal file browse input field
				print "<p> - New {$sublabel}:<br/>\n";
				print $wrapperElems['before'].$inputfld.$wrapperElems['after']."\n";
			}else{
				// fileuploader advanced input container
				$width = "";
				$height = "";
				if(file_exists(WEB_URL.$fs_image)) list($width, $height) = @getimagesize(SITE_PATH.$fs_image);
				print <<<EOT
				<input type="hidden" name="{$ids[0]}_fld" id="{$ids[0]}_fld" class="qq-upload-field" value="{$fs_image}" />
				<input type="hidden" name="{$ids[0]}_mod" id="{$ids[0]}_mod" class="qq-upload-mod" value="" />
				<input type="hidden" name="{$ids[0]}_dim" id="{$ids[0]}_dim" class="qq-upload-mod" value="{$width}|{$height}" />
				<div id="{$ids[0]}">
					<noscript>
						<p>Please enable JavaScript to use file uploader.</p>
						{$inputfld}
					</noscript>
				</div>
EOT;
			}
		}
		if($displaytype == FLD_ALL || $displaytype == FLD_CLOSEROW) print "</div>\n";
		if(!isBlank($wrapperElems['help'])) print "<div class=\"edithelp\">".$wrapperElems['help']."</div>\n";
	}else{
		if($displaytype != FLD_LABEL) {
			print "<input type=\"hidden\" id=\"{$ids[1]}\" name=\"{$ids[1]}\" value=\"{$fs_image}\" />\n";
			print "<input type=\"hidden\" id=\"{$ids[2]}\" name=\"{$ids[2]}\" value=\"{$fs_thumb}\" />\n";
			if ($fs_image != "" && $displayed[0]) {
				if(substr($folder, -1, 1) != "/") $folder .= "/";
				$photo_pic= checkImagePath($fs_image, IMG_UPLOAD_FOLDER.$folder, "");
				list($width, $height, $origwidth, $origheight) = constrainImage(SITE_PATH.$photo_pic, 80);
				print "<img src=\"".WEB_URL."$photo_pic\" border=\"1\" width=\"$width\" height=\"$height\" />";
				if($ids[3] != "") print "&nbsp;<input type=\"checkbox\" id=\"{$ids[3]}\" name=\"{$ids[3]}\" /> Delete<br/>";
			}elseif ($fs_thumb != "" && $displayed[1]) {
				$photo_pic= checkThumbPath($fs_thumb, THM_UPLOAD_FOLDER.$folder, "");
				list($width, $height) = @getimagesize(SITE_PATH.$photo_pic);
				print "<img src=\"".WEB_URL.$photo_pic."\" border=\"1\" width=\"{$width}\" height=\"{$height}\" />";
				if($ids[3] != "") print "&nbsp;<input type=\"checkbox\" id=\"{$ids[3]}\" name=\"{$ids[3]}\" /> Delete<br/>";
			}
			$inputfld = '<input type="file" id="'.$ids[0].'" name="'.$ids[0].'"'.$fldclass.$js.' />';
			// simplified file browse input field
			print $wrapperElems['before'].$inputfld.$wrapperElems['after']."<br/>".IMG_WARNING."\n";
		}
	}
}

/**
 * Displays Admin file browser input field
 * @param string $label
 * @param string $ids
 * @param string $fs_file
 * @param string $folder
 * @param string $type
 * @param string $aftertext
 * @param string $js
 * @param string $labelclass
 * @param string $fldclass
 * @param integer $displaytype
 */
function showFileField($label, $ids, $value, $folder, $type, $wrappertext = "", $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL){
	global $incl;

	if(strpos($label, "*") !== false) $fldclass = trim("required ".$fldclass);
	if($labelclass != "") $labelclass = " class=\"{$labelclass}\"";
	if($fldclass != "") $fldclass = " class=\"{$fldclass}\"";
	if($js != "" && substr($js, 0, 1) != " ") $js = " ".$js;
	$sublabel = str_replace("*", "", $label);
	switch($type){
		case "doc":
			$label .= " (DOC or DOCX Only)";
			break;
		case "pdf":
			$label .= " (PDF Only)";
			break;
		case "audio":
			$label .= " (Audio File Only)";
			break;
		case "video":
			$label .= " (Video File Only)";
			break;
	}
	if($label != '') (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";
	$wrapperElems = parseAttributes($wrappertext, array("before", "after", "help"));

	if($displaytype != FLD_DATA && $displaytype != FLD_CLOSEROW) print "<div class=\"editlabel {$labelclass}\">{$label}</div>\n";
	if($displaytype == FLD_ALL || $displaytype == FLD_OPENROW) print "<div class=\"editfield\">";
	if($displaytype != FLD_LABEL) {
		if ($ids[1] != "") print "<input type=\"hidden\" id=\"{$ids[1]}\" name=\"{$ids[1]}\" value=\"{$value}\" />\n";

		$inputfld = '<input type="file" id="'.$ids[0].'" name="'.$ids[0].'"'.$fldclass.$js.' /><br/>';
		if(strpos($incl, "fileuploader") === false){
			// normal file browse input field
			if ($value != "") {
				if(substr($folder, -1, 1) != "/") $folder .= "/";
				$file_spec	= checkFilePath($value, FILE_UPLOAD_FOLDER.$folder, "");
				print " - Existing {$sublabel}: <a href=\"".WEB_URL.$file_spec."\">";
				switch ($type) {
					case ($type == "audio" || $type == "video"):
						print "<img alt=\"Play\" title=\"Play {$type}\" src=\"".WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/icons/play.png\" />".basename($file_spec)."</a>";
						break;
					default:
						print "Click to view</a>";
						break;
				}
				if($ids[2] != "") print "&nbsp;&nbsp;<input type=\"checkbox\" id=\"{$ids[2]}\" name=\"{$ids[2]}\" /> Delete File (Cannot be undeleted)";
			}

			print "<p> - New {$sublabel}:<br/>\n";
			print $wrapperElems['before'].$inputfld.$wrapperElems['after']."\n";
		}else{
			// fileuploader advanced input container
			print <<<EOT
			<input type="hidden" name="{$ids[0]}_fld" id="{$ids[0]}_fld" class="qq-upload-field" value="{$value}" />
			<input type="hidden" name="{$ids[0]}_mod" id="{$ids[0]}_mod" class="qq-upload-mod" />
			<div id="{$ids[0]}">
				<noscript>
					<p>Please enable JavaScript to use file uploader.</p>
					{$inputfld}
				</noscript>
			</div>
EOT;
		}
	}
	if($displaytype == FLD_ALL || $displaytype == FLD_CLOSEROW) print "</div>\n";
	if($wrapperElems['help'] != '') print "<div class=\"edithelp\">".$wrapperElems['help']."</div>\n";
}

/**
 * Displays Admin hidden field
 * @param string $id
 * @param string $value
 */
function showHiddenField($id, $value, $noname = false){
	echo "<input type=\"hidden\" id=\"{$id}\"".((!$noname) ? " name=\"{$id}\"" : "")." value=\"{$value}\" />\n";
}

/**
 * displays Admin checkbox field
 * @param string $label
 * @param string $id
 * @param string $value
 * @param string $chkstate
 * @param string $text
 * @param string $separator
 * @param string $js
 * @param string $labelclass
 * @param string $fldclass
 * @param integer $displaytype
 */
function showCheckbox($label, $id, $value, $chkstate, $text, $separator="", $wrappertext = "", $js="", $labelclass="", $fldclass="", $spanclass="", $displaytype = FLD_ALL){
	if(strpos($label, "*") !== false) $fldclass = trim("required ".$fldclass);
	if($labelclass != "") $labelclass = " class=\"{$labelclass}\"";
	if($fldclass != "") $fldclass = " class=\"{$fldclass}\"";
	if($js != "" && substr($js, 0, 1) != " ") $js = " ".$js;
	($chkstate == "" || ($chkstate == 0 && is_numeric($chkstate)) || ($chkstate != $value)) ? $selected = "" : $selected = " checked=\"checked\"";
	if($label != '') (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";
	$wrapperElems = parseAttributes($wrappertext, array("before", "after", "help"));

	if($displaytype != FLD_DATA && $displaytype != FLD_CLOSEROW) print "<div class=\"editlabel {$labelclass}\">{$label}</div>\n";
	if($displaytype == FLD_ALL || $displaytype == FLD_OPENROW) print "<div class=\"editfield\">";
	if($displaytype != FLD_LABEL) print $wrapperElems['before']."<span{$spanclass}><input type=\"checkbox\" id=\"{$id}\" name=\"{$id}\" value=\"{$value}\"{$selected}{$fldclass}{$js} />{$text}</span>".$wrapperElems['after']."\n";
	if($displaytype == FLD_ALL || $displaytype == FLD_CLOSEROW) print "</div>\n";
	if($wrapperElems['help'] != '') print "<div class=\"edithelp\">".$wrapperElems['help']."</div>\n";
}

/**
 * displays Admin checkbox list fields
 * @param string $label
 * @param string $id
 * @param array $selectionarray
 * @param array $chosenvaluesarray
 * @param string $separator
 * @param array $jsarray
 * @param string $labelclass
 * @param string $fldclass
 * @param integer $displaytype
 */
function showCheckboxList($label, $id, $selectionarray, $chosenvaluesarray, $separator = "&nbsp;", $jsarray = null, $labelclass = null, $fldclass = "", $spanclassarray = null, $displaytype = FLD_ALL){
    if(strpos($label, "*") !== false) $fldclass = trim("required ".$fldclass);
    if($labelclass != "") $labelclass = " class=\"{$labelclass}\"";
    if($fldclass != "") $fldclass = " class=\"{$fldclass}\"";
    if($js != "" && substr($js, 0, 1) != " ") $js = " ".$js;
    (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";

    if($displaytype != FLD_DATA && $displaytype != FLD_CLOSEROW) print "<div class=\"editlabel {$labelclass}\">{$label}</div>\n";
    if($displaytype == FLD_ALL || $displaytype == FLD_OPENROW) print "<div class=\"editfield\">";
    if(is_array($selectionarray)){
        foreach($selectionarray as $key => $value){
            if(is_array($chosenvaluesarray)){
                (in_array($key, $chosenvaluesarray)) ? $selected = " checked=\"checked\"" : $selected = "";
            }elseif(is_numeric($chosenvaluesarray[$key])){
                (($chosenvaluesarray[$key] > 0 || $value == $chosenvaluesarray[$key])) ? $selected = " checked=\"checked\"" : $selected = "";
            }else{
                (($chosenvaluesarray[$key] != '')) ? $selected = " checked=\"checked\"" : $selected = "";
            }
            ($spanclassarray[$key] != "") ? $spanclass = " class=\"{$spanclassarray[$key]}\"" : $spanclass = "";
            if($displaytype != FLD_LABEL) print "<span{$spanclass}><input type=\"checkbox\" id=\"{$id}{$key}\" name=\"{$id}[{$key}]\" value=\"{$key}\"{$selected}{$fldclass}{$jsarray[$key]}/>{$value}</span>{$separator}\n";
        }
    }
    if($displaytype == FLD_ALL || $displaytype == FLD_CLOSEROW) print "</div>\n";
}

/**
 * Displays Admin radio button list fields
 * @param string $label
 * @param string $id
 * @param array $selectionarray
 * @param string $chosenvaluesarray
 * @param array $textarray
 * @param string $separator
 * @param string $js
 * @param string $labelclass
 * @param string $fldclass
 * @param integer $displaytype
 */
function showRadioList($label, $id, $selectionarray, $chosenvaluesarray, $textarray, $separator = "&nbsp;", $js = "", $labelclass = "", $fldclass = "", $spanclassarray = null, $displaytype = FLD_ALL){
	if(strpos($label, "*") !== false) $fldclass = trim("required ".$fldclass);
	if($labelclass != "") $labelclass = " class=\"{$labelclass}\"";
	if($fldclass != "") $fldclass = " class=\"{$fldclass}\"";
	if($js != "" && substr($js, 0, 1) != " ") $js = " ".$js;
	if($label != '') (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";

	if($displaytype != FLD_DATA && $displaytype != FLD_CLOSEROW) print "<div class=\"editlabel {$labelclass}\">{$label}</div>\n";
	if($displaytype == FLD_ALL || $displaytype == FLD_OPENROW) print "<div class=\"editfield\">";
	foreach($selectionarray as $key => $value){
		($chosenvaluesarray == $value) ? $selected = " checked=\"checked\"" : $selected = "";
		($spanclassarray[$key] != "") ? $spanclass = " class=\"{$spanclassarray[$key]}\"" : $spanclass = "";
		if($displaytype != FLD_LABEL) print "<span{$spanclass}><input type=\"radio\" id=\"{$id}\" name=\"{$id}\" value=\"{$value}\"{$selected}{$fldclass}{$js} />{$textarray[$key]}</span>{$separator}\n";
	}
	if($displaytype == FLD_ALL || $displaytype == FLD_CLOSEROW) print "</div>\n";
}

/**
 * Displays Admin select menu field
 * @param str $label
 * @param str $id
 * @param array $valuearray
 * @param str $selectedvalue
 * @param int $size
 * @param bool $multiple
 * @param str $js
 * @param string $optjs
 * @param string $aftertext
 * @param string $labelclass
 * @param string $fldclass
 * @param integer $displaytype
 */
function showMenu($label, $id, $valuearray, $selectedvalue = "", $size = 1, $multiple = false, $disablevalue = "", $wrappertext = "", $js = "", $optjs = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL){
	if(!is_array($valuearray)) die("ShowMenu: valuearray not an array!");

	if(strpos($label, "*") !== false) $fldclass = trim("required ".$fldclass);
	if($labelclass != "") $labelclass = " class=\"{$labelclass}\"";
	if($fldclass != "") $fldclass = " class=\"{$fldclass}\"";
	if($js != "" && substr($js, 0, 1) != " ") $js = " ".$js;
	if($label != '') (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";
	($multiple) ? $multiple_tag = " multiple=\"multiple\"" : $multiple_tag = "";
	$wrapperElems = parseAttributes($wrappertext, array("before", "after", "help"));

	if($displaytype != FLD_DATA && $displaytype != FLD_CLOSEROW) print "<div class=\"editlabel {$labelclass}\">{$label}</div>\n";
	if($displaytype == FLD_ALL || $displaytype == FLD_OPENROW) print "<div class=\"editfield\">";
	if($displaytype != FLD_LABEL) {
		echo $wrapperElems['before']."<select id=\"{$id}\" name=\"{$id}\" size=\"{$size}\" {$multiple_tag}{$js}{$fldclass}>\n";
		$optgroupstarted = false;
		foreach($valuearray as $key => $value){
			if(substr($key, 0, 1) == '-'){
				// opt group
				if($optgroupstarted) print "</optgroup>\n";
				print "<optgroup label=\"{$value}\">\n";
				$optgroupstarted = true;
			}elseif($disablevalue == $key){
				print "<option value=\"{$key}\" disabled=\"disabled\"{$optjs}>{$value}</option>\n";
            }else{
				// option
				($selectedvalue == $key || ($selectedvalue == $value && !is_numeric($selectedvalue))) ? $selected = " selected=\"selected\"" : $selected = "";
				print "<option value=\"{$key}\"{$selected}{$optjs}>{$value}</option>\n";
			}
		}
		if($optgroupstarted) print "</optgroup>\n";
		print "</select>".$wrapperElems['after']."\n";
	}
	if($displaytype == FLD_ALL || $displaytype == FLD_CLOSEROW) print "</div>\n";
	if($wrapperElems['help'] != '') print "<div class=\"edithelp\">".$wrapperElems['help']."</div>\n";
}

/**
 * Prepares array containing standard menu data such as months, dates, etc.
 * @param integer $menutype
 */
function prepStandardMenu($menutype, $moredata = null){
	$data = array();
	switch($menutype){
		case MENU_DAY:
			for($i=1; $i<32; $i++) $data[sprintf("%02d", $i)] = $i;
			break;
		case MENU_SHORTDOW:
			$data = array("Mon", "Tues", "Wed", "Thurs", "Fri", "Sat", "Sun");
			break;
		case MENU_DOW:
			$data = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
			break;
		case MENU_MONTH:
			if($moredata == null){
				for($i=1; $i<13; $i++) $data[sprintf("%02d", $i)] = date("F", mktime(0,0,0,$i,1,2000));
			}elseif($moredata == MENU_NUM){
				for($i=1; $i<13; $i++) $data[sprintf("%02d", $i)] = $i." - ".date("F", mktime(0,0,0,$i,1,2000));
			}
			break;
		case MENU_SHORTMONTH:
			if($moredata == null){
				for($i=1; $i<13; $i++) $data[sprintf("%02d", $i)] = date("M", mktime(0,0,0,$i,1,2000));
			}elseif($moredata == MENU_NUM){
				for($i=1; $i<13; $i++) $data[sprintf("%02d", $i)] = $i." - ".date("M", mktime(0,0,0,$i,1,2000));
			}
			break;
		case MENU_YEAR:
			$moredata = intval($moredata);
			if ($moredata < 1990 || $moredata > 2029) $moredata = date("Y");
			for($i = date("Y")+2; $i >= $moredata; $i--) $data[$i] = $i;
			break;
		case MENU_ALPHA:
			for($i=1; $i<27; $i++) $data[chr($i+64)] = chr($i+64);
			break;
		case MENU_NUM:
			for($i=0; $i<10; $i++) $data[$i] = $i;
			break;
		case MENU_ONOFF:
			$data = array("1" => "On", "0" => "Off");
			break;
		case MENU_YESNO:
			$data = array("1" => "Yes", "0" => "No");
			break;
		case MENU_STATUS:
			$data = array("open" => "Open", "closed" => "Closed", "complete" => "Completed", "inprocess" => "In-Process");
			break;
	}
	if(is_array($moredata)) $data += $moredata;
	return $data;
}

/**
 * Displays GoogleMap-powered Admin Map Box.  Requires initialization of GoogleMap Plugin
 * @param string $label
 * @param float $lat
 * @param float $lon
 * @param string $titlerowid
 * @param string $boxrowid
 * @param string $coordhint
 * @param string $js
 * @param integer $displaytype
 */
function showMapBox($label, $lat, $lon, $titlerowid = "maptitlerow", $boxrowid = "mapboxrow", $coordhint = "", $js = "", $displaytype = FLD_ALL){
	if($js != "" && substr($js, 0, 1) != " ") $js = " ".$js;
	if($label != '') (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";

	if($displaytype != FLD_DATA && $displaytype != FLD_CLOSEROW) print "<tr id=\"{$titlerowid}\"><td class=\"editlabel\">{$label}</div>\n";
	if($displaytype == FLD_ALL || $displaytype == FLD_OPENROW) print "<div class=\"editfield\">";
	if($displaytype != FLD_LABEL){
		print<<<EOT
		<tr id="{$boxrowid}"><td>
		{$coordhint}<br/>
		<div id="mapbox"{$js}></div>
		<script type="text/javascript" language="JavaScript">
			updateMapFromRegion({$lat}, {$lon});
		</script>
EOT;
	}
	if($displaytype == FLD_ALL || $displaytype == FLD_CLOSEROW) print "</div>\n";
}

/**
 * Displays address entry box (address, street, street direction, city...)
 * @param string $label
 * @param array $colids
 * @param array $collabels
 * @param array $colvals
 * @param string $js
 * @param string $labelclass
 * @param string $fldclass
 * @param string $displaytype
 */
function showAddressBox($label, array $colids, array $collabels, array $colvals, $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL){
	if(strpos($label, "*") !== false) $fldclass = trim("required ".$fldclass);
	if($labelclass != "") $labelclass = " class=\"{$labelclass}\"";
	if($fldclass != "") $fldclass = " class=\"{$fldclass}\"";
	if($js != "" && substr($js, 0, 1) != " ") $js = " ".$js;
	if($label != '') (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";
	$deflabels = array("Apt (eg. 203):", "Street # (...1950):", "Street Name (...Gordon):", "Type (...Drive):", "Dir.:", "City (...Vancouver):");
	$defids = array("aptnumber", "streetnumber", "streetname", "type", "dir", "city");
	if(is_array($collabels)) {
		if(count($collabels) == 0) $collabels = $deflabels;
	}else{
		$collabels = $deflabels;
	}
	if(is_array($colids)) {
		if(count($colids) == 0) $colids = $defids;
	}else{
		$colids = $defids;
	}
	$streettype_list = buildDataList("streettype", $streettype, "address_streettype", "name", "name", "", "", "name", "", $js);
	$streetdir_list = buildDataList("streetdir", $streetdir, "address_streetdir", "abbrev", "abbrev", "", "", "abbrev", "", $js);

	if($displaytype != FLD_DATA && $displaytype != FLD_CLOSEROW) print "<tr id=\"{$titlerowid}\"><td class=\"editlabel {$labelclass}\">{$label}</div>\n";
	if($displaytype == FLD_ALL || $displaytype == FLD_OPENROW) print "<div class=\"editfield\">";
	if($displaytype != FLD_LABEL){
		foreach($colids as $idkey => $colid){
			switch ($colid){
				case "type":
					print "<div style=\"float: left; margin-right: 5px;\">{$collabels[$idkey]}<br/>{$streettype_list}</div>\n";
					break;
				case "dir":
					print "<div style=\"float: left; margin-right: 5px;\">{$collabels[$idkey]}<br/>{$streetdir_list}</div>\n";
					break;
				default:
					print "<div style=\"float: left; margin-right: 5px;\">{$collabels[$idkey]}<br/><input type=\"text\" id=\"{$colid}\" name=\"{$colid}\" value=\"{$colvals[$idkey]}\" size=\"25\"{$js} /></div>\n";
					break;
			}
		}
	}
	if($displaytype == FLD_ALL || $displaytype == FLD_CLOSEROW) print "</div>\n";
}

/**
 * Display custom contents under label
 * @param string $label
 * @param string $contents
 * @param string $labelclass
 * @param integer $displaytype
 */
function showCustomField($label, $contents, $wrappertext = "", $labelclass = "", $displaytype = FLD_ALL){
	if($labelclass != "") $labelclass = " class=\"{$labelclass}\"";
	if($label != '') (substr($label, -1, 1) == "*") ? $label = str_replace("*", "", $label).": ".REQD_ENTRY : $label .= ": ";
	$wrapperElems = parseAttributes($wrappertext, array("before", "after", "help"));

	if($displaytype != FLD_DATA && $displaytype != FLD_CLOSEROW) print "<div class=\"editlabel {$labelclass}\">{$label}</div>\n";
	if($displaytype == FLD_ALL || $displaytype == FLD_OPENROW) print "<div class=\"editfield\">";
	if($displaytype != FLD_LABEL) print $wrapperElems['before'].$contents.$wrapperElems['after'];
	if($displaytype == FLD_ALL || $displaytype == FLD_CLOSEROW) print "</div>\n";
	if($wrapperElems['help'] != '') print "<div class=\"edithelp\">".$wrapperElems['help']."</div>\n";
}

/**
 * Output brief version of contents
 * @param string $content
 * @param integer $length [optional]
 * @param string $url [optional]
 * @param string $finish [optional]
 */
function showShortContent($content, $length = 20, $url = '', $finish = '...') {
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
 * Set page help contents (seen when the Help button is pressed)
 */
function setPageHelp($contents){
	global $_page;

	$_page->help = $contents;
}

/**
 * Causes a page to become the site home page, resetting all others to normal pages
 * @param string $pagetitle
 */
function switchHomePageTo($pagetitle){
	if($pagetitle != ''){
		updateRec("pages", "pagealias = ''", "pagetitle != '$pagetitle'");
		$pages = getRec("pages", "id, pagetitle, pagealias", "pagename = '' AND pagetitle != '$pagetitle'", "", "");
		if(count($pages) > 0){
			foreach($pages as $page){
				$pagename = codify($page['pagetitle']);
				updateRec("pages", "pagename = '$pagename'", "id = '".$page['id']."'");
			}
		}
	}
}

/**
 * Return a specific admin menu array element or all admin menus
 * @param string $menucode
 * @return array
 */
function getAdminMenus($menucode = ''){
    $menus = getRec("settings", "value", "`name` = 'ADMINMENUS'");
    if(count($menus) == 0){
        $menus = array(
            "pages" => array("table" => "pages", "title" => "Pages", "tocategory" => false, "topage" => false, "alias" => "", "childmenus" => null),
            "events" => array("table" => "data_events", "title" => "Events", "tocategory" => false, "topage" => false, "alias" => "", "childmenus" => null),
            "photo_gallery" => array("table" => "photos_cat", "title" => "Photo Gallery", "tocategory" => true, "topage" => false, "alias" => "", "childmenus" => array(
                    "table" => "photos", "title" => "Galleries", "tocategory" => true, "topage" => false, "alias" => ""
                )),
            "projects" => array("table" => "data_projects", "title" => "Projects", "tocategory" => false, "topage" => false, "alias" => "", "childmenus" => null),
            "whats_new" => array("table" => "data_whatsnew", "title" => "What's New", "tocategory" => false, "topage" => false, "alias" => "", "childmenus" => null)
        );
    }

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
?>