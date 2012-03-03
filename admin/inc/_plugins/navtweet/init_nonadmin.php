<?php
// simulate Nav Admin functions
// ----------------------------
// database values -- remember to set them!!!
if(!defined("VALID_LOAD")){
	// Either this API is being used in a pre-version 3.1 system or in a website that does not use Nav Admin
	define ("VALID_LOAD", true);
    define ("VHOST", "/".((preg_match("/(badger|stonehenge|navigatormultimedia|localhost)/i", $_SERVER['HTTP_HOST'])) ? substr($_SERVER['PHP_SELF'], 1, strpos($_SERVER['PHP_SELF'], "/", 1)) : ""));
	define ("SERVER", "http://".$_SERVER['HTTP_HOST']);
	define ("DOCUMENT_ROOT", rtrim($_SERVER['DOCUMENT_ROOT'],"/\\").VHOST);
	define ("WEB_URL", SERVER.VHOST);
	define ("SITE_PATH", DOCUMENT_ROOT);
	define ("INC_FOLDER", "inc/");
	define ("ADMIN_FOLDER", "admin/");
    define ("PLUGINS_FOLDER", INC_FOLDER."_plugins/");

	if(file_exists(SITE_PATH.INC_FOLDER."_config/db_configs.php")) {
        include (SITE_PATH.INC_FOLDER."_config/db_configs.php");			// db_configs file is required!
    }else{
        define ("DBHOST", "localhost");			// server host name
        define ("DBUSER", "dbuser");            // username
        define ("DBPASS", "dbpass");			// password
        define ("DBNAME", "dbname");            // database name
    }

    if(!defined('PLUGINLOADED')) include(SITE_PATH.ADMIN_FOLDER.INC_FOLDER."_core/common_plugin.php");
}

// STOP HERE!!!
$conn = null;

// MySQL functions
function nt_connect() {
	global $conn;

	$conn = mysql_connect(DBHOST, DBUSER, DBPASS);
	if(mysql_select_db(DBNAME, $conn)) {
		return true;
	}else{
		die("Cannot connect to ".DBNAME." (".DBHOST.", ".DBNAME.", ".DBUSER.")");
	}
}

function nt_getQuery($query) {
	global $conn;

	$arry = array();
	$rs = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_array($rs)){
		$arry[] = $row;
	}
	return $arry;
}

function nt_insertQuery($query) {
	global $conn;

	$rs = mysql_query($query, $conn) or die(mysql_error());
	$id = mysql_insert_id();
	return $id;
}

function nt_updateQuery($query) {
	global $conn;

	$rs = mysql_query($query, $conn) or die(mysql_error());
	return $rs;
}

function nt_deleteQuery($query) {
	global $conn;

	$rs = mysql_query($query, $conn) or die(mysql_error());
}

// Wrapper functions
function nt_getRec($table, $fields, $crit, $order, $limit, $groupby = "") {
	if($table != "") {
		$fields = trim($fields);
		$crit = trim($crit);
		$order = trim($order);
		$limit = trim($limit);
		if($fields == "") $fields = "*";
		$sql = "SELECT $fields
				FROM $table";
		if($crit != "") $sql .= " WHERE $crit";
		if($groupby != "") $sql .= " GROUP BY $groupby";
		if($order != "") $sql .= " ORDER BY $order";
		if($limit != "") $sql .= " LIMIT $limit";
		$rec = nt_getQuery($sql);
	}else{
		return false;
	}
	return $rec;
}

function nt_insertRec($table, $fields, $values) {
	if($table != "" && $fields != "" && $values != "") {
		$fields = trim($fields);
		$values = trim($values);
		$sql = "INSERT INTO $table
				($fields)
				VALUES ($values)";
		$id = nt_insertQuery($sql);
	}else{
		return false;
	}
	return $id;
}

function nt_updateRec($table, $fieldvals, $crit) {
	if($table != "" && $fieldvals != "" && $crit != "") {
		$fieldvals = trim($fieldvals);
		$crit = trim($crit);
		$sql = "UPDATE $table
				SET $fieldvals
				WHERE $crit";
		$id = nt_updateQuery($sql);
		return true;
	}else{
		return false;
	}
}

function nt_deleteRec($table, $crit) {
	if($table != "" && $crit != "") {
		$fields = trim($fields);
		$crit = trim($crit);
		$sql = "DELETE FROM $table
				WHERE $crit";
		$result = nt_deleteQuery($sql);
		return true;
	}else{
		return false;
	}
}

function nt_initErrorMsg(){
	global $err;

	$err = array();
	$err[0] = "";		// reserved for status message
}

function nt_addErrorMsg($msg){
	global $err;

	$err[] = $msg;
}

nt_connect();
?>
