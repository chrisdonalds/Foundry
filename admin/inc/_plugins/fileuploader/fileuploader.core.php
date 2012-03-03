<?php
/*
FILEUPLOADER PLUG-IN
Web Template 3.0
========================================
File Uploading and progress indicator
Extends and requires showImageField function
*/

if(floatval(phpversion()) < 5) {
	addErrorMsg("PHP 5 or higher required for FileUploader!");
	exit;
}

define("IMAGE_TYPES", "'jpg', 'jpeg', 'png', 'gif'");
define("AUDIO_TYPES", "'mp3', 'wav', 'ai', 'mpa', 'wma', 'mpeg', 'mid', 'ogg'");
define("DOC_TYPES", "'docx', 'doc', 'pdf', 'xls', 'xlsx', 'wps', 'txt'");
define("VIDEO_TYPES", "'mpeg', 'mp4', 'm4a', 'wmv', 'ogm', 'avi', 'mov', 'flv'");
define("WEB_TYPES", "'htm', 'html', 'doc', 'xml', 'txt', 'eml', 'pdf'");
define("PDF_TYPES", "'pdf'");

define('TYPE_GIF', 1);
define('TYPE_JPG', 2);
define('TYPE_PNG', 3);
define('COMPRESSION', 6);

define('IMG_ORIG_RESIZE_TO_MAX', true);		// if true, FU will resize original image to IMG_MAX_WIDTH/HEIGHT

if(!defined("FU_FOLDER")){
    define("FU_FOLDER", IMG_UPLOAD_FOLDER."fileuploads/");
    define("FU_TEMPFOLDER", FU_FOLDER."temp/");
    define("FU_LIBFOLDER", FU_FOLDER."library/");
}

/**
 * Initialize the plugin scripts and styles
 */
function fileuploader_headerprep($attr = null){
	addHeadLine(
		'style',
		WEB_URL.ADMIN_FOLDER.PLUGINS_FOLDER."fileuploader/inc/",
		"fileuploader.css",
		"screen"
	);
	addHeadLine(
		'script',
		WEB_URL.ADMIN_FOLDER.PLUGINS_FOLDER."fileuploader/inc/",
		"fileuploader.js",
		""
	);
	define('FU_PLUGIN_NAME', $attr);
}

/**
 * Output the HTML code to enable the FileUploader script
 * @global string $incl
 * @param string $dest_folder
 * @param integer $width
 * @param integer $height
 * @param boolean $showButtonText
 * @param string $uploadFilename
 * @param boolean $allowdelete
 */
function showFileUploaderScript($dest_folder = "", $width = 100, $height = 100, $showButtonText = true, $uploadFilename = ''){
	global $incl;

	if(defined("FILEUPLOADER_INIT")) die("'showFileUploaderScript' can only be called once.  Instead call 'showImageField' for each image entry.");
	define("FILEUPLOADER_INIT", true);

	//folders/files
	$filever = "";
	$app_folder = WEB_URL.ADMIN_FOLDER.PLUGINS_FOLDER."fileuploader/";
	$web_folder = WEB_URL;
	if(strpos($dest_folder, 'FU_TEMPFOLDER') !== false) $dest_folder = '';
	if($dest_folder == "") $dest_folder = ADMIN_FOLDER.PLUGINS_FOLDER."fileuploader/uploads/";
	$dest_folder_svr = SITE_PATH.$dest_folder;
	$dest_folder_web = WEB_URL.$dest_folder;

	// ensure folders exist
	if(!file_exists($dest_folder_svr)) {
		@mkdir($dest_folder_svr);
		chmod2($dest_folder_svr);
	}

	$js_width = "";
	$css_width = "";
	if($width > 0) {
		$js_width = "img.style.width = \"{$width}px\";";
		$css_width = "width: {$width}px; ";
	}

	$js_height = "";
	$css_height = "";
	if($height > 0) {
		$js_height = "img.style.height = \"{$height}px\";";
		$css_height = "height: {$height}px; ";
	}

	echo PHP_EOL;
	echo '<!-- '.FU_PLUGIN_NAME.' -->'.PHP_EOL;

	// store dynamic values in JSON attribute
	$attr = array('app_folder' => $app_folder, 'web_folder' => $web_folder,
				  'dest_folder_svr' => $dest_folder_svr, 'dest_folder' => $dest_folder, 'dest_folder_web' => $dest_folder_web,
				  'uploadFilename' => $uploadFilename, 'width' => $width, 'height' => $height);
	showHiddenField(FU_PLUGIN_NAME."_params", urlencode(json_encode($attr)), true);

	// call post-PHP page build script code
	addHeadLine(
		'script',
		WEB_URL.ADMIN_FOLDER.PLUGINS_FOLDER."fileuploader/inc/",
		"fileuploader.core.js",
		"",
		"",
		"",
		false,
		true
	);
}

/**
 * Attach File Uploader via Javascript code block
 * @global atring $incl
 * @param array $elems
 * @param array $lastfiles
 * @param array $allowedExts
 */
function attachFileUploader($elems, $lastfiles, $lastthms, $allowedExts){
	global $incl;

	if(defined("FILEUPLOADER_ATTACH")) die("'attachFileUploader' can only be called once.  Instead call 'showImageField' for each image entry.");
	if(!is_array($lastfiles)) die("'attachFileUploader' parameter lastfiles must be an array.");
	if(!is_array($lastthms)) die("'attachFileUploader' parameter lastthms must be an array (empty array if not used).");
	define("FILEUPLOADER_ATTACH", true);

	print "\n<script language=\"javascript\" type=\"text/Javascript\">\n";
	if(is_array($elems)){
		foreach($elems as $key => $elem){
			$filetype = "";
			$extpattern = str_replace("\'", "", $allowedExts[$key]);
			$extpattern = str_replace("(, |,)", "|", $extpattern);
			$extpattern = "/($extpattern)/i";
			if (preg_match($extpattern, IMAGE_TYPES) != 0) $filetype = 'img';
			if (preg_match($extpattern, AUDIO_TYPES.", ".VIDEO_TYPES) != 0) $filetype = 'av';
			if (strpos($incl, "imgedit") !== false && $filetype == 'img') $filetype = 'imgedit';

			print "	createUploader(\"{$elem}\", \"{$lastfiles[$key]}\", \"{$lastthms[$key]}\", [{$allowedExts[$key]}], \"$filetype\");\n";
		}
	}else{
		$extpattern = str_replace("\'", "", $allowedExts);
		$extpattern = str_replace("(, |,)", "|", $extpattern);
		$extpattern = "/($extpattern)/i";
		if (preg_match($extpattern, IMAGE_TYPES) != 0) $filetype = 'img';
		if (preg_match($extpattern, AUDIO_TYPES.", ".VIDEO_TYPES) != 0) $filetype = 'av';
		if (strpos($incl, "imgedit") !== false && $filetype == 'img') $filetype = 'imgedit';

		print "	createUploader(\"{$elems}\", \"{$lastfile}\", [{$allowedExts}], \"{$filetype}\")\n";
	}
	print "</script>\n";
}

/**
 * Move the uploaded file to the destination, and optionally create thumbnail
 * @global string $err
 * @param string $modstatus
 * @param string $src
 * @param string $destfolder
 * @param string $destthumbfolder
 * @param boolean $createThumb
 * @param array $thumbdim (width, height)
 * @param array $altdim (alternate dimension -- eg. mid size)
 * @param string $altfile (alternate file name prefix)
 * @return array $retn_file, $retn_thm
 */
function FU_MoveTempFile($modstatus, $src, $destfolder, $destthumbfolder = "", $createThumb = false, $thumbdim = false, $altdim = false, $altfile = null){
	global $err;

	$err_occurred = false;
	$retn_file = "";
	$retn_thm = "";
	if($src != "" && $destfolder != "" && $modstatus != "deleted"){
		$sfile = basename($src);
		$ext = strtolower(FU_getExtension($sfile));
		$is_image = (strpos(IMAGE_TYPES, $ext) !== false);
		if(is_null($altfile)){
			$dfile = preg_replace("/( |\*|\?|\&|\+|'|`|\%|=|\||\<|\>|:|\/|\\|\"|\#039;|\#39;|\#034;|\#34;|\#)/", "", $sfile);
		}else{
			$dfile = $altfile.".".$ext;
		}
		$srcfolder = dirname($src);
		if(substr($srcfolder, -1, 1) != "/") $srcfolder .= "/";
		if(substr($destfolder, -1, 1) != "/") $destfolder .= "/";
		$dest = $destfolder.$dfile;
		chmod2(SITE_PATH.$srcfolder, "0777");
		chmod2(SITE_PATH.$destfolder, "0777");
		$fs_err = "";

		//if(!is_image) $createThumb = false;
		if($createThumb){
			if(substr($destthumbfolder, -1, 1) != "/") $destthumbfolder .= "/";
			chmod2(SITE_PATH.$destthumbfolder, "0777");
			$srcthumb = FU_TEMPFOLDER."thumbs/thm_".$sfile;
			$destthumb = $destthumbfolder."thm_".$dfile;
		}

		if($modstatus == "fileuploader"){
			// file has not yet been moved to destination (ie. new file)
			if($src != $dest){
				if(!file_exists(SITE_PATH.$destfolder)) {
					addErrorMsg("FileUploader: Destination '$destfolder' does not exist.");
					$err_occurred = true;
				}elseif(!file_exists(SITE_PATH.$src)) {
					addErrorMsg("FileUploader: Source file '$src' not found.  It might have already been moved if you reloaded this page.");
					$err_occurred = true;
				}else{
					if(file_exists(SITE_PATH.$dest)) unlink(SITE_PATH.$dest);
					chmod2(SITE_PATH.$src, "0777");
					if($is_image){
						// only work with images
						if(!IMG_ORIG_RESIZE_TO_MAX){
							// destination image is not manipluated, and is moved as is
							if(!rename(SITE_PATH.$src, SITE_PATH.$dest)){
								addErrorMsg("FileUploader.moveTempFile: Problem moving file to '{$dest}'");
								$err_occurred = true;
							}else{
								$retn_file = $dest;
							}
						}else{
							// destination image is resized (if required) before moving
							// we will use the GenerateThumb function with max image dimensions
							$imgdim = array(IMG_MAX_WIDTH, IMG_MAX_HEIGHT);
							$retn_file = FU_GenerateThumb(SITE_PATH.$src, SITE_PATH.$dest, $imgdim);
							$retn_file = substr($retn_file, strlen(SITE_PATH));
						}
					}
				}

				if($createThumb && !$err_occurred){
					// create thumbnail now.
					chmod2(SITE_PATH.$dest, "0757");
					$retn_file = $dest;
					if($thumbdim === false) $thumbdim = array(THM_MAX_WIDTH, THM_MAX_HEIGHT);
					$retn_thm = FU_GenerateThumb(SITE_PATH.$dest, SITE_PATH.$destthumb, $thumbdim, $altdim);
					$retn_thm = substr($retn_thm, strlen(SITE_PATH));
				}
			}else{
				// do nothing
				$retn_file = $dest;
				$retn_thm  = $destthumb;
			}
		}elseif($modstatus == "thumbmod"){
			// file is in src folder and fileupload temp thumbs folder
			// this status should only be set for thumbnail edits
			if($src != $dest){
				if(!file_exists(SITE_PATH.$destfolder)) {
					addErrorMsg("FileUploader: Destination '$destfolder' does not exist.");
					$err_occurred = true;
				}elseif(!file_exists(SITE_PATH.$src)) {
					addErrorMsg("FileUploader: Source file '$src' not found.  It might have already been moved if you reloaded this page.");
					$err_occurred = true;
				}else{
					if(file_exists(SITE_PATH.$dest)) unlink(SITE_PATH.$dest);
					chmod2(SITE_PATH.$src, "0757");
					if(!rename(SITE_PATH.$src, SITE_PATH.$dest)){
						addErrorMsg("FileUploader.moveTempFile: Problem moving file to '{$dest}'");
					}else{
						chmod2(SITE_PATH.$dest, "0757");
						$retn_file = $dest;
					}
				}
			}else{
				// do nothing
				$retn_file = $dest;
			}

			if($srcthumb != $destthumb && $createThumb && !$err_occurred){
				if(!file_exists(SITE_PATH.$destthumbfolder)) {
					addErrorMsg("FileUploader: Destination '$destthumbfolder' does not exist.");
					$err_occurred = true;
				}elseif(!file_exists(SITE_PATH.$srcthumb)) {
					addErrorMsg("FileUploader: Source file '$srcthumb' not found.  It might have already been moved if you reloaded this page.");
					$err_occurred = true;
				}else{
					if(file_exists(SITE_PATH.$destthumb)) unlink(SITE_PATH.$destthumb);
					chmod2(SITE_PATH.$srcthumb, "0757");
					if(!rename(SITE_PATH.$srcthumb, SITE_PATH.$destthumb)){
						addErrorMsg("FileUploader.moveTempFile: Problem moving file to '{$destthumb}'");
					}else{
						chmod2(SITE_PATH.$destthumb, "0757");
						$retn_thm = $destthumb;
					}
				}
			}else{
				// do nothing
				$retn_thm = $destthumb;
			}
		}else{
			// do nothing to either file
			$retn_file = $dest;
			$retn_thm = $destthumb;
		}

		// Some basic housecleaning
		$tmpfile = SITE_PATH.FU_TEMPFOLDER.$sfile;
		if(file_exists($tmpfile) !== false) unlink($tmpfile);
	}else{
		// return blanks since the input parameters were not complete
		$retn_file = "";
		$retn_thm = "";
	}

	return array($retn_file, $retn_thm);
}

/**
 * Generate a thumbnail from image file
 * @global array $err
 * @global string $img_prefix
 * @param string $src
 * @param string $dest
 * @param integer $maxwidth (optional)
 * @param integer $maxheight (optional)
 * @return boolean
 */
function FU_GenerateThumb($src, $dest, $dim1, $dim2 = false){
	global $err, $img_prefix;

	$newfilename = "";

    //Check if GD extension is loaded
    if (!extension_loaded('gd') && !extension_loaded('gd2')){
        addErrorMsg("FileUploader: GD is not loaded.");
        return false;
    }

	if($src != "" && $dest != ""){
		if(file_exists($src)){
		    //Get Image size info
		    list($orig_width, $orig_height, $image_type) = @getimagesize($src);

			//Create Image GD from src
		    switch ($image_type){
		        case TYPE_GIF: $im = imagecreatefromgif($src); break;
		        case TYPE_JPG: $im = imagecreatefromjpeg($src);  break;
		        case TYPE_PNG: $im = imagecreatefrompng($src); break;
		        default: addErrorMsg(IMG_BAD_FORMAT); return false;
		    }

			$successes = 0;
			for($n = 1; $n < 3; $n++){
				if(${'dim'.$n} !== false){
					// file name that can be altered
					switch($n){
						case 1:
							// regular thumb/image resize
							$the_dest = $dest;
							break;
						case 2:
							// alternate thumb
							$the_dest = str_replace("/thm_", "/thm_a_", $dest);
							break;
					}

					// Get new aspect size for thumbnail
					$maxwidth = ${'dim'.$n}[0];
					$maxheight = ${'dim'.$n}[1];

					$width = $orig_width;
					$height = $orig_height;
					if($height > $maxheight && $maxheight > 0 && $width > 0) {
						$ratio = $maxheight / $height;
						$height = $maxheight;
						$width = intval($ratio * $width);
					}
					if($width > $maxwidth && $maxwidth > 0 && $height > 0) {
						$ratio = $maxwidth / $width;
						$width = $maxwidth;
						$height = intval($ratio * $height);
					}

					//create new image GD
					$newImg = imagecreatetruecolor($width, $height);
					if($newImg){
						/* Check if this image is PNG or GIF, then set if Transparent*/
						if($image_type == TYPE_GIF || $image_type == TYPE_PNG){
							imagealphablending($newImg, false);
							imagesavealpha($newImg,true);
							$transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
							imagefilledrectangle($newImg, 0, 0, $width, $height, $transparent);
						}
						$success = imagecopyresampled($newImg, $im, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);

						if($success){
							//Save file
							if(file_exists($the_dest)) unlink($the_dest);
							switch ($image_type){
								case TYPE_GIF: $success = imagegif($newImg, $the_dest); break;
								case TYPE_JPG: $success = imagejpeg($newImg, $the_dest, 100); break;
								case TYPE_PNG: $success = imagepng($newImg, $the_dest, 0); break;
								default: addErrorMsg(IMG_RESIZE_ERROR); return false;
							}

							if($success){
								chmod2($the_dest, "0666");
								$successes++;
							}else{
								addErrorMsg(IMG_FAILURE_COPY.$the_dest);
							}
						}else{
							addErrorMsg(IMG_RESIZE_ERROR);
						}
					}elseif($n == 1){
						addErrorMsg(IMG_CREATE_ERROR);
					}
				}
			}
			if($successes > 0) return $the_dest;			// at least one file was created ok
		}else{
			addErrorMsg(IMG_NOT_FOUND.$src);
		}
	}else{
		addErrorMsg(MISSING_ARG);
	}
	return false;
}

/**
 * Get the file extension
 * @param string $str
 * @return string
 */
function FU_getExtension($str) {
    $i = strrpos($str,".");
    if (!$i) { return ""; }
    $l = strlen($str) - $i;
    $ext = substr($str,$i+1,$l);
    return $ext;
}

?>
