<?php
/*
WEATHER PLUG-IN
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================
*/

include_once(SITE_PATH.PLUGINS_FOLDER."weather/weather.class.php");
$weather = new getWeather();
$weather->dbUser = DBUSER;
$weather->dbPass = DBPASS;
$weather->dbName = DBNAME;

$tempkey = array_search("Temperature", $weather_data[0]);
	$condkey = array_search("Condition", $weather_data[0]);
	$weather_icon_file = getRecItem("weather", "img_num", "ec_name = '".strtolower($weather_data[1][$condkey])."'");
	//if($weather_icon_file == "") $weather_icon_file = 37;

function getCondIcon($weather_data, $condkey){
	$file = getRecItem("weather", "img_num", "ec_name = '".strtolower($weather_data[1][$condkey])."'");
	//if($file == "") $file = 37;
	return $file;
}
?>
