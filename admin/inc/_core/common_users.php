<?php
// ---------------------------
//
// FOUNDRY COMMON FUNCTIONS
//  - User Support -
//
// ---------------------------
//
// list of tool functions
// - Uses: MySQL 4.2+

define ("USERSLOADED", true);
if(!defined("VALID_LOAD")) die ("This file cannot be accessed directly!");

// Allowances
// - Pages
define("UA_VIEW_PAGES_LIST", "view_pages_list");
define("UA_EDIT_PAGE", "edit_page");
define("UA_ADD_PAGE", "add_page");
define("UA_DELETE_PAGE", "delete_page");
define("UA_RENAME_PAGE", "rename_page");
define("UA_VIEW_PAGE", "view_page");
define("UA_PUBLISH_PAGE", "publish_page");
define("UA_ACTIVATE_PAGE", "activate_page");
define("UA_CLONE_PAGE", "clone_page");
define("UA_EDIT_PAGE_META", "edit_page_meta");
define("UA_VIEW_LOCKED_PAGES", "view_locked_pages");

// - Users
define("UA_VIEW_USERS", "view_users");
define("UA_CREATE_USER", "create_user");
define("UA_CREATE_LOWER_USER", "create_lower_user");
define("UA_EDIT_USER", "edit_user");
define("UA_EDIT_PROFILE", "edit_profile");
define("UA_DELETE_USER", "delete_user");
define("UA_DELETE_LOWER_USER", "delete_lower_user");
define("UA_ACTIVATE_USER", "activate_user");
define("UA_ACTIVATE_LOWER_USER", "activate_lower_user");
define("UA_BAN_USER", "ban_user");
define("UA_BAN_LOWER_USER", "ban_lower_user");

// - Themes
define("UA_VIEW_THEMES", "view_themes");
define("UA_INSTALL_WEBSITE_THEME", "install_website_theme");
define("UA_INSTALL_ADMIN_THEME", "install_admin_theme");
define("UA_EDIT_WEBSITE_THEME", "edit_website_theme");
define("UA_EDIT_ADMIN_THEME", "edit_admin_theme");
define("UA_DELETE_WEBSITE_THEME", "delete_website_theme");
define("UA_DELETE_ADMIN_THEME", "delete_admin_theme");
define("UA_ACTIVATE_WEBSITE_THEME", "activate_website_theme");
define("UA_ACTIVATE_ADMIN_THEME", "activate_admin_theme");

// - Menus
define("UA_VIEW_MENU_SETTINGS", "view_menu_settings");
define("UA_EDIT_WEBSITE_MENUS", "edit_website_menus");
define("UA_EDIT_ADMIN_MENUS", "edit_admin_menus");
define("UA_VIEW_LOCKED_MENUS", "view_locked_menus");

// - Plugins
define("UA_VIEW_PLUGINS", "view_plugins");
define("UA_INSTALL_PLUGINS", "install_plugins");
define("UA_UPDATE_PLUGINS", "update_plugins");
define("UA_REPAIR_PLUGINS", "repair_plugins");
define("UA_DELETE_PLUGINS", "delete_plugins");
define("UA_ACTIVATE_PLUGINS", "activate_plugins");

// - Frameworks
define("UA_ACTIVATE_FRAMEWORKS", "activate_frameworks");

// - Media
define("UA_VIEW_MEDIA_SETTINGS", "view_media_settings");
define("UA_EDIT_MEDIA_SETTINGS", "edit_media_settings");

// - General
define("UA_VIEW_GENERAL_SETTINGS", "view_general_settings");
define("UA_EDIT_GENERAL_SETTINGS", "edit_general_settings");

// - Database
define("UA_MANAGE_DATABASE", "manage_database");

// - Advanced
define("UA_VIEW_ADVANCED_SETTINGS", "view_advanced_settings");
define("UA_EDIT_ADVANCED_SETTINGS", "edit_advanced_settings");
define("UA_MANAGE_ALIASES", "manage_aliases");
define("UA_MANAGE_URLS", "manage_urls");
define("UA_MANAGE_VISIBILITY", "manage_visibility");
define("UA_MANAGE_DEBUGGER", "manage_debugger");

// - Records and Files
define("UA_UPLOAD_FILES", "upload_files");
define("UA_VIEW_LIST", "view_list");
define("UA_EDIT_RECORD", "edit_record");
define("UA_ADD_RECORD", "add_record");
define("UA_DELETE_RECORD", "delete_record");
define("UA_RENAME_RECORD", "rename_record");
define("UA_VIEW_RECORD", "view_record");
define("UA_PUBLISH_RECORD", "publish_record");
define("UA_ACTIVATE_RECORD", "activate_record");
define("UA_CLONE_RECORD", "clone_record");
define("UA_ORGANIZE_RECORDS", "organize_records");
define("UA_EXPORT_RECORDS", "export_records");
define("UA_SEND_EMAILS", "send_emails");

/**
 * Check if user (IP) is logged in
 * @return boolean
 */
function isUserLoggedin() {
	$iphash = md5($_SERVER['REMOTE_ADDR']);
	$id = getRecItem("session_login", "id", "ip_hash = '$iphash' AND logged_in = 1 AND section = 'admin'");
	return (intval($id) > 0);
}

/**
 * Return logged-in user's level
 * @return int
 */
function getUserLevel(){
	global $_users;

    return $_users->level;
}

/**
 * Return logged-in user's account ID
 * @return int
 */
function getUserID(){
	global $_users;

    return $_users->id;
}

/**
 * Check if user is at least at level ADMLEVEL_???
 * @param int $minlevel
 * @return boolean
 */
function userIsAtleast($minlevel = ADMLEVEL_DEVELOPER){
	global $_users;

    return ($_users->level <= $minlevel);
}

/**
 * Return whether or not user can perform or gain access to a particular action or area
 * (References Allowances settings)
 * @param string $action An allowance action
 * @return boolean
 */
function userIsAllowedTo($action){
	global $_users;

	$a = $_users->allowances;
	if(is_array($a[$action])){
		if($_users->level > 0){
			$levelbase = log($_users->level) / log(2) + 1;
		}else{
			$levelbase = 0;
		}
		return ($a[$action][$levelbase] == 1);
	}
}

/**
 * Stop further access to the page if user does not have proper allowance
 * @param string $action
 * @param boolean $is_page
 * @param boolean $hardstop End execution if true
 */
function haltIfUserCannot($action, $is_page = true, $hardstop = true){
	if(!userIsAllowedTo($action)){
		$e = (($is_page) ? ACCESS_PAGE_FAIL : ACCESS_FUNC_FAIL);
		if($hardstop){
			die($e);
		}else{
			addErrorMsg($e);
		}
	}
}

/**
 * Return array containing all users registered in system
 * @return array
 */
function getUsers(){
	global $_users;

	return $_users->active;
}

/**
 * Return default user allowances in JSON format
 * @return string
 */
function initAllowances(){
	global $_users;

	// these are the default values
	$defaults = '{"view_pages_list":[1,1,1,1,1,1],
		"edit_page":[1,1,1,1,0,0],
		"add_page":[1,1,1,0,0,0],
		"delete_page":[1,1,1,0,0,0],
		"rename_page":[1,1,1,0,0,0],
		"view_page":[1,1,1,1,1,1],
		"publish_page":[1,1,1,0,0,0],
		"activate_page":[1,1,1,0,0,0],
		"clone_page":[1,1,1,0,0,0],
		"edit_page_meta":[1,1,0,0,0,0],
		"view_locked_pages":[1,0,0,0,0,0],
		"view_users":[1,1,1,1,1,1],
		"create_user":[1,1,0,0,0,0],
		"create_lower_user":[1,1,1,1,0,0],
		"edit_user":[1,1,0,0,0,0],
		"edit_profile":[1,1,1,1,1,1],
		"delete_user":[1,1,0,0,0,0],
		"delete_lower_user":[1,1,1,1,0,0],
		"activate_user":[1,1,0,0,0,0],
		"activate_lower_user":[1,1,1,1,0,0],
		"ban_user":[1,1,0,0,0,0],
		"ban_lower_user":[1,1,1,1,0,0],
		"view_themes":[1,1,1,1,0,0],
		"install_website_theme":[1,1,0,0,0,0],
		"install_admin_theme":[1,0,0,0,0,0],
		"edit_website_theme":[1,1,1,0,0,0],
		"edit_admin_theme":[1,0,0,0,0,0],
		"delete_website_theme":[1,1,0,0,0,0],
		"delete_admin_theme":[1,0,0,0,0,0],
		"activate_website_theme":[1,1,0,0,0,0],
		"activate_admin_theme":[1,0,0,0,0,0],
		"view_menu_settings":[1,1,0,0,0,0],
		"edit_website_menus":[1,0,0,0,0,0],
		"edit_admin_menus":[1,0,0,0,0,0],
		"view_locked_menus":[1,0,0,0,0,0],
		"view_plugins":[1,1,1,1,0,0],
		"install_plugins":[1,0,0,0,0,0],
		"update_plugins":[1,1,1,0,0,0],
		"repair_plugins":[1,0,0,0,0,0],
		"delete_plugins":[1,0,0,0,0,0],
		"activate_plugins":[1,1,0,0,0,0],
		"activate_frameworks":[1,1,0,0,0,0],
		"view_media_settings":[1,1,1,1,0,0],
		"edit_media_settings":[1,1,1,0,0,0],
		"view_general_settings":[1,1,1,1,0,0],
		"edit_general_settings":[1,1,1,0,0,0],
		"manage_database":[1,0,0,0,0,0],
		"view_advanced_settings":[1,0,0,0,0,0],
		"edit_advanced_settings":[1,0,0,0,0,0],
		"manage_aliases":[1,0,0,0,0,0],
		"manage_urls":[1,0,0,0,0,0],
        "manage_robots":[1,0,0,0,0,0],
		"manage_visibility":[1,1,0,0,0,0],
		"manage_debugger":[1,0,0,0,0,0],
		"upload_files":[1,1,1,1,1,1],
		"view_list":[1,1,1,1,1,1],
		"edit_record":[1,1,1,1,0,0],
		"add_record":[1,1,1,0,0,0],
		"delete_record":[1,1,1,0,0,0],
		"rename_record":[1,1,1,0,0,0],
		"view_record":[1,1,1,1,1,1],
		"publish_record":[1,1,1,0,0,0],
		"activate_record":[1,1,1,0,0,0],
		"clone_record":[1,1,1,0,0,0],
		"organize_records":[1,1,1,1,0,0],
		"export_records":[1,1,1,0,0,0],
		"send_emails":[1,1,1,0,0,0]}';

	$defaults = preg_replace("/\s*/", "", $defaults);
	$defaults_array = json_decode($defaults, true);

	// get allowances from settings table, if missing assign defaults
	$a = getRecItem("settings", "value", "`name` = 'ALLOWANCES'");
	if(isBlank($a)){
		// aa inherits defaults
		insertRec("settings", "`name`, `value`, `type`", "'ALLOWANCES', '$defaults', 'str'");
		$aa = $defaults_array;
	}else{
		// aa inherits any missing allowances
		$aa = json_decode($a, true);
		foreach($defaults_array as $key => $allowances){
			if(!isset($aa[$key])) $aa[$key] = $allowances;
		}
		if(json_encode($aa) != $a){
			updateRec("settings", "value = '".json_encode($aa)."'", "`name` = 'ALLOWANCES'");
		}
	}
	$_users->allowances = $aa;
}

?>