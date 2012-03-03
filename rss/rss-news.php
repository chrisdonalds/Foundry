<?php
define("VALID_LOAD", true);
define("DB_USED", true);
define("VHOST", substr(str_replace("\\", "/", realpath(__DIR__."/../")), strlen($_SERVER['DOCUMENT_ROOT']))."/");
include ($_SERVER['DOCUMENT_ROOT'].VHOST."inc/_core/getinc.php");					// required - starts PHP incls!!!
// *** DO NOT EDIT ABOVE THIS LINE! ***

//Destination URL root (not including any queries)
$destpage = "news-detail/";

//Create database query (change to appropriate SQL call if not using Web Template)
$result = getRec("data_whatsnew", "id, itemtitle as title, description as content, code, start_date", "published = 1", "start_date DESC", "10");

//Create RSS feed object
include ("createrss.php");
$myfeed = new RSSFeed();
$bus = htmlentities(BUSINESS);
$site = htmlentities(SITE_NAME);

//SetChannel ($url, $title, $description, $lang, $copyright, $creator, $subject)
$myfeed->SetChannel(WEB_URL.RSS_FOLDER.'xml.rss', $bus.' RSS', $site, 'en-us', '(c) '.date("Y").' '.$bus, $bus, '');

//SetImage ($url) [optional]
$myfeed->SetImage(WEB_URL.'images/logo.png');

//SetItem ($url, $title, $description)
foreach ($result as $line) {
	if ($line['id'] != "" && $line['title'] != "" && $line['content'] != "") {
		($line['category'] != "") ? $title = html_entity_decode($line['category'])." - " : $title = "";
		$title = htmlentities(strip_tags(str_replace("&#39;", "'", $line['title']), ENT_QUOTES));
		$item = "<![CDATA[";
		if($line['start_date'] != "0000-00-00") {
			$item .= "<b>When:</b> ".date("F j, Y", strtotime($line['start_date']));
			if($line['end_date'] != "0000-00-00" && $line['end_date'] != "") $item .= " to ".date("F j, Y", strtotime($line['end_date']));
			if($line['start_time'] != "00:00:00" && $line['start_time'] != "") $item .= ", ".date("g:ia", strtotime($line['start_time']));
			$item.= "<br/><br/>\n";
		}
		$item.= substr(strip_tags(nl2br(html_entity_decode($line['content']))), 0, 50)."...";
		$item.= "]]>";
        $myfeed->SetItem(	WEB_URL.$destpage.htmlentities($line['code']),
                        	$title,
                        	$item);
	}
}
echo $myfeed->output();
?>