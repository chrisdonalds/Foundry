<?php
$filter = getRequestVar('x');
$dir = "/thumbs/".getRequestVar('thms');
$dir2 = "/thumbs/".getRequestVar('thms2');
$count = 0;

if($fh = opendir(SITE_PATH.$dir)) {
	while (false !== ($file = readdir($fh))) {
		if($file != '.' && $file != '..'){
			if(!file_exists(SITE_PATH.$dir.$file)) die("File '$file' not found!");
			echo 'Processing: ('.$filter.') '.$file.' from: <img src="'.WEB_URL.$dir.$file.'"/>';
			$image = imagecreatefromjpeg(SITE_PATH.$dir.$file);
			if($image){
				switch ($filter){
					case "emboss":
						$matrix  = array(array(2, 0, 0), array(0, -1, 0), array(0, 0, -1));
						$divisor = 1;
						$coloroffset = 127;
						break;
					case "sharpen":
						$matrix  = array(array(-1, -1, -1), array(-1, 16, -1), array(-1, -1, -1));
						$divisor = 8;
						$coloroffset = 0;
						break;
					case "gaussian":
						$matrix  = array(array(1.0, 2.0, 1.0), array(2.0, 4.0, 2.0), array(1.0, 2.0, 1.0));
						$divisor = 16;
						$coloroffset = 0;
					default:
						die("Filter '$filter' not recognized!");
						break;
				}

				imageconvolution($image, $matrix, $divisor, $coloroffset);
				//header('Content-Type: image/jpeg');
				imagejpeg($image, SITE_PATH.$dir2.$file, 100);
				imagedestroy($image);

				echo ', to: <img src="'.WEB_URL.$dir2.$file.'"/>';
				$count++;
			}
			echo '<br/>'.PHP_EOL;
		}
	}
	close($fh);
}
?>
