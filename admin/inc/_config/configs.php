<?php
// ---------------------------
//
// ADMIN SYSTEM CONFIGURATION
//
// ---------------------------

if(!defined("VALID_LOAD")) die("This file cannot be accessed directly!");

//*****************************************************************************
//   FOUNDRY SYSTEM VERSIONS
//*****************************************************************************

define ("SYS_NAME", "Foundry");
define ("CODE_VER", "3.9.5");
define ("CODE_VER_CORE", "3950");
define ("CODE_VER_NAME", "Genesis");
checkServerVersions('PHP');

//*****************************************************************************
//   MODIFY PHP DIRECTIVES
//*****************************************************************************

define ("F_MEMORY_LIMIT", "32M");
@ini_set("memory_limit", F_MEMORY_LIMIT);
@ini_set("display_errors", "on");

//*****************************************************************************
//   ROOT FOLDER
//*****************************************************************************

if(!defined("LIVE")) define ("LIVE", false);
if($_SERVER['DOCUMENT_ROOT'] == ""){
	define ("DOCUMENT_ROOT", "/public/web");		//this is for the cron script
}else{
	define ("DOCUMENT_ROOT", rtrim($_SERVER['DOCUMENT_ROOT'],"/\\").VHOST);
}

//*****************************************************************************
//   FILE SYSTEM SETTINGS
//*****************************************************************************

// - site_path: server/path/to/file
// - web_url: http://server/path/to/file

define ("PROTOCOL", ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 1 || strtolower($_SERVER['HTTPS']) == 'on')) || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')) ? "https://" : "http://");
define ("SERVER", PROTOCOL.$_SERVER['HTTP_HOST']);
define ("WEB_URL", SERVER.VHOST);
define ("SITE_PATH", DOCUMENT_ROOT);				// FQDN of site (eg. /user/domain/httpdocs/)
define ("ROOT_FOLDER", "../");
define ("INC_FOLDER", "inc/");						// Folder where service files are located (css, js, classes, rss)
define ("ADMIN_FOLDER", "admin/");					// Admin folder root
define ("ADM_CSS_FOLDER", INC_FOLDER."_css/");      // CSS sub-folder (admin)
define ("DEF_ADM_SKIN", "genesis/");                // Name of admin skin (admin). Must end with /

define ("CORE_FOLDER", INC_FOLDER."_core/");		// Core sub-folder (front/admin)
define ("CONFIG_FOLDER", INC_FOLDER."_config/");	// Configurations sub-folder (front/admin)
define ("PLUGINS_FOLDER", INC_FOLDER."_plugins/");	// Plugins sub-folder (front/admin)
define ("LIB_FOLDER", INC_FOLDER."_lib/");          // Libraries sub-folder (front/admin)
define ("JS_FOLDER", INC_FOLDER."_js/");			// Javascript sub-folder (front/admin)
define ("DB_FOLDER", LIB_FOLDER."db/");             // Database classes sub-folder (front/admin)
define ("CUSTOM_FOLDER", INC_FOLDER."_custom/");	// Custom-content sub-folder (front/admin)
define ("REV_FOLDER", LIB_FOLDER."revisions/");		// File revisions storage(admin)

define ("CSS_FOLDER", INC_FOLDER."_css/");          // CSS sub-folder (front)
define ("THEME_FOLDER", CSS_FOLDER."themes/");      // Themes sub-folder (front)
define ("DEF_THEME", "default");					// Name of default frontend theme (front)
define ("RSS_FOLDER", "rss/");						// RSS sub-folder (front)
define ("CKE_FOLDER", "ckeditor/");					// CKEditor folder
define ("CKF_FOLDER", "ckfinder/");					// CKFinder folder
define ("WEBSTAT_FOLDER", "https://".$_SERVER['HTTP_HOST']."/plesk-stat/webstat/");

//*****************************************************************************
//   SECURITY
//*****************************************************************************

// Attention! It is highly recommended that you change these two values
// from the defaults, since hackers may already know them.

define ("SECURE_KEY",  'gE6Bp2-J_VG+&sJ- qU&ec.U4Byq+bO&})ruw{-eE%8XlF_cj+Ox#+=wjmRH+|jB');
define ("SECURE_SALT", 'btVBFBD|,g%0]g1Vi7@LffNpV}3g/dzbM~0]x2vEBxZ$`&X_?,Wk:Nb8Db8Rngc@');

//*****************************************************************************
//   OTHER SETTINGS
//*****************************************************************************

define ("SECTIONING", false);                       // Data subdivisioning
define ("COPYRIGHT_NAME", "Navigator Multimedia");	// System copyright holder
define ("COPYRIGHT_WEB", "http://www.navigatormm.com/");

//*****************************************************************************
//   RECORD-KEEPING SETTINGS
//*****************************************************************************

define ("ALLOW_ARCHIVE", true);		// 'archive' action
define ("ALLOW_DELETE", true);		// 'delete' action
define ("FULL_DELETE", true);		// delete record or set 'delete' field
define ("ALLOW_UNDELETE", true);	// 'undelete' action
define ("ALLOW_PUBLISH", true);		// 'publish' action and 'save & publish' option
define ("ALLOW_ACTIVATE", true);	// 'activate' action and 'save & activate' option
define ("ALLOW_DRAFT", true);		// 'save to draft' option
define ("ALLOW_UNPUB_SAVE", true);	// 'save' option displayed
define ("ALLOW_SORT", true);        // column sorting on list page
define ("ALLOW_SEARCH", true);      // search function on list page
define ("USE_SECTIONS", false);     // universal divisioning on/off
define ("ALLOW_ADDPAGE", true);     // shows 'add sub-page' on page list
define ("ALLOW_METAPAGE", true);    // shows 'edit meta-data' on page list
define("ROOT_ID", "sectionid");		// session root table id where root data is pulled

if(USE_SECTIONS){
	$rootvar = "root";					// session root variable name -- passed between webpages
	$roottable = "sections";			// session root table where root data is pulled
	$rootdir = "pages";					// page and folder which is displayed by default
	$rootlink = "?root=";
}else{
	$rootvar = "";						// session root variable name -- passed between webpages
	$roottable = "";					// session root table where root data is pulled
	$rootdir = "";						// page and folder which is displayed by default
	$rootlink = "";
}

define("PAGE_EDITOR", 1);
define("PAGE_DB", 2);
define("PAGE_FORM", 3);

//*****************************************************************************
//   START DATABASE INITIALIZATION
//*****************************************************************************

include(SITE_PATH.ADMIN_FOLDER.CONFIG_FOLDER."db_configs.php");

//*****************************************************************************
//   CUSTOMIZABLE SETTINGS
//*****************************************************************************

$configs = array();
$defcfg  = array(	"BUSINESS" => "Business",
					"SITE_NAME" => "Business",
					"OWNER_EMAIL" => "chrisd@navigatormm.com",
					"ADMIN_EMAIL" => "chrisd@navigatormm.com",
					"BUS_ADDRESS" => "",
					"BUS_PHONE" => "",
					"BUS_FAX" => "",
                    "TIMEZONE" => "America/Vancouver",
					"IMG_MAX_WIDTH" => 800,
					"IMG_MAX_HEIGHT" => 600,
					"IMG_MAX_UPLOAD_SIZE" => "6000",
					"MAX_IFRAME_IMGS" => 6,
					"THM_MAX_WIDTH" => 100,
					"THM_MAX_HEIGHT" => 100,
					"THM_MED_MAX_WIDTH" => 200,
					"THM_MED_MAX_HEIGHT" => 200,
					"THM_MAX_UPLOAD_SIZE" => "50",
					"ORG_THM_MAX_WIDTH" => 100,
					"ORG_THM_MAX_HEIGHT" => 100,
                    "IMG_UPLOAD_FOLDER" => "images/",
                    "THM_UPLOAD_FOLDER" => "thumbs/",
                    "FILE_UPLOAD_FOLDER" => "files/",
					"ACTION_ICONS" => 0,
					"IMG_LOGIN_LOGO" => "",
					"EMAIL_CONFIRM" => "",
					"EMAIL_NOTIFY" => "",
					"THEME" => "default",
					"THEMES_ENABLED" => false,
					"PHP_DATE_FORMAT" => "Y-m-d",
					"SITEOFFLINE" => false,
					"SITEOFFLINE_MSG" => "This site is down for maintenance. Please check back again soon.",
					"DB_TABLE_PREFIX" => "data_",
					"ERROR_SENSITIVITY" => E_ERROR | E_WARNING | E_PARSE,
					"ALLOW_DEBUGGING" => true,
					"ERROR_LOG_TYPE" => 0,
					);
$configs = getCustomConfigVals();
foreach($configs as $ckey => $cval) define($ckey, $cval);

#----------- INTERFACE ----------------------------

$GLOBALS["THEME"] = $configs['THEME'];

#----------- GENERAL SETTINGS ---------------------

define ("DEF_LANGUAGE", "english");

#----------- TIMEZONE -----------------------------

ini_set("date.timezone", TIMEZONE);
date_default_timezone_set(TIMEZONE);

define ("BLANK_DATE", "0000-00-00 00:00:00");

#----------- LANGUAGES ----------------------------

#----------- IMAGES, FILES AND THUMBS -------------

//Images
define ("IMG_WARNING", "<span style=\"color:red\">(Will be resized to max ".IMG_MAX_WIDTH." x ".IMG_MAX_HEIGHT." pixels. Maximum upload size is ".(IMG_MAX_UPLOAD_SIZE / 1000)." MB)</span>");
define ("NO_IMG", "no_image.png");												// No-image file name

//Thumbs
define ("NO_THM", "no_thumb.png");

#----------- LISTS AND TABLES -------------

define ("LIST_ROWLIMIT", 100);
define ("LIST_PAGESSHOWN", 20);
define ("LIST_ROWCOLOR1", "#ffffff");
define ("LIST_ROWCOLOR2", "#eeeeee");
define ("HEADER_BGCOLOR", "#ccccff");
define ("DATE_FORMAT", "yyyy-mm-dd");
define ("EXCERPT_CHAR_LIMIT", 40);
define ("REQD_ENTRY", "<span style=\"color: red\">*</span>");

//*****************************************************************************
//   VALIDATION FUNCTIONS
//*****************************************************************************

#----------- VALIDATE FOLDER ACCESS --------------

/**
 * Checks for the existence of critical control files and folders.
 * Doing so will return more description of the missing file or folder
 */
function checkFolders(){
	$setold = 0;
	$setnew = 0;
	$setold += testFiles(SITE_PATH.ADMIN_FOLDER.INC_FOLDER."common.php", SITE_PATH.ADMIN_FOLDER.INC_FOLDER);
	$setnew += testFiles(SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_core.php", SITE_PATH.ADMIN_FOLDER.CORE_FOLDER);
	$setnew += testFiles(SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_err.php", SITE_PATH.ADMIN_FOLDER.CORE_FOLDER);
	$setnew += testFiles(SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_db.php", SITE_PATH.ADMIN_FOLDER.CORE_FOLDER);
	$setnew += testFiles(SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_form.php", SITE_PATH.ADMIN_FOLDER.CORE_FOLDER);
	$setnew += testFiles(SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_plugin.php", SITE_PATH.ADMIN_FOLDER.CORE_FOLDER);
	if($setold < 1 && $setnew < 5) die("CONFIG: One or more common_x files are missing!");
	testFiles(SITE_PATH.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN, SITE_PATH.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN, true);
	testFiles(SITE_PATH.ADMIN_FOLDER.JS_FOLDER, SITE_PATH.ADMIN_FOLDER.JS_FOLDER, true);
	testFiles(SITE_PATH.ADMIN_FOLDER.DB_FOLDER, SITE_PATH.ADMIN_FOLDER.DB_FOLDER, true);
}

/**
 * Check a file or folder for existence
 * @param string $testfor
 * @param string $where
 * @param boolean $stophere
 */
function testFiles($testfor, $where, $stophere = false){
	if(file_exists($testfor)){
		return 1;
	}elseif($stophere){
		print "CONFIG: Cannot find ".basename($testfor)." in $where!";
		die();
	}else{
		return 0;
	}
}

//*****************************************************************************
//   SUPPORT FUNCTIONS
//*****************************************************************************

function checkServerVersions($server){
	if($server == 'PHP'){
		// PHP 5.2 required
		$phpver = floatval(phpversion());
		if($phpver < 5.2) die("PHP version 5.2 minimum required to operate ".SYS_NAME);
	}elseif($server == 'MySQL'){
		// MySQL 3.34 required
		$mysqlver = floatval(mysql_get_server_info());
		if($mysqlver < 3.34) die("MySQL version 3.34 minimum required to operate ".SYS_NAME);
	}
}

/**
 * Load config values from Settings table
 * @return array Array of values
 */
function getCustomConfigVals(){
	// retrieve customizable config values from 'settings' table
	// any invalid or missing data will revert to defaults
	// first connect to database
	global $defcfg;

    $csval = $defcfg;

	if(DB_USED){
		$conn = mysql_connect(DBHOST, DBUSER, DBPASS);
		if(!mysql_select_db(DBNAME, $conn)) {
			die("Cannot connect to ".DBNAME." (".DBHOST.", ".DBNAME.", ".DBUSER.")");
		}

		$query = "SELECT * FROM settings";
		$settings = mysql_query($query, $conn);
		$err = ($settings === false);
	}else{
		$err = true;
	}

	if(!$err){
		//$csval = array();
		$err = false;
		while($rec = mysql_fetch_array($settings)){
			switch($rec['type']){
				case "str":
					$csval[$rec['name']] = $rec['value'];
					break;
				case "int":
					$csval[$rec['name']] = floatval($rec['value']);
					break;
				case "upd":
					if($rec['value'] == null || $rec['value'] == "") doInitUpdate($rec['name'], $conn);
					break;
			}
		}
	}

	if($err) {
		// problem calling data or DB not used
		//$csval = $defcfg;
	}
	$csval['DBSETTINGSOK'] = !$err;

	return $csval;
}

/**
 * Prepare/update initial settings
 * @param string $fieldname
 * @param object $conn
 */
function doInitUpdate($fieldname, $conn){
	// update data or file based on requested fieldname
	switch($fieldname){
		case "CKE_CSS_COLORS":
			// read all color attributes from "{root}css/layout.css" file
			if(!is_writable(SITE_PATH.CKE_FOLDER."config.js")) die("CKEditor config.js file not writable!");

			$values = array();
			$public_css_folder = SITE_PATH."css/";      // test css first
			(file_exists($public_css_folder."layout.css")) ? $cssfile = $public_css_folder."layout.css" : $cssfile = $public_css_folder."master.css";
			if($fp = @fopen($cssfile, "r")){
				while($line = fgets($fp)){
					$line = strtolower(trim($line));
					if(substr($line, 0, 6) == "color:"){
						preg_match("/([0-9a-f]+);/i", $line, $valfound);
						if(strlen($valfound[1]) == 3) $valfound[1] .= $valfound[1];
						$valfound[1] = strtoupper($valfound[1]);
						if(!in_array($valfound[1], $values)) $values[] = $valfound[1];
					}
				}
				fclose($fp);

				$valstr = implode (",", $values);
				if($valstr != ""){
					if($fcontents = file_get_contents(SITE_PATH.CKE_FOLDER."config.js")){
						// contents retrieved
						$fcontents = preg_replace("/colorButton_colors = '(.)+'/i", "colorButton_colors = '".$valstr."'", $fcontents);
						if(file_put_contents(SITE_PATH.CKE_FOLDER."config.js", $fcontents) !== false){
							// file updated with new contents
							$query = "UPDATE settings SET `value` = '$valstr' WHERE `name` = '$fieldname' LIMIT 1";
							mysql_query($query, $conn);
						}
					}
				}
			}
			break;
	}
}
?>