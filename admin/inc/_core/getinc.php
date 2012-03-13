<?php
// ------------------------------------------
//
// FOUNDRY ADMIN INCLUSION LOADER & INITIATOR
//
// ------------------------------------------
//
//*****************************************************************************
//   MANDATORY ADMIN INCLUSIONS
//*****************************************************************************

if(!defined("DB_USED")) define("DB_USED", true);
if(!defined("VHOST")) define("VHOST", "/");
define("IN_ADMIN", true);

include_once ($_SERVER['DOCUMENT_ROOT'].VHOST."admin/inc/_config/configs.php");
if(strpos(SITE_PATH, "/") === false) die("Config Loading Failure: Check path to configs.php in getinc.php");
require_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_core.php");
require_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_err.php");
require_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_db.php");
require_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_plugin.php");
require_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_form.php");
if(!defined("CORELOADED") || !defined("DBLOADED") || !defined("FORMLOADED") || !defined("PLUGINLOADED") || !defined("ERRLOADED")) die("One of more common_x file(s) are not started!");

//*****************************************************************************
//   DATABASE SETUP
//*****************************************************************************

if(DB_VER < 3.34){
	require_once (SITE_PATH.ADMIN_FOLDER.DB_FOLDER."db_mysql.php");
}else{
	require_once (SITE_PATH.ADMIN_FOLDER.DB_FOLDER."db_connection.class.php");
	require_once (SITE_PATH.ADMIN_FOLDER.DB_FOLDER."db_common.class.php");
	require_once (SITE_PATH.ADMIN_FOLDER.DB_FOLDER."db_wrapper.class.php");
	$db = new DB_wrapper();
}

//*****************************************************************************
//   SUPPLEMENTARY INCLUSIONS
//*****************************************************************************

require_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."getvars.php");
require_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."geterrormsgs.php");
include_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."getbrowser.php");

//*****************************************************************************
//   CUSTOM, DEVELOPER-PROVIDED INCLUSIONS
//*****************************************************************************

include_once (SITE_PATH.ADMIN_FOLDER.CUSTOM_FOLDER."common_custom.php");

//----------------------------- BASIC INIT ------------------------------------

//*****************************************************************************
//   SYSTEM PREPARATION
//*****************************************************************************

session_name('admin');
session_start();
initErrorMsg();

$_SESSION['prevpage'] = getIfSet($_SESSION['curpage']);
$_SESSION['curpage'] = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$info = array("referer" => $_SESSION['prevpage'], "curpage" => $_SESSION['curpage'], "prevpage" => $_SESSION['prevpage']);
$_system->info = $info;
$_system->datatables = getDataTables();

//*****************************************************************************
//   USER-CLASS PREPARATION
//*****************************************************************************

$_users->isloggedin = getIfSet($_SESSION['admlogin']);
$_users->id = getIfSet($_SESSION['userdata']['id']);
$_users->level = getIfSet($_SESSION['userdata']['level']);
$_users->logintimestamp = getIfSet($_SESSION['timestamp']);
$_users->username = getIfSet($_SESSION['userdata']['username']);
$_users->firstname = getIfSet($_SESSION['userdata']['firstname']);
$_users->lastname = getIfSet($_SESSION['userdata']['lastname']);
$_users->email = getIfSet($_SESSION['userdata']['email']);
$_users->twitter = getIfSet($_SESSION['userdata']['twitter_link']);
$_users->googleplus = getIfSet($_SESSION['userdata']['google_plus_link']);
$_users->facebook = getIfSet($_SESSION['userdata']['facebook_link']);
$_users->activelist = getRec("admin_accts", "*", "activated=1 AND blocked=0", "username", "");
initAllowances();

//*****************************************************************************
//   GENERAL PERSISTENCE/PAGE-CLASS PREPARATION
//*****************************************************************************

$_page->uri = $_SERVER['REQUEST_URI'];		// uri is used by SESSION to limit values to specific URL
$_page->sectionid = intval(getIfSet($_SESSION['rootid']));
$_page->row_id = intval(getRequestVar('row_id'));
$_page->nonce = createNonce();
$_page->titlefld = 'itemtitle';
$_page->imagefld = 'image';
$_page->thumbfld = 'thumb';

if(isset($_REQUEST['page'])) $_SESSION[$_page->uri]['page'] = getRequestVar('page');
if(isset($_SESSION[$_page->uri]['page'])){
	$_page->pagenum = (($_SESSION[$_page->uri]['page'] == 0) ? 1 : $_SESSION[$_page->uri]['page']);
}else{
	$_page->pagenum = 1;
}
$_page->limit = LIST_ROWLIMIT;
$_page->offset = ($_page->pagenum - 1) * LIST_ROWLIMIT;
$_page->savebuttonpressed = (getRequestVar('_savebuttonpressed') != '');

//*****************************************************************************
//   LIST SEARCH PERSISTENCE
//*****************************************************************************

if(isset($_REQUEST['search_text'])) $_SESSION[$_page->uri]['search_text'] = getRequestVar('search_text');
if(isset($_REQUEST['search_by'])) $_SESSION[$_page->uri]['search_by'] = getRequestVar('search_by');
if(isset($_REQUEST['sort_by'])) $_SESSION[$_page->uri]['sort_by'] = getRequestVar('sort_by');
if(isset($_REQUEST['sort_dir'])) $_SESSION[$_page->uri]['sort_dir'] = getRequestVar('sort_dir');
$_page->search_text = getIfSet($_SESSION[$_page->uri]['search_text']);
$_page->search_by = getIfSet($_SESSION[$_page->uri]['search_by']);
$_page->sort_by = getIfSet($_SESSION[$_page->uri]['sort_by']);
$_page->sort_dir = getIfSet($_SESSION[$_page->uri]['sort_dir']);
if($_page->search_by == '' || is_null($_page->search_by)) $_page->search_by = 'all';

//*****************************************************************************
//   DATA ALIAS REGISTER
//*****************************************************************************

$_system->dataaliases = getDataAliases();

//------------------------- ADVANCED INIT ----------------------------------

if(!defined("BASIC_GETINC")){

	//*****************************************************************************
	//   RESETS
	//*****************************************************************************

	purgeLoginSessions(0, 1, 0, 0);
	prepareSessionElements();
	dequeueAllFunctions(array(TF_CONTENTMACRO, TF_LISTACTION));

	//*****************************************************************************
	//   INCLUSION-BASED PLUGINS DYNAMIC LOADER
	//*****************************************************************************

	getInstalledPlugins();
	initPluginsandFrameworks();

}else{

	initPluginsandFrameworks();
}

?>