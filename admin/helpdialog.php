<?php
// ---------------------------
//
// ADMIN HELP DIALOG CONTENTS
//
// ---------------------------
//
define("BASIC_GETINC", true);
include("loader.php");

// load user-defined help content -- if prepared
$data = '';
$gz_data = getRequestVar('data');
if($gz_data != '') $data = gzinflate(urldecode($gz_data));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<?php
addHeadPlugin("jqueryui", array("widgets" => "tabs"));
addHeadPlugin("basic");
showHeadLines(true);
?>
</head>

<body>
	<div id="helptabs">
		<ul>
			<? if($data != '') { ?><li><a href="#help_instructions">Instructions</a></li><? } ?>
			<li><a href="#help_usage">Usage Help</a></li>
		</ul>
		<? if($data != ''){ ?>
		<div id="help_instructions">
			<? echo $data; ?>
		</div>
        <? } ?>
        <div id="help_usage">
			<ul class="helplist">
				<li>To start select one of the <strong>Groups</strong> from the <span style="background-color: #0000aa; color: #fff;">Dark Blue</span> Navigation Bar above.</li>
				<li>If this installation of <?=SYS_NAME ?> includes a <strong>Sections Menu</strong>, you can switch between any of the listed Sections.  Sections are listed on the <span style="background-color: #6A6AFF; color: #000">Light Blue</span> bar.</li>
				<li>The <strong>Search</strong> tool allows you to find records or entries listed on this page.</li>
				<li>The <strong>Sort</strong> tool allows you to change the sort order of the selected column.  This is also accomplished by clicking on the up/down arrow on the column titlebar.</li>
				<li><u>Edit</u>: Modifies the referenced data.</li>
				<li><u>View</u>: Displays contained data.</li>
				<li><u>Delete</u>/<u>Un-delete</u>: Deletes the record from the system.  (Note: The 'Un-delete' action will be available if the 'FULL_DELETE' setting is not set.  Otherwise, deletions are permanent.)</li>
				<li><u>Publish</u>/<u>Un-publish</u>: Publishing a record makes it visible to the public.  This allows you to prepare an entry before presenting it to the public.</li>
				<li><u>Archive</u>/<u>Un-arc</u>: Archiving a record removes the ability for anyone to edit, delete or publish it.  Archived entries are not seen on the public site.</li>
				<li><u>Activate</u>/<u>Deactivate</u>: Activation is a common action for registration-type entries.  It is similar to Publishing.</li>
				<li><u>Subscribe</u>/<u>Un-subscribe</u>: Subscribing finalizes a user registration and exposes their data to mailing lists, etc.</li>
				<li><u>View List</u>: Click to display subordinate records pertaining to the selected item.</li>
				<li><u>View Sub-Pages</u>: Click to display a list of pages that pertain to the selected item.</li>
				<li><u>Send Now</u>: Click to send a confirmation email to the chosen respondent.</li>
			</ul>
        </div>
        </div>
	</div>
</body>
</html>