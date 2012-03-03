PAYPAL PLUG-IN
Direct Payment API
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================

-- Inclusions --
$incl = "paypal";

-- Preparation --
1. set the username, password and signature in paypal.core.php
	define('API_USERNAME',  'chrisd_1255548429_biz_api1.navigatormm.com');   // username without @
	define('API_PASSWORD',  '1255548547');		// NOT the login password
	define('API_SIGNATURE', 'AqkQS.YoEM.F4B35mOs-ONhn4XOIAunscdS.NGK8t-NGbZ3HHTSRNjap');

2. run paypal_setup($golive = true/false);

-- Parameters (Direct Payment API) --
$item_name                              // name of item
$qty                                    // quantity
$paymentType                            // payment type (see paymentaction on paypal developer site)
$creditCardType                         // credit card type ()
$creditCardNumber                       // credit card number
$expDateMonth                           // card expiry month (2 digits)
$expDateYear                            // card expiry year (2 or 4 digits)
$cvv2Number                             // card verification number (3 or 4 digits)
$firstname                              // cardholder's first name
$lastname                               // cardholder's last name
$address1                               // cardholder's billing address line 1
$address2                               // cardholder's billing address line 2
$city                                   // cardholder's billing address city
$state                                  // cardholder's billing address state/province
$zipcode                                // cardholder's billing address zip/postal code
$phone                                  // cardholder's billing address phone number
$email                                  // cardholder's email
$countryCode                            // cardholder's billing address ISO country code
$currencyCode                           // currency code of transaction (eg. USD, CAD)
$invoice                                // invoice number (optional)

-- Parameters (Form submit) --
$data = array([elements]);
cmd	=> _cart
upload => 1
business => [PayPal business identifier, eg: richte_1244125108_biz@mac.com]
currency_code => [ISO 3-char currency]
lc => [ISO 2-char location]

first_name => [customer first name]
last_name => [customer last name
address1 => [customer address 1 of 2]
address2 => [customer address 2 of 2]
city => [customer city]
state => [customer province/state]
zip => [customer postal/zip code]
country => [ISO 2-char country code]
email => [customer email]

item_name_x => [item name, eg: plain nuts]
item_number_x => [item sequence number, eg: 2]
quantity_x => [quantity of item]
amount_x => [dollar amount of item]
no_shipping_x => [add shipping, eg: 0 to ask for address, 1 to ignore address, 2 to ask for and require an address]
no_note_x => [add note, eg: 1 for no note, 0 for note]
return => [return page on success]
cancel_return => [return page on fail/cancel]


-- Implementation (Direct Payment API) --
$api_result = paypal_directpayment();
[Data provided via $_POST]

-- Implementation (Form submit) --
$api_result = paypal_senddata($data);

-- Return Value --
$API_result['ack'] == "FAILED" or "SUCCESS"
?>