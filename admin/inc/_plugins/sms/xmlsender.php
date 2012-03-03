<?php
$debug = false;
//require_once("class_HTTPRetriever.php");
//$http = &new HTTPRetriever();
//$type = $_GET['type'];
$doctype = "<!DOCTYPE xiamSMS SYSTEM \"http://websvcs1.jumptxt.com/smsxml/xiamSMSMessage.dtd\">";
$url = "http://www.openhousekelowna.com/sms/xmlreceiver.php";

$xml = createXML();
//echo "<h2>XML File Sent:</h2><pre>".htmlentities($xml, ENT_QUOTES)."</pre>";
if($debug) {
	// output to browser
	sendpage($xml, 1);
	$response = sendXMLOverPOST_cURL($xml, 1);
}else{
	// send to server
	$response = sendXMLOverPOST_cURL($xml, 1);
}
//echo "<BR>Response From Receiver File:<br><pre>".$response."</pre>";

function createXML(){
	global $debug, $sandbox;

	$xmlarry = array(
		"deliverRequestId" => "495596",
		"from" => "+12505753067",
		"to" => "+121212",
		"content" => "smmed",
		"receivedOnGroup" => "ROUTINGCODE",
		"xirMessageID" => "495596"
	);
	
	// create a new XML document
	$dom = new DomDocument('1.0', 'UTF-8');
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;

	// create root element
	$root = $dom->createElement("xiamSMS"); $dom->appendChild($root);

	// --- response section ---
	// create child element under root
	$item = $dom->createElement('deliverRequest'); $root->appendChild($item);
	// create attribute node in deliverResponse
	$attr = $dom->createAttribute('id'); $item->appendChild($attr);
	// create attribute value for deliverResponse:id
	$atval = $dom->createTextNode($xmlarry['deliverRequestId']); $attr->appendChild($atval);

	// create child element under deliverRequest
	$child = $dom->createElement('from'); $item->appendChild($child);
	// create attribute node for from
	$attr = $dom->createAttribute('ton'); $child->appendChild($attr);
	// create attribute value for from:ton
	$atval = $dom->createTextNode(''); $attr->appendChild($atval);
	// create text node for from
	$text = $dom->createTextNode($xmlarry['from']); $child->appendChild($text);

	// create child element under deliverRequest
	$child = $dom->createElement('to'); $item->appendChild($child);
	// create attribute node for from
	$attr = $dom->createAttribute('ton'); $child->appendChild($attr);
	// create attribute value for to:ton
	$atval = $dom->createTextNode(''); $attr->appendChild($atval);
	// create text node for to
	$text = $dom->createTextNode($xmlarry['to']); $child->appendChild($text);

	// create child element under deliverRequest
	$child = $dom->createElement('content'); $item->appendChild($child);
	// create attribute node for content
	$attr = $dom->createAttribute('type'); $child->appendChild($attr);
	// create attribute value for content:type
	$atval = $dom->createTextNode('text'); $attr->appendChild($atval);
	// create text node for content
	$text = $dom->createTextNode($xmlarry['content']); $child->appendChild($text);

	// create child element under deliverRequest
	$child = $dom->createElement('receivedOnGroup'); $item->appendChild($child);
	// create attribute node for receivedOnGroup
	$attr = $dom->createAttribute('value'); $child->appendChild($attr);
	// create attribute value for receivedOnGroup:value
	$atval = $dom->createTextNode($xmlarry['receivedOnGroup']); $attr->appendChild($atval);

	// create child element under deliverRequest
	$child = $dom->createElement('xirMessageID'); $item->appendChild($child);
	// create attribute node for xirMessageID
	$attr = $dom->createAttribute('value'); $child->appendChild($attr);
	// create attribute value for xirMessageID:value
	$atval = $dom->createTextNode($xmlarry['xirMessageID']); $attr->appendChild($atval);

	// save tree to file
	$dom->save("sender.xml");

	// save tree to string
	$xml = $dom->saveXML();
	$xml = sendDocType($xml, 1);

	return $xml;
}

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

function sendpage($page, $usexml) {
	// outputs xml to browser page
	if(!isset($usexml)) $usexml = 1;
	$bar = sendDocType($page, $usexml);
	sendxhtmlheader($usexml);
	print($bar);
}

function sendXMLOverPOST_stream($xml){
	global $debug, $responseURL, $sandbox, $url;

	$context = stream_context_create(array(
			'http' => array(
			'method' => 'POST',
			'header' => $_SERVER['SERVER_PROTOCOL']." 200 OK\r\n".
						"Server: ".substr($_SERVER['SERVER_SOFTWARE'], 0, strpos($_SERVER['SERVER_SOFTWARE'], " "))."\r\n".
						"Content-Type: text/xml; charset=utf-8\r\n".
						"Transfer-Encoding: chunked\r\n"."Date: ".date("D, d M Y H:i:s T").
						"Content-Length: ".strlen(implode("", $data)),
			'content' => $xml,
			'timeout' => 10,
		),
	));

	// send it
	if(!$debug){
		$fp = fopen($url, 'r', false, $context);
		$result = fread($fp);
		fpassthru($fp);
	}
	fclose($fp);
	return $result;
}

function sendXMLOverPOST_cURL($xml) {
	global $debug, $responseURL, $sandbox;

	if($debug){
		$fp = fopen($responseURL, 'w+', false);
		fwrite($fp, $xml);
	}else{
		$ch = curl_init($responseURL);
		curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml; charset=utf-8'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		curl_close($ch);

		echo "*{$output}*";
	}
}
?>
