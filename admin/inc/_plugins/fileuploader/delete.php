<?php
define("BASIC_GETINC", true);
include("../../../loader.php");
//include ('../../_core/getinc.php');

$file = getRequestVar("file");
$delrec = intval(getRequestVar("delrec"));
$row_id = intval(getRequestVar("row_id"));
if($file != ''){
	if($delrec == 1 && $row_id > 0){
		// image was not selected during current page session
		// get the table related to the page
		$page_url = getRequestVar("page_url");
		$base_page_url = preg_replace("/((\?|\&).*)/i", "", $page_url);
		$table = getRecItem("register", "db_table", "fileurl = '$base_page_url' AND type = 'db'");
		if($table != ''){
			// clear the image data from the page record
			updateRec($table, "image='', thumb=''", "id='$row_id'");
		}
	}
	$thm_file = SITE_PATH.dirname(str_replace(IMG_UPLOAD_FOLDER, THM_UPLOAD_FOLDER, $file))."/thm_".basename($file);
	$img_file = SITE_PATH.$file;
	if(file_exists($img_file)) {
		@unlink($img_file);
		@unlink($thm_file);
		echo json_encode(array('success' => true));
	}else{
		echo json_encode(array('success' => false, 'value' => 'File `'.basename($file).'` not found!'));
	}
}else{
	echo json_encode(array('success' => false));
}
?>
