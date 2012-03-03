<?php
// XML SITEMAP GENERATOR
//
// Author: Chris Donalds <chrisd@navigatormm.com>
// Date: January 13, 2010
// License: GPL
// -----------------------------------------------------
// NOTE: ONLY EDIT AFTER THE 'ONLY EDIT BELOW HERE' MARK
// -----------------------------------------------------
//
// *** Setting the Inclusions and Exclusions:
// *** (Lines 76 to 121)
//  dirs must either begin with http://, https:// or /
//  extensions must start with .
//
//  - Excluding (skipping) a file -
//		eg. excludeit('filename.php');
//  - Excluding (skipping) a folder and all subfolders -
//		eg. excludeit($dir.'/folder');
//  - Excluding (skipping) an extension -
//		eg. excludeit('.jpg');
//
//  - Including a file -
//		eg. includeit('filename.php');
//  - Including a folder and all contents -
//		eg. includeit($dir.'/folder');
//  - Including an extension -
//		eg. includeit('.jpg');
//
// *** Calling it from your browser:
//
//	www.website.com/inc/_plugins/sitemapgen/xmlsitemapgen.php{?option1=value{&option2=value}...}
//  TYPICAL CALL: www.website.com/inc/_plugins/sitemapgen/xmlsitemapgen.php?verbose=1
//
// Options List:
//  - Verbosity (DEFAULT = off) -
//	 verbose=1 (turns verbosity on -- outputs processing information to screen)
//	 verbose=0 (turns verbosity off -- no output to browser screen)
//
//	- Change Frequency (DEFAULT = weekly) -
//	 chgfreq=string (set to daily, weekly, monthly, quarterly, yearly to tell search engines how often to update)
//
//	- Priority (DEFAULT = 50%) -
//	 priority=number (set to number between 0.1 (10%) and 1.0 (100%))
//
//	- First Index Required (DEFAULT = off) -
//	 firstindex=1 (tells xmlsitemapgen to save first xml file as sitemap1.xml)
//	 firstindex=0 (tells xmlsitemapgen to save first sml file as sitemap.xml)
//
//	- Page Name Parameter (DEFAULT = ignored) -
//	 pagenamevar=string (tells xmlsitemapgen where to look for the page title in the PHP pages
//						 eg. $pagename = 'Real Page Name';)
//
//	- Database Parser (DEFAULT = ignored) -
//	 dbfile=1 (forces xmlsitemapgen to process the database as specified in xmlsitemapgen-dbfile.php.  a separate inc file for the output is created)
//	 dbfile=0 (xmlsitemapgen will NOT process database records)
//
// *** Including it from another PHP file:
//
//	$_GET['chgfreq'] = "monthly"; $_GET['priority'] = 1; $_GET['getsitepage'] = 0; $_GET['dbfile'] = 1; $_GET['verbose'] = 0;
//	require(SITE_PATH.PLUGINS_FOLDER."sitemapgen/xmlsitemapgen.php");

$site_subfolder = "";								// if website is in sub-folder, replace "" with "/foldername"
$sm_dir = $_SERVER['DOCUMENT_ROOT'].$site_subfolder;
$http = "http://".$_SERVER['HTTP_HOST'].$site_subfolder;
$skipdirs = array();
$skipfiles = array();
$skipexts = array();
$incldirs = array();
$inclfiles = array();
$inclexts = array();
$filelist = array();
$pathlist = array();
$datalist = array();
$datalist_trans = array();

// -----------------------------------
// ------- EDIT FROM HERE ONLY -------
// -----------------------------------

$sitemapgen_folder = "inc/_plugins/sitemapgen/";

// >>>>> LIST STYLES <<<<<
// make sure you have included any styles in your site css file.  sitemapgen does not provide css formatting
$sm_style = " id=\"sitemap\"";

// >>>>> DESTPATH <<<<
// ... do not start with or set to only a slash!
$destpath = "";

// >>>> INCLUSIONS <<<
// ... folders (always start with / and do not end with / (eg: $sm_dir."/folder")
includeit(array(
				)
		);
// ... files
includeit(array(
				)
		);
// ... extensions (eg. ".php")
includeit(array(".php"
				)
		);

// >>>> EXCLUSIONS <<<
// ... folders (always start with / and do not end with / (eg: $sm_dir."/folder")
excludeit(array($sm_dir."/css",
				$sm_dir."/gallery",
				)
		);
// ... files
excludeit(array("apply.php",
				"aside.php",
				"news-detail.php",
				"project-detail.php",
				)
		);
// ...extensions (eg. ".php")
excludeit(array(
				)
		);

// -----------------------------------
// ---- DO NOT EDIT BELOW HERE!!! ----
// -----------------------------------

// read in values passed by url get
$verbose = (intval($_GET['verbose']) == 1);						// verbose = 0/1 to set whether to output results
$chgfreq = trim(htmlentities($_GET['chgfreq']));				// chgfreq = ... to set changefreq xml var
$priority = sprintf("%01.2f", floatval($_GET['priority']));		// priority = ... to set priority xml var
$firstindex = intval($_GET['firstindex']);						// firstindex = 0/1 to set whether first xml file is sitemap.xml or sitemap1.xml
$gensitepage = (intval($_GET['gensitepage']) == 1);				// gensitepage = 0/1 to set whether to generate sitemap.inc file
$pagenamevar = trim($_GET['pagenamevar']);						// pagenamevar = ... to set the PHP page name var to watch
$dbfile = (intval($_GET['dbfile']) == 1);						// dbfile = 0/1 to set the optional name of the dbfile.inc file

excludeit(array("xmlsitemapgen.php",
				"header.php",
				"footer.php",
				"config.php",
				"template.php",
				"contact-thankyou.php",
				"thank-you.php",
				"robots.txt",
				"formtoemailpro.php",
				"aside.php",
				"aside-left.php",
				"aside-right.php",
				"genpage.php",
				"search-results.php",
				"news-detail.php",
				$sm_dir."/admin",
				$sm_dir."/inc",
				$sm_dir."/css",
				$sm_dir."/images",
				$sm_dir."/files",
				$sm_dir."/thumbs",
				$sm_dir."/ckfinder",
				$sm_dir."/ckeditor",
				$sm_dir."/sitemapgen",
				$sm_dir."/rss",
				$sm_dir."/styles",
				$sm_dir."/cgi-bin",
				$sm_dir."/system",
				$sm_dir."/backups",
				$sm_dir."/tii",
				$sm_dir."/new",
				$sm_dir."/_notes",
				".bak"
			)
		);

$sm_dir = unprependslash(appendslash($sm_dir));
if(!defined('SYS_NAME')) sm_start();

function sm_start($verbose, $chgfreq, $priority, $firstindex, $gensitepage, $pagenamevar, $dbfile){
	global $sm_dir, $http, $destpath;
	global $incldirs, $inclfiles, $inclexts, $skipdirs, $skipfiles, $skipexts;

	if($pagenamevar == "") $pagenamevar = "\$pagename";
	if($chgfreq == "") $chgfreq = "weekly";
	if($priority == 0) $priority = 0.5;
	if($firstindex != 0) $firstindex = 1;

	if($verbose){
		// ---- HEADER ----
		print "XMLSITEMAPGEN<br>\n";
		print "-------------------------<br>\n";
		print "<br>\n";
		print "Usage: www.website.com/inc/_plugins/sitemapgen/xmlsitemapgen.php{?paramlist}<br>\n";
		print "Parameters:<br>\n";
		print "......verbose=n (n = 0 for silent, 1 for detailed [default = 0])<br>\n";
		print "......chkfreq=s (s = daily, weekly, monthly, yearly [default = weekly])<br>\n";
		print "......priority=n (n = 0.1 to 1.0 [default = 0.5])<br>\n";
		print "......firstindex=n (n = 0 for sitemap.xml, 1 for sitemap1.xml [default = 0])<br>\n";
		print "......gensitepage=n (n = 0 to not create sitemap.inc, 1 to create sitemap.inc [default = 0]<br>\n";
		print "......pagenamevar=s (s = varname to have xmlsitemapgen look for pagename in varname value [default = null])<br>\n";
		print "......dbfile=n (n = 0 to skip db processing, 1 to process db data and create separate db.inc file) [default = 0])<br>\n";
		print "<br>Remember:<br>";
		print "   - copy sitemap.xml to the root folder!<br>\n";
		print "   - CHMOD sitemap.xml, sitemap.inc and db.inc to 777!<br>\n";
		print "   - PHP include sitemap.inc in your sitemap page (db.inc if created will be included automatically)<br>\n";
		print "<hr>\n";
	}

	// ---- GENERATE FILES LIST ----

	if($verbose){
		print "FILES/FOLDERS EXCLUDED...";
		print "<pre>";
		if(count($skipdirs) > 0){
			print "-- Folders<br>";
			print_r($skipdirs);
			reset($skipdirs);
		}
		if(count($skipfiles) > 0){
			print "-- Files<br>";
			print_r($skipfiles);
			reset($skipfiles);
		}
		if(count($skipexts) > 0){
			print "-- Extensions<br>";
			print_r($skipexts);
			reset($skipexts);
		}
		print "</pre>";
		print "FILES/FOLDERS INCLUDED...";
		print "<pre>";
		if(count($incldirs) > 0){
			print "-- Folders<br>";
			print_r($incldirs);
			reset($incldirs);
		}
		if(count($inclfiles) > 0){
			print "-- Files<br>";
			print_r($inclfiles);
			reset($inclfiles);
		}
		if(count($inclexts) > 0){
			print "-- Extensions<br>";
			print_r($inclexts);
			reset($inclexts);
		}
		print "</pre>";
		print "<hr/>\n";
	}

	$filelist = getFilesinDir($sm_dir);
	sort($filelist);
	if($verbose){
		print "FILES/FOLDERS PROCESSED IN ".strtoupper($sm_dir)."...<br/><br/>\n";
		foreach($filelist as $key => $path){
			print "[$key] => $path [<a href=\"".str_replace("/".$sm_dir, $http."/", $path)."\" target=\"_blank\">Review</a>]<br/>\n";
		}
		print "<hr/>\n";
		reset($filelist);
	}

	if($dbfile) {
		if(defined("SITE_PATH")) {
			include (SITE_PATH.PLUGINS_FOLDER."sitemapgen/xmlsitemapgen_dbfile.php");
		}else{
			include ("xmlsitemapgen_dbfile.php");
		}
		getDBData();
		list($filelist, $datalist) = integrateAddAfterDBLines();
	}

	// ---- GENERATE XML NOW ----

	$counter = 0;
	$file_number = 1;
	$xml_output = "";
	$sm_dir = prependslash(unappendslash($sm_dir));
	foreach($filelist as $file) {
		$httpfile = str_replace($sm_dir, $http, $file);
		write_xml($httpfile, $priority, $chgfreq);
	}
	if($dbfile){
		foreach($datalist as $data) {
			write_xml($data['url'], $priority, $chgfreq);
		}
	}
	write_xml("END");

	// ---- GENERATE SITEMAP PAGE NOW ----

	if($gensitepage){
		getPathsList();
		write_smpage();
	}

	if($dbfile){
		write_smdbpage();
	}
}

function excludeit($array){
	global $skipdirs, $skipfiles, $skipexts;

	if(is_array($array)){
		foreach($array as $str){
			if(substr($str, -4, 1) != "."){
				// dir
				$skipdirs[] = strtolower($str);
			}elseif(substr($str, 0, 1) == "."){
				// ext
				$skipexts[] = substr(strtolower($str), 1);
			}else{
				// file
				$skipfiles[] = strtolower($str);
			}
		}
	}
}

function includeit($array){
	global $incldirs, $inclfiles, $inclexts;

	if(is_array($array)){
		foreach($array as $str){
			if(substr($str, -4, 1) != "."){
				// dir
				$incldirs[] = strtolower($str);
			}elseif(substr($str, 0, 1) == "."){
				// ext
				$inclexts[] = substr(strtolower($str), 1);
			}else{
				// file
				$inclfiles[] = strtolower($str);
			}
		}
	}
}

function getFilesinDir($sm_dir = '.'){
	global $skipdirs, $skipfiles, $skipexts, $incldirs, $inclfiles, $inclexts, $verbose;

	$sm_dir = prependslash(appendslash($sm_dir));
    if(!is_dir($sm_dir)) return false;

	$rule = array();
	$rule['skipdir'] = (count($skipdirs)>0);
	$rule['skipfile'] = (count($skipfiles)>0);
	$rule['skipext'] = (count($skipexts)>0);
	$rule['incldir'] = (count($incldirs)>0);
	$rule['inclfile'] = (count($inclfiles)>0);
	$rule['inclext'] = (count($inclexts)>0);

    $files = array();
    if($dirhandle = opendir($sm_dir)){
        while(false !== ($file = readdir($dirhandle))){
            // Skip '.' and '..'
            if( $file == '.' || $file == '..') continue;

			$path = strtolower($sm_dir . $file);
            if(is_dir($path)){
            	$ok = true;
            	if(($rule['skipdir'] == 1) && $ok) foreach($skipdirs as $value) if(substr($path, 0, strlen($value)) == $value) { $ok = false; break; }
            	if(($rule['incldir'] == 1) && $ok) foreach($incldirs as $value) if(substr($path, 0, strlen($value)) == $value) { $ok = true; break; }

            	if($ok == true){
            		// add array of files in dir to files array
            		$files = array_merge($files, getFilesinDir($path));
				}
            }else{
				$ok = true;
           		$info = pathinfo($path);
				$file = strtolower($info['basename']);
            	$ext = strtolower($info['extension']);			// simple extension
				$fullext = explode(".", $file);
				unset($fullext[0]);								// in case extension is complex (.bak.123.php)

             	if($rule['skipfile']!=false) $ok &= (!in_array($file, $skipfiles));
            	if($rule['skipext']!=false) $ok &= (!in_array($ext, $skipexts));
				if(count($fullext) >= 1){
					$matchext = array_intersect($fullext, $skipexts);
					$ok &= (count($matchext) == 0);
				}
            	if($rule['inclfile']!=false) $ok &= (in_array($file, $inclfiles));
            	if($rule['inclext']!=false) $ok &= (in_array($ext, $inclexts));
            	if($ok == true){
                	$files[] = $path;
				}
			}
        }
        closedir($dirhandle);
    }elseif($verbose){
    	print "<span style=\"color: red\">>>> CANNOT ACCESS ".strtoupper($sm_dir)." <<<</span><br>";
    }
    return $files;
}

function getPathsList(){
	global $pathlist, $filelist, $verbose;

	foreach($filelist as $file){
		$sm_dir = appendslash(pathinfo($file, PATHINFO_DIRNAME));
		if(!in_array($sm_dir, $pathlist) && strpos($file, ">") === false) $pathlist[] = $sm_dir;
	}
	sort($pathlist);
	if($verbose){
		print "<br/>PATHS LIST<br/>";
		print "<pre>".print_r($pathlist, true)."</pre>";
	}
}

function write_xml($url, $priority = 0.5, $chgfreq = "weekly"){
	global $sm_dir, $http, $handle, $counter, $file_number, $filename, $verbose, $destpath, $firstindex, $xml_output;

	//$filedir = appendslash($sm_dir.dirname($_SERVER['PHP_SELF']));
	$filedir = appendslash($sm_dir);
	if($counter >= 50000){
        $file_number++;
        $counter = 0;
        // Attach end of file, and close it here.
        $xml_output .= "</urlset>\n";
        if(file_write_contents($filedir.$filename, $xml_output) !== false){
			if($verbose) {
				print "&lt;/urlset&gt;<br>";
				if(confirmFileExists($filedir, $filename)) moveFile($filedir.$filename, appendslash($sm_dir).$filename);
			}
		}else{
			if($verbose)
				print "<span style=\"color: red\">>>> ".strtoupper($filedir.$filename)." DOES NOT ALLOW WRITING <<<</span><br>";
		}
	}

    if($counter == 0){
       	// Open next file here.
		if($firstindex || $file_number > 1){
        	$filename = $destpath."sitemap" . $file_number . ".xml";
		}else{
        	$filename = $destpath."sitemap.xml";
		}

        $xml_output  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml_output .= "<urlset\n";
		$xml_output .= "      xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n";
		$xml_output .= "      xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n";
		$xml_output .= "      xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\n";
        $xml_output .= "			http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">\n";
		$xml_output .= "<!-- created with Navigator Multimedia XMLSiteMapGen www.navigatormultimedia.com -->\n\n";
		if($verbose){
			print "STARTING OUTPUT OF ".strtoupper($filedir.$filename)."...<br><br>";
			print "&lt;?xml version=\"1.0\" encoding=\"UTF-8\"?&gt;<br>";
			print "&lt;urlset<br>";
			print "      xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"<br>";
			print "      xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"&gt;<br>";
			print "      xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9<br>";
			print "            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\"&gt;<br>";
			print "&lt;!-- created with Navigator Multimedia XMLSiteMapGen www.navigatormultimedia.com --&gt;<br><br>";
		}
	}

    if($url != "" && $url != "END"){
        $xml_output .= " <url>\n";
        $xml_output .= "  <loc>$url</loc>\n";
        $xml_output .= "  <changefreq>$chgfreq</changefreq>\n";
        $xml_output .= "  <priority>$priority</priority>\n";
        $xml_output .= " </url>\n";
		if($verbose){
	        print " &lt;url&gt;<br>";
	        print "  &lt;loc&gt;$url&lt;/loc&gt;<br>";
	        print "  &lt;changefreq&gt;$chgfreq&lt;/changefreq&gt;<br>";
	        print "  &lt;priority&gt;$priority&lt;/priority&gt;<br>";
	        print " &lt;/url&gt;<br>";
		}
	}

    if($url == "END"){
        $xml_output .= "</urlset>\n";
        if(file_write_contents($filedir.$filename, $xml_output) !== false){
			if($verbose) {
				print "&lt;/urlset&gt;<br>";
				confirmFileExists($filedir, $filename);
			}
		}else{
			if($verbose)
				print "<span style=\"color: red\">>>> ".strtoupper($filedir.$filename)." DOES NOT ALLOW WRITING <<<</span><br>";
		}
	}

	// Increment counter for every URL.
    $counter++;
}

function write_smpage(){
	// process files list sending line output to sitemap.inc file as list
	global $http, $filelist, $site_subfolder, $sitemapgen_folder, $datalist, $datalist_trans, $pathlist, $verbose, $destpath, $pagenamevar, $verbose, $sm_style;

	$filedir     = appendslash($_SERVER['DOCUMENT_ROOT']);
	$httproot	 = appendslash($http);
	$startoflist = "<ul$sm_style>\n";
	$endoflist   = "</ul>\n";
	$endoflist2  = "</ul>\n";
	$startofline = "<li>";
	$endofline   = "</li>\n";
	$incldbfile  = "<?php include(\"db.inc\");?>\n";
	$lastpath    = "";
	$list		 = array();
	$indentcount = 0;

	// files/paths
	if($verbose) print "STARTING OUTPUT OF SITEMAP PAGE...<br>\n";
	//$list[] = "<h4>Site Map</h4><br/><br/>\n";
	foreach($pathlist as $pathitem){
		// strip off root
		if($lastpath != $pathitem){
			$pathitem = striplead($pathitem, $filedir);
			if(substr($pathitem, 0, strlen($lastpath)) == $lastpath){
				// subfolder of last path
			}else{
				// new path
				// end last list
				if($verbose) print $endoflist;
				$list[] = $endoflist;
				$indentcount--;
			}
			if($pathitem != "") {
				if($verbose) print $startofline."<b>".strtoupper(unappendslash($pathitem))."</b>".$endofline;
				$list[] = $startofline."<b>".strtoupper(unappendslash($pathitem))."</b>".$endofline;
			}
			if($verbose) print $startoflist;
			$list[] = $startoflist;
			$lastpath = $pathitem;
			$indentcount++;
		}

		foreach($filelist as $fileitem){
			$sm_dir = appendslash(pathinfo($fileitem, PATHINFO_DIRNAME));
			$sm_dir = striplead($sm_dir, $filedir);
			$fileitem = striplead($fileitem, $filedir);
			$subitem = "";
			if(strpos($fileitem, ">") !== false){
				$fileitem = rtrim($fileitem, ">");
				$subitem = " - ";
			}
			$totop = false;
			if($sm_dir == $pathitem || $subitem != ''){
				// get page name
				$pagevarfound = false;
				$handle = fopen($filedir.$fileitem, "r");
				if($handle){
					while($buffer = fgets($handle)){
						if(strpos($buffer, $pagenamevar) !== false){
							// pagename var
							preg_match("/(.+)\=( +)'(.+)'/", $buffer, $chunks);
							if($chunks[3] != "" && strpos($chunks[3], "\$") === false) {
								if($chunks[3] == "index" && $sm_dir == "") { $chunks[3] = "home"; $totop = true; }
								$chunks[3] = str_replace("-", " ", $chunks[3]);
								if($verbose) print $startofline.$subitem."<a href=\"".$httproot.$fileitem."\">".ucwords(htmlentities($chunks[3]))."</a>".$endofline;
								if($totop){
									$elem = $startofline.$subitem."<a href=\"".$httproot.$fileitem."\">".ucwords(htmlentities($chunks[3]))."</a>".$endofline;
									array_splice($list, 1, 0, $elem);
								}else{
									$list[] = $startofline.$subitem."<a href=\"".$httproot.$fileitem."\">".ucwords(htmlentities($chunks[3]))."</a>".$endofline;
								}
								$pagevarfound = true;
								break;
							}
						}
					}
					fclose($handle);
				}

				if(!$pagevarfound){
					// use fileitem name instead
					// strip off extension
					$filepath = basename($fileitem);
					if($filepath != ''){
						// have page name
						$fileroot = substr($filepath, 0, strpos($filepath, "."));
						if($fileroot == ''){
							// page is a virtual page (/path/page)
							// look for page in datalist array, url element
							$fileroot = $datalist_trans[$fileitem];
						}elseif($fileroot == "index" && $sm_dir == "") {
							$fileroot = "home"; $totop = true;
						}
					}
					$fileroot = str_replace(array("-", "_"), " ", $fileroot);
					$fileitem = ltrim($fileitem, "/");
					if($verbose) print $startofline.$subitem."<a href=\"".$httproot.$fileitem."\">".ucwords(htmlentities($fileroot))."</a>".$endofline;
					if($totop){
						$elem = $startofline.$subitem."<a href=\"".$httproot.$fileitem."\">".ucwords(htmlentities($fileroot))."</a>".$endofline;
						array_splice($list, 1, 0, $elem);
					}else{
						$list[] = $startofline.$subitem."<a href=\"".$httproot.$fileitem."\">".ucwords(htmlentities($fileroot))."</a>".$endofline;
					}
				}
			}
		}
	}
	if($indentcount > 0) for($i=0; $i<$indentcount; $i++) {
		$list[] = $endoflist2;
		if($verbose) print $endoflist2."\n";
	}

	// finalize main sitemap.inc file
	if(count($list) > 0){
		$handle = fopen($filedir.$site_subfolder.$sitemapgen_folder."sitemap.inc", "w+");
		if($handle === false || !$handle) die("Cannot create file '".$filedir.$site_subfolder.$sitemapgen_folder."sitemap.inc'!");
		foreach($list as $listitem){
        	fwrite($handle, $listitem);
		}
		fwrite($handle, $incldbfile);
		fwrite($handle, $endoflist);
		fclose($handle);
		if($verbose) confirmFileExists($filedir.$site_subfolder.$sitemapgen_folder, "sitemap.inc");
	}
}

function write_smdbpage(){
	// process files list sending line output to db.inc file as list
	global $http, $filelist, $site_subfolder, $sitemapgen_folder, $datalist, $pathlist, $verbose, $destpath, $pagenamevar, $verbose, $sm_style;

	$filedir     = rtrim(appendslash($_SERVER['DOCUMENT_ROOT']), "/");
	$httproot	 = appendslash($http);
	$startoflist = "<ul$sm_style>\n";
	$endoflist   = "</ul>\n";
	$endoflist2  = "</ul>\n";
	$startofline = "<li>";
	$endofline   = "</li>\n";
	$incldbfile  = "<?php include(\"db.inc\");?>\n";
	$lastpath    = "";
	$list = array();
	$dblist = array();
	$indentcount = 0;

	// files/paths
	if($verbose) print "STARTING OUTPUT OF SITEMAP DB.INC PAGE...<br>\n";
	$catstarted = false;
	foreach ($datalist as $data){
		if($data['ignore']) continue;

		if($data['page'] != "") {
			/*
			if($catstarted) { $dblist[] = $endoflist2; $dblist[] = $endoflist2; }
			$dblist[] = "<h3>".strtoupper($data['page'])."</h3>";
			if($verbose) {
				if($catstarted) print $endoflist2.$endoflist2;
				print "<h3>".strtoupper($data['page'])."</h3>\n";
			}
			*/
		}elseif($data['cat'] != ""){
			if($catstarted) { $dblist[] = $endoflist2; $dblist[] = $endoflist2; }
			$dblist[] = $startoflist;
			if($data['url'] != ""){
				$catline = $startofline."<b><a href=\"".$data['url']."\">".($data['cat'])."</a></b>".$endofline;
			}else{
				$catline = $startofline."<b>".($data['cat'])."</b>".$endofline;
			}
			$dblist[] = $catline;
			$dblist[] = $startoflist;
			if($verbose) {
				if($catstarted) print $endoflist2.$endoflist2;
				print $startoflist;
				print $catline;
				print $startoflist;
			}
			$catstarted = true;
		}elseif($data['item'] != ""){
			$dblist[] = $startofline."<a href=\"".$data['url']."\">".($data['item'])."</a>".$endofline;
			if($verbose) {
				print $startofline."<a href=\"".$data['url']."\">".($data['item'])."</a>".$endofline;
			}
		}
	}
	if($catstarted) {
		$dblist[] = $endoflist2; $dblist[] = $endoflist2;
		if($verbose) print $endoflist2.$endoflist2;
	}

	// finalize db.inc file
	if(count($dblist) > 0){
		$handle = fopen($filedir.$site_subfolder.$sitemapgen_folder."db.inc", "w+");
		if($handle === false || !$handle) die("Cannot create file '".$filedir.$site_subfolder.$sitemapgen_folder."db.inc'!");
		foreach($dblist as $listitem){
        	fwrite($handle, $listitem);
		}
		fwrite($handle, $endoflist);
		fclose($handle);
		if($verbose) confirmFileExists($filedir.$site_subfolder.$sitemapgen_folder, "db.inc");
	}else{
		$handle = fopen($filedir.$site_subfolder.$sitemapgen_folder."db.inc", "w");
		fclose($handle);
	}
}

function integrateAddAfterDBLines(){
	// insert datalist elements with addafter values into filelist array after specified filename
	global $datalist, $datalist_trans, $filelist, $http, $filedir, $sm_dir;

	$datadir = prependslash($sm_dir);
	$httpdir = appendslash($http);
	$filedir = prependslash(appendslash($sm_dir));
	krsort($datalist);
	foreach($datalist as $key => $data){
		if($data['addafter'] != ""){
			$url = str_replace($httpdir, $datadir, $data['url']);
			$filelist = array_push_after($filelist, array($url.">"), null, $datadir.$data['addafter']);
			$datalist[$key]['ignore'] = true;
			$datalist_trans[substr($url, strlen($filedir))] = $data['item'];
		}
	}
	return array($filelist, $datalist);
}

function array_push_after($src, $in, $pos, $fval){
    if(is_int($pos)){
		$R = array_merge(array_slice($src, 0, $pos+1), $in, array_slice($src, $pos+1));
    }elseif($pos != null){
        foreach($src as $k => $v){
            $R[$k] = $v;
            if($k == $pos) {
				$R = array_merge($R, $in);
				if(is_int($k)) $k++;
			}
        }
    }elseif($fval != null){
        foreach($src as $k => $v){
            $R[] = $v;
            if($v == $fval) {
				$R = array_merge($R, $in);
			}
        }
    }
	return $R;
}

function renamefile($filedir, $filename){
	global $verbose;

	$ok = false;
	if(strpos($filename, "/") !== false){
		if(file_exists($filedir.$filename)) {
			$newfilename = basename($filename);
			$result = rename($filedir.$filename, $filedir.$newfilename);

			if($result !== false){
				print ">>> ".strtoupper($filedir.$newfilename)." CREATED <<<<br>";
				$ok = true;
			}else{
				print "<span style=\"color: red\">>>> ".strtoupper($filedir.$newfilename)." NOT CREATED <<<</span><br>";
			}
		}else{
			print "<span style=\"color: red\">>>> ".strtoupper($filedir.$filename)." NOT FOUND <<<</span><br>";
		}
	}
	return $ok;
}

function moveFile($filename, $destfilename){

}

function confirmFileExists($filedir, $filename){
	global $verbose;

	$ok = false;
	if(file_exists($filedir.$filename)) {
		print "<span style=\"color: green\">>>> ".strtoupper($filedir.$filename)." CREATED <<<</span><br>";
		$ok = true;
	}else{
		print "<span style=\"color: red\">>>> ".strtoupper($filedir.$filename)." NOT FOUND <<<</span><br>";
	}
	if($verbose) print "<hr>\n";
	return $ok;
}

function prependslash($folder){
	if(substr($folder, 0, 1) != "/") $folder = "/".$folder;
	return $folder;
}

function appendslash($folder){
	if(substr($folder, -1, 1) != "/") $folder .= "/";
	return $folder;
}

function unprependslash($folder){
	if(substr($folder, 0, 1) == "/") $folder = substr($folder, 1);
	return $folder;
}

function unappendslash($folder){
	if(substr($folder, -1, 1) == "/") $folder = substr($folder, 0, strlen($folder)-1);
	return $folder;
}

function striplead($haystack, $needle){
	if(substr($haystack, 0, strlen($needle)) == $needle) $haystack = substr($haystack, strlen($needle));
	return $haystack;
}

function file_write_contents($filename, $data, $flags = 0, $context = null){
	global $verbose;

	if(is_writable($filename)){
		if(floatval(phpversion()) >= 5){
			return file_put_contents($filename, $data, $flags, $context);
		}else{
			if($fh = fopen($filename, "w")){
				if(fwrite($fh, $data)) $retn = true;
				fclose($fh);
			}
		}
	}
}
?>
