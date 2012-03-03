<?php
/*
MY PLUG-IN
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================
*/

function myplugin_headerprep(){
    //echo "HELLO 1";
}

function myplugin_settings($action = null){
    if($action == null){
        return pluginSettingsDialogContents("My Plugin", "Settings details go here.", __FUNCTION__);
    }elseif($action == PLUGIN_SETTINGS_SAVE){
        return pluginSettingsDialogButtonPressed("Saved!", true);
    }elseif($action == PLUGIN_SETTINGS_CLOSE){
        return pluginSettingsDialogButtonPressed("Closed!", true);
    }
}

?>