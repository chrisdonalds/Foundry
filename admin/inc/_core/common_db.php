<?php
// ---------------------------
//
// FOUNDRY COMMON FUNCTIONS
//  - Database Controller -
//
// ---------------------------
//
// list of tool functions
// - Uses: MySQL 4.2+

define ("DBLOADED", true);

if(!defined("VALID_LOAD")) die ("This file cannot be accessed directly!");

// ----------- DATABASE UNIVERSAL FUNCTIONS ---------------

function univGetQuery($sql){
	_e($sql."<br/>");
	if(DB_VER < 3.34){
		return getQuery($sql);
	}else{
		global $db;
		return $db->getQuery($sql);
	}
}

function univUpdateQuery($sql){
	_e($sql."<br/>");
	if(DB_VER < 3.34){
		return updateQuery($sql);
	}else{
		global $db;
		return $db->updateQuery($sql);
	}
}

function univInsertQuery($sql){
	_e($sql."<br/>");
	if(DB_VER < 3.34){
		return insertQuery($sql);
	}else{
		global $db;
		return $db->insertQuery($sql);
	}
}

function univDeleteQuery($sql){
	_e($sql."<br/>");
	if(DB_VER < 3.34){
		return deleteQuery($sql);
	}else{
		global $db;
		return $db->deleteQuery($sql);
	}
}

function univGetNumRows($sql){
	_e($sql."<br/>");
	if(DB_VER < 3.34){
		return getNumRows($sql);
	}else{
		global $db;
		return $db->getNumRows($sql);
	}
}

function univGetTotalRows($sql){
	_e($sql."<br/>");
	if(DB_VER < 3.34){
		return getTotalRows($sql);
	}else{
		global $db;
		return $db->getTotalRows($sql);
	}
}

function univGetAffectedRows(){
	if(DB_VER < 3.34){
		return getAffectedRows();
	}else{
		global $db;
		return $db->getTotalAffectedRows();
	}
}

function univGetError(){
	if(DB_VER < 3.34){
		return dbError();
	}else{
		global $db;
		return $db->dbError();
	}
}

// ----------- DATABASE WRAPPER FUNCTIONS ---------------

/**
 * Return recordset array from SELECT query
 * @param string $table
 * @param string $fields
 * @param string $crit
 * @param string $order
 * @param string $limit
 * @param string $groupby
 * @param boolean $flatten
 * @return array
 */
function getRec($table, $fields = "*", $crit = "", $order = "", $limit = "", $groupby = "", $flatten = false) {
	global $db;

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
		if($db->savequery) $db->lastquery = $sql;
		$rec = univGetQuery($sql);
		if($flatten && count($rec) == 1){
            // reduce multidimension array with a single element to a single dimension array
            return $rec[0];
        }else{
	        return $rec;
        }
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
}

/**
 * Return recordset array from SELECT JOIN query of 2 tables
 * @param string $table1
 * @param string $table2
 * @param string $fields
 * @param string $joinfields
 * @param string $jointype (JOIN, INNER JOIN, LEFT JOIN, RIGHT JOIN)
 * @param string $crit
 * @param string $order
 * @param string $limit
 * @return array
 */
function getRecJoin($table1, $table2, $fields, $joinfields, $jointype, $crit = "", $order = "", $limit = "", $groupby = "") {
	//select fields from table1 join table2 on field1 = field2
	global $db;

	$mysql_joins = array("JOIN", "INNER JOIN", "LEFT JOIN", "RIGHT JOIN");
	$jointype = strtoupper($jointype);
	if($table1 != "" && $table2 != "" && $fields != "" && $joinfields != "" && in_array($jointype, $mysql_joins)) {
		$fields = trim($fields);
		$crit = trim($crit);
		$order = trim($order);
		$limit = trim($limit);
		if($fields == "") $fields = "*";
		$sql = "SELECT $fields
				FROM $table1
				$jointype $table2
				ON $joinfields";
		if($crit != "") $sql .= " WHERE $crit";
		if($groupby != "") $sql .= " GROUP BY $groupby";
		if($order != "") $sql .= " ORDER BY $order";
		if($limit != "") $sql .= " LIMIT $limit";
		if($db->savequery) $db->lastquery = $sql;
		$rec = univGetQuery($sql);
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
	return $rec;
}

/**
 * Return recordset array from SELECT JOIN query of up to 3 tables
 * @param string $table1
 * @param string $table2
 * @param string $table3
 * @param string $fields
 * @param string $joinfields1
 * @param string $jointype1 (JOIN, INNER JOIN, LEFT JOIN, RIGHT JOIN)
 * @param string $joinfields2
 * @param string $jointype2 (JOIN, INNER JOIN, LEFT JOIN, RIGHT JOIN)
 * @param string $crit
 * @param string $order
 * @param string $limit
 * @param string $groupby [optional]
 * @return array
 */
function getRecTripleJoin($table1, $table2, $table3, $fields, $joinfields1, $jointype1, $joinfields2, $jointype2, $crit = "", $order = "", $limit = "", $groupby = "") {
	//select fields from table1 join table2 on field1 = field2 join table3 on field3 = field4
	global $db;

	$mysql_joins = array("JOIN", "INNER JOIN", "LEFT JOIN", "RIGHT JOIN");
	$jointype1 = strtoupper($jointype1);
	$jointype2 = strtoupper($jointype2);
	if($table1 != "" && $table2 != "" && $table3 != "" && $fields != "" && $joinfields1 != "" && $joinfields2 != "" && in_array($jointype1, $mysql_joins) && in_array($jointype2, $mysql_joins)) {
		$fields = trim($fields);
		$crit = trim($crit);
		$order = trim($order);
		$limit = trim($limit);
		if($fields == "") $fields = "*";
		$sql = "SELECT $fields
				FROM $table1
				$jointype1 $table2
				ON $joinfields1
				$jointype2 $table3
				ON $joinfields2";
		if($crit != "") $sql .= " WHERE $crit";
		if($groupby != "") $sql .= " GROUP BY $groupby";
		if($order != "") $sql .= " ORDER BY $order";
		if($limit != "") $sql .= " LIMIT $limit";
		if($db->savequery) $db->lastquery = $sql;
		$rec = univGetQuery($sql);
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
	return $rec;
}

/**
 * Return recordset array from SELECT JOIN query of multiple tables
 * @param array $tables
 * @param array $fields
 * @param array $joinfields
 * @param array $jointypes (JOIN, INNER JOIN, LEFT JOIN, RIGHT JOIN)
 * @param string $crit
 * @param string $order
 * @param string $limit
 * @param string $groupby [optional]
 * @return array
 */
function getRecArrayJoin($tables, $fields, $joinfields, $jointypes, $crit = "", $order = "", $limit = "", $groupby = "") {
	//select fields from table1 join table2 on field1 = field2
	global $db;

	$mysql_joins = array("JOIN", "INNER JOIN", "LEFT JOIN", "RIGHT JOIN");
	if(is_array($tables) && is_array($fields) && is_array($joinfields) && is_array($jointypes)) {
		if($fields[0] == "") $fields[0] = "*";
		$sql = "SELECT ".implode(", ", $fields)."
				FROM ".$tables[0]."
				";
		for ($i = 1; $i < count($tables); $i++){
			$table = trim($tables[$i]);
			$field = trim($fields[$i]);
			if($field == "") $field = "*";
			$joinfield = trim($joinfields[$i]);
			$jointype = strtoupper(trim($jointypes[$i]));
			if(in_array($jointype, $mysql_joins)){
				$sql .= "$jointype $table
						ON $joinfield
						";
			}
		}

		$crit = trim($crit);
		$order = trim($order);
		$limit = trim($limit);
		if($crit != "") $sql .= " WHERE $crit";
		if($groupby != "") $sql .= " GROUP BY $groupby";
		if($order != "") $sql .= " ORDER BY $order";
		if($limit != "") $sql .= " LIMIT $limit";
		if($db->savequery) $db->lastquery = $sql;
		$rec = univGetQuery($sql);
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
	return $rec;
}

/**
 * Return recordset array from SELECT DISTINCT query
 * @param string $table
 * @param string $fields
 * @param string $crit
 * @param string $order
 * @param string $limit
 * @param string $groupby
 * @param boolean $flatten
 * @return array
 */
function getRecDistinct($table, $fields = "*", $crit = "", $order = "", $limit = "", $groupby = "", $flatten = false) {
	global $db;

	if($table != "" && $fields != "") {
		$fields = trim($fields);
		$crit = trim($crit);
		$order = trim($order);
		$limit = trim($limit);
		if($fields == "") $fields = "*";
		$sql = "SELECT DISTINCT $fields
				FROM $table";
		if($crit != "") $sql .= " WHERE $crit";
		if($groupby != "") $sql .= " GROUP BY $groupby";
		if($order != "") $sql .= " ORDER BY $order";
		if($limit != "") $sql .= " LIMIT $limit";
		if($db->savequery) $db->lastquery = $sql;
		$rec = univGetQuery($sql);
		if($flatten and count($rec) > 0){
            // reduce multidimension array with a single element to a single dimension array
            $temp = $rec[0];
            $rec = $remp;
        }
        return $rec;
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
}

/**
 * Return recordset array from SELECT UNION query
 * @param array $tables
 * @param array $fields
 * @param array $crits
 * @param array $orders
 * @param string $limit
 * @return array
 */
function getRecUnion($tables, $fields, $crits, $orders, $limit = "") {
	global $db;

	$union = "";
	$sql = "";
	if(isset($tables)) {
        $limit = trim($limit);
		for($i = 0; $i<count($tables); $i++) {
			// bind sqls
			if($sql != "") $sql .= " UNION ";
			// create a select statement for each tableset
			$tableset = trim($tables[$i]);
			if(is_array($fieldset)) $fieldset = trim($fields[$i]);
			if(is_array($critset)) $critset = trim($crits[$i]);
			if(is_array($orderset)) $orderset = trim($orders[$i]);
			if($fieldset == "") $fieldset = "*";
			$sql .= "(SELECT $fieldset FROM $tableset";
    		if($critset != "") $sql .= " WHERE $critset";
    		if($orderset != "") $sql .= " ORDER BY $orderset";
			$sql .= ")";
		}
		if($limit != "") $sql = "SELECT uni.* FROM (".$sql.") AS uni LIMIT $limit";
		if($db->savequery) $db->lastquery = $sql;
		$rec = univGetQuery($sql);
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
	return $rec;
}

/**
 * Return number of rows from SELECT query
 * @param string $table
 * @param string $fields
 * @param string $crit
 * @return integer
 */
function getRecNumRows($table, $fields, $crit = "") {
	global $db;

	if($table != "") {
		$fields = trim($fields);
		$crit = trim($crit);
		$sql = "SELECT $fields
				FROM $table";
		if($crit != "") $sql .= " WHERE $crit";
		$num = univGetNumRows($sql);
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
	return $num;
}

/**
 * Return number of rows from SELECT JOIN query
 * @param string $table1
 * @param string $table2
 * @param string $fields
 * @param string $joinfields
 * @param string $jointype
 * @param string $crit
 * @return integer
 */
function getRecJoinNumRows($table1, $table2, $fields, $joinfields, $jointype, $crit = "", $order = "", $limit = "", $groupby = "") {
	//select fields from table1 join table2 on field1 = field2
	global $db;

	$mysql_joins = array("JOIN", "INNER JOIN", "LEFT JOIN", "RIGHT JOIN");
	$jointype = strtoupper($jointype);
	if($table1 != "" && $table2 != "" && $fields != "" && $joinfields != "" && in_array($jointype, $mysql_joins)) {
		$fields = trim($fields);
		$crit = trim($crit);
		$order = trim($order);
		$limit = trim($limit);
		if($fields == "") $fields = "*";
		$sql = "SELECT $fields
				FROM $table1
				$jointype $table2
				ON $joinfields";
		if($crit != "") $sql .= " WHERE $crit";
		if($groupby != "") $sql .= " GROUP BY $groupby";
		if($order != "") $sql .= " ORDER BY $order";
		if($limit != "") $sql .= " LIMIT $limit";
		$num = univGetNumRows($sql);
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
	return $num;
}

/**
 * Return number of rows from SELECT JOIN query for multiple tables
 * @param array $tables
 * @param array $fields
 * @param array $joinfields
 * @param array $jointypes
 * @param string $crit
 * @param string $order
 * @param string $limit
 * @param string $groupby
 * @return integer
 */
function getRecNumRowsArrayJoin($tables, $fields, $joinfields, $jointypes, $crit = "", $order = "", $limit = "", $groupby = "") {
	//select fields from table1 join table2 on field1 = field2
	global $db;

	$mysql_joins = array("JOIN", "INNER JOIN", "LEFT JOIN", "RIGHT JOIN");
	if(is_array($tables) && is_array($fields) && is_array($joinfields) && is_array($jointypes)) {
		if($fields[0] == "") $fields[0] = "*";
		$sql = "SELECT ".implode(", ", $fields)."
				FROM ".$tables[0]."
				";
		for ($i = 1; $i < count($tables); $i++){
			$table = trim($tables[$i]);
			$field = trim($fields[$i]);
			if($field == "") $field = "*";
			$joinfield = trim($joinfields[$i]);
			$jointype = strtoupper(trim($jointypes[$i]));
			if(in_array($jointype, $mysql_joins)){
				$sql .= "$jointype $table
						ON $joinfield
						";
			}
		}

		$crit = trim($crit);
		$order = trim($order);
		$limit = trim($limit);
        $groupby = trim($groupby);
		if($crit != "") $sql .= " WHERE $crit";
		if($groupby != "") $sql .= " GROUP BY $groupby";
		if($order != "") $sql .= " ORDER BY $order";
		if($limit != "") $sql .= " LIMIT $limit";
		$num = univGetNumRows($sql);
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
	return $num;
}

/**
 * Return the total rows from SELECT query (better for large result sets)
 * @param string $table
 * @param string $field
 * @param string $crit
 * @return integer
 */
function getRecTotal($table, $field, $crit = "") {
	global $db;

	if($table != "") {
		$fields = trim($fields);
		$crit = trim($crit);
		$sql = "SELECT $field
				AS `total`
				FROM $table";
		if($crit != "") $sql .= " WHERE $crit";
		$num = univGetTotalRows($sql);
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
	return $num;
}

/**
 * Return the total affected rows from most recent insert or delete query
 * @return integer
 */
function getRecTotalAffectedRows() {
	return univGetAffectedRows();
}

/**
 * Return field value of a record
 * @param string $table
 * @param string $field
 * @param string $crit
 * @return string
 */
function getRecItem($table, $field, $crit) {
	global $db;

	if($table != "" && $field != "" && $crit != "") {
		if(strpos($field, "(") === false){
			$item_rec = getRec($table, "`".$field."`", $crit, "", "1");
		}else{
			$item_rec = getRec($table, $field, $crit, "", "1");
		}
		if(isset($item_rec[0])){
			_e(current($item_rec[0]))."<br/>";
			return current($item_rec[0]);
		}
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
}

/**
 * Return array of field values from a recordset
 * @param string $table
 * @param string $fields
 * @param string $crit
 * @return array
 */
function getRecItemList($table, $fields, $crit) {
	global $db;

	if($table != "" && $fields != "" && $crit != "") {
		$item_rec = getRec($table, $fields, $crit, "", "1");
		$field_array = explode(",", $fields);
		$data_array = array();
		foreach($field_array as $field){
			$data_array[] = $item_rec[0][trim($field)];
		}
		_pr($data_array);
		return $data_array;
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
}

/**
 * Return an array of arrays containing field data
 * @param string $table
 * @param string $fields
 * @param string $crit
 */
function getRecFieldArray($table, $fields, $crit = ""){
	global $db;

	if($table != "" && $fields != "") {
		$rec = getRec($table, $fields, $crit, "", "");
		$data_array = array();
		foreach($rec as $row){
			foreach($row as $field => $data){
				$data_array[$field][] = $data;
			}
		}
		_pr($data_array);
		return $data_array;
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
}

/**
 * Return the lowest autoindex value
 * @param string $table
 * @param string $index
 * @return integer
 */
function getFirstID($table, $index, $crit = "") {
	if($table != "" && $index != "") {
		$index = trim($index);
		$sql = "SELECT $index
				FROM $table ";
		if ($crit != "") $sql .= "WHERE $crit ";
	 	$sql.= "ORDER BY $index ASC
				LIMIT 1";
	 	$rec = univGetQuery($sql);
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
	return intval($rec[0][$index]);
}

/**
 * Return the last autoindex value
 * @param string $table
 * @param string $index
 * @return integer
 */
function getLastID($table, $index, $crit = "") {
	if($table != "" && $index != "") {
		$index = trim($index);
		$sql = "SELECT $index
				FROM `$table` ";
		if ($crit != "") $sql .= "WHERE $crit ";
	 	$sql.= "ORDER BY $index DESC
				LIMIT 1";
	 	$rec = univGetQuery($sql);
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
	return intval($rec[0][$index]);
}

/**
 * Return the parent cat_id of the current record
 * @param string $table
 * @param integer $row_id
 */
function getRecCatID($table, $row_id){
    $id = intval($row_id);
    $cat_id = 0;
    if($table != '' && $id > 0){
        $catfield = (($table == "page") ? "ppage_id" : "cat_id");
        $cat_id = intval(getRecItem($table, $catfield, "id = '$id'"));
    }
    return $cat_id;
}

/**
 * Update a record
 * @param string $table
 * @param string $fieldvals
 * @param string $crit
 * @return boolean
 */
function updateRec($table, $fieldvals, $crit) {
	global $db;

	if($table != "" && $fieldvals != "" && $crit != "") {
		$fieldvals = trim($fieldvals);
		$crit = trim($crit);
		$sql = "UPDATE $table
				SET $fieldvals
				WHERE $crit";
		univUpdateQuery($sql);
		return true;
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
}

/**
 * Insert a record
 * @param string $table
 * @param string $fields
 * @param string $values
 * @param string $rememberdata
 * @return integer index value
 */
function insertRec($table, $fields, $values, $rememberdata = null) {
	global $db;

	if($table != "" && $fields != "" && $values != "") {
		$fields = trim($fields);
		$values = trim($values);
		$sql = "INSERT INTO $table
				($fields)
				VALUES ($values)";
		$id = univInsertQuery($sql);
		if($rememberdata != null) {
			$GLOBALS['lastitemval'] = $rememberdata;
			$GLOBALS['lastitemid'] = $id;
			unset($_REQUEST);
		}
        return $id;
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
}

/**
 * Update a record if exists, otherwise insert a new record
 * @param string $table
 * @param string $fieldvals
 * @param string $crit
 * @return boolean
 */
function replaceRec($table, $fieldvals, $crit) {
	global $db;

	if($table != "" && $fieldvals != "" && $crit != "") {
		$fieldvals = trim($fieldvals);
		$crit = trim($crit);
		if(getRecNumRows($table, "*", $crit) > 0){
			$sql = "UPDATE $table
					SET $fieldvals
					WHERE $crit";
			univUpdateQuery($sql);
			return true;
		}else{
			$sql = "INSERT INTO $table
					SET $fieldvals";
			$id = univInsertQuery($sql);
			return $id;
		}
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
}

/**
 * Delete a record
 * @param string $table
 * @param string $crit
 * @return boolean
 */
function deleteRec($table, $crit) {
	if($table != "" && $crit != "") {
		$crit = trim($crit);
		$sql = "DELETE FROM $table
				WHERE $crit";
		$result = univDeleteQuery($sql);
		if(!$result) $fnerr = DB_DELETE_ERROR;
    	return true;
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
}

/**
 * Copy a record
 * @param string $table
 * @param string $srcid
 * @param string $clonenewfld
 * @param string $clonenewdata
 * @return integer index value
 */
function cloneRec($table, $srcid, $clonenewfld, $clonenewdata){
	if($table != "" && $srcid > 0 && $clonenewfld != "" && $clonenewdata != ""){
		$srcrec = getRec($table, "*", "id = $srcid", "", "1");
		$newflds = $clonenewfld;
		$newdata = "'$clonenewdata'";
		foreach($srcrec[0] as $key => $value){
			if($key != $clonenewfld && $key != "id" && !is_int($key)){
				// skip passed param which includes the new record title provided by user
				$incl_fld = true;
				if($key == "rank"){
					$value = getLastID($table, "rank", "") + 1;
				}elseif(substr($key, 0, 5) == 'date_' && $value == ''){
					$value = date('Y-m-d');
				}
				if($incl_fld){
					$newflds .= ", ".$key;
					$newdata .= ", '".$value."'";
				}
			}
		}
		$id = insertRec($table, $newflds, $newdata);
		if($id > 0){
			return $id;
		}else{
			return false;
		}
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
}

/**
 * Increase the ranking of a record
 * @param string $table
 * @param integer $row_id
 */
function promoteRec($table, $row_id, $groupid = "") {
	/* move ranking up (to next lower index)
	 * - obtaining next highest rank id ($rank_high)
	 * - storing current rank id ($rank_cur)
	 * - updating rank of current record with $rank_higher value
	 * - updating rank of next higher record with $rank_cur value
	*/
	global $db;

	$currec = getRec($table, "*", "id = '$row_id'", "", "1");
	if(is_array($currec)) {
		// current record exists, continue
		$rank_cur = $currec[0]['rank'];
		// if we are moving within a group, build group crit
		if($groupid != "") $rank_group = " AND ".$groupid."=".$currec[0][$groupid];
		$prorec = getRec($table, "rank, id", "rank < ".$rank_cur.$rank_group, "rank DESC", "1");
		if(is_array($prorec)) {
			// higher ranking record exists, continue
			$rank_high = $prorec[0]['rank'];
			$higher_id = $prorec[0]['id'];
			// swap ranking
			updateRec($table, "rank = '".$rank_high."'", "id = '$row_id'");
			updateRec($table, "rank = '".$rank_cur."'", "id = '$higher_id'");
		}
	}
}

/**
 * Decrease ranking of a record
 * @param string $table
 * @param integer $row_id
 */
function demoteRec($table, $row_id, $groupid = "") {
	/* move ranking down (to next higher index)
	 * - obtaining next lower rank id ($rank_low)
	 * - storing current rank id ($rank_cur)
	 * - updating rank of current record with $rank_lower value
	 * - updating rank of next lower record with $rank_cur value
	*/
	$currec = getRec($table, "*", "id = '$row_id'", "", "1");
	if(is_array($currec)) {
		// current record exists, continue
		$rank_cur = $currec[0]['rank'];
		// if we are moving within a group, build group crit
		if($groupid != "") $rank_group = " AND ".$groupid."=".$currec[0][$groupid];
		$demrec = getRec($table, "rank, id", "rank > ".$rank_cur.$rank_group, "rank ASC", "1");
		if(is_array($demrec)) {
			// higher ranking record exists, continue
			$rank_low = $demrec[0]['rank'];
			$lower_id = $demrec[0]['id'];
			// swap ranking
			updateRec($table, "rank = '".$rank_low."'", "id = '$row_id'");
			updateRec($table, "rank = '".$rank_cur."'", "id = '$lower_id'");
		}
	}
}

/**
 * Return a list (UL/LI) of field data contained in a DIV
 * @param string $table
 * @param string $field
 * @param string $dir
 * @return string
 */
function getMenuFromDB($table, $field, $dir, $section, $crit) {
	if($table != "" && $field != "") {
		$recset = getRec($table, "*", $crit, "id", "");
		$retn = '<div id="popup_'.$table.'" class="navmenu">';
		#$retn .= '<h3>'.ucwords_smart($table).'</h3>';
		$retn .= '<ul>';
		for($i=0; $i<count($recset); $i++){
			$value = $recset[$i]['name'];
			$code = $recset[$i]['code'];
			$retn .= '<li><a href="'.$dir.'?'.$section.'='.$code.'"><span>'.$value.'</span></a></li>';
		}
		$retn .= '</ul>';
		$retn .= '</div>';
	}
	return $retn;
}

/**
 * Reduce an array derived from a 'get' function into an array of id:value-paired elements
 * @param array $arry
 * @param string $idfld Set to blank to reduce array even further to an indexed array of values (val1, val2...)
 * @param string $valfld
 * @return array
 */
function flattenDBArray($arry, $idfld, $valfld) {
	$retnarry = array();
	if(is_array($arry) && $valfld != ''){
		foreach($arry as $elem){
            if($idfld != ''){
                if(!isblank($elem[$idfld]))
                    $retnarry[$elem[$idfld]] = getIfSet($elem[$valfld]);
            }else{
                $retnarray[] = getIfSet($elem[$valfld]);
            }
		}
		return $retnarry;
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
}

/**
 * Return array containing all data table names
 * @return array
 */
function getDataTables(){
	$rtn = array();
    if(defined('DBNAME')){
        $tables = univGetQuery("SHOW TABLES WHERE `tables_in_".DBNAME."` LIKE '".DB_TABLE_PREFIX."%'");
        if(count($tables) > 0){
            foreach($tables as $table){
                $rtn[] = $table['Tables_in_'.DBNAME];
            }
        }
    }
	return $rtn;
}

/**
 * Return all or specific field(s) schema from a table
 * @param type $table
 * @param type $find_field
 * @return boolean
 */
function getTableFields($table, $find_field = ''){
	if($table != "") {
		$sql = "SHOW COLUMNS FROM `$table`".(($find_field != '') ? " WHERE `field` = '$find_field'" : "");
		$rec = univGetQuery($sql);
        return $rec;
	}else{
		_e(MISSING_ARG.__FUNCTION__, true);
		return false;
	}
}

/**
 * Return whether or not table exists
 * @param string $table
 * @return boolean
 */
function findTable($table){
    if($table != ''){
        $tables = univGetQuery("SHOW TABLES WHERE `tables_in_".DBNAME."` = '".$table."'");
        return (count($tables) > 0);
    }
    return false;
}

// ----------- DATABASE CONFIG FUNCTIONS ---------------

/**
 * Read db.ini file settings int array
 * @return array
 */
function readDBINI(){
	global $db;

	$dbset = array();
    if(file_exists(SITE_PATH.ADMIN_FOLDER.CONFIG_FOLDER."db.ini")){
        // load each line of db.ini into $dbset where lines starting with # are the parent key
        if(($fh = @fopen(SITE_PATH.ADMIN_FOLDER.CONFIG_FOLDER."db.ini", "r")) !== false){
            $dbkey = "";
            while($line = fgets($fh)){
                $line = trim($line);
                if(substr($line, 0, 1) == "#"){
                    if($dbkey != substr($line, 1)) {
                        $dbkey = substr($line, 1);
                        $dbset[$dbkey] = array();
                    }
                }elseif($dbkey != ''){
                    $nameval = explode("=", $line);
                    if(strtolower($nameval[0]) == 'dbport'){
                        $nameval[1] = intval($nameval[1]);
                    }
                    $dbset[$dbkey][strtolower($nameval[0])] = $nameval[1];
                }
            }
            fclose($fh);
        }
    }
    return $dbset;
}

/**
 * Update db.ini file with settings
 * @param array $dbset
 */
function updateDBINI($dbset){
    // write $dbset nodes to db.ini file
	global $db;

	chmod(SITE_PATH.ADMIN_FOLDER.CONFIG_FOLDER."db.ini", 0777);
    $sys_go = (isset($dbset["SYS_GO"]));
    if($sys_go) unset($dbset["SYS_GO"]);                        // ensures SYS_GO is the last line
    if(($fh = @fopen(SITE_PATH.ADMIN_FOLDER.CONFIG_FOLDER."db.ini", "w")) !== false){
        foreach($dbset as $key => $vals){
            fwrite($fh, "#".$key.PHP_EOL);
            foreach($vals as $name => $val){
                if($name == 'DBPORT' && $val == 0) $val = '';   // don't write 0
                fwrite($fh, strtoupper($name)."=".$val.PHP_EOL);
            }
        }
        fwrite($fh, "#SYS_GO".PHP_EOL);
        fclose($fh);
    }
    // lock up the file from further writing
    chmod(SITE_PATH.ADMIN_FOLDER.CONFIG_FOLDER."db.ini", 0644);
}

// ----------- ANCESTRY FUNCTIONS ---------------

/**
 * Based on the lineage (hierarchy categorization) of the parent record,
 * get the next incremented sibling index
 * @param string $table
 * @param integer $parent_id
 * @return integer Next lineage index or false on failure.
 */
function getNextLineage($table, $parent_id, $lineage_field = "lineage"){
    // based on the lineage of the parent record,
    // get the next incremented sibling index

    if($table != '' && $parent_id >= 0){
        $catfield = (($table == "pages") ? "ppage_id" : "cat_id");
        $siblings = getRec($table, $lineage_field, $catfield." = '".intval($parent_id)."'", "lineage DESC", "1");
        if(count($siblings) > 0){
            $highest_sibling_lineage = $siblings[0]['lineage'];
        }else{
            $highest_sibling_lineage = '';
        }
        if($highest_sibling_lineage != ''){
            $pos = strrpos($highest_sibling_lineage, ",");
            if($pos === false){
                // top level sibling => n+1
                $new_sibling_lineage = intval($highest_sibling_lineage) + 1;
            }else{
                // child sibling => n,m...+1
                $next_index = intval(substr($highest_sibling_lineage, $pos + 1)) + 1;
                $new_sibling_lineage = substr($highest_sibling_lineage, 0, $pos).",".$next_index;
            }
        }else{
            // no siblings -- top or child => {n,{m,}}0
            $parent_lineage = getRecItem($table, $lineage_field, "id = '".intval($parent_id)."'");
            $new_sibling_lineage = $parent_lineage.(($parent_lineage != '') ? "," : "")."0";
        }
        return $new_sibling_lineage;
    }else{
        _e(MISSING_ARG.__FUNCTION__, true);
        return false;
    }
}

/**
 * Update lineages (hierarchy indices) of a record and it's decendants
 * @param string $table
 * @param integer $id
 * @param string $lineage
 * @param string $lineage_field
 */
function updateLineages($table, $id, $new_lineage, $lineage_field = 'lineage'){
    // update lineages of a record's decendants
	global $db;

	if($id >= 0 && $table != ''){
        // get the lineage of the subject record
        $lineage = getRecItem($table, $lineage_field, "id = '$id'");
        if($new_lineage == '') $new_lineage = '0';

        // update lineages of all records whose lineages start with same
        updateRec($table, "{$lineage_field} = REPLACE({$lineage_field}, '{$lineage}', '$new_lineage')", "{$lineage_field} = '$lineage' OR {$lineage_field} LIKE '{$lineage},%'");
    }
}

/**
 * Retrieves array of multiple-depth categories (if a single-depth list is desired, use getRec).
 * @param string $cattable The category table
 * @param integer $cat_id The category id to remove from the list (required to prevent a category from being its own parent)
 * @param $hierarchical Sort by lineage (hierarchy)
 * @param $skipself True to exclude the input cat_id if encountered
 * @return array
 */
function getCategories($cattable, $cat_id = 0, $hierarchical = true, $skipself = true){
    if($cattable != ''){
        if($cattable == "pages"){
            $catfield = "ppage_id";
            $namefield = "pagetitle";
        }else{
            $catfield = "cat_id";
            $namefield = "name";
        }
        $crit = (($skipself) ? "p.id != '{$cat_id}'" : "");
        if($hierarchical)
            $pname = "IF({$catfield}>0, CONCAT(REPEAT('...', LENGTH(lineage)-LENGTH(REPLACE(lineage, ',', ''))), {$namefield}), {$namefield}) as pname";
        else
            $pname = "p.{$namefield} as pname";
        $cat_array = getRec($cattable." p", "p.id, ".$pname, $crit, (($hierarchical) ? "p.lineage, " : "")."p.{$namefield}", "");
        $cat_array = array('0' => '- Top Level (No Parent) -') + flattenDBArray($cat_array, 'id', 'pname');
        return $cat_array;
    }else{
        return null;
    }
}

/**
 * Returns 1) an array of ancestor ids, 2) the highest ancestor id reached, 3) if we reached
 * the top of the ancestor tree.
 * Reaching the treetop is useful especially if we want to know if the entire
 * $code_array chain is valid, otherwise the process will only collect ancestor ids
 * @param string $table
 * @param integer $from_id Leave 0 to traverse by and validate code array (required if $from_id = 0)
 * @param array $code_array Include to traverse by and validate codes
 * @return array ancestor array, highest ancestor, if treetop was reached
 */
function getRecAncestors($table, $from_id = 0, $code_array = null){
    $ancestors = array();
    $highest_parent = -1;
    $cat_id = 0;
    $treetop_reached = false;

    if($table != ''){
        // if $from_id = 0, get rec id from code/pagename based on code_array
        $table = strtolower($table);
        $cat_field = (($table == "pages") ? "ppage_id" : "cat_id");
        $code_field = (($table == "pages") ? "pagename" : "code");
        if($from_id == 0 && is_array($code_array)){
            $from = $code_array[count($code_array) - 1];
            $id = intval(getRecItem($table, "id", $code_field." = '$from'"));
            array_pop($code_array);
        }else{
            $id = intval($from_id);
        }
        if($id > 0){
            // cat_table is either the current table (category-based records) or
            // {$table}_cat (standard records)
            $cat_table = $table;
            if(substr($table, -4, 4) != '_cat'){
                if(findTable($table."_cat")){
                    $cat_table = $table."_cat";
                }
            }

            // get the category index of this record
            $cat_id = intval(getRecItem($table, $cat_field, "id = '$id'"));
            $limiter = 0;
            while($cat_id > 0 && $limiter < 255){
                // a parent exists
                $ancestors[] = $cat_id;
                $id = $cat_id;
                if($from_id > 0){
                    // traverse by ids
                    $cat_id = intval(getRecItem($cat_table, $cat_field, "id = '$id'"));
                }else{
                    // traverse by matching next code
                    if(count($code_array) > 0){
                        $code = $code_array[count($code_array) - 1];
                        $next_cat = getRec($cat_table, "*", $code_field." = '$code'", "", "1");
                        $next_cat_id = intval(getIfSet($next_cat[0]['id']));
                        if($next_cat_id != $cat_id) {
                            // code was not for the expected parent; exit early
                            break;
                        }else{
                            // get the cat id and reduce code array by one
                            $cat_id = intval($next_cat[0][$cat_field]);
                            array_pop($code_array);
                        }
                    }else{
                        $limiter = 255; // we ran out of codes; end early
                    }
                }
                $limiter++;         // just in case we enter a loop
            }
            $highest_parent = $id;  // highest parent is the last id reached
        }
    }
    $treetop_reached = ($highest_parent > 0 && $cat_id == 0);
    return array($ancestors, $highest_parent, $treetop_reached);
}

/**
 * Return the direct children (tables with no lineage) or all children and children of children
 * (tables with lineage) of a table starting from a specific id or code
 * @param string $table
 * @param string|integer $from
 * @return array descendants
 */
function getRecDescendants($table, $from){
    $descendants = array();
    if($table != '' && !isBlank($from)){
        if(!is_numeric($from) || intval($from) == 0){
            // get id from record with code
            $code_field = (($table == 'pages') ? 'pagename' : 'code');
            $id = intval(getRecItem($table, "id", "{$code_field} = '{$from}'"));
        }else{
            $id = intval($from);
        }

        if($id > 0){
            // does table contain the lineage column?
            $cat_field = (($table == 'pages') ? 'ppage_id' : 'cat_id');
            if (count(getTableFields($table, "lineage")) > 0){
                // search and sort by lineage
                $lineage = getRecItem($table, "lineage", "id = '$id'");
                $descendants = getRec($table, "*", "lineage LIKE '{$lineage},%'", "lineage", "");
            }else{
                // search and sort by id
                $descendants = getRec($table, "*", "{$cat_field} = '{$id}'", "id", "");
            }
        }
    }
    return $descendants;
}

?>