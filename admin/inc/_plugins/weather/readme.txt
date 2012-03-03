WEATHER PLUG-IN
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================

For a working example, view testWeather.php;
For descriptions of all configuation settings, read weather.class.php

-- Inclusions --
$incl = "weather";

-- Preparation --

** Environment Canada **
<?php
$weather->cityCode = 'citycode';
if($weather->fetchData()){
	$weather_data = $weather->getCurrent();
    $weather_temp = $weather->getTemp($weather_data);
    $weather_cond = $weather->getCondition($weather_data);
    $weather_icon = getCondIcon($weather_data, $weather_cond);

    // optional -- Display the gathered data in preformatted div
    echo $weather->showWeather();
}

** Weather Network (also includes nighttime images) **
if(intval($weather_icon_num) != "") {
    echo '<div class="weather-image">';
    echo '<img src="'.WEB_URL.INC_FOLDER.'_plugins/weather/images/'.$weather_icon_num.'.png" alt="" title="" width="57" height="57" />';
    echo '</div>';
}
?>