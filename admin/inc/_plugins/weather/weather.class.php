<?php
require_once("cache.class.php");
/** ########################### ###########################
WEATHER PLUG-IN
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================
 * 
 * Environment Canada Weather Script
 * Fetches data from Environment Canada's weather server,
 * and displays it on your own site, in a cleaner format.
 * 
 * Email me possible bugs and suggestions to help improve
 * future releases and patches.
 *
 * @author JMan <jman@bedpan.ca>
 * @link http://www.bedpan.ca
 * @version 2.3.5
 * 
 */ ########################### ###########################


if (!function_exists('array_diff_key')){
/**
 * PHP4 substitute for array_diff_key(). Should work as needed.
 * Usage: array_diff_key(array $array1, array $array2 [, array $...  ])
 *
 * @return array
 */
	function array_diff_key(){
		$arrs = func_get_args();
		for ($i=1; $i<count($arrs); $i++){
			foreach ($arrs[$i] as $key=>$value){
				if (array_key_exists($key, $arrs[0])){
					unset($arrs[0][$key]);
				}
			}
		}
		return $arrs[0];
	}
}
class getWeather{
	private $weatherInfo;
	private $currentInfo;
	private $otherInfo;
	private $scriptVersion = "2.3.5";

 ############### Set These Variables Before Calling fetchData() ###############
	/**
	 * Toggles whether to use MySQL database, or Flat-File Caching.
	 *
	 * @var bool
	 */
	public $useMySQL = true;
	/**
	 * Your MySQL server address.
	 *
	 * @var string
	 */
	public $dbServer = "localhost";
	/**
	 * The user name for your MySQL with read and write privleges
	 * to the cache table that you've already created.
	 *
	 * @var string
	 */
	public $dbUser = "root";
	/**
	 * The password for the MySQL account above.
	 *
	 * @var string
	 */
	public $dbPass = "";
	/**
	 * The name of the database where the cache table is located.
	 *
	 * @var string
	 */
	public $dbName = "db_name";
	/**
	 * Directory on your server where cache data is to be kept
	 * with the trailing slash, relative to the script.
	 * Must exist, and be readable and writable in order to work.
	 *
	 * @var string
	 */
	public $cacheDir = "weatherCache/";
	/**
	 * To find the cityCode for your city of choice, go to
	 * www.weatheroffice.eg.gc.ca and search for your city.
	 *
	 * @var string
	 */
	public $cityCode = "bc-48";
	/**
	 * Toggles whether to use Metric or Imperial units of measure.
	 *
	 * @var bool
	 */
	public $imperialUnits = false;
	/**
	 * If you would rather use French instead of English.
	 *
	 * @var bool
	 */
	public $langFr = false;
	/**
	 * If your host doesn't support file_get_contents(),
	 * enable this, to use cURL instead.
	 *
	 * @var bool
	 */
	public $useCURL = false;
	/**
	 * If Environment Canada's Server times out or gives an error,
	 * show the cached data (if available), rather than showing nothing.
	 * Setting to false can allow you to program in a custom 
	 * "Not Available" message into your script, should you desire that.
	 *
	 * @var bool
	 */
	public $showBackupCached = true;

 ############### Set These Variables at Any Time ###############
  ##### Global Variables #####
	/**
	 * Relative directory to where your weather icons are stored,
	 * With the trailing slash.
	 *
	 * @var string
	 */
	public $imageDir = "images/weather/";
	/**
	 * Toggles whether or not to display the name of the city.
	 *
	 * @var bool
	 */
	public $showCity = true;
	/**
	 * Toggles whether or not to display the 'Last Updated' info.
	 *
	 * @var bool
	 */
	public $showUpdt = true;
	/**
	 * Sets the font which is used when text is displayed.
	 *
	 * @var String
	 */
	public $fontFace = "Arial";
	/**
	 * Hex RGB colour code (eg. #000000) for the text colour.
	 * Set to null for page default.
	 *
	 * @var string
	 */
	public $fontColour = "#000000";
	/**
	 * Sets the size of font to use for displayed text.
	 *
	 * @var int
	 */
	public $fontSize = 11;
	/**
	 * Sets the size of font to use for header text.
	 *
	 * @var int
	 */
	public $headSize = 12;
	/**
	 * Hex RGB colour code (eg. #000000) for the background
	 * colour of the titles of the various tables.
	 * Set to null for none.
	 *
	 * @var string
	 */
	public $headColour = "#00CC99";
	/**
	 * Hex RGB colour code (eg. #000000) for the background
	 * colour of the rest of the table backgrounds.
	 * Set to null for none.
	 *
	 * @var string
	 */
	public $bgColour = "#FFFFFF";
	/**
	 * Sets the time you want to cache the weather information for, in seconds
	 * (1 hour is 3600 seconds).
	 *
	 * @var int
	 */
	public $cacheTime = 3600;
	/**
	 * Toggles whether or not to show a black border around the objects.
	 *
	 * @var bool
	 */
	public $showBorders = true;

  ##### showWeather Variables #####
	/**
	 * Toggles whether to display the standard short text, 
	 * or an optional longer text description, if available.
	 *
	 * @var bool
	 */
	public $longText = false;
	/**
	 * Sets the display type that will be used:
	 *  0 for Horizontal
	 *  1 for Vertical (Tall)
	 *  2 for Vertical (Wide)
	 *
	 * @var int
	 */
	public $dispFormat = 0;
	/**
	 * Sets the number of weather blocks to display:
	 *  0 shows all blocks available (Default)
	 *  1-6 shows that specific number (if available) of blocks
	 *
	 * @var int
	 */
	public $showBlocks = 0;
	/**
	 * Either a number, or a percentage, to specify the table width for
	 * the normal 5 day forecast display.
	 *
	 * @var string
	 */
	public $normalWidth = "525";
	/**
	 * Either a number, or a percentage, to specify the table width for
	 * the tall version of the 5 day forecast display.
	 *
	 * @var string
	 */
	public $tallWidth = "125";
	/**
	 * Either a number, or a percentage, to specify the table width for
	 * the wide verstion of the  5 day forecast display.
	 *
	 * @var string
	 */
	public $wideWidth = "225";
	/**
	 * Overlays the POP% on the weather image instead of showing it
	 * in green text with the high and low temperatures.
	 *
	 * @var bool
	 */
	public $overlayPop = "true";

  ##### showCurrent Variables #####
	/**
	 * Either a number, or a percentage, to specify the table width for
	 * the current conditions display.
	 *
	 * @var string
	 */
  	public $currentWidth = "165";
	/**
	 * Toggles whether or not to show the 'Current Conditions' block.
	 *
	 * @var bool
	 */
	public $showCurrentBlock = true;
	/**
	 * Toggles whether or not to show the 'Yesterday' block.
	 *
	 * @var bool
	 */
	public $showYesterdayBlock = true;
	/**
	 * Toggles whether or not to show the 'Regional Normals' block.
	 *
	 * @var bool
	 */
	public $showNormalsBlock = true;
	/**
	 * Toggles whether or not to show the 'Today' block.
	 *
	 * @var bool
	 */
	public $showTodayBlock = true;
############### Functions ###############
	/**
	 * This converts the accents on french words, but does not break 
	 * any HTML code which is sent to it.
	 *
	 * @param string $text
	 * @return string
	 */
	private function htmlentities2($text){
		return strtr($text, array_diff_key(get_html_translation_table(HTML_ENTITIES,ENT_NOQUOTES), get_html_translation_table(HTML_SPECIALCHARS)));
	}
	/**
	 * This is like ucwords() except it also capitalizes accented characters.
	 * It also capitalizes after apostrophies, like in "D'Averses".
	 *
	 * @param string $text
	 * @return string
	 */
	private function ucAccentwords($text){
		return stripslashes(preg_replace('/((?:&(?:ae|[a-z])|.))((?:(?:acute|grave|cedil|circ|lig|ring|slash|tilde|uml);|(?:.*?))(?:\'(?=[^ ]{2,})| |$))/e', 'strtoupper("\\1")."\\2"', $text));
	}
	/**
	 * This snips element $key out of $array, and return everything else.
	 *
	 * @param array $array
	 * @param $key int
	 * @return array
	 */
	public function array_snip($array, $key) {
		$snipped = array_slice($array, 0, $key - 1);
		foreach(array_slice($array, $key) as $snip) $snipped[] = $snip;
		return $snipped;
	}
	/**
	 * This function either gets the cached information from MySQL, or
	 * fetches up to date information from the Environment Canada Website.
	 * Returns false on failure.
	 *
	 * @return bool
	 */
	public function fetchData(){
		global $weatherComment, $weatherLang, $weatherUnits, $textArray;
		$weatherComment = "\n<!--\nWeather Information Collected by Environment Canada [www.weatheroffice.gc.ca]\nDisplayed via Bedpan.ca's PHP Weather Script Version ".$this->scriptVersion." [www.bedpan.ca/weather2.php]\n-->\n";
		if ($this->imperialUnits == true) $weatherUnits = "i";
		else $weatherUnits = "m";
		if ($this->langFr == true) $weatherLang = "f";
		else $weatherLang = "e";
		$textArray["e"] = array("Updated"=>"Last Updated", "Current"=>"Current Conditions", "Observed"=>"Observed at", "Pressure"=>"Pressure", "Temperature"=>"Temperature", "Tendency"=>"Tendency", "Rising"=>"rising", "Falling"=>"falling", "Yesterday"=>"Yesterday", "Normals"=>"Normals", "Today"=>"Today");
		$textArray["f"] = array("Updated"=>"Actualis&eacute;e &Agrave;", "Current"=>"Conditions actuelles", "Observed"=>"Enregistr&eacute;es &agrave;", "Pressure"=>"Pression", "Temperature"=>"Temp&eacute;rature", "Tendency"=>"Tendance", "Rising"=>"&agrave; la hausse", "Falling"=>"&agrave; la baisse", "Yesterday"=>"Hier", "Normals"=>"Normales", "Today"=>"aujourd'hui");
		$cache = new Cache();
		if ($this->useMySQL){
			$cache->useMySQL = true;
			$cache->dbServer = $this->dbServer;
			$cache->dbUser = $this->dbUser;
			$cache->dbPass = $this->dbPass;
			$cache->dbName = $this->dbName;
		}else{
			$cache->useMySQL = false;
			$cache->cacheDir = $this->cacheDir;
		}
		if ($weatherFull = $cache->doCache("weather_".$weatherLang."_".$weatherUnits."_".$this->cityCode)){
			$this->weatherInfo = $weatherFull[0];
			$this->currentInfo = $weatherFull[1];
			$this->otherInfo = $weatherFull[2];
		}else{
			if ($this->useCURL == true){
				$ch = @curl_init();
				@curl_setopt($ch, CURLOPT_URL, "http://www.weatheroffice.gc.ca/forecast/city_".$weatherLang.".html?".$this->cityCode."&unit=".$weatherUnits);
				@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$contents = @curl_exec($ch);
				@curl_close($ch);
			}else $contents = @file_get_contents("http://www.weatheroffice.gc.ca/forecast/city_".$weatherLang.".html?".$this->cityCode."&unit=".$weatherUnits);
			if ($contents && strlen($contents) > 0){
				preg_match_all('%<div class="fperiod(?: fperiodlast)?">.*?<h3>(?:\s*<abbr>.*? title=")?(.+?)(?:">.*?</abbr>\s*)?</h3>.*?weathericons/(\d+?)\.gif.*?alt="(.+?)".*?<li(?: class="high".*?)?>(.+?)</li>.*?<li(?: class="low".*?)?>(.+?)</li>.*?<li(?: class="pop".*?)?>\s?(.+?)</li>%s', $contents, $this->weatherInfo, PREG_PATTERN_ORDER);
				preg_match_all('%(?:<p class="fissued"><span class="section"><span class="bold">(?:\s|.)*?:</span>\s?(.+?)</span>)|<dt>(.+?)</dt>\s*<dd>(?:<span class="section">)?(.+?)(?:</span>)?</dd>\s*<dd class="dd2">.+?</dd>%', $contents, $tempData, PREG_PATTERN_ORDER);
				array_shift($tempData[3]);
				for ($j=2; $j<count($tempData[2]); $j++){
					if (preg_match('/night|soir|nuit/', $tempData[2][$j]) && $tempData[2][$j] != $this->weatherInfo[1][$j-1]){
						$tempData[3][$j-2] .= " " . $tempData[2][$j] . ": " . $tempData[3][$j-1];
						$tempData[3] = $this->array_snip($tempData[3], $j);
					}
				}
				$this->weatherInfo = array_merge($this->weatherInfo, array($tempData[3], $tempData[1]));
				unset($tempData);
				preg_match_all('%<dt>(?:\s*<a href.+?>)?(?:\s*<abbr>.+?>)?(.+?):?\s*?(?:</span></abbr>\s*|</a>\s*)?</dt>\s*<dd.*?>(.+?)</dd>(?!\s*<dd class="dd2">)|(Yesterday|Hier)|(Normal(?:e)?s)|(Today|aujourd\'hui)%', $contents, $this->currentInfo, PREG_PATTERN_ORDER);
				preg_match_all('/id="c1">(.+?)<\/h1>.*?weathericons\/(\\d+)\\.gif.*?alt="(.+?)"/s', $contents, $this->otherInfo, PREG_PATTERN_ORDER);

				array_shift($this->weatherInfo);
				array_shift($this->currentInfo);
				array_shift($this->otherInfo);
				if (!$this->weatherInfo[0] || !$this->currentInfo[0] || !$this->otherInfo[0]) return false;
				if ($this->langFr == true){
					foreach ($this->weatherInfo as $key => $subArray) foreach ($subArray as $subKey => $subEntry) $this->weatherInfo[$key][$subKey] = $this->htmlentities2($subEntry);
					foreach ($this->currentInfo as $key => $subArray) foreach ($subArray as $subKey => $subEntry) $this->currentInfo[$key][$subKey] = $this->htmlentities2($subEntry);
					foreach ($this->otherInfo as $key => $subArray) foreach ($subArray as $subKey => $subEntry) $this->otherInfo[$key][$subKey] = $this->htmlentities2($subEntry);
				}
				for ($i=0; $i<count($this->currentInfo[0]); $i++) for ($o=2; $o<5; $o++) if ($this->currentInfo[$o][$i]) $this->currentInfo[0][$i] = $this->currentInfo[$o][$i];
				$this->currentInfo = array($this->currentInfo[0], $this->currentInfo[1]);
				$this->currentInfo[1][0] = array($this->otherInfo[0][0], $this->currentInfo[1][0]);
				$tempDate = $this->currentInfo[1][1];
				$this->currentInfo[1][1] = array();
				if ($this->langFr == true){
					$this->currentInfo[1][1][0] = preg_replace('/(\\d+h\\d+ \\w+) .*? (\\d+) (.*?) (\\d+)/e', "'\\2 '.ucwords('\\3').' \\4 @ \\1'", $this->weatherInfo[7][0]);
					$this->currentInfo[1][1][1] = preg_replace('/(\\d+h\\d+ \\w+) .+? (\\d+) (.+?) (\\d+)/e', "str_pad('\\2',2,'0',STR_PAD_LEFT).' /\\3 /\\4 \\1'", $tempDate);
					$monthNames = array("01"=>"janvier", "02"=>"f&eacute;vrier", "03"=>"mars", "04"=>"avril", "05"=>"mai", "06"=>"juin", "07"=>"juillet", "08"=>"ao&ucirc;t", "09"=>"septembre", "10"=>"octobre", "11"=>"novembre", "12"=>"d&eacute;cembre");
				}else{
					$this->currentInfo[1][1][0] = preg_replace('/(\\d+)\\.(\\d+ \\w+ \\w+) \\w+ (\\d+) (\\w+) (\\d+)/', '\\4 \\3 \\5 @ \\1:\\2', $this->weatherInfo[7][0]);
					$this->currentInfo[1][1][1] = preg_replace('/(\\d+:\\d+ \\w+ \\w+) \\w+ (\\d+) (\\w+) (\\d+)/e', "'\\3 /'.str_pad('\\2',2,'0',STR_PAD_LEFT).' /\\4 \\1'", $tempDate);
					$monthNames = array("01"=>"January", "02"=>"February", "03"=>"March", "04"=>"April", "05"=>"May", "06"=>"June", "07"=>"July", "08"=>"August", "09"=>"September", "10"=>"October", "11"=>"November", "12"=>"December");
				}
				foreach ($monthNames as $key => $month) $this->currentInfo[1][1][1] = str_replace($month, $key, $this->currentInfo[1][1][1]);
				unset ($tempDate);
				$dayNames = array("Sun"=>"Sunday", "Mon"=>"Monday", "Tue"=>"Tuesday", "Wed"=>"Wednesday", "Thu"=>"Thursday", "Fri"=>"Friday", "Sat"=>"Saturday", "dim"=>"Dimanche", "lun"=>"Lundi", "mar"=>"Mardi", "mer"=>"Mercredi", "jeu"=>"Jeudi", "ven"=>"Vendredi", "sam"=>"Samedi");
				foreach ($this->weatherInfo[0] as $b => $weatherText){
					if (key_exists($weatherText, $dayNames)) $this->weatherInfo[0][$b] = $dayNames[$weatherText];
					else $this->weatherInfo[0][$b] = $this->ucAccentwords($this->weatherInfo[0][$b]);
				}
				foreach ($this->weatherInfo[2] as $b => $weatherText) $this->weatherInfo[2][$b] = $this->ucAccentwords($weatherText);
				foreach ($this->weatherInfo[3] as $key => $value) if ($value != "&nbsp;") $this->weatherInfo[3][$key] = "<font color=\"red\">$value</font>";
				foreach ($this->weatherInfo[4] as $key => $value) if ($value != "&nbsp;") $this->weatherInfo[4][$key] = "<font color=\"blue\">$value</font>";
				if ($this->overlayPop == false) foreach ($this->weatherInfo[5] as $key => $value) if ($value != "&nbsp;") $this->weatherInfo[5][$key] = "<font color=\"green\">$value</font>";
				foreach ($this->weatherInfo[6] as $key => $value) $this->weatherInfo[6][$key] = str_replace("</span>", "<Br>", preg_replace('/<span class="bold">(.+?)<\/span>/', "<b>\\1</b>", $value));
				$cache->doCache("weather_".$weatherLang."_".$weatherUnits."_".$this->cityCode, $this->cacheTime, array($this->weatherInfo, $this->currentInfo, $this->otherInfo));
			}elseif (!$this->showBackupCached){
				return false;
			}elseif ($cache->oldCache["weather_".$weatherLang."_".$weatherUnits."_".$this->cityCode]){
				$this->weatherInfo = $cache->oldCache["weather_".$weatherLang."_".$weatherUnits."_".$this->cityCode][0];
				$this->currentInfo = $cache->oldCache["weather_".$weatherLang."_".$weatherUnits."_".$this->cityCode][1];
				$this->otherInfo = $cache->oldCache["weather_".$weatherLang."_".$weatherUnits."_".$this->cityCode][2];
				$cache->doCache("weather_".$weatherLang."_".$weatherUnits."_".$this->cityCode, 0, array($this->weatherInfo, $this->currentInfo, $this->otherInfo));
			}else return false;
		}
		if ($this->weatherInfo[0] && $this->currentInfo[0] && $this->otherInfo[0]) return true;
		else return false;
	}
	/**
	 * This function is used to display the '5 Day Forecast' chunk.
	 * fetchData() must be called before calling this function.
	 * Returns false on failure.
	 *
	 * @return string
	 */
	public function showWeather(){
		global $weatherComment, $weatherLang, $textArray;
		if ($this->weatherInfo){
			if ($this->headSize) $headSize = " font-size:".$this->headSize."px;";
			elseif (!$this->headSize && $this->fontSize) $headSize = " font-size:".($this->fontSize+1)."px;";
			else $headSize = "";
			if ($this->fontFace) $fontFace = " font-family:".$this->fontFace.";";
			else $fontFace = "";
			if ($this->fontColour) $fontColour = " color:".$this->fontColour.";";
			else $fontColour = "";
			if ($this->fontSize) $fontSize = " font-size:".$this->fontSize."px;";
			else $fontSize = "";
			if ($this->headColour) $headColour = " bgcolor=\"".$this->headColour."\"";
			else $headColour = "";
			if ($this->bgColour) $bgColour = " bgcolor=\"".$this->bgColour."\"";
			else $bgColour = "";
			$wdth = intval(100/count($this->weatherInfo[0]));
			if ($this->showBlocks == 0 || $this->showBlocks > count($this->weatherInfo[0])-1 || $this->showBlocks < 0) $this->showBlocks = count($this->weatherInfo[0]);

			if ($this->dispFormat == 1 || $this->dispFormat == 2){
				if ($this->dispFormat == 1 && $this->tallWidth) $tblWdth = " width=\"".$this->tallWidth."\"";
				elseif ($this->dispFormat == 2 && $this->wideWidth) $tblWdth = $tblWdth = " width=\"".$this->wideWidth."\"";
				else $tblWdth = "";
				$weather = $weatherComment . "<table".$tblWdth." style=\"".$fontFace.$fontColour.$fontSize." text-align:center;";
				if ($this->showBorders) $weather .= " border-style:solid; border-width:1px; border-color:#000000;";
				$weather .= "\" cellspacing=\"0\" cellpadding=\"1\"".$bgColour.">\n";
				if ($this->showCity){
					$weather .= "\t<tr>\n\t\t<td colspan=\"".$this->dispFormat."\" style=\"".$headSize;
					if ($this->showBorders) $weather .= " border-style:none none solid none; border-width:1px; border-color:#000000;";
					$weather .= "\"".$headColour."><center><b>".$this->currentInfo[1][0][0]."</b></center></td>\n\t</tr>\n";
				}
				for ($b=0; $b<$this->showBlocks; $b++){
					for ($g=0, $tdExtra=""; $g<4; $g++, $tdExtra="", $altEntry=""){
						if ($g==0){
							$altEntry = "<b>".$this->weatherInfo[$g][$b]."</b>";
							if ($this->dispFormat == 2) $tdExtra = " colspan=\"2\"";
						}
						elseif ($g==1){
							if ($this->overlayPop && $this->weatherInfo[5][$b] && $this->weatherInfo[5][$b] != "&nbsp;") $altEntry = "\n\t\t\t<table cellpadding=\"0\" cellspacing=\"0\">\n\t\t\t\t<tr>\n\t\t\t\t\t<td background=\"".$this->imageDir.$this->weatherInfo[$g][$b].".gif\"><img src=\"".$this->imageDir."p".str_replace("%", "", $this->weatherInfo[5][$b]).".gif\" alt=\"".$this->ucAccentwords($this->weatherInfo[2][$b])."\" title=\"".$this->ucAccentwords($this->weatherInfo[2][$b])."\"></td>\n\t\t\t\t</tr>\n\t\t\t</table>\n\t\t";
							else $altEntry = "<img src=\"$this->imageDir".($this->weatherInfo[$g][$b]).".gif\" alt=\"".$this->ucAccentwords($this->weatherInfo[2][$b])."\" title=\"".$this->ucAccentwords($this->weatherInfo[2][$b])."\">";
							$tdExtra = " height=\"51\" valign=\"top\"";
						}
						elseif ($g==2){
							if ($this->longText) $altEntry .= $this->weatherInfo[6][$b];
							if ($this->dispFormat == 2) $tdExtra = " align=\"left\" valign=\"top\" rowspan=\"2\"";
						}
						elseif ($g==3){
							if ($this->weatherInfo[3][$b] != "&nbsp;") $this->weatherInfo[$g][$b] = $this->weatherInfo[3][$b];
							else $this->weatherInfo[3][$b] = null;
							if ($this->weatherInfo[4][$b] != "&nbsp;"){
								if ($this->weatherInfo[3][$b]) $this->weatherInfo[3][$b] .= "<Br>" . $this->weatherInfo[4][$b];
								else $this->weatherInfo[3][$b] .= $this->weatherInfo[4][$b];
							}
							if ($this->overlayPop == false && $this->weatherInfo[5][$b] != "&nbsp;"){
								if ($this->weatherInfo[3][$b]) $this->weatherInfo[3][$b] .= "<Br>" . $this->weatherInfo[5][$b];
								else $this->weatherInfo[3][$b] .= $this->weatherInfo[5][$b];
							}
						}
						if (!($this->dispFormat==2 && $g==2)) $weather .= "\t<tr>\n";
						if ($b!=0 && $g==0 && $this->showBorders) $tdExtra .= " style=\"border-style:solid none none none; border-width:1px; border-color:#000000;\"";
						if ($altEntry != "") $weather .= "\t\t<td".$tdExtra."><center>".$altEntry."</center></td>\n";
						else $weather .= "\t\t<td".$tdExtra."><center>".$this->weatherInfo[$g][$b]."</center></td>\n";
						if (!($this->dispFormat == 2 && $g == 1)) $weather .= "\t</tr>\n";
					}
				}
				if ($this->showUpdt){
					if ($this->dispFormat == 1) $showTime = str_replace(" @", "<Br>", $this->currentInfo[1][1][0]);
					else $showTime = $this->currentInfo[1][1][0];
					$weather .= "\t<tr>\n\t\t<td style=\"text-align:right;";
					if ($this->showBorders) $weather .= " border-style:solid none none none; border-width:1px; border-color:#000000;";
					$weather .= "\" colspan=\"".$this->dispFormat."\"><i>".$textArray[$weatherLang]["Updated"].":<Br> ".$showTime."</i></td>\n\t</tr>\n";
				}
				$weather .= "</table>\n";
			}else{
				if ($this->normalWidth) $tblWdth = " width=\"".$this->normalWidth."\"";
				else $tblWdth = "";
				$weather = $weatherComment . "<table".$tblWdth." style=\"".$fontFace.$fontColour.$fontSize." text-align:center;";
				if ($this->showBorders) $weather .= " border-style:solid; border-width:1px; border-color:#000000;";
				$weather .= "\" cellspacing=\"0\" cellpadding=\"1\"".$bgColour.">\n";
				if ($this->showCity){
					$weather .= "\t<tr>\n\t\t<td colspan=\"".$this->showBlocks."\" style=\"".$headSize." text-align:left;";
					if ($this->showBorders) $weather .= " border-style:none none solid none; border-width:1px; border-color:#000000;";
					$weather .= "\"".$headColour."><b>&nbsp;".$this->currentInfo[1][0][0]."</b></td>\n\t</tr>\n";
				}
				for ($g=0; $g<4; $g++){
					$weather .= "\t<tr>\n";
						for ($b=0; $b<$this->showBlocks; $b++, $altEntry="", $tblWdth=""){
							if ($g==0){
								$tblWdth = " width=\"".$wdth."%\"";
								$altEntry = "<b>".$this->weatherInfo[$g][$b]."</b>";
							}
							elseif ($g==1){
								if ($this->overlayPop && $this->weatherInfo[5][$b] && $this->weatherInfo[5][$b] != "&nbsp;") $altEntry = "\n\t\t\t<table cellpadding=\"0\" cellspacing=\"0\">\n\t\t\t\t<tr>\n\t\t\t\t\t<td background=\"".$this->imageDir.$this->weatherInfo[$g][$b].".gif\"><img src=\"".$this->imageDir."p".str_replace("%", "", $this->weatherInfo[5][$b]).".gif\" alt=\"".$this->ucAccentwords($this->weatherInfo[2][$b])."\" title=\"".$this->ucAccentwords($this->weatherInfo[2][$b])."\"></td>\n\t\t\t\t</tr>\n\t\t\t</table>\n\t\t";
								else $altEntry = "<img src=\"$this->imageDir".($this->weatherInfo[$g][$b]).".gif\" alt=\"".$this->ucAccentwords($this->weatherInfo[2][$b])."\" title=\"".$this->ucAccentwords($this->weatherInfo[2][$b])."\">";
							}
							elseif ($g==2 && $this->longText) $altEntry = $this->weatherInfo[6][$b];
							elseif ($g==3){
								if ($this->weatherInfo[3][$b] != "&nbsp;") $this->weatherInfo[$g][$b] = $this->weatherInfo[3][$b];
								else $this->weatherInfo[3][$b] = null;
								if ($this->weatherInfo[4][$b] != "&nbsp;"){
									if ($this->weatherInfo[3][$b]) $this->weatherInfo[3][$b] .= "<Br>" . $this->weatherInfo[4][$b];
									else $this->weatherInfo[3][$b] .= $this->weatherInfo[4][$b];
								}
								if ($this->overlayPop == false && $this->weatherInfo[5][$b] != "&nbsp;"){
									if ($this->weatherInfo[3][$b]) $this->weatherInfo[3][$b] .= "<Br>" . $this->weatherInfo[5][$b];
									else $this->weatherInfo[3][$b] .= $this->weatherInfo[5][$b];
								}
								$tblWdth .= " valign=\"top\"";
							}
							elseif ($g==4 || $g==5) continue;
							if ($b!=0 && $this->showBorders) $tblWdth.= " style=\"border-style:none none none solid; border-width:1px; border-color:#000000;\"";
							if ($altEntry != "") $weather .= "\t\t<td".$tblWdth."><center>".$altEntry."</center></td>\n";
							else $weather .= "\t\t<td".$tblWdth."><center>".$this->weatherInfo[$g][$b]."</center></td>\n";
						}
					$weather .= "\t</tr>\n";
				}
				if ($this->showUpdt){
					$weather .= "\t<tr>\n\t\t<td style=\"text-align:right;";
					if ($this->showBorders) $weather .= " border-style:solid none none none; border-width:1px; border-color:#000000;";
					$weather .= "\" colspan=\"".$this->showBlocks."\"><i>".$textArray[$weatherLang]["Updated"].": ".$this->currentInfo[1][1][0]."</i></td>\n\t</tr>\n";
				}
				$weather .= "</table>\n";
			}
			return $weather;
		}else return false;
	}
	/**
	 * This function is used to display the 'Current Conditions' chunk.
	 * fetchData() must be called before calling this function.
	 * Returns false on failure.
	 *
	 * @return string
	 */
	public function showCurrent(){
		global $weatherComment, $weatherLang, $textArray;
		$tempCurrent = array();
		$subArray = $textArray[$weatherLang]["Current"];
		foreach ($this->currentInfo[1] as $key => $value){
			if (is_array($value)) $value = $value[1];
			if ($value == null) $subArray = $this->currentInfo[0][$key];
			else $tempCurrent[$subArray][$this->currentInfo[0][$key]] = $value;
			if ($this->currentInfo[0][$key] == $textArray[$weatherLang]["Observed"]) $tempCurrent[$subArray]["Image"] = $this->otherInfo[1][0];
			if ($this->currentInfo[0][$key] == $textArray[$weatherLang]["Observed"]) $tempCurrent[$subArray]["Condition"] = null;
			if ($this->currentInfo[0][$key] == "Date") $tempCurrent[$subArray]["Date"] = $this->currentInfo[1][1][1];
			if ($this->currentInfo[0][$key] == "Date") $tempCurrent[$subArray][$textArray[$weatherLang]["Temperature"]] = null;
			if ($this->currentInfo[0][$key] == $textArray[$weatherLang]["Tendency"]) {
				if ($value == $textArray[$weatherLang]["Rising"]) $tempCurrent[$subArray][$textArray[$weatherLang]["Pressure"]] .= "&uarr;";
				elseif ($value == $textArray[$weatherLang]["Falling"]) $tempCurrent[$subArray][$textArray[$weatherLang]["Pressure"]] .= "&darr;";
				array_pop($tempCurrent[$subArray]);
			}
		}
		unset($subArray);

		if ($this->currentInfo){
			if ($this->headSize) $headSize = " font-size:".$this->headSize."px;";
			elseif (!$this->headSize && $this->fontSize) $headSize = " font-size:".($this->fontSize+1)."px;";
			else $headSize = "";
			if ($this->fontFace) $fontFace = " font-family:".$this->fontFace.";";
			else $fontFace = "";
			if ($this->fontColour) $fontColour = " color:".$this->fontColour.";";
			else $fontColour = "";
			if ($this->fontSize) $fontSize = " font-size:".$this->fontSize."px;";
			else $fontSize = "";
			if ($this->headColour) $headColour = " bgcolor=\"".$this->headColour."\"";
			else $headColour = "";
			if ($this->bgColour) $bgColour = " bgcolor=\"".$this->bgColour."\"";
			else $bgColour = "";
			if (!$this->showBorders){
				for ($i=4, $tblWdth=" width=\"50%\""; $i<=8; $i++, $tblWdth=""){
					if ($i !=5 && $this->currentInfo[$i][0]){
						$this->currentInfo[$i][0] = preg_replace('/style=".*?"/', null, $this->currentInfo[$i][0]);
					}
				}
			}
			if ($this->currentWidth) $tblWdth = " width=\"".$this->currentWidth."\"";
			else $tblWdth = "";
			if (!$this->showCurrentBlock || !key_exists($textArray[$weatherLang]["Current"], $tempCurrent)) $tblBorder = "none solid solid solid";
			else $tblBorder = "solid";
			$current = $weatherComment . "<table".$tblWdth." style=\"".$fontFace.$fontColour.$fontSize." text-align:center;";
			if ($this->showBorders) $current .= " border-style:".$tblBorder."; border-width:1px; border-color:#000000;";
			$current .= "\" cellspacing=\"0\" cellpadding=\"2\"".$bgColour.">\n";
			if ($this->showCurrentBlock && array_key_exists($textArray[$weatherLang]["Current"], $tempCurrent)){
				$current .= "\t<tr>\n\t\t<td colspan=\"2\" style=\"".$headSize."\" ".$headColour."><center><b>".$this->ucAccentwords($textArray[$weatherLang]["Current"])."</b></center></td>\n\t</tr>\n";
				if ($this->showCity && $tempCurrent[$textArray[$weatherLang]["Current"]][$textArray[$weatherLang]["Observed"]]){
					$current .= "\t<tr>\n\t\t<td colspan=\"2\"";
					if ($this->showBorders) $current .= " style=\"border-style:solid none none none; border-width:1px; border-color:#000000;\"";
					$current .= "><center><b>".$this->ucAccentwords($tempCurrent[$textArray[$weatherLang]["Current"]][$textArray[$weatherLang]["Observed"]])."</b></center></td>\n\t</tr>\n";
				}
				if ($tempCurrent[$textArray[$weatherLang]["Current"]]["Image"]){
					if ($tempCurrent[$textArray[$weatherLang]["Current"]]["Condition"] == "Not observed") $tempCurrent[$textArray[$weatherLang]["Current"]]["Image"] = "29";
					$current .= "\t<tr>\n\t\t<td colspan=\"2\"><center><img src=\"".$this->imageDir.($tempCurrent[$textArray[$weatherLang]["Current"]]["Image"]).".gif\"";
					if ($this->showBorders) $current .= " border=\"1\"";
					$current .= " alt=\"".$this->ucAccentwords($tempCurrent[$textArray[$weatherLang]["Current"]]["Condition"])."\" title=\"".$this->ucAccentwords($tempCurrent[$textArray[$weatherLang]["Current"]]["Condition"])."\"></center></td>\n\t</tr>\n";
				}
				if ($tempCurrent[$textArray[$weatherLang]["Current"]]["Condition"]){
					$current .= "\t<tr>\n\t\t<td colspan=\"2\"><center><b>".$this->ucAccentwords($tempCurrent[$textArray[$weatherLang]["Current"]]["Condition"])."</b>";
					if ($this->showUpdt) $current .= "<Br>\n\t\t\t";
					else $current .= "</center></td>\n\t</tr>\n";
				}elseif ($this->showUpdt) $current .= "\t<tr>\n\t\t<td colspan=\"2\"><center>";
				if ($this->showUpdt) $current .= "<i>".$tempCurrent[$textArray[$weatherLang]["Current"]]["Date"]."</i></center></td>\n\t</tr>\n";
				$tempCurrent[$textArray[$weatherLang]["Current"]] = array_diff_key($tempCurrent[$textArray[$weatherLang]["Current"]], array_flip(array($textArray[$weatherLang]["Observed"], "Image", "Condition", "Date")));
			}
			foreach ($tempCurrent as $curSection => $sectionArray){
				if ((!$this->showCurrentBlock || !key_exists($textArray[$weatherLang]["Current"], $tempCurrent)) && $curSection == $textArray[$weatherLang]["Current"]) continue;
				if ((!$this->showYesterdayBlock || !key_exists($textArray[$weatherLang]["Yesterday"], $tempCurrent)) && $curSection == $textArray[$weatherLang]["Yesterday"]) continue;
				if ((!$this->showNormalsBlock || !key_exists($textArray[$weatherLang]["Normals"], $tempCurrent)) && $curSection == $textArray[$weatherLang]["Normals"]) continue;
				if ((!$this->showTodayBlock || !key_exists($textArray[$weatherLang]["Today"], $tempCurrent)) && $curSection == $textArray[$weatherLang]["Today"]) continue;
				if ($curSection != $textArray[$weatherLang]["Current"]) $current .= "\t<tr>\n\t\t<td colspan=\"2\" style=\"border-style:solid none none none; border-width:1px; border-color:#000000;\"".$headSize."\" ".$headColour."><center><b>".$this->ucAccentwords($curSection)."</b></center></td>\n\t</tr>\n";
				foreach ($sectionArray as $sectionKey => $sectionValue){
					if ($sectionKey && $sectionKey != "&nbsp;") $current .= "\t<tr>\n\t\t<td style=\"border-style:solid none none none; border-width:1px; border-color:#000000;\"><center>".$this->ucAccentwords($sectionKey)."</center></td>\n\t\t<td style=\"border-style:solid none none solid; border-width:1px; border-color:#000000;\"><center>".$this->ucAccentwords($sectionValue)."</center></td>\n\t</tr>\n";
				}
			}
			
			$current .= "</table>\n";
			return $current;
		}else return false;
	}

	public function getCurrent(){
		return $this->currentInfo;
	}

	public function getTemp($weather_data){
		return array_search("Temperature", $weather_data[0]);
	}

	public function getCondition($weather_data){
		return array_search("Condition", $weather_data[0]);
	}
}

?>