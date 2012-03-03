FILEUPLOADER PLUG-IN
Web Template 3.0
========================================

-- Requires --
PHP 5+

-- Inclusion --
$incl = 'fileuploader';

-- Implementation --
Part 1:  establish image fields
showImageField($label, $ids, $values, $displayed, $folder);

Part 2:  prepare uploader Javascript code
showFileUploaderScript($fileType, $allowedExt, $dest_folder, $width, $height, $inline, $showbuttontext);
 - $dest_folder = server-qualified path to destination
 - $width = integer of displayed image
 - $height = integer of displayed image
 - $inline = true (buttons are to the left of the file box) or false (buttons are above the file box)
 - $showbuttontext = true (to show 'Select...' and 'Delete...') or false (to show icons)

eg.

showFileUploaderScript(IMGEDITOR_TEMPFOLDER, 177, 107, false, false);

Part 3:  create fileuploader objects and attach to fileuploader container created in Part 1 ($ids)
attachFileUploader($elems, $lastfiles);
 - $elems can be either "single" element or array("array", "of", "elements") for each image block
 - $lastfiles can be either one file or array of files for each image block
 - Call attachFileUploader only once.  Use the array parameter construction if there are multiple image containers

eg.

attachFileUploader(array('image',...), array($image,...), array($allowedExt,...);
 - $allowedExt = comma-separated string of extensions
        (IMAGE_TYPES, AUDIO_TYPES, VIDEO_TYPES, DOC_TYPES, PDF_TYPES, WEB_TYPES)

-- Returns --
The system returns the following codes in the {label}_mod field:
 - fileuploader ... when file is successfully uploaded
 - imgeditthumb ... after an image file is edited


