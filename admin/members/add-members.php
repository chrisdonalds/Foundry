<?php

//  ------------------------------------------------------------------------------------
//  CREATE
//  ------------------------------------------------------------------------------------

$incl = "fileuploader filehandler autocomplete(city)";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."members";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$contact = clean_text(getRequestVar('contact'));
	$company = clean_text(getRequestVar('company'));
	$streetname = ucwords_smart(clean_text(getRequestVar('streetname')));
	$city = ucwords_smart(clean_text(getRequestVar('city')));
	$province = intval(getRequestVar('province'));
	$country = intval(getRequestVar('country'));
	if($country == 0) $country = intval($canada_id);
	$postalcode = clean_text(getRequestVar('postalcode'));
	$day_phone = clean_text(getRequestVar('day_phone'));
	$toll_phone = clean_text(getRequestVar('toll_phone'));
	$fax = clean_text(getRequestVar('fax'));
	$email = clean_text(getRequestVar('email'));
	$website = clean_text(strtolower(getRequestVar('website')));
	if($website == 'http://' || $website == 'https://' || $website == 'http') $website = '';
	if(substr($website, 0, 7) != 'http://' && substr($website, 0, 8) != 'https://') $website = 'http://'.$website;
	$activated = intval(getRequestVar('activated'));
	$affiliate_id = intval(getRequestVar('affiliate_id'));
	$npassword = getRequestVar('password');
	if($npassword != "") $npassword = md5(getRequestVar('password'));
	$lastID = getLastID($db->table, "id");
	if(getRecItem($db->table, "id", "email = '$email'") != "" && $email != ''){
		addErrorMsg(sprintf(DUPLICATE_RECORD, "The email `".strtoupper($email)."`", "", "email"));
	}

	if (!errorMsgExists()) {
		// sql fields and data lists. end both with ', '
		$sqlfields = "contact, company, password, streetname, city, province, country, postalcode, day_phone, toll_phone, fax, email, website, activated, affiliate_id";
		$sqldata   = "'$contact', '$company', '$password', '$streetname', '$city', $province, $country, '$postalcode', '$day_phone', '$toll_phone', '$fax', '$email', '$website', $activated', $affiliate_id";
    	switch($_page->savebuttonpressed) {
    		case "save":
    			$_page->row_id = insertRec($db->table, $sqlfields.", date_created", $sqldata.", NOW()");
                if($_page->row_id > 0){
               		addErrorStatMsg(SUCCESS_CREATE);
                }else{
                	addErrorStatMsg(FAILURE_CREATE);
                }
    			break;
    		case "saveact":
    			$_page->row_id = insertRec($db->table, $sqlfields.", activated, date_published", $sqldata.", 1, NOW()");
                if($_page->row_id > 0){
               		addErrorStatMsg(SUCCESS_CREATE);
                }else{
                	addErrorStatMsg(FAILURE_CREATE);
                }
    			break;
    	}
	}

	if(getErrorStatMsg(SUCCESS_CREATE)) gotoEditPage();
}

$password = "";
$contact = getRequestVar('contact');
$company = getRequestVar('company');
$streetname = getRequestVar('streetname');
$city = getRequestVar('city');
$province = intval(getRequestVar('province'));
$country = intval(getRequestVar('country'));
if($country == 0) $country = intval($canada_id);
$postalcode = getRequestVar('postalcode');
$day_phone = getRequestVar('day_phone');
$toll_phone = getRequestVar('toll_phone');
$fax = getRequestVar('fax');
$email = getRequestVar('email');
$website = getRequestVar('website');
$activated = getRequestVar('activated');
if($activated == '') $activated = "1";
$affiliate_id = getRequestVar('affiliate_id');

// build arrays
$provlist = flattenDBArray(getRec(DB_TABLE_PREFIX."provinces", "id, name", "country_id = ".$canada_id, "name", ""), "id", "name");
$provlist = array("0"=>"-- Select a Province --") + $provlist;
$affiliatelist = flattenDBArray(getRec(DB_TABLE_PREFIX."affiliates", "id, concat(affiliate_code, ' - ', company) as affname", "activated=1", "", ""), "id", "affname");
$affiliatelist = array("0"=>"-- Select a Promo Code --") + $affiliatelist;

// build javascript block
$js = new JSBlock();
$js->subject = "member";
$js->section = "members";
$js->addCheckReqEntry('company', 'Please enter the company name.');
$js->addCheckReqEntry('contact', 'Please enter the contact name.');
$js->addCheckReqMinCharEntry('password', 6, 'Please enter and confirm the password (6 to 20 chars., mixed case, numbers allowed).');
$js->addCheckReqEntry('city', 'Please enter the city.');
$js->addCheckReqEntry('province', 'Please select the province/state.');
$js->addCheckReqEntry('postalcode', 'Please enter the postal/ZIP code.');
$js->addCheckReqEntry('day_phone', 'Please enter the phone number.');
$js->addCheckReqEmailEntry('email');
$js->buildCheckFormFunc();
$js->buildPasswordIconFunc();
$js->buildJQueryAjaxCode("city", "selectoption", "province", "a Province", DB_TABLE_PREFIX."provinces", "id", "name", "id IN (SELECT prov_id FROM data_cities WHERE name LIKE \"%'+val+'%\" AND country_id = 1)", "", "name");
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Members List" => "members/list-members.php"));
showEditorButtons(DEF_EDITBUT_SAVE);
echo "</div>\n";

startEditorBlock("add_content");
showPageTitle("Create a New Member");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Company Name*", "company", $company);
showTextField("Contact Name*", "contact", $contact);
showMenu("Affiliate Promo Code", "affiliate_id", $affiliatelist, $affiliate_id, 1, false);
showTextField("Street Address", "streetname", $streetname);
showTextField("City*", "city", $city, 35);
showMenu("Province*", "province", $provlist, $province, 1, false);
showTextField("Postal Code*", "postalcode", $postalcode, 10);
showTextField("Phone*", "day_phone", $day_phone, 15);
showTextField("Toll-Free Phone", "toll_phone", $toll_phone, 15);
showTextField("Fax Number", "fax", $fax, 15);
showTextField("Email Address*", "email", $email);
showTextField("Website URL", "website", $website);
showRadioList("Is this Member Activated?", "activated", array("1", "0"), $activated, array("Yes", "No"));
showPasswordField("Password (6 to 20 chars.)*", "password*", $password, 20, 20);
showPasswordField("Confirm Password*", "cpassword", $cpassword, 20, 20);
endPageForm();
//showImgEditBox("Image", "image");
showFileUploaderScript(IMG_UPLOAD_FOLDER."imgedit/temp/", 0, 100);
attachFileUploader(array('image'), array($image), array(IMAGE_TYPES));
showFooter();
?>