<?php

/* DATABASE GLOBAL CLASS */

/**
 * DB_WRAPPER
 * Performs database operations (MySQL engine 3.3+)
 * - table
 * - child_table
 * - parent_table
 * - num_rows
 * - savequery
 * - lastquery
 */
require_once('db_connection.class.php');
require_once('db_common.class.php');

class DB_wrapper extends DB_common {
	/* variables */
	private $_keys = array("table" => "", "child_table" => "", "parent_table" => "",
						   "num_rows" => 0, "savequery" => false, "lastquery" => "");
	protected $db;
	protected $conn;

	/* class object variables */

	// instantiate class object
	public function __construct() {
        $this->conn = new Connection();
		$this->db = $this->conn->getConnection();
    }

	// this would only work the best for a small amount of data.
	// use getTotalRows instead
	public function getNumRows($query) {
		return parent::get_num_rows($this->db, $query);
	}

	//only return the 'total' data
	public function getTotalRows($query) {
		$array = parent::get_query($this->db, $query);
		return $array[0]['total'];
	}

	public function getTotalAffectedRows() {
		return parent::get_total_affected_rows($this->db);
	}

	public function getQuery($query) {
		$array = parent::get_query($this->db, $query);
		$this->_keys['num_rows'] = count($array);
		return $array;
	}

	public function updateQuery($query) {
		return parent::update_query($this->db, $query);
	}

	public function insertQuery($query) {
		return parent::insert_query($this->db, $query);
	}

	public function deleteQuery($query) {
		return parent::delete_query($this->db, $query);
	}

	public function dbError() {
		return $this->db->error();
	}

	public function db_close() {
		$this->db->close();
	}

	public function showProperties(){
		printr($this->_keys);
	}

	public function saveNextQuery(){
		$this->_keys['savequery'] = true;
	}

	// magic functions
	public function __get($name) {
		if(isset($this->_keys[$name])){
			// return scalar value
			return $this->_keys[$name];
		}else{
			addErrorMsg("Cannot get '$name'.  It is not a valid DB property. ", CORE_ERR);
		}
	}

	public function __set($name, $value) {
		switch($name){
			case "table":
			case "child_table":
			case "parent_table":
				$this->_keys[$name] = $value;
				$fileurl = $_SERVER['SCRIPT_NAME'];
				if(strpos($fileurl, INC_FOLDER) === false){
					if(isset($_SESSION['register'][$fileurl])) unset($_SESSION['register'][$fileurl]);
					$_SESSION['register'][$fileurl] = array(
						"fileurl" => $fileurl,
						"db_".$name => $value
					);
					replaceRec("register", "`type` = 'db', `fileurl` = '$fileurl', `db_{$name}` = '$value'", "`type` = 'db' AND `fileurl` = '$fileurl'");
				}
				break;
			case "num_rows":
				$this->_keys[$name] = $value;
				break;
			case "lastquery":
				$this->_keys[$name] = str_replace(array("\n", "\r", "\t"), array("", "", " "), $value);
				$this->_keys['savequery'] = false;
				break;
			case "savequery":
				if($value === false || $value === true) $this->_keys[$name] = $value;
				break;
			default:
				addErrorMsg("Cannot set '$name'.  It is not a valid DB property. ", CORE_ERR);
				break;
		}
	}
}
?>