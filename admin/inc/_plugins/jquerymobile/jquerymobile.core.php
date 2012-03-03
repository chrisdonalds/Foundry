<?php
/*
JQUERY MOBILE FOR FOUNDRY
Web Template 3.0
jQuery Project
========================================
*/

function jquerymobile_headerprep(){
	$err = addHeadLine(
		'style',                                    // script, style, rss, or favicon
		'http://code.jquery.com/mobile/1.0.1/',		// path to the file
		'jquery.mobile-1.0.1.min.css',				// the name of the js, css, rss, ico file
		'all'                                       // the output media (screen, all, print...)
	);
	$err = addHeadLine(
		'script',                                   // script, style, rss, or favicon
		'http://code.jquery.com/mobile/1.0.1/',		// path to the file
		'jquery.mobile-1.0.1.min.js'				// the name of the js, css, rss, ico file
	);
}

?>