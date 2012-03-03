<?php
$debug = false;
$sandbox = false;
$sendemail = true;

($sandbox) ? $expectedShortCode = "+121212" : $expectedShortCode = "+33344";
($debug) ? $responseURL = "response.xml" : $responseURL = "http://websvcs1.jumptxt.com/smsxml/collector";

//$doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">";
$doctype = "<!DOCTYPE xiamSMS SYSTEM \"xiamSMSMessage.dtd\">";
$vendorid = "137";
$row = null;	// stores listing data

define ('DIR',dirname(dirname(__FILE__)));
define ('LOGIN_REQUIRED', false);
include '../app/config.php';

// get header data
$postText = trim(file_get_contents('php://input'));

// start processing
if($postText != "") {
	// process what was received via HTTP POST
	process($postText);
}else{
	// nothing received, process dummy data
	createSuccessResponseXML(null, null);
	createFailResponseXML(null, "FAIL", array("test"));
}

// **************************************************************

function process($xmlstring){
	global $debug, $vendorid, $sandbox, $sendemail;

	if($debug) echo "<h2>XML Data Received:</h2><pre>".htmlentities($xmlstring)."</pre>";

	//$xml = @simplexml_load_string($xmlstring);

	if($debug) {
		echo "<b>XML Input is:</b><br>";
		var_dump($xml);
	}

	if($debug) echo "<b>XML-to-Array is:</b><br><br>";
	$xmlarray = XML2Array($xmlstring);
	if(is_array($xmlarray)){
		if($debug) print_r($xmlarray);
	}else{
		if($debug) echo "FAIL: XMLIN Not Created";
		exit;
	}

	if($debug) echo "<hr>";

	// parse received xml to create paramarry
	if($debug) echo "<b>Parsing Data and XML:</b><br><br>";
	
	$result = parseReceivedXML($xmlarray);
	$paramarry = $result[2];
	$paramarry['vendorId'] = $vendorid;
	switch($result[0]){
		case "FAIL":
			createFailResponseXML($paramarry, $result[0], $result[1]);
			exit;
		case "INFO":
			createSystemResponseXML($paramarry, $result[0], $result[1]);
			break;
		case "HELP":
			createSystemResponseXML($paramarry, $result[0], $result[1]);
			break;
		case "STOP":
			createSystemResponseXML($paramarry, $result[0], $result[1]);
			break;
		default:
			break;
	}

	//Only for testing, created by Kirk
	if($sandbox) $paramarry['id'] = 3;
	
	// parse db data using paramarry
	$result = parseDBData($paramarry);
	if($result[0] == "FAIL") { createFailResponseXML($paramarry, $result[0], $result[1]); exit; }
	$content = $result;
  
	// create success response xml
	$result = createSuccessResponseXML($paramarry, $content);
	if($result[0] == "FAIL") { createFailResponseXML($paramarry, $result[0], $result[1]); exit; }
	$xmlstring = $result;

	// send email to member
	if($sendemail) sendSuccessEmail($paramarry);

	exit;
}

function XML2Array($xml, $recursive = false){
	global $debug;

    if (!$recursive){
        $array = simplexml_load_string ($xml);
    }else{
        $array = $xml;
    }

    $newArray = array() ;
    $array = (array) $array ;
    foreach ($array as $key => $value){
        $value = (array) $value ;
        if (isset($value[0])){
            $newArray[$key] = trim ($value[0]);
        }else{
            $newArray[$key] = XML2Array($value, true);
        }
    }
    return $newArray;
}

function parseReceivedXML($xmlarray){
	global $debug, $expectedShortCode;

	$return = "";
	$arry = array();
	$xmlsubarray = array();
	
	// check for deliverRequest
	if(!is_array($xmlarray['deliverRequest'])){
		// this may not transmit since there is no deliveryRequestId
		return array("FAIL", "DeliverRequest missing", $arry);
	}else{
		$xmlsubarray = $xmlarray['deliverRequest'];
		
		// check for @attributes:id
		$data = $xmlsubarray['@attributes']['id'];
		if($data == "") {
			// this may not transmit since there is no deliveryRequestId
			return array("FAIL", "No DeliverRequest Id", $arry);
		}else{
			// store all params in array for later use
			$arry['deliverRequestId'] = $data;
			
			// check for from, to, content, receivedOnGroup and xirMessageId
			$arry['from'] = $xmlsubarray['from'];
			if(!preg_match("/^(\+).[0-9]{10}$/", $arry['from'])) return array("FAIL", "From value invalid", $arry);
			$arry['to'] = $xmlsubarray['to'];
			if(!preg_match("/^(\+)[0-9]{5,6}$/", $arry['to'])) return array("FAIL", "To value invalid", $arry);
			if($arry['to'] != $expectedShortCode) return array("FAIL", "Shortcode invalid", $arry);
			$arry['content'] = $xmlsubarray['content'];
			$arry['id'] = $arry['content'];
			$arry['receivedOnGroup'] = $xmlsubarray['receivedOnGroup']['@attributes']['value'];
			$arry['xirMessageID'] = $xmlsubarray['xirMessageID']['@attributes']['value'];			
			if (strtoupper($arry['id']) == "INFO") {
				return array("INFO", "Street Text listing information, streettext.com, $0.25/message, To Stop reply STOP", $arry);
				break;
			} elseif (strtoupper($arry['id']) == "HELP" || strtoupper($arry['id']) == "AIDE") {
				return array("HELP", "Street Text listing information, streettext.com, $0.25/message, To Stop reply STOP", $arry);
				break;
			} elseif (strtoupper($arry['id']) == "STOP" || strtoupper($arry['id']) == "ARRET") {
				return array("STOP", "You have been opted out of Street Text listing information and will not receive any further messages. streettext.com", $arry);
				break;
			} else {
				return array("OK", "", $arry);
				break;
			}
		}
	}
	return array("FAIL", "Internal Error - 001", $arry);
}

function parseDBData($paramarry){
	global $debug, $row;

	// get data from database
	$sql = "select a.*, u.shortcode_active from ads a INNER JOIN users u on a.id_user = u.id_user where id = (select id_ad from ads_shortcodes where id = '".intval($paramarry['id'])."' limit 1) limit 1";
	$rs = mysql_query($sql);
	$nr = mysql_num_rows($rs);
	if($rs != false && $nr > 0){
		$row = mysql_fetch_assoc($rs);		// row holds house data and is global
		if($row['shortcode_active'] == 1){
			$content1 = "";
			$overage = 0;

			// *******************************************
			// Content #1
			//
			// -- open house date and time
			if($debug) print "--Content1--<br>";
			if($row['oh_date'] != "" && $row['oh_date'] != "0000-00-00") {
				$content1 .= "OpenHouse:".date("M d", strtotime($row['oh_date']));
				if($row['time_from'] != "" && $row['time_from'] != ":") {
					$content1 .= " ".date("g:ia", strtotime($row['time_from'].$row['periodf']));
					if($row['time_to'] != ":" && $row['time_to'] != "") $content1 .= "-".date("g:ia", strtotime($row['time_to'].$row['periodt']));
				}
				$content1 .= "\n";
				//print "[".strlen($content1).", ".$overage."] ".$content1."<br>";
			}else{
				//$content1 .= "OpenHouse:Call for appt.\n";
			}

			// -- address
			$strtypes = array(
				"Alley" => "Ally",
				"Arcade" => "Arc",
				"Avenue" => "Ave",
				"Boulevard" => "Blvd",
				"Bypass" => "Byp",
				"Circuit" => "Cir",
				"Close" => "Cl",
				"Corner" => "Cn",
				"Court" => "Ct",
				"Crescent" => "Cres",
				"Cul-de-sac" => "Cul",
				"Drive" => "Dr",
				"Esplanade" => "Esp",
				"Green" => "Gn",
				"Grove" => "Gr",
				"Highway" => "Hwy",
				"Junction" => "Jt",
				"Lane" => "Ln",
				"Link" => "Lk",
				"Mews" => "Mw",
				"Parade" => "Par",
				"Place" => "Pl",
				"Parkway" => "Pkwy",
				"Road" => "Rd",
				"Square" => "Sq",
				"Street" => "St",
				"Terrace" => "Ter",
				"Way" => "Wy"
			);

			if($row['prop_address'] != ""){
				$limit = 37;
				$address = strtoupper($row['prop_address']);
				if(strlen($address) > $limit) {
					// abbreviate street type
					$address = str_ireplace(array_keys($strtypes), array_values($strtypes), $address);
					if(strlen($address) > $limit) {
						// issue: address still too long. see if we can absorb excess up to 3 chars later
						$overage = strlen($address) - $limit;
						if($overage > 3) return array("FAIL", "Cannot display - address too long", $paramarry);
					}
				}
				$content1 .= strtoupper($address) . "\n";
				//print "[".strlen($content1).", ".$overage."] ".$content1."<br>";
			}

			// -- price
			$price = intval($row['price']);
			($price > 100000000) ? $price = "--" : $price = number_format($price);
			$content1 .= "$".$price."\n";
			//print "[".strlen($content1).", ".$overage."] ".$content1."<br>";

			// -- type
			$content1 .= $row['type']."\n";
			//print "[".strlen($content1).", ".$overage."] ".$content1."<br>";

			// -- bedrooms
			$content1 .= intval($row['bedrooms'])." bds\n";
			//print "[".strlen($content1).", ".$overage."] ".$content1."<br>";

			// -- bathrooms
			$content1 .= intval($row['bathrooms'])." bath\n";
			//print "[".strlen($content1).", ".$overage."] ".$content1."<br>";

			// -- sqft
			$content1 .= intval($row['sqt'])." sqft.\n";
			//print "[".strlen($content1).", ".$overage."] ".$content1."<br>";

			// -- realtor
			$sql = "select phone_business from users where id_user = ".$row['id_user']." limit 1";
			$rl = mysql_query($sql);
			if($rs != false){
				$realtor = mysql_fetch_array($rl);
				$phone = $realtor['phone_cell'];
				if($phone == "") $phone = $realtor['phone_business'];
				if($phone != ""){
					$phone = preg_replace("/([^0-9])+/i", "", $phone);
					if(strlen($phone) == 7){
						$phone = preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2", $phone);
					}elseif(strlen($phone) == 10){
						$phone = preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2-$3", $phone);
					}elseif(strlen($phone) == 11){
						$phone = preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2-$3-$4", $phone);
					}else{
						$phone = "invalid#";
					}
					$content1 .= "Realtor:".$phone;
					//print "[".strlen($content1).", ".$overage."] ".$content1."<br>";
				}
			}
			if($debug) print $content1." [".strlen($content1).", ".$overage."]"."<br>";

			// *******************************************
			// Content #2
			//
			// -- short descr
			if($debug) print "<Br>--Content2--<br>";
			$content2 = "";
			$overage = 0;
			$img_path = PATH_DOMAIN_FILES."/{$row['domain_id']}/ads/{$row['id']}_1.jpg";
			(file_exists(DIR.$img_path)) ? $image_exists = true : $image_exists = false;

			$descr = $row['prop_shortdescr'];
			($image_exists) ? $limit = 82 : $limit = 82 + 53;
			if(strlen($descr) > $limit) $descr = substr($descr, 0, $limit);
			$content2 .= $descr."\n";
			//print "[".strlen($content2).", ".$overage."] ".$content2."<br>";

			// -- photos
			if($image_exists){
				$content2 .= "Photos http://housepal.ca/".intval($paramarry['id'])." std data rates apply";
				//$content2 .= "Photos http://housepal.ca/".$row['domain_id']."/".intval($paramarry['id'])." std data rates apply";
				//print "[".strlen($content2).", ".$overage."] ".$content2."<br>";
			}
			if($debug) print $content2." [".strlen($content2).", ".$overage."]"."<br>";

			// *******************************************
			// Finaize Content
			//
			if(strlen($content1) > 136 || strlen($content2) > 136){
				return array("FAIL", "Internal Error - 002", $paramarry);
			}
			return array($content1, $content2);
		}else{
			// shortcode_active = 0
			return array("FAIL", "This realtors Street Text membership is suspended, we apologize for any inconvenience (id ".$paramarry['id'].")", $paramarry);
		}
	}else{
		return array("FAIL", "Data not found for ".$paramarry['id'], $paramarry);
	}
}

/*-----------------------------------------------------------------------------*/

function sendxhtmlheader($usexml) {
	// constructs header
	if ($usexml == 1) {
		header("Content-Type: application/xhtml+xml; charset=utf-8");
	} else {
		header("Content-type: text/html; charset=utf-8");
	}
}

function sendDocType($page, $usexml){
	global $doctype;
	
	$xhtmldtd = "\n{$doctype}\n";
	$bar = preg_replace('/\n/', $xhtmldtd, $page, 1);
	return $bar;
}

function sendpage($page, $usexml, $doctype) {
	// outputs xml to browser page
	if(!isset($usexml)) $usexml = 1;
	$bar = sendDocType($page, $usexml);
	sendxhtmlheader($usexml);
	print($bar);
}

function createSuccessResponseXML($paramarry = null, $dataarry = null){
	global $debug, $sandbox;
	
	// *******************************************
	// Prepare XML output
	if($paramarry == null || $dataarry == null){
		$paramarry = array("deliverRequestId" => "betye54", "from" => "+12508630337", "to" => "+121212", "vendorId" => "137", "receivedOnGroup" => "RG_PROD");
		$dataarry = array(1 => "content1", 2 => "content2");
	}
	
	$xmlarry = array(
		"from" => $paramarry['to'],
		"to" => $paramarry['from'],
		"content" => "",
		"sendOnGroup" => $paramarry['receivedOnGroup'],
		"tariffCode" => "C000"
	);
	$pagenum = 1;

	// create header
	if (!isset($usexml)) {
		$usexml=1;
	}

	if ($usexml == 1) {
		if (isset($_SERVER['HTTP_ACCEPT'])) {
			if(!strpos($_SERVER['HTTP_ACCEPT'], "application/xhtml+xml")) {
				$usexml=0;
			}
		} else {
			$usexml=0;
		}
	}

	// create doctype
	$dom  = new DOMDocument("1.0", "UTF-8");
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;

	// create root element
	$root = $dom->createElement("xiamSMS"); $dom->appendChild($root);

	// --- response section ---
	// create child element under root
	$item = $dom->createElement('deliverResponse'); $root->appendChild($item);
	// create attribute node in deliverResponse
	$attr = $dom->createAttribute('id'); $item->appendChild($attr);
	// create attribute value for deliverResponse:id
	$atval = $dom->createTextNode($paramarry['deliverRequestId']); $attr->appendChild($atval);

	// create child element under deliverResponse
	$child = $dom->createElement('result'); $item->appendChild($child);
	// create attribute node for result
	$attr = $dom->createAttribute('status'); $child->appendChild($attr);
	// create attribute value for result:status
	$atval = $dom->createTextNode('OK'); $attr->appendChild($atval);
	// create text node for result
	$text = $dom->createTextNode($paramarry['from']); $child->appendChild($text);

	// --- content sections ---
	foreach($dataarry as $data){
		$xmlarry['content'] = $data;
		(count($dataarry) > 1) ? $pageid = "-$pagenum" : $pageid = "";

		// create child element under root
		$item = $dom->createElement('submitRequest'); $root->appendChild($item);
		// create attribute node in child
		$attr = $dom->createAttribute('id'); $item->appendChild($attr);
		// create attribute value for child:id
		$atval = $dom->createTextNode($paramarry['vendorId'].$pageid); $attr->appendChild($atval);

		// create child element under submitRequest
		$child = $dom->createElement('from'); $item->appendChild($child);
		// create attribute value for child:from
		$text = $dom->createTextNode($xmlarry['from']); $child->appendChild($text);

		// create child element under submitRequest
		$child = $dom->createElement('to'); $item->appendChild($child);
		// create attribute value for child:to
		$text = $dom->createTextNode($xmlarry['to']); $child->appendChild($text);

		// create child element under submitRequest
		$child = $dom->createElement('content'); $item->appendChild($child);
		// create attribute node for content
		$attr = $dom->createAttribute('type'); $child->appendChild($attr);
		// create attribute value for content:type
		$atval = $dom->createTextNode('text'); $attr->appendChild($atval);
		// create attribute value for child:content
		$text = $dom->createTextNode($xmlarry['content']); $child->appendChild($text);		
		
		// create child element under submitRequest
		$child = $dom->createElement('sendOnGroup'); $item->appendChild($child);
		// create attribute node in child
		$attr = $dom->createAttribute('value'); $child->appendChild($attr);
		// create attribute value for child:id
		$atval = $dom->createTextNode($xmlarry['sendOnGroup']); $attr->appendChild($atval);

		// create child element under submitRequest
		$child = $dom->createElement('tariffCode'); $item->appendChild($child);
		// create attribute node in child
		$attr = $dom->createAttribute('value'); $child->appendChild($attr);
		// create attribute value for child:id
		$atval = $dom->createTextNode($xmlarry['tariffCode']); $attr->appendChild($atval);

		$pagenum++;
	}

	// save tree to file
	$dom->save("response.xml");

	// save tree to string
	$xml = $dom->saveXML();
	
	if($debug) {
		// output to browser
		sendpage($xml, $usexml);
	}else{
		// send to server
		sendXMLOverPOST_cURL($xml);
	}
}

function createFailResponseXML($paramarry = null, $status = null, $err = null){
	global $debug, $sandbox;

	// *******************************************
	// Prepare XML output

	if($paramarry == null || $err == null){
		$paramarry = array("deliverRequestId" => "betye54", "from" => "+14165551234", "to" => "+444555", "vendorId" => "545vd68", "receivedOnGroup" => "ROUTINGCODE");
		$err = "Demo error";
	}

	$xmlarry = array(
		"from" => $paramarry['to'],
		"to" => $paramarry['from'],
		"content" => $err,
		"sendOnGroup" => $paramarry['receivedOnGroup'],
		"tariffCode" => "C000"
	);

	// create header
	if (!isset($usexml)) {
		$usexml=1;
	}

	if ($usexml == 1) {
		if (isset($_SERVER['HTTP_ACCEPT'])) {
			if(!strpos($_SERVER['HTTP_ACCEPT'], "application/xhtml+xml")) {
				$usexml=0;
			}
		} else {
			$usexml=0;
		}
	}

	// create doctype
	$dom  = new DOMDocument("1.0", "UTF-8");
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;

	// create root element
	$root = $dom->createElement("xiamSMS"); $dom->appendChild($root);

	// --- response section ---
	// create child element under root
	$item = $dom->createElement('deliverResponse'); $root->appendChild($item);
	// create attribute node in deliverResponse
	$attr = $dom->createAttribute('id'); $item->appendChild($attr);
	// create attribute value for deliverResponse:id
	$atval = $dom->createTextNode($paramarry['deliverRequestId']); $attr->appendChild($atval);

	// create child element under deliverResponse
	$child = $dom->createElement('result'); $item->appendChild($child);
	// create attribute node for result
	$attr = $dom->createAttribute('status'); $child->appendChild($attr);
	// create attribute value for result:status
	$atval = $dom->createTextNode($status); $attr->appendChild($atval);
	// create text node for result
	$text = $dom->createTextNode($err); $child->appendChild($text);

		// create child element under root
		$item = $dom->createElement('submitRequest'); $root->appendChild($item);
		// create attribute node in child
		$attr = $dom->createAttribute('id'); $item->appendChild($attr);
		// create attribute value for child:id
		$atval = $dom->createTextNode($paramarry['vendorId'].$pageid); $attr->appendChild($atval);

		// create child element under submitRequest
		$child = $dom->createElement('from'); $item->appendChild($child);
		// create attribute value for child:from
		$text = $dom->createTextNode($xmlarry['from']); $child->appendChild($text);

		// create child element under submitRequest
		$child = $dom->createElement('to'); $item->appendChild($child);
		// create attribute value for child:to
		$text = $dom->createTextNode($xmlarry['to']); $child->appendChild($text);

		// create child element under submitRequest
		$child = $dom->createElement('content'); $item->appendChild($child);
		// create attribute node for content
		$attr = $dom->createAttribute('type'); $child->appendChild($attr);
		// create attribute value for content:type
		$atval = $dom->createTextNode('text'); $attr->appendChild($atval);
		// create attribute value for child:content
		$text = $dom->createTextNode($xmlarry['content']); $child->appendChild($text);

		// create child element under submitRequest
		$child = $dom->createElement('sendOnGroup'); $item->appendChild($child);
		// create attribute node in child
		$attr = $dom->createAttribute('value'); $child->appendChild($attr);
		// create attribute value for child:id
		$atval = $dom->createTextNode($xmlarry['sendOnGroup']); $attr->appendChild($atval);

	// save tree to file
	$dom->save("response.xml");

	// save tree to string
	$xml = $dom->saveXML();

	if($debug) {
		// output to browser
		sendpage($xml, $usexml);
	}else{
		// send to server
		sendXMLOverPOST_cURL($xml);
	}
	exit;
}

function createSystemResponseXML($paramarry = null, $status = null, $stmt = null){
	global $debug, $sandbox;

	// *******************************************
	// Prepare XML output

	if($paramarry == null || $stmt == null){
		$paramarry = array("deliverRequestId" => "betye54", "from" => "+14165551234", "to" => "+444555", "vendorId" => "545vd68", "receivedOnGroup" => "ROUTINGCODE");
		$stmt = "Demo statement";
	}

	$xmlarry = array(
		"from" => $paramarry['to'],
		"to" => $paramarry['from'],
		"content" => $stmt,
		"sendOnGroup" => $paramarry['receivedOnGroup'],
		"tariffCode" => "C000"
	);

	// create header
	if (!isset($usexml)) {
		$usexml=1;
	}

	if ($usexml == 1) {
		if (isset($_SERVER['HTTP_ACCEPT'])) {
			if(!strpos($_SERVER['HTTP_ACCEPT'], "application/xhtml+xml")) {
				$usexml=0;
			}
		} else {
			$usexml=0;
		}
	}

	// create doctype
	$dom  = new DOMDocument("1.0", "UTF-8");
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;

	// create root element
	$root = $dom->createElement("xiamSMS"); $dom->appendChild($root);

	// --- response section ---
	// create child element under root
	$item = $dom->createElement('deliverResponse'); $root->appendChild($item);
	// create attribute node in deliverResponse
	$attr = $dom->createAttribute('id'); $item->appendChild($attr);
	// create attribute value for deliverResponse:id
	$atval = $dom->createTextNode($paramarry['deliverRequestId']); $attr->appendChild($atval);

	// create child element under deliverResponse
	$child = $dom->createElement('result'); $item->appendChild($child);
	// create attribute node for result
	$attr = $dom->createAttribute('status'); $child->appendChild($attr);
	// create attribute value for result:status
	$atval = $dom->createTextNode($status); $attr->appendChild($atval);
	// create text node for result
	$text = $dom->createTextNode($stmt); $child->appendChild($text);

		// create child element under root
		$item = $dom->createElement('submitRequest'); $root->appendChild($item);
		// create attribute node in child
		$attr = $dom->createAttribute('id'); $item->appendChild($attr);
		// create attribute value for child:id
		$atval = $dom->createTextNode($paramarry['vendorId'].$pageid); $attr->appendChild($atval);

		// create child element under submitRequest
		$child = $dom->createElement('from'); $item->appendChild($child);
		// create attribute value for child:from
		$text = $dom->createTextNode($xmlarry['from']); $child->appendChild($text);

		// create child element under submitRequest
		$child = $dom->createElement('to'); $item->appendChild($child);
		// create attribute value for child:to
		$text = $dom->createTextNode($xmlarry['to']); $child->appendChild($text);

		// create child element under submitRequest
		$child = $dom->createElement('content'); $item->appendChild($child);
		// create attribute node for content
		$attr = $dom->createAttribute('type'); $child->appendChild($attr);
		// create attribute value for content:type
		$atval = $dom->createTextNode('text'); $attr->appendChild($atval);
		// create attribute value for child:content
		$text = $dom->createTextNode($xmlarry['content']); $child->appendChild($text);

		// create child element under submitRequest
		$child = $dom->createElement('sendOnGroup'); $item->appendChild($child);
		// create attribute node in child
		$attr = $dom->createAttribute('value'); $child->appendChild($attr);
		// create attribute value for child:id
		$atval = $dom->createTextNode($xmlarry['sendOnGroup']); $attr->appendChild($atval);

	// save tree to file
	$dom->save("response.xml");

	// save tree to string
	$xml = $dom->saveXML();

	if($debug) {
		// output to browser
		sendpage($xml, $usexml);
	}else{
		// send to server
		sendXMLOverPOST_cURL($xml);
	}
	exit;
}

function sendXMLOverPOST_cURL($xml){
	global $debug, $responseURL, $sandbox, $vendorid;

	// build http request
	// *******************************************
	// Prepare XML output
	$xml = sendDocType($xml, $usexml);

	// send it
	if($debug){
		$fp = fopen($responseURL, 'w+', false);
		fwrite($fp, $xml);
	}else{
		$xml_length = strlen($xml);
		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, $responseURL);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 4);
		curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("POST /smsxml/collector HTTP/1.1",
													"Content-Type: text/xml; charset=utf-8",
													"X-XIAM-Provider-ID: " . $vendorid,
													"User-Agent: Jakarta Commons-HttpClient/3.0",
													"Content-Length: " . $xml_length));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml);

		$result = curl_exec( $ch );
		curl_close($ch);
	}
	fclose($fp);
}

function sendSuccessEmail($paramarry){
	global $debug, $row;

	if($row['id_user'] > 0){
		$sql = "select * from users where id_user = ".$row['id_user']." limit 1";
		$rl = mysql_query($sql);
		$realtor = mysql_fetch_array($rl);
		preg_match("/(\+)([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", $paramarry['from'], $parts);
		$phone = $parts[2]."-".$parts[3]."-".$parts[4]."-".$parts[5];
		
		//$msg  = "Street Text Header\n\n";
		$msg .= "Dear ".$realtor['name']."\n\n";
		$msg .= "At ".date("g:ia")." ".$phone." texted ".$row['prop_address']." for information.\n\n";
		$msg .= "You can use this information as feedback for your seller and you can contact the phone number through text or phone call. ";
		$msg .= "We will also send you an email if the link was clicked through to your listing page to view photos and details.\n\n";
		$msg .= "Thank you and happy selling.\n\n";
		//$msg .= "Street Text Footer";

		mail("chrisd@navigatormm.com", "OpenHouse Street Text Inquiry", "test msg");
		mysql_query("insert into smsmsgs (date, status, note) values (now(), 'ok', '$sql')");
	}else{
		$sql = "insert into smsmsgs (date, status, note) values (now(), 'error', 'User ID invalid')";
		mysql_query($sql);
	}
}
?>
