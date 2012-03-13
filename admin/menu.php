<?php
// ---------------------------
//
// MENUS
//
// ---------------------------
//

if(!defined("VALID_LOAD")) die("This file cannot be accessed directly!");

$dir = VHOST.ADMIN_FOLDER;

// root bar (deprecate)
$menu = array("root" => null, "rootrec" => null);
$menu['rootid'] = getIfSet($_SESSION['rootid']);
$menu['rootname'] = getIfSet($_SESSION['rootname']);
if(!isblank($rootvar)){
    // root persistence
    $menu['root'] = getRequestVar('root');
    $menu['rootrec'] = setRootSession(true);

    print "<ul id=\"roothead\">";
	if($_SESSION['rootname'] != ""){
		// display root name and menu
		$sessionname = $_SESSION['rootname']." ";
		$rootlink = "?".$rootvar."=".$_SESSION['root'];
		$link = $dir.$roottable."/list-".$roottable.".php".$rootlink;
		$popupmenu[$roottable] = getMenuFromDB($roottable, "*", $dir.$rootdir."/list-".$rootdir.".php", $rootvar, "id > 0");
		print "<li class=\"rootitem\"><a href=\"$link\">Section: ".$_SESSION['rootname'];
		print "&nbsp;<img src=\"".WEB_URL.ADMIN_FOLDER."images/icons/arrow-dn.gif\" border=\"0\" alt=\"Click to show menu\" title=\"Click to show menu\" />";
		print "</a>";
		if(is_array(getIfSet($popupmenu))) foreach($popupmenu as $value) print $value;
		print "</li>";
	}else{
		$rootlink = "";
	}
	print "</ul>\n\n";
}

// top and sub navigation menus
$menus = getAdminMenus();
$_page->menu = $menu;
$_page->menus = $menus;

$toplevel = "";
$sublevel = "";
$p = parse_url($_SERVER['REQUEST_URI']);
$filebase = preg_replace("/(^list-|^edit-)/i", "", basename($p['path'], ".php"));
foreach($menus as $key => $menu){
    if(userIsAllowedTo("view_locked_menus") || !$menu['restricted']){
        $chosen = (($_page->menu['section'] == $key) ? "chosen" : "unchosen");
        $toplevel .= "<li class=\"{$chosen}\" id=\"menu_{$key}\"><a href=\"".WEB_URL.ADMIN_FOLDER.$menu['target']."\">{$menu['title']}</a></li>\n";
        if(is_array($menu['childmenus']) && $chosen == "chosen"){
            foreach($menu['childmenus'] as $skey => $smenu){
                if(userIsAllowedTo("view_locked_menus") || !$smenu['restricted']){
                    $schosen = (($filebase == $skey) ? "chosen" : "unchosen");
                    $sublevel .= "<li class=\"{$schosen}\" id=\"submenu_".$skey."\"><a href=\"".WEB_URL.ADMIN_FOLDER.$smenu['target']."\">{$smenu['title']}</a></li>\n";
                }
            }
        }
    }
}
$toplevel = "<ul id=\"navigation\">$toplevel</ul>\n";
if($sublevel != "") $sublevel = "<ul id=\"subnavigation\">$sublevel</ul>\n";
echo $toplevel;
echo $sublevel;
?>

<div id="display_core_msg"></div>
<div id="display_runtime_msg"></div>

<? // tools bar ?>
<div id="toolbar">
    <div id="adminlogout">
        <a href="<?=WEB_URL.ADMIN_FOLDER?>admlogin.php?admsubmit=Logout">
            <img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN?>images/icons/exit.png" border="0" alt="Click to Log Out" title="Click to Log Out" />
        </a>
    </div>
    <div id="gotowebsite">
        <a href="<?=WEB_URL?>">
            <img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN?>images/icons/globe.png" border="0" alt="Click to go to <?=SITE_NAME?> website" title="Click to go to <?=SITE_NAME?> website" />
        </a>
    </div>
    <div id="gotomeeting">
        <a href="https://www1.gotomeeting.com/t/URL/g2m/joingotomeeting;jsessionid=abc8R0wcX_Kl9d2Rq3RGs?Target=/m/join_gotomeeting.tmpl" target="_blank">
            <img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN?>images/icons/gotomeeting.png" border="0" alt="Click to Join GotoMeeting" title="Click to Join GotoMeeting" />
        </a>
    </div>
    <? if(LIVE){ ?>
    <div id="webstats">
        <a href="<?=WEBSTAT_FOLDER?>" target="_blank">
            <img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN?>images/icons/webstats.png" border="0" alt="Click view your website stats" title="Click view your website stats" />
        </a>
    </div>
    <? } ?>
	<div id="settings">
		<a href="<?=WEB_URL.ADMIN_FOLDER?>settings.php" id="settingsbtn">
			<img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN?>images/icons/settings.png" border="0" alt="Click to edit settings" title="Click to edit settings" />
		</a>
	</div>
    <div id="help">
        <a href="#" onclick="$('#helpdialog').load('<?=WEB_URL.ADMIN_FOLDER?>helpdialog.php', {data:'<?=urlencode(gzdeflate($_page->help))?>'}, function(){ $('#helpdialog').dialog('open'); }); return false" />
            <img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN?>images/icons/help.png" border="0" alt="Click for help" title="Click for help" />
        </a>
    </div>
    <div id="about">
        <a href="#" id="aboutbtn" />
            <img src="<?=WEB_URL.ADMIN_FOLDER.ADM_CSS_FOLDER.DEF_ADM_SKIN?>images/icons/info_button_16.png" border="0" alt="Click to view credits and system environment" title="Click to view credits and system environment" />
        </a>
    </div>
</div>

<div id="helpdialog" title="<?=BUSINESS?> Admin System" style="display: none;"></div>

