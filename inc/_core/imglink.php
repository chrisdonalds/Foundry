<?php
header("Content-type: text/html");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$pic = strip_tags($_GET['pic']);
if (!$pic) die("No picture specified.");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title><?= $pic; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<p>
    <img src="/<?= $pic; ?>" alt="Image">
</p>
<p>
    Image from <a href="http://<?=$_SERVER['HTTP_HOST']?>/"><?=$_SERVER['HTTP_HOST']?></a>.
</p>
</body>
</html>