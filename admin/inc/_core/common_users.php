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