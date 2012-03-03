<?php

/* DATABASE CONNECTION CLASS */

/**
 * CONNECTION
 * Performs database engine connecting and database object loading
 */

if(defined('DBNAME')){
	$db_database = DBNAME;
}
if(defined('DBHOST')){
	$db_host = DBHOST;
}
if(defined('DBUSER')){
	$db_username = DBUSER;
}
if(defined('DBPASS')){
	$db_password = DBPASS;
}
if(defined('DBPORT')){
	$db_port = DBPORT;
}

class Connection {
	protected $db_conn;
	protected $db_username;
	protected $db_password;
	protected $db_database;
	protected $db_host;
	protected $db_port;

	public function __construct($db_username=null, $db_password=null, $db_database=null, $db_host=null, $db_port=null) {
		if($db_username == ""){
			global $db_username;
		}

		if($db_password == ""){
			global $db_password;
		}

		if($db_database == ""){
			global $db_database;
		}

		if($db_host == ""){
			global $db_host;
		}

		if($db_port == ""){
			global $db_port;
		}

		$this->db_username = $db_username;
		$this->db_password = $db_password;
		$this->db_database = $db_database;
		$this->db_host = $db_host;
		$this->db_port = $db_port;
  	}

	public function getConnection(){
        // Get a new connection
		$this->db_conn = new mysqli($this->db_host, $this->db_username, $this->db_password, $this->db_database, $this->db_port);

		// Check for connection errors
		if (mysqli_connect_errno() || !$this->db_conn){
			$this->db_fatalError(mysqli_connect_error());
	  	}

		return $this->db_conn;
	}

	protected function db_fatalError($db_message) {
		die("Connection Fatal Error: " . $db_message);
  	}
}
?>