<?php
/*
IMGEDIT PLUG-IN
Web Template 3.0
Version: 3.0
========================================
File Uploading and progress indicator
*/

/**
 * Initialize the plugin scripts and styles
 */
function imgedit_headerprep($attr = null){
	addHeadLine(
		'script',
		WEB_URL.ADMIN_FOLDER.PLUGINS_FOLDER."imgedit/js/",
		"jquery.jcrop.js"
	);
	addHeadLine(
		'script',
		WEB_URL.ADMIN_FOLDER.PLUGINS_FOLDER."imgedit/js/",
		"imgedit.ajax.js"
	);
	addHeadLine(
		'style',
		WEB_URL.ADMIN_FOLDER.PLUGINS_FOLDER."imgedit/css/",
		"jquery.imgedit.css",
		"screen"
	);
	define('IMGEDITOR_PLUGIN_NAME', $attr);
}

/**
 * Prepare Image Editor HTML content
 * @param string $label
 * @param string $imglabel
 * @param array|null $dims width, height
 */
function showImgEditBox($label, $imglabel, $dims = null){
	// prepares imgedit dialogs and handlers
    if(is_null($dims)) {
        $dims = array(THM_MAX_WIDTH, THM_MAX_HEIGHT);
    }elseif(!is_array($dims)) {
        die("ShowImgEditBox: Thumbnail dimensions not provided as an array (W, H)");
    }
	$action = $_SERVER['PHP_SELF'];

	echo PHP_EOL;
	echo '<!-- IMGEDIT -->'.PHP_EOL;

	$web = WEB_URL;
	$svr = SITE_PATH;
	$nosrc = FU_FOLDER."nosrc.png";
	$pluginfolder = ADMIN_FOLDER.PLUGINS_FOLDER."imgedit/";
	$destfolder = FU_TEMPFOLDER."/thumbs/";
	$json_thmdims = json_encode($dims);

	// store dynamic values in JSON attribute
	$attr = array(
		"web" => WEB_URL,
		"svr" => SITE_PATH,
		"nosrc" => FU_FOLDER."nosrc.png",
		"pluginfolder" => ADMIN_FOLDER.PLUGINS_FOLDER."imgedit/",
		"destfolder" => FU_TEMPFOLDER."thumbs/",
		"thmdims" => $dims
	);
	showHiddenField("imgedit_params", urlencode(json_encode($attr)), true);

	print<<<EOT

<div id="imgeditordialog" title="{$label} Editor" style="display: none;">
	<form method="post" action="{$action}" name="imgeditorform" enctype="multipart/form-data">
		<input type="hidden" id="x" name="x" />
		<input type="hidden" id="y" name="y" />
		<input type="hidden" id="w" name="w" />
		<input type="hidden" id="h" name="h" />
		<input type="hidden" name="imgeditfileloc" id="imgeditfileloc" value="" />
		<input type="hidden" name="imgeditscale" id="imgeditscale" value="1" />

		<p><b>File being edited:</b> <span id="imgeditfilename"></span></p>
		<div style="clear: both; margin-top: 10px; position: relative; text-align: right;">
			<span style="font-size: 8pt; margin-right: 10px;">Remember to save/update the page you are on.</span>
			<input type="button" id="imgeditorupdate" value="Update & Close" class="greenbutton bold" />&nbsp;
			<input type="button" id="imgeditorclose" value="Close" class="bold" />
		</div>

		<div id="imgedit_tabs">
			<img id="imgedit_src" src="" alt="" title="" />
			<ul>
				<li><a href="#imgedit_crop">Crop</a></li>
				<li><a href="#imgedit_rot">Rotate/Flip</a></li>
				<li><a href="#imgedit_effects">Effects</a></li>
				<li><a href="#imgedit_colorize">Colorize</a></li>
				<li><a href="#imgedit_help">Help</a></li>
			</ul>

			<!-- Crop -->
			<div id="imgedit_crop" class="clearfix">
				<div id="imgedit_cropleft">
					<img id="imgedit_cropsrc" src="" alt="Source image" title="Source image" />
				</div>
				<div id="imgedit_cropright">
					<div id="imgedit_cropthumb">
						<img id="imgedit_thumb" src="" style="" />
					</div>
					<div style="clear: both; width: 150px;">
						<strong>Selection:</strong><br/>
						x = <span id="cropx">0</span>&nbsp;
						y = <span id="cropy">0</span><br/>
						w = <span id="cropw">0</span>&nbsp;
						h = <span id="croph">0</span>
					</div>
				</div>
			</div>
			<!-- End Crop -->

			<!-- Rotate/Flip -->
			<div id="imgedit_rot" class="clearfix">
				<div id="imgedit_rotleft">
					<img id="imgedit_rotsrc" src="" alt="Source image" title="Source image"/>
				</div>
				<div id="imgedit_rotright">

				</div>
			</div>
			<!-- End Rotate -->

			<!-- Effects -->
			<div id="imgedit_effects" class="clearfix">
				<div id="imgedit_effectleft">
					<img id="imgedit_effectsrc" src="" alt="Source image" title="Source image"/>
				</div>
			</div>
			<!-- End Effects -->

			<!-- Colorize -->
			<div id="imgedit_colorize" class="clearfix">
				<div id="imgedit_colorleft">
					<img id="imgedit_colorsrc" src="" alt="Source image" title="Source image"/>
				</div>
			</div>
			<!-- End Colorize -->

			<!-- Help -->
			<div id="imgedit_help" class="clearfix">
				<h3>Crop</h3>
				<p>Click and drag on the left image to create a selection box.  The selection box can be moved or resized as desired.  The right image shows a preview of the thumbnail that will be created.</p>
				<p>Note: This tool affects the thumbnail only; the original image will remain unchanged.</p>
			</div>
			<!-- End Help -->
		</div>
	</form>
</div>

<div id="imgeditorlibrarydialog" title="Image Library" style="display: none">
	<form method="post" action="{$action}" name="imgeditform" enctype="multipart/form-data">
		<strong>Upload or Use a New File</strong><br/>
		New File: <input type="file" name="imgeditimage" size="60" /><br/>
		<input type="button" name="imgeditorlibraryupload" id="imgeditorlibraryupload" value="Upload to Library" />&nbsp;
		<input type="button" name="imgeditorlibraryselect" id="imgeditorlibraryselect" value="Use Immediately" />
		<hr/>
		<strong>Files and Folders in Library<strong><br/>
		<div style="clear: both; position: absolute; right: 10px; bottom: 10px;">
			<input type="button" id="imgeditorlibraryclose" value="Close" class="bold" />
		</div>
	</form>
</div>
EOT;

	// call post-PHP page build script code
	addHeadLine(
		'script',
		WEB_URL.ADMIN_FOLDER.PLUGINS_FOLDER."imgedit/js/",
		"imgedit.core.js",
		"",
		"",
		"",
		false,
		true
	);
}
?>