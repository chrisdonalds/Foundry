<?php
// ---------------------------
//
// DATABASE CONFIGS AND OPS
//
// ---------------------------

if(!defined("VALID_LOAD")) die("This file cannot be accessed directly!");

/* REQUIRES CONFIGS.PHP TO BE CALLED FIRST */

define ('DB_ERR_INIT', 0);
define ('DB_ERR_NOTREG', 1);
define ('DB_ERR_SVRCONN', 2);
define ('DB_ERR_LOAD', 3);
define ('DB_ACT_CONNECT', 100);
define ('DB_ACT_BACKREST', 101);

// setup db
if(DB_USED){
    initDB();
	$link = @mysql_connect(DBHOSTPORT, DBUSER, DBPASS) || die(mysql_error);
    if(!$link) configDB(DB_ERR_SVRCONN);
    checkServerVersions('MySQL');

	define("DB_VER", floatval(mysql_get_server_info()));
}else{
	define("DB_VER", 999);
}

if(!defined('LIVE')) define ("LIVE", false);
if(!defined('BASICDB') && DB_USED) checkDB();

/*******************************************************************************/

/**
 * Connect and start the database controller
 * From this point, Foundry will attempt to connect to and build missing required
 * tables and data structures or redirect you to the Database Manager
 */
function initDB(){
    // looks in db.ini file for either the server host name or,
    // if host includes an extension, the extension
    // if not found or if db.ini is missing, configDB interface will be called
    include_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."common_db.php");
    $host = str_replace('www.', '', $_SERVER['HTTP_HOST']);
    $hostext = substr($_SERVER['HTTP_HOST'], -3);
    $dbset = readDBINI();

    if(count($dbset) == 0){
        configDB(DB_ERR_INIT);
    }else{
        if(isset($dbset[$host])){
            // continue loading site
            if(!isset($dbset[$host]['dbport']) || $dbset[$host]['dbport'] == 0) $dbset[$host]['dbport'] = 3306;
            foreach($dbset[$host] as $key => $val) define(strtoupper($key), $val);
            define ("DBHOSTPORT", ((DBPORT != 3306 && DBPORT > 0) ? DBHOST.":".DBPORT : DBHOST));
            if(DBHOST != '' && DBNAME != '' && DBUSER != ''){
                return;
            }else{
                configDB(DB_ERR_INIT);
            }
        }elseif(isset($dbset[$hostext])){
            // continue loading site
            if(!isset($dbset[$host]['dbport']) || $dbset[$host]['dbport'] == 0) $dbset[$host]['dbport'] = 3306;
        	foreach($dbset[$hostext] as $key => $val) define(strtoupper($key), $val);
            define ("DBHOSTPORT", ((DBPORT != 3306) ? DBHOST.":".DBPORT : DBHOST));
            if(DBHOST != '' && DBNAME != '' && DBUSER != ''){
                return;
            }else{
                configDB(DB_ERR_INIT);
            }
        }elseif(isset($dbset['SYS_GO'])){
            configDB(DB_ERR_NOTREG);
        }else{
            configDB(DB_ERR_INIT);
        }
    }
}

/**
 * Builds Database Manager page, triggered by:
 *  - DB initialization call (first run)
 *  - DB connection problem (from anywhere DB is accessed)
 *  - DB connection alteration call (from Settings)
 *  - DB restoration call (from Settings)
 *  - DB backup call (from Settings)
 * @param integer $reason
 * @param array $dbset
 */
function configDB($reason = null, $dbset = null){
    include_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."getvars.php");
    include_once (SITE_PATH.ADMIN_FOLDER.CORE_FOLDER."geterrormsgs.php");
    $const = get_defined_constants();
    $host = str_replace('www.', '', $_SERVER['HTTP_HOST']);
    date_default_timezone_set('America/Vancouver');

    if(is_null($dbset)){
        $dbset = array( 'db_host' => getIfSet($const['DBHOST']),
                        'db_name' => getIfSet($const['DBNAME']),
                        'db_user' => getIfSet($const['DBUSER']),
                        'db_pass' => getIfSet($const['DBPASS']),
                        'db_port' => getIfSet($const['DBPORT']),
                        'googlemap_key' => getIfSet($const['GOOGLEMAP_KEY']));
    };

    $jumpto = (($reason >= DB_ACT_CONNECT) ? 'jQuery("#cfgdiv").tabs("select", '.($reason - DB_ACT_CONNECT).');' : '');
    $cfgothertabs = (($reason >= DB_ACT_CONNECT) ? '<li><a href="#db_backrest">Backup/Restore</a></li>' : '');

    echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title>Admin: Database Configurator</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="content-language" content="EN" />
    <meta http-equiv="Pragma" content="no-cache"/>
    <meta http-equiv="Expires" content="-1"/>
    <script type="text/javascript" language="javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script type="text/javascript" language="javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.js"></script>
    <link href="{$const['WEB_URL']}{$const['ADMIN_FOLDER']}{$const['JS_FOLDER']}ui/ui.theme.css" rel="stylesheet" type="text/css" media="screen" />
    <script type="text/javascript" language="javascript" src="{$const['WEB_URL']}{$const['ADMIN_FOLDER']}{$const['JS_FOLDER']}ui/ui.script.js"></script>
    <script type="text/javascript" language="javascript" src="{$const['WEB_URL']}{$const['ADMIN_FOLDER']}{$const['JS_FOLDER']}ui/ui.dialog.js"></script>
    <link href="{$const['WEB_URL']}{$const['ADMIN_FOLDER']}{$const['ADM_CSS_FOLDER']}{$const['DEF_ADM_SKIN']}master.css" rel="stylesheet" type="text/css" media="screen" />
</head>

<body>
    <script type="text/javascript" language="javascript">
        jQuery(document).ready(function($){
            jQuery("#db_conn_form").submit(function(){
                var d = '{$host}';
                var h = jQuery("#db_host").val();
                var u = jQuery("#db_user").val();
                var p = jQuery("#db_pass").val();
                var n = jQuery("#db_name").val();
                var r = jQuery("#db_port").val();
                var g = jQuery("#googlemap_key").val();
                jQuery.post("{$const['VHOST']}{$const['ADMIN_FOLDER']}{$const['CORE_FOLDER']}ajaxwrapper.php",
                    { op: 'trytoaccessdb', db_used: 0, d: d, h: h, u: u, p: p, n: n, r: r, g: g },
                    function(data) {
                        if(data.success){
                            alert(data.rtndata);
                            var lastpage = jQuery("#referer").val();
                            if(lastpage == '' || lastpage == 'undefined') {
                                location.reload();
                            }else{
                                window.location = lastpage;
                            }
                        }else{
                            alert(data.rtndata);
                        }
                    }, "json");
                return false;
            });
            {$jumpto}
        });
    </script>

    <div id="wrapper">
        <div id="header" style="background-color: #ccccff">
            <h1>{$const['SYS_NAME']} Database Manager - Admin</h1>
        </div>

        <div id="content-wrapper">
            <div id="contentarea">
                <div id="cfgdiv">
                    <ul>
                        <li><a href="#db_connect">Database Connection</a></li>
                        {$cfgothertabs}
                    </ul>
EOT;

    // config form
    echo PHP_EOL;
    echo <<<EOT
                    <div id="db_connect">
                        <h3>Database Connection</h3>
                        <form action="javascript: void(0);" method="post" id="db_conn_form">
EOT;

    echo PHP_EOL;
    if($reason == DB_ERR_INIT){
        echo "              <p>Welcome to {$const['SYS_NAME']} Admin.</p>\n";
        echo "              <p>In a moment your website will be ready for use.  However, first you will need to provide some information in order to setup a database for use with this website.</p>\n";
        echo "              <input type=\"hidden\" id=\"referer\" value=\"".WEB_URL.ADMIN_FOLDER."\"/>\n";
    }elseif($reason < DB_ACT_CONNECT){
        echo "              <p>You have been redirected to this page because a database has not been properly registered for use with this website.  Perhaps one or more of the connection settings need updating.</p>\n";
        echo "              <input type=\"hidden\" id=\"referer\" value=\"".$_SERVER['PHP_SELF']."\"/>\n";
    }else{
        echo "              <p>The Database Manager Connection area allows for easy modification of database connection parameters.</p>\n";
        echo "              <input type=\"hidden\" id=\"referer\" value=\"".$_SERVER['HTTP_REFERER']."\"/>\n";
    }

    echo <<<EOT
                        <p>If you don't have the following information ready, you can obtain it from your web host.</p>
                        <p class="cfgblock">
                            <span class="cfglabel">Domain</span><span class="cfgdata"><input type="text" name="db_host" id="db_host" value="{$_SERVER['HTTP_HOST']}" readonly="readonly" style="background-color: #ddd"/></span><span class="cfgnote"></span><br/>
                            <span class="cfglabel">Database Host</span><span class="cfgdata"><input type="text" name="db_host" id="db_host" value="{$dbset['db_host']}"/></span><span class="cfgnote">Try <b>localhost</b>.  Otherwise you will need to contact your provider.</span><br/>
                            <span class="cfglabel">Database Name</span><span class="cfgdata"><input type="text" name="db_name" id="db_name" value="{$dbset['db_name']}"/></span><span class="cfgnote">The name of the database you want to use with this site.</span><br/>
                            <span class="cfglabel">Username</span><span class="cfgdata"><input type="text" name="db_user" id="db_user" value="{$dbset['db_user']}"/></span><span class="cfgnote">The username which grants access to this database.</span><br/>
                            <span class="cfglabel">Password</span><span class="cfgdata"><input type="text" name="db_pass" id="db_pass" value="{$dbset['db_pass']}"/></span><span class="cfgnote">The password for that username.</span><br/>
                            <span class="cfglabel">Port</span><span class="cfgdata"><input type="text" name="db_port" id="db_port" value="{$dbset['db_port']}"/></span><span class="cfgnote">Typically set to <b>3306</b>.</span>
                        </p>
                        <p class="cfgblock">
                            <span class="cfglabel">Google Map Key</span><span class="cfgdata"><input type="text" name="googlemap_key" id="googlemap_key" value="{$dbset['googlemap_key']}" size="100"/></span><span class="cfgnote"><a href="http://code.google.com/apis/maps/signup.html" target="_blank">Get a new key now</a> (required to use Google Map plugin).</span>
                        </p>
                        <p class="cfgblock">
                            When ready, ensure that the <strong>db.ini</strong> file is writable (CHMOD 777), and...
                        </p>
                        <p class="cfgblock">
                            <span class="cfgdata"><input type="submit" name="db_submit" id="db_submit" value="Save Connection Settings"/></br>
                        </p>
                    </form>
                </div>
EOT;

    if($reason >= DB_ACT_CONNECT){
        // backup/restore form
        echo PHP_EOL;
   		$rs = mysql_query("SHOW TABLES");
        $tables = array();
		$ignored_tables = array("cache", "page_types", "session_login", "plugins");
		while($row = mysql_fetch_array($rs)){
			$key = array_search($row[0], $ignored_tables);
            if($key === false) $tables[] = $row[0];
        }

        echo <<<EOT
                <div id="db_backrest">
                    <h3>Data Backup</h3>
                    <form action="javascript: void(0);" method="post" id="db_back_form">
EOT;
        printr($tables);
        echo <<<EOT
                    </form>
                    <p>&nbsp;</p>
                    <h3>Data Restore</h3>
                    <form action="javascript: void(0);" method="post" id="db_rest_form">
                    </form>
                </div>
EOT;
    }

    echo PHP_EOL;
    echo "  </div>\n";

    include (SITE_PATH.ADMIN_FOLDER."footer.php");
    exit;
}

/**
 * Verifies existence of critical database structures
 */
function checkDB(){
	$link = mysql_connect(DBHOSTPORT, DBUSER, DBPASS);
    if(!$link) configDB(DB_ERR_SVRCONN);
	if(!defined("DB_VER")) define("DB_VER", floatval(mysql_get_server_info()));

	// locate basic required tables (settings, sections, admin_accts, pages, page_types, session_login)
	if($link) {
		$db0 = mysql_select_db(DBNAME);
        if(!$db0) configDB(DB_ERR_LOAD);

		// fix any errant or missing columns in available required tables
		$tables = mysql_query("SHOW TABLES");
		//repairTableColumns($tables);

		// create any missing required tables with defaults
		$reqd_tables = array("settings", "sections", "admin_accts", "pages", "page_types", "editor_userpages", "session_login", "plugins");

		while($row = mysql_fetch_array($tables)){
			$key = array_search($row[0], $reqd_tables);
			if($key !== false) {
				unset($reqd_tables[$key]);
			}
		}

		foreach($reqd_tables as $table){
			switch($table){
				case "settings":
					$sql = "CREATE TABLE `settings` (
					  `name` varchar(50) NOT NULL default '',
					  `value` text default NULL,
					  `type` char(3) default 'str',
					  PRIMARY KEY (`name`)
					)";

					$sql2 = "INSERT INTO `settings` (`name`, `value`, `type`) VALUES
					('DEF_METAKEYWORDS', 'Default Keywords', 'str'),
					('DEF_METADESCRIPTION', 'Default Description', 'str'),
					('DEF_METATITLE', 'Default Title', 'str'),
					('BUSINESS', 'Business', 'str'),
					('SITE_NAME', 'Site Name', 'str'),
					('OWNER_EMAIL', 'chrisd@navigatormm.com', 'str'),
					('ADMIN_EMAIL', 'chrisd@navigatormm.com', 'str'),
					('BUS_ADDRESS', '', 'str'),
					('BUS_PHONE', '', 'str'),
					('BUS_FAX', '', 'str'),
                    ('TIMEZONE', 'America/Vancouver', 'str'),
					('IMG_MAX_WIDTH', '800', 'int'),
					('IMG_MAX_HEIGHT', '600', 'int'),
					('IMG_MAX_UPLOAD_SIZE', '6000', 'str'),
                    ('IMG_UPLOAD_FOLDER', 'images/', 'str'),
					('MAX_IFRAME_IMGS', '6', 'int'),
					('THM_MAX_WIDTH', '100', 'int'),
					('THM_MAX_HEIGHT', '100', 'int'),
					('THM_MED_MAX_WIDTH', '200', 'int'),
					('THM_MED_MAX_HEIGHT', '200', 'int'),
                    ('THM_UPLOAD_FOLDER', 'thumbs/', 'str'),
                    ('FILES_UPLOAD_FOLDER', 'files/', 'str'),
					('ACTION_ICONS', '0', 'int'),
					('THM_MAX_UPLOAD_SIZE', '50', 'str'),
					('ORG_THM_MAX_WIDTH', '100', 'int'),
					('ORG_THM_MAX_HEIGHT', '100', 'int'),
					('CKE_CSS_COLORS', '', 'upd'),
					('EMAIL_CONFIRM', null, 'str'),
					('EMAIL_NOTIFY', null, 'str'),
					('IMG_LOGIN_LOGO', '', 'str'),
					('THEME', 'default', 'str'),
					('THEMES_ENABLED', '0', 'int')";
					break;
				case "sections":
					$sql = "CREATE TABLE `sections` (
					  `id` int(11) unsigned NOT NULL auto_increment,
					  `code` varchar(50) NOT NULL,
					  `alias` varchar(50) default NULL,
					  `name` varchar(50) NOT NULL,
					  `description` text NOT NULL,
					  `image` varchar(255) default NULL,
					  `display` tinyint(1) NOT NULL default '1',
					  `lat` float(20,10) default NULL,
					  `lon` float(20,10) default NULL,
					  `weatherurl` varchar(255) default NULL,
					  `citycode` varchar(10) default NULL,
					  `area` varchar(50) default NULL,
					  PRIMARY KEY  (`id`)
					)".((DB_VER > 3.99) ? " ENGINE=MyISAM DEFAULT CHARSET=latin1" : "");
					break;
				case "admin_accts":
					$sql = "CREATE TABLE `admin_accts` (
					  `id` bigint(11) unsigned NOT NULL auto_increment,
					  `username` varchar(50) default NULL,
					  `password` varchar(255) default NULL,
					  `phash` varchar(255) default NULL,
					  `pcle` varchar(255) default NULL,
					  `level` int(2) default '0',
					  `email` varchar(255) default NULL,
					  `hint` varchar(255) default NULL,
					  `firstname` varchar(20) default NULL,
					  `lastname` varchar(20) default NULL,
					  `twitter_link` varchar(255) default NULL,
					  `google_plus_link` varchar(255) default NULL,
					  `facebook_link` varchar(255) default NULL,
					  `image` varchar(255) default NULL,
					  `thumb` varchar(255) default NULL,
					  `avatar` varchar(255) default NULL,
					  `activated` tinyint(1) default '1',
					  `blocked` tinyint(1) default '0',
					  `blocked_time` datetime default '0000-00-00 00:00:00',
					  PRIMARY KEY  (`id`)
					)".((DB_VER > 3.99) ? " ENGINE=MyISAM DEFAULT CHARSET=latin1" : "");

					$sql2 = "INSERT INTO `admin_accts` (`username`, `password`, `phash`, `pcle`, `email`, `hint`, `activated`, `blocked`, `blocked_time`, `level`, ) VALUES
					('admin', '5f4dcc3b5aa765d61d8327deb882cf99', 'd4617373776f7264:2cd911c6b4f2098a5c72ce9feaf2d075', '5336f4d3c830c63d4e84dd49f6f572476c6d53d1', null, 'Location and date', '1', '0', null, ".ADMLEVEL_DEVELOPER.")";
					break;
				case "pages":
					$sql = "CREATE TABLE `pages` (
					  `id` int(11) unsigned NOT NULL auto_increment,
					  `sectionid` int(11) NOT NULL default '0',
					  `ppage_id` int(11) default '0',
					  `lineage` varchar(255) default '0',
					  `pagename` varchar(50) NOT NULL,
					  `pagealias` varchar(50) default NULL,
					  `pagetitle` varchar(50) NOT NULL,
					  `metatitle` varchar(255) default NULL,
					  `metadescr` varchar(255) default NULL,
					  `metakeywords` ".((DB_VER > 3.99) ? "varchar(512)" : "blob")." default NULL,
					  `description` varchar(255) NOT NULL default '',
					  `language` varchar(50) NOT NULL default 'english',
					  `pagetypeid` int(11) NOT NULL default '1',
					  `locked` tinyint(1) NOT NULL default '1',
					  `searchable` tinyint(1) NOT NULL default '1',
					  `displayed` tinyint(1) NOT NULL default '1',
					  `protected` tinyint(1) NOT NULL default '0',
					  `rank` int(6) NOT NULL default '0',
					  `draft` tinyint(1) NOT NULL default '0',
					  `published` tinyint(1) NOT NULL default '1',
					  `date_created` timestamp".((DB_VER > 3.99) ? " NOT NULL default CURRENT_TIMESTAMP" : "").",
					  `date_updated` timestamp".((DB_VER > 3.99) ? " NULL default '0000-00-00 00:00:00'" : "").",
					  `date_published` timestamp".((DB_VER > 3.99) ? " NULL default '0000-00-00 00:00:00'" : "").",
					  PRIMARY KEY  (`id`)
					)".((DB_VER > 3.99) ? " ENGINE=MyISAM DEFAULT CHARSET=latin1" : "");

					$sql2 = "INSERT INTO `pages` (`sectionid`, `ppage_id`, `pagename`, `pagealias`, `pagetitle`, `metatitle`, `metadescr`, `metakeywords`, `description`, `language`, `pagetypeid`, `locked`, `searchable`, `displayed`, `protected`, `rank`, `published`) VALUES
					('0', '0', 'home', 'index', 'Home', null, null, null, '', 'english', '1', '1', '1', '1', '0', '0', '1'),
					('0', '0', 'aboutus', null, 'About Us', null, null, null, '', 'english', '1', '1', '1', '1', '0', '0', '1'),
					('0', '0', 'events', null, 'Events', null, null, null, '', 'english', '2', '1', '1', '0', '0', '0', '1'),
					('0', '0', 'galleries', null, 'Galleries', null, null, null, '', 'english', '2', '1', '1', '0', '0', '0', '1'),
					('0', '0', 'requestinfo', null, 'Request More Info', null, null, null, '', 'english', '1', '1', '1', '1', '0', '0', '1'),
					('0', '0', 'contactus', null, 'Contact Us', null, null, null, '', 'english', '1', '1', '1', '1', '0', '0', '1'),
					('0', '0', 'whatsnew', null, 'Whats New', null, null, null, '', 'english', '1', '1', '1', '1', '0', '0', '1'),
					('0', '0', '404', null, 'Page Not Found', null, null, null, '', 'english', '1', '1', '0', '0', '0', '0', '1'),
					('0', '0', 'sitemap', null, 'Sitemap', null, null, null, '', 'english', '1', '1', '0', '0', '0', '0', '1')";
					break;
				case "editor_userpages":
					$sql = "CREATE TABLE `editor_userpages` (
					  `id` int(11) unsigned NOT NULL auto_increment,
					  `pageid` int(11) default '0',
					  `content` text,
					  `link` varchar(255) default NULL,
					  `objwidth` int(4) default '305',
					  `objheight` int(4) default '205',
					  PRIMARY KEY  (`id`)
					)".((DB_VER > 3.99) ? " ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1" : "");
					break;
				case "page_types":
					$sql = "CREATE TABLE `page_types` (
					  `id` int(11) unsigned NOT NULL auto_increment,
					  `type` varchar(50) NOT NULL,
					  `description` varchar(50) NOT NULL,
					  `locked` tinyint(1) NOT NULL,
					  PRIMARY KEY  (`id`)
					)".((DB_VER > 3.99) ? " ENGINE=MyISAM DEFAULT CHARSET=latin1" : "");

					$sql2 = "INSERT INTO `page_types` (`type`, `description`, `locked`) VALUES
					('editor', 'Page is constructed with a WYSIWYG editor', '0'),
					('data', 'Page provides access to database-driven content', '1'),
					('form', 'Page includes a simple form', '1')";
					break;
				case "session_login":
					$sql = "CREATE TABLE `session_login` (
					  `id` int(5) NOT NULL auto_increment,
					  `user_id` int(11) default '0',
					  `ip_hash` varchar(255) default NULL,
					  `username` varchar(255) default NULL,
					  `section` varchar(255) default NULL,
					  `logged_in_date` timestamp".((DB_VER > 3.99) ? " NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP" : "").",
					  `logged_in` tinyint(1) default '0',
					  PRIMARY KEY  (`id`)
					)".((DB_VER > 3.99) ? " ENGINE=MyISAM DEFAULT CHARSET=utf8" : "");
					break;
				case "plugins":
                    $sql = "CREATE TABLE `plugins` (
                      `id` int(5) NOT NULL AUTO_INCREMENT,
                      `name` varchar(50) DEFAULT NULL,
                      `ver` varchar(10) DEFAULT NULL,
                      `sysver` float(4,2) DEFAULT '3.00',
                      `author` varchar(255) DEFAULT NULL,
                      `created` timestamp NULL DEFAULT '0000-00-00 00:00:00',
                      `revised` timestamp NULL DEFAULT '0000-00-00 00:00:00',
                      `descr` varchar(255) DEFAULT NULL,
                      `license` varchar(50) DEFAULT 'free',
                      `website` varchar(255) DEFAULT NULL,
                      `usedin` varchar(50) DEFAULT 'both',
                      `folder` varchar(255) DEFAULT NULL,
                      `depends` varchar(255) DEFAULT NULL,
                      `incl` varchar(50) DEFAULT NULL,
                      `initfile` varchar(255) DEFAULT NULL,
                      `headerfunc` varchar(255) DEFAULT NULL,
                      `settingsfunc` varchar(255) DEFAULT NULL,
                      `nodisable` tinyint(1) DEFAULT '0',
                      `nodelete` tinyint(1) DEFAULT '0',
                      `builtin` tinyint(1) DEFAULT '0',
                      `active` tinyint(1) DEFAULT '1',
                      `is_framework` tinyint(1) DEFAULT '0',
                      `is_deleted` tinyint(1) DEFAULT '0',
                      `errors` text DEFAULT NULL,
                      `error_code` int(3) DEFAULT '0',
                      `updflag` varchar(5) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    )".((DB_VER > 3.99) ? " ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1" : "");
					break;
				case "register":
					$sql = "CREATE TABLE `register` (
					  `id` int(5) NOT NULL AUTO_INCREMENT,
                      `type` varchar(255) NOT NULL,
					  `fileurl` varchar(255) NOT NULL,
					  `db_table` varchar(50) DEFAULT NULL,
					  `db_child_table` varchar(50) DEFAULT NULL,
					  `function` varchar(255) DEFAULT NULL,
					  `parameters` text DEFAULT NULL,
					  `trigger` varchar (50) DEFAULT NULL,
					  `priority` int(2) DEFAULT '0',
					  `active` tinyint(1) NOT NULL DEFAULT '1',
					  PRIMARY KEY (`id`)
					)".((DB_VER > 3.99) ? " ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1" : "");
					break;
			}
			if($sql != "") {
				mysql_query($sql) or die("Error creating table '$table': $sql".mysql_error());

				if($sql2 != "") {
					mysql_query($sql2) or die("Error inserting initial values in '$table': ".mysql_error()."<br>$sql2");
				}
			}
			$sql = "";
			$sql2 = "";
		}
	}
}

/**
 * Validates database structures against preset hashes (CRC)
 */
function getTableHashes(){
	$tables = mysql_query("SHOW TABLES");
	$retn_tables = array();
	$reqd_tables = array("settings", "sections", "admin_accts", "pages", "page_types", "editor_userpages", "session_login", "plugins", "register", "cache");
	while($row = mysql_fetch_array($tables)){
		$key = array_search($row[0], $reqd_tables);
		if($key !== false){
			$sql = "SHOW COLUMNS FROM `{$row[0]}`";
			$columns = mysql_query($sql);
			while($col = mysql_fetch_assoc($columns)){
				$data = $col['Field']."|".$col['Type']."|".$col['Key']."|".$col['Default'];
				$hash = md5($data);
				$retn_tables[$row[0]][$col['Field']] = $hash;
			}
		}
	}

	print "<pre>".print_r($retn_tables, true)."</pre>";
	exit;
}

/**
 * Attempts to reset corrupt tables to standard structuring
 * @param array $tables
 */
function repairTableColumns($tables){
	$hash_tables = array(
    	"admin_accts" => Array(
            "id" => "7a4ccd1d51109e099da81abef6db8c2d",
            "username" => "550cdd582107601106e504d6083c7e28",
            "password" => "7c654b50c8280d428b2d2bd346b2bf11",
            "phash" => "908a92f295b1421431b1c0c06a2d6651",
            "pcle" => "20b46e14bd9026cfd471862ca1ad6126",
            "level" => "8d93adc2ee127c7dd4ada6fff28b12e1",
            "email" => "efc8f176e89c21fa075914984120577c",
            "firstname" => "bbf3c5cc61790a095bd65b70b2b56995",
            "lastname" => "167dddf59087c7248550ae05bcfe405c",
            "twitter_link" => "df66a56846f79bb280db7af12795dfc1",
            "google_plus_link" => "f71e519774c4f05d54c720fcb37572ce",
            "facebook_link" => "3f471eea0dad3eb7f8d041eea9e9a707",
            "image" => "17b2e135601ec045117660b9c6d665c7",
            "thumb" => "eeb175fb9df7d9cffd4ff3d58092a975",
            "avatar" => "58cb0b575fa3a26ecce9c5849f88b253",
            "hint" => "72b73b7741dbcb70d8ca7670b2f6077a",
            "activated" => "3a74912b1311254d96d5990330b75f71",
            "blocked" => "91491fd9bc6f3276f63a2fd075137c44",
            "blocked_time" => "5ad3cae38ec849a8d0bbbcd9fd4c05b3",
        ),
    	"cache" => Array(
            "id" => "619e3a9bf2fa90b8f01570d353a37744",
            "stored" => "b93e65de80e852c1531419deba730d95",
            "timestamp" => "9b4e8ab4a6045a611c27ee0c602d6e26",
        ),
    	"editor_userpages" => Array(
            "id" => "50cff0e73a497981519b58e484506c36",
            "pageid" => "b61a6dbb22d08ba1fe263a5b160ea392",
            "content" => "7e62ba8dbf2476cbb7af8095eea6c368",
            "link" => "56cf3d0a875077dcd000a703e83f3775",
            "objwidth" => "52ba3e1a3520de4706ad0df1dffb60bc",
            "objheight" => "88868797a54dca78b2ecdc6a8db83120",
            "image" => "17b2e135601ec045117660b9c6d665c7",
            "thumb" => "eeb175fb9df7d9cffd4ff3d58092a975",
        ),
    	"page_types" => Array(
            "id" => "50cff0e73a497981519b58e484506c36",
            "type" => "41dc9ba06f03c83f8922de658db26e8e",
            "description" => "0bccceea6e537ea6f6e7faf2b61668eb",
            "locked" => "16517cb2fb5153312994b2dea56facce",
        ),
    	"pages" => Array(
            "id" => "50cff0e73a497981519b58e484506c36",
            "sectionid" => "290c5d2752f67fe5e9ce2cacdd4fb808",
            "ppage_id" => "61a4933e0277948ca4dcab23ab3049f4",
            "lineage" => "15198e3859ae58ecc76da57c77c08072",
            "pagename" => "5fb39a8cf5d73a08ff89ce4b2e832b6c",
            "pagealias" => "35b809eaea6c9d5d3259e9258b077b76",
            "pagetitle" => "6fc31eb3d48223bc810f6a5ffbb2c57f",
            "metatitle" => "b92fada020683ede02cafb83be5dd98e",
            "metakeywords" => "fbb2cdc1021ca75882f145541daecc2c",
            "metadescr" => "6ac158de8507b564126daa70655ad416",
            "language" => "86a1e22e0c805e63840a9ce83c8dd8fc",
            "pagetypeid" => "3fefeb4f63ef2afe56375409a2089b3a",
            "locked" => "0d0b5515aad5aa20293ddeaa5a33ede5",
            "searchable" => "92087020fa97475ad1736fa97649dcf4",
            "displayed" => "3ebc6c8f7e7a080c3d7228de488463e0",
            "protected" => "56fe5f92e839be7d68a314a3c3432a80",
            "subpagesallowed" => "780dc71feaf7cb98b52961b7ee562180",
            "rank" => "59d8135b9683f0e22670881a2c883cc1",
            "draft" => "16a404392105e96b60aa00d2559278e1",
            "published" => "9def0b32f59faf9ae986a8959d0ac74b",
            "date_created" => "378ae5f8f75c1c6cf9d8428e2a24903c",
            "date_updated" => "12dfd57acfd8096d5bcf7273ca52073b",
            "date_published" => "a853a828c0f95c59050c7aea684746ea",
        ),
    	"plugins" => Array(
            "id" => "6367b31a6992bec5d2f848248cfad266",
            "name" => "e1134d124276c8803bb5cd93a1377205",
            "ver" => "e3853f9867cde17ded1e706f08d0abe4",
            "sysver" => "4df08b824924810bba26c3ed423be1f0",
            "author" => "6d4644ac8bbe521857ad263cab841fb3",
            "created" => "314e4c6756953ac2d3ec4c58a72c3e00",
            "revised" => "71620e72bb654c10b879e264407b339b",
            "descr" => "7c8d45a5c4725cf40fcb471262a7b528",
            "license" => "1d26d2dabbf950ab63336c5666c39882",
            "website" => "419b6ee323d3ae2e7cbaf61c56676744",
            "usedin" => "31968422547aad1d5c49709e79522f1c",
            "folder" => "1b38bbe07f54a90f9b006ba848dba037",
            "depends" => "30cc4ab28df3ed9e39337ab1621ca5e9",
            "incl" => "fd34f6820320b6c6da3d02156af5eda6",
            "initfile" => "37a0798ad7c301938eca036b6dc0afd0",
            "headerfunc" => "a8fdc4e9bf2f57be57c5a8fed4e540f8",
            "settingsfunc" => "79cb51cfefcffa40ce29b8ecbc7c4ba8",
            "nodisable" => "12acc826262caa9b0f89d4eec78b90c5",
            "nodelete" => "e3edd81bc8fd3a6dee40c9484b946ef8",
            "builtin" => "fb0004a82f5545b9b14376f600bcfdaf",
            "is_framework" => "9b120afe19ef4561c2c1a9a2d9372cf4",
            "active" => "6eacc188404e17505d79ebbc965f6933",
            "is_deleted" => "136124fc59238f0bc25c178ec0bd1e84",
            "errors" => "4d721a1c979953a052caaa1654635772",
            "error_code" => "e0bf963b4a9996d93a7bc80a1f8d9791",
            "updflag" => "44cb575b70bc99fc780f03379b3c3453",
        ),
    	"register" => Array(
            "id" => "6367b31a6992bec5d2f848248cfad266",
            "type" => "09e8c9cc1a262bb5bbbe1285e5891d80",
            "fileurl" => "29a2f8d416b721abffcc336cdb8071e8",
            "db_table" => "0aa817c6421c2ee877967f21ecd2df13",
            "db_child_table" => "71ac70c0e36e01f9e410a88bf8ea9c3c",
            "db_parent_table" => "ba28119f172de6e220e31a2c65956391",
            "function" => "6f5de9c8b95498a0ac48b6197234963c",
            "parameters" => "ac5fef1c6792c88374d069ba2704123b",
            "trigger" => "a7b19e2f9535f373c83af25746a987eb",
            "priority" => "beb981dab870fab6a115670b8b93baca",
            "active" => "6eacc188404e17505d79ebbc965f6933",
        ),
    	"sections" => Array(
            "id" => "50cff0e73a497981519b58e484506c36",
            "code" => "efe1875686ea155f840248fbb9a95ac9",
            "alias" => "3c37ff051609044c1da3268687e8f1bf",
            "name" => "e1134d124276c8803bb5cd93a1377205",
            "description" => "48f2657877c95da2a47beccbe4b49223",
            "image" => "17b2e135601ec045117660b9c6d665c7",
            "display" => "cd0ebcdf1915e41f0eea642d5a74e1a4",
            "lat" => "aa726593233635e2da8fefe53d8cca97",
            "lon" => "892f94db64e616bfcf489e4ff7b00651",
            "weatherurl" => "512ecd96090b409f1be5c4cd353ffc9e",
            "citycode" => "6c0d0f00754ea6dbe1e56124fb49310c",
            "area" => "ed246f8c80b89bdf8907aff86d6a05f3",
        ),
    	"session_login" => Array(
            "id" => "6367b31a6992bec5d2f848248cfad266",
            "ip_hash" => "b021b775cf009d3f0c13129761f4892a",
            "user_id" => "ac1cae0a2c7dcdf7410beb5c565ac072",
            "username" => "7754c5ec2533addd8685439af824f2cb",
            "section" => "3a6c77488515e77c3eccc3543e9154e2",
            "logged_in_date" => "0b0ed130ca3eb3c81d2da8d7a2637df5",
            "logged_in" => "29dd6052bf11fe74270873fac8fa095f",
        ),
    	"settings" => Array(
            "name" => "ed25baa67c8da9efe496b576c775503f",
            "value" => "2a7c10854cfef27cb0a1d57666213a0f",
            "type" => "a00bd97757a5c2544f5543d7821f5b08",
        )
	);

	while($row = mysql_fetch_array($tables)){
		if(isset($hash_tables[$row[0]])){
			// we only care about required tables
			if(is_array($hash_tables[$row[0]])) {
				// table is a required table
				$reqd_columns = $hash_tables[$row[0]];
				$sql = "SHOW COLUMNS FROM `{$row[0]}`";
				$columns = mysql_query($sql);
				while($col = mysql_fetch_assoc($columns)){
					$data = $col['Field']."|".$col['Type']."|".$col['Key']."|".$col['Default'];
					$hash = md5($data);
					if(!isset($reqd_columns[$col['Field']])){
						// column does not exist in required table
						$hash_tables[$row[0]][$col['Field']] = "drop";
					}elseif($hash != $reqd_columns[$col['Field']]){
						// column structure doesn't match required structure
						$hash_tables[$row[0]][$col['Field']] = "alter";
					}else{
						unset($hash_tables[$row[0]][$col['Field']]);
					}
				}
				if(count($hash_tables[$row[0]]) == 0) unset($hash_tables[$row[0]]);
			}
		}
	}
	mysql_data_seek($tables, 0);
}

?>