<?php
// ---------------------------
//
// AJAX WRAPPER
//
//  - Handles specific operations
//  - Use Ajaxprocessor for generic SQL requests
//
// ---------------------------

define("IN_AJAX", true);
define("VALID_LOAD", true);
define("BASIC_GETINC", true);
define("VHOST", substr(str_replace("\\", "/", realpath(dirname(__FILE__)."/../../../")), strlen(realpath($_SERVER['DOCUMENT_ROOT'])))."/");
define("DB_USED", ((isset($_REQUEST['db_used'])) ? (bool) $_REQUEST['db_used'] : true));

include ($_SERVER['DOCUMENT_ROOT'].VHOST."admin/inc/_core/getinc.php");				// required - starts PHP incls!!!
$op = strtolower(getRequestVar("op"));
$val = getRequestVar("val");
extractVariables($_REQUEST);

switch($op){
    // SETTINGS

    case 'getphpinfo':
        echo phpinfo();
        break;

	// THEMES

	case 'updatethemefields':
		// get current theme name
		$curtheme = getRecItem("settings", "value", "name='THEME'");
		echo "1|Changed to theme '$val'.|";
		changeTheme($curtheme, $val);
		break;
	case 'copytheme':
		$themes = prepThemes();
		if(!in_array($val, $themes)){
			// create theme folder
			// first, see if parent theme folder is still writable.  if not try to make it writable
			$perms = getFileACL(SITE_PATH.THEME_FOLDER);
			if(!$perms['user']['write']) exec ("chmod 757 ".SITE_PATH.THEME_FOLDER);

			// next, see if current theme file is readable
			$curtheme = getRecItem("settings", "value", "name='THEME'");
			if(is_readable(SITE_PATH.THEME_FOLDER.$curtheme)){
				if(mkdir2(SITE_PATH.THEME_FOLDER.$val, "0757")){
					if(@copy(SITE_PATH.THEME_FOLDER.$curtheme."/theme.css", SITE_PATH.THEME_FOLDER.$val."/theme.css")){
						echo "1|The theme '$val' was created and is ready for use.";
					}else{
						echo "0|The theme '$val' was not created!";
					}
				}else{
					echo "0|The theme folder for '$val' could not be created!";
				}
			}else{
				echo "0|The current theme '$curtheme' is not readable!";
			}
		}else{
			echo "0|A theme named '$val' already exists.";
		}
		break;
	case 'remtheme':
		// get current theme name
		$curtheme = getRecItem("settings", "value", "name='THEME'");
		if($val != 'default'){
			if(@unlink(SITE_PATH.THEME_FOLDER.$val."/theme.css")){
				if(@file_exists(SITE_PATH.THEME_FOLDER.$val."/theme.bak")) @unlink(SITE_PATH.THEME_FOLDER.$val."/theme.bak");
				@rmdir(SITE_PATH.THEME_FOLDER.$val);
			}
			if($curtheme == $val){
				// user is removing current theme, reset to default
				echo "2|Theme '$val' was removed.|";
				changeTheme($curtheme, 'default');
			}else{
				echo "1|Theme '$val' was removed.";
			}
		}else{
			echo "0|'Default' theme cannot be removed.";
		}
		break;
	case 'selectmenu':
	case 'selectoption':
		// get table data for display in select menu
		$table = getRequestVar('table');
		$fld = getRequestVar('fld');
		$crit = html_entity_decode(getRequestVar('crit'));
		$limit = intval(getRequestVar('limit'));
		if($limit <= 0) $limit = "";
		$order = getRequestVar('order');
		if(!isblank($table) && !isblank($fld)){
			$recarry = getRec($table, "id, $fld", $crit, $order, $limit);
			exitAjax(true, $recarry);
		}else{
			exitAjax(false, 'Table and Fld parameters missing in AJAX call!');
		}
		break;
	case 'getcolorpickercontents':
		$html = getThemePaletteBox();
		echo $html;
		break;

    // MENUS

    case 'updateadminmenulayout':
        if(!isblank($val)) {
            $ok = updateAdminMenusLayout($val);
            exitAjax($ok, '');
        }else{
            exitAjax(false, '');
        }
        break;
    case 'getadminmenueditorhtml':
        $level = getRequestVar('level');
        if(!isblank($level)) {
            if($level == 'top'){
                $html = getAdminMenuEditorHTML($val, "", $level);
                $html.= "||".getAdminMenuEditorSubMenu($val);
            }else{
                $vals = explode(":", $val);
                if(isset($vals[0]))
                    $html = getAdminMenuEditorHTML($vals[1], $vals[0], $level);
            }
            echo $html;
        }
        break;
    case 'saveadmintopmenu':
        if($key == 'pages'){
            $table = 'pages';
            $targettype = '';
        }
        $ok = false;
        if(!isblank($table) && !isblank($title)){
            list($ok, $key) = saveAdminMenu($level, $key, '', $title, $table, $targettype, $alias, $restricted);
        }
        exitAjax($ok, $key);
        break;
    case 'saveadminsubmenu':
        $ok = false;
        if(!isblank($parent) && !isblank($table) && !isblank($title)){
            list($ok, $key) = saveAdminMenu($level, $key, $parent, $title, $table, '', $alias, $restricted);
        }
        exitAjax($ok, $key);
        break;
    case 'getadminmenutarget':
        $html = '';
        if(!isblank($table) && $table != '- Unknown -'){
            $html = getAdminMenuTarget($table, $alias, getIfSet($targettype));
        }
        echo $html;
        break;
    case 'deleteadminmenu':
        $ok = false;
        if(!isblank($key) && !isblank($level)){
            $ok = deleteAdminMenu($key, $level, $parentkey);
        }
        exitAjax($ok);
        break;

	// PLUGINS

	case 'setpluginactivestate':
		$id = intval(getRequestVar('id'));
		if($id > 0){
			if(updateRec("plugins", "active={$val}", "id='{$id}'")){
				exitAjax(true, $val);
			}else{
				exitAjax(false, null);
			}
		}
		break;
	case 'getplugindata':
		$id = intval(getRequestVar('id'));
		if($id > 0){
			$dataarry = getRec("plugins", "*", "id='{$id}'", "", "1");
			$rtn = '';
			if(isBlank($val)){
				if($dataarry[0]['is_framework'] == 0){
					$keys = array('name'=>'Name', 'author'=>'Author', 'ver'=>'Version', 'license'=>'License',
									'created'=>'Created', 'revised'=>'Revised', 'descr'=>'',
									'sysver'=>'System version req\'d',
									'website'=>'Website', 'usedin'=>'Usage zone', 'incl'=>'Inclusion verb',
									'initfile'=>'Core file', 'folder'=>'Located', 'headerfunc'=>'Header function',
									'settingsfunc'=>'Settings function', 'depends'=>'Requires', 'nodelete'=>'Can be deleted?',
									'nodisable'=>'Can be disabled?', 'builtin'=>'Built-in?',
									'active'=>'Status');
				}else{
					$keys = array('name'=>'Name', 'ver'=>'Version',
									'sysver'=>'System version req\'d',
									'website'=>'Website', 'incl'=>'Inclusion verb',
									'initfile'=>'Loader file', 'depends'=>'Requires',
									'nodisable'=>'Can be disabled?', 'builtin'=>'Built-in?',
									'active'=>'Status');
				}
				foreach($dataarry[0] as $key => $data){
					if(!isBlank($keys[$key])){
						$text = '';
						switch($key){
							case 'created':
							case 'revised':
								if(isDate($data, "Y-m-d")) $text = date('M j, Y', strtotime($data));
								break;
							case 'builtin':
								$text = (($data == 1) ? 'Yes' : 'No');
								break;
							case 'active':
								$text = (($data == 1) ? 'Active' : 'Not Active');
								break;
							case 'nodelete':
							case 'nodisable':
								$text = (($data == 1) ? 'No' : 'Yes');
								break;
							case 'folder':
								$text = str_replace(SITE_PATH, '', $data);
								break;
							case 'incl':
								$text = ((isBlank($data)) ? 'Not required, built-in' : $data);
								break;
							case 'depends':
								$text = ((isBlank($data)) ? 'None' : $data);
								break;
                            case 'ver':
                                if(!isBlank($dataarry[0]['inline_settings']) && userIsAllowedTo('update_plugins')){
                                    $cfgs = explode(",", $dataarry[0]['inline_settings']);
                                    if(in_array($key, $cfgs)){
                                        $text = '<input type="text" class="plugin_datamod smallfldsize" rel="'.$dataarry[0]['id'].'|'.$key.'" value="'.$data.'" />';
                                    }else{
                                        $text = $data;
                                    }
                                }else{
                                    $text = $data;
                                }
                                break;
                            case 'website':
                                $text = '<a href="'.$data.'" target="_blank">'.$data.'</a>';
                                break;
							default:
								$text = $data;
								break;
						}
						if(!isblank($text)) $rtn .= ((!isblank($rtn)) ? '<br/>' : '').'<strong>'.$keys[$key].':</strong>&nbsp;'.$text;
					}
				}
			}elseif($val == '_prob'){
				$rtn = "&bull;&nbsp;".str_replace("<br/>", "<br/>&bull;&nbsp;", $dataarry[0]['errors']);
				$rtn.= "<br/>[PE".$dataarry[0]['error_code']."]<br/><br/><span style=\"font-weight: bold\">".SYS_NAME." may be able to <a href=\"#\" class=\"plugin_fix\" style=\"font-weight: bold !important\">repair</a> the INFO file.</span>";
			}
			exitAjax(true, $rtn);
		}
		break;
    case 'updateplugindata':
        $rel = getRequestVar('rel');
        if(!isblank($val) && !isblank($rel)){
            $rel = explode('|', $rel);
            if(updateRec("plugins", "`".$rel[1]."` = '$val'", "`id` = '".$rel[0]."'")){
                exitAjax(true, '');
            }else{
                exitAjax(false, '');
            }
        }else{
            exitAjax(false, '');
        }
        break;
	case 'deleteplugin':
	case 'scrapplugin':
		$plugin_id = intval(getRequestVar('id'));
		if($plugin_id > 0){
			if(PLUGIN_FULLDELETE || $op == 'scrapplugin'){
				$dataarry = getRec("plugins", "*", "id='{$plugin_id}'", "", "1");
				$plugin_folder = $dataarry[0]['folder'];
				$rtn = deleteFileContents($plugin_folder);
				deleteRec("plugins", "id='{$plugin_id}'");
				exitAjax((isBlank($rtn)), $rtn);
			}else{
				if(updateRec("plugins", "is_deleted=1", "id='{$plugin_id}'")){
					$plugin_data = getRec("plugins", "*", "id='{$plugin_id}'");
					$rtn = array();
					$rtn['row'] = getSettingsPluginRow($plugin_data[0], 'prob');
					$rtn['setting'] = ((!isblank($plugin_data[0]['settingsfunc'])) ? codify($plugin_data[0]['name']) : '');
					exitAjax(true, $rtn);
				}else{
					exitAjax(false, 'Problem marking plugin as deleted in the database.');
				}
			}
		}
		break;
	case 'undeleteplugin':
		$plugin_id = intval(getRequestVar('id'));
		if($plugin_id > 0){
			if(!PLUGIN_FULLDELETE){
				 if(updateRec("plugins", "is_deleted=0", "id='{$plugin_id}'")){
					$plugin_data = getRec("plugins", "*", "id='{$plugin_id}'");
					$rtn = array();
					$rtn['row'] = getSettingsPluginRow($plugin_data[0], 'normal');
					$rtn['setting'] = ((!isblank($plugin_data[0]['settingsfunc'])) ? getSettingsPluginSettingsRow($plugin_data[0]) : '');
					exitAjax(true, $rtn);
				 }else{
					 exitAjax(false, 'Problem marking plugin as un-deleted in the database.');
				 }
			}
		}
		break;
	case 'runpluginsettingsfunc':
        // execute a registered plugin trigger function called 'settingsfunc'
		if(!isblank($val)){
			$func = explode('|', $val);
			if(function_exists($func[0])){
				// plugin init file already included
				echo call_user_func($func[0]);
			}else{
				// include plugin and try again
				include($func[1]);
				if(function_exists($func[0])){
						echo call_user_func($func[0]);
				}else{
					// passthru with error
					echo pluginSettingsDialogContents('', '', $func[0]);
				}
			}
		}
		break;
	case 'runpluginsettingsaction':
		$action = getRequestVar('action');
		$data = getRequestVar('data');
		if(!isblank($val)){
			$func = explode('|', $val);
			if(function_exists($func[0])){
				// plugin init file already included
				$rtn = call_user_func($func[0], $action, $data);
			}else{
				// include plugin and try again
				include($func[1]);
				if(function_exists($func[0])){
					$rtn = call_user_func($func[0], $action, $data);
				}
			}
			echo $rtn;
		}
		break;
	case 'getpluginrepairform':
		// part 1: prepare and open repair dialog
		$plugin_id = intval(getRequestVar('id'));
		if($plugin_id > 0){
			$plugin_data = getRec("plugins", "*", "id = '$plugin_id'", "", "1");
			if(count($plugin_data) == 1){
				$errcode = $plugin_data[0]['error_code'];
				$html = '
				<p>'.SYS_NAME.' will try to correct the following problematic entries in the <b>'.$plugin_data[0]['name'].' <i>plugin.info</i></b> file.  If the repair does not work, you will need to correct the issues manually.</p>
				<form id="plugin_repair_form" method="post" action="">
					<input type="hidden" name="plugin_id" id="subj_plugin_id" value="'.$plugin_id.'"/>';
				$html.= '<div style="height: 240px; overflow: auto;">';

				$line1  = '<p><b>%s</b><br/>';
				$line2t = '%s: <input type="text" name="%s" id="%s" value="%s" />%s</p>';
				$line2m = '%s: <select name="%s" id="%s">';
				$line2mo= '<option value="%s"%s>%s</option>';
				$line2mc= '</select>%s</p>';
				if($errcode & PLUGIN_CFGERR_NAMEDUP){
					$html .= sprintf($line1, "NAME Already Exists");
					$html .= sprintf($line2t, "New Name", "name", "name", $plugin_data[0]['name'], '');
				}
				if($errcode & PLUGIN_CFGERR_SYSVERBAD){
					$html .= sprintf($line1, "SYSVER Invalid");
					$html .= sprintf($line2t, "Min. System Version", "sysver", "sysver", $plugin_data[0]['sysver'], '');
				}
				if($errcode & PLUGIN_CFGERR_USEDINBAD){
					$html .= sprintf($line1, "USEDIN Invalid");
					$html .= sprintf($line2m, "Usage Zone", "usedin", "usedin");
					$html .= sprintf($line2mo, "admin", "", "Admin");
					$html .= sprintf($line2mo, "front", "", "Front");
					$html .= sprintf($line2mo, "both", "", "Both");
					$html .= sprintf($line2mc, "");
				}
				if($errcode & PLUGIN_CFGERR_LICENSEBAD){
					$html .= sprintf($line1, "LICENCE Invalid");
					$html .= sprintf($line2m, "License", "license", "License");
					$html .= sprintf($line2mo, "", "", "- n/a -");
					$html .= sprintf($line2mo, "free", "", "Free");
					$html .= sprintf($line2mo, "trial", "", "Trial");
					$html .= sprintf($line2mo, "limited", "", "Limited");
					$html .= sprintf($line2mo, "full", "", "Full");
					$html .= sprintf($line2mo, "subscription", "", "Subscription");
					$html .= sprintf($line2mc, "");
				}
				if($errcode & PLUGIN_CFGERR_SETTINGSFUNC){
					$html .= sprintf($line1, "SETTINGSFUNC Already Exists");
					$html .= sprintf($line2t, "New Settings Function", "settingsfunc", "settingsfunc", $plugin_data[0]['settingsfunc'], '');
				}
				if($errcode & PLUGIN_CFGERR_HEADERFUNC){
					$html .= sprintf($line1, "HEADERFUNC Already Exists");
					$html .= sprintf($line2t, "New Header Function", "headerfunc", "headerfunc", $plugin_data[0]['headerfunc'], '');
				}
				if($errcode & PLUGIN_CFGERR_INCLDUP){
					$html .= sprintf($line1, "INCL Already Exists");
					$html .= sprintf($line2t, "New Inclusion Verb", "incl", "incl", $plugin_data[0]['incl'], '');
				}
				if($errcode & PLUGIN_CFGERR_INCLBAD){
					$html .= sprintf($line1, "INCL Contains Invalid Characters");
					$html .= sprintf($line2t, "New Inclusion Verb", "incl", "incl", $plugin_data[0]['incl'], '');
				}
				if($errcode & PLUGIN_CFGERR_NAMENF){
					$html .= sprintf($line1, "NAME Missing");
					$html .= sprintf($line2t, "Plugin Name", "name", "name", '', '');
				}
				if($errcode & PLUGIN_CFGERR_SYSVERNF){
					$html .= sprintf($line1, "SYSVER Missing");
					$html .= sprintf($line2t, "Min. System Version", "sysver", "sysver", '', 'Current system version is '.CODE_VER);
				}
				if($errcode & PLUGIN_CFGERR_USEDINNF){
					$html .= sprintf($line1, "USEDIN Invalid");
					$html .= sprintf($line2m, "Usage Zone", "usedin", "usedin");
					$html .= sprintf($line2mo, "admin", "", "Admin");
					$html .= sprintf($line2mo, "front", "", "Front");
					$html .= sprintf($line2mo, "both", "", "Both");
					$html .= sprintf($line2mc, "");
				}
				if($errcode & PLUGIN_CFGERR_INCLNF){
					$html .= sprintf($line1, "INCL Missing");
					$html .= sprintf($line2t, "Inclusion Verb", "incl", "incl", '', '');
				}
				if($errcode & PLUGIN_CFGERR_INITFILENF){
					$html .= sprintf($line1, "INITFILE Missing");
					$html .= sprintf($line2t, "Core File", "initfile", "initfile", '', '');
				}
				if($errcode & PLUGIN_CFGERR_INITFILEBAD){
					$html .= sprintf($line1, "INITFILE Not Found");
					$html .= sprintf($line2t, "Core File", "initfile", "initfile", $plugin_data[0]['initfile'], '');
				}

				$html.= '
						</div>
						<div style="text-align: right; border-top: 1px dotted black; padding: 5px 0px">
								<input type="submit" id="plugin_fix_submit" value="Apply Fixes" />
						</div>
				</form>';
			}else{
				$html = '
				Uh Oh!  The requested plugin data could not be retrieved from the database.';
			}
		}

		$html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
		<html>
		</html>
		<body>
'.$html.'
		</body>
</html>';
		echo $html;
		break;
	case 'repairplugincfg':
		// part 2: try to repair plugin.info file
		parse_str($val);
		if($plugin_id > 0){
			$plugin_data = getRec("plugins", "*", "id = '{$plugin_id}'", "", "1");
			$msg = array();
			if(count($plugin_data) == 1){
				// get plugins field array (name[], license[], incl[], sysver[]...)
				$field_array = getRecFieldArray("plugins", "*", "id != '{$plugin_id}'");
				foreach($field_array as $key => $sub_array) foreach($sub_array as $sub_key => $value) { if(!isblank($value) && !is_null($value)) { $field_array[$key][$sub_key] = strtolower($value); } else { unset($field_array[$key][$sub_key]); } }

				// get error code array (plugin_cfgerr_...)
				$errcode = $plugin_data[0]['error_code'];
				$errcode_array = getConsts('PLUGIN_CFGERR');
				foreach($errcode_array as $code => $codeval){
					$field_from_code = str_replace(array("plugin_cfgerr_", "dup", "nf", "bad"), "", strtolower($code));
					$postvar = ${$field_from_code};
					if(($errcode & $codeval) > 0){
						switch(strtoupper($code)){
							case "PLUGIN_CFGERR_NAMEDUP":
							case "PLUGIN_CFGERR_NAMENF":
							case "PLUGIN_CFGERR_HEADERFUNC":
							case "PLUGIN_CFGERR_SETTINGSFUNC":
							case "PLUGIN_CFGERR_INCLDUP":
							case "PLUGIN_CFGERR_INCLNF":
							case "PLUGIN_CFGERR_INCLBAD":
								if(in_array(strtolower($postvar), $field_array[$field_from_code])){
									$msg[] = strtoupper($field_from_code)." already exists.\n";
								}elseif(preg_match('/([^a-z0-9\_\- ])/i', $postvar)){
									$msg[] = strtoupper($field_from_code)." can only consist of letters, numbers, space, hyphen and underscore.\n";
								}else{
									$errcode -= constant(strtoupper($code));
								}
								break;
							case "PLUGIN_CFGERR_SYSVERBAD":
							case "PLUGIN_CFGERR_SYSVERNF":
								$postvar_d = convertCodeVer2Dec($postvar);
								if(isBlank($postvar)){
									$msg[] = strtoupper($field_from_code)." cannot be blank.\n";
								}elseif(preg_match('/([^0-9\.])/i', $postvar)){
									$msg[] = strtoupper($field_from_code)." can only consist of numbers and decimal point.\n";
								}elseif($postvar_d < 2.06 or $postvar_d > getCodeVer()){
									$msg[] = strtoupper($field_from_code)." must be between 2.6.0 and ".CODE_VER.".\n";
								}else{
									$errcode -= constant(strtoupper($code));
								}
								break;
							case "PLUGIN_CFGERR_LICENSEBAD":
								if(!in_array($postvar, array('free', 'trial', 'limited', 'full', 'subscription')) && !isblank($postvar)){
									$msg[] = strtoupper($field_from_code)." is still invalid.\n";
								}else{
									$errcode -= constant(strtoupper($code));
								}
								break;
							case "PLUGIN_CFGERR_USEDINBAD":
							case "PLUGIN_CFGERR_USEDINNF":
								if(isBlank($postvar)){
									$msg[] = strtoupper($field_from_code)." was not chosen.\n";
								}elseif(!in_array($postvar, array('admin', 'front', 'both')) && !isblank($postvar)){
									$msg[] = strtoupper($field_from_code)." is still invalid.\n";
								}else{
									$errcode -= constant(strtoupper($code));
								}
								break;
							case "PLUGIN_CFGERR_INITFILENF":
							case "PLUGIN_CFGERR_INITFILEBAD":
								if(isBlank($postvar)){
									$msg[] = strtoupper($field_from_code)." cannot be blank.\n";
								}elseif(!file_exists($plugin_data[0]['folder'].$postvar)){
									$msg[] = strtoupper($field_from_code)." was not found in plugin folder.\n";
								}else{
									$errcode -= constant(strtoupper($code));
								}
								break;
						}
					}
				}

				$msg = array_unique($msg);
				if($errcode > 0 || count($msg) > 0){
					// we still have a problem
					exitAjax(false, "- ".join("- ", $msg));
				}else{
					// everything is ok now
					// save data back to file
					$file = $plugin_data[0]['folder']."plugin.info";
					$fcontents = file_get_contents($file);
					$flines = preg_split("/(\n)/i", $fcontents);
					$fnewcontents = "";
					foreach($flines as $fline){
						$fline_parts = explode(":", $fline, 2);
						$fline_off = ((substr($fline_parts[0], 0, 2) == '//') ? '//' : '');
						$field = strtolower(str_replace(array("#", "/"), "", $fline_parts[0]));
						$value = trim($fline_parts[1]);
						if(!isblank($field) && !isblank($value)){
							if(isset(${$field})) $value = ${$field};
							$fnewcontents .= $fline_off."#".$field.":".str_repeat(" ", (13-strlen($field))).$value.PHP_EOL;
						}
					}
					chmod2($file);
					@file_put_contents($file, $fnewcontents);

					// and update the database
					$settings_issues = array();
					getInstalledPlugins();

					// and return the plugin row to the js process
					$plugin_data = getRec("plugins", "*", "id = '{$plugin_id}'", "", "1");
					$rtn = array();
					$rtn['row'] = getSettingsPluginRow($plugin_data[0], 'normal');
					$rtn['setting'] = ((!isblank($plugin_data[0]['settingsfunc'])) ? getSettingsPluginSettingsRow($plugin_data[0]) : '');
					$rtn['plugins_issues'] = showSettingsIssues('plugins', true);
					$rtn['settings_issues'] = showSettingsIssues('', true);
					exitAjax(true, $rtn);
				}
			}else{
				exitAjax(false, "The plugin was not found in the database.");
			}
		}
		break;
	case 'getsettingshelpfile':
		if(!isblank($val)){
			$val = SITE_PATH.$val;
			if(file_exists($val)){
				$fcontents = file_get_contents($val);
				$fcontents = str_replace(array("\"", "<", ">"), array("&quot;", "&lt;", "&gt;"), $fcontents);
				$fcontents = str_replace(array("\n"), array("<br/>"), $fcontents);
				echo '<p>'.$fcontents.'</p>';
			}
		}
		break;

	// USERS

	case 'setuseractivestate':
		$id = intval(getRequestVar('id'));
		if($id > 0){
			if(updateRec("admin_accts", "activated={$val}", "id='{$id}'")){
				exitAjax(true, $val);
			}else{
				exitAjax(false, null);
			}
		}
		break;
	case 'deleteuserdata':
		$user_id = intval(getRequestVar('id'));
		if($user_id > 0){
			$rtn = deleteRec("admin_accts", "id='{$user_id}'");
			exitAjax($rtn, '');
		}
		break;
	case 'addnewuser':
		$sel = "";
		$val = intval($val) + 1;
		$user_levels = getConsts('ADMLEVEL_');
		foreach($user_levels as $key => $lvl){
			if($lvl >= getUserLevel()){
				$sel .= '<option value="'.$lvl.'">'.ucwords(strtolower(substr($key, 9))).'</option>';
			}
		}
		$data = <<<EOT
				<div class="user_row user_new">
					<input type="hidden" class="user_id" name="users_id[$val]" value="0" />
					<div class="user_leftside">
						<span class="user_name"><i>- New -</i></span><br/>
						<span class="user_actions"><a href="" class="user_del">Delete</a></span>
						<input type="hidden" name="users_active[$val]" id="users_active{$val}" value="1" />
						<input type="hidden" name="users_cur[$val]" class="user_cur" value="" />
					</div>
					<div class="user_rightside">
						<span>New Name:</span> <input type="text" name="users_name[$val]" id="users_name{$val}" size="20" value=""/><br/>
						<span>New Password:</span> <input type="password" name="users_pass[$val]" id="users_pass{$val}" size="20" value=""/><br/>
						<span>Email Address:</span> <input type="text" name="users_email[$val]" id="users_email{$val}" size="30" value=""/><br/>
						<span>User Level:</span> <select name="users_level[$val]" id="users_level{$val}">
							{$sel}
							</select>
					</div>
				</div>

EOT;
		echo $data;
		break;

	// ROBOTS

	case 'revertrobotfile':
		$revertfile = SITE_PATH.ADMIN_FOLDER.$val;
		$success = false;
		if(!isblank($val) && file_exists($revertfile)){
			// first backup the current file
            chmod(SITE_PATH."robots.txt", 0777);
            if(copy (SITE_PATH."robots.txt", SITE_PATH.ADMIN_FOLDER.REV_FOLDER."robots.".date("YmdHis").".txt")){
                $fcontents = file_get_contents($revertfile);
                file_put_contents(SITE_APTH."robots.txt", $fcontents);
                $success = true;
            }
            chmod(SITE_PATH."robots.txt", 0644);
		}
		exitAjax($success, '');
		break;

    // DATA ALIASES

    case 'validatedataaliasmeta':
        if(!isblank($val)){
            list(,$error) = getDataAliasPatternFromMeta($val);
            if($error == ''){
                exitAjax(true, ((dataAliasIsCategory($val)) ? 'Valid category meta' : ''));
            }else{
                exitAjax(false, $error.'...');
            }
        }else{
            exitAjax(true, '');
        }
        break;

	// DB MANAGER
    // -- DB Manager operations require the database controller to be disabled

	case 'trytoaccessdb':
		$domain  = getRequestVar('d');
		$domainext = substr($domain, -3);
		$db_host = getRequestVar('h');
		$db_user = getRequestVar('u');
		$db_pass = getRequestVar('p');
		$db_name = getRequestVar('n');
		$db_port = intval(getRequestVar('r'));
		if(!isblank($db_host) && !isblank($db_user) && !isblank($db_name) && !isblank($db_pass)){
			$db_hostport = (($db_port > 0 && $db_port != 3306) ? $db_host.':'.$db_port : $db_host);
			$link = @mysql_connect($db_hostport, $db_user, $db_pass);
			if($link){
				if(mysql_select_db($db_name)){
					include_once(SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_db.php");
					$dbset = readDBINI();
					$key = "";
					// if a key for this domain exists, update the settings
					if(isset($dbset[$domain])){
						$key = $domain;
					}elseif(isset($dbset[$domainext])){
						$key = $domainext;
					}else{
					// ... otherwise add a new key
						$key = $domain;
					}

					$dbset[$key] = array("DBNAME" => $db_name,
										 "DBHOST" => $db_host,
										 "DBUSER" => $db_user,
										 "DBPASS" => $db_pass,
										 "DBPORT" => $db_port,
										);

					// put it back in the file
					updateDBINI($dbset);

					exitAjax(true, "Yureka! Your settings worked.  The site will continue starting.");
				}else{
					exitAjax(false, "The database '$db_name' was not found or is inaccessible.");
				}
			}else{
				exitAjax(false, "Connection to the provided database server was not possible.");
			}
		}else{
			exitAjax(false, "Host, Username, and Database Name are required.");
		}
		break;
	case 'updatedbsettings':
		break;

	// REGISTER

	case 'executelistaction':
		$param = getRequestVar('param');		// action, row_id
		$pagedata = getRequestVar('pagedata');
		$page_subject = getIfSet($pagedata['page_subject']);
		$page_ingroup = getIfSet($pagedata['page_ingroup']);
		$page_url = urldecode(getIfSet($pagedata['page_url']));
		$base_page_url = preg_replace("/((\?|\&).*)/i", "", $page_url);
		$page_flds = json_decode(getIfSet($param['page_flds']));
		$x_data = getIfSet($param['x_data']);				// supplementary data for secondary functions

		// get the db tables used on the calling page
		$tables = getRec("register", "db_table, db_child_table, db_parent_table", "fileurl = '$base_page_url' AND type = 'db'", "", "1", "", true);

		// get the parameters saved by the showlist operation
		$funcrec = getRec("register", "*", "`type` = 'showlist' AND fileurl = '$page_url'");
	    if(!isblank($funcrec[0]['parameters'])) {
	    	$list_params = json_decode($funcrec[0]['parameters'], true);
	    }

		// see if there is a registered trigger function to handle the action
		$retn = null;
		$gotopage = null;
		list($do_std_process, $retn) = executeTriggerFunction(TF_LISTACTION, $param, $base_page_url);

		if($do_std_process){
			// function call failed or nothing there,
			// so handle the process the traditional way
			debugger_on_error();
		    switch($param['action']) {
		    	case DEF_ACTION_DELETE:
		    		if($tables['db_table'] == 'pages'){
		    			// page type record
			    		if(FULL_DELETE) {
							$subimgrec = getRec("`".$tables['db_child_table']."`", "*", "pageid = '{$param['row_id']}' OR pageid IN (SELECT id FROM `{$tables['db_table']}` WHERE ppage_id = '{$param['row_id']}')", "", "");
							if(count($subimgrec) > 0){
								foreach($subimgrec as $subimg){
									deleteImage(SITE_PATH.$subimg['image'], SITE_PATH.$subimg['thumb']);
								}
							}
							// delete page content and, then, page records
							deleteRec("`".$tables['db_child_table']."`", "pageid = '{$param['row_id']}' OR pageid IN (SELECT id FROM `{$tables['db_table']}` WHERE ppage_id = '{$param['row_id']}')");
							deleteRec("`".$tables['db_table']."`", "id = '{$param['row_id']}' OR ppage_id = '{$param['row_id']}'");
						} else {
							updateRec("`".$tables['db_table']."`", "deleted = 1", "id = '{$param['row_id']}'");
						}
						if(function_exists('sm_start')){
							sm_start(false, "monthly", 1, 0, true, "", true);
						}
		    		}elseif(!isblank($tables['db_child_table'])){
		    			// parent record
						if(FULL_DELETE) {
							$subimg_array = getRec("`".$tables['db_child_table']."`", "*", "cat_id = '{$param['row_id']}' OR cat_id IN (SELECT id FROM `{$tables['db_table']}` WHERE cat_id = '{$param['row_id']}')", "", "");
							if(count($subimg_array) > 0){
								foreach($subimg_array as $subimg){
									deleteImage(SITE_PATH.$subimg['image'], SITE_PATH.$subimg['thumb']);
								}
							}
							deleteRec("`".$tables['db_child_table']."`", "cat_id = '{$param['row_id']}' OR cat_id IN (SELECT id FROM `{$tables['db_table']}` WHERE cat_id = '{$param['row_id']}')");
							deleteRec("`".$tables['db_table']."`", "id = '{$param['row_id']}' OR cat_id = '{$param['row_id']}'");
						} else {
							updateRec("`".$tables['db_table']."`", "deleted = 1", "id = '{$param['row_id']}' OR cat_id = '{$param['row_id']}'");
						}
		    		}else{
		    			// single item record
						if(FULL_DELETE) {
							$imgrec = getRec("`".$tables['db_table']."`", "*", "id = '{$param['row_id']}'", "", "1");
							deleteImage(SITE_PATH.$imgrec[0]['image'], SITE_PATH.$imgrec[0]['thumb']);
							if ($param['row_id'] > 0) deleteRec("`".$tables['db_table']."`", "id = '{$param['row_id']}'");
						} else {
							updateRec("`".$tables['db_table']."`", "deleted = 1", "id = '{$param['row_id']}'");
						}
		    		}
		    		break;
		    	case DEF_ACTION_UNDELETE:
					updateRec("`".$tables['db_table']."`", "deleted = 0", "id = '{$param['row_id']}'");
		    		break;
		    	case DEF_ACTION_ARCHIVE:
					updateRec("`".$tables['db_table']."`", "archived = 1", "id = '{$param['row_id']}'");
		    		break;
		    	case DEF_ACTION_UNARCHIVE:
					updateRec("`".$tables['db_table']."`", "archived = 0", "id = '{$param['row_id']}'");
		    		break;
				case DEF_ACTION_ACTIVATE:
					updateRec("`".$tables['db_table']."`", "activated = 1, date_published = NOW()", "id = '{$param['row_id']}'");
					break;
				case DEF_ACTION_DEACTIVATE:
					updateRec("`".$tables['db_table']."`", "activated = 0", "id = '{$param['row_id']}'");
					break;
		    	case DEF_ACTION_PUBLISH:
					updateRec("`".$tables['db_table']."`", "published = 1, date_published = NOW()", "id = '{$param['row_id']}'");
		    		break;
		    	case DEF_ACTION_UNPUBLISH:
					updateRec("`".$tables['db_table']."`", "published = 0", "id = '{$param['row_id']}'");
		    		break;
		    	case DEF_ACTION_OPEN:	// reserved for sections
					$root = getRecItem("`".$tables['db_table']."`", "key", "id = '{$param['row_id']}");
					setRootSession();
					$gotopage = WEB_URL.ADMIN_FOLDER."pages/list-pages.php?root=".$root;
		    	case DEF_ACTION_DEFAULT:
					$cat = intval(getRecItem("`".$tables['db_table']."`", "cat_id", "id = '{$param['row_id']}'"));
					updateRec("`".$tables['db_table']."`", "gallery_def = 0", "cat_id = '$cat'");	// clear all defaults
					updateRec("`".$tables['db_table']."`", "gallery_def = 1", "id = '{$param['row_id']}'");	// set this as default
		    		break;
				case DEF_ACTION_CLONE:
					cloneRec("`".$tables['db_table']."`", $param['row_id'], $list_params['titlefld'], $x_data);
					break;
				case DEF_ACTION_SAVEORG:
					// ranks are posted as id:rank[,id:rank]...
					$ranks = explode(",", $x_data);
					foreach($ranks as $rank){
						$rankparts = explode(":", $rank);
						updateRec("`".$tables['db_table']."`", "rank = ".$rankparts[1], "id=".$rankparts[0]);
					}
					break;
				default:
		    		break;
		    }
		}

		if(!isblank($funcrec[0]['parameters'])){
	    	// the ajaxpage array is a simulated version of the $_page object
	    	// passed to form functions so that the originating page data is preserved
	    	$ajaxpage = array(
	    		'altparams' => $list_params['altparams'],
	    		'altgroups' => $list_params['altgroups'],
	    		'addqueries' => $list_params['addqueries'],
	    		'titlefld' => $list_params['titlefld'],
	    		'imagefld' => $list_params['imagefld'],
	    		'thumbfld' => $list_params['thumbfld']
	    	);
	    	$recset = univGetQuery($list_params['query']);
	    	$cols = (array) $list_params['cols'];
	    	$colsize = (array) $list_params['colsize'];
	    	$colattr = (array) $list_params['colattr'];
	    	$totalcols = (array) $list_params['totalcols'];
	    	$buttons = (array) $list_params['buttons'];
			$colattr = prepColAttr();
			$db->table = $tables['db_table'];

			// using the listbody parameters, get the HTML that will be returned to refresh the list object
	    	ob_start();
			showListDataRows($recset, $cols, $colsize, $totalcols, $colattr, $buttons, $list_params['buttoncondindex'], $list_params['buttontagfield'], $ajaxpage);
	    	$html = ob_get_clean();
	    }

		exitAjax(true, array('alert' => $retn, 'html' => $html, 'gotopage' => $gotopage));
	    break;

    // LIST FORMS

	case 'loadorganizer':
		// builds the organizer panel dynamically rather than loading it on page start
		// this way the query that populated the list form is used
		$pagedata = getRequestVar('pagedata');
		$page_subject = $pagedata['page_subject'];
		$page_ingroup = $pagedata['page_ingroup'];
		$page_url = urldecode($pagedata['page_url']);
		$base_page_url = preg_replace("/((\?|\&).*)/i", "", $page_url);

		// get the db tables used on the calling page
		$tables = getRec("register", "db_table, db_child_table, db_parent_table", "fileurl = '$base_page_url' AND type = 'db'", "", "1", "", true);

		// get the parameters saved by the showlist operation
		$funcrec = getRec("register", "*", "`type` = 'showlist' AND fileurl = '$page_url'");
	    if(!isblank($funcrec[0]['parameters'])) {
	    	$params = json_decode($funcrec[0]['parameters'], true);
	    	$orgrec = univGetQuery($params['query']);

	    	//$outp = "<input type=\"hidden\" name=\"orgranks\" id=\"orgranks\" value=\"\" />\n";
	    	$outp = "";
			$rank = 0;
			if(count($orgrec) > 0){
				$outp.= "<p>Note: Drag boxes to rearrange them. When you're finished, click ";
				$outp.= "<input type=\"button\" class=\"action_saveorg\" value=\"Save Changes\" />\n";
				$outp.= ".</p>\n";
				$outp.= "<p>You can also edit an item by clicking on its thumbnail or title.</p>\n";
				$outp.= "<input type=\"hidden\" id=\"organize_mod\" value=\"\" />\n";
				$outp.= "<ul id=\"organize\" class=\"clearfix\">\n";
				foreach($orgrec as $orgitem){
					if(isBlank($params['imagefld'])){
						$outp.= "<li>";
						$outp.= $orgitem[$params['titlefld']];
					}else{
						// show image
						if($params['imagefld'] == "image") {
							$folder = IMG_UPLOAD_FOLDER;
							$no_pic = "";
						} else {
							$folder = THM_UPLOAD_FOLDER;
							$no_pic = "";
						}
						$photo_pic	= $orgitem[$params['imagefld']];
						$path 		= pathinfo($photo_pic);
						$filename 	= $path['basename'];
						$photo_pic	= $folder.$tables['db_table']."/".$filename;
						if ($filename == "" OR !@file_exists(SITE_PATH.$photo_pic)) $photo_pic = ADMIN_FOLDER."images/no-pic.gif";
						list($width, $height, $origwidth, $origheight) = constrainImage(SITE_PATH.$photo_pic, ORG_THM_MAX_WIDTH, ORG_THM_MAX_HEIGHT);
						$outp.= "<li style=\"width: $width\">";
						$outp.= "<a class=\"action_edit editfromorg\" rel=\"".$orgitem['id']."\" title=\"$filename\">";
						$outp.= "<img src=\"".WEB_URL.$photo_pic."\" border=\"1\" width=\"$width\" height=\"$height\">";
						$outp.= "<br/>".$orgitem[$params['titlefld']];
						$outp.= "</a>";
					}
					$outp.= "<input type=\"hidden\" id=\"rank".$rank."\" class=\"orgitem\" value=\"".$orgitem['id']."\"/>";
					$outp.= "</li>\n";
					$rank++;
				}
				$outp.= "</ul>\n";
			}else{
				$outp.= "There are no items to organize.";
			}
			$outp.= "</div>\n";

			echo $outp;
	    }
		break;
}
exit;

/*------------------------------------------------------------------------------------------*/

/**
 * Return JSON results
 * @param boolean $success
 * @param mixed $rtndata
 */
function exitAjax($success, $rtndata = null){
	echo json_encode(array('success' => $success, 'rtndata' => $rtndata));
	exit;
}

function changeTheme($curtheme, $tovalue){
	// update database settings, theme value
	updateRec("settings", "value='".$tovalue."'", "name='THEME'");
	$GLOBALS['THEME'] = $tovalue;
	// update master.css file
	//$cssfolder = substr(STYLES_FOLDER, strlen(INC_FOLDER));
	chMod2(SITE_PATH.STYLES_FOLDER, "0757");
	if(chMod2(SITE_PATH.STYLES_FOLDER."master.css", "0757")){
		if(false !== ($fcontents = file_get_contents(SITE_PATH.STYLES_FOLDER."master.css"))){
			$fcontents = str_replace($curtheme, $tovalue, $fcontents);
			file_put_contents(SITE_PATH.STYLES_FOLDER."master.css", $fcontents);
		}
	}else{
		echo "Cannot change to theme file '$tovalue'!";
	}
	displaySettingsThemesTab();
}

function deleteFileContents($folder){
	global $plugin_folder, $plugin_id;

	$rtn = '';
	if(!isblank($folder)){
		if(file_exists($folder)){
			if(false !== ($handle = opendir($folder))) {
				while (false !== ($file = readdir($handle))) {
					if($file != '.' && $file != '..'){
						if(is_dir($folder.$file)){
							$rtn .= deleteFileContents($folder.$file."/");
							$files = @scandir($folder.$file);
							if($files && count($files) <= 2){
								// folder is empty
								closedir(opendir($folder.$file));   // close all connections to it
								chmod($folder.$file, 0777);
								@rmdir($folder.$file);
							}
						}else{
							if(strpos(strtolower($folder), strtolower($plugin_folder)) !== false){
								// ensure file is below absolute parent folder
								unlink($folder.$file);
							}
						}
					}
				}
			}
			closedir($handle);

			// lastly, remove plugin folder
			if($folder == $plugin_folder){
				$files = @scandir($plugin_folder);
				var_dump($files);
				if($files && count($files) <= 2){
					chmod($plugin_folder, 0777);
					@rmdir($plugin_folder);
					if($plugin_id > 0) deleteRec("plugins", "id = '{$plugin_id}'");
				}else{
					$rtn .= 'Could not completely delete plugin folder because it still contains files/sub-folders.';
				}
			}
		}
	}
	return $rtn;
}

?>
