<?php
// ------------------------------------------
//
// FOUNDRY FRONT INCLUSION LOADER & INITIATOR
//
// ------------------------------------------
//
//*****************************************************************************
//   MANDATORY INCLUSIONS
//*****************************************************************************

if(!defined("DB_USED")) define("DB_USED", true);
if(!defined("VHOST")) define("VHOST", "/");
define("IN_ADMIN", false);

include_once ($_SERVER['DOCUMENT_ROOT'].VHOST."admin/inc/_config/configs.php");
if(strpos(SITE_PATH, "/") === false) die("Config Loading Failure: Check path to configs.php in getinc.php");
include_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_core.php");
include_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_err.php");
include_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_db.php");
include_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_plugin.php");
include_once (SITE_PATH.CORE_FOLDER."common_form.php");									// required!!!
include_once (SITE_PATH.CORE_FOLDER."common_rpc.php");									// required!!!
if(!defined("CORELOADED") || !defined("DBLOADED") || !defined("FORMLOADED") || !defined("PLUGINLOADED") || !defined("ERRLOADED")) die("One of more Common_x file(s) are not started!");

//*****************************************************************************
//   DATABASE SETUP
//*****************************************************************************

if(DB_VER < 3.34){
	include_once (SITE_PATH.ADMIN_FOLDER.DB_FOLDER."db_mysql.php");
}else{
	include_once (SITE_PATH.ADMIN_FOLDER.DB_FOLDER."db_connection.class.php");
	include_once (SITE_PATH.ADMIN_FOLDER.DB_FOLDER."db_common.class.php");
	include_once (SITE_PATH.ADMIN_FOLDER.DB_FOLDER."db_wrapper.class.php");
	$db = new DB_wrapper();
}

//*****************************************************************************
//   SUPPLEMENTARY INCLUSIONS
//*****************************************************************************

include_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."getvars.php");
include_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."geterrormsgs.php");
include_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."getbrowser.php");

//*****************************************************************************
//   CUSTOM, DEVELOPER-PROVIDED INCLUSIONS
//*****************************************************************************

include_once (SITE_PATH.CUSTOM_FOLDER."common_custom.php");

//----------------------------- BASIC INIT ------------------------------------

//*****************************************************************************
//   SYSYEM PREPARATION
//*****************************************************************************

session_name('front');
session_start();
initErrorMsg();

//*****************************************************************************
//   USER-CLASS PREPARATION
//*****************************************************************************

$_users->isloggedin = getIfSet($_SESSION['admlogin']);
initAllowances();

//------------------------- ADVANCED INIT ----------------------------------

if(!defined("BASIC_GETINC")){

	//*****************************************************************************
	//   RESETS
	//*****************************************************************************

	prepareSessionElements();
	dequeueAllFunctions(array(TF_CONTENTMACRO, TF_LISTACTION));

	//*****************************************************************************
	//   INCLUSION-BASED PLUGINS DYNAMIC LOADER
	//*****************************************************************************

	initPluginsandFrameworks();

	//*****************************************************************************
	//   DATA ALIAS REGISTER
	//*****************************************************************************

    $_system->dataaliases = getDataAliases();
}
?>