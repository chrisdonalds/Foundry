<?php

//  ------------------------------------------------------------------------------------
//  CREATE NEW PAGE
//  ------------------------------------------------------------------------------------

$incl = "lightbox filehandler";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."testimonials";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$client_name = getRequestVar('client_name');
	$client_company = getRequestVar('client_company');
	$testimonial = str_replace("&#34;", "\"", getRequestVar('testimonial'));
	$city = getRequestVar('city');
	$prov = intval(getRequestVar('prov'));
	$lastID = getLastID($db->table, "id");

	list($err, $saveimg) = uploadImage('image', 'lastimg', '', 'delimg', SITE_PATH.IMG_UPLOAD_FOLDER.$db->table."/", SITE_PATH.THM_UPLOAD_FOLDER.$db->table."/", array("jpg","jpeg","gif","png"));

	if (!errorMsgExists()) {
		// sql fields and data lists. end both with ', '
		$sqlfields = "client_name, client_company, testimonial, city, prov, image";
		$sqldata   = "'$client_name', '$client_company', '$testimonial', '$city', $prov, '$saveimg'";
    	switch($_page->savebuttonpressed) {
    		case "save":
    			$_page->row_id = insertRec($db->table, $sqlfields, $sqldata);
                if($_page->row_id > 0){
               		addErrorStatMsg(SUCCESS_CREATE);
                }else{
                	addErrorStatMsg(FAILURE_CREATE);
                }
    			break;
    		case "savedraft":
    			$_page->row_id = insertRec($db->table, $sqlfields, $sqldata);
                if($_page->row_id > 0){
               		addErrorStatMsg(SUCCESS_CREATE);
                }else{
                	addErrorStatMsg(FAILURE_CREATE);
                }
    			break;
    		case "savepub":
    			$_page->row_id = insertRec($db->table, $sqlfields.", published, date_published", $sqldata.", 1, NOW()");
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

$client_name = getRequestVar('client_name');
$client_company = getRequestVar('client_company');
$testimonial = getRequestVar('testimonial');
$city = getRequestVar('city');
$prov = getRequestVar('prov');
$image = getRequestVar('image');

// build javascript block
$js = new JSBlock();
$js->subject = "testimonial";
$js->section = "testimonials";
$js->addCheckReqEntry('client_name', 'Please provide the client`s name.');
$js->addCheckReqEntry('testimonial', 'Please enter the testimonial.');
$js->buildCheckFormFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Testimonials List" => "testimonials/list-testimonials.php"));
showEditorButtons(DEF_EDITBUT_PUB);
echo "</div>\n";

startEditorBlock("add_content");
showPageTitle("Create a Testimonials");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Client Name*", "client_name", $client_name);
showTextField("Client Company", "client_company", $client_company);
showTextareaField("Testimonial*", "testimonial", $testimonial);
endPageForm();
showFooter();
?>
