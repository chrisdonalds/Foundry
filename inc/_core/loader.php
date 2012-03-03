<?php
// ---------------------------
//
// PUBLIC SITE LOADER
// - Form & Page
//
// ---------------------------
if(!defined('VALID_LOAD')){
    define("VALID_LOAD", true);
    //define("VHOST", "/".((preg_match("/(badger|stonehenge|navigatormultimedia|localhost)/i", $_SERVER['HTTP_HOST'])) ? substr($_SERVER['PHP_SELF'], 1, strpos($_SERVER['PHP_SELF'], "/", 1)) : ""));
    define("VHOST", substr(str_replace("\\", "/", realpath(__DIR__."/../../")), strlen($_SERVER['DOCUMENT_ROOT']))."/");
    include ($_SERVER['DOCUMENT_ROOT'].VHOST."inc/_core/getinc.php");					// required - starts PHP incls!!!
}

if(!SITEOFFLINE){
	// site online

    // get file path parts
    $filespec = getRequestVar('f');
    if(isBlank($filespec)) $filespec = substr($_SERVER['REQUEST_URI'], strlen(VHOST));

    $fileparts = parse_url($filespec);
    $filename = getIfSet($fileparts['path']);

	$_page->name = $filename;
	$_page->file = $filename;
	if(preg_match("/[.]/", $_page->name)) {
		// remove extension from name, file is ok
		$_page->name = preg_replace("/([.].*)/", "", $_page->name);
	}else{
		// add .php to file, name is ok
		$_page->file .= ".php";
	}

    // get file query elements
    $fileparts = parse_url($_SERVER['REQUEST_URI']);
    $filequery = getIfSet($fileparts['query']);
    parse_str($filequery, $qv);
    if(count($qv) > 0) {
        $_page->queryvars = $qv;
    }else{
        $_page->queryvars = array();
    }

	if(file_exists(SITE_PATH.$_page->file)) {
		//... EC01: physical file found.  page will have to obtain data itself
        setupDirectPage($filename);
		@include_once(SITE_PATH.$_page->file);
        return;
	}elseif(setupPage()){
        //... EC02: go to {code}.php/page.php for dynamic page alias processing
        $file = SITE_PATH.$_page->code.".php";
        if(file_exists($file)){
            //... EC02A
            @include_once($file);
            return;
        }elseif(file_exists(SITE_PATH."page.php")){
            //... EC02B
            @include_once(SITE_PATH."page.php");
            return;
        }
    }elseif(setupPageData()){
        //... EC03: go to {metabase}.php/data.php for dynamic data alias processing
        $file = SITE_PATH.$_data->metabase.((!$_data->iscategory) ? "-details" : "").".php";
        if(file_exists($file)){
            //... EC03A
            @include_once($file);
            return;
        }elseif(file_exists(SITE_PATH."data.php")){
            //... EC03B
            @include_once(SITE_PATH."data.php");
            return;
        }
    }elseif(setupPageController()){
        //... EC04: call the applicable controller class method
        exit;
    }

    if(!$_page->found){
        $_page->error = '404';
        if(file_exists(SITE_PATH."404.php")){
            //... EC40: nothing to handle the URI, go to physical 404.php
            @include_once(SITE_PATH."404.php");
            return;
        }elseif(setupPage(true) && file_exists(SITE_PATH."page.php")){
            //... EC41: 404 database page
            @include_once(SITE_PATH."page.php");
            return;
        }else{
            //... EC42: safety net. output a watered-down 404-page
            echo "
            <h1>Oops, We Have a Problem!</h1>
            <p>The page or resource, ".strtoupper($_page->name).", was not found.</p>
            <p>As this message is presented on the rare occasion that the site has encountered a problem,
            we recommend than you:</p>
            <ul>
            <li>Discuss this issue with the webmaster or developer, or;</li>
            <li>Contact the administrator or hosting provider.</li>
            </ul>";
            exit;
        }
    }
}else{
	// site offline

	if(file_exists(SITE_PATH."offline.php")){
        //... EC10: offline page found
		@include_once(SITE_PATH."offline.php");
        exit;
	}else{
        //... EC11: simple offline statement
		echo "
		<h1>Site is Down for Maintenance</h1>
		<p>".SITEOFFLINE_MSG."</p>";
		exit;
	}
}
?>
