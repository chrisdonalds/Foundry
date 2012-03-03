<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php 

/* STEP 1/4: Enter the web-accessible URL of your TubePress installation */
$tubepress_base_url = "http://3hough.pb:8888/tubepress_pro/";

/* STEP 2/4: Include the TubePress stand-alone library file (tubepress-pro.php) */
require_once dirname(__FILE__) . "/../tubepress-pro.php";
?>

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>TubePress Pro Example in Plain PHP</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<!-- STEP 3/4: Include this statement inside the HEAD of your HTML document -->
		<?php print tubepressHeadElements(true); ?>
    </head>
    <body>
<div style="width:500px">

<!-- STEP 4/4: Include this statement anywhere you want your gallery -->
<?php print tubepressGallery("resultsPerPage='3' mode='user'  playerLocation='tinybox' ajaxPagination='true'"); ?>
</div>
</body>
</html>
