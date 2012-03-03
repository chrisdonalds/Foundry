<?php
// ---------------------------
//
// PUBLIC SITE GD FUNCTIONS
//
// ---------------------------
//

extract($_GET);
switch($op){
	case 'mask':
		maskText($st, $fs, $fg);
		break;
}

function maskText($string, $fontsize = 3, $fg = "#000000"){
	header ("Content-type: image/png");

	//Get string info
	$string = urldecode(base64_decode($string));

	//Get the size of the string
	$width = imagefontwidth($fontsize) * strlen($string);
	$height = imagefontheight($fontsize);

	//Create the image
	$img = @imagecreatetruecolor($width, $height)
	      or die("Cannot Initialize new GD image stream");

	//Make it transparent
	imagesavealpha($img, true);
	$trans_colour = imagecolorallocatealpha($img, 0, 0, 0, 127);
	imagefill($img, 0, 0, $trans_colour);

	//Get the text color
	$color = hex2RGB($fg);
	$text_color = imagecolorallocate($img, $color['red'], $color['green'], $color['blue']);

	//Draw the string
	imagestring($img, $fontsize, 0, 2, $string, $text_color);
	//imagettftext($img, $fontsize, 0, 0, 0, $text_color, "arial.ttf", $string);

	//Output the image
	imagepng($img);
	imagedestroy($img);
}

/**
 * Convert a hexadecimal color code to its RGB equivalent
 *
 * @param string $hexStr (hexadecimal color value)
 * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
 * @param string $seperator (to separate RGB values. Applicable only if second parameter is true.)
 * @return array or string (depending on second parameter. Returns False if invalid hex color value)
 */
function hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {
    $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
    $rgbArray = array();
    if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
        $colorVal = hexdec($hexStr);
        $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
        $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
        $rgbArray['blue'] = 0xFF & $colorVal;
    } elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
        $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
        $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
        $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
    } else {
        //return false; //Invalid hex color code
        $rgbArray['red'] = 0;
        $rgbArray['green'] = 0;
        $rgbArray['blue'] = 0;
    }
    return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
}
?>