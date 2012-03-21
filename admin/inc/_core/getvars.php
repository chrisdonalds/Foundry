<?php
// ---------------------------
//
// VARIABLE HANDLER FUNCTIONS
//
// ---------------------------

/**
 * Return SQL-friendly sanitized text wrapped in ''
 * @param string $var
 * @param boolean $cleanhtml
 * @return string
 */
function getQuotedRequestVar($var, $cleanhtml = false){
    if (isset($var) && strlen($var) > 0){
        if(isset($_REQUEST["$var"])){
            $value = $_REQUEST["$var"];
            if (get_magic_quotes_gpc()) {
            	if ($cleanhtml) {
		        	// htmlentities to convert eg. ' to &#39;  (require an html_entities_decode later)
					// stripslashes to convert eg. \" to "
					// strip_tags to convert eg. <b> to &lt;b&gt;
					$value = strip_tags(htmlentities(stripslashes($value), ENT_QUOTES));
				}else{
					$value = stripslashes($value);
				}
    		}
			$value = iconv("UTF-8", "ISO-8859-1//IGNORE", $value);
			$value = str_replace("'", "&#39;", $value);
			$value = str_replace("\"", "&#34;", $value);
    		$value = "'" . mysql_escape_string($value) . "'";
    	}else{
    		$value = "''";
    	}
    	return $value;
    }else{
    	return "";
    }
}

/**
 * Return SQL-friendly sanitized text
 * @param string $var
 * @param boolean $stripbreak
 * @param integer $options
 * @param mixed $key
 * @param string $typecase
 * @return string
 */
function getRequestVar($var, $stripbreak = false, $options = ENT_QUOTES, $key = null, $typecase = null){
    if (isset($var) && strlen($var) > 0){
		if($key == null) {
			if(isset($_REQUEST[$var])) $request_var = $_REQUEST["$var"];
		}else{
			if(isset($_REQUEST[$var][$key])) $request_var = $_REQUEST["$var"][$key];
		}
        if(isset($request_var)){
        	// htmlentities to convert eg. ' to &#39;  (require an html_entities_decode later)
			// stripslashes to convert eg. \" to "
            if(!is_array($request_var)){
                $value = stripslashes(trim($request_var));
                $value = iconv("UTF-8", "ISO-8859-1//IGNORE", $value);
                $value = str_replace("'", "&#39;", $value);
                if ($options == ENT_QUOTES) {
                    $value = str_replace("\"", "&#34;", $value);
                }
                $value = str_replace("&nbsp;</p>", "&#160;</p>", $value);
                if ($stripbreak) {
                    $value = preg_replace("/^(<p>)+/i", "", $value);
                    $value = preg_replace("/(<\/p>)+$/i", "", $value);
                    $value = preg_replace("/(<br>)+$/i", "", $value);
                }
                if(in_array($typecase, array("string", "boolean", "bool", "float", "array", "object"))){
                	$value = settype($value, $typecase);
                }
                return $value;
            }else{
                return $request_var;
            }
        }else{
	        return null;
        }
    }else{
        return null;
    }
}

/**
 * Return sanitized text
 * @param string $content
 * @param integer $options
 * @return string
 */
function clean_text($content, $options = ENT_QUOTES){
    if (isset($content)){
    	// htmlentities to convert eg. ' to &#39;  (require an html_entities_decode later)
		// stripslashes to convert eg. \" to "
		$value = stripslashes(strip_tags(trim($content)));
		$value = iconv("UTF-8", "ISO-8859-1//IGNORE", $value);
		$value = str_replace("'", "&#39;", $value);
		if(($options & ENT_QUOTES) > 0) $value = str_replace("\"", "&#34;", $value);
		$value = str_replace("&nbsp;</p>", "&#160;</p>", $value);
        return $value;
    }else{
        return null;
    }
}

/**
 * Return sanitized text originally formatted by CKEditor
 * @param string $content
 * @param integer $options
 * @return string
 */
function clean_cke_text($content){
	$content = str_replace(array("&#034;"), array("\""), $content);
	$content = preg_replace("/(\.\.\/){1,}ckfinder\//i", WEB_URL.CKF_FOLDER, $content);
	return $content;
}

/**
 * Return sanitized array
 * @param string $var
 * @param integer $options
 * @return string
 */
function sanitizeArray($array, $options = ENT_QUOTES){
    if (is_array($array) || is_object($array)){
    	// htmlentities to convert eg. ' to &#39;  (require an html_entities_decode later)
		// stripslashes to convert eg. \" to "
		foreach($array as $key => $arrayval){
			$value = stripslashes(strip_tags(trim($arrayval)));
			$value = iconv("UTF-8", "ISO-8859-1//IGNORE", $value);
			$value = str_replace("'", "&#39;", $value);
			if(($options & ENT_QUOTES) > 0) $value = str_replace("\"", "&#34;", $value);
			$value = str_replace("&nbsp;</p>", "&#160;</p>", $value);
			$array[$key] = $value;
		}
        return $array;
    }else{
        return null;
    }
}

/**
 * Remove HTML tags from text
 * @param string $var
 * @return string
 */
function strip_html_tags($var){
	$var = preg_replace("/<([^>]+)>/i", "", $var);
	return $var;
}

/**
 * Return text formatted in one of four capitalization formats
 * @param string $text
 * @param string $cap_type [optional]
 * @return string
 */
function capitalizeText($text, $cap_type = CAP_WORDS) {
	if ($text != "") {
		switch ($cap_type) {
			case CAP_NONE:
				$text = strtolower($text);
				break;
			case CAP_FIRST:
				$text = strtoupper(substr($text, 0, 1)).strtolower(substr($text, 1));
				break;
			case CAP_WORDS:
				$text = mb_convert_case($text, MB_CASE_TITLE, "UTF-8");
				break;
			case CAP_ALL:
				$text = strtoupper($text);
				break;
		}
	}
	return $text;
}

/**
 * Return text truncated to specific number of words
 * @deprecated
 * @param string $content
 * @param integer $length [optional]
 * @param string $finish [optional] ...
 * @param string $linkto [optional] URL
 * @return string
 */
function limitWords($content, $length = 20, $finish = '...', $linkto = '') {
	// Clean and explode our content; strip all HTML tags, and special characters.
	$words = explode(' ', strip_tags(preg_replace('/[^(\x20-\x7F)]*/','', $content)));
	// Get a count of all words
	$count = count($words);
	$limit = ($count > $length) ? $length : $count;
	// if we have more words than we want to show, add finish
	if($linkto != '') $finish = ' [<a href="'.$linkto.'">'.$finish.'</a>]';
	$end   = ($count > $length) ? $finish : '';
	// create output
	for($w = 0; $w <= $limit; $w++) {
		$output .= $words[$w];
		if($w < $limit) $output .= ' ';
	}
	// return end result
	return $output.$end;
}

/**
 * Return string limited to specific length and appended with ...
 * @deprecated
 * @param string $text
 * @param integer $maxlen
 * @return string
 */
function formatText($text, $maxlen) {
	if(strlen($text) > $maxlen-3) $text=substr($text, 0, $maxlen-3)."...";
	return $text;
}

/**
 * Return cleaned, lower-cased text
 * @param string $text
 * @return string
 */
function condenseText($text) {
	if ($text != "") {
		$text = strtolower(preg_replace("/[ \.\[\$\|*\+\?\{\\]+/i", "", $text));
	}
	return $text;
}

/**
 * Advanced version of uc_words sensitive to geographic abbreviations, roman numerals, and name prefixes
 * @param string $str
 * @return string
 */
function ucwords_smart($str) {
	$all_uppercase = 'Po|Rr|Se|Sw|Ne|Nw|';
	$all_uppercase.= 'Bc|Ab|Sk|Mb|Qu|Nb|Ns|Pe|Nf|Nu|Nt|Yt|';		// Canadian provinces (ON same as English word)
	$all_uppercase.= 'Al|Ak|Az|Ar|Ca|Co|Ct|De|Fl|Ga|Id|Il|';
	$all_uppercase.= 'Ia|Ks|Ky|La|Me|Md|Ma|Mi|Mn|Ms|Mo|Mt|';
	$all_uppercase.= 'Ne|Nv|Nh|Nj|Nm|Ny|Nc|Nd|Oh|Pa|Ri|Sc|';
	$all_uppercase.= 'Sd|Tn|Tx|Ut|Vt|Va|Wa|Wv|Wi|Wy|';				// US States (HI, IN, OK, OR same as English words)
	$all_uppercase.= 'Ca|Us|Usa|Uk|';								// Country abbreviations
	$all_uppercase.= 'i|ii|iii|iv|v|vi|vii|viii|ix|x|xx|xxx|';
	$all_uppercase.= 'xl|l|lx|lxx|lxxx|lc|c|';						// Roman numerals
	$all_uppercase.= 'Xs|Cia|Fbi|Rcmp|Csis|Abc|Nbc|Cbs|Ctv|Cbc';	// Others
	$all_lowercase = 'De La|De Las|Der|Van De|Van Der|Vit De';
	$all_lowercase.= 'Von|Or|And|A|As|By|In|Of|Or|To|The';
	$prefixes = 'Mc';
	$suffixes = "'S|&#39;S";
	$is_name = false;

	// captialize all first letters
	$str = preg_replace('/\\b(\\w)/e', 'strtoupper("$1")', strtolower(trim($str)));

	if ($all_uppercase) {
		// capitalize acronymns and initialisms e.g. PHP
		$str = preg_replace("/\\b($all_uppercase)\\b/e", 'strtoupper("$1")', $str);
	}
	if ($all_lowercase) {
	   // decapitalize short words e.g. and
	   if ($is_name) {
		   // all occurences will be changed to lowercase
		   $str = preg_replace("/\\b($all_lowercase)\\b/e", 'strtolower("$1")', $str);
	   } else {
		   // first and last word will not be changed to lower case (i.e. titles)
		   $str = preg_replace("/(?<=\\W)($all_lowercase)(?=\\W)/e", 'strtolower("$1")', $str);
	   }
	}
	if ($prefixes) {
	   // capitalize letter after certain name prefixes e.g 'Mc'
	   $str = preg_replace("/\\b($prefixes)(\\w)/e", '"$1".strtoupper("$2")', $str);
	}
	if ($suffixes) {
	   // decapitalize certain word suffixes e.g. 's
	   $str = preg_replace("/(\\w)($suffixes)\\b/e", '"$1".strtolower("$2")', $str);
	}
	return $str;
}

/**
 * Advanced version of PHP extract.  ExtractVariables decodes HTML entities
 * @param array $arry
 */
function extractVariables($arry, $extract_type = EXTR_OVERWRITE, $prefix = null) {
    if(is_array($arry)){
        foreach ($arry as $key => $value){
            $tempvar = html_entity_decode($value, ENT_NOQUOTES);
            if(!isset($$key) || $extract_type == EXTR_OVERWRITE){
                if($extract_type == EXTR_PREFIX_ALL && !is_null($prefix)){
                    $GLOBALS[$prefix.$key] = $tempvar;
                }else{
                    $GLOBALS[$key] = $tempvar;
                }
            }elseif(($extract_type == EXTR_PREFIX_SAME || $extract_type == EXTR_PREFIX_ALL) && !is_null($prefix)){
                $GLOBALS[$prefix.$key] = $tempvar;
            }elseif($extract_type == EXTR_IF_EXISTS){
                $GLOBALS[$key] = $tempvar;
            }elseif($extract_type == EXTR_PREFIX_IF_EXISTS && !is_null($prefix)){
                $GLOBALS[$prefix.$key] = $tempvar;
            }elseif($extract_type == EXTR_SKIP){
                continue;
            }
        }
    }
}

/**
 * Return variable value, if set, or null
 * @param mixed $var
 * @return mixed
 */
function getIfSet(&$var){
	if(isset($var)){
		return $var;
	}else{
		return null;
	}
}

/**
 * Return integer value, if set, or 0
 * @param mixed $var
 */
function getIntValIfSet(&$var){
	return intval(getIfSet($var));
}

/**
 * Return the number of elements of an array if the array exists or zero if it doesn't
 * @param array $array
 * @return int
 */
function countIfSet(&$array){
	if(is_array(getIfSet($array))){
		return count($array);
	}else{
		return 0;
	}
}

/**
 * Return whether or not variable is '', blank, empty, not set, or null
 * @param mixed $var
 */
function isBlank(&$var){
	return (!isset($var) || $var == '' || (empty($var) && !is_numeric($var)) || is_null($var));
}
?>