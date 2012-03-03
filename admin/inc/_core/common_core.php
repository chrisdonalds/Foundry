<?php
// ---------------------------
//
// FOUNDRY COMMON FUNCTIONS
//  - Core Support -
//
// ---------------------------
//
// list of tool functions
// - Uses: MySQL 4.2+

define ("CORELOADED", true);
if(!defined("VALID_LOAD")) die ("This file cannot be accessed directly!");

// admin user levels
define ("ADMLEVEL_DEVELOPER", 0);
define ("ADMLEVEL_SYSADMIN", 1);
define ("ADMLEVEL_OWNER", 2);
define ("ADMLEVEL_USER", 4);
define ("ADMLEVEL_AUTHOR", 8);
define ("ADMLEVEL_GUEST", 16);

// start here
checkCodeVer();

// include supplementary core functions
require_once(SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_register.php");
require_once(SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_themes.php");
require_once(SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_users.php");
if(!defined("REGISTERLOADED") || !defined("THEMESLOADED") || !defined("USERSLOADED")) die("Common_register, common_themes and common_users are all required!");

// instantiate classes
include (SITE_PATH.ADMIN_FOLDER.LIB_FOLDER."system.class.php");
include (SITE_PATH.ADMIN_FOLDER.LIB_FOLDER."statistics.class.php");
$_system = SystemClass::init();
$_users  = UsersClass::init();
$_stats  = StatsClass::init();

// ----------- CORE FUNCTIONS ---------------

/**
 * Checks the code (system) version.  Ensures critical files are of compatible version
 * @internal
 */
function checkCodeVer(){
	$codever = str_replace('.', '', CODE_VER);
	if(strlen($codever) < 4) $codever .= "0";
    preg_match("/(.{1})(.{1})(.{2})/i", CODE_VER_CORE, $cvc);
	if(intval($codever) < CODE_VER_CORE) die($codever.': Code versioning mismatch error!  Must be version '.$cvc[1].'.'.$cvc[2].'.'.$cvc[3].' or higher.  Check CODE_VER in '.CONFIG_FOLDER.'configs.php.');
}

/**
 * Returns code (system) version
 * @return float
 */
function getCodeVer(){
    return convertCodeVer2Dec(CODE_VER);
}

/**
 * Convert version string (x.xx.xx) to a decimal (x.xxxx)
 * @param string $ver
 * @return float
 */
function convertCodeVer2Dec($ver){
	if($ver != ''){
		$ver_p = explode(".", $ver);
		$ver_r = $ver_p[0].".".sprintf("%02d", intval(getIfSet($ver_p[1]))).sprintf("%02d", intval(getIfSet($ver_p[2])));
		return $ver_r;
	}else{
		return 0;
	}
}

/**
 * Purge any login session data if time is beyond specific days, hours, minutes, or seconds since last login time
 * @param integer $days
 * @param integer $hrs
 * @param integer $mins
 * @param integer $secs
 */
function purgeLoginSessions($days, $hrs, $mins, $secs) {
	deleteRec("session_login", "logged_in_date < '".date("Y-m-d H:i:s", mktime(date("H")-$hrs, date("i")-$mins, date("s")-$secs, date("m"), date("d")-$days, date("Y")))."'");
	if (!isUserLoggedin()) {
        unset($_SESSION['admlogin']);
        unset($_SESSION['admuserid']);
        unset($_SESSION['admuserlevel']);
    }
}

/**
 * Prepare $_SESSION elements
 * @internal
 */
function prepareSessionElements(){
	if (!isset($_SESSION['admlogin'])) $_SESSION['admlogin'] = false;
	if (!isset($_SESSION['rootname'])) $_SESSION['rootname'] = '';
	if (!isset($_SESSION['rootid'])) $_SESSION['rootid'] = '';
	if (!isset($_SESSION['root'])) $_SESSION['root'] = '';
}

/**
 * Set the root sessioning
 * @param boolean $getval [optional]
 * @param string $persist [optional]
 * @return array
 */
function setRootSession($getval = false, $persist = ""){
	global $root, $roottable, $_page;

	//session_name('admin');
	//session_start();
	// root code from URL
	if($getval && $_GET['root'] != "") {
		$root = $_GET['root'];
		#print "FROM GET: ".$_GET['root'];
	}

	// root is blank so inherit it from session
	if($_page->menu['root'] != "" && $root == "") {
		$root = $_SESSION['root'];
		#print "FROM SESSION: ".$_SESSION['root'];
	}

	if($root == ""){
		// root is still blank, use the first root code from DB
		$rootrec = getRec($roottable, "*", "", "", "1");
		#print " FROM DB (ROOT BLANK)";
	}else{
		// root is not blank, find its record from DB
		$rootrec = getRec($roottable, "*", "`code` = '".$root."'", "", "1");
		#print " FROM DB (ROOT NOT BLANK)";
	}
	if(is_array($rootrec)){
		$_SESSION['root'] = $rootrec[0]['code'];
		$_SESSION['rootname'] = $rootrec[0]['name'];
		$_SESSION['rootid'] = $rootrec[0]['id'];
	}
	if(is_array($persist)){
		foreach($persist as $code => $value){
			$_SESSION[$code] = $value;
		}
	}
	#print ", ";
	_pr($_SESSION);
	return $rootrec;
}

/**
 * Create a nonce value used to validate form data
 * @param string $param
 */
function createNonce($param = ''){
	global $_users, $_page;

	$salt = SECURE_SALT;

	if(is_string($param)){
		if($param != '')
			return getHMAC('md5', $param, $salt);
		else
			return getHMAC('md5', $_users->id.$_users->username.$_page->title.'_n', $salt);
	}else{
		addErrorMsg("CreateNonce expects a string parameter.");
		return false;
	}
}

/**
 * Check nonce value against expected value
 * @param string $nonce
 */
function validateNonce($nonce){
	global $_users, $_page;

	if(is_string($nonce)){
		return ($nonce == $_page->nonce);
	}else{
		addErrorMsg("ValidateNonce expects a string parameter.");
		return false;
	}
}

/**
 * Use hash_hmac (if present) or custom hmac hasher if not.
 * @param string $algo
 * @param string $data
 * @param string $key
 * @param boolean $raw_output
 */
function getHMAC($algo, $data, $key, $raw_output = false){
	if(function_exists('hash_hmac')){
		return hash_hmac($algo, $data, $key);
	}else{
	    $algo = strtolower($algo);
	    $pack = 'H'.strlen($algo('test'));
	    $size = 64;
	    $opad = str_repeat(chr(0x5C), $size);
	    $ipad = str_repeat(chr(0x36), $size);

	    if(strlen($key) > $size){
	        $key = str_pad(pack($pack, $algo($key)), $size, chr(0x00));
	    }else{
	        $key = str_pad($key, $size, chr(0x00));
	    }

	    for ($i = 0; $i < strlen($key) - 1; $i++){
	        $opad[$i] = $opad[$i] ^ $key[$i];
	        $ipad[$i] = $ipad[$i] ^ $key[$i];
	    }

	    $output = $algo($opad.pack($pack, $algo($ipad.$data)));
	    return ($raw_output) ? pack($pack, $output) : $output;
	}
}

// ----------- SUPPORT FUNCTIONS ---------------

# --- Constants and Variables

/**
 * Return constant value
 * @param string $var
 * @return mixed
 */
function getConst($var) {
	if($var != "") {
		$var = strtoupper($var);
		if(defined($var)) {
			return constant($var);
		}else{
			return "Unknown Const";
		}
	}
}

/**
 * Return array with all constants starting with prefix
 * @param string $prefix
 * @return array
 */
function getConsts($prefix = ''){
    $arry = array();
    foreach(get_defined_constants() as $key => $val){
        if(substr(strtolower($key), 0, strlen($prefix)) == strtolower($prefix)) $arry[$key] = $val;
    }
    return $arry;
}

/**
 * Parse an attribute string in the form attr:'value', attr:'value...
 * @param string $atts
 * @param array $defaults [optional]
 * @return array Attributes in array
 */
function parseAttributes($atts, $defaults = null){
	$attrs = array();
	if(is_array($defaults)) foreach($defaults as $elem) $attrs[$elem] = "";
	$parts = explode(",", $atts);
	if(is_array($parts)){
		foreach($parts as $part){
			$subparts = explode(":", $part, 2);
			if(count($subparts) > 1)
				$attrs[trim($subparts[0])] = trim($subparts[1], "' ");
		}
	}
	return $attrs;
}

/**
 * Swap two variable values
 * @param object $text1
 * @param object $text2
 * @return array
 */
function swap($obj1, $obj2){
	if($obj1 != "" AND $obj2 != "") {
		$temp = $obj1;
		$obj1 = $obj2;
		$obj2 = $temp;
	}
	return array($obj1, $obj2);
}

/**
 * Search for value in multidimensional array and return key
 * @param string $needle
 * @param array $haystack
 * @param boolean $bykey    True to search for a key instead of a value (default)
 * @return mixed
 */
function multiarray_search($needle, $haystack, $bykey = false) {
    if(is_array($haystack)){
        foreach($haystack as $key => $value){
            if(is_array($value)){
                if(($rtn = multiarray_search($needle, $value, $bykey)) !== false) return $rtn;
            }elseif($value === $needle && !$bykey){
                return $key;
            }elseif($key === $needle && $bykey){
                return $value;
            }
        }
    }
    return false;
}

/**
 * An upgraded version of array_merge where intersecting keys are made unique
 * @param array $array1
 * @param array $array2
 * @param array $keysuffix
 * @return array
 */
function array_blend($array1, $array2, $keysuffix){
	if(is_array($array1) && is_array($array2)){
		foreach($array1 as $key => $value){
			if(isset($array2[$key])) {
				// make shared key in array1 unique by adding a suffix to its key
				$array2[$key.$keysuffix] = $array2[$key];
			}
		}
		$array3 = $array1 + $array2;
	}
	return $array3;
}

/**
 * Advanced version of array concatenation where arrays are tested and only concatenated if both are valid
 * @param array $array1
 * @param array $array2
 * @return array
 */
function array_concat($array1, $array2){
    if(is_array($array1) && is_array($array2)){
        return $array1 + $array2;
    }elseif(is_array($array1)){
        return $array1;
    }elseif(is_array($array2)){
        return $array2;
    }else{
        return array();
    }
}

/**
 * Returns array containing unique values from a multidimensional array
 * @param array $array
 */
function multiarray_unique($array){
	$m_array = array();
	if(is_array($array)){
		foreach($array as $elem){
			$m_array = array_merge($m_array, (array)$elem);
		}
		$m_array = array_unique($m_array);
	}
	return $m_array;
}

/**
 * Return the first matching value of a search for needle array elements in the haystack
 * @param array $haystack
 * @param array $needle_array
 * @return string
 */
function getFirstMatch($haystack, $needle_array){
    $match = '';
    if(is_array($haystack) && is_array($needle_array)){
        if(count($needle_array) > 0 && count($haystack) > 0){
            foreach($needle_array as $field){
                if(isset($haystack[$field])){
                    $match = $haystack[$field];
                    break;
                }
            }
        }
    }
    return $match;
}

/**
 * Output email address with the '@' symbol converted to GD graphic
 * @param string $email Address to obfuscate
 * @param integer $fs	Font size
 * @param string $fg	Font color
 */
function maskEmail($email, $fs = 3, $fg = "000000"){
    $link = preg_replace('/([\w]+)@([\w]+)\.([a-z0-9\.]+)/i', "javascript: parse_email('$1', '$2', '$3')", $email);
    echo '<a href="'.$link.'"><img src="'.WEB_URL.ADMIN_FOLDER.CORE_FOLDER.'gdfuncs.php?op=mask&st='.urlencode(base64_encode($email)).'&fs='.$fs.'&fg='.$fg.'"></a>';
}

/**
 * Validate email formatting.  This function does not determine if an email address
 * can receive email messages, only that the address is well-formed.
 * @param string $email
 */
function validateEmail($email){
    $valid = preg_match("/^[A-Z0-9._%+-]+@(?:[A-Z0-9-]+\.)+(?:[A-Z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum)$/i", $email);
    return ($valid > 0);
}

# --- Date/Time

/**
 * Split date string into year, month, day and place parts into GLOBALS
 * @param string $varname
 */
function tokenizeDateVar($varname){
    $datevar = $GLOBALS[$varname];
	if($datevar == "" || $datevar == "0000-00-00") $datevar = date("Y/m/d");
	$GLOBALS[$varname.'_m'] = date("m", strtotime($datevar));
	$GLOBALS[$varname.'_d'] = date("j", strtotime($datevar));
	$GLOBALS[$varname.'_y'] = date("Y", strtotime($datevar));
    $GLOBALS[$varname] = $datevar;
}

/**
 * Return whether value is a valid date
 * @param str $date
 * @param str $format
 * @return boolean
 */
function isDate($date, $format){
    $date = trim($date);
    return (strtotime($date) !== false);
}

/**
 * Return whether or not parameter is a valid date
 * @param string $date
 * @param string $format
 * @return boolean
 */
function validateDate($date, $format = ""){
    if($format == "") $format = DATE_FORMAT;
    switch(strtoupper($format)){
        case 'YYYY/MM/DD':
        case 'YYYY-MM-DD':
            preg_match('/([\d]{4})[-.\/]([\d]{2})[-.\/]([\d]{2})/', $date, $matches);
            $y = getIfSet($matches[1]);
            $m = getIfSet($matches[2]);
            $d = getIfSet($matches[3]);
            break;
        case 'YYYY/DD/MM':
        case 'YYYY-DD-MM':
            preg_match('/([\d]{4})[-.\/]([\d]{2})[-.\/]([\d]{2})/', $date, $matches);
            $y = getIfSet($matches[1]);
            $d = getIfSet($matches[2]);
            $m = getIfSet($matches[3]);
            break;
        case 'DD-MM-YYYY':
        case 'DD/MM/YYYY':
            preg_match('/([\d]{2})[-.\/]([\d]{2})[-.\/]([\d]{4})/', $date, $matches);
            $d = getIfSet($matches[1]);
            $m = getIfSet($matches[2]);
            $y = getIfSet($matches[1]);
            break;
        case 'MM-DD-YYYY':
        case 'MM/DD/YYYY':
            preg_match('/([\d]{2})[-.\/]([\d]{2})[-.\/]([\d]{4})/', $date, $matches);
            $m = getIfSet($matches[1]);
            $d = getIfSet($matches[2]);
            $y = getIfSet($matches[1]);
            break;
        case 'YYYYMMDD':
            $y = substr($date, 0, 4);
            $m = substr($date, 4, 2);
            $d = substr($date, 6, 2);
            break;
        case 'YYYYDDMM':
            $y = substr($date, 0, 4);
            $d = substr($date, 4, 2);
            $m = substr($date, 6, 2);
            break;
        default:
            addErrorMsg("Invalid Date Format '$format'");
    }
    return ((checkdate($m, $d, $y)) ? date(PHP_DATE_FORMAT, mktime(0, 0, 0, $m, $d, $y)) : null);
}

/**
 * Return array of timezones
 * @return array
 */
function getTimezones(){
    $tz_array = array(
        "Africa/Abidjan" => "Abidjan", "Africa/Accra" => "Accra", "Africa/Addis_Ababa" => "Addis Ababa",
        "Africa/Algiers" => "Algiers", "Africa/Asmara" => "Asmara", "Africa/Bamako" => "Bamako",
        "Africa/Bangui" => "Bangui", "Africa/Banjul" => "Banjul", "Africa/Bissau" => "Bissau",
        "Africa/Blantyre" => "Blantyre", "Africa/Brazzaville" => "Brazzaville", "Africa/Bujumbura" => "Bujumbura",
        "Africa/Cairo" => "Cairo", "Africa/Casablanca" => "Casablanca", "Africa/Ceuta" => "Ceuta",
        "Africa/Conakry" => "Conakry", "Africa/Dakar" => "Dakar", "Africa/Dar_es_Salaam" => "Dar es Salaam",
        "Africa/Djibouti" => "Djibouti", "Africa/Douala" => "Douala", "Africa/El_Aaiun" => "El Aaiun",
        "Africa/Freetown" => "Freetown", "Africa/Gaborone" => "Gaborone", "Africa/Harare" => "Harare",
        "Africa/Johannesburg" => "Johannesburg", "Africa/Kampala" => "Kampala", "Africa/Khartoum" => "Khartoum",
        "Africa/Kigali" => "Kigali", "Africa/Kinshasa" => "Kinshasa", "Africa/Lagos" => "Lagos",
        "Africa/Libreville" => "Libreville", "Africa/Lome" => "Lome", "Africa/Luanda" => "Luanda",
        "Africa/Lubumbashi" => "Lubumbashi", "Africa/Lusaka" => "Lusaka", "Africa/Malabo" => "Malabo",
        "Africa/Maputo" => "Maputo", "Africa/Maseru" => "Maseru", "Africa/Mbabane" => "Mbabane",
        "Africa/Mogadishu" => "Mogadishu", "Africa/Monrovia" => "Monrovia", "Africa/Nairobi" => "Nairobi",
        "Africa/Ndjamena" => "Ndjamena", "Africa/Niamey" => "Niamey", "Africa/Nouakchott" => "Nouakchott",
        "Africa/Ouagadougou" => "Ouagadougou", "Africa/Porto-Novo" => "Porto-Novo", "Africa/Sao_Tome" => "Sao Tome",
        "Africa/Tripoli" => "Tripoli", "Africa/Tunis" => "Tunis", "Africa/Windhoek" => "Windhoek",
        "America/Adak" => "Adak", "America/Anchorage" => "Anchorage", "America/Anguilla" => "Anguilla",
        "America/Antigua" => "Antigua", "America/Araguaina" => "Araguaina", "America/Argentina/Buenos_Aires" => "Argentina - Buenos Aires",
        "America/Argentina/Catamarca" => "Argentina - Catamarca", "America/Argentina/Cordoba" => "Argentina - Cordoba",
        "America/Argentina/Jujuy" => "Argentina - Jujuy", "America/Argentina/La_Rioja" => "Argentina - La Rioja",
        "America/Argentina/Mendoza" => "Argentina - Mendoza", "America/Argentina/Rio_Gallegos" => "Argentina - Rio Gallegos",
        "America/Argentina/Salta" => "Argentina - Salta", "America/Argentina/San_Juan" => "Argentina - San Juan",
        "America/Argentina/San_Luis" => "Argentina - San Luis", "America/Argentina/Tucuman" => "Argentina - Tucuman",
        "America/Argentina/Ushuaia" => "Argentina - Ushuaia", "America/Aruba" => "Aruba",
        "America/Asuncion" => "Asuncion", "America/Atikokan" => "Atikokan", "America/Bahia" => "Bahia",
        "America/Barbados" => "Barbados", "America/Belem" => "Belem", "America/Belize" => "Belize",
        "America/Blanc-Sablon" => "Blanc-Sablon", "America/Boa_Vista" => "Boa Vista",
        "America/Bogota" => "Bogota", "America/Boise" => "Boise", "America/Cambridge_Bay" => "Cambridge Bay",
        "America/Campo_Grande" => "Campo Grande", "America/Cancun" => "Cancun", "America/Caracas" => "Caracas",
        "America/Cayenne" => "Cayenne", "America/Cayman" => "Cayman", "America/Chicago" => "Chicago",
        "America/Chihuahua" => "Chihuahua", "America/Costa_Rica" => "Costa Rica", "America/Cuiaba" => "Cuiaba",
        "America/Curacao" => "Curacao", "America/Danmarkshavn" => "Danmarkshavn", "America/Dawson" => "Dawson",
        "America/Dawson_Creek" => "Dawson Creek", "America/Denver" => "Denver", "America/Detroit" => "Detroit",
        "America/Dominica" => "Dominica", "America/Edmonton" => "Edmonton", "America/Eirunepe" => "Eirunepe",
        "America/El_Salvador" => "El Salvador", "America/Fortaleza" => "Fortaleza", "America/Glace_Bay" => "Glace Bay",
        "America/Godthab" => "Godthab", "America/Goose_Bay" => "Goose Bay", "America/Grand_Turk" => "Grand Turk",
        "America/Grenada" => "Grenada", "America/Guadeloupe" => "Guadeloupe", "America/Guatemala" => "Guatemala",
        "America/Guayaquil" => "Guayaquil", "America/Guyana" => "Guyana", "America/Halifax" => "Halifax",
        "America/Havana" => "Havana", "America/Hermosillo" => "Hermosillo", "America/Indiana/Indianapolis" => "Indiana - Indianapolis",
        "America/Indiana/Knox" => "Indiana - Knox", "America/Indiana/Marengo" => "Indiana - Marengo",
        "America/Indiana/Petersburg" => "Indiana - Petersburg", "America/Indiana/Tell_City" => "Indiana - Tell City",
        "America/Indiana/Vevay" => "Indiana - Vevay", "America/Indiana/Vincennes" => "Indiana - Vincennes",
        "America/Indiana/Winamac" => "Indiana - Winamac", "America/Inuvik" => "Inuvik",
        "America/Iqaluit" => "Iqaluit", "America/Jamaica" => "Jamaica", "America/Juneau" => "Juneau",
        "America/Kentucky/Louisville" => "Kentucky - Louisville", "America/Kentucky/Monticello" => "Kentucky - Monticello",
        "America/La_Paz" => "La Paz", "America/Lima" => "Lima", "America/Los_Angeles" => "Los Angeles",
        "America/Maceio" => "Maceio", "America/Managua" => "Managua", "America/Manaus" => "Manaus",
        "America/Marigot" => "Marigot", "America/Martinique" => "Martinique", "America/Mazatlan" => "Mazatlan",
        "America/Menominee" => "Menominee", "America/Merida" => "Merida", "America/Mexico_City" => "Mexico City",
        "America/Miquelon" => "Miquelon", "America/Moncton" => "Moncton", "America/Monterrey" => "Monterrey",
        "America/Montevideo" => "Montevideo", "America/Montreal" => "Montreal", "America/Montserrat" => "Montserrat",
        "America/Nassau" => "Nassau", "America/New_York" => "New York", "America/Nipigon" => "Nipigon",
        "America/Nome" => "Nome", "America/Noronha" => "Noronha", "America/North_Dakota/Center" => "North Dakota - Center",
        "America/North_Dakota/New_Salem" => "North Dakota - New Salem", "America/Panama" => "Panama",
        "America/Pangnirtung" => "Pangnirtung", "America/Paramaribo" => "Paramaribo", "America/Phoenix" => "Phoenix",
        "America/Port-au-Prince" => "Port-au-Prince", "America/Port_of_Spain" => "Port of Spain",
        "America/Porto_Velho" => "Porto Velho", "America/Puerto_Rico" => "Puerto Rico",
        "America/Rainy_River" => "Rainy River", "America/Rankin_Inlet" => "Rankin Inlet",
        "America/Recife" => "Recife", "America/Regina" => "Regina", "America/Resolute" => "Resolute",
        "America/Rio_Branco" => "Rio Branco", "America/Santarem" => "Santarem", "America/Santiago" => "Santiago",
        "America/Santo_Domingo" => "Santo Domingo", "America/Sao_Paulo" => "Sao Paulo",
        "America/Scoresbysund" => "Scoresbysund", "America/Shiprock" => "Shiprock",
        "America/St_Barthelemy" => "St Barthelemy", "America/St_Johns" => "St Johns",
        "America/St_Kitts" => "St Kitts", "America/St_Lucia" => "St Lucia",
        "America/St_Thomas" => "St Thomas", "America/St_Vincent" => "St Vincent",
        "America/Swift_Current" => "Swift Current", "America/Tegucigalpa" => "Tegucigalpa",
        "America/Thule" => "Thule", "America/Thunder_Bay" => "Thunder Bay",
        "America/Tijuana" => "Tijuana", "America/Toronto" => "Toronto", "America/Tortola" => "Tortola",
        "America/Vancouver" => "Vancouver", "America/Whitehorse" => "Whitehorse",
        "America/Winnipeg" => "Winnipeg", "America/Yakutat" => "Yakutat",
        "America/Yellowknife" => "Yellowknife", "Antarctica/Casey" => "Casey",
        "Antarctica/Davis" => "Davis", "Antarctica/DumontDUrville" => "DumontDUrville",
        "Antarctica/Mawson" => "Mawson", "Antarctica/McMurdo" => "McMurdo",
        "Antarctica/Palmer" => "Palmer", "Antarctica/Rothera" => "Rothera", "Antarctica/South_Pole" => "South Pole",
        "Antarctica/Syowa" => "Syowa", "Antarctica/Vostok" => "Vostok", "Arctic/Longyearbyen" => "Longyearbyen",
        "Asia/Aden" => "Aden", "Asia/Almaty" => "Almaty", "Asia/Amman" => "Amman",
        "Asia/Anadyr" => "Anadyr", "Asia/Aqtau" => "Aqtau", "Asia/Aqtobe" => "Aqtobe",
        "Asia/Ashgabat" => "Ashgabat", "Asia/Baghdad" => "Baghdad", "Asia/Bahrain" => "Bahrain",
        "Asia/Baku" => "Baku", "Asia/Bangkok" => "Bangkok", "Asia/Beirut" => "Beirut",
        "Asia/Bishkek" => "Bishkek", "Asia/Brunei" => "Brunei", "Asia/Choibalsan" => "Choibalsan",
        "Asia/Chongqing" => "Chongqing", "Asia/Colombo" => "Colombo", "Asia/Damascus" => "Damascus",
        "Asia/Dhaka" => "Dhaka", "Asia/Dili" => "Dili", "Asia/Dubai" => "Dubai",
        "Asia/Dushanbe" => "Dushanbe", "Asia/Gaza" => "Gaza", "Asia/Harbin" => "Harbin",
        "Asia/Ho_Chi_Minh" => "Ho Chi Minh", "Asia/Hong_Kong" => "Hong Kong", "Asia/Hovd" => "Hovd",
        "Asia/Irkutsk" => "Irkutsk", "Asia/Jakarta" => "Jakarta", "Asia/Jayapura" => "Jayapura",
        "Asia/Jerusalem" => "Jerusalem", "Asia/Kabul" => "Kabul", "Asia/Kamchatka" => "Kamchatka",
        "Asia/Karachi" => "Karachi", "Asia/Kashgar" => "Kashgar", "Asia/Kathmandu" => "Kathmandu",
        "Asia/Kolkata" => "Kolkata", "Asia/Krasnoyarsk" => "Krasnoyarsk", "Asia/Kuala_Lumpur" => "Kuala Lumpur",
        "Asia/Kuching" => "Kuching", "Asia/Kuwait" => "Kuwait", "Asia/Macau" => "Macau",
        "Asia/Magadan" => "Magadan", "Asia/Makassar" => "Makassar", "Asia/Manila" => "Manila",
        "Asia/Muscat" => "Muscat", "Asia/Nicosia" => "Nicosia", "Asia/Novosibirsk" => "Novosibirsk",
        "Asia/Omsk" => "Omsk", "Asia/Oral" => "Oral", "Asia/Phnom_Penh" => "Phnom Penh",
        "Asia/Pontianak" => "Pontianak", "Asia/Pyongyang" => "Pyongyang", "Asia/Qatar" => "Qatar",
        "Asia/Qyzylorda" => "Qyzylorda", "Asia/Rangoon" => "Rangoon", "Asia/Riyadh" => "Riyadh",
        "Asia/Sakhalin" => "Sakhalin", "Asia/Samarkand" => "Samarkand", "Asia/Seoul" => "Seoul",
        "Asia/Shanghai" => "Shanghai", "Asia/Singapore" => "Singapore", "Asia/Taipei" => "Taipei",
        "Asia/Tashkent" => "Tashkent", "Asia/Tbilisi" => "Tbilisi", "Asia/Tehran" => "Tehran",
        "Asia/Thimphu" => "Thimphu", "Asia/Tokyo" => "Tokyo", "Asia/Ulaanbaatar" => "Ulaanbaatar",
        "Asia/Urumqi" => "Urumqi", "Asia/Vientiane" => "Vientiane", "Asia/Vladivostok" => "Vladivostok",
        "Asia/Yakutsk" => "Yakutsk", "Asia/Yekaterinburg" => "Yekaterinburg",
        "Asia/Yerevan" => "Yerevan", "Atlantic/Azores" => "Azores", "Atlantic/Bermuda" => "Bermuda",
        "Atlantic/Canary" => "Canary", "Atlantic/Cape_Verde" => "Cape Verde",
        "Atlantic/Faroe" => "Faroe", "Atlantic/Madeira" => "Madeira", "Atlantic/Reykjavik" => "Reykjavik",
        "Atlantic/South_Georgia" => "South Georgia", "Atlantic/Stanley" => "Stanley",
        "Atlantic/St_Helena" => "St Helena", "Australia/Adelaide" => "Adelaide",
        "Australia/Brisbane" => "Brisbane", "Australia/Broken_Hill" => "Broken Hill",
        "Australia/Currie" => "Currie", "Australia/Darwin" => "Darwin",
        "Australia/Eucla" => "Eucla", "Australia/Hobart" => "Hobart", "Australia/Lindeman" => "Lindeman",
        "Australia/Lord_Howe" => "Lord Howe", "Australia/Melbourne" => "Melbourne",
        "Australia/Perth" => "Perth", "Australia/Sydney" => "Sydney", "Europe/Amsterdam" => "Amsterdam",
        "Europe/Andorra" => "Andorra", "Europe/Athens" => "Athens", "Europe/Belgrade" => "Belgrade",
        "Europe/Berlin" => "Berlin", "Europe/Bratislava" => "Bratislava", "Europe/Brussels" => "Brussels",
        "Europe/Bucharest" => "Bucharest", "Europe/Budapest" => "Budapest", "Europe/Chisinau" => "Chisinau",
        "Europe/Copenhagen" => "Copenhagen", "Europe/Dublin" => "Dublin", "Europe/Gibraltar" => "Gibraltar",
        "Europe/Guernsey" => "Guernsey", "Europe/Helsinki" => "Helsinki", "Europe/Isle_of_Man" => "Isle of Man",
        "Europe/Istanbul" => "Istanbul", "Europe/Jersey" => "Jersey", "Europe/Kaliningrad" => "Kaliningrad",
        "Europe/Kiev" => "Kiev", "Europe/Lisbon" => "Lisbon", "Europe/Ljubljana" => "Ljubljana",
        "Europe/London" => "London", "Europe/Luxembourg" => "Luxembourg", "Europe/Madrid" => "Madrid",
        "Europe/Malta" => "Malta", "Europe/Mariehamn" => "Mariehamn", "Europe/Minsk" => "Minsk",
        "Europe/Monaco" => "Monaco", "Europe/Moscow" => "Moscow", "Europe/Oslo" => "Oslo",
        "Europe/Paris" => "Paris", "Europe/Podgorica" => "Podgorica", "Europe/Prague" => "Prague",
        "Europe/Riga" => "Riga", "Europe/Rome" => "Rome", "Europe/Samara" => "Samara",
        "Europe/San_Marino" => "San Marino", "Europe/Sarajevo" => "Sarajevo", "Europe/Simferopol" => "Simferopol",
        "Europe/Skopje" => "Skopje", "Europe/Sofia" => "Sofia", "Europe/Stockholm" => "Stockholm",
        "Europe/Tallinn" => "Tallinn", "Europe/Tirane" => "Tirane", "Europe/Uzhgorod" => "Uzhgorod",
        "Europe/Vaduz" => "Vaduz", "Europe/Vatican" => "Vatican", "Europe/Vienna" => "Vienna",
        "Europe/Vilnius" => "Vilnius", "Europe/Volgograd" => "Volgograd", "Europe/Warsaw" => "Warsaw",
        "Europe/Zagreb" => "Zagreb", "Europe/Zaporozhye" => "Zaporozhye", "Europe/Zurich" => "Zurich",
        "Indian/Antananarivo" => "Antananarivo", "Indian/Chagos" => "Chagos", "Indian/Christmas" => "Christmas",
        "Indian/Cocos" => "Cocos", "Indian/Comoro" => "Comoro", "Indian/Kerguelen" => "Kerguelen",
        "Indian/Mahe" => "Mahe", "Indian/Maldives" => "Maldives", "Indian/Mauritius" => "Mauritius",
        "Indian/Mayotte" => "Mayotte", "Indian/Reunion" => "Reunion", "Pacific/Apia" => "Apia",
        "Pacific/Auckland" => "Auckland", "Pacific/Chatham" => "Chatham", "Pacific/Easter" => "Easter",
        "Pacific/Efate" => "Efate", "Pacific/Enderbury" => "Enderbury", "Pacific/Fakaofo" => "Fakaofo",
        "Pacific/Fiji" => "Fiji", "Pacific/Funafuti" => "Funafuti", "Pacific/Galapagos" => "Galapagos",
        "Pacific/Gambier" => "Gambier", "Pacific/Guadalcanal" => "Guadalcanal", "Pacific/Guam" => "Guam",
        "Pacific/Honolulu" => "Honolulu", "Pacific/Johnston" => "Johnston", "Pacific/Kiritimati" => "Kiritimati",
        "Pacific/Kosrae" => "Kosrae", "Pacific/Kwajalein" => "Kwajalein", "Pacific/Majuro" => "Majuro",
        "Pacific/Marquesas" => "Marquesas", "Pacific/Midway" => "Midway", "Pacific/Nauru" => "Nauru",
        "Pacific/Niue" => "Niue", "Pacific/Norfolk" => "Norfolk", "Pacific/Noumea" => "Noumea",
        "Pacific/Pago_Pago" => "Pago Pago", "Pacific/Palau" => "Palau", "Pacific/Pitcairn" => "Pitcairn",
        "Pacific/Ponape" => "Ponape", "Pacific/Port_Moresby" => "Port Moresby", "Pacific/Rarotonga" => "Rarotonga",
        "Pacific/Saipan" => "Saipan", "Pacific/Tahiti" => "Tahiti", "Pacific/Tarawa" => "Tarawa",
        "Pacific/Tongatapu" => "Tongatapu", "Pacific/Truk" => "Truk", "Pacific/Wake" => "Wake",
        "Pacific/Wallis" => "Wallis", "UTC" => "UTC",
        "Offset/UTC-12" => "UTC-12", "Offset/UTC-11.5" => "UTC-11:30",
        "Offset/UTC-11" => "UTC-11", "Offset/UTC-10.5" => "UTC-10:30",
        "Offset/UTC-10" => "UTC-10", "Offset/UTC-9.5" => "UTC-9:30",
        "Offset/UTC-9" => "UTC-9", "Offset/UTC-8.5" => "UTC-8:30",
        "Offset/UTC-8" => "UTC-8", "Offset/UTC-7.5" => "UTC-7:30",
        "Offset/UTC-7" => "UTC-7", "Offset/UTC-6.5" => "UTC-6:30",
        "Offset/UTC-6" => "UTC-6", "Offset/UTC-5.5" => "UTC-5:30",
        "Offset/UTC-5" => "UTC-5", "Offset/UTC-4.5" => "UTC-4:30",
        "Offset/UTC-4" => "UTC-4", "Offset/UTC-3.5" => "UTC-3:30",
        "Offset/UTC-3" => "UTC-3", "Offset/UTC-2.5" => "UTC-2:30",
        "Offset/UTC-2" => "UTC-2", "Offset/UTC-1.5" => "UTC-1:30",
        "Offset/UTC-1" => "UTC-1", "Offset/UTC-0.5" => "UTC-0:30",
        "Offset/UTC+0" => "UTC+0", "Offset/UTC+0.5" => "UTC+0:30",
        "Offset/UTC+1" => "UTC+1", "Offset/UTC+1.5" => "UTC+1:30",
        "Offset/UTC+2" => "UTC+2", "Offset/UTC+2.5" => "UTC+2:30",
        "Offset/UTC+3" => "UTC+3", "Offset/UTC+3.5" => "UTC+3:30",
        "Offset/UTC+4" => "UTC+4", "Offset/UTC+4.5" => "UTC+4:30",
        "Offset/UTC+5" => "UTC+5", "Offset/UTC+5.5" => "UTC+5:30",
        "Offset/UTC+6" => "UTC+6", "Offset/UTC+6.5" => "UTC+6:30",
        "Offset/UTC+7" => "UTC+7", "Offset/UTC+7.5" => "UTC+7:30",
        "Offset/UTC+8" => "UTC+8", "Offset/UTC+8.5" => "UTC+8:30",
        "Offset/UTC+9" => "UTC+9", "Offset/UTC+9.5" => "UTC+9:30",
        "Offset/UTC+10" => "UTC+10", "Offset/UTC+10.5" => "UTC+10:30",
        "Offset/UTC+11" => "UTC+11", "Offset/UTC+11.5" => "UTC+11:30",
        "Offset/UTC+12" => "UTC+12", "Offset/UTC+12.5" => "UTC+12:30",
        "Offset/UTC+13" => "UTC+13", "Offset/UTC+13.5" => "UTC+13:20",
        "Offset/UTC+14" => "UTC+14"
        );
    return $tz_array;
}

function getCitiesWithTimezones(){
	$timezones = DateTimeZone::listAbbreviations();

	$cities = array();
	foreach($timezones as $key => $zones){
	    foreach($zones as $id => $zone){
	        /**
	         * Only get timezones explicitely not part of "Others".
	         * @see http://www.php.net/manual/en/timezones.others.php
	         */
	        if (preg_match( '/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $zone['timezone_id']))
	            $cities[$zone['timezone_id']][] = $key;
	    }
	}

	// For each city, have a comma separated list of all possible timezones for that city.
	foreach($cities as $key => $value)
	    $cities[$key] = join(', ', $value);

	// Only keep one city (the first and also most important) for each set of possibilities.
	$cities = array_unique($cities);

	// Sort by area/city name.
	ksort($cities);

	return $cities;
}

# --- Filesystem

/**
 * Return whether or not SSL is in use
 * @return boolean
 */
function checkSSL() {
	if(isset($_SERVER['HTTPS'])){
		if(strtolower($_SERVER['HTTPS']) == 'on' || intval($_SERVER['HTTPS']) == 1) return true;
	}elseif(isset($_SERVER['SERVER_PORT'])){
		if($_SERVER['SERVER_PORT'] == '443') return true;
	}
	return false;
}

/**
 * Return a byte-formatted size
 * @param string $data
 * @return string
 */
function formatFileSize($data) {
	if($data < 1024) {
		// bytes
    	return $data . " B";
    }else if($data < 1048576) {
		// kilobytes
    	return round(($data / 1024), 1) . "KB";
    }else if( $data < 1073741824) {
		// megabytes
        return round(($data / 1048576), 1) . " MB";
    }else {
        return round(($data / 1073741824), 1) . " GB";
    }
}

/**
 * Return array of limited image dimensions
 * @param string $filename
 * @param integer $wdim [optional]
 * @param integer $hdim [optional]
 * @return array
 */
function constrainImage($filename, $wdim = 0, $hdim = 0, $exact = false) {
	if($filename != "") {
		if(!file_exists($filename) || substr($filename, -1, 1) == "/") return false;
		list($width, $height, $type, $attr) = @getimagesize($filename);
		$origwidth = $width;
		$origheight= $height;
		if ($wdim > 0) {
			// if $width > $wdim, get reducer ratio and multiple by $height
			if (($width > $wdim || ($width != $wdim && $exact)) && $height > 0) {
				$ratio = $wdim / $width;
				$width = $wdim;
				$height = intval($ratio * $height);
			}
		}
		if ($hdim > 0) {
			// if $height > $hdim, get reducer ratio and multiple by $width
			if (($height > $hdim || ($height != $hdim && $exact)) && $width > 0) {
				$ratio = $hdim / $height;
				$height = $hdim;
				$width = intval($ratio * $width);
			}
		}
	}
	return array($width, $height, $origwidth, $origheight);
}

/**
 * Sanitize URL and include into system
 * @param string $url
 * @param boolean $required
 */
function includeFile($url, $required = false){
	$url = trim($url);
	if(preg_match("/[\s]/i", $url)) $url = urlencode($url);

	@include($url);
}

/**
 * Redirect browser to URL
 * @param string $url
 */
function gotoPage($url){
	$url = trim($url);
	if(preg_match("/[\s]/i", $url)) $url = urlencode($url);

	if ($url != ""){
		if(!headers_sent()){
			header("location: ".$url);
		}else{
	 		print "<script type=\"text/Javascript\">window.location='$url';</script>";
		}
	}
}

/**
 * Redirect browser to Edit Page URL
 * @param string $url
 */
function gotoEditPage($url = ""){
	global $_page;

	$url = trim($url);
	if(preg_match("/[\s]/i", $url)) $url = urlencode($url);

	if($url == ""){
		$path = $_SERVER['PHP_SELF'];
		$file = str_replace("add", "edit", basename($path));
		$folder = getLowestChildFolder(dirname($path))."/";
		$url = WEB_URL.ADMIN_FOLDER.$folder.$file."?row_id={$_page->row_id}";
	}
 	print "<script type=\"text/Javascript\">window.location='$url';</script>";
}

/**
 * Check file existence and return either file or blank
 * @param string $file
 * @param string $inpath
 * @return string
 */
function checkFilePath($file, $inpath) {
	if(substr($inpath, -1, 1) != "/") $inpath .= "/";
	$path 		= pathinfo($file);
	$filename 	= $path['basename'];
	$file       = $inpath.$filename;
	if ($filename == "" || !@file_exists(SITE_PATH.$file)) $file = "";
	return $file;
}

/**
 * Check if image exists
 * @param object $image
 * @param object $inpath
 * @return
 */
function checkImagePath($image, $inpath, $noimgurl = NO_IMG, $fileprefix = null) {
    if($image != ""){
        if(substr($inpath, -1, 1) != "/") $inpath .= "/";
        $path         = pathinfo($image);
        $filename     = $path['basename'];
        $photo_pic    = $inpath.$fileprefix.$filename;
        if ($filename == "" || !@file_exists(SITE_PATH.$photo_pic)){
            $photo_pic = (($noimgurl != "") ? IMG_UPLOAD_FOLDER.$noimgurl : "");
        }
    }
    return $photo_pic;
}


/**
 * Check if thumbnail exists
 * @param object $image
 * @param object $inpath
 * @return
 */
function checkThumbPath($image, $inpath, $nothmurl = NO_THM, $fileprefix = null) {
    if($image != ""){
        if(substr($inpath, -1, 1) != "/") $inpath .= "/";
        $path         = pathinfo($image);
        $filename     = $path['basename'];
        $photo_pic    = $inpath.$fileprefix.$filename;
        if ($filename == "" || !@file_exists(SITE_PATH.$photo_pic)){
            $photo_pic = (($nothmurl != "") ? THM_UPLOAD_FOLDER.$nothmurl : "");
        }
    }
    return $photo_pic;
}

/**
 * Delete image and/or thumb files.  Returns array of blanks if successful
 * @param string $image
 * @param string $thumb
 * @return string
 */
function deleteImage($image, $thumb) {
	if(file_exists($image) && is_file($image)) {
		if(unlink($image)) $image = "";
	}else{
		$image = "";
	}
    $thumb_a = str_replace("thm_", "thm_a_", $thumb);
	if(file_exists($thumb) && is_file($thumb)) {
		if(unlink($thumb)) $thumb = "";
	}else{
		$thumb = "";
	}
	if(file_exists($thumb_a) && is_file($thumb_a)) {
		if(unlink($thumb)) $thumb_a = "";
	}else{
		$thumb_a = "";
	}
	return array($image, $thumb);
}

/**
 * Delete file.  Returns blank if successful
 * @param string $file
 * @return string
 */
function deleteFile($file) {
	if(file_exists($file) && is_file($file)) {
		if(unlink($file)) $file = "";
	}else{
		$file = "";
	}
	return $file;
}

/**
 * Return the last/lowest subfolder in path
 * @param string $dir
 * @return string
 */
function getLowestChildFolder($dir){
    if($dir != ''){
        $dir_arry = explode("/", rtrim($dir, "/"));
        $last = count($dir_arry) - 1;
        return (($last >= 0) ? $dir_arry[$last] : "");
    }
}

/**
 * Return path relative to root (either URL or filesys)
 * @param string $dir
 * @param string $root
 * @return string
 */
function getRelativePath($dir, $root = SITE_PATH){
    return (preg_replace("/^".str_replace("/", "\/", $root)."/i", "", $dir));
}

/**
 * Convert octal string to mode array (opposite of convertArray2Mode)
 * @param string $mode
 * @return array
 */
function convertMode2Array($mode){
	$level = array(1=>"user", 2=>"group", 3=>"other");
	$outp = array();
	$inp = str_split($mode, 1);
	for($i=1; $i<4; $i++){
		$outp[$level[$i]]['read'] = (($inp[$i] & 1) > 0);
		$outp[$level[$i]]['write'] = (($inp[$i] & 2) > 0);
		$outp[$level[$i]]['exec'] = (($inp[$i] & 4) > 0);
	}
	return $outp;
}

/**
 * Convert mode array to octal string (opposite of convertMode2Array)
 * @param array $arry
 * @return string
 */
function convertArray2Mode(array $arry){
	$outp = "";
	$level = array(1=>"user", 2=>"group", 3=>"other");
	for($i=1; $i<4; $i++){
		$num = (getIntValIfSet($arry[$level[$i]]['read'])) + (getIntValIfSet($arry[$level[$i]]['write']) * 2) + (getIntValIfSet($arry[$level[$i]]['exec']) * 4);
		$outp .= "$num";
	}
	return $outp;
}

/**
 * Return existence of file using cURL
 * @param string $url
 * @return boolean
 */
function file_exists_2($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);

	/* Check for 404 (file not found). */
	$httpCode = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
	curl_close($ch);

	if($response !== false) {
		// url good
		if(in_array($httpCode, array(301, 302, 200)))
			return true;
		else
			return false;
	}else{
		return false;
	}
}

/**
 * Get file/folder permissions using PHP fileperms
 * @param string $file
 * @return string
 */
function getFilePerms($file, $tostr = false){
	if($file != ''){
		clearstatcache();
		$rtn = substr(sprintf('%o', @fileperms($file)), -4);
		if(intval($rtn) == 0) $rtn = getFileACL($file, $tostr);
		return $rtn;
	}
	return false;
}

/**
 * Use getfacl to obtain file/folder attributes (more powerful than getFilePerms)
 * @param string $file
 */
function getFileACL($file, $tostr = false){
	$retn = array();
	$outp = array();
	clearstatcache();
	if($file != ''){
		exec ("getfacl {$file}", $outp, $status);
		foreach($outp as $outl){
			$outl = strtolower($outl);
			if(strpos($outl, "# owner: ") !== false){
				$retn['owner'] = substr($outl, 8);
			}elseif(strpos($outl, "# group: ") !== false){
				$retn['grpown'] = substr($outl, 8);
			}elseif(strpos($outl, "user::") !== false){
				$retn['user']['read'] = (strpos(substr($outl, 6), 'r') !== false);
				$retn['user']['write'] = (strpos(substr($outl, 6), 'w') !== false);
				$retn['user']['exec'] = (strpos(substr($outl, 6), 'x') !== false);
			}elseif(strpos($outl, "group::") !== false){
				$retn['group']['read'] = (strpos(substr($outl, 6), 'r') !== false);
				$retn['group']['write'] = (strpos(substr($outl, 6), 'w') !== false);
				$retn['group']['exec'] = (strpos(substr($outl, 6), 'x') !== false);
			}elseif(strpos($outl, "other::") !== false){
				$retn['other']['read'] = (strpos(substr($outl, 6), 'r') !== false);
				$retn['other']['write'] = (strpos(substr($outl, 6), 'w') !== false);
				$retn['other']['exec'] = (strpos(substr($outl, 6), 'x') !== false);
			}
		}
		if($tostr) $retn = convertArray2Mode($retn);
		return $retn;
	}
	return false;
}

/**
 * Use multiple techniques to change file mode
 * @param string $file
 * @param string $perms
 * @return boolean
 */
function chmod2($file, $mode = null){
	if($mode == null) $mode = "0777";
	if($file != ''){
		$perms_str = getFilePerms($file, true);
		$mode_str = substr($mode, -3);			// we are only interested in the permissions not file type
		//echo "1: $perms_str <-> $mode_str<br>";
		if(intval($perms_str) < intval($mode_str)){
			// use PHP chmod (if web server has access)
			@chmod ($file, octdec($mode));
			$perms_str = getFilePerms($file, true);
		//echo "2: $perms_str <-> $mode_str<br>";
			if(intval($perms_str) < intval($mode_str)){
				// use system otherwise
				system ("chmod {$mode_str} {$file}");
				$perms_str = getFilePerms($file, true);
		//echo "3: $perms_str <-> $mode_str<br>";
				if(intval($perms_str) < intval($mode_str)) return false;
			}
		}
		return true;
	}
	return false;
}

/**
 * Use multiple techniques to create folder
 * @param string $folder
 * @param octal $perms
 */
function mkdir2($pathname, $mode = null){
	if($mode == null) $mode = "0757";
	if($pathname != ''){
		if(!@mkdir($pathname, octdec($mode))){
			system ("mkdir {$pathname}");
		}
	}
	return file_exists($pathname);
}

/**
 * Return full url information in array
 * @param string $url
 * @return array
 */
function parseUrl($url) {
    $r  = "^(?:(?P<scheme>\w+)://)?";
    $r .= "(?:(?P<login>\w+):(?P<pass>\w+)@)?";
    $r .= "(?P<host>(?:(?P<subdomain>[\w\.]+)\.)?" . "(?P<domain>\w+\.(?P<extension>\w+)))";
    $r .= "(?::(?P<port>\d+))?";
    $r .= "(?P<path>[\w/]*/(?P<file>\w+(?:\.\w+)?)?)?";
    $r .= "(?:\?(?P<arg>[\w=&]+))?";
    $r .= "(?:#(?P<anchor>\w+))?";
    $r = "!$r!";                                                // Delimiters

    preg_match ($r, $url, $out);
    return $out;
}

/**
 * Build a <select> object based on database data
 * @param string $selname
 * @param string $in_value
 * @param string $table
 * @param string/array $id_fld
 * @param string $data_fld
 * @param string $data_sep
 * @param string $crit
 * @param string $sort
 * @param string $limit
 * @return string
 */
function buildDataList($selname, $in_value, $table, $id_fld, $data_fld, $data_sep, $crit, $sort, $limit, $js = "", $ignoreemptytable = false){
	if(is_array($data_fld)){
		$fields = implode(", ", $data_fld);
		$header_elem = array($id_fld => "");
		foreach($data_fld as $fld) $header_elem[$fld] = "";
	}else{
		$fields = $data_fld;
		$header_elem = array($id_fld => "", $data_fld => "");
	}
	$fields = $id_fld.", ".$fields;
	$tblchk = univGetQuery("show tables where tables_in_".DBNAME." = '{$table}'");
	if(count($tblchk) == 0 && !$ignoreemptytable) die ("{$table} table missing!");
	$datarec = getRec($table, $fields, $crit, $sort, $limit);
	array_unshift($datarec, $header_elem);
	$data_list = "<select name=\"{$selname}\" id=\"{$selname}\" size=\"1\"{$js}>\n";
	foreach($datarec as $item){
		($in_value == $item[$id_fld]) ? $selected = " selected=\"selected\"" : $selected = "";
		if(is_array($data_fld)){
			$text = "";
			foreach($data_fld as $fldkey => $fld) {
				if($text != "") $text .= $data_sep;
				(is_numeric($fldkey)) ? $key = $fld : $key = $fldkey;
				$text .= $item[$key];
			}
		}else{
			$text = $item[$data_fld];
		}
		$data_list .= "<option value=\"".$item[$id_fld]."\"$selected>".$text."</option>\n";
	}
	$data_list .= "</select>\n";
	return $data_list;
}

# --- Slugs

/**
 * Return a sanitized, URL-safe version of input string (code)
 * @param string $str
 * @return string
 */
function codify($str, $table = '', $code_field = 'code'){
	$code = str_replace(" ", "-", $str);
	$code = preg_replace("/(\*|\?|\&|\+|'|`|\%|=|\||\.|\<|\>|:|\/|\\|\"|\#039;|\#39;|\#034;|\#34;|\#)/", "_", strtolower($code));

    if($table != '' && $code_field != ''){
        $code_array = getRec($table, "code", "", "", "");
        if(count($code_array) > 0){
            $code_array = flattenDBArray($code_array, "", "code");
            if(in_array($code, $code_array)){
                // code already exists. to make unique, add the next index to it
                $indx = 1;
                while(in_array($code."_".$indx, $code_array)){
                    $indx++;
                }
                $code .= "_".$indx;
            }
        }
    }
	return $code;
}

/**
 * Convert string to slug (a slug is different from a code in that it can only contain alphanumeric chars, hyphen and underscore)
 * @param string $str
 */
function slugify($str){
    $str = strtolower(preg_replace("/[^a-z0-9\-_]/i", "", $str));
    return $str;
}

# --- Settings

function getSettingsIssuesCount($section = ""){
	global $settings_issues;

	if($section == ""){
		// count from all sections
		$count = 0;
		foreach($settings_issues as $section => $section_issues){
			$count += count($settings_issues[$section]);
		}
		return $count;
	}else{
		// count from single section
		$count = countIfSet($settings_issues[$section]);
		if(strpos($section, "-info") === false) $count += countIfSet($settings_issues[$section."-info"]);
		return $count;
	}
}

/**
 * Output settings issues (notices at top of dialog or top of tabs)
 * @param string $section
 * @param boolean $rtn True to return HTML code rather than outputting it
 */
function showSettingsIssues($section = "", $rtn = false){
    global $settings_issues;

    $html = '';
    $bullet = "&nbsp;&nbsp;&bull;&nbsp;";
    if($section == ""){
    	if(getSettingsIssuesCount() == 0) return false;

        $warn = 0;
        $info = 0;
        foreach($settings_issues as $section => $section_issues){
            if(substr($section, -5) == '-info') { $info += count($section_issues); } else { $warn += count($section_issues); }
        }
        $html = (($warn > 0) ? "<img src=\"".WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/icons/warning.png\" border=\"0\" alt=\"\" />&nbsp;{$warn} critical issue(s).  " : "");
        $html.= (($info > 0) ? "<img src=\"".WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/icons/info_button_16.png\" border=\"0\" alt=\"\" />&nbsp;{$info} notice(s)." : "");
    }else{
        if(count($settings_issues[$section]) > 0) $html = $bullet.join("<br/>".$bullet, $settings_issues[$section]);
        if(strpos($section, '-info') === false && count($settings_issues[$section.'-info']) > 0) $html .= (($html != '') ? '<br/><br/>' : '').$bullet.join("<br/>".$bullet, $settings_issues[$section.'-info']);
    }
    if($rtn) {
    	return $html;
    }else{
    	echo $html;
    }
}

/**
 * Output settings indicator icon (shown on tabs when issues are present)
 * @param unknown_type $section
 */
function showSettingsIssuesIndicator($section){
    global $settings_issues;

    $html = "";
    $crit_issues = getIfSet($settings_issues[$section]);
    $info_issues = getIfSet($settings_issues[$section."-info"]);
    if(count($crit_issues) > 0 && is_array($crit_issues)){
        $alt = count($crit_issues)." critical issue(s)";
        $html = "&nbsp;<img src=\"".WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/icons/warning.png\" border=\"0\" title=\"{$alt}\" alt=\"{$alt}\" />";
    }elseif(count($info_issues) > 0 && is_array($info_issues)){
        $alt = count($info_issues)." information notice(s)";
        $html = "&nbsp;<img src=\"".WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/icons/info_button_16.png\" border=\"0\" title=\"{$alt}\" alt=\"{$alt}\" />";
    }
    echo $html;
}

/**
 * Output action objects (buttons, links, menus)
 * @param integer $curindex
 * @param array $objects Type of buttons, links, menu.  ['type']['id']['options']
 */
function showSettingsActions($curindex = 0, $objects = null){
	foreach($objects as $type => $obj){
	    if(is_array($obj)){
	    	$type = strtolower($type);
			if($type == 'links'){
		        $index = 0;
		        foreach($obj as $id => $val){
		            if($index > 0) echo "|&nbsp;";
		            if($index == $curindex) $val = "<b>{$val}</b>";
		            if(substr($id, 0, 4) == 'url:') {
		                $attr = "href=\"".substr($id, 4)."\"";
		            }else{
		                $attr = "id=\"{$id}\"";
		            }
		            echo "<a {$attr}>{$val}</a>\n";
		            $index++;
		        }
		    }elseif($type == 'buttons'){
		        foreach($obj as $id => $val){
		            echo "<input type=\"button\" id=\"{$id}\" value=\"{$val}\" />\n";
		        }
		    }elseif($type == 'menu'){
		    	// id
		    	// options ---- val => text
				echo "<select id=\"{$obj['id']}\">\n";
				$obj['options'] = array('' => '--') + getIfSet($obj['options']);
				foreach($obj['options'] as $val => $text){
					$sel = (($val == $obj['sel']) ? ' selected="selected"' : '');
					echo "<option value=\"{$val}\"{$sel}>{$text}</option>\n";
				}
				echo "</select>\n";
		    }elseif($type == 'checkbox'){
		    	$id = (($obj['id'] != '') ? ' id="'.$obj['id'].'"' : '');
		    	$class = ((!isBlank($obj['class'])) ? ' class="'.$obj['class'].'"' : '');
		        echo "<input type=\"checkbox\"{$id}{$class} value=\"".getIfSet($obj['val'])."\" />\n";
		    }
		}
	}
}

?>