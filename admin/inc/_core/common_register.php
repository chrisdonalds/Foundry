<?php
// ---------------------------
//
// FOUNDRY COMMON FUNCTIONS
//  - Register Support -
//
// ---------------------------
//
// list of tool functions
// - Uses: MySQL 4.2+

define ("REGISTERLOADED", true);
if(!defined("VALID_LOAD")) die ("This file cannot be accessed directly!");

// Trigger functions
define("TF_LISTACTION", "listaction");
define("TF_CONTENTMACRO", "contentmacro");

// Data Aliases meta codes
define("DA_CATEGORY", "@category@");
define("DA_CATEGORIES", "@categories@");
define("DA_CODE", "@code@");
define("DA_ID", "@id@");
define("DA_CAT_ID", "@catid@");
define("DA_TABLE", "@table@");
define("DA_YEAR", "@year@");
define("DA_MONTH", "@month@");
define("DA_DAY", "@day@");
define("DA_HOUR", "@hour@");
define("DA_MINUTE", "@minute@");
define("DA_SECOND", "@second@");
define("DA_ACTYEAR", "@ayear@");
define("DA_ACTMONTH", "@amonth@");
define("DA_ACTDAY", "@actday@");
define("DA_ACTHOUR", "@acthour@");
define("DA_ACTMINUTE", "@actminute@");
define("DA_ACTSECOND", "@actsecond@");
define("DA_STARTYEAR", "@startyear@");
define("DA_STARTMONTH", "@startmonth@");
define("DA_STARTDAY", "@startday@");

/**
 * Loads a triggered function into the register
 * and queues it for execution.  A trigger function
 * can be called during either a server-side operation
 * or an AJAX process.  Note that an exit or die in
 * AJAX-initiated triggers have no effect on client-side
 * scripts.
 * @param string $type          The type of function
 * @param string $func          The function to execute
 * @param string $triggercode   The unique code that idenifies the function to be executed
 * @param array $args           'first'... true to push function to top of call stack for this trigger
 * @return boolean
 */
function queueFunction($type, $func, $triggercode, $args = ''){
	if($func != '' && $triggercode != '' && $type != ''){
		if(function_exists($func) && is_callable($func)){
            if(is_array($args) || isblank($args)){
                $fileurl = $_SERVER['SCRIPT_NAME'];
                $j_args = json_encode($args);
                if(in_array($type, array(TF_LISTACTION))){
                    // single function triggers (i.e. first arg does not apply)
                    return replaceRec("register",
                                    "`type` = '$type', `fileurl` = '$fileurl', `function` = '$func', `trigger` = '', `parameters` = '$j_args', `priority` = '1'",
                                    "`type` = '$type' AND `fileurl` = '$fileurl'");
                }elseif(in_array($type, array(TF_CONTENTMACRO))){
                    // content macro trigger
                    return replaceRec("register",
                                    "`type` = '$type', `fileurl` = '', `function` = '$func', `trigger` = '$triggercode', `parameters` = '$j_args', `priority` = '1'",
                                    "`type` = '$type' AND `trigger` = '$triggercode'");
                }else{
                    // multiple function triggers
                    if(isset($args['first'])){
                        // push priority of all other functions of trigger down one
                        updateRec("register", "`priority` = `priority` + 1", "`type` = '$type' AND `fileurl` = '$fileurl' AND `trigger` = '$triggercode' AND `function` != ''");
                        $priority = 0;
                    }else{
                        // get next priority
                        $priority = getLastID("register", "priority", "`type` = '$type' AND `fileurl` = '$fileurl' AND `trigger` = '$triggercode' AND `function` != ''");
                    }
                    $priority++;
                    return replaceRec("register",
                                    "`type` = '$type', `fileurl` = '$fileurl', `function` = '$func', `trigger` = '$triggercode', `parameters` = '$j_args', `priority` = '$priority'",
                                    "`type` = '$type' AND `function` = '$func' AND `fileurl` = '$fileurl' AND `trigger` = '$triggercode'");
                }
            }else{
                trigger_error("Only array or null value for args parameter for ".__FUNCTION__);
            }
		}else{
			trigger_error("Cannot queue function '$func' because it does not exist or is not callable.");
		}
	}else{
		trigger_error("No function to queue.");
	}
}

/**
 * Unload a triggered function from the register
 * @param string $type
 * @param string $func
 * @return boolean
 */
function dequeueFunction($type, $func){
	if($func != '' && $type != ''){
		//$fileurl = $_SERVER['SCRIPT_NAME'];
		return deleteRec("register", "`type` = '$type' AND `function` = '$func'");
	}else{
		trigger_error("Function name and/or type missing for ".__FUNCTION__);
	}
}

/**
 * Temporarily deactivate a triggered function in the register
 * @param string $type
 * @param string $func
 * @return boolean
 */
function suspendFunction($type, $func){
	if($func != '' && $type != ''){
		return updateRec("register", "`active` = 0", "`type` = '$type' AND `function` = '$func'");
	}else{
		trigger_error("Function name and/or type missing for ".__FUNCTION__);
	}
}

/**
 * Reactivate a suspended triggered function in the register
 * @param string $type
 * @param string $func
 * @return boolean
 */
function resumeFunction($type, $func){
	if($func != '' && $type != ''){
		return updateRec("register", "`active` = 1", "`type` = '$type' AND `function` = '$func'");
	}else{
		trigger_error("Function name and/or type missing for ".__FUNCTION__);
	}
}

/**
 * Unload all registered trigger functions
 * @param array $types  List of trigger types to dequeue
 */
function dequeueAllFunctions($types){
    if(is_array($types)){
        if(count($types) > 0){
            foreach($types as $type){
                deleteRec("register", "`type` = '$type'");
            }
        }else{
            trigger_error("List of types is empty for ".__FUNCTION__);
        }
    }else{
		trigger_error("List of types missing for ".__FUNCTION__);
    }
}

/**
 * Retrieve an array containing function data (function, trigger, parameters (JSON), and priority)
 * related to trigger command
 * @param string $type          Type of trigger
 * @param string $triggercode   The unique function code
 * @param integer $active
 */
function getQueuedFunctions($type, $triggercode = '', $active = true){
	$retn = array();
    if($type != ''){
        $fileurl = $_SERVER['SCRIPT_NAME'];
        $rec = getRec("register", "`function`, `parameters` as args, `priority`", "`type` = '$type' AND function != '' AND `active` = '".(($active) ? 1 : 0)."'".(($triggercode != '') ? " AND `trigger` = '$triggercode'" : ""), "function", "");
        if(count($rec) > 0){
            foreach($rec as $row){
                $row['args'] = json_decode($row['args']);
                $retn[] = $row;
            }
        }
    }
	return $retn;
}

/**
 * Core: Process a trigger function
 * @param string $_type The type of trigger
 * @param string $_triggercode The triggercode that is unique to the call function
 * @param array $_params List of attributes in array form that will be passed to function
 * @param string $_callfile Required if $_triggercode is blank
 */
function executeTriggerFunction($_type, $_triggercode, $_params, $_callfile = ''){
	$retn = null;
	$do_std_process = true;

    // call a user-registered trigger function to handle the process
    // this allows developers to create custom handlers for the trigger
    if($_callfile != ''){
        // trigger is made unique by the callfile (i.e. this trigger can only be registered once per file)
        $_callfile = urldecode($_callfile);
        $_rec = getRec("register", "*", "`type` = '$_type' AND `fileurl` = '$_callfile' AND `active` = 1", "", "1", "", true);
        $_args = json_decode(getIfSet($_rec['parameters']), true);
        $_function = getIfSet($_rec['function']);

        $filepath = SITE_PATH.((VHOST != DIRECTORY_SEPARATOR) ? str_replace(VHOST, "", $_callfile) : $_callfile);
        if(file_exists($filepath) && $_function != ''){
            // is the flag 'allow_std_process' part of the function's parameters?
            // if so, developer wants this function to allow the normal process to continue
            $func_allows_std_process = false;
            if(isset($_args['allow_std_process']) && (intval($_args['allow_std_process']) == 1 || $_args['allow_std_process'] == true)){
                $func_allows_std_process = true;
                unset($_args['allow_std_process']);
            }

            // include function parameters (if provided) with params from script_form js file
            if(is_array($_args)) $_params = array_merge($_params, $_args);

            // inherit global variables in preparation for the included file
            foreach($GLOBALS as $key => $val){
                if(!in_array($key, array('GLOBALS', '_SERVER', '_COOKIE', '_SESSION', '_GET', '_POST', '_FILES', '_REQUEST', '_ENV'))){
                    $$key = $val;
                }
            }

            // buffer the output of the function file
            chdir(dirname($filepath));
            ob_start();
            include($filepath);
            ob_end_clean();			// scrap the output so it doesn't pollute the process

            if(function_exists($_function)){
                // call the triggered function and get the result
                // param may be loaded with additional args
                $retn = call_user_func_array($_function, $_params);
                if(!$func_allows_std_process) $do_std_process = false;

                if($retn !== false){
                    // echo the result of the function call (if applicable)
                    if(!in_array($_type, array(TF_LISTACTION))) echo $retn;
                }
            }
        }
    }elseif($_triggercode != ''){
        // trigger is made unique by the function (i.e. this trigger can be registered more than once)
        $_rec = getRec("register", "*", "`type` = '$_type' AND `trigger` = '$_triggercode' AND `active` = 1", "", "1", "", true);
        $_args = json_decode(getIfSet($_rec['parameters']), true);
        $_function = getIfSet($_rec['function']);

        // is the flag 'allow_std_process' part of the function's parameters?
        // if so, developer wants this function to allow the normal process to continue
        $func_allows_std_process = false;
        if(isset($_args['allow_std_process']) && (intval($_args['allow_std_process']) == 1 || $_args['allow_std_process'] == true)){
            $func_allows_std_process = true;
            unset($_args['allow_std_process']);
        }

        // include function parameters (if provided) with params from script_form js file
        if(is_array($_args)) $_params = array_merge($_params, $_args);

        // inherit global variables in preparation for the included file
        foreach($GLOBALS as $key => $val){
            if(!in_array($key, array('GLOBALS', '_SERVER', '_COOKIE', '_SESSION', '_GET', '_POST', '_FILES', '_REQUEST', '_ENV'))){
                $$key = $val;
            }
        }

        // because the callfile is not required, the function must have been included
        // during normal loading (i.e. in a plugin or app)
        if(function_exists($_function)){
            // call the triggered function and get the result
            // param may be loaded with additional args
            $retn = call_user_func($_function, $_params);
            if(!$func_allows_std_process) $do_std_process = false;

            if($retn !== false){
                // echo the result of the function call (if applicable)
                //if(in_array($_triggercode, array(TF_CONTENTMACRO))) echo $retn;
            }
        }
    }else{
        trigger_error("Trigger code or callfile missing for ".__FUNCTION__);
    }
	return array($do_std_process, $retn);
}

/**
 * Add a content macro function to the register
 * @param string $function  The trigger function to register
 * @param string $code      The unique code that identifies the function
 * @param array $args       A list of arguments to pass to function
 * @param integer $priority Priority of execution (1=first...)
 * @return boolean
 */
function queueMacro($function, $code = '', $args = '', $priority = 1){
    if($code == '') $code = $function;
    return queueFunction(TF_CONTENTMACRO, $function, $code, $args);
}

/**
 * Remove a content macro function from the register
 * @param string $function
 * @return boolean
 */
function dequeueMacro($function){
    return dequeueFunction(TF_CONTENTMACRO, $function);
}

/**
 * Execute a registered content macro function
 * @param string $function
 * @param string $args
 * @return string Either the result of the call or <!-- CONTENTMACRO: ... --> if failed
 */
function executeMacro($triggercode, $args){
    // function and args are derived originally from {function name=value, name=value,...}
    // where args = parsed set of name/value pairs
    if($triggercode != ''){
        list($continue, $retn) = executeTriggerFunction(TF_CONTENTMACRO, $triggercode, $args);
        return (($retn !== false) ? $retn : '<!-- CONTENTMACRO: ERR -->');
    }
    return "<!-- CONTENTMACRO: FUNC MISSING -->";
}

/**
 * Return either the entire data alias structure or data alias set for a single table
 * @param string $table [optional] If provided, only the data alias set for this table will be returned
 * @return array data alias set
 */
function getDataAliases($table = ""){
	$aliases = array();
    if(DB_USED){
        $data_tables = getDataTables();
        $table = str_replace(DB_TABLE_PREFIX, "", $table);
        $crit = "`type` = 'dataalias' AND `parameters` != '' AND `parameters` IS NOT NULL";
        if($table != '' && in_array(DB_TABLE_PREFIX.$table, $data_tables)) $crit .= " AND `db_table` = '".$table."'";

        $rec = getRec("register", "`fileurl` as meta, `db_table`, `db_child_table`, `parameters`", $crit, "`db_table`");
        if(is_array($rec)){
            foreach($rec as $row){
                $meta_parts = explode("/", ltrim(strtolower($row['meta']), "/"));
                $row['parameters'] = json_decode($row['parameters'], true);
                list($row['pattern'], $row['error']) = getDataAliasPatternFromMeta($row['meta']);
                $row['iscategory'] = dataAliasIsCategory($row['meta']);
                $aliases[$meta_parts[0]] = $row;
            }
        }
    }
	return $aliases;
}

/**
 * Convert Data Alias meta data (/events/@year@/@month@/@day@/@code@) to
 * pattern (/events/(\d{4})/(\d{2})/(\d{2})/([^\/]+)$).
 * Meta data -- the human-readable version of the pattern regex (table/literal/@meta-tags@)
 * Pattern data -- the actual regex used to parse urls
 * @param string $meta
 */
function getDataAliasPatternFromMeta($meta){
	$pattern = '';
    $error = '';
	if($meta != ''){
		// break meta up by directory separator (/)
		$meta_parts = explode("/", ltrim(strtolower($meta), "/"));
		$patt_parts = array();
        $meta_started = false;
		foreach($meta_parts as $meta_piece){
            switch($meta_piece){
                case DA_CATEGORY:       // single category
                case DA_CODE:
                    $patt_parts[] = "([^/]+)";
                    $meta_started = true;
                    break;
                case DA_CATEGORIES:
                    $patt_parts[] = "(.*)";
                    $meta_started = true;
                    break;
                case DA_CAT_ID:
                case DA_ID:
                    $patt_parts[] = "(\d)";
                    $meta_started = true;
                    break;
                case DA_MONTH:
                case DA_DAY:
                case DA_HOUR:
                case DA_MINUTE:
                case DA_SECOND:
                case DA_ACTMONTH:
                case DA_ACTDAY:
                case DA_ACTHOUR:
                case DA_ACTMINUTE:
                case DA_ACTSECOND:
                case DA_STARTMONTH:
                case DA_STARTDAY:
                    $patt_parts[] = "(\d{2})";
                    $meta_started = true;
                    break;
                case DA_YEAR:
                case DA_ACTYEAR:
                case DA_STARTYEAR:
                    $patt_parts[] = "(\d{4})";
                    $meta_started = true;
                    break;
                default:
                    if(!$meta_started){
                        $patt_parts[] = $meta_piece;
                    }else{
                        if($error == ''){
                            $error = 'Error at \'/'.substr($meta_piece, 0, 5).'\'';
                        }
                    }
                    break;
			}
		}
		$pattern = "/".join("/", $patt_parts)."$";
	}
	return array($pattern, $error);
}

/**
 * Return whether a Data Alias (represented by its meta data) is for category data
 * or record data
 * @param string $meta
 */
function dataAliasIsCategory($meta){
	// a category meta has @category@/@categories@ as the first meta-tag (no matter
    // how many literal segments are found ahead of it)
	$meta_parts = explode("/", ltrim(strtolower($meta), "/"));
    $cat_tag_found = false;
    $non_cat_tag_found = false;
    foreach($meta_parts as $indx => $meta_piece){
        if(($meta_piece == DA_CATEGORY || $meta_piece == DA_CATEGORIES) && !$cat_tag_found){
            // @category@ or @categories@ tag found for the first time
            $cat_tag_found = true;
        }elseif(preg_match("/@([^@]+)@/i", $meta_piece)){
            // anything else found ensures this meta is not
            // able to properly parse a category URI
            $non_cat_tag_found = true;
        }
    }
	return ($cat_tag_found && !$non_cat_tag_found);
}

/**
 * Save meta data for specific table
 * @param string $table
 * @param string $meta
 */
function updateDataAliasMeta($table, $meta){
    $param = '';
    if($meta != ''){
        //if(dataAliasIsCategory($meta)){
            //$param = json_encode(array('cc' => '$1'));
        //}else{
            $d = '';
            $ad = '';
            $t = '';
            $at = '';
            $sd = '';
            $arry = array();
            $meta_parts = explode("/", ltrim($meta, "/"));
            foreach($meta_parts as $indx => $part){
                if($indx == 0) continue;
                switch($part){
                    case DA_CATEGORY:
                        $arry['cc'] = '$'.$indx;
                        break;
                    case DA_CATEGORIES:
                        $arry['mc'] = '$'.$indx;
                        break;
                    case DA_ID:
                    case DA_CAT_ID:
                        $arry['id'] = '$'.$indx;
                        break;
                    case DA_CODE:
                        $arry['c'] = '$'.$indx;
                        break;
                    case DA_YEAR:
                    case DA_MONTH:
                    case DA_DAY:
                        if($d != '') $d .= '-';
                        $d .= '$'.$indx;
                        break;
                    case DA_HOUR:
                    case DA_MINUTE:
                    case DA_SECOND:
                        if($t != '') $t .= ':';
                        $t .= '$'.$indx;
                        break;
                    case DA_ACTYEAR:
                    case DA_ACTMONTH:
                    case DA_ACTDAY:
                        if($ad != '') $ad .= '-';
                        $ad .= '$'.$indx;
                        break;
                    case DA_ACTHOUR:
                    case DA_ACTMINUTE:
                    case DA_ACTSECOND:
                        if($at != '') $at .= ':';
                        $at .= '$'.$indx;
                        break;
                    case DA_STARTYEAR:
                    case DA_STARTMONTH:
                    case DA_STARTDAY:
                        if($sd != '') $sd .= '-';
                        $sd .= '$'.$indx;
                        break;
                }
            //}
            if($d != '') $arry['d'] = $d;
            if($t != '') $arry['t'] = $t;
            if($ad != '') $arry['ad'] = $ad;
            if($at != '') $arry['at'] = $at;
            if($sd != '') $arry['sd'] = $sd;
            $param = json_encode($arry);
        }
    }
    if($meta != ""){
        if(getRecItem("register", "id", "`db_table` = '$table' AND `type` = 'dataalias'") != ''){
            updateRec("register", "`fileurl` = '$meta', `parameters` = '$param'", "`db_table` = '$table' AND `type` = 'dataalias'");
        }else{
            insertRec("register", "`fileurl`, `type`, `db_table`, `parameters`", "'$meta', 'dataalias', '$table', '$param'");
        }
    }else{
        deleteRec("register", "`db_table` = '$table'");
    }
}

/**
 * Populate the data class with a recordset
 * @param array $rec
 * @param boolean $newclass True to instantiate a new class thus leaving $_data unchanged
 * @return array
 */
function setupData($rec, $newclass = false){
    global $_data;

    if($newclass){
        $d = DataClass::init();
    }else{
        $d = $_data;
    }

    $d->dbrec = $rec;
    if(isset($rec['id'])){
        $d->id = $rec['id'];
    }elseif(isset($rec[0]['id']) && count($rec) == 1){
        $d->id = $rec[0]['id'];
    }else{
        $d->id = 0;
    }
    if(isset($rec['published']) == 1){
        $d->ispublished = true;
    }elseif(isset($rec[0]['published']) && count($rec) == 1){
        $d->ispublished = true;
    }else{
        $d->ispublished = false;
    }
    if(isset($rec['draft']) == 1){
        $d->isdraft = true;
    }elseif(isset($rec[0]['draft']) && count($rec) == 1){
        $d->isdraft = true;
    }else{
        $d->isdraft = false;
    }
    $d->created = multiarray_search('date_created', $rec, true);
    $d->updated = multiarray_search('date_updated', $rec, true);
    $d->published = multiarray_search('date_published', $rec, true);
    $d->iscategory = (multiarray_search('cat_id', $rec, true) === false);
    $d->numrows = count($rec);
    $d->nonce = '';
    $d->metabase = '';
    $d->table = '--';
    $d->childtable = '--';
    $d->query = '';
    $d->queryvars = array();
    $d->found = true;

    return $d;
}

/**
 * Return whether or not the $_data class contains record data
 * @return boolean
 */
function dataExists(){
    global $_data;

    return (count($_data->dbrec) > 0);
}
?>