<?php
	foreach($_REQUEST as $key => $value) $$key = $value;

	include(WEB_URL.INC_FOLDER."paypal/doDirectPaymentAPI.php");

	if($API_result['ack'] == "FAILED"){
		echo "<b>The transaction Failed</b><br/><br/>";
		echo $API_result['msg']."<br/>";
		echo "<br/><br/>Please <a href=\"checkout_form.php?item_name=$item_name&qty=$qty\">check your details</a> and try again<br/>";
	}else{
		echo "<b>The transaction has been completed successfully</b><br/><br/>";
		echo "Transaction ID: ".$API_result['txid']."<br/>";
		echo "<br/><b>Thank you for ordering from ".BUSINESS.".<br/>";
		echo "Your order has been processed.</b>.<br/>";
	}
?>
