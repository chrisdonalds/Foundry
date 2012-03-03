SHRINKURL PLUG-IN
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================

-- Requirements --
1. db_configs.php file in the root/inc folder

-- Root .HTAccess Config --
# ----- ShrinkURL
RewriteRule ^s/([^\/]+)$ inc/_plugins/shrinkurl/shrinkurl.core.php?s=$1
RewriteRule ([^\/]+)/s/([^\/]+)$ inc/_plugins/shrinkurl/shrinkurl.core.php?s=$2

-- Inclusion --
$incl = 'shrinkurl';

-- Call --
$link = shrinkURL($link);

Note: If your code already includes the NavTweet plugin ($incl = 'twitter'), ShrinkURL will be automatically
loaded and the above call is handled by the NavTweet plugin internally.
