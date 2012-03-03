<?php
/**
 * Data Caching Script
 * Easily Cache Information to a MySQL Table or a Flat-File
 *
 * Email me possible bugs and suggestions to help improve
 * future releases and patches.
 *
 * @author JMan <jman@bedpan.ca>
 * @link http://www.bedpan.ca
 * @version 2.2.0
 */
class Cache{
	/**
	 * Toggles whether to use MySQL or Flat-File Caching.
	 *
	 * @var bool
	 */
	public $useMySQL = true;
	/**
	 * Your MySQL server address.
	 *
	 * @var string
	 */
	public $dbServer = "localhost";
	/**
	 * The user name for your MySQL with read and write privleges
	 * to the cache table that you've already created.
	 *
	 * @var string
	 */
	public $dbUser = "root";
	/**
	 * The password for the MySQL account above.
	 *
	 * @var string
	 */
	public $dbPass = "";
	/**
	 * The name of the database where the cache table is located.
	 *
	 * @var string
	 */
	public $dbName = "db_name";
	/**
	 * Directory on your server where cache data is to be kept
	 * with the trailing slash, relative to the script.
	 * Must exist, and be readable and writable in order to work.
	 *
	 * @var string
	 */
	public $cacheDir = "testCache/";
	/**
	 * Stores a back-up of old cached data (if available).
	 *
	 * @var array
	 */
	public $oldCache = array();

	/**
	 * Function used to connect to your MySQL server.
	 *
	 * @return bool
	 */
	private function mysqlConnect(){
		if ($con = @mysql_connect($this->dbServer, $this->dbUser, $this->dbPass)){
			mysql_select_db($this->dbName);
			return true;
		}else return false;
	}
	/**
	 * Adds the appropriate slashes to the data which is to be cached.
	 *
	 * @param string $item1
	 * @param int $clean
	 * @return string
	 */
	private function slash($item1, $clean=false){
		if ($clean == 1){
			return stripslashes($item1);
		}elseif ($clean == 2){
			return str_replace("\\", "\\\\", $item1);
		}else{
			return addslashes(str_replace("\\", "\\\\", $item1));
		}
	}
	/**
	 * Decodes the slashed and serialized cached data.
	 *
	 * @param unknown_type $data
	 * @return var
	 */
	private function deCode($data){
		$data = unserialize($this->slash($data, 1));
		if (is_array($data)){
			foreach ($data as $key => $value){
				$data[$key] = $this->slash($value, 2);
			}
		}else{
			$data = $this->slash($data, 2);
		}
		return $data;
	}
	/**
	 * The main function, which caches, and retrieves previously
	 * caches data from your MySQL server.
	 * Returns cached data (if available), or false on failure.
	 *
	 * @param string $id
	 * @param int $seconds
	 * @param var $data
	 * @return var/bool
	 */
	public function doCache($id, $seconds=0, $data=""){
		$id = addslashes($id);
		$exptime = time() + $seconds;
		if ($this->useMySQL){
			if ($this->mysqlConnect()){
				$result = mysql_query("SELECT * FROM weather_cache WHERE id = '$id'") or die(mysql_error());
				if (mysql_num_rows($result)){
					$db = mysql_fetch_row($result);
					$this->oldCache[$id] = $this->deCode($db[1]);
					mysql_query("DELETE FROM weather_cache WHERE id = '$id' && timestamp <= UNIX_TIMESTAMP()") or die(mysql_error());
				}
				$result = mysql_query("SELECT * FROM weather_cache WHERE id = '$id'") or die(mysql_error());
				if (mysql_num_rows($result)){
					$db = mysql_fetch_row($result);
					return $this->deCode($db[1]);
				}else{
					$data = $this->slash(serialize($data));
					if ($data) mysql_query("INSERT INTO weather_cache (id, stored, timestamp) VALUES ('$id', '$data', '$exptime')") or die(mysql_error());
					return false;
				}
			}else return false;
		}else{
			if (!is_dir(dirname($_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF']) . DIRECTORY_SEPARATOR . $this->cacheDir)) die("ERROR: \$cacheDir [" . dirname($_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF']) . DIRECTORY_SEPARATOR . $this->cacheDir . "] Must Exist, and be Readable and Writable");
			$fileName = realpath(dirname($_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF']) . DIRECTORY_SEPARATOR . $this->cacheDir) . DIRECTORY_SEPARATOR . $id;
			if (file_exists($fileName)){
				$this->oldCache = $this->deCode(file_get_contents($fileName));
				if (filemtime($fileName) <= time()) unlink($fileName);
			}
			if (file_exists($fileName)) return $this->deCode(file_get_contents($fileName));
			else{
				$data = $this->slash(serialize($data));
				$handle = fopen($fileName, 'w+');
				if ($data && $handle){
					fwrite($handle, $data);
					fclose($handle);
					touch($fileName, $exptime);
				}
				return false;
			}
		}
	}
}
?>