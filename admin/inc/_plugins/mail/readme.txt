FORMTOEMAILPRO PLUG-IN
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================

-- Inclusions --
$incl = "form2email";
// edit '//stuff to do' section of formtoemail_head.php file to handle specific posts

-- Implementation --
# Put in FORM

<form action="<?= $_SERVER['PHP_SELF']?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="required_fields_dyn" value="name|email|phone">
    <input type="hidden" name="redirect_url" value="redirectpage.php" />
    <input type="hidden" name="subject" value="Subject" />
    <input type="hidden" name="error_output_type" value="divbox" />

    <?php
    // Formtoemailpro divbox
    if($error_content != "") {
        echo '<div class="errorbox regbox">';
        echo $error_content;
        echo '</div>';
    }
    // End divbox

    <input type="text" name="sec" id="sec" value="" class="fsec" />
    <input type="text" name="time" id="time" value="<?= time(); ?>" class="fsec" />
</form>

If you want to utilize custom AltMailers, add the following hidden field:

    <input type="hidden" name="altmailer" value="<?=SITE_PATH.PLUGINS_FOLDER?>mail/altmailerfilename.php" />

To create an AltMailer, create the altmailer php file in inc/mail/... and include:

function altmail($email, $subject, $message, $headers){
    ... code to send email ...
    return $result-of-mail-process; //i.e: return mail($email, $subject, $message, $headers);
}

----------------------------------------

MASKED EMAILS
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================

# Use this piece if you are working with many emails and want a way to dynamically parse them

<?php
// Specify the email however you wish
$email = "username@server.com";

// You can replace WEB_URL.IMG_UPLOAD_FOLDER below with a full path to the @ symbol graphic
$atsymbol = WEB_URL.IMG_UPLOAD_FOLDER."interface/atsymbol.gif";

preg_match("/([a-z0-9\.\-_]*)@([a-z0-9\-_#%&\^\*\(\)]*)\.([a-z0-9\.]*)/", $email, $emailarray);
$email = maskEmailAt($email, $atsymbol);

echo "<a href=\"javascript: parse_email('".$emailarray[1]."', '".$emailarray[2]."', '".$emailarray[3]."', 'subject')\">";
echo $email."</a>";
?>


# Use this piece if you are working with one email

<?php
// You can replace WEB_URL.IMG_UPLOAD_FOLDER below with a full path to the @ symbol graphic
$atsymbol = WEB_URL.IMG_UPLOAD_FOLDER."interface/atsymbol.gif";
?>
<a href="javascript: parse_email('username', 'server', 'domain', 'subject')"><?= maskEmailAt("username@server.domain", $atsymbol) ?></a>


# Use this to mask emails contained in string content (i.e. page content)

$content = maskEmailinContent($content, $atpicpath);
