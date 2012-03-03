<?php
// ---------------------------
//
// AJAX PROCESSOR
//
//  - Handles generic SQL requests
//  - Use Ajaxwrapper for specific functions
//
// ---------------------------

define("VALID_LOAD", true);
define("BASIC_GETINC", true);
define("VHOST", substr(str_replace("\\", "/", realpath(__DIR__."/../../../")), strlen($_SERVER['DOCUMENT_ROOT']))."/");
define("DB_USED", ((isset($_REQUEST['db_used'])) ? (bool) $_REQUEST['db_used'] : true));

include ($_SERVER['DOCUMENT_ROOT'].VHOST."admin/inc/_core/getinc.php");				// required - starts PHP incls!!!

// Retrieve data from Query String
//		-- action, table, flds, vals, crit, orderby
// Escape User Input to help prevent SQL Injection
extractVariables($_GET);
//foreach($_GET as $key => $value) $$key = $value;

// Build query
if($action != "" && $table != ""){
	if($flds == "") $flds = "*";
	if($crit != "") $crit = " WHERE ".stripslashes($crit);
	if($orderby != "") $orderby = " ORDER BY ".$orderby;
	switch ($action){
		case "select":
			$sql = "SELECT {$flds} AS newfld FROM {$table}{$crit}{$orderby}";
			$qry_result = univGetQuery($sql);
			if($qry_result){
				// Build output string (returns to Ajax for displaying)
				$retn_string = "";
				for($i=0; $i<count($qry_result); $i++){
					if($i > 0) $retn_string .= "\n";
					$retn_string .= $qry_result[$i]['id']."|".$qry_result[$i]['newfld'];
				}
				echo $retn_string;
			}
		case "update":
			if($crit != "" && $flds != "" && $vals != ""){
				$fldarray = split("(,|, )", $flds);
				$valarray = split("(,|, )", $vals);
				$fldset   = "";
				for($i = 0; $i < count($fldarray); $i++){
					if($fldset != "") $fldset .= ", ";
					$fldset .= $fldarray[$i]."='".$valarray[$i]."'";
				}
				$sql = "UPDATE {$table} SET ".$fldset."{$crit}";
				$qry_result = $db->updateQuery($sql);
			}
			break;
		case "insert":
			if($flds != "" && $vals != ""){
				$valarray = split("(,|, )", $vals);
				$vals = implode("','", $valarray);
				$sql = "INSERT INTO {$table} ({$flds}) VALUES ('{$vals}')";
				$qry_result = $db->insertQuery($sql);
			}
			break;
		case "delete":
			if($crit != ""){
				$sql = "DELETE FROM {$table}{$crit}";
				$qry_result = $db->deleteQuery($sql);
			}
			break;
	}
}
?>
