<?php

/* FRONT PAGE GLOBAL CLASSES */

/**
 * PAGECLASS
 * Stores and manages front page structure data
 * - header
 * - footer
 * - sectionid
 * - headlines[]
 */
class PageClass {
	// overloaded data
	private $_page = array();
	private $_keys = array(	"header", "footer", "sectionid");
	private $_arykeys= array("headlines");
	private $_subkeys = array(
							"file", "name", "id", "code", "alias", "title",
							"parenttitle", "content", "contenttype",
							"metatitle", "metakeywords", "metadescr", "nonce",
							"found", "error", "formTemplate", "ishomepage", "islocked",
							"issearchable", "isprotected", "ispublished", "isdraft",
                            "created", "updated", "published", "dbrec", "query",
	                        "queryvars");
	protected static $_instance = null;

	private function __construct() {
	}

	private function __clone(){
	}

	public static function init(){
		$s = new self;
		$s->checkCaller("init");
		return $s;
	}

	public function show_properties(){
        foreach($this->_keys as $k) print "[$k] = ".getIfSet($this->_page[$k])."<br>";
        foreach($this->_arykeys as $k)
            if(isset($this->_page[$k])) print "[$k] = <pre>".printr($this->_page[$k])."</pre><br/>";
        foreach($this->_subkeys as $k){
            if(isset($this->_page['subkeys'][$k])) {
                print "[$k] = ";
                if(is_array($this->_page['subkeys'][$k]))
                    print "<pre>".print_r($this->_page['subkeys'][$k], true)."</pre><br/>";
                else
                    print $this->_page['subkeys'][$k]."<br/>";
            }
        }
	}

	public function __get($name){
		if(in_array($name, $this->_keys)){
			// return scalar value
			return getIfSet($this->_page[$name]);
		}elseif(in_array($name, $this->_arykeys)){
			// return array
			return getIfSet($this->_page[$name]);
		}elseif(in_array($name, $this->_subkeys)){
			// return subarray value
			return getIfSet($this->_page['subkeys'][$name]);
		}else{
			trigger_error("Cannot get '$name'.  It is not a valid _PAGE property. ");
		}
	}

	public function __set($name, $value){
		if(in_array($name, $this->_keys)){
			// set scalar value
			//$this->checkCaller("set");
			$this->_page[$name] = $value;
		}elseif(in_array($name, $this->_arykeys)){
			// set array
			$this->_page[$name][] = $value;
		}elseif(in_array($name, $this->_subkeys)){
			// set subarray value
			$this->_page['subkeys'][$name] = $value;
		}else{
			trigger_error("Cannot set '$name'.  It is not a valid _PAGE property. ");
		}
	}

	private function checkCaller($action){
		$stack = debug_backtrace();
		$callerfile = $stack[1]['file'];
		if(strpos($callerfile, "_core") === false){
			die("Calling Page::$action in $callerfile not allowed!");
		}
	}
}

/**
 * DATACLASS
 * Stores and manages record structure data
 * - table
 * - childtable
 * - id
 * - dbrec
 */
class DataClass {
	// overloaded data
	private $_data = array();
	private $_keys = array(	"table", "childtable", "id" );
	private $_arykeys= array();
	private $_subkeys = array( "iscategory", "queryvars", "pattern", "dbrec",
                               "nonce", "found", "metabase", "error", "query",
                               "ispublished", "isdraft", "created", "updated",
                               "published", "numrows");
	protected static $_instance = null;

	private function __construct() {
	}

	private function __clone(){
	}

	public static function init(){
		$s = new self;
		$s->checkCaller("init");
		return $s;
	}

	public function show_properties(){
        foreach($this->_keys as $k) print "[$k] = ".getIfSet($this->_data[$k])."<br>";
        foreach($this->_arykeys as $k)
            if(isset($this->_data[$k])) print "[$k] = <pre>".printr($this->_data[$k])."</pre><br/>";
        foreach($this->_subkeys as $k){
            if(isset($this->_data['subkeys'][$k])) {
                print "[$k] = ";
                if(is_array($this->_data['subkeys'][$k]))
                    print "<pre>".print_r($this->_data['subkeys'][$k], true)."</pre><br/>";
                else
                    print $this->_data['subkeys'][$k]."<br/>";
            }
        }
	}

	public function __get($name){
		if(in_array($name, $this->_keys)){
			// return scalar value
			return getIfSet($this->_data[$name]);
		}elseif(in_array($name, $this->_arykeys)){
			// return array
			return getIfSet($this->_data[$name]);
		}elseif(in_array($name, $this->_subkeys)){
			// return subarray value
			return getIfSet($this->_data['subkeys'][$name]);
		}else{
			trigger_error("Cannot get '$name'.  It is not a valid _DATA property. ");
		}
	}

	public function __set($name, $value){
		if(in_array($name, $this->_keys)){
			// set scalar value
			//$this->checkCaller("set");
			$this->_data[$name] = $value;
		}elseif(in_array($name, $this->_arykeys)){
			// set array
			$this->_data[$name][] = $value;
		}elseif(in_array($name, $this->_subkeys)){
			// set subarray value
			$this->_data['subkeys'][$name] = $value;
		}else{
			trigger_error("Cannot set '$name'.  It is not a valid _DATA property. ");
		}
	}

	private function checkCaller($action){
		$stack = debug_backtrace();
		$callerfile = $stack[1]['file'];
		if(strpos($callerfile, "_core") === false){
			die("Calling Data::$action in $callerfile not allowed!");
		}
	}
}

?>