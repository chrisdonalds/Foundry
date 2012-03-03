<?php

//  ------------------------------------------------------------------------------------
//  EDIT
//  ------------------------------------------------------------------------------------

$incl = "filehandler";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."subscribers";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$firstname = getQuotedRequestVar('firstname');
	$lastname = getQuotedRequestVar('lastname');
	$newsletter = getRequestVar('newsletter');
	$email = getQuotedRequestVar('email');
	$lastID = getLastID($db->table, "id");

	if (!errorMsgExists()) {
		// SQL fields to be updated.
		// end list with ', '
		$sqlbase = "firstname = $firstname, lastname = $lastname, newsletter = $newsletter, email = $email, ";
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
}else{
    gotoPage("list-subscribers.php");
	exit;
}

// build arrays
$rec = getRec(DB_TABLE_PREFIX."newsletters", "*", "", "id", "");
$newsletters_array = array("" => "- Please select a newsletter -") + flattenDBArray($rec, "id", "itemtitle");

// build javascript block
$js = new JSBlock();
$js->subject = "subscriber";
$js->section = "newsletters";
$js->addCheckReqEntry('firstname', 'Please enter the first name.');
$js->addCheckReqEntry('lastname', 'Please enter the last name.');
$js->addCheckReqEntry('email', 'Please enter the email address.');
$js->addCheckReqEntry('newsletter', 'Please choose a newsletter.');
$js->buildCheckFormFunc();
$js->buildPasswordIconFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Subscribers List" => "newsletters/list-subscribers.php"));
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit Subscriber");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("First Name*", "firstname", $firstname);
showTextField("Last Name*", "lastname", $lastname);
showTextField("Email Address*", "email", $email);
showMenu("Newsletter*", "newsletter", $newsletters_array, $newsletter, 1, false);
endPageForm();
showStats();
showFooter();
?>
