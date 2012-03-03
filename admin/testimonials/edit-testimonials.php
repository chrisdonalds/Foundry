<?php

//  ------------------------------------------------------------------------------------
//  EDIT
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

	list($err, $saveimg, $savethm) = uploadImage('image', 'lastimg', '', 'delimg', SITE_PATH.IMG_UPLOAD_FOLDER.$db->table."/", SITE_PATH.THM_UPLOAD_FOLDER.$db->table."/", array("jpg","jpeg","gif","png"));

	if (!errorMsgExists()) {
		// SQL fields to be updated.
		// end list with ', '
		$sqlbase = "client_name = '$client_name', client_company = '$client_company', testimonial = '$testimonial', city = '$city', prov = $prov, image = '$saveimg', ";
    	switch($_page->savebuttonpressed) {
    		case "save":
                if(updateRec($db->table, $sqlbase."date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    		case "savedraft":
                if(updateRec($db->table, $sqlbase."draft = 1, date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    		case "savepub":
                if(updateRec($db->table, $sqlbase."draft = 0, published = 1, date_published = NOW(), date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    		case "savearc":
                if(updateRec($db->table, $sqlbase."archived = 1, date_updated = NOW()", "id = '{$_page->row_id}'")){
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
}else{
    gotoPage("list-testimonials.php");
	exit;
}

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
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit Testimonial");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Client Name*", "client_name", $client_name);
showTextField("Client Company", "client_company", $client_company);
showTextareaField("Testimonial*", "testimonial", $testimonial);
endPageForm();
showStats();
showFooter();
?>
