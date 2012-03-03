<?php

/* DATABASE COMMON CLASS */

/**
 * DB_COMMON
 * Contains common, low-level database functions
 * Extended by DB_Wrapper
 */

abstract class DB_common {
	private $data_array;

	//common method
	public function loadDataArray($result) {
		$data_array = array();

		$row_count = 0;
		while ($row = $result->fetch_assoc()) {
			foreach ($row as $key => $value) {
				//collect the data to be exported
				$data_array[$row_count][$key] = $value;
			}
			$row_count ++;
		}

		$result->close();	/* free result set */
		return $data_array;
	}

	public function get_query($db, $query) {
		if ($result = $db->query($query)) {
			return self::loadDataArray($result);
		}

		$this->log_error($db, $query);
		return null;
	}

	public function update_query($db, $query){
		if ($result = $db->query($query)) {
			return 1;
		}

		$this->log_error($db, $query);
		return 0;
	}

	public function insert_query($db, $query) {
		if ($result = $db->query($query)) {
			return $db->insert_id;
		}

		$this->log_error($db, $query);
		return 0;
	}

	public function delete_query($db, $query) {
		if ($result = $db->query($query)) {
			return 1;
		}

		$this->log_error($db, $query);
		return 0;
	}

	//this would only work the best for a small amount of data.
	public function get_num_rows($db, $query) {
		if ($result = $db->query($query)) {
			$num_rows = mysqli_num_rows($result);
			return $num_rows;
		}

		$this->log_error($db, $query);
		return 0;
	}

	public function get_total_affected_rows($db) {
        $aff_rows = mysqli_affected_rows($db);
        return $aff_rows;
	}

	//log error function
	public function log_error($db, $query) {
		error_log("\nDB ERROR => Filename: ".__FILE__." Line: ".__LINE__."\nSQL: $query \nProblem: ". $db->error."\n");
	}
}

?>