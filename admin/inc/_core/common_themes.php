<?php
// ---------------------------
//
// FOUNDRY COMMON FUNCTIONS
//  - Themes Support -
//
// ---------------------------
//
// list of tool functions
// - Uses: MySQL 4.2+

define ("THEMESLOADED", true);
if(!defined("VALID_LOAD")) die ("This file cannot be accessed directly!");

/**
 * Prepare theme subsystem (folder permissions and arrays) and return array of loaded themes
 * @return array
 */
function prepThemes(){
	// ensure that all theme folders and theme.css file are writable
	$dir = SITE_PATH.THEME_FOLDER;
	$themes = array();

	if(is_dir($dir)){
		if($dirhandle = opendir($dir)){
			while(false !== ($file = readdir($dirhandle))){
				// Skip '.' and '..'
				if($file == '.' || $file == '..') continue;

				$path = strtolower($dir.$file);
				if(is_dir($path)){
					$themes[] = $file;
					$perms_str = getFileACL($file, true);
					chmod2($dir, "0777");
					//if(getFilePerms($dir) != "0777") exec ("chmod 777 $dir");
				}elseif($file == "theme.css"){
					chmod2($path, "0777");
					//if(getFilePerms($path) != "0777") exec ("chmod 777 $path");
				}
			}
			closedir($dirhandle);
		}
	}

	chmod2(SITE_PATH.IMG_UPLOAD_FOLDER."theme_images", "0777");
	chmod2(SITE_PATH.FILE_UPLOAD_FOLDER."theme_files", "0777");
	//if(getFilePerms(SITE_PATH.IMG_UPLOAD_FOLDER."theme_images") != "0777") exec ("chmod 777 ".SITE_PATH.IMG_UPLOAD_FOLDER."theme_images");
	//if(getFilePerms(SITE_PATH.FILE_UPLOAD_FOLDER."theme_files") != "0777") exec ("chmod 777 ".SITE_PATH.FILE_UPLOAD_FOLDER."theme_files");
	return $themes;
}

/**
 * Output theme settings tab contents
 */
function displaySettingsThemesAttributes(){
	$settings = array();
	getThemeAttributes();

	$groups = array("Page" => array("html, body"),
					"Header" => array("#header"),
					"Navigation" => array("Label" => "#nav, .nav", "Label (Link)" => "#nav a, .nav a", "Label (Hover)" => "#nav a:hover, .nav a:hover", "Label (Current)" => "#nav .on"),
					"Content" => array("H1" => "h1", "H2" => "h2", "H3" => "h3", "Links" => "a:link", "Links (Hover)" => "a:hover", "Links (Active)" => "a:active", "Links (Visited)" => "a:visited"),
					"Sections" => array(".section"),
					"Images" => array("img"),
					"Forms" => array("Fields" => ".inputfield"),
					"Buttons" => array("Basic" => ".button", "Button (Link)" => ".button:link", "Button (Hover)" => ".button:hover", "Button (Active)" => ".button:active", "Button (Visited)" => ".button:visited"),
					"Footer" => array("Links" => "#footer a", "Lists" => "#footer li")
					);
	foreach($groups as $grp_title => $grp_elems){
		$last_grp_title = "";
		$retn_objects_ids = array();
		$img_objects_ids = array();
		$img_objects_vals = array();
		foreach($grp_elems as $grp_elemname => $grp_attr){
			$grp_values = getThemeValues($grp_attr);
			if(count($grp_values) > 0){
				if($last_grp_title != $grp_title){
					echo "<div class=\"setlabelnarrow\">$grp_title:</div>\n";
				}else{
					echo "<div class=\"setlabelnarrow nooverline\"></div>\n";
				}
				echo "<div class=\"setdata\">\n";
				if(is_int($grp_elemname)) $grp_elemname = "";
				echo "<div class=\"setinnerlabel\"><b>".$grp_elemname."</b></div>\n";
				echo "<div class=\"setinnerdata\">\n";
				foreach($grp_values as $grp_value_elem){
					echo "<span class=\"setinnerlabel2\">".$grp_value_elem['label'].":&nbsp;</span>";
					showThemeInput($grp_value_elem);
					echo "<br/>";
				}
				echo "</div>\n";
				echo "</div>\n";
				$last_grp_title = $grp_title;
			}
		}
	}

	if(count($GLOBALS['themepalettes']) > 0){
		$palette = current($GLOBALS['themepalettes']);
		$palettename = key($GLOBALS['themepalettes']);
	}else{
		$palette = "";
		$palettename = "";
	}
	echo "<input type=\"hidden\" name=\"themepalettes[{$palettename}]\" id=\"themepalettes\" value=\"{$palette}\"/>\n";
}

/**
 * Get all theme attributes and palettes (stored in $GLOBALS)
 * @return boolean
 */
function getThemeAttributes(){
	$themefile = SITE_PATH.THEME_FOLDER.$GLOBALS['THEME']."/theme.css";
	$themebak  = SITE_PATH.THEME_FOLDER.$GLOBALS['THEME']."/theme.bak";

	// check if theme has been backed up
	if(!file_exists($themebak)){
		copy ($themefile, $themebak);
	}

	$params = array();
	if($fp = fopen($themefile, "r")){
		$comment_started = false;
		$attr_started = false;
		$value_started = false;
		$is_value = false;
		$themeattrs = array();
		$attrs = array();
		$palettes = array();
		while($line = fgets($fp)){
			$line = trim($line);
			// skip any line starting with @, #, //, or is blank
			// also set comment flag if line starts with /* and unset if line ends with */
			$is_skipped = false;
			$palette_line = false;
			if(substr($line, 0, 1) == '@' || substr($line, 0, 2) == '//' || (substr($line, 0, 2) == '/*' && substr($line, -2, 2) == '*/' && ord(substr($line, 2, 1)) < 48)) $is_skipped = true;
			if(substr($line, 0, 9) == '/*palette') $palette_line = true;
			if($line == '') $is_skipped = true;
			if(substr($line, 0, 2) == '/*' && ord(substr($line, 2, 1)) < 48) $comment_started = true;
			if(substr($line, -2, 2) == '*/' && $comment_started) { $is_skipped = true; $comment_started = false; }
			if(!$palette_line) $line = trim(str_replace("\/\*(.+)\*\/", "", $line));
			if(!$is_skipped && !$comment_started){
				if($palette_line){
					// this is a palette line
					$line = str_replace(array("/*", "*/", ";"), "", $line);
					$palette_name = substr($line, 0, strpos($line, ":"));
					$palettes[$palette_name] = substr($line, strlen($palette_name)+1);
				}elseif(!$value_started){
					// start attribute names parsing
					// they may be comma-separated and/or on multiple lines
					// generally the attribute name parsing will end when { is encountered
					$attr_started = true;
					if(strpos($line, '{') !== false) {
						// switch to value parsing
						$attr_started = false; $value_started = true;
						$line = trim(str_replace("{", "", $line));
					}
					if($line != ""){
						// split attribute names by commas
						preg_match_all("/[^(,)]+/", $line, $curattrs);
						foreach($curattrs as $thisattr) $attrs = array_merge($attrs, $thisattr);
					}
					if(!$attr_started) {
						// end of attribute parsing
						$values = array();
					}
				}else{
					// start value parsing
					// values may be semi-colon separated and/or on multiple lines
					if(strpos($line, '}') !== false) {
						// switch off value parsing
						$value_started = false;
						$line = trim(str_replace("}", "", $line));
					}
					if($line != ""){
						// split values by semi-colons and add to values list (remove custom-tags /*tag...*/)
						$line = str_replace(array("/*", "*/"), "", $line);
						preg_match_all("/[^(;)]+/", $line, $curvalues);
						foreach($curvalues as $thisvalue) $values = array_merge($values, $thisvalue);
					}
					if(!$value_started) {
						// end of value parsing
						// assign value pairs to attributes array elements
						$attrvals = array();
						foreach($values as $thisvalue){
							$valpair = explode(":", $thisvalue);
							$attrvals[] = array("name" => trim(strtolower($valpair[0])), "setting" => trim(strtolower($valpair[1])));
						}
						foreach($attrs as $thisattr){
							$thisattr = trim(strtolower($thisattr));
							$thiskey = preg_replace("/[^(a-z0-9)]/i", "-", $thisattr);
							$thiskey = preg_replace("/^(-)+/i", "", $thiskey);
							if(count($attrvals) > 0){
								$themeattrs[$thiskey] = array("attr" => $thisattr, "value" => $attrvals);
							}
						}
						$attrs = array();
					}
				}
			}
		}
		fclose($fp);
	}
	$GLOBALS['themeattrs'] = $themeattrs;
	$GLOBALS['themepalettes'] = $palettes;
	//print "<pre>";
	//print_r($GLOBALS['themeattrs']);
	//print_r($GLOBALS['themepalettes']);
	//print "</pre>";
	//die();
	return true;
}

/**
 * Return array containing values for a specific theme attribute
 * @param string $attr
 * @return array
 */
function getThemeValues($attr){
	$settings = array();

	$attrarry = explode(",", $attr);
	foreach($attrarry as $attrpart){
		if($attrpart != '') {
			$thisattrpart = preg_replace("/[^(a-z0-9)]/i", "-", $attrpart);
			$thisattrpart = preg_replace("/^(-)+/i", "", $thisattrpart);
			$thiskey = strtolower($thisattrpart);
			$thisattr = getIfSet($GLOBALS['themeattrs'][$thiskey]);
			break;
		}
	}
	if(count($thisattr) > 0){
		// attributes: attr, value [array]
		foreach($thisattr['value'] as $thisvalue){
			// values : name, setting
			$name = $thisvalue['name'];
			$attr = key($thisattr);
			switch($name){
				case "color":
					$settings[] = array("attr" => $thiskey, "name" => $name, "label" => "Text Color", "type" => "color", "value" => $thisvalue['setting']);
					break;
				case "background":
					$settings[] = array("attr" => $thiskey, "name" => $name, "label" => "Background Color", "type" => "color", "value" => $thisvalue['setting']);
					break;
				case "background-color":
					$settings[] = array("attr" => $thiskey, "name" => $name, "label" => "Background Color", "type" => "color", "value" => $thisvalue['setting']);
					break;
				case "border-color":
					$settings[] = array("attr" => $thiskey, "name" => $name, "label" => "Border Color", "type" => "color", "value" => $thisvalue['setting']);
					break;
				case "font-weight":
					$settings[] = array("attr" => $thiskey, "name" => $name, "label" => "Font Weight", "type" => "weight", "value" => $thisvalue['setting']);
					break;
				case "text-decoration":
					$settings[] = array("attr" => $thiskey, "name" => $name, "label" => "Style", "type" => "decoration", "value" => $thisvalue['setting']);
					break;
				case "border":
					$settings[] = array("attr" => $thiskey, "name" => $name, "label" => "Border Style", "type" => "border", "value" => $thisvalue['setting']);
					break;
				case "width":
					$settings[] = array("attr" => $thiskey, "name" => $name, "label" => "Width", "type" => "size", "value" => $thisvalue['setting']);
					break;
				case "left":
					$settings[] = array("attr" => $thiskey, "name" => $name, "label" => "Left", "type" => "size", "value" => $thisvalue['setting']);
					break;
				case "top":
					$settings[] = array("attr" => $thiskey, "name" => $name, "label" => "Top", "type" => "size", "value" => $thisvalue['setting']);
					break;
				case (substr($name, 0, 7) == "padding"):
					$settings[] = array("attr" => $thiskey, "name" => $name, "label" => "Padding ".substr($name, 7), "type" => "size", "value" => $thisvalue['setting']);
					break;
				case (substr($name, 0, 7) == "margin"):
					$settings[] = array("attr" => $thiskey, "name" => $name, "label" => "Margin ".substr($name, 7), "type" => "size", "value" => $thisvalue['setting']);
					break;
				case (substr($name, 0, 7) == "headimg"):
					$settings[] = array("attr" => $thiskey, "name" => $name, "label" => "Header Image ".substr($name, 7), "type" => "img", "value" => $thisvalue['setting']);
					break;
			}
		}
	}
	return $settings;
}

/**
 * Output theme field value for attribute
 * @param array $grp_set_elem
 * @return array
 */
function showThemeInput($grp_set_elem){
	$retn_ids = array();		// holds array of plugin object ids (such as fileuploader)

	if(is_array($grp_set_elem)){
		$id = $grp_set_elem['attr']."_".$grp_set_elem['name'];
		$value = $grp_set_elem['value'];
		switch ($grp_set_elem['type']){
			case "color":
				echo "<input type=\"text\" id=\"$id\" name=\"themeattr[$id]\" value=\"$value\" size=\"10\" style=\"float: left;\" />";
				echo "<div style=\"background-color: $value;\" class=\"colorpicker_button\" title=\"Click to view color palette\" alt=\"Click to view color palette\" id=\"cp_$id\">&nbsp;</div>";
				break;
			case "size":
				echo "<input type=\"text\" id=\"$id\" name=\"themeattr[$id]\" value=\"$value\" size=\"5\" style=\"float: left;\" />";
				break;
			case "border":
				echo "<input type=\"text\" id=\"$id\" name=\"themeattr[$id]\" value=\"$value\" size=\"20\" style=\"float: left;\" />";
				break;
			case "weight":
				echo "<input type=\"text\" id=\"$id\" name=\"themeattr[$id]\" value=\"$value\" size=\"10\" style=\"float: left;\" />";
				break;
			case "decoration":
				echo "<select id=\"$id\" name=\"themeattr[$id]\" size=\"1\" style=\"float: left; width: 135px;\">";
				$choices = array("underline", "none");
				foreach($choices as $choice){
					($choice == $value) ? $sel = " selected=\"selected\"" : $sel = "";
					echo "<option value=\"$choice\"$sel>$choice</option>";
				}
				echo "</select>\n";
				break;
			case "img":
				echo "<br/>";
				showImageField("Image", array("themeattr[{$id}_imgtag]", "lastimg_$id", "lastthm_$id", "delimg_$id"), array($value, ""), array(true, false), "theme_images/", 30, "", "", "", "", FLD_SIMPLE);
				break;
		}
		echo "<img class=\"colorpicker_reset disabled\" id=\"cp_{$id}__reset\" src=\"".WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/icons/reset.png\" border=\"0\" height=\"16\" width=\"16\" alt=\"Reset\" title=\"Reset\" rel=\"$value\"/>\n";
	}
	return $retn_ids;
}

/**
 * Output theme color picker (palette) box
 * @param string $palette
 * @param string $palettename
 */
function getThemePaletteBox($palette = "", $palettename = ""){
	// build palette selector boxes
	if($palette != ''){
		$colors  = preg_split("/(,|, )/", $palette);
	}else{
		$colors	 = array();
		$colors[] = "#000000";
		$colors[] = "#FFFFFF";
		for($r = 0; $r < 256; $r+=16){
			for($g = 0; $g < 256; $g+=16){
				for($b = 0; $b < 256; $b+=16){
					$colors[] = "#".sprintf("%02X%02X%02X", $r, $g, $b);
				}
			}
		}
	}

	$outp = <<<EOT
		<input type="hidden" name="colorpicker_buttonid" id="colorpicker_buttonid" value=""/>
		<input type="hidden" name="colorpicker_fieldid" id="colorpicker_fieldid" value=""/>
		<span>Current:</span><div id="colorpicker_fieldcolor"></div>
		<span>Chosen:</span><div id="colorpicker_hoverdiv"></div>
		<div id="colorpicker_palettebox">
EOT;

	$colorid = 1;
	foreach($colors as $color){
		if($colorid == 3) $outp .= "<hr style=\"clear: both\"/>";
		$outp .= "<div style=\"background-color: $color;\" class=\"colorpicker_swatch\" title=\"$color\" alt=\"$color\" id=\"scp_swatch$colorid\">&nbsp;</div>";
		$colorid++;
	}

	$outp .= <<<EOT
		</div>
		<a href="#" id="colorpicker_closebutton">Close</a>
EOT;

	return $outp;
}

/**
 * Build CSS theme file contents
 * @param array $themeattrs
 * @param array $themepalettes
 * @return string
 */
function rebuildCSSFromThemeAttrs($themeattrs, $themepalettes){
	// using a translation key, rebuild the theme.css file from the two arrays passed
	//if(!is_array($themeattrs) || !is_array($themepalettes)) die("rebuildCSSFromThemeAttrs requires an array for attributes and one for palettes!");

    if(!is_array($themeattrs) || !is_array($themepalettes)) return false;

	$xKey = array(
					"html" => "html, body",
					"body" => "body",
					"header" => "#header",
					"nav" => "#nav",
					"nav-a" => "#nav a",
					"nav-a-hover" => "#nav a:hover",
					"nav--on" => "#nav .on",
					"h1" => "h1",
					"h2" => "h2",
					"h3" => "h3",
					"section" => ".section",
					"a-link" => "a:link",
					"a-hover" => "a:hover",
					"a-ative" => "a:active",
					"a-visited" => "a:visited",
					"img" => "img",
					"button" => ".button",
					"button-link" => ".button:link",
					"button-hover" => ".button:hover",
					"button-active" => ".button:active",
					"button-visited" => ".button:visited",
					"inputfield" => ".inputfield",
					"footer-a" => "#footer a",
					"footer-li" => "#footer li",
				);

	$css = "@charset \"utf-8\";
/* ---------------------------------------------------
Title:			Default Style Sheet
Updated:		February 5, 2012
Updated By:		".SYS_NAME." System, version ".CODE_VER."
--------------------------------------------------- */

/* ----- Theme Color Palette ------
Start all palette lines with 'palette[n]' in comment block
Try to use full 6-character HEX codes or color names in your CSS */\n\n";

	// palettes
	$csspal_array = array();
	foreach($themepalettes as $key => $value){
		if($value != '') $csspal_array[$key] = "/*".$key.": ".$value." */\n";
	}
	if(count($csspal_array) > 0) $css .= join("", $csspal_array)."\n";

	// files
	foreach($_FILES['themeattr']['name'] as $key => $value){
		$themeattrs[$key] = $value;
	}

	// attributes
	$cssattr_array = array();
	foreach($themeattrs as $key => $value){
		// break key into components delimited by '_'
		$attr = explode("_", strtolower($key));
		if(count($attr) == 2){
			if(isset($xKey[$attr[0]])){
				$newkey = $xKey[$attr[0]];
				if($attr[2] != "imgtag" && $attr[2] != "filetag"){
					// normal css
					$cssattr_array[$newkey] .= "\t".$attr[1].": ".$value.";\n";
				}else{
					// custom tagged css
					$lastfile = $_POST['lastimg_'.$attr[0]."_".$attr[1]];
					$delfile = $_POST['delimg_'.$attr[0]."_".$attr[1]];
					$tmp_name = $_FILES['themeattr']['tmp_name'][$key];
					$file_name = "";
					if($tmp_name != ''){
						$file_info = pathinfo($_FILES['themeattr']['name'][$key]);
						$file_name = "image".preg_replace("/[^(0-9)+]/i", "", $attr[1]).".".$file_info['extension'];
						if($attr[2] == "imgtag"){
							move_uploaded_file($tmp_name, SITE_PATH.IMG_UPLOAD_FOLDER."theme_images/".$file_name) or die("Cannot upload file");
							exec ("chmod 777 ".SITE_PATH.IMG_UPLOAD_FOLDER."theme_images/".$file_name);
							//doUploadFile("themeattr", $key, SITE_PATH.IMG_UPLOAD_FOLDER."theme_images/", array("jpg","jpeg","gif","png"));
						}elseif($attr[2] == "filetag"){
							move_uploaded_file($tmp_name, SITE_PATH.FILE_UPLOAD_FOLDER."theme_files/".$file_name) or die("Cannot upload file");
							exec ("chmod 777 ".SITE_PATH.FILE_UPLOAD_FOLDER."theme_files/".$file_name);
							//doUploadFile("themeattr", $key, SITE_PATH.FILE_UPLOAD_FOLDER."theme_files/", array("pdf","doc","docx","xls", "xlsx", "txt", "csv"));
						}
					}elseif($lastfile != '' || $delfile != ''){
						if($attr[2] == "imgtag"){
							if($delfile == ''){
								$file_name = $lastfile;
							}elseif($lastfile != ''){
								@unlink(SITE_PATH.IMG_UPLOAD_FOLDER."theme_images/".$lastfile);
							}
						}else{
							$lastfile = $_POST['themeattr']['lastfile_'.$attr[0]."_".$attr[1]];
							$delfile = $_POST['themeattr']['delfile_'.$attr[0]."_".$attr[1]];
							if($delfile == ''){
								$file_name = $lastfile;
							}elseif($lastfile != ''){
								@unlink(SITE_PATH.FILE_UPLOAD_FOLDER."theme_files/".$lastfile);
							}
						}
					}
					$cssattr_array[$newkey] .= "\t/*".$attr[1].": ".$file_name." */\n";
				}
			}
		}
	}
	if(count($cssattr_array) > 0){
		foreach($cssattr_array as $key => $part) $css .= $key." {\n".$part."}\n\n";
	}

	return $css;
}

?>