<?php
// ---------------------------
//
// BASIC FILE UPLOAD PROCESSOR
//
// ---------------------------

//This function reads the extension of the file.
//It is used to determine if the file is an image by checking the extension.

if(!defined("VALID_LOAD")) die("This file cannot be accessed directly!");

define('TYPE_GIF', 1);
define('TYPE_JPG', 2);
define('TYPE_PNG', 3);
define('COMPRESSION', 6);
$thumb_dim = array("size" => THM_MAX_UPLOAD_SIZE, "w" => THM_MAX_WIDTH, "h" => THM_MAX_HEIGHT);

if(!function_exists('addErrorMsg')) {
	function initErrorMsg(){
		global $_err;

		if(!isset($_err)) $_err = array();
		$_err[STAT_ERR] = array();		// reserved for status message
		if(!isset($_err[RUNTIME_ERR])) $_err[RUNTIME_ERR] = array();
		if(!isset($_err[CORE_ERR])) $_err[CORE_ERR] = array();
		if(!isset($_err[DEBUGGER_ERR])) $_err[DEBUGGER_ERR] = array();
	}

	function addErrorMsg($msg, $err_grp = RUNTIME_ERR){
		global $_err;

		if(in_array($err_grp, array(CORE_ERR, STAT_ERR, RUNTIME_ERR, DEBUGGER_ERR)))
			$_err[$err_grp][] = $msg;
	}
}

/**
 * Upload a file
 * @global array $err
 * @global string $img_prefix
 * @param string $elemname
 * @param string $lastfile
 * @param string $delfile
 * @param string $to_path
 * @param array $allowed_exts
 * @param array $alt_max_size
 * @param boolean $showProgressBar
 * @return boolean
 */
function uploadFile($elemname, $lastfile, $delfile, $to_path, $allowed_exts, $alt_max_size = false){
	global $err, $img_prefix;

	preg_match("/([^\[]+)(([\[])?([0-9]+)([\]]))?/i", $elemname, $elemparts);
	$elemname = $elemparts[1];
	($elemparts[4] != "") ? $key = $elemparts[4] : $key = null;

	($key == null) ? $thisfile = $_FILES[$elemname]['name'] : $thisfile = $_FILES[$elemname]['name'][$key];
	if($lastfile != "") $savefile = getRequestVar($lastfile, false, ENT_QUOTES, $key);
	if($delfile != "") $delfile = getRequestVar($delfile, false, ENT_QUOTES, $key);
	if($delfile != "") {
		// delete file option chosen by user
		$savefile = deleteFile($to_path.$savefile);
	}

	if($thisfile != "") {
		if ($savefile != "") {
			// delete the old image since we are replacing it
			$savefile = deleteFile($to_path.$savefile);
		}

		/*
		if($showProgressBar){
			// Progress Box
			print "<div id=\"upload_dropshadow\"><div id=\"process_box\"><span id=\"msg\">Uploading...</span><br/><img id=\"process_bar\" src=\"".WEB_URL.ADMIN_FOLDER."images/loader.gif\" /><br/></div></div>\n";
			// Progress Box starter
			print "<script type=\"text/Javascript\">startUpload();</script>\n";
		}
		*/
		$savefile = doUploadFile($elemname, $key, $to_path, $allowed_exts, $alt_max_size, false);
		$savefile = basename($savefile);
		/*
		if($showProgressBar){
			// Progress Box closer
			print "<script type=\"text/Javascript\">stopUpload('".join(" ", $err)."', '".$thisfile."');</script>\n";
		}
		*/
	}

	return array($err, $savefile);
}

/**
 * Upload an image and thumb
 * @global array $err
 * @global string $img_prefix
 * @param string $elemname
 * @param string $lastimg
 * @param string $lastthm
 * @param string $delimg
 * @param string $to_imgpath
 * @param string $to_thmpath
 * @param array $allowed_exts
 * @param array $alt_max_size
 * @param integer $exactsize
 * @param string $nothmprefix
 * @return boolean
 */
function uploadImage($elemname, $lastimg, $lastthm, $delimg, $to_imgpath, $to_thmpath, $allowed_exts, $alt_max_size = false, $exactsize = false, $nothmprefix = false){
	global $err, $img_prefix;

	preg_match("/([^\[]+)(([\[])?([0-9]+)([\]]))?/i", $elemname, $elemparts);
	$elemname = $elemparts[1];
	($elemparts[4] != "") ? $key = $elemparts[4] : $key = null;
	($key == null) ? $thisfile = $_FILES[$elemname]['name'] : $thisfile = $_FILES[$elemname]['name'][$key];
	if($lastimg != "") $saveimg = getRequestVar($lastimg, false, ENT_QUOTES, $key);
	if($lastthm != "") $savethm = getRequestVar($lastthm, false, ENT_QUOTES, $key);
	if($delimg != "") $delimg = getRequestVar($delimg, false, ENT_QUOTES, $key);
	if($delimg != "") {
		// delete image option chosen by user
		list($saveimg, $savethm) = deleteImage($to_imgpath.$saveimg, $to_thmpath.$savethm);
	}
	if($thisfile != "") {
		if ($saveimg != "") {
			// delete the old image since we are replacing it
			list($saveimg, $savethm) = deleteImage($to_imgpath.$saveimg, $to_thmpath.$savethm);
		}
		$saveimg = doUploadFile($elemname, $key, $to_imgpath, $allowed_exts, $alt_max_size, $exactsize);
		if($saveimg != "") $savethm = generateThumb($elemname, $key, $saveimg, $to_thmpath, $nothmprefix);
		$saveimg = basename($saveimg);
		$savethm = basename($savethm);
	}
	return array($err, $saveimg, $savethm);
}

/**
 * Return file extension
 * @param string $str
 * @return string
 */
function getExtension($str) {
    $i = strrpos($str,".");
    if (!$i) { return ""; }
    $l = strlen($str) - $i;
    $ext = substr($str,$i+1,$l);
    return $ext;
}

/**
 * Generate a thumbnail
 * @param string $fieldname
 * @param string $src
 * @param string $dest
 * @return string|boolean
 */
function generateThumb($fieldname, $key, $src, $dest, $nothmprefix = false){
	global $err, $img_prefix;

	$dim = 0;
	$newfilename = "";

    //Check if GD extension is loaded
    if (!extension_loaded('gd') && !extension_loaded('gd2')){
        addErrorMsg("GD is not loaded.");
        return false;
    }

	if($fieldname != "" && $src != "" && $dest != ""){
		if($key == null){
			$filename = $img_prefix.stripslashes($_FILES[$fieldname]['name']);
		}else{
			$filename = $img_prefix.stripslashes($_FILES[$fieldname]['name'][$key]);
		}
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

			// Get new aspect size for thumbnail
			if (THM_MAX_WIDTH > 0) {
				// if $width > IMG_MAX_WIDTH, get reducer ratio and multiple by $height
				if($dim == 0) $dim = THM_MAX_WIDTH;
				if($orig_width > THM_MAX_WIDTH && $orig_height > 0) {
					$ratio = THM_MAX_WIDTH / $orig_width;
					$width = THM_MAX_WIDTH;
					$height = intval($ratio * $orig_height);
				}else{
					$width = $orig_width;
					$height = $orig_height;
				}
			}
			if (THM_MAX_HEIGHT > 0) {
				// if $height > IMG_MAX_HEIGHT, get reducer ratio and multiple by $width
				if($dim == 0) $dim = THM_MAX_HEIGHT;
				if($orig_height > THM_MAX_HEIGHT && $orig_width > 0) {
					$ratio = THM_MAX_HEIGHT / $orig_height;
					$height = THM_MAX_HEIGHT;
					$width = intval($ratio * $orig_width);
				}else{
					$width = $orig_width;
					$height = $orig_height;
				}
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
				    //Generate the file, and save it to $newfilename
					$filename = preg_replace("/[\(\) \\\*\?\'\"/]/i", "_", $filename);
					($nothmprefix) ? $newfilename = $dest.$filename : $newfilename = $dest."thm_".$filename;
					if(file_exists($newfilename)) @unlink($newfilename);
				    switch ($image_type){
				        case TYPE_GIF: $success = imagegif($newImg, $newfilename); break;
				        case TYPE_JPG: $success = imagejpeg($newImg, $newfilename); break;
				        case TYPE_PNG: $success = imagepng($newImg, $newfilename); break;
				        default: addErrorMsg(IMG_RESIZE_ERROR); return false;
				    }

					if($success){
						chmod2($newfilename, "0666");
					    return $newfilename;
					}else{
						addErrorMsg(IMG_FAILURE_COPY.$newfilename);
					}
				}else{
					addErrorMsg(IMG_RESIZE_ERROR);
				}
			}else{
				addErrorMsg(IMG_CREATE_ERROR);
			}
		}else{
			addErrorMsg(IMG_NOT_FOUND.$src);
		}
	}else{
		addErrorMsg(MISSING_ARG);
	}
	return false;
}

/**
 * Resize an image
 * @param string $src
 * @param string $target
 * @param string $dest
 * @param array $alt_max_size
 * @return string|boolean
 */
function resizeImage($src, $target, $dest, $alt_max_size){
	global $err;

	$newfilename = "";

	if($src != "" && $target != "" && $dest != ""){
		$src = $dest.$src;
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

			// Get new aspect size for image
			if ($alt_max_size['h'] > 0 && $orig_height >= $orig_width && $orig_height > 0) {
				// if $height > IMG_MAX_HEIGHT, get reducer ratio and multiple by $width
				if($orig_height != $alt_max_size['h']) {
					$ratio = $alt_max_size['h'] / $orig_height;
					$height = $alt_max_size['h'];
					$width = intval($ratio * $orig_width);
				}else{
					$width = $orig_width;
					$height = $orig_height;
				}
			}
			if ($alt_max_size['w'] > 0 && $orig_width >= $orig_height && $orig_width > 0) {
				// if $width > IMG_MAX_WIDTH, get reducer ratio and multiple by $height
				if($orig_width != $alt_max_size['w']) {
					$ratio = $alt_max_size['w'] / $orig_width;
					$width = $alt_max_size['w'];
					$height = intval($ratio * $orig_height);
				}else{
					$width = $orig_width;
					$height = $orig_height;
				}
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
				    //Generate the file, and save it to $newfilename
					$target = preg_replace("/[\(\) \\\*\?\'\"/]/i", "_", $target);
					$newfilename = $dest.$target;
					if(file_exists($newfilename)) @unlink($newfilename);
				    switch ($image_type){
				        case TYPE_GIF: $success = imagegif($newImg, $newfilename); break;
				        case TYPE_JPG: $success = imagejpeg($newImg, $newfilename); break;
				        case TYPE_PNG: $success = imagepng($newImg, $newfilename); break;
				        default: addErrorMsg(IMG_RESIZE_ERROR); return false;
				    }

					if($success){
						chmod2($newfilename, "0666");
						unlink($src);
					    return $newfilename;
					}else{
						addErrorMsg(IMG_FAILURE_COPY.$newfilename);
					}
				}else{
					addErrorMsg(IMG_RESIZE_ERROR);
				}
			}else{
				addErrorMsg(IMG_CREATE_ERROR);
			}
		}else{
			addErrorMsg(IMG_NOT_FOUND.$src);
		}
	}else{
		addErrorMsg(MISSING_ARG);
	}
	return false;
}

/**
 * Generate Photo XML file from database data and files
 * @param string $xmlfilepath
 * @param array $gallarray
 * @param array $photoarray
 * @param string $idrelation
 * @param string $imgpath
 * @param string $thmpath
 * @param string $gallflds
 * @param string $photoflds
 * @return boolean
 */
function updateGalleryXML($xmlfilepath, $gallarray, $photoarray, $idrelation = "gallery_id=id", $imgpath = "", $thmpath = "", $gallflds = "", $photoflds = ""){
	global $db;

	if($xmlfilepath == "" || $idrelation == "" || $gallflds == "" || $photoflds == "" || !is_array($gallflds) || !is_array($photoflds)) return false;

	if(is_array($gallarray)){
		// get array of galleries
		$gflds = join(", ", $gallflds);
		$galls = getRec($gallarray['table'], $gflds, $gallarray['crit'], $gallarray['order'], $gallarray['limit']);

		if(count($galls) > 0){
			// prepare values
			if($imgpath != "" && substr($imgpath, 0, strlen(WEB_URL)) != WEB_URL) $imgpath = WEB_URL.$imgpath;
			if(substr($imgpath, -1, 1) != "/") $imgpath .= "/";
			if($thmpath != "" && substr($thmpath, 0, strlen(WEB_URL)) != WEB_URL) $thmpath = WEB_URL.$thmpath;
			if(substr($thmpath, -1, 1) != "/") $thmpath .= "/";

			// create doctype
			$dom  = new DOMDocument("1.0", "UTF-8");
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;

			// create root (gallery) element
			$root = $dom->createElement("gallery"); $dom->appendChild($root);

			foreach($galls as $gall){
				// check data
				(trim($gall['thumb']) != "") ? $tn = $thmpath.$gall['thumb'] : $tn = "";
				// create album under gallery
				$album = $dom->createElement('album'); $root->appendChild($album);
				// create attribute nodes and values in album
				$attr = $dom->createAttribute('id'); $album->appendChild($attr);
				$atval = $dom->createTextNode($gall[$gallflds['code']]); $attr->appendChild($atval);
				$attr = $dom->createAttribute('title'); $album->appendChild($attr);
				$atval = $dom->createTextNode($gall[$gallflds['title']]); $attr->appendChild($atval);
				$attr = $dom->createAttribute('lgPath'); $album->appendChild($attr);
				$atval = $dom->createTextNode($imgpath); $attr->appendChild($atval);
				$attr = $dom->createAttribute('tnPath'); $album->appendChild($attr);
				$atval = $dom->createTextNode($thmpath); $attr->appendChild($atval);
				$attr = $dom->createAttribute('description'); $album->appendChild($attr);
				$atval = $dom->createTextNode($gall[$gallflds['descr']]); $attr->appendChild($atval);
				$attr = $dom->createAttribute('tn'); $album->appendChild($attr);
				$atval = $dom->createTextNode($tn); $attr->appendChild($atval);

				// get array of gallery photos
				$idrelpair = explode("=", $idrelation);
				$crit = $photoarray['crit'];
				if($crit != "") $crit .= " and ";
				$crit .= " ".$idrelpair[0]."='".$gall[$idrelpair[1]]."'";
				$pflds = join(", ", $photoflds);
				$photos = getRec($photoarray['table'], "*", $crit, $photoarray['order'], $photoarray['limit']);

				foreach($photos as $photo){
					// create img under album
					$img = $dom->createElement('img'); $album->appendChild($img);
					// create attribute nodes and values for img
					$attr = $dom->createAttribute('src'); $img->appendChild($attr);
					$atval = $dom->createTextNode($photo[$photoflds['file']]); $attr->appendChild($atval);
					$attr = $dom->createAttribute('title'); $img->appendChild($attr);
					$atval = $dom->createTextNode($photo[$photoflds['title']]); $attr->appendChild($atval);
					$attr = $dom->createAttribute('caption'); $img->appendChild($attr);
					$atval = $dom->createTextNode($photo[$photoflds['caption']]); $attr->appendChild($atval);
					$attr = $dom->createAttribute('link'); $img->appendChild($attr);
					$atval = $dom->createTextNode(""); $attr->appendChild($atval);
					$attr = $dom->createAttribute('target'); $img->appendChild($attr);
					$atval = $dom->createTextNode(""); $attr->appendChild($atval);
					$attr = $dom->createAttribute('pause'); $img->appendChild($attr);
					$atval = $dom->createTextNode(""); $attr->appendChild($atval);
					$attr = $dom->createAttribute('main'); $img->appendChild($attr);
					$atval = $dom->createTextNode(""); $attr->appendChild($atval);
				}
			}

			// save tree to file
			$dom->save(SITE_PATH.$xmlfilepath);

			// save tree to string
			//$xml = $dom->saveXML();
			//var_dump($xml);
			//print $xml;
			return true;
		}
	}
}

?>
