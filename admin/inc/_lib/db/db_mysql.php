<?php

/* ALTERNATE DATABASE FUNCTIONS */
// - Used if MySQL is below 3.4.3
// - deprecated as of PHP 5.3

$GLOBALS['connection'] = null;
function connect() {
	$GLOBALS['connection'] = mysql_connect(DBHOST, DBUSER, DBPASS);
	if(mysql_select_db(DBNAME, $GLOBALS['connection'])) {
		return true;
	}else{
		die("Cannot connect to ".DBNAME." (".DBHOST.", ".DBNAME.", ".DBUSER.")");
	}
}

//this would only work the best for a small amount of data.
//use getTotalRows instead
function getNumRows($query) {
	return count(getQuery($query));
}

//only return the 'total' data
function getTotalRows($query) {
	$array = getQuery($query, $GLOBALS['connection']);
	return $array[0]['total'];
}

function getQuery($query) {
	$array = array();
	$rs = mysql_query($query, $GLOBALS['connection']);
	while($row = mysql_fetch_array($rs)){
		$array[] = $row;
	}
	return $array;
}

function updateQuery($query) {
	$rs = mysql_query($query, $GLOBALS['connection']);
	return $rs;
}

function insertQuery($query) {
	$rs = mysql_query($query, $GLOBALS['connection']);
	$id = mysql_insert_id();
	return $id;
}

function deleteQuery($query) {
	$rs = mysql_query($query, $GLOBALS['connection']);
}

function getAffectedRows() {
	$rs = mysql_affected_rows($GLOBALS['connection']);
	return $rs;
}

function dbError() {
	return mysql_error($GLOBALS['connection']);
}

function db_close() {
	global $connection;

	mysql_close($GLOBALS['connection']);
}

connect();
?>