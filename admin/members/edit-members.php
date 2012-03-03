<?php

//  ------------------------------------------------------------------------------------
//  EDIT
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
	if($npassword != "") {
		$npassword = md5(getRequestVar('password'));
	}else{
		$npassword = getRecItem($db->table, "password", "id = '{$_page->row_id}'");		// resubmit old password
	}
	$lastID = getLastID($db->table, "id");
	if(getRecItem($db->table, "id", "email = '$email' AND id != '{$_page->row_id}'") != "" && $email != ''){
		addErrorMsg(sprintf(DUPLICATE_RECORD, "The email `".strtoupper($email)."`", "", "email"));
	}

    // get current activation state (if status has been changed in this session, an email will be sent to the user)
    // this prevents an email sent each time the record is successfully saved
    $prevactivestate = intval(getRecItem($db->table, "activated", "id = '{$_page->row_id}'"));

	if (!errorMsgExists()) {
		// SQL fields to be updated.
		// end list with ', '
		$sqlbase = "contact = '$contact', company = '$company', password = '$npassword', streetname = '$streetname', city = '$city', province = $province, country = $country, postalcode = '$postalcode', day_phone = '$day_phone', toll_phone = '$toll_phone', fax = '$fax', email = '$email', website = '$website', activated = $activated, affiliate_id = $affiliate_id, ";
    	switch($_page->savebuttonpressed) {
    		case "save":
                if(updateRec($db->table, $sqlbase."date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    		case "saveact":
                if(updateRec($db->table, $sqlbase."published = 1, date_activated = NOW(), date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    	}
	}
    if(getErrorStatMsg(SUCCESS_EDIT) && ($prevactivestate != $activated || ($prevbannedstate != $banned && $banned = 1))){
        // email, activated, firstname, lastname
		//include(SITE_PATH.PLUGINS_FOLDER."mail/senduseractive-mailer.php");
    }
}

// build query

$recset = getRec($db->table, "*", "id = '{$_page->row_id}'", "", "");
if(count($recset) == 1) {
    extractVariables($recset[0]);
}else{
    gotoPage("list-members.php");
	exit;
}

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
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit Member");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Company Name*", "company", $company);
showLabel("Service Listings", "", "You can view this Member's Service Listings <a href=\"".WEB_URL.ADMIN_FOLDER."members/list-listings.php?member_id={$_page->row_id}\">here</a>.");
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
showPasswordField("Password (6 to 20 chars.)*", "password*", "", 20, 20);
showPasswordField("Confirm Password*", "cpassword", "", 20, 20);
endPageForm();
showStats();
showFooter();
?>
