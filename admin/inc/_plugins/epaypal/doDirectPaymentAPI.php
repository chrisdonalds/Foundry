<?php

function paypal_directpayment(){
	define('USE_PROXY', FALSE);
	define('PROXY_HOST', '127.0.0.1');
	define('PROXY_PORT', '808');
	define('PAYPAL_URL', 'https://www.paypal.com/webscr&cmd=_express-checkout&token=');
	define('VERSION', '53.0');

	$item_name = $_POST['item_name'];
	$qty = $_POST['qty'];
	$paymentType = $_POST['paymentType'];
	$creditCardType= $_POST['creditCardType'];
	$creditCardNumber = $_POST['creditCardNumber'];
	$expDateMonth = $_POST['expdate_month'];
	$expDateYear = $_POST['expdate_year'];
	$cvv2Number = $_POST['cvv2Number'];
	$firstname =$_POST['firstName'];
	$lastname = $_POST['lastName'];
	$address1 = $_POST['address1'];
	$address2 = $_POST['address2'];
	if ($address1 != "") $address1 .= ' '.$address2;
	$city =   $_POST['city'];
	$state =  $_POST['state'];
	$zipcode =   $_POST['zip'];
	$phone =   $_POST['phone'];
	$email =   $_POST['email'];
	$countryCode = $_POST['country'];
	$padDateMonth = str_pad($expDateMonth, 2, '0', STR_PAD_LEFT);
	$currencyCode = $_POST['currencyCode'];
	$paymentType = urlencode($_POST['paymentType']);
	$invoice = $_POST['invoice'];

	$nvpstr = "&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType".
	"&ACCT=$creditCardNumber&EXPDATE=".$padDateMonth.$expDateYear."&CVV2=$cvv2Number".
	"&FIRSTNAME=".urlencode($firstname)."&LASTNAME=".urlencode($lastname).
	"&STREET=".urlencode($address1)."&CITY=".urlencode($city)."&STATE=$state".
	"&ZIP=".urlencode($zipcode)."&COUNTRYCODE=$countryCode&CURRENCYCODE=$currencyCode".
	"&EMAIL=".urlencode($email)."&INVOICE=".urlencode($invoice)."&PHONE=".urlencode($phone);

	$resArray = hash_call("doDirectPayment", $nvpstr);

	$ack = strtoupper($resArray["ACK"]);
	$amt = $resArray["AMT"];
	$transactionid = $resArray['TRANSACTIONID'];
	$failureMessage = $resArray['L_LONGMESSAGE0'];

	$API_result = array();
	if($ack=='FAILURE'){
		$API_result['ack'] = "FAILED";
		$API_result['msg'] = $failureMessage;
		$API_result['txid'] = null;
	}else{
		$API_result['ack'] = "SUCCESS";
		$API_result['msg'] = "";
		$API_result['txid'] = $transactionid;
	}
	
	return $API_result;
}

function hash_call($methodName, $nvpStr){
	ini_set('max_execution_time', 300);
	//declaring of global variables

	global $API_Endpoint, $version, $API_UserName, $API_Password, $API_Signature, $nvp_Header;

	$API_UserName =	API_USERNAME;
	$API_Password = API_PASSWORD;
	$API_Signature= API_SIGNATURE;
	$API_Endpoint = API_ENDPOINT;

	$version=VERSION;
	//setting the curl parameters.
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,$API_Endpoint);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);

	//turning off the server and peer verification(TrustManager Concept).
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POST, 1);
	//if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
	//Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php

	if(USE_PROXY)
		//echo CURLOPT_PROXY;
		curl_setopt ($ch, CURLOPT_PROXY, PROXY_HOST.":".PROXY_PORT);

	//NVPRequest for submitting to server
	//echo $version;
	$nvpreq="METHOD=".urlencode($methodName)."&VERSION=".urlencode($version)."&PWD=".urlencode($API_Password)."&USER=".urlencode($API_UserName)."&SIGNATURE=".urlencode($API_Signature).$nvpStr;
	//print $nvpreq."<br>";

	//setting the nvpreq as POST FIELD to curl
	//CURLOPT_POSTFIELDS;
	curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);

	//getting response from server
	$response = curl_exec($ch);

	// echo gettype($response);
	//echo "lkj"; die;
	//convrting NVPResponse to an Associative Array
	$nvpResArray=deformatNVP($response);
	$nvpReqArray=deformatNVP($nvpreq);

	//print_r($nvpReqArray);
	$_SESSION['nvpReqArray']=$nvpReqArray;

	if (curl_errno($ch)) {
		// moving to display page to display curl errors
		echo $_SESSION['curl_error_no']=curl_errno($ch) ;
		echo $_SESSION['curl_error_msg']=curl_error($ch); die;
		$location = "error.php";
		header("Location: $location");
	}else{
		//closing the curl
		curl_close($ch);
	}

	return $nvpResArray;
}

function deformatNVP($nvpstr){
	$intial=0;
	$nvpArray = array();

	while(strlen($nvpstr)){
		//postion of Key
		$keypos= strpos($nvpstr,'=');
		//position of value
		$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);

		/*getting the Key and Value values and storing in a Associative Array*/
		$keyval=substr($nvpstr,$intial,$keypos);
		$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
		//decoding the respose
		$nvpArray[urldecode($keyval)] =urldecode( $valval);
		$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
	}

	return $nvpArray;
}
?>