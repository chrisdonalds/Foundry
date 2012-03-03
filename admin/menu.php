<?php
// ---------------------------
//
// MENUS
//
// ---------------------------
//
// Load menu headings from $_page->menu['sections'] array

if(!defined("VALID_LOAD")) die("This file cannot be accessed directly!");

$dir = VHOST.ADMIN_FOLDER;
$section = $_page->menu['section'];

// root bar
if($rootvar != ""){
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

// top navigation bar scroll controller
$menuscroller = array();
(isset($_COOKIE['menu_scr_start'])) ? $menuscroller['start'] = $_COOKIE['menu_scr_start'] : $menuscroller['start'] = 0;

// top navigation bar
print "<ul id=\"navigation\">";
foreach($_page->menu['sections'] as $key => $value) {
	if(substr($value, 0, 2) == "\d" && !userIsAtleast(ADMLEVEL_DEVELOPER)){
		continue;
	}else{
		$value = str_replace("\\d", "", $value);
		$excl_pos = strpos($key, "!");
		$oktodisplay = true;
		if($excl_pos > 0){
			// sections that do not display this menu item
			$excl = substr($key, $excl_pos + 1);
			$key = substr($key, 0, $excl_pos);
			if(strpos($excl, ",") > 0){
				$items = split("\,", $excl);
				$oktodisplay = !in_array($_SESSION['rootid'], $items);
			}else{
				$items = $excl;
				$oktodisplay = ($excl != $_SESSION['rootid']);
			}
		}

		if($oktodisplay){
			$popupmenu = "";
			$skey = $key;
			$spage = $key;
			$js = "";
			switch(substr($value, 0, 2)){
				case "\p":
					$pageroot = "edit-";
					$pagesuffix = "";
					$value = str_replace("\\p", "", $value);
					break;
				case "\c":
					$pageroot = "list-";
					$pagesuffix = "_cat";
					$value = str_replace("\\c", "", $value);
					break;
                case "\a":
                    $pageroot = "list-";
                    $joinedvalue = explode("|", $value);
                    $value = $joinedvalue[1];
                    $spage = substr($joinedvalue[0], 3);
				default:
					$pageroot = "list-";
					$pagesuffix = "";
					break;
			}
			$value = preg_replace("/{div}/i", $_page->menu['rootname'], $value);

			if(strtolower($value) == strtolower($roottable) || $rootvar == "" || ($rootvar != "" && $_page->menu['root'] != "")) {
				// display if menu is roottable, or rootvar is not used, or rootvar and session exist
				$link = WEB_URL.ADMIN_FOLDER.$skey."/".$pageroot.$spage.$pagesuffix.".php".$rootlink;
				if($section == $skey) {
					print "<li id=\"menu_{$key}\" class=\"chosen\"$js><a href=\"$link\">$value</a>";
					if(is_array($popupmenu)) foreach($popupmenu as $value) print $value;
					print "</li>";
				}else{
					print "<li id=\"menu_{$key}\" class=\"unchosen\"$js><a href=\"$link\">$value</a>";
					if(is_array($popupmenu)) foreach($popupmenu as $value) print $value;
					print "</li>";
				}
			}
		}
	}
}
print "</ul>\n\n";

// sub-navigation bar
if(isset($_page->menu['subsections'][$section])){
	if(is_array($_page->menu['subsections'][$section])) {
		print "<ul id=\"subnavigation\">";
		foreach($_page->menu['subsections'][$section] as $skey=>$value) {
			if($value != "") {
				switch(substr($value, 0, 2)){
					case "\p":
						$pageroot = "edit-";
						$value = str_replace("\\p", "", $value);
						break;
					default:
						$pageroot = "list-";
						break;
				}
				$link = WEB_URL.ADMIN_FOLDER.$section."/".$pageroot.$skey.".php".$rootlink;
				if($_page->menu['subsection'] == $skey) {
					print "<li id=\"submenu_{$skey}\" class=\"chosen\"><a href=\"$link\">$value</a></li>";
				}else{
					print "<li id=\"submenu_{$skey}\" class=\"unchosen\"><a href=\"$link\">$value</a></li>";
				}
			}
		}
		print "</ul>\n\n";
	}
}
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

