/**
 * Copyright 2006 - 2010 Eric D. Hough (http://ehough.com)
 * 
 * This file is part of TubePress (http://tubepress.org) and is released 
 * under the General Public License (GPL) version 3
 *
 * Shrink your JS: http://developer.yahoo.com/yui/compressor/
 */
function tubepress_tinybox_player(galleryId, videoId) {
    var wrapperId 	= "#tubepress_embedded_object_" + galleryId,
        wrapper 	= jQuery(wrapperId),
        obj 		= jQuery(wrapperId + " > object"),
        height 		= obj.css("height").replace("px", ""),
        width 		= obj.css("width").replace("px", ""),
        params      = obj.children("param"),
        newHtml     = TubePress.deepConstructObject(wrapper, params);
    
    TINY.box.show(newHtml,0,width,height,1);
}

function tubepress_tinybox_player_init(baseUrl) {
    var base = baseUrl + '/ui/players/tinybox/lib/';
    
    TubePressUtils.loadCss(base + 'style.css');
    jQuery.getScript(base + 'tinybox.js');
}

