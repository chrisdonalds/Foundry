/**
 * Copyright 2006 - 2010 Eric D. Hough (http://ehough.com)
 * 
 * This file is part of TubePress (http://tubepress.org) and is released 
 * under the General Public License (GPL) version 3
 *
 * Shrink your JS: http://developer.yahoo.com/yui/compressor/
 */

function tubepress_longtail_matcher() {
    return /youtube\.com\/watch\?v=(.{11}).*/;
}
function tubepress_longtail_param() {
	return "flashvars";
}