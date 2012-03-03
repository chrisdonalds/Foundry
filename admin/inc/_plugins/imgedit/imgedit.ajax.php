<?php
/*
IMGEDIT PLUG-IN
Web Template 3.0
Version: 3.0
========================================
PHP AJAX handler complement
*/
define("BASIC_GETINC", true);
include("../../../loader.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	foreach($_REQUEST as $key => $value) $$key = urldecode($value);
	if($action == 'update'){
		if($src != '' && $dest != '' && $fw > 0 && $x >= 0 && $y >= 0 && $w > 0 && $h > 0){
			require('imgedit.process.php');

			$objImage = new ImageManipulation($src);
			if ($objImage->imageok){
				$objImage->setCrop($x, $y, $w, $h);
				$objImage->resize($fw);
				//$objImage->show();
				if(file_exists($dest)) @unlink($dest);
				$dir = dirname($dest);
				if($dir != '') chmod($dir, 0777);
				if(!$objImage->save($dest)) echo 'ImgEdit: Saving error!';
			}else{
				echo 'ImgEdit: Image load error!';
			}
		}else{
			echo 'ImgEdit: Image dimensioning, source, or destination error!';
			echo "<br/>src=$src, dest=$dest, fw=$fw, x=$x, y=$y, w=$w, h=$h";
		}
	}elseif($action == 'savetemp' || $action == 'saveinitialtemp'){
		if($src != ''){
			// relying on the FileUploader fileuploads folder,
			$incl = 'fileuploader';
			getInstalledPlugins();
			initPluginsandFrameworks();

			// filename is 'imgedit_' + time + '_nn'
			$filebase = 'imgedit_'.session_id().'_';

			// delete any initial temp images created during this user session
			// that are over 1 hour old
			$path = SITE_PATH.FU_TEMPFOLDER;
			$fh = opendir($path);
			$latesttime = 0;
			$latestindx = 0;
			if($fh){
				while($file = readdir($fh)){
					if(substr($file, 0, 7) == 'imgedit'){
						$filepath = $path."/".$file;
						$filetime = filemtime($filepath);
						if(time() - $filetime > 3600) {
							// clear all aged files
							unlink($filepath);
						}elseif(substr($file, 0, strlen($filebase)) == $filebase){
							if($action == 'saveinitialtemp'){
								// clear all session temp files
								unlink($filepath);
							}elseif($filetime > $latesttime){
								// get last session temp file
								$latesttime = $filetime;
								$latestindx = intval(substr($file, strlen($filebase)));
							}
						}
					}
				}
			}

			// store the src passed to one of 10 temp session files
			// and return the filename
			$latestindx++;
			if($latestindx > 9) $latestindx = 0;

			$ext = '.'.IMGEDIT_getExtension($src);
			$tempfile = $filebase.str_pad($latestindx, 2, '00', STR_PAD_LEFT).$ext;
			copy($src, $path.$tempfile);
			echo FU_TEMPFOLDER.$tempfile;
		}
	}
	exit;
}


/**
 * Get the file extension
 * @param string $str
 * @return string
 */
function IMGEDIT_getExtension($str) {
    $i = strrpos($str,".");
    if (!$i) { return ""; }
    $l = strlen($str) - $i;
    $ext = substr($str,$i+1,$l);
    return $ext;
}

?>