<?php
// ---------------------------
//
// FOUNDRY COMMON FUNCTIONS
//  - Error & Debugger Handling
//
// ---------------------------
//
// list of tool functions
// - Uses: MySQL 4.2+

define ("ERRLOADED", true);

if(!defined("VALID_LOAD")) die ("This file cannot be accessed directly!");

// turn off debugger engine
$GLOBALS['debugger'] = false;
$GLOBALS['debugger_on_error'] = false;

// error detection trapping level (set in configs.php)
// - Standard = E_ERROR | E_WARNING | E_PARSE
// - Strict = E_ALL
error_reporting(ERROR_SENSITIVITY);
if(floatval(phpversion()) >= 5) set_error_handler("customErrorHandler");

define ("STAT_ERR", 1);
define ("CORE_ERR", 2);
define ("RUNTIME_ERR", 4);
define ("DEBUGGER_ERR", 8);

// ----------- DEBUGGER FUNCTIONS ---------------

/**
 * Toggle or set the debugger engine
 * @param boolean $set
 */
function debugger($set = null){
	if(ALLOW_DEBUGGING) {
		if(is_null($set)) $set = !$GLOBALS['debugger'];
		$GLOBALS['debugger'] = $set;
	}
}

/**
 * Toggle or set the debugger-on-error switch
 * @param boolean $set
 */
function debugger_on_error($set = null){
	if(ALLOW_DEBUGGING) {
		if(is_null($set)) $set = !$GLOBALS['debugger_on_error'];
		$GLOBALS['debugger_on_error'] = $set;
	}
}

/**
 * Return state of debugger
 * @return boolean
 */
function isDebugging(){
	return $GLOBALS['debugger'];
}

/**
 * HTML formatted print_r
 * @param array $array
 * @param boolean $return Set to true to return instead of echoing output
 */
function printr($array, $return = false){
	if(is_array($array) || is_object($array)){
		if($return){
            return print_r($array, true);
        }else{
            print "<pre>".print_r($array, true)."</pre>";
        }
	}
}

/**
 * Debugger-aware echo
 * @param mixed $text
 * @param boolean $is_error_msg
 */
function _e($text, $is_error_msg = false){
    if($GLOBALS['debugger'] || ($GLOBALS['debugger_on_error'] && $is_error_msg)) {
    	echo $text."<br/>";
    	_sendErrorTo($text);
    }
}

/**
 * Debugger-aware printr
 * @param array $array
 */
function _pr($array){
    if($GLOBALS['debugger'] && is_array($array)) {
    	printr($array);
    	_sendErrorTo(print_r($array, true));
    }
}

/**
 * Debugger-aware var_dump
 * @param mixed $obj
 */
function _vd($obj){
    if($GLOBALS['debugger']) {
    	var_dump($obj);
    }
}

/**
 * Debugger-aware ReflectionClass getStatic
 */
function _rc_getstatic(){
    if($GLOBALS['debugger']){
        $reflection = new ReflectionClass('Static');
        printr($reflection->getStaticProperties());
    }
}

/**
 * Debugger-aware ReflectionClass getProperties
 * @param str $class
 */
function _rc_getprops($class){
    if($GLOBALS['debugger']){
        $reflect = new ReflectionClass($class);
        $props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        foreach ($props as $prop) {
            print $prop->getName()."\n";
        }
    }
}

/**
 * Debugger-aware ReflectionClass getMethods
 * @param str $class
 */
function _rc_getmethods($class){
    if($GLOBALS['debugger']){
        $reflect = new ReflectionClass($class);
        $methods = $reflect->getMethods();
        printr($methods);
    }
}

/**
 * Debugger-aware dbError output
 * @return mixed dbError
 */
function _db_error(){
    if($GLOBALS['debugger']) {
    	_sendErrorTo(dbError());
        return dbError();
    }
}

function _sendErrorTo($message){
	if($message != ''){
		switch (ERROR_LOG_TYPE){
			case 0:
				error_log($message, 0);
				break;
			case 1:
				if(ERROR_SEND_TO_EMAIL != ''){
					@error_log($message, 1, ERROR_SEND_TO_EMAIL);
				}
				break;
			case 3:
				if(ERROR_SEND_TO_FILE != ''){
					@error_log($message, 3, ERROR_SEND_TO_FILE);
				}
				break;
		}
	}
	error_log($message);
}

// ----------- ERROR-HANDLER FUNCTIONS ---------------

/**
 * Custom error handler
 * @param integer $errno
 * @param string $errstr
 * @param string $errfile
 * @param integer $errline
 * @return boolean
 */
function customErrorHandler($errno, $errstr, $errfile, $errline){
	global $_err;

	echo "<div style=\"clear: both; position: relative; border: 1px solid black; background: white; padding: 2px;\">\n";
    switch ($errno) {
        case E_COMPILE_ERROR:
        case E_RECOVERABLE_ERROR:
		case E_USER_ERROR:
        case E_STRICT:
		case E_ERROR:
			echo "<b>CRITICAL ERROR</b> [$errno] $errstr. ";
			echo "  Critical error on line $errline in file $errfile\n";
			echo "Aborting...<br/>\n";
			exit(1);
			break;

		case E_USER_WARNING:
		case E_WARNING:
			echo "<b>WARNING</b> [$errno] $errstr. ";
			echo "  Warning on line $errline in file $errfile<br/>\n";
			break;

		case E_USER_NOTICE:
		case E_NOTICE:
			echo "<b>NOTICE</b> [$errno] $errstr. ";
			echo "  Notice on line $errline in file $errfile<br/>\n";
			break;

		default:
			echo "<b>UNKNOWN error type</b>: [$errno] $errstr. ";
			echo "  Error on line $errline in file $errfile<br/>\n";
			break;
	}
	echo "</div>\n";

	/* Don't execute PHP's internal error handler */
    return true;
}

/**
 * Initialize error log
 *  - index 0 = record handling status errors (STAT_ERR)
 *  - index 1 = core errors (CORE_ERR)
 *  - index 2 = runtime errors (RUNTIME_ERR)
 *  - index 4 = debugger messages (DEBUGGER_ERR)
 * @global array $_err
 */
function initErrorMsg(){
	global $_err;

	if(!isset($_err)) $_err = array();
	$_err[STAT_ERR] = array();		// reserved for status message
	if(!isset($_err[RUNTIME_ERR])) $_err[RUNTIME_ERR] = array();
	if(!isset($_err[CORE_ERR])) $_err[CORE_ERR] = array();
	if(!isset($_err[DEBUGGER_ERR])) $_err[DEBUGGER_ERR] = array();
}

/**
 * Add a status message to error log
 * @param string $msg
 */
function addErrorStatMsg($msg){
	global $_err;

	if($msg != '')
		$_err[STAT_ERR][] = $msg;
}

/**
 * Add a message to error log
 * @param string $msg
 * @param integer $err_grp
 * @global array $_err
 */
function addErrorMsg($msg, $err_grp = RUNTIME_ERR){
	global $_err;

	if(in_array($err_grp, array(CORE_ERR, STAT_ERR, RUNTIME_ERR, DEBUGGER_ERR)))
		$_err[$err_grp][] = $msg;
}

/**
 * Remove a message from error log
 * @global array $_err
 * @param integer $num
 * @param integer $err_grp STAT_ERR, RUNTIME_ERR, CORE_ERR, DEBUGGER_ERR
 */
function removeErrorMsg($num, $err_grp = RUNTIME_ERR){
	global $_err;

	if($num > 0)
		unset($_err[$err_grp][$num]);
}

/**
 * Return if specific status message exists
 * @global array $_err
 * @param message $msg
 * @return boolean
 */
function getErrorStatMsg($msg){
	global $_err;

	return (array_search($msg, $_err[STAT_ERR]) !== false);
}

/**
 * Return if specific message exists
 * @global array $_err
 * @param string $msg
 * @param integer $err_grp STAT_ERR, RUNTIME_ERR, CORE_ERR, DEBUGGER_ERR
 * @return boolean
 */
function getErrorMsg($msg, $err_grp = RUNTIME_ERR){
	global $_err;

	if(in_array($err_grp, array(CORE_ERR, STAT_ERR, RUNTIME_ERR, DEBUGGER_ERR))){
		return (array_search($msg, $_err[$err_grp]) !== false);
	}else{
		return false;
	}
}

/**
 * Return if any message exists
 * @global array $_err
 * @param integer $err_grp STAT_ERR, RUNTIME_ERR, CORE_ERR, DEBUGGER_ERR
 * @return boolean
 */
function errorMsgExists($err_grp = RUNTIME_ERR){
	global $_err;

	$errfound = 0;
	$errs = array(CORE_ERR, STAT_ERR, RUNTIME_ERR, DEBUGGER_ERR);
	foreach($errs as $err_num){
		if(($err_grp & $err_num) > 0){
			$errfound += count($_err[$err_num]);
		}
	}
	return ($errfound > 0);
}

/**
 * Return the JS alert function with the error messages
 * @global array $_err
 * @return string
 */
function prepErrorAlert($extra_msg = '', $err_grp = 5){
	global $_err;

	if(errorMsgExists()){
		if($err_grp == RUNTIME_ERR + STAT_ERR) {
			$e = $_err[RUNTIME_ERR] + $_err[STAT_ERR];
		}else{
			$e = $_err[$err_grp];
		}
		$msg = join("\" + '\\n' + \"", $e);
		if($extra_msg != '') $msg .= "\" + '\\n' + \"".$extra_msg;
		return "alert(\"".$msg."\"); ";
	}else{
		return "";
	}
}

/**
 * Output JS block with alert message
 * @param string $alert
 */
function showErrorAlertScript($alert){
	echo "<script type=\"text/Javascript\">{$alert}</script>\n";
}

/**
 * Output error div with error message
 * @global array $_err
 * @param integer $err_grp STAT_ERR, RUNTIME_ERR, CORE_ERR, DEBUGGER_ERR
 */
function showErrorMsg($err_grp = STAT_ERR){
	global $_err;

	if($err_grp == STAT_ERR && errorMsgExists($err_grp)){
		// output status message div since this type of msg occurs after POST
		// and can just be sent to the output
		$msg = join("<br/>", $_err[STAT_ERR]);
		echo "<div class=\"display_msg\">{$msg}</div>\n";
		return;
	}
	if(($err_grp & CORE_ERR) > 0 && errorMsgExists(CORE_ERR)){
		// use jQuery to output the error message(s) in the div
		$msg = str_replace("'", "&#39;", join("<br/>", $_err[CORE_ERR]));
		echo "	jQuery(\"#display_core_msg\").html('{$msg}').css('display', 'block');\n";
		return;
	}
	if( (($err_grp & DEBUGGER_ERR) > 0 && errorMsgExists(DEBUGGER_ERR))
		|| (($err_grp & RUNTIME_ERR) > 0 && errorMsgExists(RUNTIME_ERR)) ){
		// use jQuery to output the error message(s) in the div
		if($GLOBALS['debugger']) $msg = str_replace("'", "&#39;", join("<br/>", $_err[DEBUGGER_ERR]));
		$msg.= (($msg != '') ? '<br/>' : '').str_replace("'", "&#39;", join("<br/>", $_err[RUNTIME_ERR]));
		echo "	jQuery(\"#display_runtime_msg\").html('{$msg}').css('display', 'block');\n";
	}
}
?>