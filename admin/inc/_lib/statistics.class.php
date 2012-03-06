<?php

/* STATISTICS GLOBAL CLASSES */

/**
 * STATSCLASS
 * Keeps track of statistical information
 * ie.: referrers, ips, hits, counters, pageviews
 * -
 */
class StatsClass {
	// overloaded data
	private $_system = array();
	private $_keys = array("");
	private $_arykeys = array();
	private $_subkeys = array();
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
        foreach($this->_keys as $k) print "[$k] = ".getIfSet($this->_system[$k])."<br>";
        foreach($this->_arykeys as $k)
            if(isset($this->_system[$k])) print "[$k] = <pre>".printr($this->_system[$k])."</pre><br/>";
        foreach($this->_subkeys as $k){
            if(isset($this->_system['subkeys'][$k])) {
                print "[$k] = ";
                if(is_array($this->_system['subkeys'][$k]))
                    print "<pre>".print_r($this->_system['subkeys'][$k], true)."</pre><br/>";
                else
                    print $this->_system['subkeys'][$k]."<br/>";
            }
        }
	}

	public function __get($name){
		if(in_array($name, $this->_keys)){
			// return scalar value
			return getIfSet($this->_system[$name]);
		}elseif(in_array($name, $this->_arykeys)){
			// return array
			return getIfSet($this->_system[$name]);
		}elseif(in_array($name, $this->_subkeys)){
			// return subarray value
			return getIfSet($this->_system['subkeys'][$name]);
		}else{
			addErrorMsg("Cannot get '$name'.  It is not a valid _STATISTICS property. ", CORE_ERR);
		}
	}

	public function __set($name, $value){
		if(in_array($name, $this->_keys)){
			// set scalar value
			$this->checkCaller("set");
			$this->_system[$name] = $value;
		}elseif(in_array($name, $this->_arykeys)){
			// set array
			$this->_system[$name][] = $value;
		}elseif(in_array($name, $this->_subkeys)){
			// set subarray value
			$this->_system['subkeys'][$name] = $value;
		}else{
			addErrorMsg("Cannot set '$name'.  It is not a valid _STATISTICS property. ", CORE_ERR);
		}
	}

	private function checkCaller($action){
		$stack = debug_backtrace();
		$callerfile = $stack[1]['file'];
		if(strpos($callerfile, "_core") === false){
			die("Calling Statistics::$action in $callerfile not allowed!");
		}
	}
}
?>