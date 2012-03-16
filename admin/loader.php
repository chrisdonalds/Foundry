<?php
// ---------------------------
//
// INIT LOADER
// - For use by any foreign or plugin code
//   that requires loading of Foundry Admin
//
// ---------------------------
define("VALID_LOAD", true);
define("VHOST", substr(str_replace("\\", "/", realpath(dirname(__FILE__)."/../")), strlen(realpath($_SERVER['DOCUMENT_ROOT'])))."/");
include ($_SERVER['DOCUMENT_ROOT'].VHOST."admin/inc/_core/getinc.php");					// required - starts PHP incls!!!

if(isset($url)){
	$path = dirname($url);
	chdir("../../");
	include($url);
	exit;
}

// $_GET values
// Call system functions dynamically
$func_caller = getRequestVar('f');
$func_to_call = getRequestVar('fc');
$func_params = getRequestVar('fp');       // val,val2
if($func_to_call != ''){
    if(function_exists($func_to_call)) {
        if($func_params != ''){
            $func_params = urldecode($func_params);
            $func_param_arry = explode(",", $func_params);
            call_user_func_array($func_to_call, $func_param_arry);
        }else{
            call_user_func($func_to_call);
        }
    }else{
        if($func_caller != '') $func_caller = "called from '$func_caller' ";
        die("Init: Function named '$func_to_call' {$func_caller}does not exist.");
    }
}

// If script did not end in above function call, continue with rest of script page
?>
