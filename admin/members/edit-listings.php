<?php

//  ------------------------------------------------------------------------------------
//  EDIT
//  ------------------------------------------------------------------------------------

$incl = "fileuploader filehandler autocomplete(city)";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."listings";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$cat_id = getRequestVar('category');
	$itemtitle = clean_text(getRequestVar('itemtitle'));
	$code = codify($itemtitle);
	$description = clean_text(getRequestVar('description'));
	$address = ucwords_smart(clean_text(getRequestVar('address')));
	$city = clean_text(getRequestVar('city'));
	$prov_id = intval(getRequestVar('province'));
	$city_id = intval(getRecItem(DB_TABLE_PREFIX."cities", "id", "name = '$city' AND prov_id = '$prov_id'"));
	$postalcode = clean_text(getRequestVar('postalcode'));
	$phone = clean_text(getRequestVar('phone'));
	$fax = clean_text(getRequestVar('fax'));
	$email = clean_text(getRequestVar('email'));
	$website = clean_text(strtolower(getRequestVar('website')));
	if($website == 'http://' || $website == 'https://' || $website == 'http') $website = '';
	if(substr($website, 0, 7) != 'http://' && substr($website, 0, 8) != 'https://') $website = 'http://'.$website;
	$showmap = ((getRequestVar('showmap') == '1') ? 1 : 0);
	$lastID = getLastID($db->table, "id");

	$image = getRequestVar('image_fld');
	$image_mod = getRequestVar('image_mod');
	if($image != "" && $image_mod != ""){
		list($saveimg, $savethm) = FU_MoveTempFile($image_mod, $image, IMG_UPLOAD_FOLDER.$db->table, THM_UPLOAD_FOLDER.$db->table, true, false);
	}elseif($image_mod != 'deleted'){
		$saveimg = getRequestVar('lastimg');
		$savethm = getRequestVar('lastthm');
	}

	if (!errorMsgExists()) {
		// SQL fields to be updated.
		// end list with ', '
		$sqlbase = "code = '$code', member_id = $member_id, cat_id = $cat_id, itemtitle = '$itemtitle', description = '$description', address = '$address', city_id = $city_id, postalcode = '$postalcode', phone = '$phone', fax = '$fax', email = '$email', website = '$website', image = '$saveimg', thumb = '$savethm', showmap = $showmap, ";
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
}

// build query

$recset = getRec($db->table, "*", "id = '{$_page->row_id}'", "", "");
if(count($recset) == 1) {
    extractVariables($recset[0]);
	$city = getRecItem(DB_TABLE_PREFIX."cities", "name", "id=$city_id");
}else{
    gotoPage("list-listings.php");
	exit;
}

// build arrays
$provlist = flattenDBArray(getRec(DB_TABLE_PREFIX."provinces", "id, name", "country_id = ".$canada_id, "name", ""), "id", "name");
$provlist = array("0"=>"-- Select a Province --") + $provlist;
$prov_id = intval(getRecItem(DB_TABLE_PREFIX."provinces", "id", "id = (SELECT prov_id FROM data_cities WHERE id = '{$city_id}')"));
$catlist = flattenDBArray(getRec(DB_TABLE_PREFIX."categories", "id, name", "id IN (SELECT cat_id FROM data_cities_categories WHERE city_id = '{$city_id}')", "", ""), "id", "name");
$catlist = array("0"=>"-- Select a Category --") + $catlist;

// build javascript block
$js = new JSBlock();
$js->subject = "listing";
$js->section = "listings";
$js->addCheckReqEntry('itemtitle', 'Please enter the listing title.');
$js->addCheckReqEntry('city', 'Please enter the city.');
$js->addCheckReqEntry('province', 'Please select the province/state.');
$js->addCheckReqEntry('postalcode', 'Please enter the postal/ZIP code.');
$js->addCheckReqEntry('category', 'Please select the category.');
$js->buildCheckFormFunc();
$js->buildPasswordIconFunc();
$js->buildJQueryAjaxCode("city", "selectoption", "province", "a Province", DB_TABLE_PREFIX."provinces", "id", "name", "id IN (SELECT prov_id FROM data_cities WHERE name LIKE \"%'+val+'%\" AND country_id = 1)", "", "name");
$js->buildJQueryAjaxCode("city", "selectmenu", "category", "a Category", DB_TABLE_PREFIX."categories", "id", "name", "id IN (SELECT cat_id FROM data_cities_categories WHERE city_id = (SELECT id FROM data_cities WHERE name LIKE \"%'+val+'%\" AND country_id = 1))", "", "name");
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Listings List" => "members/list-listings.php?member_id=".$member_id));
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit Listing");
showReqdText();

startPageForm("edit_form", "", "POST", true, array("member_id" => $member_id));
showTextField("Listing Title*", "itemtitle", $itemtitle);
showImageField("Listing Logo", array("image", "lastimg", "lastthm", "delimg"), array($image, $thumb), array(false, false), $db->table);
showHTMLEditorField("Description", "description", $description);
showTextField("Street Address", "address", $address);
showTextField("City*", "city", $city, 35);
showMenu("Province*", "province", $provlist, $prov_id, 1, false);
showMenu("Category*", "category", $catlist, $cat_id, 1, false);
showTextField("Postal Code*", "postalcode", $postalcode, 10);
showTextField("Phone*", "phone", $phone, 15);
showTextField("Fax Number", "fax", $fax, 15);
showTextField("Email Address*", "email", $email);
showTextField("Website URL", "website", $website);
showRadioList("Display the Google Map?", "showmap", array("1", "0"), $showmap, array("Yes", "No"));
endPageForm();
showStats();
showFileUploaderScript(IMG_UPLOAD_FOLDER."imgedit/temp/", 0, 100);
attachFileUploader(array('image'), array($image), array(IMAGE_TYPES));
showFooter();
?>
