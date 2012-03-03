// ---------------------------
//
// FOUNDRY
// - WRITING A PLUGIN
//
// ---------------------------

1) Plugin Info File
-------------------

Every plugin must include a single plugin.info file in its base folder.  The plugin.info
file describes how Foundry is to register and prepare the plugin.

Each attribute line starts with '#attribute:' and one space

Here is a sample plugin.info file:

#name:         My Plugin
#ver:          1.0
#author:       Chris Donalds
#descr:        First plugin
#created:      Apr 14, 2011
#revised:      Apr 15, 2011
#sysver:       3.0
#usedin:       admin
#incl:         myplugin
#initfile:     myplugin.core.php
#headerfunc:   myplugin_headerprep
#settingsfunc: myplugin_settings
#depends:      jquery
#nodelete:     no
#nodisable:    no
#builtin:      no
#active:       yes

* = required

*name... 		The unique name displayed in the Plugins section of Settings
ver...			The plugin's version
author...		The developer or author of the plugin.  If there are additional creators, you can credit them here as well.  Note: HTML anchor tags are removed.
descr...		A brief description of the plugin
created...		The creation or initial release date of this version of the plugin
revised...		The date of the latest revision of this version of the plugin
*sysver...		The minimum Foundry version required to properly run the plugin
*usedin...		The zone (or interface area) of the site: front, admin, or both
*incl...		The plugin's unique inclusion phrase
*initfile...	The name of the initializer file without path.  Must be written in PHP.
headerfunc...	The name of the function that will prepare script/style lines for the HEAD
settingsfunc...	The name of the function that will handle settings calls
depends...		The inclusion phrase of one or more plugins (comma-separated) that are required for this plugin to work
nodelete...		Set to 1 or yes to prevent users from being able to delete the plugin from within the Admin
nodisable...	Set to 1 or yes to prevent users from being able to disable the plugin from within the Admin.  Disabled plugins are not available to the system.
active...		Set to 1 or yes to set the plugin's initial activation state.  Inactive plugins are analogous to being temporarily disabled.

2) Header Function
------------------

/**
 * This function generates a line that is placed within the HEAD block
 * Put this function in your plugin initiation file (see step 2)
 */
function plugin_headerfunc(){
	$err = addHeadLine(
		'asType',				// script, style, rss, or favicon
		'dir',					// path to the file
		'filename',				// the name of the js, css, rss, ico file
		'media',				// the output media (screen, all, print...)
		'charset',				// optional character set (script only)
		'if'					// optional if condition (<!--[if...]> ... <![endif]-->)
	);
}

3) Settings Function
--------------------

incl = 'myplugin'

function myplugin_settings($action = null, $data = ''){
    if($action == null){
        // get the saved data from either the plugins table (PLUGIN_SETTINGS_SAVETOSTD)
        // ... or settings table (PLUGIN_SETTINGS_SAVETOSYS).  Your choice, but must be
        // ... the same as below.
        $value = getPluginCustomSetting("myplugin", PLUGIN_SETTINGS_SAVETOSTD);

        // return dialog HTML.  Name your fields and their values will be returned in the
        // ... $data parameter once the 'save' button is pressed.
    	$html = '
    	<h3 class="header">My Plugin Settings</h3>
   		<p>You may modify the values below:</p>
    	<p>New Value: <input type="text" name="thevalue" value="'.$value.'" /></p>
    	';
        return pluginSettingsDialogContents("My Plugin", $html, __FUNCTION__);

    }elseif($action == PLUGIN_SETTINGS_SAVE){
        // $data parameter contains the $_GET queried set of field values from the form
        // ... use parse_str to extract them from the dataset
        // ... (see the <thevalue> field above and variable below?)
        parse_str($data);

        // save data to either the plugins table (PLUGIN_SETTINGS_SAVETOSTD)
        // ... or settings table (PLUGIN_SETTINGS_SAVETOSYS)
        $result = savePluginCustomSettings("myplugin", $thevalue, PLUGIN_SETTINGS_SAVETOSTD);

        // return a response back to the script (first parameter is optional)
        return pluginSettingsDialogButtonPressed((($result) ? 'Setting saved successfully' : 'Setting was not saved'), true);

    }elseif($action == PLUGIN_SETTINGS_CLOSE){
        // closed button pressed
        return pluginSettingsDialogButtonPressed("Closed!", true);

    }
}
