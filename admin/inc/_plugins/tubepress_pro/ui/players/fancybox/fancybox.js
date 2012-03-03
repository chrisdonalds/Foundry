/**
 * Copyright 2006 - 2010 Eric D. Hough (http://ehough.com)
 * 
 * This file is part of TubePress (http://tubepress.org) and is released 
 * under the General Public License (GPL) version 3
 *
 * Shrink your JS: http://developer.yahoo.com/yui/compressor/
 */
function tubepress_fancybox_player(galleryId, videoId) {
	var wrapperId 			= "#tubepress_embedded_object_" + galleryId,
		wrapper 			= jQuery(wrapperId),
		obj 				= jQuery(wrapperId + " > object"),
		params 				= obj.children("param");
	
	jQuery.fancybox({
		'content' 			: TubePress.deepConstructObject(wrapper, params),
		'height' 			: parseInt(obj.css("height").replace("px", ""), 10) + 5,
		'width' 			: parseInt(obj.css("width").replace("px", ""), 10) + 5,
		'autoDimensions' 	: false,
		'title' 			: jQuery("a[id='tubepress_image_" + videoId + "_" + galleryId + "'] img").attr("alt")
	});
}

function tubepress_fancybox_player_init(baseUrl) {
	var base 		= baseUrl + '/ui/players/fancybox/lib/',
		readyTest 	= function () { 
			return typeof jQuery.fancybox !== 'undefined';
		},
		init 		= function () { 
			jQuery.fancybox.init();
		};
		
	TubePressUtils.loadCss(base + 'jquery.fancybox-1.3.1.css');
	TubePressUtils.getWaitCall(base + 'jquery.fancybox-1.3.1.js', readyTest, init);
	if (jQuery.browser.msie) {
		tubepress_fancybox_css_init();
	}
}

function tubepress_fancybox_css_init (baseUrl) {
	var a 	= "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='",
		b 	= "', sizingMethod='scale')",
		c	= "background",
		d	= "transparent",
		e	= "filter",
		f	= "', sizingMethod='scale')"
	
	jQuery("#fancybox-loading.fancybox-ie div").css({c : d, e : a + "fancy_loading.png" + b});
	jQuery(".fancybox-ie #fancybox-close").css({c : d, e : a + "fancy_close.png" + b});
	jQuery(".fancybox-ie #fancybox-title-over").css({c : d, e : a + "fancy_title_over.png" + b});
	jQuery(".fancybox-ie #fancybox-title-left").css({c : d, e : a + "fancy_title_left.png" + b});
	jQuery(".fancybox-ie #fancybox-title-main").css({c : d, e : a + "fancy_title_main.png" + b});
	jQuery(".fancybox-ie #fancybox-title-right").css({c : d, e : a + "fancy_title_right.png" + b});
	jQuery(".fancybox-ie #fancybox-left-ico").css({c : d, e : a + "fancy_nav_left.png" + b});
	jQuery(".fancybox-ie #fancybox-right-ico").css({c : d, e : a + "fancy_nav_right.png" + b});
	jQuery(".fancybox-ie #fancy-bg-n").css({ e: a + "fancy_shadow_n.png" + f});
	jQuery(".fancybox-ie #fancy-bg-ne").css({ e: a + "fancy_shadow_ne.png" + f});
	jQuery(".fancybox-ie #fancy-bg-e").css({ e: a + "fancy_shadow_e.png" + f});
	jQuery(".fancybox-ie #fancy-bg-se").css({ e: a + "fancy_shadow_se.png" + f});
	jQuery(".fancybox-ie #fancy-bg-s").css({ e: a + "fancy_shadow_s.png" + f});
	jQuery(".fancybox-ie #fancy-bg-sw").css({ e: a + "fancy_shadow_sw.png" + f});
	jQuery(".fancybox-ie #fancy-bg-w").css({ e: a + "fancy_shadow_w.png" + f});
	jQuery(".fancybox-ie #fancy-bg-nw").css({ e: a + "fancy_shadow_nw.png" + f});
}
