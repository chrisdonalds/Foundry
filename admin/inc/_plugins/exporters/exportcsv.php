<?
define("BASIC_GETINC", true);
include("../../../loader.php");

$file = FILE_UPLOAD_FOLDER.'export.csv';
header('Content-type: text/x-csv');
header('Content-Disposition: attachment; filename="'.WEB_URL.$file.'"');
readfile(SITE_PATH.$file);
?>