<?php

/* SYSTEM GLOBAL CLASSES */

/**
 * SYSTEMCLASS
 * Stores and manages system data
 * - plugins
 * - pluginsincl
 * - pluginsprob
 * - frameworks
 */
class SystemClass {
	// overloaded data
	private $_system = array();
	private $_keys = array( "plugins", "pluginsincl", "pluginsprob", "frameworks", "dataaliases",
							"incl", "currentexecplugin", "get", "post", "filesys"
                            );
	private $_arykeys = array("info");
	private $_subkeys = array("datatables");
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
			addErrorMsg("Cannot get '$name'.  It is not a valid _SYSTEM property. ", CORE_ERR);
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
			addErrorMsg("Cannot set '$name'.  It is not a valid _SYSTEM property. ", CORE_ERR);
		}
	}

	private function checkCaller($action){
		$stack = debug_backtrace();
		$callerfile = $stack[1]['file'];
		if(strpos($callerfile, "_core") === false){
			die("Calling System::$action in $callerfile not allowed!");
		}
	}
}

/**
 * USERSCLASS
 * Stores and manages user data
 * - active
 * - roles
 * - allowances
 */
class UsersClass {
	// overloaded data
	private $_users = array();
	private $_keys = array(	"activelist", "roles", "allowances");
	private $_arykeys = array();
	private $_subkeys = array(	"isloggedin", "logintimestamp",
								"id", "level", "isactivated",
								"username", "firstname", "lastname",
								"email", "twitter", "googleplus",
								"facebook");
	protected static $_instance = null;

	private function __construct() {
	}

	private function __clone(){
	}

	public static function init(){
		$s = new self;
		$s->checkCaller("init");
		$s->_users['roles'] = getConsts('ADMLEVEL_');
		return $s;
	}

	public function show_properties(){
        foreach($this->_keys as $k) print "[$k] = ".getIfSet($this->_users[$k])."<br>";
        foreach($this->_arykeys as $k)
            if(isset($this->_users[$k])) print "[$k] = <pre>".printr($this->_users[$k])."</pre><br/>";
        foreach($this->_subkeys as $k){
            if(isset($this->_users['subkeys'][$k])) {
                print "[$k] = ";
                if(is_array($this->_users['subkeys'][$k]))
                    print "<pre>".print_r($this->_users['subkeys'][$k], true)."</pre><br/>";
                else
                    print $this->_users['subkeys'][$k]."<br/>";
            }
        }
	}

	public function __get($name){
		if(in_array($name, $this->_keys)){
			// return scalar value
			return $this->_users[$name];
		}elseif(in_array($name, $this->_arykeys)){
			// return array
			return $this->_users[$name];
		}elseif(in_array($name, $this->_subkeys)){
			// return subarray value
			return $this->_users['subkeys'][$name];
		}else{
			addErrorMsg("Cannot get '$name'.  It is not a valid _USERS property. ", CORE_ERR);
		}
	}

	public function __set($name, $value){
		if(in_array($name, $this->_keys)){
			// set scalar value
			$this->checkCaller("set");
			$this->_users[$name] = $value;
		}elseif(in_array($name, $this->_arykeys)){
			// set array
			$this->_users[$name][] = $value;
		}elseif(in_array($name, $this->_subkeys)){
			// set subarray value
			$this->_users['subkeys'][$name] = $value;
		}else{
			addErrorMsg("Cannot set '$name'.  It is not a valid _USERS property. ", CORE_ERR);
		}
	}

	private function checkCaller($action){
		$stack = debug_backtrace();
		$callerfile = $stack[1]['file'];
		if(strpos($callerfile, "_core") === false){
			die("Calling Users::$action in $callerfile not allowed!");
		}
	}
}


?>