<?
// ---------------------------
//
// FOUNDRY RPC & ATOM FUNCTIONS
// - Pingback Client
// - Pingback Server
// - Atom Client
// - Atom Server
//
// Credits: based on Pingor from http://blog.kapsobor.de/articles/pingor/
// License: BSD License
//
// ------------------------------------------
//
define ("RPCLOADED", true);

if(!defined("VALID_LOAD")) die("This file cannot be accessed directly!");

function rpc_pingback($text, $sourceURI, $sameSite = False) {
    $targets = rpc_getTargets($text);
    foreach($targets as $targetURI) {
        preg_match("/X-Pingback: (\S+)/i", rpc_httpreq($targetURI, "HEAD"), $matches);

        if(isset($matches[1])) {
            $pingbackserver = $matches[1];
        } else {
            preg_match("/<link rel=\"pingback\" href=\"([^\"]+)\" ?\/?>/i", rpc_httpreq($targetURI), $matches);

            $pingbackserver = $matches[1];
            if(!$pingbackserver) {
                continue;
            }
        }
        if ($sameSite !== False) {
            preg_match("/^http:\/\/([^\/]+)(.*)$/", $sourceURI, $matches);
            $hostname1 = $matches[1];
            preg_match("/^http:\/\/([^\/]+)(.*)$/", $targetURI, $matches);
            $hostname2 = $matches[1];
            if ($hostname2 == $hostname) {
                continue;
            }
        }
        rpc_ping($pingbackserver, $sourceURI, $targetURI);
    }
    return true;
}

function rpc_getTargets($text) {
    preg_match_all("/<a[^>]+href=.(http:\/\/[^'\"]+)/i", $text, $matches);
    return array_unique($matches[1]);
}

function rpc_httpreq($uri, $method = "GET", $add_header = '', $payload =  '') {
    preg_match("/^http:\/\/([^\/]+)(.*)$/", $uri, $matches);
    $hostname = $matches[1];
    $script = $matches[2];
    if(empty($hostname)) {
        return;
    }
    $fp = fsockopen($hostname, 80, $errno, $errstr, 30);
    if(!$fp) {
        return;
    }
    fwrite($fp, "$method $script HTTP/1.1
Host: $hostname
User-Agent: pingback
$add_header

$payload
");
    stream_set_timeout($fp, 5);
    $res = stream_get_contents($fp);
    fclose($fp);
    return $res;
}

function rpc_ping($pingbackserver, $sourceURI, $targetURI) {
    $payload = <<<ENDE
    <?xml version="1.0"?>
    <methodCall>
        <methodName>pingback.ping</methodName>
        <params>
        <param>
        <value>$sourceURI</value>
        </param>
        <param>
        <value>$targetURI</value>
        </param>
        </params>
    </methodCall>
ENDE;
    $length = strlen($payload);
    $request_head = "Content-Type: text/xml\r\nContent-length: $length";
    return rpc_httpreq($pingbackserver, "POST", $request_head, $payload);
}
?>
