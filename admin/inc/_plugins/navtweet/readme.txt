NAVTWEET PLUG-IN
Web Template 3.0
========================================

-- Requirements --
1. PHP 5.2 or higher
2. Navtweet database table
3. db_configs.php file in the root/inc folder
[note: CONSUMER_KEY and CONSUMER_SECRET values are already included in this plug-in and registered with Twitter]

-- Inclusion (Nav Admin 3.1+) --
$incl = 'twitter';

-- Implementation --
:If used in Nav Admin 3.1+ system:

    createTwitterSession($twitter_section, $twitter_op, $twitter_content, $twitter_link, $twitter_linkname, $twitter_callback);

:If used in website that is not powered by Nav Admin 3.1+:

Customize the database values on lines 5 through 8 in init_nonadmin.php
Add the following two lines to your PHP script:

    include("navtweet.core.php");
    createTwitterSession($twitter_section, $twitter_op, $twitter_content, $twitter_link, $twitter_linkname, $twitter_callback);

- $twitter_section = one of the Twitter function groups, commonly 'status' (run navtweet.help.html)
- $twitter_op = one of the Twitter operation functions, commonly 'update'
- $twitter_content = text you want to send
- $twitter_link = an optional URL that a viewer can click to continue reading (see note below)
- $twitter_linkname = the name of the link (eg. "article", "post", "topic"...)
- $twitter_callback = the URL where you want Twitter to return after performing the requested operation

-- Returns --
On success: Twitter redirects browser to callback URL.
On error: array containg result of error.  Errors are collected in the $err array

-- Notes --
1. If using the $twitter_link parameter:
    Since tweets are limited to 140 characters, the API will attempt to load the ShrinkURL API
    to minify the link (condensed to http://www.website.com/s/code).

    Simply place the ShrinkURL files in the http://www.website.com/inc/shrinkurl/ folder and follow
    its instructions in the ShrinkURL readme.txt.

    If ShrinkURL and its table are not found, the link parameter will not be minified.

