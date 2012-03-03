SMS PLUG-IN
jumpTxt.com format
MT & MO enabled
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================

-- Inclusions --
none

-- Preparation --
$debug = false;             // true to turn on debugger.  xml data is not passed to calling server
$sandbox = false;           // true to use sms sandbox account.  false to use real world account
$sendemail = true;          // true to send email to account owner

($sandbox) ? $expectedShortCode = "+sandbox calling number" : $expectedShortCode = "+real world calling number";
($debug) ? $responseURL = "response.xml" : $responseURL = "http://websvcs1.jumptxt.com/smsxml/collector";

-- Implementation --
Calling server simply calls url of xmlreceiver.php script and passes xml data via http-post

-- Return --
last call saved to response.xml file
if debug is false, xml will be http-posted to calling server
?>