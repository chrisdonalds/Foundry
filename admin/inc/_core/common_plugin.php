<?php
// ---------------------------
//
// FOUNDRY COMMON FUNCTIONS
//  - Plugin Controller
//
// ---------------------------
//
// list of tool functions
// - Uses: MySQL 4.2+

define ("PLUGINLOADED", true);
define ("PLUGIN_SETTINGS_SAVE", 1);
define ("PLUGIN_SETTINGS_CLOSE", 2);
define ("PLUGIN_FULLDELETE", false);
define ("PLUGIN_CFGERR_NAMEDUP", 1);
define ("PLUGIN_CFGERR_SYSVERBAD", 2);
define ("PLUGIN_CFGERR_USEDINBAD", 4);
define ("PLUGIN_CFGERR_LICENSEBAD", 8);
define ("PLUGIN_CFGERR_SETTINGSFUNC", 16);
define ("PLUGIN_CFGERR_HEADERFUNC", 32);
define ("PLUGIN_CFGERR_INCLDUP", 64);
define ("PLUGIN_CFGERR_INCLBAD", 128);
define ("PLUGIN_CFGERR_NAMENF", 256);
define ("PLUGIN_CFGERR_SYSVERNF", 512);
define ("PLUGIN_CFGERR_USEDINNF", 1024);
define ("PLUGIN_CFGERR_INCLNF", 2048);
define ("PLUGIN_CFGERR_INITFILENF", 4096);
define ("PLUGIN_CFGERR_INITFILEBAD", 8192);

define ("PLUGIN_SETTINGS_SAVETOSTD", "std");
define ("PLUGIN_SETTINGS_SAVETOSYS", "sys");

if(!defined("VALID_LOAD")) die ("This file cannot be accessed directly!");
$jq_lines = array();

/**
 * load plugins and frameworks into system
 */
function initPluginsandFrameworks(){
	global $_system, $incl;

    /* 	usedin = both or usedin = admin/front
        is_framework = 0
     */
    $plugins_array = array();		// all plugins in system
    $pluginsincl_array = array();	// included plugins
    $pluginsprob_array = array();	// problem/deleted plugins
	$incl_zones = ((IN_ADMIN) ? array('admin', 'both') : array('front', 'both'));

    // prepare inclusion verbs list
    $incl = trim($incl);
   	$incl_array = (($incl != '') ? preg_split("/( |\||,|, )/", $incl) : array());
   	$incl_array = array_unique($incl_array);
   	$rec_i = $incl_array;	// used to determine if all included verb were processed

   	// prepare full plugins and included plugins lists
    $rec_p = getRec("plugins", "*", "is_framework = 0", "depends ASC", "");
 	if(count($rec_p) > 0){
        // if plugin is dependent on another non-built-in plugin
        // that is not in inclusion list, add parent to list
 		foreach($rec_p as $the_plugin){
 			$depends = trim($the_plugin['depends']);
            if(!in_array($depends, array('jquery', 'jqueryui')) && $depends != '' && in_array($the_plugin['incl'], $incl_array)){
              	if(!in_array($depends, $incl_array)){
                	$incl_array[] = $depends;
               	}
            }
  		}

        foreach($rec_p as $the_plugin){
        	$incl_verb = $the_plugin['incl'];
           	if($the_plugin['error_code'] == 0 && $the_plugin['is_deleted'] == 0){
           		// save the plugin in the full plugins array
	           	$plugins_array[$the_plugin['incl']] = $the_plugin;
	           	if($the_plugin['active'] == 1 && in_array($the_plugin['usedin'], $incl_zones)){
	           		// plugin is ready (active and in right zone)
           			$incl_key = array_search($incl_verb, $incl_array);
		            if($incl_key !== false && !isset($pluginsincl_array[$incl_verb])){
		            	// plugin hasn't already been included.  get ready to include it
		           		$initfile = $the_plugin['folder'].$the_plugin['initfile'];
		            	if(file_exists($initfile)){
		            		// file found. save plugin in the included plugins array
			                if(include ($initfile)){
				                $pluginsincl_array[$incl_verb] = $the_plugin;
				                unset($rec_i[$incl_key]);
			                }
		            	}
		            }
	           	}
           	}else{
				$pluginsprob_array[$incl_verb] = $the_plugin;
           	}
        }
  	}
    if(count($rec_i) > 0) addErrorMsg("Plugin inclusion problem: INCL verb".((count($rec_i) > 1) ? "s" : "")." '".join(", ", $rec_i)."' could not initiate desired plugin".((count($rec_i) > 1) ? "s" : "").".", CORE_ERR);
  	ksort($plugins_array);

  	// prepare frameworks list
  	$frameworks_array = array();
    $rec_f = getRec("plugins", "*", "is_framework = 1", "depends ASC", "");
    if(count($rec_f) > 0){
	    foreach($rec_f as $the_fw){
	    	$frameworks_array[$the_fw['incl']] = $the_fw;
	    }
	  	ksort($frameworks_array);
    }

  	// register system properies
  	$_system->plugins = $plugins_array;
  	$_system->pluginsincl = $pluginsincl_array;
  	$_system->pluginsprob = $pluginsprob_array;
  	$_system->frameworks = $frameworks_array;
  	$_system->incl = $incl_array;
}

/**
 * Return jQuery version number
 * @return string
 */
function getJQueryVer(){
    return getRecItem("`plugins`", "ver", "`incl` = 'jquery'");
}

/**
 * Return jQuery UI Core version number
 * @return string
 */
function getJQueryUIVer(){
    return getRecItem("`plugins`", "ver", "`incl` = 'jqueryui'");
}

/**
 * Return jQuery version number
 * @return string
 */
function getCKEditorVer(){
    return getRecItem("`plugins`", "ver", "`incl` = 'ckeditor'");
}

// ----------- PLUGIN SETTINGS FUNCTIONS ---------------

/**
 * Load plugin settings found in plugin.info files into database.
 * This function does not make the plugins ready for use.  It
 * processes any new or updated plugins.  Call prepHeaderPluginsBlock
 * or addHeadPlugin to put plugins into use.
 * @param str $plugin_status
 * @see addHeadPlugin
 * @version 3.6
 */
function getInstalledPlugins($status = "all"){
	global $settings_issues, $pluginslist, $pluginpaths, $_users;

	if(!userIsAllowedTo('install_plugins') || !userIsAllowedTo('update_plugins'))
		return false;

	$settings_issues['plugins'] = array();
	$settings_issues['plugins-info'] = array();

	$updflag = rand(0, 99999);
	$numIns = 0;
	$numUpd = 0;
	$numDel = 0;

	$pluginpaths = array();
	readInstalledPluginFolders(ADMIN_FOLDER.PLUGINS_FOLDER);

	$pluginslist = array();
	readInstalledPluginInfoFiles($pluginpaths);

	// add built-in plugins
	$pluginslist = array('ckeditor' => array(
							'id' => getRecItem("plugins", "id", "name='CKEditor'"),
							'name' => 'CKEditor', 'author' => 'Frederico Knabben', 'created' => BLANK_DATE,
							'revised' => BLANK_DATE, 'descr' => 'CMS HTML editor', 'sysver' => 1.5,
							'website' => 'http://www.ckeditor.com', 'usedin' => 'both', 'incl' => 'ckeditor', 'initfile' => 'jquery.min.js',
							'headerfunc' => '', 'settingsfunc' => '', 'depends' => 'jquery', 'is_framework' => 0,
							'nodelete' => 1, 'nodisable' => 1, 'builtin' => 1, 'active' => 1
					)) + $pluginslist;

	// delete temp marked records
	//deleteRec("plugins", "updflag = -2");

	// loop through plugins updating/adding each to the database
	foreach($pluginslist as $pluginslug => $pluginarry){
		// find plugin record
		$temp_updflag = $updflag;
		$pluginid     = $pluginarry['id'];

		// handle read cycle errors
		if(isset($pluginarry['errors'])){
			// error detected...
			$pluginarry['errors'] = join("<br/>", $pluginarry['errors']);
			$temp_updflag = $pluginarry['mark'];
		}elseif(getIfSet($pluginfromdb[0]['is_deleted'])){
			// plugin was soft-deleted within Foundry...
			$pluginsarry['is_deleted'] = true;
			$temp_updflag = -1;
		}else{
			// no problems here...
			$pluginarry['errors'] = '';
			$pluginarry['error_code'] = 0;
		}

		unset($pluginarry['id']);
		unset($pluginarry['mark']);
		if($pluginid > 0){
			// update
			$fldvals = '';
			foreach($pluginarry as $key => $data) $fldvals .= (($fldvals != '') ? ', ' : '')."`$key`='".str_replace("'", "&#34;", $data)."'";
			updateRec('plugins', $fldvals.", updflag = '{$temp_updflag}'", "id = {$pluginid}");
			$numUpd++;
		}else{
			// insert
			$flds = join(", ", array_keys($pluginarry)).", updflag";
			$vals = '';
			foreach($pluginarry as $data) $vals .= (($vals != '') ? ', ' : '')."'".str_replace("'", "&#34;", $data)."'";
			$vals .= ", '{$temp_updflag}'";
			$pluginid = insertRec('plugins', $flds, $vals);
			$numIns++;
		}
	}

	// mark plugins that were not found, or have config errors, as deleted
	updateRec("plugins", "updflag = '-1'", "updflag != '{$updflag}' && updflag != -2");
	$numDel = getRecNumRows("plugins", "*", "updflag = -1 AND deleted = 0");

	// present issues
	if($numIns > 0) $settings_issues['plugins-info'][] = $numIns." NEW plugin".(($numIns != 1) ? "s" : "")." ".(($numIns != 1) ? "were" : "was")." found and installed.";
	if($numDel > 0) $settings_issues['plugins'][] = $numDel." plugin".(($numDel != 1) ? "s" : "")." ".(($numDel != 1) ? "were" : "was")." NOT found, ".(($numDel > 1) ? "have" : "has")." had ".(($numDel > 1) ? "their" : "its")." name changed, or ".(($numDel > 1) ? "have" : "has")." info file problems.";

	getInstalledFrameworks($updflag);
}

/**
 * Load frameworks into database.
 * This function does not make the frameworks ready for use.
 * Call prepHeaderPluginsBlock or addHeadPlugin
 * @return array
 * @version 3.6
 */
function getInstalledFrameworks($updflag){
	global $settings_issues;

	// add built-in frameworks
	$fwlist = array('jqueryui' => array(
							'name' => 'jQuery UI', 'author' => 'various', 'created' => BLANK_DATE,
							'revised' => BLANK_DATE, 'descr' => 'User Interface module for jQuery.  Includes UI Core, several interactions, widgets, and effects.',
							'sysver' => 2, 'website' => 'http://www.jqueryui.com', 'usedin' => 'both', 'incl' => 'jqueryui', 'initfile' => 'jquery-ui.min.js',
							'headerfunc' => '', 'settingsfunc' => '', 'depends' => 'jquery', 'is_framework' => 1, 'inline_settings' => 'ver',
							'nodelete' => 1, 'nodisable' => 1, 'builtin' => 1, 'active' => 1
					)) +
					array('jquery' => array(
							'name' => 'jQuery', 'author' => 'John Resig', 'created' => '2010-11-11',
							'revised' => BLANK_DATE, 'descr' => 'jQuery core framework.', 'sysver' => 2,
							'website' => 'http://www.jquery.com', 'usedin' => 'both', 'incl' => 'jquery', 'initfile' => 'jquery.min.js',
							'headerfunc' => '', 'settingsfunc' => '', 'depends' => '', 'is_framework' => 1, 'inline_settings' => 'ver',
							'nodelete' => 1, 'nodisable' => 1, 'builtin' => 1, 'active' => 1
					));

	// add custom-loaded frameworks (typically from Google APIs)
	$fwlist += readCustomFrameworkINI();

	// loop through frameworks updating/adding each to the database
	$numIns = 0;
	$numUpd = 0;

	foreach($fwlist as $fwslug => $fwarry){
		// find framework record
		$fwfromdb = getRec("plugins", "*", "name = '{$fwarry['name']}' AND is_framework = 1", "", "1");
		$fwid     = $fwfromdb[0]['id'];

		$fwarry['errors'] = '';
		$fwarry['error_code'] = 0;
		$fwarry['updflag'] = $updflag;
		$fwarry['is_framework'] = 1;

		// update the records
		if($fwid > 0){
			// update
			$fldvals = '';
			foreach($fwarry as $key => $data) $fldvals .= (($fldvals != '') ? ', ' : '')."`$key`='".str_replace("'", "&#34;", $data)."'";
			updateRec('plugins', $fldvals, "id = {$fwid}");
			$numUpd++;
		}else{
			// insert (framework is iniitially disabled)
			if(!isset($fwarry['active'])) $fwarry['active'] = 0;
			$flds = join(", ", array_keys($fwarry));
			$vals = '';
			foreach($fwarry as $data) $vals .= (($vals != '') ? ', ' : '')."'".str_replace("'", "&#34;", $data)."'";
			$fwid = insertRec('plugins', $flds, $vals);
			$numIns++;
		}
	}

	// present issues
	if($numIns > 0) $settings_issues['plugins-info'][] = $numIns." NEW framework".(($numIns != 1) ? "s" : "")." ".(($numIns != 1) ? "were" : "was")." found and installed.";
}

/**
 * Read plugins folders searching for valid plugin.info files for processing
 * @param str $dir
 * @return array
 * @version 3.6
 */
function readInstalledPluginFolders($dir){
	global $settings_issues, $pluginpaths;

	// first, read folders and identify whether their contents
	// are already in DB or are newly installed

	$updpaths = array();	// plugins that are found in db and ready for update
	$newpaths = array();	// plugins not found in db and deemed new
	$errpaths = array();	// plugins that are found in db but were marked with an error
	if(isset($dir)){
		$this_dir = SITE_PATH.$dir;			// path/_plugins
		$slash = ((substr($this_dir, -1, 1) != '/') ? '/' : '');
		if(false !== ($handle = opendir(SITE_PATH.$dir))) {
			while (false !== ($file = readdir($handle))) {
				if($file != '.' && $file != '..'){
					if(is_dir($this_dir.$slash.$file)){
						$sub_dir = $this_dir.$slash.$file."/";		// path/_plugins/folder
						if(file_exists($sub_dir.'plugin.info')){
                            // remove record if folder is relative
                            deleteRec("plugins", "folder = '".getRelativePath($sub_dir)."'");

							// get the plugin record where the folders match
							// - if matched, plugin is being updated, otherwise it is new
							$pluginfromdb = getRec("plugins", "*", "folder = '".$sub_dir."'", "", "1");
							if(count($pluginfromdb) > 0){
								// matched
								if($pluginfromdb[0]['error_code'] == 0){
									$updpaths[] = array("id" => $pluginfromdb[0]['id'], "path" => $sub_dir, "info" => $sub_dir."plugin.info", "tempname" => "");
								}else{
									$errpaths[] = array("id" => $pluginfromdb[0]['id'], "path" => $sub_dir, "info" => $sub_dir."plugin.info", "tempname" => "");
								}
							}else{
								// not matched
								$newpaths[] = array("id" => 0, "path" => $sub_dir, "info" => $sub_dir."plugin.info", "tempname" => $file);
							}
						}
					}
				}
			}
			closedir($handle);
		}
	}
	$pluginpaths = array_merge($updpaths, $errpaths, $newpaths);
}

/**
 * Read plugin.info file data, populating pluginlist array
 * @param str $dir
 * @return array
 * @version 3.6
 */
function readInstalledPluginInfoFiles($pluginpaths){
	global $settings_issues, $pluginslist;

	// first, read folders and identify whether their contents
	// are already in DB or are newly installed

	$valid_setting_keys = array('name', 'author', 'ver', 'created', 'revised', 'descr', 'sysver', 'license',
								'website', 'usedin', 'incl', 'initfile', 'headerfunc', 'settingsfunc',
								'depends', 'nodelete', 'nodisable', 'builtin');

	if(is_null($pluginpaths)) return false;

	foreach($pluginpaths as $pathdata){
		$fhandle = @fopen($pathdata['info'], 'r');
		if($fhandle !== false){
			$pluginname = null;
			$pluginslug = null;
			$pluginarry = array();
			$pluginarry['id'] = $pathdata['id'];
			$pluginerrs = array();
			$pluginerrcode = 0;
			$pluginmark = -1;
			$pluginfolder = getLowestChildFolder($pathdata['path'])."/";

			while($setting = fscanf($fhandle, "%[#]%s\t%[^\t]")){
				list(, $key, $val) = $setting;
				$key = strtolower(str_replace(':', '', $key));
				$val = trim($val);
				if(in_array($key, $valid_setting_keys)){
					switch($key){
						case 'name':
							if(is_null($pluginname)){
								if($pathdata['tempname'] != '') $val = ucwords_smart($pathdata['tempname']);
								$pluginslug = slugify($val);
								if(isset($pluginslist[$pluginslug])){
									// oops! slug already in use
									$pluginerrs[] = "The name '".$val."' in ".$pluginfolder."plugin.info has been registered to another plugin.";
									$pluginerrcode += PLUGIN_CFGERR_NAMEDUP;
									// use folder as temp name so data can be saved
									$pluginslug = slugify($pluginfolder);
									$pluginname = ucwords_smart($pluginslug);
									$pluginmark = -2;
								}else{
									$pluginname = $val;
								}
							}
							break;
						case 'builtin':
						case 'nodelete':
						case 'nodisable':
							$val = strtolower($val);
							$val = (($val == '1' || $val == 'yes' || $val == 'true') ? 1 : 0);
							break;
						case 'sysver':
							$val_d = convertCodeVer2Dec($val);
							if($val_d < 2.06 or $val_d > getCodeVer()){
								$pluginerrs[] = "SYSVER must be between 2.6.0 and ".CODE_VER." in ".$pluginfolder."plugin.info.";
								$pluginerrcode += PLUGIN_CFGERR_SYSVERBAD;
							}
							break;
						case 'created':
						case 'revised':
							$val = ((isDate($val, DATE_FORMAT)) ? date(PHP_DATE_FORMAT, strtotime($val)) : BLANK_DATE);
							break;
						case 'usedin':
							if(!in_array(strtolower($val), array('admin', 'front', 'both'))) {
								$pluginerrs[] = "'".$val."' is not a valid USEDIN value in ".$pluginfolder."plugin.info.";
								$pluginerrcode += PLUGIN_CFGERR_USEDINBAD;
							}
							break;
						case 'license':
							if(!in_array(strtolower($val), array('free', 'trial', 'limited', 'full', 'subscription'))) {
								$pluginerrs[] = "'".$val."' is not a valid LICENSE value in ".$pluginfolder."plugin.info.";
								$pluginerrcode += PLUGIN_CFGERR_LICENSEBAD;
							}
							break;
						case 'website':
							if(strtolower(substr($val, 0, 4)) != 'http' && $val != '') $val = 'http://'.$val;
							break;
						case 'settingsfunc':
						case 'headerfunc':
							if(multiarray_search($val, $pluginslist) == $key){
								$pluginerrs[] = "'".$val."' is already registered as the ".strtoupper($key)." to another plugin.";
								$pluginerrcode += (($key == 'settingsfunc') ? PLUGIN_CFGERR_SETTINGSFUNC : PLUGIN_CFGERR_HEADERFUNC);
							}
							break;
						case 'incl':
							if(multiarray_search($val, $pluginslist) == $key){
								$pluginerrs[] = "'".$val."' is already assigned as the ".strtoupper($key)." to another plugin.";
								$pluginerrcode += PLUGIN_CFGERR_INCLDUP;
							}elseif(preg_match("/([^0-9a-z\-_])/i", $val)){
								$pluginerrs[] = "'".$val."' can only contain letters, numbers, hyphen or underscore.";
								$pluginerrcode += PLUGIN_CFGERR_INCLBAD;
							}
							break;
						case 'initfile':
							if($val == ''){
								$pluginerrs[] = strtoupper($key)." cannot be blank.";
								$pluginerrcode += PLUGIN_CFGERR_INITFILENF;
							}elseif(!file_exists($pathdata['path'].$val)){
								$pluginerrs[] = "The ".strtoupper($key)." was not found in the ".$pluginfolder." folder.";
								$pluginerrcode += PLUGIN_CFGERR_INITFILEBAD;
							}
							break;
					}
					$pluginarry[$key] = $val;
				}
			}
			fclose($fhandle);

			if($pluginname == null) {
				$pluginerrs[] = 'NAME is missing in '.$pluginfolder.'plugin.info.';
				$pluginerrcode += PLUGIN_CFGERR_NAMENF;
				continue;
			}elseif(!isset($pluginarry['sysver'])){
				$pluginerrs[] = 'SYSVER is required in '.$pluginfolder.'plugin.info.';
				$pluginerrcode += PLUGIN_CFGERR_SYSVERNF;
			}elseif(!isset($pluginarry['usedin'])){
				$pluginerrs[] = 'USEDIN is required in '.$pluginfolder.'plugin.info.';
				$pluginerrcode += PLUGIN_CFGERR_USEDINNF;
			}elseif(!isset($pluginarry['incl'])){
				$pluginerrs[] = 'INCL is required in '.$pluginfolder.'plugin.info.';
				$pluginerrcode += PLUGIN_CFGERR_INCLNF;
			}elseif(!isset($pluginarry['initfile'])){
				$pluginerrs[] = 'INITFILE is required in '.$pluginfolder.'plugin.info.';
				$pluginerrcode += PLUGIN_CFGERR_INITFILENF;
			}

			if(count($pluginerrs) > 0){
				$pluginarry['errors'] = $pluginerrs;
				$pluginarry['error_code'] = $pluginerrcode;
				$pluginarry['mark'] = $pluginmark;
				$settings_issues['plugins'] = array_merge($settings_issues['plugins'], $pluginerrs);
			}
			$pluginarry['folder'] = SITE_PATH.getRelativePath($pathdata['path']);
			$pluginslist[$pluginslug] = $pluginarry;
		}
	}
}

/**
 * Read the frameworks.ini file to load custom frameworks
 */
function readCustomFrameworkINI() {
	global $settings_issues;

	$valid_setting_keys = array('name', 'ver', 'descr', 'website',
								'depends', 'nodelete', 'nodisable', 'active');

	$fwinitfiles = array('mootools' => 'mootools-yui-compressed.js', 'chrome-frame' => 'CFInstall.min.js', 'dojo' => 'dojo/dojo.xd.js',
						 'ext-core' => 'ext-core.js', 'prototype' => 'prototype.js', 'scriptaculous' => 'scriptaculous.js',
						 'swfobject' => 'swfobject.js', 'yui' => 'build/yui/yui-min.js', 'webfont' => 'webfont.js');

	$fcontents = file_get_contents(SITE_PATH.ADMIN_FOLDER.CONFIG_FOLDER."frameworks.ini");
	$flines = preg_split("/\\n/", $fcontents);
	$fwname = null;
	$fwslug = null;
	$fwarry = array();
	$fwlist = array();
	$fwerrs = array();
	$fwerrcode = 0;
	$commentstarted = false;

	foreach($flines as $fline){
		$fline = trim($fline);
		if($fline != ''){
			if($fline == "/*" && !$commentstarted){
				$commentstarted = true;
			}elseif($fline == "*/" && $commentstarted){
				$commentstarted = false;
			}elseif(!$commentstarted){
				if(substr($fline, 0, 1) == "#" && strlen($fline) > 1){
					if(count($fwarry) > 0 && $fwslug != '') $fwlist[$fwslug] = $fwarry;
					$fwslug = strtolower(substr($fline, 1));
					$fwarry = array("incl" => $fwslug);
				}elseif(!is_null($fwslug)){
					$fwparts = explode("=", str_replace(";", "", $fline));
					if(in_array($fwparts[0], $valid_setting_keys)){
						$fwarry[$fwparts[0]] = $fwparts[1];
					}
				}
			}
		}
	}
	if(count($fwarry) > 0 && $fwslug != '') $fwlist[$fwslug] = $fwarry;
	foreach($fwlist as $slug => $fwarry){
		$fwlist[$slug]['usedin'] = 'both';
		$fwlist[$slug]['is_framework'] = 1;
		$fwlist[$slug]['nodelete'] = 1;
		$fwlist[$slug]['builtin'] = 0;
		$fwlist[$slug]['initfile'] = $fwinitfiles[$slug];
		$fwlist[$slug]['descr'] = getIfSet($fwarry['name'])." Framework.  ".getIfSet($fwarry['descr']);
	}
	return $fwlist;
}

/**
 * Output the HTML for the settings area plugins (installed)
 * @version 3.6
 */
function showSettingsPluginsInstalledList(){
	global $_system;

	$count = 0;
	$action = array('checkbox' => array(
							'id' => 'plugin_bulkcheck'),
					'menu' => array(
							'id' => 'plugin_bulkopt',
							'sel' => '',
							'options' => array('deactivate' => '(De)activate', 'delete' => 'Delete')
					),
					'buttons' => array(
							'plugin_bulkact' => 'Go')
				);
	if(!userIsAllowedTo('activate_plugins')) unset($action['menu']['options']['deactivate']);
	if(!userIsAllowedTo('delete_plugins')) unset($action['menu']['options']['delete']);
	?>
		<p class="settingsactions">
			<? if(count($action['menu']['options']) > 0) showSettingsActions(0, $action); ?>
			&nbsp;Usage Zones:&nbsp;
			<img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN ?>images/plugin_front.png" title="Front" alt="Front" width="10" />&nbsp;Front&nbsp;|&nbsp;
			<img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN ?>images/plugin_admin.png" title="Admin" alt="Admin" width="10" />&nbsp;Admin&nbsp;|&nbsp;
			<img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN ?>images/plugin_both.png" title="Both" alt="Both" width="10" />&nbsp;Both
		</p>
	<?

	foreach($_system->plugins as $data){
		$rtn = getSettingsPluginRow($data, 'normal');
		echo $rtn;
		if($rtn != '') $count++;
	}
	if($count == 0){ ?>
		<p>No plugins are installed in this zone.</p>
	<?
	}
}

/**
 * Output the HTML for the settings area problem plugins (missing, deleted, error)
 * @version 3.6
 */
function showSettingsPluginsProblemList(){
	global $_system;

	$count = 0;
	?>
		<p class="settingsactions"></p>
		<div id="genpanel3" title=""></div>
	<?
	foreach($_system->pluginsprob as $data){
		$rtn = getSettingsPluginRow($data, 'prob');
		echo $rtn;
		if($rtn != '') $count++;
	}

	if($count == 0){ ?>
		<p>No plugins are currently deleted or have plugin.info file configuration problems.</p>
	<?
	}
}


/**
 * Output the HTML for the settings area frameworks list
 * @version 3.6
 */
function showSettingsPluginsFrameworks(){
	global $_system;

	$count = 0;
	$action = array('checkbox' => array(
							'id' => 'plugin_fw_bulkcheck'),
					'menu' => array(
							'id' => 'plugin_fw_bulkopt',
							'sel' => '',
							'options' => array('deactivate' => '(De)activate')),
					'buttons' => array(
							'plugin_fw_bulkact' => 'Go')
			 		);
	if(!userIsAllowedTo('activate_frameworks')) unset($action['menu']['options']['deactivate']);
	?>
		<p class="settingsactions">
			<? if(count($action['menu']['options']) > 0) showSettingsActions(0, $action); ?>
		</p>
	<?
	foreach($_system->frameworks as $data){
		$rtn = getSettingsPluginRow($data, 'framework');
		echo $rtn;
		if($rtn != '') $count++;
	}

	if($count == 0){ ?>
		<p>No frameworks are installed.</p>
	<?
	}
}

/**
 * Return HTML for a single plugin row
 * @param str $plugin_data
 * @param boolean $is_prob
 */
function getSettingsPluginRow($data, $type = 'normal'){
	if($type == 'normal'){
		$act_link = '';
		$del_link = '';
		if(userIsAllowedTo('activate_plugins')){
			if($data['active'] == 0){
				$act_class = ' notactive';
				if($data['nodisable'] == 0) $act_link = '<a href="#" class="plugin_act">Activate</a>';
			}else{
				$act_class = '';
				if($data['nodisable'] == 0) $act_link = '<a href="#" class="plugin_act">Deactivate</a>';
			}
		}
		if($data['nodelete'] == 0 && userIsAllowedTo('delete_plugins')){
			$del_link = (($act_link != '') ? '&nbsp;|&nbsp;' : '').'<a href="#" class="plugin_del">Delete</a>';
		}

		ob_start();
		?>
			<div class="plugin_row">
				<input type="hidden" name="plugin_id[]" class="plugin_id" value="<?=$data['id']?>"/>
				<input type="hidden" name="plugin_slug[]" class="plugin_slug" value="<?=codify($data['name'])?>"/>
				<input type="hidden" name="plugin_title[]" class="plugin_title" value="<?=$data['name']?>"/>
				<div class="plugin_leftside">
					<? if($act_link.$del_link != '') { ?><input type="checkbox" id="plugin_check_<?=$data['id']?>" class="plugin_checks" /><? } ?>
					<span class="plugin_name<?=$act_class?>"><?=$data['name'].(($data['ver'] != '') ? ' v. '.$data['ver'] : '')?></span><br/>
					<span class="plugin_actions"><?=$act_link.$del_link?></span>
				</div>
				<div class="plugin_midside">
					<img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/plugin_".$data['usedin'].".png" ?>" alt="<?=$data['usedin']?>" title="<?=$data['usedin']?>" />
				</div>
				<div class="plugin_rightside">
					<span class="plugin_descr"><?=$data['descr'].(($data['builtin'] > 0) ? ' [Built-in]' : '')?></span><br/>
					<a href="#" class="plugin_more">More</a>
					<? if($data['website'] != '') {?>&nbsp;|&nbsp;<a href="<?=$data['website']?>" class="plugin_links" target="_blank">Visit plugin website</a><? } ?>
					<? if(file_exists($data['folder'].'readme.txt')) {?>&nbsp;|&nbsp;<a href="#" class="plugin_help_link" rel="<?=str_replace(SITE_PATH, "", $data['folder']).'readme.txt'?>">Documentation</a><? } ?>
					<? if($data['settingsfunc'] != '' && $data['active'] > 0) {?>&nbsp;|&nbsp;<a href="#" class="plugin_settings_link" rel="<?=$data['settingsfunc']?>|<?=$data['folder'].$data['initfile'];?>"><b>Settings</b></a><? } ?>
					<br/>
					<div class="plugin_info"></div>
				</div>
			</div>
			<? 	$rtn = ob_get_clean();

	}elseif($type == 'prob'){
		$fix_link   = '';
		$undel_link = '';
		$scrap_link = '';
		$note       = '';
		if($data['errors'] != ''){
			$note = 'Its <i>plugin.info</i> file has one or more configuration errors that prevents it from being fully installed.  Click "more" to view.';
			if(userIsAllowedTo('repair_plugins'))
				$fix_link = '<a href="#" class="plugin_fix">Repair</a>';
		}elseif($data['is_deleted'] == 1){
			$note = 'This plugin was deleted from within '.SYS_NAME.'.';
			if(userIsAllowedTo('delete_plugins')){
				$undel_link = '<a href="#" class="plugin_undel">Restore</a>';
				$scrap_link = '&nbsp;|&nbsp;<a href="#" class="plugin_scrap">Scrap It</a>';
			}
		}elseif($data['updflag'] == -1){
			$note = 'This plugin was not found in its registered installation folder.  If the <i>plugin.info</i> file cannot be recovered, you will have to re-install the plugin.';
		}

		ob_start();
		?>
			<div class="plugin_row">
				<input type="hidden" name="plugin_id[]" class="plugin_id" value="<?=$data['id']?>"/>
				<input type="hidden" name="plugin_slug[]" class="plugin_slug" value="<?=codify($data['name'])?>"/>
				<input type="hidden" name="plugin_title[]" class="plugin_title" value="<?=$data['name']?>"/>
				<div class="plugin_leftside">
						<span class="plugin_name"><?=$data['name'].(($data['ver'] != '') ? ' v. '.$data['ver'] : '')?></span><br/>
						<span class="plugin_actions"><?=$fix_link.$undel_link.$scrap_link?></span>
				</div>
				<div class="plugin_rightside">
					<span class="plugin_descr"><?=$note?></span><br/>
					<? if(strpos($note, 'more') > 0){ ?>
					<a href="#" class="plugin_more_prob">More</a>
					<? } ?>
					<br/>
					<div class="plugin_info_prob"></div>
				</div>
			</div>
		<?
		$rtn = ob_get_clean();

	}elseif($type == 'framework'){
		$act_link = '';
		if(userIsAllowedTo('activate_frameworks')){
			if($data['active'] == 0){
				$act_class = ' notactive';
				if($data['nodisable'] == 0) $act_link = '<a href="#" class="plugin_act">Activate</a>';
			}else{
				$act_class = '';
				if($data['nodisable'] == 0) $act_link = '<a href="#" class="plugin_act">Deactivate</a>';
			}
		}
		ob_start();
		?>
		<div class="plugin_row">
			<input type="hidden" name="plugin_id[]" class="plugin_id" value="<?=$data['id']?>"/>
			<input type="hidden" name="plugin_slug[]" class="plugin_slug" value="<?=codify($data['name'])?>"/>
			<input type="hidden" name="plugin_title[]" class="plugin_title" value="<?=$data['name']?>"/>
			<div class="plugin_leftside">
				<? if($act_link != ''){?><input type="checkbox" id="plugin_check_<?=$data['id']?>" class="plugin_checks" /><? } ?>
				<span class="plugin_name<?=$act_class?>"><?=$data['name'].(($data['ver'] != '') ? ' v. '.$data['ver'] : '')?></span><br/>
				<span class="plugin_actions"><?=$act_link?></span>
			</div>
			<div class="plugin_midside">
			</div>
			<div class="plugin_rightside">
				<span class="plugin_descr"><?=$data['descr']?></span><br/>
				<a href="#" class="plugin_more">More</a>
				<? if($data['website'] != '') {?>&nbsp;|&nbsp;<a href="<?=$data['website']?>" class="plugin_links" target="_blank">Visit framework website</a><? } ?>
				<br/>
				<div class="plugin_info"></div>
			</div>
		</div>
		<? 	$rtn = ob_get_clean();

	}
	return $rtn;
}

/**
 * Output plugin settings tab contents
 */
function showSettingsPluginsSettings(){
	global $_system;

	$count = 0;
	foreach($_system->plugins as $plugin_name => $data){
		if($data['active'] > 0 && $data['settingsfunc'] != '' && $data['is_deleted'] == 0){
			$rtn = getSettingsPluginSettingsRow($data);
			echo $rtn;
			if($rtn != '') $count++;
		}
	}

	if($count == 0){ ?>
	<p>No plugin settings are available.</p>
	<?
	}
}

/**
 * Return single plugin settings block
 * @param array $data
 */
function getSettingsPluginSettingsRow($data){
	$imgfolder = str_replace(SITE_PATH, WEB_URL, $data['folder']);

	ob_start();
	?>
	<div class="plugin_tile" id="plugin_setting_<?=codify($data['name']) ?>">
		<a href="#" class="plugin_settings_link" rel="<?=$data['settingsfunc']?>|<?=$data['folder'].$data['initfile'];?>">
			<?
			if(file_exists($data['folder']."icon.png")){
				echo '<img src="'.$imgfolder.'icon.png" alt="'.$data['name'].'" /><br/>';
			}else{
				echo '<img src="'.WEB_URL.ADMIN_FOLDER.IMG_UPLOAD_FOLDER.'no-pic.gif" /></br/>';
			}
			echo $data['name'];
			?>
		</a>
	</div>
	<?
	$rtn = ob_get_clean();
	return $rtn;
}

function showSettingsPluginsRepository(){

}

// ----------- OPERATION FUNCTIONS ---------------

/**
 * Setup plugins header block
 * @version 3.0
 */
function prepHeaderPluginsBlock(){
	global $_system, $uicore, $uieffects, $jq_lines;

	// Initiate built-in framework/tool header processes
	addHeadPlugin("jquery");
	if(IN_ADMIN) {
        // admin pages always includes dialog and tabs as well as other on-demand UIs
	    addHeadPlugin("jqueryui", array("widgets" => "dialog tabs ".$uicore, "effects" => $uieffects));
	}elseif($uicore != ''){
        // on-demand UIs
	    addHeadPlugin("jqueryui", array("widgets" => $uicore, "effects" => $uieffects));
	}
	if(IN_ADMIN) addHeadPlugin("ckeditor");

	// Initiate additional framework header processes
	if(count($_system->frameworks) > 0){
		foreach($_system->frameworks as $the_framework){
			if($the_framework['builtin'] == 0 && $the_framework['active'] != 0){
				$param = array("is_framework" => true, "ver" => $the_framework['ver'], "initfile" => $the_framework['initfile']);
				addHeadPlugin($the_framework['incl'], $param);
			}
		}
	}

	// Initiate included plugin header processes
	if(count($_system->pluginsincl) > 0){
		foreach($_system->pluginsincl as $the_plugin){
			if($the_plugin['headerfunc'] != ''){
				addHeadPlugin($the_plugin['incl'], array("initfile" => $the_plugin['initfile'], "headerfunc" => $the_plugin['headerfunc']));
			}
		}
	}

	// Initiate basic header process
	addHeadPlugin("basic");

	// Output header lines
	showHeadlines(true);

	// JQuery block
	if(count($jq_lines) > 0){
		echo "
<script type=\"text/javascript\">
		$(document).ready(function() {
";
		echo "\t".join("\n", $jq_lines)."\n";
		echo "    });\n</script>\n";
	}
}

/**
 * Add script/style lines as a group based on specific plugin
 * @param string $name
 * @param string $subname
 * @param boolean $in_head
 * @version 3.0
 */
function addHeadPlugin($name, $param_array = null, $in_head = true) {
	global $_system;

	$pluginsfolder = WEB_URL.ADMIN_FOLDER.PLUGINS_FOLDER;
	$incfolder = WEB_URL.ADMIN_FOLDER.INC_FOLDER;
	$jsfolder = WEB_URL.ADMIN_FOLDER.JS_FOLDER;
	$themefolder = WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN;
	$front_cssfolder = WEB_URL.CSS_FOLDER;
	$front_jsfolder = WEB_URL.JS_FOLDER;

	$group = array();
	$widgets_array = array();
	$effects_array = array();
	if(is_array($param_array)){
		if(isset($param_array['widgets'])) $widgets_array = preg_split("(,|, | )", trim($param_array['widgets']));
		if(isset($param_array['effects'])) $effects_array = preg_split("(,|, | )", trim($param_array['effects']));
		$is_framework = getIfSet($param_array['is_framework']);
		$ver = getIfSet($param_array['ver']);
		$initfile = getIfSet($param_array['initfile']);
	}

	$l_name = strtolower($name);
	switch(strtolower($l_name)){
		// frameworks and tools
		case "jquery":
			if(!preg_match("/(.+)/", getIfSet($ver))) $ver = getJQueryVer();
			if(!preg_match("/(.+)/", getIfSet($initfile))) $initfile = "jquery.min.js";
			$group[] = array("asType" => "script", "dir" => "http://ajax.googleapis.com/ajax/libs/$l_name/$ver/", "file" => $initfile, "media" => "");
			break;
		case "jqueryui":
			if(!preg_match("/(.+)/", getIfSet($ver))) $ver = getJQueryUIVer();
			if(!preg_match("/(.+)/", getIfSet($initfile))) $initfile = "jquery-ui.min.js";
			$group[] = array("asType" => "script", "dir" => "http://ajax.googleapis.com/ajax/libs/$l_name/$ver/", "file" => $initfile, "media" => "");
			$group[] = array("asType" => "style", "dir" => $jsfolder, "file" => "ui/ui.theme.css", "media" => "screen");
			if(count($widgets_array) > 0){
				$valid_widgets = explode(",", "draggable,sortable,droppable,resizable,accordion,progressbar,dialog,tabs,datepicker,nestedsortable");
				foreach($widgets_array as $widget){
					if(in_array($widget, $valid_widgets)){
						$group[] = array("asType" => "script", "dir" => $jsfolder, "file" => "ui/ui.".$widget.".js", "media" => "");
					}
				}
				if(in_array('nestedsortable', $widgets_array))
					$group[] = array("asType" => "script", "dir" => $jsfolder, "file" => "ui/ui.interface-1.2.js", "media" => "");
			}
			if(count($effects_array) > 0){
				$valid_effects = explode(",", "blind,bounce,clip,drop,explode,fade,fold,highlight,pulsate,scale,shake,slide,transfer");
				$group[] = array("asType" => "script", "dir" => $jsfolder, "file" => "ui/effects.core.js", "media" => "");
				foreach($effects_array as $effect){
					if(in_array($effect, $valid_effects)){
						$group[] = array("asType" => "script", "dir" => $jsfolder, "file" => "ui/effects.".$effect.".js", "media" => "");
					}
				}
			}
			if(IN_ADMIN) $group[] = array("asType" => "script", "dir" => $jsfolder, "file" => "ui/ui.script.js", "media" => "");
			break;
		case "ckeditor":
			if(!preg_match("/(.+)/", getIfSet($ver))) $ver = getCKEditorVer();
			if(!preg_match("/(.+)/", getIfSet($initfile))) $initfile = "ckeditor/ckeditor.js";
			$group[] = array("asType" => "script", "dir" => WEB_URL, "file" => $initfile, "media" => "");
			break;
		case ($param_array['is_framework'] == true):
			$group[] = array("asType" => "script", "dir" => "http://ajax.googleapis.com/ajax/libs/$l_name/$ver/", "file" => $initfile, "media" => "");
			break;
		case "basic":
			if(IN_ADMIN){
				$group[] = array("asType" => "style", "dir" => $themefolder, "file" => "master.css", "media" => "screen", "in_head" => true);
				$group[] = array("asType" => "script", "dir" => $jsfolder, "file" => "scripts.js", "media" => "", "in_head" => false);
				$group[] = array("asType" => "script", "dir" => $jsfolder, "file" => "validator.js", "media" => "", "in_head" => false);
			}else{
				$group[] = array("asType" => "style", "dir" => $front_cssfolder, "file" => "master.css", "media" => "screen", "in_head" => true);
				$group[] = array("asType" => "script", "dir" => $front_jsfolder, "file" => "scripts.js", "media" => "", "in_head" => false);
			}
			break;

		// custom plugin header
		default:
			if(is_array($param_array)){
				if(isset($param_array['headerfunc'])){
					$func = $param_array['headerfunc'];
					if(function_exists($func)){
						// call the custom plugin header initiator
						$_system->currentexecplugin = $l_name;
						call_user_func($func, $l_name);
					}else{
						addErrorMsg("Plugin headerfunc problem: Function '{$param_array['initfile']}>{$func}' not found.", CORE_ERR);
					}
				}
			}
			return;
	}
	$err = addHeadLineSet($group, $name, $in_head);
}

/**
 * Add header style/script block to headlines property of $_page class, from line-set object.
 * Can be called from anywhere in HTML where <script> or <style> code is allowed
 * @param array $lineset (type [style/script], dir, file, media, charset)
 * @param string $groupname
 * @param boolean $in_head
 * @version 3.0
 */
function addHeadLineSet($lineset, $groupname = "", $in_head = true) {
	global $_page;

	$err = false;
	// add comment line to headlines object
	$_page->headlines = array("line" => "<!-- ".strtoupper($groupname)." -->", "in_head" => $in_head);
	foreach($lineset as $item) {
		if(!isset($item['charset'])) $item['charset'] = '';
		$err = addHeadLine( $item['asType'],
							$item['dir'],
							$item['file'],
							getIfSet($item['media']),
							getIfSet($item['charset']),
							getIfSet($item['if']),
							((isset($item['in_head'])) ? $item['in_head'] : $in_head)
							);
		if($err) {
			addErrorMsg("Header problem: Cannot prepare header line for '$groupname'.", CORE_ERR);
			break;
		}
	}
}

/**
 * Store script/style line from either addHeadLineSet or direct call in headline property of $_page class.
 * Can be called from anywhere in HTML where <script> or <style> code is allowed
 * @param string $asType style or script
 * @param string $dir
 * @param string $file
 * @param string $media [optional]
 * @param string $charset [optional]
 * @param boolean $in_head [optional] True places line in <HEAD>, false in footer
 * @param boolean $immediate [optional] Output line immediately rather than queued
 * @return boolean
 * @version 3.0
 */
function addHeadLine($asType, $dir, $file, $media = "", $charset = "", $if = "", $in_head = true, $immediate = false) {
	global $_page;

	$asType = strtolower($asType);
	if(strstr($media, "RSS") == "") $media  = strtolower($media);
	$out = "";
	if($asType != "" && $dir != "" && $file != "") {
		switch($asType){
			case "script":
				//eg: <script type="text/javascript" language="javascript" src="WEB_URL.ckeditor/ckeditor.js"></script>
				if($if != "") $out .= "<!--[$if]>\n";
				$out.= "<script type=\"text/javascript\" language=\"javascript\" ";
				if($charset != "") $out.= "charset=\"$charset\" ";
				$out.= "src=\"".$dir.$file."\"></script>";
				if($if != "") $out .= "<![endif]-->\n";
				break;
			case "style":
				//eg: <link href=".WEB_URL.ADMIN_FOLDER.JS_FOLDER.jscal2/css/jscal2.css" rel="stylesheet" type="text/css" />
				if($if != "") $out .= "<!--[$if]>\n";
				$out.= "<link href=\"".$dir.$file."\" ";
				$out.= "rel=\"stylesheet\" ";
				$out.= "type=\"text/css\" ";
				if($media != "") $out.= "media=\"$media\" ";
				$out.= "/>";
				if($if != "") $out .= "<![endif]-->\n";
				break;
			case "rss":
				//eg: <link href="http://blogs.msdn.com/ie/rss.xml" rel="alternate" type="application/rss+xml" title="IEBlog (RSS 2.0)" />
				//eg: <link href=".WEB_URL.RSS_FOLDER.showrss.php" rel="alternate" type="application/rss+xml" title=".BUSINESS." />
				$out.= "<link href=\"http://blogs.msdn.com/ie/rss.xml\" ";
				$out.= "rel=\"alternate\" ";
				$out.= "type=\"application/rss+xml\" ";
				$out.= "title=\"IEBlog (RSS 2.0)\" ";
				$out.= "/>\n";
				break;
			case "favicon":
				//eg: <link rel="icon" href=".WEB_URL.IMG_UPLOAD_FOLDER.favicon.ico" type="image/x-icon" />
				$out.= "<link href=\"".$dir.$file."\" ";
				$out.= "rel=\"icon\" ";
				$out.= "type=\"image/x-icon\" />\n";
				break;
		}
		if($out != ''){
			if($immediate){
				echo $out.PHP_EOL;
			}else{
				// add line to headlines object
				$_page->headlines = array("line" => $out, "in_head" => $in_head);
			}
		}
		return false;
	}else{
		return true;
	}
}

/**
 * Output contents of PAGE->headline array
 * @param boolean $sorted true to sort JS first, CSS last
 * @param boolean $for_head True to output lines designated for the <HEAD>, or false for footer
 */
function showHeadLines($sorted = false, $for_head = true){
	global $_page;

	echo PHP_EOL;
	if(!$sorted){
		foreach($_page->headlines as $set) {
			if($set['in_head'] == $for_head) echo $set['line']."\n";
		}
	}else{
		$js = array();
		$css= array();
		if(count($_page->headlines) > 0){
			foreach($_page->headlines as $set){
				if($set['in_head'] == $for_head){
					$line = $set['line'];
					if(substr($line, 0, 4) != '<!--'){
						if(strpos($line, '.js') !== false){
							$js[] = $line;
						}else{
							$css[] = $line;
						}
					}
				}
			}
			foreach($css as $line) echo $line."\n";
			foreach($js as $line) echo $line."\n";
		}
	}
}

/**
 * Checks if plugin is in section (admin, root, any)
 * @param str $section
 * @version 3.4
 */
function checkPluginHome($plugin_name, $section = 'any'){
	$section = trim(strtolower($section));
	switch($section){
		case 'admin':
			$validfolder1 = SITE_PATH.ADMIN_FOLDER.PLUGINS_FOLDER;
			$validfolder2 = '';
			break;
		case 'root':
		case 'front':
			$validfolder1 = SITE_PATH.PLUGINS_FOLDER;
			$validfolder2 = '';
			break;
		case 'any':
		case 'both':
			$validfolder1 = SITE_PATH.ADMIN_FOLDER.PLUGINS_FOLDER;
			$validfolder2 = SITE_PATH.PLUGINS_FOLDER;
			break;
		default;
			return false;
	}

	if($validfolder2 != ''){
		if(!file_exists($validfolder1."/".strtolower($plugin_name)) && !file_exists($validfolder2."/".strtolower($plugin_name)))
			if($validfolder2 != '') $validfolder2 = " or ".$validfolder2;
				addErrorMsg("Plugin problem: Plugin '$plugin_name' was not installed in {$validfolder1}{$validfolder2}.", CORE_ERR);
	}else{
		if(!file_exists($validfolder1."/".strtolower($plugin_name)))
			addErrorMsg("Plugin problem: Plugin '$plugin_name' was not installed in {$validfolder1}.", CORE_ERR);
	}
}

/**
 * Checks the minimum PHP version required to run plugin and halts if PHP too old
 * @param str $plugin_name
 * @param float $minver
 * @version 3.4
 */
function checkPluginPHPVersion($plugin_name, $minver){
	if(floatval(phpversion()) < $minver) die("PHP {$minver} or higher required for {$plugin_name}!");
}

/**
 * Output plugin settings dialog contents as JSON data
 * @param string $title
 * @param string $contents
 * @param string $func Calling function
 * @version 3.7
 */
function pluginSettingsDialogContents($title, $contents, $func){
	return json_encode(array("title"=>$title, "contents"=>$contents, "func"=>$func));
}

/**
 * Pass plugin settings dialog button pressed response back through AJAX
 * @param string $message
 * @param boolean $closedialog
 * @version 3.7
 */
function pluginSettingsDialogButtonPressed($message = '', $closedialog = true){
	return json_encode(array("success"=>true, "message"=>$message, "closedialog"=>$closedialog));
}

/**
 * Return array containing all plugin objects installed in system
 * @return array
 */
function getPluginsInstalled(){
	global $_system;

	return $_system->plugins;
}

/**
 * Return an installed plugin object fetched by its inclusion verb
 * @param string $verb
 * @return array
 */
function getPluginInstalledbyVerb($verb){
	global $_system;

	return $_system->plugins[$verb];
}

/**
 * Return true if plugin is installed in the system
 * @param string $verb
 * @return boolean
 */
function isPluginInstalled($verb){
	global $_system;

	return (isset($_system->plugins[$verb]));
}

/**
 * Return array containing all plugin objects initiated in the $incl variable
 * @return array
 */
function getPluginsIncluded(){
	global $_system;

	return $_system->pluginsincl;
}

/**
 * Return plugin object fetched by its inclusion verb (initiated in the $incl variable)
 * @param string $verb
 * @return array or null if not found
 */
function getPluginIncludedbyVerb($verb){
	global $_system;

	return $_system->pluginsincl[$verb];
}

/**
 * Return true if plugin is included (initiated in the $incl variable)
 * @param string $verb
 * @return boolean
 */
function isPluginIncluded($verb){
	global $_system;

	return (isset($_system->pluginsincl[$verb]));
}

/**
 * Return array containing all plugin objects with problems or have been deleted internally
 * @return array
 */
function getPluginsWithProblems(){
	global $_system;

	return $_system->pluginsprob;
}

/**
 * Return problem plugin object fetched by its inclusion verb
 * @param string $verb
 * @return array or null if not found
 */
function getProblemPluginIncludedbyVerb($verb){
	global $_system;

	return $_system->pluginsprob[$verb];
}

/**
 * Return true if problem plugin is included
 * @param string $verb
 * @return boolean
 */
function isProblemPluginIncluded($verb){
	global $_system;

	return (isset($_system->pluginsprob[$verb]));
}

/**
 * Return array of inclusion verbs
 * @return array
 */
function getInclusionVerbs(){
	global $_system;

	return $_system->incl;
}

function getPluginCustomSetting($verb, $fromwhere = PLUGIN_SETTINGS_SAVETOSTD){
    global $_system;

    $result = false;
    if($verb != '' && in_array($fromwhere, array(PLUGIN_SETTINGS_SAVETOSTD, PLUGIN_SETTINGS_SAVETOSYS))){
        if(isPluginInstalled($verb)){
            switch($fromwhere){
                case PLUGIN_SETTINGS_SAVETOSTD:
                    // retrieve from plugin table
                    $result = getRecItem("plugins", "custom_settings", "incl = '$verb'");
                    break;
                case PLUGIN_SETTINGS_SAVETOSYS:
                    // retrieve from settings table
                    $result = getRecItem("settings", "value", "`name` = 'PLUGIN_".strtoupper($verb)." AND `type` = 'plg'");
                    break;
            }
        }
    }
    return $result;
}

/**
 * Save a plugin custom array, string, or object value to either the plugins or settings (system) table
 * @param string $verb      Plugin's registered verb (must be installed)
 * @param mixed $settings
 * @param string $savewhere
 * @return boolean
 */
function savePluginCustomSettings($verb, $settings, $savewhere = PLUGIN_SETTINGS_SAVETOSTD){
    global $_system;

    $result = false;
    if($verb != '' && in_array($savewhere, array(PLUGIN_SETTINGS_SAVETOSTD, PLUGIN_SETTINGS_SAVETOSYS))){
        $setval = $settings;
        if(is_array($setval) || is_object($setval)) $setval = json_encode($setval);

        if(isPluginInstalled($verb)){
            switch($savewhere){
                case PLUGIN_SETTINGS_SAVETOSTD:
                    // save to plugin table
                    $result = updateRec("plugins", "`custom_settings` = '$setval'", "incl = '$verb'");
                    break;
                case PLUGIN_SETTINGS_SAVETOSYS:
                    // save to settings table
                    $result = (replaceRec("settings", "`name` = 'PLUGIN_".strtoupper($verb)."', `value` = '$setval', `type` = 'plg'", "`name` = 'PLUGIN_".strtoupper($verb)."' AND `type` = 'plg'") !== false);
                    break;
            }
        }
    }
    return $result;
}
?>
