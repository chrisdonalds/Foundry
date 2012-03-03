SITEMAPGEN PLUG-IN
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================

-- Inclusions --
none

-- Implementation (Run once) --

ONLY MODIFY EXCLUSION/INCLUSION ARRAYS IN LINES 76 to 121!

Go to www.domain.com/path/to/xmlsitemapgen.php without any switches to view options
 - Verbose controls silent operation
 - No options will process XML file
 - Gensitepage controls processing of INC file
 - Dbfile controls processing and inclusion of database content
 - Chkfreq sets XML spidering frequency
 - Priority sets XML priority

-- Implementation (Run on record change) --

$_GET['chgfreq'] = "monthly";   (options: daily, weekly, monthly, quarterly)
$_GET['priority'] = 1;          (options: 1 to 9)
$_GET['gensitepage'] = 1;       (options: 1 to process sitemap.inc, 0 to skip)
$_GET['dbfile'] = 1;            (options: 1 to process xmlsitemapgen_dbfile.php, 0 to skip)
$_GET['verbose'] = 0;           (options: 1 to display output, 0 to run silent)

$_GET['chgfreq'] = "monthly"; $_GET['priority'] = 1; $_GET['gensitepage'] = 1; $_GET['dbfile'] = 1; $_GET['verbose'] = 0;
include(SITE_PATH.PLUGINS_FOLDER."sitemapgen/xmlsitemapgen.php");

// or include("path/to/xmlsitemapgen.php");
