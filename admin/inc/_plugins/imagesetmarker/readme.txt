IMAGESETMARKER PLUG-IN FOR CKEDITOR
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================

The php file contained in this folder is part of a system that has the following
components:

- ImagesetMarker CKEditor plug-in which adds the marker HTML to CKEditor content.
- The photo-iframe admin system module
    eg.: <iframe width="100%" height="345" frameborder="1" scrolling="yes" marginheight="0" marginwidth="0"
          src="<?= WEB_URL.ADMIN_FOLDER ?>photos-iframe/list-photos.php?pagename=<?=$pagename?>
          &page_id=<?=$row_id?>&list_type=photo&itemsperrow=3">
- A call from the site page to the imagesetmarker.php file in this folder

-- Inclusions --
none

-- Preparation --

<?php
include(SITE_PATH.PLUGINS_FOLDER."imagesetmarker/imageset.php");
?>