<?php

/**
 * @file
 * A single location to store configuration.
 */

require_once("init_nonadmin.php");
if(DBUSER == "" || DBNAME == "") die("Database settings not set in NavTweet config file!");

define('CONSUMER_KEY', 'oUGhc9CpJmTFncKZo6TfA');
define('CONSUMER_SECRET', 'yk0df46PUCJ24JXHtPsMOBWszlCczrXobhPzjJObAPI');
define('OAUTH_CALLBACK', WEB_URL.ADMIN_FOLDER.PLUGINS_FOLDER.'/navtweet/twitter_callback.php');
?>