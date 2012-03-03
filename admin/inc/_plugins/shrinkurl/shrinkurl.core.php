<?php
// SHRINKURL
//
// Author: Chris Donalds <chrisd@navigatormm.com>
// Date: September 3, 2010
// Version: 3.0
// License: GPL
// ----------------------------------------------
// Either shrinks any URL on this site to micro version
// Or, expands a shrunk URL to its full path

define ("SURL_LOADED", true);

if($_REQUEST['s'] != ""){
	// if we got here from htaccess redirect
	$surl = $_REQUEST['s'];
	if(strlen($surl) == 5){
		if(surl_setDB()){
			redirectTo(expandURL($surl));
		}
	}
	exit;
}else{
	// otherwise we are hopefully setting up this plugin from an admin system
	surl_setDB();
}

// setup database functions
// ------------------------
function surl_setDB(){
	if(!defined("VALID_LOAD")){
		define("VALID_LOAD", true);
		define("DB_VER", 0);
	}

	if(!defined("DBHOST")){
		// custom database connection settings here
		include ("../../_core/db_configs.php");
	}
	if(surl_connect()) surl_checkDB();
	return true;
}

function surl_checkDB(){
	global $conn;

	if($conn) {
		$db0 = mysql_select_db(DBNAME) OR die("SHRINKURL: Could not connect to database '".DBNAME."'");
		$tables = mysql_query("SHOW TABLES");
		$reqd_tables = array("shrinkurl");
		while($row = mysql_fetch_array($tables)){
			$key = array_search($row[0], $reqd_tables);
			if($key !== false) unset($reqd_tables[$key]);
		}
		foreach($reqd_tables as $table){
			switch($table){
				case "shrinkurl":
					$sql = "CREATE TABLE `shrinkurl` (
					  `id` int(5) unsigned NOT NULL auto_increment,
					  `url` varchar(255) NOT NULL,
					  `surl` varchar(10) NOT NULL,
					  `key` varchar(10) NOT NULL,
					  `hits` int(5) unsigned NOT NULL default '0',
					  PRIMARY KEY  (`id`)
					) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
					break;
			}
			if($sql != "") mysql_query($sql);
		}
	}
}

function surl_connect() {
	$conn = mysql_connect(DBHOST, DBUSER, DBPASS) or die("Cannot connect to ".DBNAME." (".DBHOST.", ".DBNAME.", ".DBUSER.")");
	define("DBCONN", $conn);
	if(mysql_select_db(DBNAME, DBCONN)) {
		return true;
	}else{
		die("Cannot connect to ".DBNAME." (".DBHOST.", ".DBNAME.", ".DBUSER.")");
	}
}

function surl_getQuery($query) {
	$array = array();
	$rs = mysql_query($query, DBCONN) or die(mysql_error());
	while($row = mysql_fetch_array($rs)){
		$array[] = $row;
	}
	return $array;
}

function surl_updateQuery($query) {
	$rs = mysql_query($query, DBCONN) or die(mysql_error());
	return $rs;
}

function surl_insertQuery($query) {
	$rs = mysql_query($query, DBCONN) or die(mysql_error());
	$id = mysql_insert_id();
	return $id;
}

function surl_deleteQuery($query) {
	$rs = mysql_query($query, DBCONN) or die(mysql_error());
}

function surl_dbError() {
	return mysql_error(DBCONN) or die(mysql_error());
}

function surl_db_close() {
	mysql_close(DBCONN) or die(mysql_error());
}

function surl_univGetQuery($sql){
	return surl_getQuery($sql);
}

function surl_univUpdateQuery($sql){
	return surl_updateQuery($sql);
}

function surl_univInsertQuery($sql){
	return surl_insertQuery($sql);
}

function surl_univDeleteQuery($sql){
	return surl_deleteQuery($sql);
}

function surl_getRec($table, $fields, $crit, $order, $limit) {
	if($table != "") {
		$fields = trim($fields);
		$crit = trim($crit);
		$order = trim($order);
		$limit = trim($limit);
		if($fields == "") $fields = "*";
		$sql = "SELECT $fields
				FROM $table";
		if($crit != "") $sql .= " WHERE $crit";
		if($order != "") $sql .= " ORDER BY $order";
		if($limit != "") $sql .= " LIMIT $limit";
		$rec = surl_univGetQuery($sql);
	}else{
		return false;
	}
	return $rec;
}

function surl_updateRec($table, $fieldvals, $crit) {
	if($table != "" && $fieldvals != "" && $crit != "") {
		$fieldvals = trim($fieldvals);
		$crit = trim($crit);
		$sql = "UPDATE $table
				SET $fieldvals
				WHERE $crit";
		$id = surl_univUpdateQuery($sql);
		return true;
	}else{
		return false;
	}
}

function surl_deleteRec($table, $crit) {
	if($table != "" && $crit != "") {
		$fields = trim($fields);
		$crit = trim($crit);
		$sql = "DELETE FROM $table
				WHERE $crit";
		$result = surl_univDeleteQuery($sql);
    	return true;
	}else{
		return false;
	}
}

function surl_insertRec($table, $fields, $values) {
	if($table != "" && $fields != "" && $values != "") {
		$fields = trim($fields);
		$values = trim($values);
		$sql = "INSERT INTO $table
				($fields)
				VALUES ($values)";
		$id = surl_univInsertQuery($sql);
	}else{
		return false;
	}
	return $id;
}

// ------------------------

function expandURL($surl){
	if($surl != ""){
		// short url passed properly
		$urlrec = surl_getRec("shrinkurl", "*", "surl = '{$surl}'", "", "1");
		if(count($urlrec) > 0){
			// record found
			$id   = $urlrec[0]['id'];
			$furl = $urlrec[0]['url'];
			$hits = $urlrec[0]['hits'];
			surl_updateRec("shrinkurl", "hits = ".($hits + 1), "id = '{$id}'");
			return $furl;
		}else{
			// no matching full url
			return false;
		}
	}
}

function shrinkURL($furl){
	if($furl != ""){
		// full url passed properly
		$urlrec = surl_getRec("shrinkurl", "*", "url = '{$furl}'", "", "1");
		if(count($urlrec) == 0){
			// full url not yet in db
			// create key based on time
			$key_pool = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			$t = (string) time();
			$t_parts = str_split($t, 2);
			$key = "";
			foreach($t_parts as $t_part){
				$char = ($t_part % 62);
				$key .= substr($key_pool, $char, 1);
			}
			// store it and the full url in database
			surl_insertRec("shrinkurl", "surl, url, hits", "'{$key}', '{$furl}', 0");
			$surl = WEB_URL."s/".$key;
			return $surl;
		}else{
			// full url found, use the short url in record
			$surl = WEB_URL."s/".$urlrec[0]['surl'];
			return $surl;
		}
	}
}

function redirectTo($furl){
	if($furl != ""){
		header("location: {$furl}");
	}
}
//
?>
