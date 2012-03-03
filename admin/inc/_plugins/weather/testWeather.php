<?
include_once("weather.class.php");

$weather = new getWeather();
$weather->dbUser="root";
$weather->dbPass="";
$weather->dbName="okmapguides";
$weather->cityCode="bc-27";		// Kelowna

echo "<table cellspacing=0 cellpadding=3>";
if ($weather->fetchData()){
	print_r($weather->getCurrent());
}
echo "</table>";
?>