<?php
// ---------------------------
//
// ADMIN MENU CONFIGS
//
// ---------------------------

if(!defined("VALID_LOAD")) die("This file cannot be accessed directly!");

//Record-keeping Settings
define ("ALLOW_ARCHIVE", true);		// 'archive' action
define ("ALLOW_DELETE", true);		// 'delete' action
define ("FULL_DELETE", true);		// delete record or set 'delete' field
define ("ALLOW_UNDELETE", true);	// 'undelete' action
define ("ALLOW_PUBLISH", true);		// 'publish' action and 'save & publish' option
define ("ALLOW_ACTIVATE", true);	// 'activate' action and 'save & activate' option
define ("ALLOW_DRAFT", true);		// 'save to draft' option
define ("ALLOW_UNPUB_SAVE", true);	// 'save' option displayed
define ("ALLOW_SORT", true);        // column sorting on list page
define ("ALLOW_SEARCH", true);      // search function on list page
define ("USE_SECTIONS", false);     // universal divisioning on/off
define ("ALLOW_ADDPAGE", true);     // shows 'add sub-page' on page list
define ("ALLOW_METAPAGE", true);    // shows 'edit meta-data' on page list

//Divisions/Tables
// -- uncomment if using sections
// -- remember to prepare the `sections` table with data
define("ROOT_ID", "sectionid");		// session root table id where root data is pulled
if(USE_SECTIONS){
	$rootvar = "root";					// session root variable name -- passed between webpages
	$roottable = "sections";			// session root table where root data is pulled
	$rootdir = "pages";					// page and folder which is displayed by default
	$rootlink = "?root=";
}else{
	$rootvar = "";						// session root variable name -- passed between webpages
	$roottable = "";					// session root table where root data is pulled
	$rootdir = "";						// page and folder which is displayed by default
	$rootlink = "";
}

// Main nav bar sections: "main database table" => "{prefix}Name"
// (prefix can be:
//      \p for page,
//      \c for category (adds '_cat' to url)
//      \d for developer only
//      \a= for aliased nav (ie. sect => \a=pagename|Title will go to sect/list-pagename.php rather than sect/list-sect.php)
$sections = array(		"pages"			=> "\dPages",
						"events"		=> "Events",
						"photos"		=> "\cPhoto Gallery",
                        "projects"      => "Projects",
						"whatsnew"		=> "What's New",
						);

// Sub nav bar sections: "main database table" => "table" or array("table" => "Name"...)
$subsections = array(	"photos" 		=> array ( "photos_cat" => "Galleries" ),
						);

// sub nav bar: "database table" => "main database table"
$altsections = array(   "pages"			=> "pages",
						"userpage"		=> "pages",
						"meta"			=> "pages",
						"products"		=> "products",
                        "projects"      => "projects",
						"events"		=> "events",
						"photos"		=> "photos",
						"photos_cat"	=> "photos",
						);

//Pages

define("PAGE_EDITOR", 1);
define("PAGE_DB", 2);
define("PAGE_FORM", 3);

?>