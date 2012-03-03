<?php

//  ------------------------------------------------------------------------------------
//  CREATE
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

	debugger();
	if (!errorMsgExists()) {
		// sql fields and data lists. end both with ', '
		$sqlfields = "firstname, lastname, newsletter, email";
		$sqldata   = "$firstname, $lastname, $newsletter, $email";
    	switch($_page->savebuttonpressed) {
    		case "save":
    			$_page->row_id = insertRec($db->table, $sqlfields, $sqldata);
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

$firstname = getRequestVar('firstname');
$lastname = getRequestVar('lastname');
$email = getRequestVar('email');

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
$js->buildCheckFormFunc();
$js->buildPasswordIconFunc();
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Subscribers List" => "newsletters/list-subscribers.php"));
showEditorButtons(DEF_EDITBUT_SAVE);
echo "</div>\n";

startEditorBlock("add_content");
showPageTitle("Create a New Subscriber");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("First Name*", "firstname", $firstname);
showTextField("Last Name*", "lastname", $lastname);
showTextField("Email Address*", "email", $email);
showMenu("Newsletter*", "newsletter", $newsletters_array, $newsletter, 1, false);
endPageForm();
showFooter();
?>