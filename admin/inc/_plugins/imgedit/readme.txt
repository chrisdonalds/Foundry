IMGEDIT PLUG-IN
Web Template 3.0
========================================

-- Inclusion --
$incl = 'imgedit fileuploader';

-- Requires --
1. FileUploader Plugin
2. Folders structured under {root}/images/, all CHMOD'ed to 777:
    /images
        /imgedit
            /library
            /temp
                /thumbs

-- Implementation --
showImgEditBox($label, $imglabel, $width, $height, $js, $labelclass, $fldclass);

eg. showImgEditBox("Photo Image", "image", 177, 107);
