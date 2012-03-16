<?php
// ---------------------------
//
// ADMIN HELP DIALOG CONTENTS
//
// ---------------------------
//
define("VALID_LOAD", true);
define("BASIC_GETINC", true);
define("VHOST", substr(str_replace("\\", "/", realpath(dirname(__FILE__)."/../")), strlen(realpath($_SERVER['DOCUMENT_ROOT'])))."/");
include ($_SERVER['DOCUMENT_ROOT'].VHOST."admin/inc/_core/getinc.php");					// required - starts PHP incls!!!
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<?php
addHeadPlugin("basic");
showHeadLines(true);
?>
</head>

<body>
    <div id="help_about">
        <h2><?=SYS_NAME ?> <?= intval(CODE_VER) ?></h2>
        <p>
            <h3>Version <?= CODE_VER?> "<?= CODE_VER_NAME?>"</h3>
        </p>
        <p>
            &copy; <?=date("Y")?> <?=COPYRIGHT_NAME ?> and contributors.  All rights reserved.<br/>
            <?=SYS_NAME?> is free software released under the <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU/GPL License</a><br/></br>
            <? if(COPYRIGHT_WEB != '') echo 'Visit <a href="'.COPYRIGHT_WEB.'" target="_blank">'.COPYRIGHT_WEB.'</a> for more.'?>
        </p>
        <hr/>
        <p style="text-align: left;">
            <span><strong>Installation Environment</strong></span><br/>
            Web Server: <?= apache_get_version()?><br/>
            PHP Version: <?= phpversion()?> <a href="#" id="help_phpcfg_link">View Config Settings</a><br/>
            MySQL Version: <?= mysql_get_server_info()?><br/>
            Zend Engine Version: <?= zend_version()?><br/>
            Memory Usage: <?= sprintf("%01.3f", memory_get_usage(true) / 1024)?> Kb<br/><br/>
        </p>
        <hr/>
        <p style="text-align:left;">
            <span><strong>You are Using</strong></span><br/>
            <?
            $browser = browser_detection("full_assoc");
            switch(strtolower($browser['browser_name'])){
                case 'gecko':
                    $browser_name = 'Firefox';
                    $browser_number = $browser['moz_data'][3];
                    $browser_notes = '';
                    $browser_bkpos = 1;
                    break;
                case 'msie':
                    $browser_name = 'Internet Explorer';
                    $browser_number = $browser['browser_math_number'];
                    $browser_notes = (($browser['os'] == 'nt' && $browser['os_number'] >= 6 && $browser_number < 9) ? 'You should upgrade your browser to the latest version.' : '');
                    $browser_bkpos = 4;
                    break;
                case 'chrome':
                    $browser_name = 'Google Chrome';
                    $browser_number = $browser['webkit_data'][1];
                    $browser_notes = '';
                    $browser_bkpos = 0;
                    break;
                case 'safari':
                    $browser_name = 'Safari';
                    $browser_number = $browser['browser_math_number'];
                    $browser_notes = '';
                    $browser_bkpos = 3;
                    break;
                default:
                    $browser_name = ucwords($browser['browser_name']);
                    $browser_number = $browser['browser_math_number'];
                    $browser_notes = '';
                    $browser_bkpos = -1;
                    break;
            }
            if($browser_bkpos >= 0){
            ?>
            <span class="browser_logo" style="background-position: <?=(-$browser_bkpos * 36)?>px 0px;"></span>
            <? } ?>
            Browser/Agent: <?=$browser_name?> <?=$browser_number?><br/>
            <?=$browser_notes?>
        </p>
        <hr/>
        <div id="help_phpcfg"></div>
    </div>
</body>
</html>