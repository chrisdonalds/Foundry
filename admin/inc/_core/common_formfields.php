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

define ("FORMFIELDSLOADED", true);

if(!defined("VALID_LOAD")) die ("This file cannot be accessed directly!");

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
?>