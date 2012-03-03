<?
/*
GOOGLEMAP/GEOCODER PLUG-IN
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================
*/

if(defined('GOOGLEMAP_KEY')) $googlekey = GOOGLEMAP_KEY;

function googlemap_settings($action = null, $data = ''){
    if($action == null){
        // return dialog HTML
        $gkey = getPluginCustomSetting('googlemap', PLUGIN_SETTINGS_SAVETOSTD);
        if(isBlank($gkey)) $gkey = GOOGLEMAP_KEY;
    	$html = '
    	<h3 class="header">GoogleMap Plugin Settings</h3>
   		<p>You may modify the values below:</p>
    	<p>Google API Key: <input type="text" name="googleapikey" value="'.$gkey.'" maxlength="255" style="width: 300px;" /></p>
    	';
        return pluginSettingsDialogContents("GoogleMap", $html, __FUNCTION__);
    }elseif($action == PLUGIN_SETTINGS_SAVE){
        // save data to database
        parse_str($data);
        $result = savePluginCustomSettings('googlemap', $googleapikey, PLUGIN_SETTINGS_SAVETOSTD);
        return pluginSettingsDialogButtonPressed((($result) ? 'Setting saved successfully' : 'Setting was not saved'), true);
    }elseif($action == PLUGIN_SETTINGS_CLOSE){
        // closed
        //return pluginSettingsDialogButtonPressed("Closed!", true);
    }
}

class GeoCoder {
	public $doc;
	public $addr;
	public $city;
	public $state;
	public $lat;
	public $lng;
	public $accuracy;
	public $coord;
	public $zoom;
	public $maptype;
	public $ip;
	public $xml;
	public $html;
	public $file;
	public $googlekey;
	public $err;

	function  __construct() {
		global $googlekey;

		$this->googlekey = $googlekey;
	}

	function init(){
		$this->doc = new DOMDocument();
		$url = "http://maps.google.com/maps/api/geocode/xml?address=".$addr.",+".$city.",+".$state."&sensor=false";
		//echo $url;
		$this->doc->load($url); //input address
		$this->xml = $this->doc->saveXML();
		$this->html = $this->doc->saveHTML();
	}

	function geocode(){
		//Set up our variables
		$longitude = "";
		$latitude = "";
		$precision = "";
		$err = "";

		if($this->addr == "") { $err = "GEOCODE: Address missing!"; return array($err, "", ""); }

		//Three parts to the querystring: q is address, output is the format (
		$this->addr = urlencode($this->addr);
		$url = "http://maps.google.com/maps/geo?q=".$this->addr."&amp;output=csv&amp;key=".$this->googlekey;

		$data = file($url);

		//Check our Response code to ensure success
		foreach($data as $row){
			$value = trim($row);
			$value = eregi_replace("(\{|\}|\[|\]|\")", "", $value);
			if(substr(strtolower($value), 0, 4) == "code") $code = explode(":", str_replace(",", "", strtolower($value)));
			if(substr(strtolower($value), 0, 11) == "coordinates") $coordrow = strtolower($value);
			if(substr(strtolower($value), 0, 8) == "accuracy") $accurow = str_replace(",", "", strtolower($value));
		}

		if ($code[1] == "200") {
			$accuracy = explode(":", $accurow);
			$this->accuracy = $accuracy[1];
			$this->coord = explode(":", $coordrow);
			$latlon = explode(",", $coord[1]);
			$this->lat = trim($latlon[1]);
			$this->lng = trim($latlon[0]);
			if(abs($this->lat) > 90) list($this->lat, $this->lng) = swap($this->lat, $this->lng);
		} else {
			$this->err = "GEOCODE: Error in geocoding! Http error ".substr($data, 0, 3);
			$this->lat = null;
			$this->lng = null;
			$this->accuracy = null;
		}
	}

	function convertGMap2URL($location, $lat, $lng, $zoom = 14, $near = "", $label = "View Larger Map"){
		if($location != "" && floatval($lat) != 0 && floatval($lng) != 0){
			echo '<a href="http://maps.google.com/maps?f=q&amp;hl=en&amp;q='.urlencode($location).'&amp;sll='.$lat.','.$lng.'&amp;ie=UTF8&amp;ll='.$lat.','.$lng.'&amp;z='.$zoom.'&amp;hnear='.$near.'" target="_blank">'.$label.'</a>';
		}
	}

	function getLatLong(){
		//traverse the nodes to get to latitude and longitude
		$results = $this->doc->getElementsByTagName("result");
		$results = $results->item(0);
		$results = $results->getElementsByTagName("geometry");
		$results = $results->item(0);
		$results = $results->getElementsByTagName("location");

		foreach($results as $result){
			$lats = $result->getElementsByTagName("lat");
			$this->lat = $lats->item(0)->nodeValue;

			$lngs = $result->getElementsByTagName("lng");
			$this->lng = $lngs->item(0)->nodeValue;
		}
	}
}

?>