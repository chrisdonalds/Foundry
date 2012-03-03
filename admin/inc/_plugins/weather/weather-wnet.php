<?php

$doc = new DOMDocument();
$doc->load("http://rss.theweathernetwork.com/weather/cabc0245");
$arrFeeds = array();
foreach ($doc->getElementsByTagName('item') as $node) {
	$itemRSS = array (
		'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
		'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
		'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
		'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue
		);
	array_push($arrFeeds, $itemRSS);
}

foreach($arrFeeds as $feed){
	if (strpos($feed['title'], "Current") !== false) { $weather_data = $feed; break; }
}

$weather_parts = explode(",", $weather_data['desc']);
$condition = trim($weather_parts[0]);
$temp = floatval($weather_parts[1]);
$weatherrec = getRec("weather", "*", "", "wordcount DESC", "");
$highestmatch = 0;
foreach($weatherrec as $weatherdata){
	$match = 0;
	for($i = 1; $i < 5; $i++){
		$weatherword = $weatherdata["word{$i}"];
		if(strpos(strtolower($condition), strtolower($weatherword)) !== false && $weatherword != null && $weatherword != "") $match++;
	}
	if($match > $highestmatch){
		$highestmatch = $match;
		$weather_icon_num = $weatherdata["img_num"];
		if(date("H")<5 || date("H")>21) $weather_icon_num .= "n";	// nighttime?
	}
}
?>
