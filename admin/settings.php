<?php

// ---------------------------
//
// ADMIN SETTINGS
//
// ---------------------------
//

$settings_issues = array();
include ("header.php");

(strpos(SERVER, "adminsys.nav") > 0) ? $is_adminsys = true : $is_adminsys = false;

// SAVE SETTINGS

if (getRequestVar('cfgsubmit') != ""){
    $loadpage = $_SERVER['HTTP_REFERER'];
	$errstr = array();

    $settings = flattenDBArray(getRec("settings", "*", "", "", ""), "name", "value");

	// Config settings
	if(!$is_adminsys){
		$newcfg = $_POST['newcfg'];
		foreach($newcfg as $key => $value){
			$err = "";
            $value = trim($value);
			switch ($key){
				case ($key == "BUSINESS" || $key == "SITE_NAME" || $key == "DB_TABLE_PREFIX"):
					if($value == "") $err = "is missing";
					break;
				case ($key == "OWNER_EMAIL" || $key == "ADMIN_EMAIL"):
					if($value == "") $err = "is missing";
					break;
				case ($key == "MAX_IFRAME_IMGS"):
					if(intval($value) > 10) $err = "is greater than 10";
					break;
				case ($key == "IMG_MAX_WIDTH" || $key == "IMG_MAX_HEIGHT"):
					if(intval($value) > 2000) $err = "is greater than 2000px";
					break;
				case ($key == "IMG_MAX_UPLOAD_SIZE"):
					if(intval($value) > 10000) $err = "is greater than 10000Kb (10Mb)";
					break;
				case ($key == "THM_MAX_WIDTH" || $key == "THM_MAX_HEIGHT" || $key == "ORG_THM_MAX_WIDTH" || $key == "ORG_THM_MAX_HEIGHT"):
					if(intval($value) > 200) $err = "is greater than 200px";
					break;
				case ($key == "THM_MED_MAX_WIDTH" || $key == "THM_MED_MAX_HEIGHT"):
					if(intval($value) > 400) $err = "is greater than 400px";
					break;
				case ($key == "THM_MAX_UPLOAD_SIZE"):
					if(intval($value) > 1000) $err = "is greater than 1000Kb";
					break;
				case ($key == "THM_UPLOAD_FOLDER" || $key == "IMG_UPLOAD_FOLDER"):
					if($value == "" || $value == "/") $err = "is missing";
                    if(preg_match("/[\*\?\|\>\<]/i", $value)) $err = "contains invalid characters.";
					break;
			}
			if($err != ""){
				$value = $config[$key];
				$errstr[] = str_replace("_", " ", $key)." ".$err;
			}else{
                if(isset($settings[$key])){
                    updateRec("settings", "value = '$value'", "name = '$key'");
                }else{
                    insertRec("settings", "name, value", "'$key', '$value'");
                }
			}
		}

        $key = 'IMG_LOGIN_LOGO';
        if($_FILES['newcfg']['name'][$key] != ''){
            include (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."filehandler.php");
            $folder = SITE_PATH.ADMIN_FOLDER.IMG_UPLOAD_FOLDER."logo/";
            chmod2($folder);
            list($err, $img) = uploadImage('newcfg[IMG_LOGIN_LOGO]', '', '', '', $folder, $folder, array('jpg', 'png', 'gif'), array("size" => THM_MAX_UPLOAD_SIZE, "w" => 250, "h" => 100));
			if($err){
				$value = $config[$key];
				$errstr[] = str_replace("_", " ", $key)." ({$err[0]})";
			}else{
				updateRec("settings", "value = '$img'", "name = '$key'");
			}
        }

    	// Theme attributes
		$cssdata = rebuildCSSFromThemeAttrs(getIfSet($_POST['themeattr']), getIfSet($_POST['themepalettes']));
        if($cssdata != false){
            chmod2(SITE_PATH.THEME_FOLDER.$GLOBALS['THEME']."/theme.css", "0757");
            file_put_contents(SITE_PATH.THEME_FOLDER.$GLOBALS['THEME']."/theme.css", $cssdata);
        }

        // Users
        include (SITE_PATH.ADMIN_FOLDER.PLUGINS_FOLDER."cipher/cipherlib.php");
        $user_id_array = $_POST['users_id'];
        $user_name_array = $_POST['users_name'];
        $user_pass_array = $_POST['users_pass'];
        $user_email_array = $_POST['users_email'];
        $user_level_array = $_POST['users_level'];
        $user_active_array = $_POST['users_active'];
        $user_firstname_array = $_POST['users_firstname'];
        $user_lastname_array = $_POST['users_lastname'];
        $user_facebook_array = $_POST['users_facebook_link'];
        $user_twitter_array = $_POST['users_twitter_link'];
        $user_google_array = $_POST['users_google_plus_link'];

        if(is_array($user_id_array)){
            foreach($user_id_array as $key => $user_id){
                $user_ready_to_save = true;
                $user_name = getIfSet($user_name_array[$key]);
                $user_pass = getIfSet($user_pass_array[$key]);
                $user_email = getIfSet($user_email_array[$key]);
                $user_level = getIntValIfSet($user_level_array[$key]);
                $user_active = ((!isBlank($user_active_array[$key])) ? 1 : 0);
                $user_firstname = getIfSet($user_firstname_array[$key]);
                $user_lastname = getIfSet($user_lastname_array[$key]);
                $user_facebook = getIfSet($user_facebook_array[$key]);
                $user_twitter = getIfSet($user_twitter_array[$key]);
                $user_google = getIfSet($user_google_array[$key]);

                $flds = array();
                if (!isBlank($user_name)) {
                    if(getRecItem("admin_accts", "username", "username = '$user_name' and id != $user_id") == ""){
                        $flds['username'] = $user_name;
                    }else{
                        $errstr[] = "The USERNAME, '$user_name', already exists";
                        $user_ready_to_save = false;
                    }
                }else{
                    $errstr[] = "The USERNAME is required.";
                    $user_ready_to_save = false;
                }

                if (!isBlank($user_pass)) {
                    // generate parallel keys
                    $privateKey = genPrivateKey();
                    $ciphertext = encrypt($user_pass, $privateKey);
                    //$plaintext = decrypt($ciphertext, $privateKey);
                    $flds['password'] = md5($user_pass);
                    $flds['phash'] = $ciphertext;
                    $flds['pcle'] = $privateKey;
                }else{
                    //$errstr[] = "The PASSWORD was not provided for user '$user_name'.";
                    //$user_ready_to_save = false;
                }

                if(!isBlank($user_email)) {
                    $flds['email'] = $user_email;
                }else{
                    $errstr[] = "The EMAIL was not provided for user '$user_name'.  It is required if you forget your password.";
                    $user_ready_to_save = false;
                }

                if($user_ready_to_save){
                    $flds['activated'] = $user_active;
                    $flds['level'] = $user_level;
                    $flds['firstname'] = $user_firstname;
                    $flds['lastname'] = $user_lastname;
                    $flds['facebook_link'] = $user_facebook;
                    $flds['twitter_link'] = $user_twitter;
                    $flds['google_plus_link'] = $user_google;

                    if($user_id > 0){
                        if($user_level != intval(getRecItem("admin_accts", "level", "id = '$user_id'"))) $loadpage = WEB_URL.ADMIN_FOLDER."admlogin.php?admsubmit=Logout";
                        $fldlist = "";
                        foreach($flds as $key => $fld) $fldlist .= (($fldlist != '') ? ', ':'').$key."='$fld'";
                        updateRec("admin_accts", $fldlist, "id = $user_id");
                        if(getUserID() == $user_id) $_SESSION['admuserlevel'] = $user_level;
                    }else{
                        $fldlist = "";
                        $vallist = "";
                        foreach($flds as $key => $fld){
                            $fldlist .= (($fldlist != '') ? ', ' : '').$key;
                            $vallist .= (($vallist != '') ? ', ' : '')."'".$fld."'";
                        }
                        insertRec("admin_accts", $fldlist, $vallist);
                    }
                }
            }
        }

    	// Htaccess content
		if($_POST['ht_mod1'] != '' || $_POST['ht_mod2'] != ''){
			// first backup the current file
			$filename = SITE_PATH.time().".htaccess";
			if(copy(SITE_PATH.".htaccess", $filename)){
                $ht_sect = array();
                $ht_sect['301'][] = "# ----- 301 Redirects";
                for($i=0; $i<count($_POST['ht_301_to']); $i++){
                    $val = "redirect 301 ".$_POST['ht_301_from'][$i]." ".$_POST['ht_301_to'][$i];
                    if($_POST['ht_301_active'][$i] != 1) $val = "#".$val;
                    $ht_sect['301'][] = $val;
                }

                $ht_sect['seo'][] = "# ----- SEO";
                for($i=0; $i<count($_POST['ht_seo_to']); $i++){
                    $val = "RewriteRule ".$_POST['ht_seo_from'][$i]." ".$_POST['ht_seo_to'][$i];
                    if($_POST['ht_seo_active'][$i] != 1) $val = "#".$val;
                    $ht_sect['seo'][] = $val;
                }

                $ht_sect['www'][] = "# ----- WWW Rewrites";
                $ht_sect['www'][] = $_POST['ht_www_data'];

                $ht_sect['img'][] = "# ----- Image Hotlinking";
                $ht_sect['img'][] = $_POST['ht_img_data'];

                $ht_contents = $_POST['ht_line_data'];
                foreach($ht_sect as $key => $ht_elem){
                    // put the sections back into the content where they came from
                    if(strpos($ht_contents, "#< {$key} >") !== false){
                        $ht_contents = str_replace("#< {$key} >", join(PHP_EOL, $ht_elem), $ht_contents);
                    }
                }

                $ht_contents = str_replace(array('\\\\', '\\\/', '\\"', '\"', '\\\.', "\'"), array("\\", '\/', '"', '"', "\.", "'"), $ht_contents);

                if(!file_put_contents(SITE_PATH.".htaccess", $ht_contents)){
                    $errstr[] = "Changes to ".SITE_PATH.".htaccess were not saved!";
                }
			}else{
				$errstr[] = "Cannot backup ".SITE_PATH.".htaccess!";
			}
		}

        // Visibility
		$newbots = getRequestVar('ua');
		$newpath = getRequestVar('da');
		$currules = getRequestVar('currules');
		$newrules = "# Standard robots.txt
# System folders
User-agent: *
Disallow: /".ADMIN_FOLDER."
Disallow: /".INC_FOLDER."
Disallow: /".PLUGINS_FOLDER."
Disallow: /".CORE_FOLDER."
Disallow: /".CUSTOM_FOLDER."
Disallow: /".JS_FOLDER."
# End of System folders
		";
		if(is_array($newbots)){
			foreach($newbots as $rkey => $bot) {
				if($bot != ''){
					$newrules .= "\nUser-agent: ".$bot."\n";
					$newpaths = explode("\n", $newpath[$rkey]);
					foreach($newpaths as $path){
						$newrules .= "Disallow: ".$path."\n";
					}
				}
			}
		}

		if($currules != $newrules){
			// first backup the current file
			chmod(SITE_PATH."robots.txt", 0777);
			if(copy (SITE_PATH."robots.txt", SITE_PATH.ADMIN_FOLDER.REV_FOLDER."robots.".date("YmdHis").".txt")){
				file_put_contents(SITE_PATH."robots.txt", $newrules);
			}else{
				$errstr[] = "Cannot backup or modify robots.txt!";
			}
			chmod(SITE_PATH."robots.txt", 0644);
		}

        // data aliases
		foreach($_POST['dataalias_meta'] as $table => $meta){
            updateDataAliasMeta($table, $meta);
        }
	}

	if(count($errstr) > 0){
        $nl = "\\r\\n";
		print "<script type=\"text/javascript\">alert(\"One or more problems occurred:{$nl}{$nl}- ".join("$nl- ", $errstr)."{$nl}{$nl}The value".((count($errstr) == 1) ? ' has' : 's have')." been reset.\");</script>";
	}else{
		gotoPage($loadpage);
	}
}

// SETTINGS FUNCTIONS

function showResetLink($key1, $key2 = null){
	global $defcfg, $config, $settings_errors;

	$resetimg = "<img src=\"".WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/icons/reset.png\" border=\"0\" alt=\"Reset to default\" title=\"Reset to default\" style=\"vertical-align: middle\">";
	$undoimg  = "<img src=\"".WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/icons/undo.png\" border=\"0\" alt=\"Undo\" title=\"Undo\" style=\"vertical-align: middle\">";

	if($key2 == null){
		print "<input type=\"hidden\" id=\"reset_{$key1}\" value=\"".$defcfg[$key1]."\"/>";
		print "<input type=\"hidden\" id=\"undo_{$key1}\" value=\"".constant($key1)."\"/>";
		print "&nbsp;&nbsp;<a href=\"#\" onclick=\"$('#$key1').val($('#reset_{$key1}').val());\">$resetimg</a>";
		print "&nbsp;<a href=\"#\" onclick=\"$('#$key1').val($('#undo_{$key1}').val());\">$undoimg</a>";
	}else{
		print "<input type=\"hidden\" id=\"reset_{$key1}\" value=\"".$defcfg[$key1]."\"/>";
		print "<input type=\"hidden\" id=\"undo_{$key1}\" value=\"".constant($key1)."\"/>";
		print "<input type=\"hidden\" id=\"reset_{$key2}\" value=\"".$defcfg[$key2]."\"/>";
		print "<input type=\"hidden\" id=\"undo_{$key2}\" value=\"".constant($key2)."\"/>";
		print "&nbsp;&nbsp;<a href=\"#\" onclick=\"$('#$key1').val($('#reset_{$key1}').val()); $('#$key2').val($('#reset_{$key2}').val());\">$resetimg</a>";
		print "&nbsp;<a href=\"#\" onclick=\"$('#$key1').val($('#undo_{$key1}').val()); $('#$key2').val($('#undo_{$key2}').val());\">$undoimg</a>";
	}
}

function showBasicResetLink($key1, $origkey){
	$resetimg = "<img src=\"".WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/icons/reset.png\" border=\"0\" alt=\"Reset to default\" title=\"Reset to default\" style=\"vertical-align: middle\">";
	//$undoimg  = "<img src=\"".WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/icons/undo.png\" border=\"0\" alt=\"Undo\" title=\"Undo\" style=\"vertical-align: middle\">";

	print "&nbsp;&nbsp;<a href=\"#\" onclick=\"$('#$key1').val($('#".$origkey."').val())\">$resetimg</a>";
}

function showSettingsTimeZoneData($readonly = ""){
    global $configs;

    $tz_array = getTimezones();
    ?>
    <select name="newcfg[TIMEZONE]" id="TIMEZONE"<?=$readonly?>>
        <?
        $tzgroup = "";
        foreach($tz_array as $tzkey => $tz){
            $sel = (($tzkey == $configs['TIMEZONE']) ? ' selected="selected"' : '');
            $tzkey_parts = explode("/", $tzkey);
            if($tzgroup != $tzkey_parts[0]) {
                echo (($tzgroup != '') ? '</optgroup>' : '').'<optgroup label="'.ucwords($tzkey_parts[0]).'">'.PHP_EOL;
                $tzgroup = $tzkey_parts[0];
            }
            echo '<option value="'.$tzkey.'"'.$sel.'>'.$tz.'</option>'.PHP_EOL;
        }
        ?>
    </select> <? showResetLink('TIMEZONE')?><br/>
    [Server Time: <?=date("Y-m-d g:i:s a"); ?>]
    <?
}

function showSettingsUsersList($readonly){
	global $_users;
	?>
	<p class="settingsactions">
	<?
    $action = array('checkbox' => array(
    						'id' => 'user_bulkcheck'),
    				'menu' => array(
    						'id' => 'user_bulkopt',
	    					'sel' => '',
	    					'options' => array('deactivate' => '(De)activate', 'delete' => 'Delete')),
    				'buttons' => array(
    						'user_bulkact' => 'Go', 'admnewuser' => 'Add User')
    			   );
	if($readonly == "") showSettingsActions(0, $action);
	?>
    </p>
    <p class="user_you">Your account (Cannot be deleted by you)</p>
    <?
    $users_array = getRec("admin_accts", "*", "", "username", "");
    $user_levels = $_users->roles;
    $user_count  = 0;
    $user_admins = 0;
    $current_loggedin_user_id = getUserID();
    $current_loggedin_user_level = getUserLevel();
    foreach($users_array as $this_user){
        $this_user_id = $this_user['id'];
        $user_count++;
        if($this_user['level'] <= ADMLEVEL_SYSADMIN && $this_user['activated'] == 1) $user_admins++;
        $current_user_can_edit_user = ($current_loggedin_user_level < $this_user['level'] || $current_loggedin_user_level == ADMLEVEL_DEVELOPER || $current_loggedin_user_id == $this_user_id) && ($readonly == "");
    ?>
    <div class="user_row<?=(($this_user_id == $current_loggedin_user_id) ? ' user_you' : '')?>">
        <input type="hidden" class="user_id" name="users_id[<?=$user_count?>]" value="<?=$this_user_id?>" />
        <div class="user_leftside">
        	<? if($current_user_can_edit_user) { ?><input type="checkbox" name="users_check[<?=$user_count?>]" id="users_check<?=$user_count?>" class="user_checks" /><? } ?>
            <span class="user_name<?=(($this_user['activated'] == 0) ? " notactive" : "")?>"><?=ucwords_smart($this_user['username'])?></span><br/>
            <div class="user_actions">
            	<?
                if($current_user_can_edit_user) {
                    if(userIsAllowedTo('activate_user')){?><a href="#" class="user_act"><?=(($this_user['activated'] == 1) ? "Dea" : "A")?>ctivate</a> | <? }
                    if($this_user_id != $current_loggedin_user_id && userIsAllowedTo('delete_user')){?><a href="#" class="user_del">Delete</a><br/><? }
                    if($this_user_id == $current_loggedin_user_id && userIsAllowedTo('edit_profile')){?><a href="#" class="user_editprofile">View Profile</a><? }
                }
                ?>
            </div>
            <input type="hidden" name="users_active[<?=$user_count?>]" id="users_active<?=$user_count?>" value="<?=$this_user['activated']?>" />
            <input type="hidden" name="users_cur[<?=$user_count?>]" class="user_cur" value="<?=(($this_user_id == $current_loggedin_user_id) ? 'yes' : '');?>" />
        </div>
        <div class="user_rightside">
            <? if($current_user_can_edit_user) { ?>
            <span>New Name:</span> <input type="text" name="users_name[<?=$user_count?>]" id="users_name<?=$user_count?>" size="20" value="<?=$this_user['username']?>"/><br/>
            <span>New Password:</span> <input type="password" name="users_pass[<?=$user_count?>]" id="users_pass<?=$user_count?>" size="20" value=""/><br/>
            <span>Email Address:</span> <input type="text" name="users_email[<?=$user_count?>]" id="users_email<?=$user_count?>" size="30" value="<?=$this_user['email']?>"/><br/>
            <span>Role:</span>
            <select name="users_level[<?=$user_count?>]" id="users_level<?=$user_count?>">
            <? foreach($user_levels as $key => $lvl){
                if($lvl >= $this_user['level'] || $lvl >= $current_loggedin_user_level){
                    $sel = (($this_user['level'] == $lvl) ? ' selected="selected"' : '');
                    echo '<option value="'.$lvl.'"'.$sel.'>'.ucwords(strtolower(substr($key, 9))).'</option>';
                }
            } ?>
            </select>
            <div class="user_profile">
            	<span><strong>Profile</strong></span><br/>
            	<span>First Name:</span> <input type="text" name="users_firstname[<?=$user_count?>]" size="20" value="<?=$this_user['firstname']?>" /><br/>
            	<span>Last Name:</span> <input type="text" name="users_lastname[<?=$user_count?>]" size="20" value="<?=$this_user['lastname']?>" /><br/>
            	<span>Facebook Link:</span> <input type="text" name="users_facebook_link[<?=$user_count?>]" size="40" value="<?=$this_user['facebook_link']?>" /><br/>
            	<span>Twitter Link:</span> <input type="text" name="users_twitter_link[<?=$user_count?>]" size="40" value="<?=$this_user['twitter_link']?>" /><br/>
            	<span>Google+ Link:</span> <input type="text" name="users_google_plus_link[<?=$user_count?>]" size="40" value="<?=$this_user['google_plus_link']?>" /><br/>
            </div>
            <? } else { ?>
            <span>Name:</span>&nbsp;<?=$this_user['username']?><input type="hidden" name="users_name[<?=$user_count?>]" id="users_name<?=$user_count?>" size="20" value="<?=$this_user['username']?>"/><br/>
            <span>Email Address:</span>&nbsp;<?=$this_user['email']?><input type="hidden" name="users_email[<?=$user_count?>]" id="users_email<?=$user_count?>" size="30" value="<?=$this_user['email']?>"/><br/>
            <span>Role:</span>&nbsp;
             	<?
             	$key = array_search($this_user['level'], $user_levels);
              	echo trim(ucwords(strtolower(substr($key, 9))))."\n";
            } ?>
        </div>
    </div>
    <? } ?>
    <input type="hidden" id="users_count" value="<?=$user_count?>" />
    <input type="hidden" id="users_admins" value="<?=$user_admins?>" />
    <?
}

function showSettingsMenusforWebsite(){

}

function showSettingsMenusforAdmin(){
    $menus = getAdminMenus();

    // build the initial samples
    $toplevel = "";
    $sublevel = "";
    foreach($menus as $key => $menu){
        $chosen = (($toplevel == "") ? "chosen" : "unchosen");
        if($sublevel == "" && $toplevel == "" && !is_null($menu['childmenus'])){
            $sublevel .= "<li class=\"{$chosen}\" id=\"setsubmenu_".key($menu['childmenus'])."\"><a href=\"#\" class=\"adminmenu_subelem\" rel=\"{$key}\" title=\"Click to edit; drag to re-order\">{$menu['childmenus']['title']}</a></li>\n";
        }
        $toplevel .= "<li class=\"{$chosen}\" id=\"setmenu_{$key}\"><a href=\"#\" class=\"adminmenu_topelem\" rel=\"{$key}\" title=\"Click to edit; drag to re-order\">{$menu['title']}</a></li>\n";
    }
    $toplevel = "<ul id=\"adminmenu_navigation\">$toplevel</ul>\n";
    $sublevel = "<ul id=\"adminmenu_subnavigation\">$sublevel</ul>\n";
    reset($menus);
    ?>
    <div id="adminmenu_sample">
        <?=$toplevel?>
        <div class="adminmenu_actions">
            <a href="#" id="adminmenu_addtop" title="Add new menu to top level"><span>+</span></a>
        </div>
        <?=$sublevel?>
        <div class="adminmenu_actions">
            <a href="#" id="adminmenu_addsub" title="Add new menu to sub level"><span>+</span></a>
        </div>
    </div>
    <div id="adminmenu_editor"><?=getAdminMenuEditorHTML(key($menus), "", "top")?></div>
    <?
}

function showSettingsAliases($readonly = ""){
    global $_system, $_page;

	?><p><?= SYS_NAME; ?> offers many ways to customize URLs for pages and data.  Beyond the standard query-based URL format, which might resemble <i><?=WEB_URL?>page.php?id=1)</i>, the
	system also allows:

	<p>
		<i class="hovertip" alt="Page organization using a familiar folder-format hierarchy without ugly file extensions or queries.">Page Aliases</i>,
		<i class="hovertip" alt="Data can be organized by category, year, date, section, zone, or virtually anything else you can think of.">Data Aliases</i>,
		<i class="hovertip" alt="If you are used to systems such as Code Igniter&reg; or Joomla&trade;, you will be at home with this format.  Data is accessed via a controller/model/method structure.">Controllers</i>,
		<i class="hovertip" alt="Of course, <strong><?= SYS_NAME; ?></strong> doesn't prevent you from using mod-rewrites.  Actually, it makes it easier with an editor right here in the Settings.">Mod-Rewrites</i>,
		<i class="hovertip" alt="Lastly, files can be served directly without interference.">Direct File</i>
	</p>

	<p>This section will concentrate on <i>Data Aliases</i>.  Need help preparing your Data Alias Metas?  Don't worry, <a href="#" class="triggerhelp">here's some guidance</a>.</p>

	<?
	$aliases = $_system->dataaliases;
    $data_tables = $_system->datatables;
    // replace the standard alternate key with the table name
    foreach($aliases as $key => $arry){
        $tkey = DB_TABLE_PREFIX.$arry['db_table'];
        unset($aliases[$key]);
        $aliases[$tkey] = $arry;
    }
    ?>
    <div class="setlabel"><h3 class="header">Table</h3></div>
    <div class="setdata"><h3 class="header">Alias Meta</h3></div>
    <?
	foreach($data_tables as $table){
        $t = str_replace(DB_TABLE_PREFIX, "", $table);
        $notice = '';
        $notice_color = ' gray';
        $rel = '';
        if(isset($aliases[$table])){
            if($aliases[$table]['error'] == ''){
                if($aliases[$table]['iscategory'] == 1) $notice = 'Valid Category meta';
            }else{
                $notice = $aliases[$table]['error'].'...';
                $notice_color = ' red';
            }
            list($rel, ) = getDataAliasPatternFromMeta($aliases[$table]['meta']);
        }
        ?>
        <div class="setlabel"><?=$table?></div>
        <div class="setdata">
            <input type="text" name="dataalias_meta[<?=$t?>]" class="dataalias_meta bigfldsize" value="<?=getIfSet($aliases[$table]['meta'])?>" rel="<?=$table?>" title="<?=$rel?>" />
            <span class="dataalias_notice<?=$notice_color?>"><?=$notice?></span>
        </div>
	<?
	}
}

function showSettingsLinksArea($readonly = ""){
    ?>
        <p><b>CAUTION: Invalid settings may cause page loading or server problems!</b>  If you are unable to access the site, the only recourse will be to modify the .htaccess file directly.</p>
    <?
    $cur_htfile = SITE_PATH.".htaccess";
    $ht_linedata = array();
    $ht_sectdata = array("301" => array(), "seo" => array(), "www" => array(), "img" => array());
    $ht_section = "";

    $fp = @fopen($cur_htfile, "r");
    if($fp){
        $domain = str_replace(array(".", "www"), array("\.", ""), $_SERVER['HTTP_HOST']);
        while(($line = fgets($fp)) !== false){
            $line = trim(addslashes($line));
            if(substr($line, 0, 8) == '# ----- '){
                switch(strtolower(substr($line, 8))){
                    case "301 redirects":
                        $ht_section = "301";
                        break;
                    case "seo":
                        $ht_section = "seo";
                        break;
                    case "www rewrites":
                        $ht_section = "www";
                        $ht_sectdata['www']['set_www'] = 'RewriteCond %{HTTP_HOST} ^([a-z.]+)?'.$domain.'$ [NC]
RewriteCond %{HTTP_HOST} !^www\. [NC]
RewriteRule .? http://www.%1'.$_SERVER['HTTP_HOST'].'%{REQUEST_URI} [R=301]';
                        $ht_sectdata['www']['unset_www'] = 'RewriteCond %{HTTP_HOST} ^www\.([a-z.]+)?'.$domain.'$ [NC]
RewriteRule .? http://%1'.$_SERVER['HTTP_HOST'].'%{REQUEST_URI} [R=301]';
                        $ht_sectdata[$ht_section]['www_active'] = true;
                        break;
                    case "image hotlinking":
                        $ht_section = "img";
                        $ht_sectdata['img']['basic_img'] = 'RewriteCond %{HTTP_REFERER} !^$
RewriteCond %{HTTP_REFERER} !^http://(www\.)?'.$domain.'/.*$ [NC]
RewriteRule \.(gif|jpg|png)$ - [F]';
                        $ht_sectdata['img']['adv_img'] = 'RewriteCond %{REQUEST_FILENAME} .*(jpg|jpeg|gif|png)$ [NC]
RewriteCond %{HTTP_REFERER} !^$
RewriteCond %{HTTP_REFERER} !'.$domain.' [NC]
RewriteCond %{HTTP_REFERER} !google\. [NC]
RewriteCond %{HTTP_REFERER} !search\?q=cache [NC]
RewriteRule (.*) /'.substr(VHOST, 1).'_imghl.php?pic=$1';
                        $ht_sectdata[$ht_section]['img_active'] = true;
                        break;
                    default:
                        $ht_section = '';
                        $ht_linedata[] = $line;
                        break;
                }
                if($ht_section != '') $ht_linedata[] = "#< ".$ht_section." >";
            }elseif($line != '' && $ht_section != ''){
                $ht_active = true;
                if(substr($line, 0, 1) == '#') {
                    $ht_active = false;
                    if($ht_section == '301' || $ht_section == 'seo') $line = substr($line, 1);
                }
                switch($ht_section){
                    case "301":
                        $chunks = preg_split("/[\s]+/i", $line);
                        if(strtolower($chunks[0]) == 'redirect' && $chunks[1] == '301' && count($chunks) >= 4){
                            $ht_sectdata[$ht_section][] = array("from" => $chunks[2], "to" => $chunks[3], "flags" => $chunks[4], "active" => $ht_active);
                        }
                        break;
                    case "seo":
                        $chunks = preg_split("/[\s]+/i", $line);
                        if(strtolower($chunks[0]) == 'rewriterule' && count($chunks) >= 3){
                            $ht_sectdata[$ht_section][] = array("from" => $chunks[1], "to" => $chunks[2], "active" => $ht_active);
                        }
                        break;
                    case "www":
                        $ht_sectdata[$ht_section]['www_data'] .= ((!isBlank($ht_sectdata[$ht_section]['www_data'])) ? PHP_EOL : "").stripslashes($line);
                        $ht_sectdata[$ht_section]['www_active'] &= $ht_active;
                        break;
                    case "img":
                        $ht_sectdata[$ht_section]['img_data'] .= ((!isBlank($ht_sectdata[$ht_section]['img_data'])) ? PHP_EOL : "").stripslashes($line);
                        $ht_sectdata[$ht_section]['img_active'] &= $ht_active;
                        break;
                }
            }else{
                $ht_linedata[] = $line;
            }
        }
        fclose($fp);
        if($ht_linedata[0] == '') unset($ht_linedata[0]);
    }
    ?>
        <textarea id="ht_line_data" name="ht_line_data" style="display: none"><?=join("\n", $ht_linedata);?></textarea>
        <input type="hidden" id="ht_mod1" name="ht_mod1" value="" />
        <input type="hidden" id="ht_mod2" name="ht_mod2" value="" />

        <h3 class="header">Custom SEO Links  <a href="http://www.yourhtmlsource.com/sitemanagement/urlrewriting.html" target="_blank"><img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN?>images/icons/link.png" title="Learn more about SEO page redirects"/></a></h3>
        <p>These page redirects enhance page links by making URLs more user-friendly and maximize search engine ranking.</p>
        [RewriteRule <i>url_from</i> <i>url_to</i>]
        <? if($readonly == '') {?><input type="button" id="ht_seo_addrow" value="Add New" /><? } ?>
        <br/>
        <?
        $n = 0;
        foreach($ht_sectdata['seo'] as $line){
        	echo "<div class=\"ht_seo\">\n";
            echo "<input{$readonly} type=\"checkbox\" name=\"ht_seo_active[$n]\" class=\"ht_seo_active\" value=\"1\"".(($line['active'] == 1) ? ' checked="checked" rel="y"' : ' rel=""')." title=\"Check to enable\" /> ";
            echo "<input{$readonly} type=\"text\" name=\"ht_seo_from[$n]\" class=\"ht_seo_from\" size=\"28\" value=\"{$line['from']}\" rel=\"{$line['from']}\" /> ";
            echo "<input{$readonly} type=\"text\" name=\"ht_seo_to[$n]\" class=\"ht_seo_to\" size=\"28\" value=\"{$line['to']}\" rel=\"{$line['to']}\" /> ";
            if($readonly == "") echo "<a href=\"#\" class=\"ht_delete\" rel=\"$n\"><img src=\"".WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/icons/delete.png\" title=\"Delete\" /></a> ";
            echo "</div>\n";
            $n++;
        }
        ?>
        <h3 class="header">301 Redirects  <a href="" target="_blank"><img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN?>images/icons/link.png" title="Learn more about 301 redirects"/></a></h3>
        <p>A 301 Redirect instructs the server to access a different web page when a specific address is requested.</p>
        [Redirect 301 <i>url_from</i> <i>url_to</i>]
        <? if($readonly == '') {?><input type="button" id="ht_301_addrow" value="Add New" /><? } ?>
        <br/>
        <?
        $n = 0;
        foreach($ht_sectdata['301'] as $line){
        	echo "<div class=\"ht_301\">\n";
            echo "<input{$readonly} type=\"checkbox\" name=\"ht_301_active[$n]\" class=\"ht_301_active\" value=\"1\"".(($line['active'] == 1) ? ' checked="checked" rel="y"' : ' rel=""')." title=\"Check to enable\" /> ";
            echo "<input{$readonly} type=\"text\" name=\"ht_301_from[$n]\" class=\"ht_301_from\" size=\"28\" value=\"{$line['from']}\" rel=\"{$line['from']}\" /> ";
            echo "<input{$readonly} type=\"text\" name=\"ht_301_to[$n]\" class=\"ht_301_to\" size=\"28\" value=\"{$line['to']}\" rel=\"{$line['to']}\" /> ";
            if($readonly == "") echo "<a href=\"#\" class=\"ht_delete\" rel=\"$n\"><img src=\"".WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN."images/icons/delete.png\" title=\"Delete\" /></a> ";
            echo "</div>\n";
            $n++;
        }
        ?>

        <h3 class="header">WWW Checking  <a href="" target="_blank"><img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN?>images/icons/link.png" title="Learn more about www rewriting"/></a></h3>
        <p>Choose one of the provided options that will determine how to handle www prefixes.</p>
        <span class="ht_toggle" id="ht_www_toggle">Less</span>
        <textarea<?=$readonly?> id="ht_www_data" name="ht_www_data" cols="70" rows="5"><?=$ht_sectdata['www']['www_data'];?></textarea>
        <?
        $www1 = "";
        $data = strtolower($ht_sectdata['www']['www_data']);
        $sel = ' selected="selected"';
        if(strpos($data, strtolower($ht_sectdata['www']['set_www'])) !== false){
            $www1 = "set_www";
        }elseif(strpos($data, strtolower($ht_sectdata['www']['unset_www'])) !== false){
            $www1 = "unset_www";
        }
        foreach($ht_sectdata['www'] as $key => $val) echo '<input type="hidden" id="'.$key.'" value="'.$val.'" />'.PHP_EOL;
        ?>
        <p>
            <select id="ht_www1" name="ht_www1">
                <option value="">Original/Customized</option>
                <option value="disable"<?=((!$ht_sectdata['www']['www_active']) ? $sel : '')?>>Disable 'www' checking</option>
                <option value="set_www"<?=(($www1 == "set_www") ? $sel : '')?>>Add 'www' to URLs</option>
                <option value="unset_www"<?=(($www1 == "unset_www") ? $sel : '')?>>Remove 'www' from URLs</option>
            </select>
        </p>

        <h3 class="header">Image Hotlinking  <a href="" target="_blank"><img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN?>images/icons/link.png" title="Learn more about preventing image hotlinking"/></a></h3>
        <p>Prevent web designers from directly requesting images, located on this server, for use on their sites by activating the following option.
        Note: you may need to disable image hotlinking before moving this site to another domain or images will not be displayed.</p>
        <span class="ht_toggle" id="ht_img_toggle">Less</span>
        <textarea<?=$readonly?> id="ht_img_data" name="ht_img_data" cols="70" rows="5"><?=$ht_sectdata['img']['img_data'];?></textarea>
        <?
        $img1 = "";
        $data = strtolower($ht_sectdata['img']['img_data']);
        $sel = ' selected="selected"';
        if(strpos($data, strtolower($ht_sectdata['img']['basic_img'])) !== false){
            $img1 = "basic_img";
        }elseif(strpos($data, strtolower($ht_sectdata['img']['adv_img'])) !== false){
            $img1 = "adv_img";
        }
        foreach($ht_sectdata['img'] as $key => $val) echo '<input type="hidden" id="'.$key.'" value="'.$val.'" />'.PHP_EOL;
        ?>
        <p>
            <select id="ht_img1" name="ht_img1">
                <option value="">Original/Customized</option>
                <option value="disable"<?=((!$ht_sectdata['img']['img_active']) ? $sel : '')?>>Disable image hotlink blocking</option>
                <option value="basic_img"<?=(($img1 == "basic_img") ? $sel : '')?>>Basic protection</option>
                <option value="adv_img"<?=(($img1 == "adv_img") ? $sel : '')?>>Advanced protection</option>
            </select>
        </p>
    <?
}

function showSettingsSiteVisibilityArea(){
	$files = scandir(SITE_PATH.ADMIN_FOLDER.REV_FOLDER, 1);
    ?>
        <h3 class="header">Access Rules List:</h3>
        <p>The robots.txt file contains a set of rules that dictate which automated search engine scripts, colloquially called 'robots' or 'spiders'
        are allowed to index parts or the entire site.  You can learn more about how to construct the robots.txt file at
        <a href="http://www.outfront.net/tutorials_02/adv_tech/robots.htm" target="_blank">www.outfront.net/tutorials_02/adv_tech/robots.htm</a>.</p>
        <p>Simply, select an agent (or * for all) and enter the list of folders, one per line, starting and ending with a forward-slash (/), that you <em>do not want</em> the agent to see.  System folders are already blocked.</p>
		<? if (count($files) > 2){ ?>
    	<div class="setlabel"><strong>Previous Versions</strong></div>
    	<div class="setdata">
    		<select id="robots_revfile">
    			<?
    			foreach($files as $file) if($file != '..' && $file != '.') echo '<option value="'.REV_FOLDER.$file.'">'.$file.'</option>';
    			?>
    		</select>
    		&nbsp;<input type="button" id="robots_revision" value="Revert" />
    	</div>
		<? } ?>
    <?
    echo "<div class=\"setlabel\"><strong>Agent</strong> <span class=\"hovertip\" alt=\"List of search crawlers, bots, scrapers, and spiders that you can select\">[?]</span></div>\n";
    echo "<div class=\"setdata\"><strong>Affected Path (eg. /, /folder/, /events/today/)</strong> <span class=\"hovertip\" alt=\"The path(s) that will be blocked\">[?]</span></div>\n";
    $bots = array(	""=>"",
                    "*" => "All",
                    "AbachoBOT"=>"Abacho",
                    "Acoon"=>"Acoon",
                    "AESOP_com_SpiderMan"=>"Aesop",
                    "ah-ha.comcrawler"=>"Ah-ha",
                    "ia_archiver"=>"Alexa",
                    "FAST-WebCrawler"=>"AlltheWeb",
    				"Scooter"=>"<b>AltaVista",
                    "Atomz"=>"Atomz",
                    "DeepIndex"=>"DeepIndex",
    				"Arachnoidea"=>"Euroseek",
    				"EZResult"=>"EZResults",
                    "Gigabot"=>"Gigablast",
                    "Googlebot"=>"<b>Google",
                    "KIT-Fireball/2.0"=>"Fireball 2.0 (GermanSEatwww.fireball.de)",
                    "Slurp.so/1.0"=>"Inktomi 1.0",
                    "Slurp/2.0"=>"Inktomi 2.0",
                    "Slurp/2.0j"=>"Inktomi 2.0j",
                    "Slurp/3.0"=>"<b>Inktomi 3.0",
    				"LNSpiderguy"=>"Lexis-Nexis",
                    "MantraAgent"=>"LookSmart",
                    "HenryTheMiragoRobot"=>"Mirago",
    				"MSN"=>"<b>MSN (MicrosoftPrototypeCrawler)",
                    "NationalDirectory-SuperSpider"=>"<b>National Directory",
                    "Openbot"=>"Openfind",
                    "Fido"=>"PlanetSearch",
    				"Openfindpiranha,Shark"=>"Openfind",
                    "Scrubby"=>"ScrubTheWeb",
                    "Fluffythespider"=>"SearchHippo",
    				"Teoma_agent1"=>"Teoma",
                    "ESISmartSpider"=>"TravelFinder",
    				"UKSearcherSpider"=>"UKSearcher",
    				"appie"=>"Walhello",
    				"WebCrawler"=>"WebCrawler",
                    "Nazilla"=>"Websmostlinked",
    				"Winona"=>"WhatUSeek",
                    "ZyBorg"=>"Wisenut",
                    "Gulper"=>"Yuntis",
    			);

    $curfilename = SITE_PATH."robots.txt";
    $fp = @fopen($curfilename, "r");
    if($fp){
        $ua = array();
        $da = array();
        $indx = -1;
        $sysrules = false;
        $currobot = "";
        while($line = fgets($fp)){
            $line = trim($line);
            $currobot .= $line."\n";
            if($line == "# System folders"){
            	$sysrules = true;
            }elseif($line == "# End of System folders"){
            	$sysrules = false;
            }
            if(!$sysrules){
	            if (substr(strtolower($line), 0, 11) == "user-agent:"){
	                $indx++;
	                $ua[$indx] = substr($line, 12);
	            }elseif (substr(strtolower($line), 0, 9) == "disallow:"){
	                $da[$indx] .= substr($line, 10)."\n";
	            }
            }
        }
        fclose($fp);
    }

    for($i = 0; $i < 20; $i++){
        $list = "<select name=\"ua[{$i}]\" id=\"ua{$i}\" size=\"1\">";
        foreach($bots as $bot => $se){
            (getIfSet($ua[$i]) == $bot) ? $sel = " selected=\"selected\"" : $sel = "";
            ($bot == "*") ? $bottag == " (All)" : $bottag = "";
            (strpos($se, '<b>') !== false) ? $style = ' style="font-weight: bold;"' : $style = "";
            $list .= "<option value=\"{$bot}\"{$sel}{$style}>{$se}{$bottag}</option>";
        }
        $list .= "</select>";
        print "<div class=\"setlabel\">$list</div>\n";
        print "<div class=\"setdata\"><textarea name=\"da[{$i}]\" id=\"da{$i}\" cols=\"42\" rows=\"2\">".getIfSet($da[$i])."</textarea></div>\n";
    }
    ?>
        <input type="hidden" id="currules" name="currules" value="<?=json_encode(array($ua, $da)) ?>"/>
    <?
}

/*------- SETTINGS FORMS -----------------------------------------------------------------*/
?>

	<div id="contentarea">
		<div id="waitoverlay"><br/><p><img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN?>images/loading.gif"/> Please Wait...<br/><br/>Your Settings are Loading</p></div>
		<div id="title"><?=SYS_NAME?> Settings</div>
		<form method="post" action="<?=$_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
	        <p id="issues" class="setissue<? if(getSettingsIssuesCount() == 0) echo ' disabled';?>"><? showSettingsIssues(); ?></p>
			<div class="setsubmit"><input type="submit" name="cfgsubmit" id="cfgsubmit" value="Save Settings"/></div>
	        <p style="clear: both;"></p>
			<div id="settingstabs" class="clearfix" style="display: none">
				<ul>
					<? if(userIsAllowedTo('view_general_settings')) { ?><li><a href="#tabs-general">General<?showSettingsIssuesIndicator('general'); ?></a></li><?=PHP_EOL; } ?>
					<? if(userIsAllowedTo('view_media_settings')) { ?><li><a href="#tabs-media">Media<?showSettingsIssuesIndicator('media'); ?></a></li><?=PHP_EOL; } ?>
					<? if(THEMES_ENABLED && userIsAllowedTo('view_themes')) { ?><li><a href="#tabs-themes">Themes</a></li><?=PHP_EOL; } ?>
					<? if(userIsAllowedTo('view_menu_settings')) { ?><li><a href="#tabs-menus">Menus<?showSettingsIssuesIndicator('menus'); ?></a></li><?=PHP_EOL; } ?>
					<? if(userIsAllowedTo('view_plugins')) { ?><li><a href="#tabs-plugins">Plugins<?showSettingsIssuesIndicator('plugins'); ?></a></li><?=PHP_EOL; } ?>
					<? if(userIsAllowedTo('view_users')) { ?><li><a href="#tabs-users">Users<?showSettingsIssuesIndicator('users'); ?></a></li><?=PHP_EOL; } ?>
					<? if(userIsAllowedTo('view_advanced_settings')) { ?><li><a href="#tabs-adv">Advanced<?showSettingsIssuesIndicator('advanced'); ?></a></li><?=PHP_EOL; } ?>
				</ul>

				<? if(userIsAllowedTo('view_general_settings')) { ?>
				<div id="tabs-general" class="settingstab">
	                <p id="issues-general" class="setissue<? if(getSettingsIssuesCount('general') == 0) echo ' disabled';?>"><? showSettingsIssues('general'); ?></p>
	                <? $readonly = '';
	                if(!userIsAllowedTo('edit_general_settings')) {
	                	$readonly = ' readonly="readonly"';
	                	echo '<p>You are only allowed to view these settings.  Editing them is not permitted.</p>'.PHP_EOL;
	                }
	                ?>
	                <h3 class="header">Business</h3>
					<div class="setlabel">Business Name: <span class="hovertip" alt="The name of the business (may be the same as the website name)">[?]</span><?=REQD_ENTRY?></div>
						<div class="setdata"><input<?=$readonly?> type="text" id="BUSINESS" name="newcfg[BUSINESS]" size="30" value="<?=$configs['BUSINESS']?>"/> <? showResetLink('BUSINESS')?></div>
					<div class="setlabel">Website Name: <span class="hovertip" alt="The name of the site">[?]</span><?=REQD_ENTRY?></div>
						<div class="setdata"><input<?=$readonly?> type="text" id="SITE_NAME" name="newcfg[SITE_NAME]" size="30" value="<?=$configs['SITE_NAME']?>"/> <? showResetLink('SITE_NAME')?></div>
					<div class="setlabel">Owner's Email: <span class="hovertip" alt="The main public email of the business or site owner">[?]</span><?=REQD_ENTRY?></div>
						<div class="setdata"><input<?=$readonly?> type="text" id="OWNER_EMAIL" name="newcfg[OWNER_EMAIL]" size="30" value="<?=$configs['OWNER_EMAIL']?>"/> <? showResetLink('OWNER_EMAIL')?></div>
					<div class="setlabel">Administrator Email: <span class="hovertip" alt="The email used to issue system or critical alerts and messages">[?]</span><?=REQD_ENTRY?></div>
						<div class="setdata"><input<?=$readonly?> type="text" id="ADMIN_EMAIL" name="newcfg[ADMIN_EMAIL]" size="30" value="<?=$configs['ADMIN_EMAIL']?>"/> <? showResetLink('ADMIN_EMAIL')?></div>
					<div class="setlabel">Business Address: <span class="hovertip" alt="This information can be shown on the contact page and in the footer">[?]</span></div>
						<div class="setdata"><textarea<?=$readonly?> id="BUS_ADDRESS" name="newcfg[BUS_ADDRESS]" cols="42" rows="6"><?=$configs['BUS_ADDRESS']?></textarea> <? showResetLink('BUS_ADDRESS')?></div>
					<div class="setlabel">Business Phone:</div>
						<div class="setdata"><input<?=$readonly?> type="text" id="BUS_PHONE" name="newcfg[BUS_PHONE]" size="30" value="<?=$configs['BUS_PHONE']?>"/> <? showResetLink('BUS_PHONE')?></div>
					<div class="setlabel">Business Fax:</div>
						<div class="setdata"><input<?=$readonly?> type="text" id="BUS_FAX" name="newcfg[BUS_FAX]" size="30" value="<?=$configs['BUS_FAX']?>"/> <? showResetLink('BUS_FAX')?></div>
	                <h3 class="header">Date and Time</h3>
					<div class="setlabel">Date Format: <span class="hovertip" alt="The date format is more than cosmetic.  It may affect time-sensitive features of the site">[?]</span></div>
						<div class="setdata">
							<select name="newcfg[PHP_DATE_FORMAT]" id="PHP_DATE_FORMAT" size="1"<?=$readonly?>>
							<? $df = array("Y-m-d", "m-d-Y", "d-m-Y", "Y/m/d", "m/d/y", "d/m/Y");
							foreach($df as $d){
								$sel = (($d == $configs['PHP_DATE_FORMAT']) ? ' selected="selected"' : '');
								echo '<option value="'.$d.'"'.$sel.'>'.$d.(($d == 'Y-m-d') ? ' [default]' : '').'</option>'.PHP_EOL;
							}
							?>
							</select>
                            </br>The date looks like this: <?=date($configs['PHP_DATE_FORMAT'])?>
						</div>
					<div class="setlabel">Timezone: <span class="hovertip" alt="The timezone setting affects the login timer, garbage collection, simcron tasks, and any other time-sensitive functions">[?]</span></div>
	                    <div class="setdata">
	                        <? showSettingsTimeZoneData($readonly); ?>
	                    </div>
	                <p>&nbsp;</p>
				</div>
				<? }

				if(userIsAllowedTo('view_media_settings')) { ?>
				<div id="tabs-media" class="settingstab">
	                <p id="issues-media" class="setissue<? if(getSettingsIssuesCount('media') == 0) echo ' disabled';?>"><? showSettingsIssues('media'); ?></p>
	                <? $readonly = '';
	                if(!userIsAllowedTo('edit_media_settings')) {
	                	$readonly = ' readonly="readonly"';
	                	echo '<p>You are only allowed to view these settings.  Editing them is not permitted.</p>'.PHP_EOL;
	                }
	                ?>
	                <h3 class="header">Images</h3>
	 				<div class="setlabel">Full-Size Image Dimensions: <span class="hovertip" alt="The standard image dimension that images will be reduced to if larger">[?]</span><?=REQD_ENTRY?><br/>(in Pixels, up to 2000 x 2000)</div>
						<div class="setdata">Max Width <input<?=$readonly?> type="text" id="IMG_MAX_WIDTH" name="newcfg[IMG_MAX_WIDTH]" maxlength="4" size="5" value="<?=$configs['IMG_MAX_WIDTH']?>"/>, Max Height <input type="text" id="IMG_MAX_HEIGHT" name="newcfg[IMG_MAX_HEIGHT]" size="5" value="<?=$configs['IMG_MAX_HEIGHT']?>"/> <? showResetLink('IMG_MAX_WIDTH', 'IMG_MAX_HEIGHT')?></div>
					<div class="setlabel">Maximum Image File Size: <span class="hovertip" alt="The maximum image file size that can be uploaded">[?]</span><?=REQD_ENTRY?><br/>(up to 10MB)</div>
						<div class="setdata"><input<?=$readonly?> type="text" id="IMG_MAX_UPLOAD_SIZE" name="newcfg[IMG_MAX_UPLOAD_SIZE]" maxlength="5" size="5" value="<?=$configs['IMG_MAX_UPLOAD_SIZE']?>"/> KB <? showResetLink('IMG_MAX_UPLOAD_SIZE')?></div>
					<div class="setlabel">Images Folder: <span class="hovertip" alt="The path, from document root, to the images folder">[?]</span><?=REQD_ENTRY?><br/>(default 'images/')</div>
						<div class="setdata"><input<?=$readonly?> type="text" id="IMG_UPLOAD_FOLDER" name="newcfg[IMG_UPLOAD_FOLDER]" maxlength="64" size="20" value="<?=$configs['IMG_UPLOAD_FOLDER']?>"/> <? showResetLink('IMG_UPLOAD_FOLDER')?></div>
	                <h3 class="header">Thumbnails</h3>
					<div class="setlabel">Thumbnail Image Dimensions: <span class="hovertip" alt="The thumbnail dimension for standard thumbnail images generated by the system">[?]</span><?=REQD_ENTRY?><br/>(in Pixels, up to 200 x 200)</div>
						<div class="setdata">Width <input<?=$readonly?> type="text" id="THM_MAX_WIDTH" name="newcfg[THM_MAX_WIDTH]" maxlength="3" size="5" value="<?=$configs['THM_MAX_WIDTH']?>"/>, Height <input type="text" id="THM_MAX_HEIGHT" name="newcfg[THM_MAX_HEIGHT]" size="5" value="<?=$configs['THM_MAX_HEIGHT']?>"/> <? showResetLink('THM_MAX_WIDTH','THM_MAX_HEIGHT')?></div>
					<div class="setlabel">Medium Image Dimensions: <span class="hovertip" alt="The dimensions for medium thumbnail images (pocket images) generated by the system">[?]</span><?=REQD_ENTRY?><br/>(in Pixels, up to 400 x 400)</div>
						<div class="setdata">Width <input<?=$readonly?> type="text" id="THM_MED_MAX_WIDTH" name="newcfg[THM_MED_MAX_WIDTH]" maxlength="3" size="5" value="<?=$configs['THM_MED_MAX_WIDTH']?>"/>, Height <input type="text" id="THM_MED_MAX_HEIGHT" name="newcfg[THM_MED_MAX_HEIGHT]" size="5" value="<?=$configs['THM_MED_MAX_HEIGHT']?>"/> <? showResetLink('THM_MED_MAX_WIDTH','THM_MED_MAX_HEIGHT')?></div>
					<div class="setlabel">Maximum Thumbnail File Size: <span class="hovertip" alt="The maximum thumbnail file size that can be uploaded.  Generated thumbnails will automatically comply with this setting">[?]</span><?=REQD_ENTRY?><br/>(up to 1MB)</div>
						<div class="setdata"><input<?=$readonly?> type="text" id="THM_MAX_UPLOAD_SIZE" name="newcfg[THM_MAX_UPLOAD_SIZE]" maxlength="4" size="5" value="<?=$configs['THM_MAX_UPLOAD_SIZE']?>"/> KB <? showResetLink('THM_MAX_UPLOAD_SIZE')?></div>
					<div class="setlabel">Thumbnails Folder: <span class="hovertip" alt="The path, from document root, to the thumbnails folder.  It can be the same as the images folder, if you wish">[?]</span><?=REQD_ENTRY?><br/>(default 'thumbs/')</div>
						<div class="setdata"><input<?=$readonly?> type="text" id="THM_UPLOAD_FOLDER" name="newcfg[THM_UPLOAD_FOLDER]" maxlength="64" size="20" value="<?=$configs['THM_UPLOAD_FOLDER']?>"/> <? showResetLink('THM_UPLOAD_FOLDER')?></div>
	                <h3 class="header">Organizer Tool</h3>
					<div class="setlabel">Organizer Thumbnail Dimensions: <span class="hovertip" alt="The dimensions of thumbnails generated for use with the Organizer">[?]</span><?=REQD_ENTRY?><br/>(in Pixels, up to 200 x 200)</div>
						<div class="setdata">Width <input<?=$readonly?> type="text" id="ORG_THM_MAX_WIDTH" name="newcfg[ORG_THM_MAX_WIDTH]" maxlength="3" size="5" value="<?=$configs['ORG_THM_MAX_WIDTH']?>"/>, Height <input type="text" id="ORG_THM_MAX_HEIGHT" name="newcfg[ORG_THM_MAX_HEIGHT]" maxlength="3" size="5" value="<?=$configs['ORG_THM_MAX_HEIGHT']?>"/> <? showResetLink('ORG_THM_MAX_WIDTH','ORG_THM_MAX_HEIGHT')?></div>
	                <h3 class="header">Logos</h3>
					<div class="setlabel">Admin System Logo Image: <span class="hovertip" alt="An optional logo to display on the login page">[?]</span><br/>(in Pixels, up to 250x100)</div>
						<div class="setdata"><input<?=$readonly?> type="file" id="IMG_LOGIN_LOGO" name="newcfg[IMG_LOGIN_LOGO]" size="40"/></div>
				</div>
				<? }

				if(userIsAllowedTo('view_themes')) { ?>
				<div id="tabs-themes" class="settingstab">
	                <p id="issues-themes" class="setissue<? if(getSettingsIssuesCount('themes') == 0) echo ' disabled';?>"><? showSettingsIssues('themes'); ?></p>
	                <ul>
	                    <li><a href="#theme_website">Website Theme</a></li>
	                    <li><a href="#theme_admin">Admin Theme</a></li>
	                </ul>
	                <div id="theme_website">
		                <? $readonly = '';
		                if(!userIsAllowedTo('edit_website_theme')) {
		                	$readonly = ' readonly="readonly"';
		                	echo '<p>You are only allowed to view these settings.  Editing them is not permitted.</p>'.PHP_EOL;
		                }
		                ?>
	                    <div id="themebody">
	                    <? displaySettingsThemesAttributes($readonly); ?>
	                    </div>
	                </div>
	                <div id="theme_admin">
		                <? $readonly = '';
		                if(!userIsAllowedTo('edit_admin_theme')) {
		                	$readonly = ' readonly="readonly"';
		                	echo '<p>You are only allowed to view these settings.  Editing them is not permitted.</p>'.PHP_EOL;
		                }
		                ?>
	                    <div class="setlabel" style="line-height: 2em;">
	                        <span id="curtheme" style="font-weight: bold">'<?=ucwords($GLOBALS['THEME']);?>'</span></div>
	                    <div class="setdata" style="line-height: 2em;">
	                        <? $themes = prepThemes();?>
	                        <? if($readonly == "") { ?>
	                        <br/>Change to... <select name="themechgsel" id="themechgsel">
	                            <option value="">- Select new theme -</option>
	                            <?
	                            if(count($themes) > 0){
	                                foreach($themes as $theme){
	                                    (strtolower($theme) == "default") ? $themelocked = ' [system]' : $themelocked = '';
	                                    (strtolower($theme) == strtolower($GLOBALS['THEME'])) ? $seltheme = ' selected="selected"' : $seltheme = '';
	                                    echo '<option value="'.$theme.'"'.$seltheme.'>'.$theme.$themelocked.'</option>';
	                                }
	                            }
	                            ?>
	                        </select>
	                        <input type="button" name="chgtheme" id="chgtheme" value="Change" />
	                        <br/>Copy to... <input type="text" id="themename" size="20" maxlength="50" value=""/>
	                        <input type="button" name="copytheme" id="copytheme" value="Copy" />
	                        <br/>Remove... <select name="themeremsel" id="themeremsel">
	                            <option value="">- Select theme -</option>
	                            <?
	                            if(count($themes) > 0){
	                                foreach($themes as $theme){
	                                    if(strtolower($theme) != "default") {
	                                        echo '<option value="'.$theme.'">'.$theme.$themelocked.'</option>';
	                                    }
	                                }
	                            }
	                            ?>
	                        </select>
	                        <input type="button" name="remtheme" id="remtheme" value="Remove" />
	                        <? } ?>
	                    </div>
	                </div>
				</div>
				<? }

				if(userIsAllowedTo('view_menu_settings')) { ?>
				<div id="tabs-menus" class="settingstab">
	                <p id="issues-menus" class="setissue<? if(getSettingsIssuesCount('menus') == 0) echo ' disabled';?>"><? showSettingsIssues('menus'); ?></p>
	                <ul>
	                    <?if(userIsAllowedTo('edit_website_menus')) { ?><li><a href="#menus_website">Website Menus</a></li><? } ?>
	                    <?if(userIsAllowedTo('edit_admin_menus')) { ?><li><a href="#menus_admin">Admin Menus</a></li><? } ?>
	                </ul>

	                <? $readonly = '';
	                if(userIsAllowedTo('edit_website_menus')) { ?>
	                <div id="menus_website">
                        <? showSettingsMenusforWebsite(); ?>
	                </div>
	                <? }

	                if(userIsAllowedTo('edit_admin_menus')) { ?>
	                <div id="menus_admin">
                        <? showSettingsMenusforAdmin(); ?>
	                </div>
	                <? } ?>
				</div>
				<? }

				if(userIsAllowedTo('view_plugins')) { ?>
				<div id="tabs-plugins" class="settingstab">
	                <p id="issues-plugins" class="setissue<? if(getSettingsIssuesCount('plugins') == 0) echo ' disabled';?>"><? showSettingsIssues('plugins'); ?></p>
	                <ul>
	                    <li><a href="#plugin_installed">Installed Plugins</a></li>
	                    <li><a href="#plugin_problem">Problem/Deleted Plugins</a></li>
	                    <li><a href="#plugin_settings">Settings</a></li>
	                    <li><a href="#plugin_frameworks">Frameworks</a></li>
	                    <li><a href="#plugin_lib">Repository</a></li>
	                </ul>
	                <div id="plugin_installed">
	                    <? showSettingsPluginsInstalledList(); ?>
	                </div>
	                <div id="plugin_problem">
	                    <? showSettingsPluginsProblemList(); ?>
	                </div>
	                <div id="plugin_settings">
	                    <? showSettingsPluginsSettings(); ?>
	                </div>
	                <div id="plugin_frameworks">
	                    <? showSettingsPluginsFrameworks(); ?>
	                </div>
	                <div id="plugin_lib">
	                    <? showSettingsPluginsRepository(); ?>
	                </div>
				</div>
				<? }

				if(userIsAllowedTo('view_users')) { ?>
				<div id="tabs-users" class="settingstab">
	                <p id="issues-users" class="setissue<? if(getSettingsIssuesCount('users') == 0) echo ' disabled';?>"><? showSettingsIssues('users'); ?></p>
	                <? $readonly = '';
	                if(!userIsAllowedTo('edit_user')) {
	                	$readonly = ' readonly="readonly"';
	                	echo '<p>You are only allowed to view these settings.  Editing them is not permitted.</p>'.PHP_EOL;
	                }
	                ?>
	                <div id="users">
	                    <? showSettingsUsersList($readonly); ?>
	                </div>
				</div>
				<? }

				if(userIsAllowedTo('view_advanced_settings')) { ?>
				<div id="tabs-adv" class="settingstab">
	                <p id="issues-adv" class="setissue<? if(getSettingsIssuesCount('advanced') == 0) echo ' disabled';?>"><? showSettingsIssues('advanced'); ?></p>
	                <ul>
	                    <? if(userIsAllowedTo('manage_aliases')) { ?><li><a href="#adv_data_aliases">Data Alias URLs</a></li><? } ?>
	                    <? if(userIsAllowedTo('manage_urls')) { ?><li><a href="#adv_links">Mod-Rewrites</a></li><? } ?>
	                    <? if(userIsAllowedTo('manage_visibility')) { ?><li><a href="#adv_visibility">Site Visibility</a></li><? } ?>
	                    <? if(userIsAllowedTo('manage_database')) { ?><li><a href="#adv_dbcontrol">Database</a></li><? } ?>
	                    <? if(userIsAllowedTo('manage_debugger')) { ?><li><a href="#adv_debugger">Error Handling</a></li><? } ?>
	                </ul>

	                <?
	                if(userIsAllowedTo('manage_aliases')) { ?>
	                <div id="adv_data_aliases">
	                    <? showSettingsAliases(); ?>
	                </div>
	                <? }

	                if(userIsAllowedTo('manage_urls')) { ?>
	                <div id="adv_links">
	                    <? showSettingsLinksArea(); ?>
	                </div>
	                <? }

	                if(userIsAllowedTo('manage_visibility')) { ?>
	                <div id="adv_visibility">
        				<h3 class="header">Site Presence</h3>
	                    <div class="setlabel">Is Site Online?: <span class="hovertip" alt="Set to 'no' to temporarily turn the site off and remove page access.">[?]</span></div>
							<div class="setdata">
								<input type="radio" id="SITEOFFLINE_off" name="newcfg[SITEOFFLINE]" value="0"<?=(($configs['SITEOFFLINE'] == 0) ? ' checked="checked"' : '')?> /> Yes
								<input type="radio" id="SITEOFFLINE_on" name="newcfg[SITEOFFLINE]" value="1"<?=(($configs['SITEOFFLINE'] == 1) ? ' checked="checked"' : '')?> /> No
							</div>
						<div class="setlabel">Offline Message:</div>
							<div class="setdata"><textarea id="SITEOFFLINE_MSG" name="newcfg[SITEOFFLINE_MSG]" cols="42" rows="4"><?=$configs['SITEOFFLINE_MSG']?></textarea> <? showResetLink('SITEOFFLINE_MSG')?></div>
	                    <? showSettingsSiteVisibilityArea(); ?>
	                </div>
	                <? }

	                if(userIsAllowedTo('manage_database')) { ?>
	                <div id="adv_dbcontrol">
	                    <p>Below are the current database connection settings.  If you require direct modification of these parameters, the Database Manager can perform those changes.</p>
	                    <div class="setlabel"><strong>Database Host:</strong></div>
	                        <div class="setdata"><?=DBHOST?></div>
	                    <div class="setlabel"><strong>Database Name:</strong></div>
	                        <div class="setdata"><?=DBNAME?></div>
	                    <div class="setlabel"><strong>Database Username:</strong></div>
	                        <div class="setdata"><?=DBUSER?></div>
	                    <div class="setlabel"><strong>Database Password:</strong></div>
	                        <div class="setdata"><?=DBPASS?></div>
	                    <div class="setlabel"><strong>Database Port:</strong></div>
	                        <div class="setdata"><?=((DBPORT != 0) ? DBPORT : 'Default (3306)') ?></div>
						<div class="setlabel"><strong>Data Tables Prefix:</strong> <span class="hovertip" alt="Although you can create any table for use in <?=SYS_NAME?>, ones starting with this prefix will be included in the Data Alias structure, Admin Menus, and other subsystems.">[?]</span></div>
							<div class="setdata"><input type="text" id="DB_TABLE_PREFIX" name="newcfg[DB_TABLE_PREFIX]" maxlength="10" size="10" value="<?=$configs['DB_TABLE_PREFIX']?>"/> <? showResetLink('DB_TABLE_PREFIX')?></div>
	                    <p style="clear: both; float: right">Launch the <a href="<?=WEB_URL.ADMIN_FOLDER?>loader.php?f=settings&fc=configDB&fp=100">Database Manager...</a></p>
	                </div>
	                <? }

	                if(userIsAllowedTo('manage_debugger')) { ?>
	                <div id="adv_debugger">
	                	<p><?=SYS_NAME?> is equipped with a fully articulated error handling and debugger system.  It can be customized to respond to different levels of errors, and output a variety of diagnostic data to the console, browser, a file or an email.</p>
	                    <div class="setlabel">Error Handler Sensitivity: <span class="hovertip" alt="What type of error(s) the system will trap">[?]</span></div>
	                        <div class="setdata">
								<select name="newcfg[ERROR_SENSITIVITY]" id="ERROR_SENSITIVITY" size="1">
								<?
								$es = array(E_ERROR | E_WARNING | E_PARSE => "Default detection", E_ERROR => "Stop on fatal errors only", E_ERROR | E_WARNING => "Stop on fatal and display warning errors", E_ALL => "Detect all errors", E_STRICT => "Warn of strict compatibility issues");
								foreach($es as $key => $er){
									$sel = (($key == $configs['ERROR_SENSITIVITY']) ? ' selected="selected"' : '');
									echo '<option value="'.$key.'"'.$sel.'>'.$er.'</option>'.PHP_EOL;
								}
								?>
								</select>
	                        </div>
	                    <div class="setlabel">Error Logging: <span class="hovertip" alt="What to do if an error is encountered">[?]</span></div>
	                        <div class="setdata">
								<select name="newcfg[ERROR_LOG_TYPE]" id="ERROR_LOG_TYPE" size="1">
								<?
								$es = array(0 => "Log to system", 1 => "Send email", 3 => "Save to log file");
								foreach($es as $key => $er){
									$sel = (($key == $configs['ERROR_LOG_TYPE']) ? ' selected="selected"' : '');
									echo '<option value="'.$key.'"'.$sel.'>'.$er.'</option>'.PHP_EOL;
								}
								?>
								</select>
	                        </div>
						<div class="setlabel">Error Log Email Recipient: <span class="hovertip" alt="The recipient to whom error messages will be sent">[?]</span></div>
							<div class="setdata"><input type="text" id="ERROR_LOG_TO_EMAIL" name="newcfg[ERROR_LOG_TO_EMAIL]" size="30" value="<?=$configs['ERROR_LOG_TO_EMAIL']?>"/> <? showResetLink('ERROR_LOG_TO_EMAIL')?></div>
						<div class="setlabel">Error Log File Name:</div>
							<div class="setdata"><input type="text" id="ERROR_LOG_TO_FILE" name="newcfg[ERROR_LOG_TO_FILE]" size="30" value="<?=$configs['ERROR_LOG_TO_FILE']?>"/> <? showResetLink('ERROR_LOG_TO_FILE')?></div>
	                    <div class="setlabel">Activate the Debugging System?</div>
							<div class="setdata">
								<input type="radio" id="ALLOW_DEBUGGING_on" name="newcfg[ALLOW_DEBUGGING]" value="1"<?=(($configs['ALLOW_DEBUGGING'] == 1) ? ' checked="checked"' : '')?> /> Yes
								<input type="radio" id="ALLOW_DEBUGGING_off" name="newcfg[ALLOW_DEBUGGING]" value="0"<?=(($configs['ALLOW_DEBUGGING'] == 0) ? ' checked="checked"' : '')?> /> No (Debugger messages will not be displayed)
							</div>
                        <div class="setlabel">PHP Error Configuration:</div>
                            <div class="setdata">
                                Display_errors = <?=ini_get('display_errors')?><br/>
                                Display_startup_errors = <?=ini_get('display_startup_errors')?><br/>
                                Error_log = <?=ini_get('error_log')?><br/>
                                Html_errors = <?=ini_get('html_errors')?><br/>
                                Error_append_string = <?=ini_get('error_append_string')?><br/>
                                Error_prepend_string = <?=ini_get('error_prepend_string')?><br/>
                                Error_reporting = <?=ini_get('error_reporting')?><br/>
                                Ignore_repeated_errors = <?=ini_get('ignore_repeated_errors')?><br/>
                                Log_errors = <?=ini_get('log_errors')?><br/>
                                Log_errors_max_len = <?=ini_get('log_errors_max_len')?><br/>
                                Track_errors = <?=ini_get('track_errors')?>
                                <p>More settings can be seen in Help > About</p>
                            </div>
	                </div>
	                <? } ?>
				</div>
				<? } ?>
			</div>

			<div id="settingscolorpicker" title="Color Picker" style="display: none"></div>
		</form>
	</div>
	<div id="pluginsettingsdialog" title="Settings" style="display: none;"></div>

	<script type="text/javascript">
		jQuery(window).load(function(){
			jQuery("#waitoverlay").css('display', 'none');
			jQuery("#settingstabs").css('display', '');
		});
	</script>

<?php
showFooter();
?>

