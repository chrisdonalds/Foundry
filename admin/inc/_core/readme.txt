--------------------------------------------------------------------------------------------------
                                        F O U N D R Y
                                   PHP/HTML Web Framework
--------------------------------------------------------------------------------------------------

Author: Chris Donalds, cdonalds01@gmail.com
Current Stable Version: 3.9.5
Copyright (C) 2012, Navigator Multimedia, Inc.

Special thanks to the staff of Navigator Multimedia for ongoing help with coding and
styling roadblocks and being general guinea pigs.  This software contains several
modules and supplied plugins, of which I give credit to, some of which are:

- JQuery Validator Pack (Jörn Zaefferer)
- ImgEdit/Jcrop Plugin (Kelly Hallman)
- Browser Detector (Anthony Hand)

Individual plugins (/admin/inc/_plugins) may contain their own licenses and/or requirements.

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

Get the full text of the GPL here: http://www.gnu.org/licenses/gpl.txt

--------------------------------------------------------------------------------------------------

- FORM & FIELDS -

Button Types:
-------------
DEF_ACTION_LIST         -- list
DEF_ACTION_ADD          -- add
DEF_ACTION_EDIT         -- edit
DEF_ACTION_EDITFORM     -- editfrm
DEF_ACTION_EDITMETA     -- editmeta
DEF_ACTION_DELETE       -- delete
DEF_ACTION_UNDELETE     -- undelete
DEF_ACTION_PUBLISH      -- publish
DEF_ACTION_UNPUBLISH    -- unpublish
DEF_ACTION_ACTIVATE     -- activate
DEF_ACTION_DEACTIVATE   -- deactivate
DEF_ACTION_ARCHIVE      -- archive
DEF_ACTION_UNARCHIVE    -- unarchive
DEF_ACTION_VIEWRECS     -- view
DEF_ACTION_VIEWPAGES    -- viewpages
DEF_ACTION_PROMOTE      -- promote
DEF_ACTION_DEMOTE       -- demote
DEF_ACTION_SUBSCRIBE    -- subscribe
DEF_ACTION_UNSUBSCRIBE  -- unsubscribe
DEF_ACTION_SEND         -- send
DEF_ACTION_OPEN         -- open
DEF_ACTION_REPLY        -- reply
DEF_ACTION_DEFAULT      -- default
DEF_ACTION_SAVEORG      -- saveorganize
DEF_ACTION_CLONE        -- clone
DEF_ACTION_EXPORT       -- export

Display Types ($displaytype):
-----------------------------
FLD_DATA	-- Display only the field object
FLD_LABEL	-- Display label wrapped in table row/cell object
FLD_OPENROW	-- Same as FLD_ALL except the closing </td></tr> is not displayed, allows more items, typically prepared with FLD_DATA, to be added after
FLD_ALL		-- Displays entire block content (default)

Javascript content ($js):
-------------------------
Includes Javascript content in field object
eg: 'onclick="dosomething()"'

CSS Class content ($labelclass and $fldclass):
----------------------------------------------
Labelclass 	-- Includes CSS class attribute in label text, eg: 'highlight' or 'red highlight'
Fldclass	-- Includes CSS class attribute in field object, eg: 'shortwidth' or 'shortwidth backgreen'

Notations ($notation):
----------------------
Adds optional text after field object.  See function references below for specific details.

Labels ($label):
----------------
The label text
Suffixing the label text with an asterisk causes the required marker (*) to be displayed

IDs ($id, $ids):
----------------
id		-- The object id and name (id and name are the same)
ids		-- An array of object ids.  eg: array("image", "lastimg", "lastthm", "delimg")

Values ($value, $values):
-------------------------
value		-- The value string for the object
values		-- An array of values for the objects.  eg: array($image, $thumb)

Toolbars ($toolbar):
--------------------
CKEditor toolbars

values		-- 'FormatOnly', 'FormatOnlyWithSource', 'Custom', 'CustomWithSource'
By default, the system will automatically add 'WithSource' (displaying the 'source' button) when user is logged in as a developer


=========
FUNCTIONS
=========

showList
--------
Displays list page data list

showList($formname, $hiddenfields, $recset, $buttoncondindex = "", $buttontagfield = "", $allowsort = true);
$formname => name of form
$hiddenfields => array of hidden fields (name => value)
$recset => data recordset
$buttoncondindex => button conditional index (record column that will be checked against button keys)
$buttontagfield => button tags associated by button key
$allowsort => specifies whether or not sorting is used (will be overwritten by ALLOW_SORT constant)

$colattr values ('columnname' => 'attr value/expression')
// hover col (hover=columnname)
// fileexists col (fileexists=columnname)
// quick edit col (quickedit=columnname)
// flag col (flag=columnname)
// conditional expression (expr=columnname{=,>,<,>=,<=,!=}value,actiontrue,actionfalse)
// conditional flag col (expr=columnname{=,>,<,>=,<=,!=}value)
// image (image=columnname)

showLiteral
-----------
Displays text, span or div

showLiteral($text, $spanid, $divid, $style, $js);
$spanid not blank => outputs text surrounded by span
$divid not blank => outputs text surrounded by div

showLabel
---------
Displays label portion only

showLabel($label, $labelclass);


showTextField
-------------
Displays structured form text input field and/or label

showTextField($label, $id, $value, $size = 60, $maxlen = 0, $notation = "", $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL);


showTextareaField
-----------------
Displays structured form textarea field and/or label

showTextareaField($label, $id, $value, $cols = 75, $rows = 7, $maxlen = 0, $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL);


showHTMLEditorField
-------------------
Displays structured CKEditor block and/or label

showHTMLEditorField($label, $id, $value, $cols = 20, $rows = 15, array($width = 750, $height = 500), $toolbar = "FormatOnly", $maxlen = 0, $notation = "", $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL);


ShowPasswordField
-------------
Displays structured form password input field and/or label

showPasswordField($label, $id, $value, $size = 60, $maxlen = 0, $notation = "", $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL);

if $id ends with a asterisk (*) the object is treated as an original password rather than a confirmation password


showButtonField
---------------
Displays structured form button field and/or label

showButtonField($label, $id, $value, $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_DATA);


showImageField
--------------
Displays structured new/edit form image field, thumbnail sample and/or label

showImageField($label, $ids, $values, $displayed, $folder, $size = 60, $notation = "", $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL);

$ids = array('image', 'lastimg', 'lastthm', 'delimg')
$values = array($image, $thumb)
$displayed = array(true to show image section, true to show thumbnail section)
$folder = $db->table


showImgEditBox
------------
Displays structured ImgEditBox (Image Editor) block

showImgEditBox($label, $imglabel, $js = "", $labelclass = "", $fldclass = "");


showFileField
-------------
Displays structured new/edit form file field and/or label

showFileField($label, $ids, $fs_file, $folder, $type, $size = 60, $notation = "", $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL);

$ids = array('pdf', 'lastpdf', 'delpdf')
$fs_file = filespec
$type = 'image', 'pdf', 'audio', 'doc', 'video'
$folder = $db->table


showAddFileField
----------------
Displays structured new form file field and/or label

showAddFileField($label, $id, $value, $size = 60, $maxlen = 0, $notation = "", $js = "", $labelclass = "", $fldclass = "", $displaytype = FLD_ALL);


showHiddenField
---------------
Displays structured form hidden field

showHiddenField($id, $value);


Starting Foundry From Within a non-Foundry or non-Plugin PHP file
-----------------------------------------------------------------
Registered plugins are easily started with the $incl variable containing
the inclusion phrase of the chosen plugin or plugins (separated by a space).

Sometimes, however you may want to start Foundry to gain access to its features,
such as:

- plugins
- user accounts
- constants
- core functions
- handlers
- themes
- libraries
- custom apps
- settings
- or, admin pages

To do so, you will need to simulate the tasks automatically performed by Foundry.

Add the following to your PHP file where you want Foundry to start:

(** NOTE: The number of ../ segments equals the number of subfolders required to return
to the document root.  I.e. If the file is in a folder one folders from the root, there
would be one ../)

define("VALID_LOAD", true);			// tells Foundry that this is a valid load process
define("BASIC_GETINC", true);		// tells Foundry not to run advanced startup tasks
define("VHOST", substr(str_replace("\\", "/", realpath(dirname(__FILE__)."/../../")), strlen(realpath($_SERVER['DOCUMENT_ROOT'])))."/");
include ($_SERVER['DOCUMENT_ROOT'].VHOST."admin/inc/_core/getinc.php");	// starts Foundry

To start the plugins system, add:

getInstalledPlugins();
initPluginsandFrameworks();

