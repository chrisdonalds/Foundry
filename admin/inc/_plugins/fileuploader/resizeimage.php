<?php
define('IMG_BAD_FORMAT','FILE: Invalid file format. ');
define('IMG_TOO_BIG','The uploaded file size exceeds limit');
define('IMG_DIM_TOO_BIG','The image [image] exceeds the maximum allowed dimensions');
define('IMG_DIM_WRONG_SIZE','The image [image] must be exactly the required dimensions');
define('IMG_FAILURE_COPY','File upload failed copying file to ');
define('IMG_PATH_UNWRITABLE','The upload path is not writable, please check permissions. ');
define('IMG_NOT_FOUND','Cannot find ');
define('IMG_NO_FILENAME','No filename provided. ');
define('IMG_CREATE_ERROR','Cannot create image GD. ');
define('IMG_RESIZE_ERROR','Cannot resize image GD. ');

define ("MAX_WIDTH", 350);
define ("MAX_HEIGHT", 500);

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

$root = $_SERVER['DOCUMENT_ROOT'];
$dir = "/images/data_products/";
$dir2 = "/images/data_products_new/";
$count = 0;
if($fh = opendir($root.$dir)) {
	while (false !== ($file = readdir($fh))) {
		if($file != '.' && $file != '..'){
			if(!file_exists($root.$dir.$file)) die("File '$file' not found!");
			echo "Processing: $file<br/>";
			resize($root.$dir.$file, $root.$dir2.$file, MAX_WIDTH, MAX_HEIGHT);
		}
	}
}

function resize($src, $dest, $maxwidth = MAX_WIDTH, $maxheight = MAX_HEIGHT){
	global $err, $img_prefix;

	$newfilename = "";

    //Check if GD extension is loaded
    if (!extension_loaded('gd') && !extension_loaded('gd2')){
        die("FileUploader: GD is not loaded.");
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
		        default: die(IMG_BAD_FORMAT);
		    }

			// Get new aspect size for thumbnail
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
			        imagesavealpha($newImg, true);
			        $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
			        imagefilledrectangle($newImg, 0, 0, $width, $height, $transparent);
			    }
			    $success = imagecopyresampled($newImg, $im, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);

			   	if($success){
				    //Save file
					if(file_exists($dest)) @unlink($dest);
				    switch ($image_type){
				        case TYPE_GIF: $success = imagegif($newImg, $dest); break;
				        case TYPE_JPG: $success = imagejpeg($newImg, $dest, 100); break;
				        case TYPE_PNG: $success = imagepng($newImg, $dest, 0); break;
				        default: die(IMG_RESIZE_ERROR);;
				    }

					if($success){
						chmod2($dest, "0666");
					    return $dest;
					}else{
						die(IMG_FAILURE_COPY.$dest);
					}
				}else{
					die(IMG_RESIZE_ERROR);
				}
			}else{
				die(IMG_CREATE_ERROR);
			}
		}else{
			die(IMG_NOT_FOUND.$src);
		}
	}else{
		die(MISSING_ARG);
	}
	return false;
}

function chmod2($file, $mode = null){
	if($mode == null) $mode = "0777";
	if($file != ''){
		$perms_str = getFilePerms($file, true);
		$mode_str = substr($mode, -3);			// we are only interested in the permissions not file type
		//echo "1: $perms_str <-> $mode_str<br>";
		if(intval($perms_str) < intval($mode_str)){
			// use PHP chmod (if web server has access)
			@chmod ($file, octdec($mode));
			$perms_str = getFilePerms($file, true);
		//echo "2: $perms_str <-> $mode_str<br>";
			if(intval($perms_str) < intval($mode_str)){
				// use system otherwise
				system ("chmod {$mode_str} {$file}");
				$perms_str = getFilePerms($file, true);
		//echo "3: $perms_str <-> $mode_str<br>";
				if(intval($perms_str) < intval($mode_str)) return false;
			}
		}
		return true;
	}
	return false;
}

function getFilePerms($file, $tostr = false){
	if($file != ''){
		clearstatcache();
		$rtn = substr(sprintf('%o', @fileperms($file)), -4);
		if(intval($rtn) == 0) $rtn = getFileACL($file, $tostr);
		return $rtn;
	}
	return false;
}
?>
