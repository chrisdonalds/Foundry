<?
/*
 +-------------------------------------------------------------------+
 |                     M I M E M A I L   (v2.1)                      |
 |                                                                   |
 | Copyright Gerd Tentler               www.gerd-tentler.de/tools    |
 | Created: Nov. 2, 2004                Last modified: Mar. 21, 2008 |
 +-------------------------------------------------------------------+
 | This program may be used and hosted free of charge by anyone for  |
 | personal purpose as long as this copyright notice remains intact. |
 |                                                                   |
 | Obtain permission before selling the code for this program or     |
 | hosting this software on a commercial website or redistributing   |
 | this software over the Internet or in any other medium. In all    |
 | cases copyright must remain intact.                               |
 +-------------------------------------------------------------------+

==========================================================================================================

 This script can send MIME mails with attachments. It uses the PHP mail() function.

 EXAMPLE:

 include('mimemail.inc.php');
 $mail = new MIMEMAIL("HTML");

 $mail->senderName = "sender name";
 $mail->senderMail = "sender@email";
 $mail->bcc = "bcc@email";

 $mail->subject = "This is the subject line";

 $mail->body = "Hello! This is a message for you.";   // OR: $mail->body = "path/to/file";

 $mail->attachments[] = "path/to/file1";
 $mail->attachments[] = "path/to/file2";
 ...

 $mail->create();

 $recipients ='recipient1@email,recipient2@email,recipient3@email';
 if(!$mail->send($recipients)) echo $mail->error;

==========================================================================================================
*/
  error_reporting(E_WARNING);

  class MIMEMAIL {
//--------------------------------------------------------------------------------------------------------
// Configuration
//--------------------------------------------------------------------------------------------------------
    var $type = 'Text';             // default e-mail type ("HTML" or "Text")
    var $senderName = '';           // default sender name
    var $senderMail = '';           // default sender e-mail address
    var $cc = '';                   // default cc (e-mail address)
    var $bcc = '';                  // default bcc (e-mail address)
    var $replyTo = '';              // default reply-to (e-mail address)
    var $subject = '';              // default subject line
    var $priority = 'normal';       // default priority ("high", "normal", "low")

    var $documentRoot = '';         // document root (path to images, stylesheets, etc.)
    var $saveDir = '';              // save e-mail to this directory instead of sending it => just for testing :)
    var $charSet = 'ISO-8859-1';    // character set (ISO)
    var $useQueue = false;          // use mail queue (true = yes, false = no) => does not work with PHP
                                    // versions < 4.0.5, or with versions >= 4.2.3 in Safe Mode, or with
                                    // MTAs other than sendmail!

//--------------------------------------------------------------------------------------------------------
// Don't change from here unless you know what you're doing
//--------------------------------------------------------------------------------------------------------
    var $inline = array();
    var $attachments = array();
    var $cnt = 0;
    var $body, $header, $footer, $error, $subjectLine, $bodyText;
    var $uid1, $uid2, $uid3;
    var $created = false;
    var $exclude = array('htm', 'php', 'pl', 'prl', 'cgi', 'py', 'asp');
    var $mimeTypes = array('dwg'     => 'application/acad',
                           'asd'     => 'application/astound',
                           'tsp'     => 'application/dsptype',
                           'dxf'     => 'application/dxf',
                           'spl'     => 'application/futuresplash',
                           'gz'      => 'application/gzip',
                           'ptlk'    => 'application/listenup',
                           'hqx'     => 'application/mac-binhex40',
                           'mbd'     => 'application/mbedlet',
                           'mif'     => 'application/mif',
                           'xls'     => 'application/msexcel',
                           'xla'     => 'application/msexcel',
                           'hlp'     => 'application/mshelp',
                           'chm'     => 'application/mshelp',
                           'ppt'     => 'application/mspowerpoint',
                           'ppz'     => 'application/mspowerpoint',
                           'pps'     => 'application/mspowerpoint',
                           'pot'     => 'application/mspowerpoint',
                           'doc'     => 'application/msword',
                           'dot'     => 'application/msword',
                           'bin'     => 'application/octet-stream',
                           'oda'     => 'application/oda',
                           'pdf'     => 'application/pdf',
                           'ai'      => 'application/postscript',
                           'eps'     => 'application/postscript',
                           'ps'      => 'application/postscript',
                           'rtc'     => 'application/rtc',
                           'smp'     => 'application/studiom',
                           'tbk'     => 'application/toolbook',
                           'vmd'     => 'application/vocaltec-media-desc',
                           'vmf'     => 'application/vocaltec-media-file',
                           'xhtml'   => 'application/xhtml+xml',
                           'bcpio'   => 'application/x-bcpio',
                           'z'       => 'application/x-compress',
                           'cpio'    => 'application/x-cpio',
                           'csh'     => 'application/x-csh',
                           'dcr'     => 'application/x-director',
                           'dir'     => 'application/x-director',
                           'dxr'     => 'application/x-director',
                           'dvi'     => 'application/x-dvi',
                           'evy'     => 'application/x-envoy',
                           'gtar'    => 'application/x-gtar',
                           'hdf'     => 'application/x-hdf',
                           'php'     => 'application/x-httpd-php',
                           'phtml'   => 'application/x-httpd-php',
                           'latex'   => 'application/x-latex',
                           'mif'     => 'application/x-mif',
                           'nc'      => 'application/x-netcdf',
                           'cdf'     => 'application/x-netcdf',
                           'nsc'     => 'application/x-nschat',
                           'sh'      => 'application/x-sh',
                           'shar'    => 'application/x-shar',
                           'swf'     => 'application/x-shockwave-flash',
                           'cab'     => 'application/x-shockwave-flash',
                           'spr'     => 'application/x-sprite',
                           'sprite'  => 'application/x-sprite',
                           'sit'     => 'application/x-stuffit',
                           'sca'     => 'application/x-supercard',
                           'sv4cpio' => 'application/x-sv4cpio',
                           'sv4crc'  => 'application/x-sv4crc',
                           'tar'     => 'application/x-tar',
                           'tcl'     => 'application/x-tcl',
                           'tex'     => 'application/x-tex',
                           'texinfo' => 'application/x-texinfo',
                           'texi'    => 'application/x-texinfo',
                           't'       => 'application/x-troff',
                           'tr'      => 'application/x-troff',
                           'roff'    => 'application/x-troff',
                           'troff'   => 'application/x-troff',
                           'ustar'   => 'application/x-ustar',
                           'src'     => 'application/x-wais-source',
                           'zip'     => 'application/zip',
                           'au'      => 'audio/basic',
                           'snd'     => 'audio/basic',
                           'es'      => 'audio/echospeech',
                           'tsi'     => 'audio/tsplayer',
                           'vox'     => 'audio/voxware',
                           'aif'     => 'audio/x-aiff',
                           'aiff'    => 'audio/x-aiff',
                           'aifc'    => 'audio/x-aiff',
                           'dus'     => 'audio/x-dspeeh',
                           'cht'     => 'audio/x-dspeeh',
                           'mid'     => 'audio/x-midi',
                           'midi'    => 'audio/x-midi',
                           'mp2'     => 'audio/x-mpeg',
                           'ram'     => 'audio/x-pn-realaudio',
                           'ra'      => 'audio/x-pn-realaudio',
                           'rpm'     => 'audio/x-pn-realaudio-plugin',
                           'stream'  => 'audio/x-qt-stream',
                           'wav'     => 'audio/x-wav',
                           'dwf'     => 'drawing/x-dwf',
                           'cod'     => 'image/cis-cod',
                           'ras'     => 'image/cmu-raster',
                           'fif'     => 'image/fif',
                           'gif'     => 'image/gif',
                           'ief'     => 'image/ief',
                           'jpeg'    => 'image/jpeg',
                           'jpg'     => 'image/jpeg',
                           'jpe'     => 'image/jpeg',
                           'tiff'    => 'image/tiff',
                           'tif'     => 'image/tiff',
                           'mcf'     => 'image/vasa',
                           'wbmp'    => 'image/vnd.wap.wbmp',
                           'fh4'     => 'image/x-freehand',
                           'fh5'     => 'image/x-freehand',
                           'fhc'     => 'image/x-freehand',
                           'pnm'     => 'image/x-portable-anymap',
                           'pbm'     => 'image/x-portable-bitmap',
                           'pgm'     => 'image/x-portable-graymap',
                           'ppm'     => 'image/x-portable-pixmap',
                           'rgb'     => 'image/x-rgb',
                           'xwd'     => 'image/x-windowdump',
                           'xbm'     => 'image/x-xbitmap',
                           'xpm'     => 'image/x-xpixmap',
                           'csv'     => 'text/comma-separated-values',
                           'css'     => 'text/css',
                           'htm'     => 'text/html',
                           'html'    => 'text/html',
                           'shtml'   => 'text/html',
                           'js'      => 'text/javascript',
                           'txt'     => 'text/plain',
                           'rtx'     => 'text/richtext',
                           'rtf'     => 'text/rtf',
                           'tsv'     => 'text/tab-separated-values',
                           'wml'     => 'text/vnd.wap.wml',
                           'wmlc'    => 'application/vnd.wap.wmlc',
                           'wmls'    => 'text/vnd.wap.wmlscript',
                           'wmlsc'   => 'application/vnd.wap.wmlscriptc',
                           'xml'     => 'text/xml',
                           'etx'     => 'text/x-setext',
                           'sgm'     => 'text/x-sgml',
                           'sgml'    => 'text/x-sgml',
                           'talk'    => 'text/x-speech',
                           'spc'     => 'text/x-speech',
                           'mpeg'    => 'video/mpeg',
                           'mpg'     => 'video/mpeg',
                           'mpe'     => 'video/mpeg',
                           'qt'      => 'video/quicktime',
                           'mov'     => 'video/quicktime',
                           'viv'     => 'video/vnd.vivo',
                           'vivo'    => 'video/vnd.vivo',
                           'avi'     => 'video/x-msvideo',
                           'movie'   => 'video/x-sgi-movie',
                           'vts'     => 'workbook/formulaone',
                           'vtts'    => 'workbook/formulaone',
                           '3dmf'    => 'x-world/x-3dmf',
                           '3dm'     => 'x-world/x-3dmf',
                           'qd3d'    => 'x-world/x-3dmf',
                           'qd3'     => 'x-world/x-3dmf',
                           'wrl'     => 'x-world/x-vrml');

//--------------------------------------------------------------------------------------------------------
// Functions
//--------------------------------------------------------------------------------------------------------
    function MIMEMAIL($type = '') {
      if($type) $this->type = $type;
    }

    function get_img_type($data) {
      $abc = substr($data, 0, 20);
      if(stristr($abc, 'GIF')) $ftype = 'gif';
      else if(stristr($abc, 'JFIF') || stristr($abc, 'Exif')) $ftype = 'jpeg';
      else if(stristr($abc, 'PNG')) $ftype = 'png';
      else if(stristr($abc, 'FWS') || stristr($abc, 'CWS')) $ftype = 'swf';
      else $ftype = '';

      return $ftype;
    }

    function get_inl_data($html, $m, $css) {
      global $HTTP_HOST;

      if(!$HTTP_HOST) $HTTP_HOST = $_SERVER['HTTP_HOST'];
      $host = 'http://' . ereg_replace('/$', '', $HTTP_HOST);

      for($i = 0; $i < count($m[0]); $i++) {
        $data = $ext = $fname = '';

        if(!preg_match('/^(http|ftp|mailto|javascript)/i', $m[2][$i])) {
          $inlName = $m[2][$i];
          $ext = substr($inlName, strrpos($inlName, '.') + 1);
          $incl = true;

          for($j = 0; $j < count($this->exclude) && $incl; $j++) {
            if(stristr($ext, $this->exclude[$j])) $incl = false;
          }

          if($incl) {
            if($this->documentRoot) {
              $doc_root = $this->documentRoot;

              while(ereg('^\.\./', $inlName)) {
                $inlName = substr($inlName, 3);
                $doc_root = ereg_replace('/[^/]+$', '', $doc_root);
              }
              $fname = "$doc_root/$inlName";
            }
            else $fname = $inlName;

            if($fp = @fopen($fname, 'rb')) {
              $data = fread($fp, filesize($fname));
              fclose($fp);
            }
          }
        }

        if($data) {
          if(!$ext) $ftype = $this->get_img_type($data);
          else $ftype = $ext;

          if($css) $html = str_replace($m[0][$i], ' ' . $m[1][$i] . '(cid:' . $inlName . ')', $html);
          else $html = str_replace($m[0][$i], ' ' . $m[1][$i] . '="cid:' . $inlName . '"', $html);

          if(!$this->inline[$ftype][$inlName]) {
            $this->inline[$ftype][$inlName] = chunk_split(base64_encode($data));
          }
        }
        else if(!preg_match('/^(http|ftp|mailto|javascript)/i', $m[2][$i])) {
          if($css) $html = str_replace($m[0][$i], ' ' . $m[1][$i] . "($host/$inlName)", $html);
          else $html = str_replace($m[0][$i], ' ' . $m[1][$i] . "=\"$host/$inlName\"", $html);
        }
      }
      return $html;
    }

    function check_body() {
      if(preg_match_all('/ (src|background|href)="?([^" >]+)"?/i', $this->body, $m))
        $this->body = $this->get_inl_data($this->body, $m, false);
      if(preg_match_all('/ (url)\(([^\)]+)\)/i', $this->body, $m))
        $this->body = $this->get_inl_data($this->body, $m, true);

      $this->body = preg_replace("/<(table|tr|div)([^>]*)>\r?\n?/i", "<\\1\\2>\r\n", $this->body);
      $this->body = preg_replace("/<\/(table|tr|td|style|script|div|p)>\r?\n?/i", "</\\1>\r\n", $this->body);
    }

    function make_boundaries() {
      $this->uid1 = 'Next_' . strtoupper(md5(uniqid('MIMEmail') . 1));
      $this->uid2 = 'Next_' . strtoupper(md5(uniqid('MIMEmail') . 2));
      $this->uid3 = 'Next_' . strtoupper(md5(uniqid('MIMEmail') . 3));
    }

    function build_header() {
		$this->header = "Subject: " . $this->subject . "\n" .
					  "From: <" . $this->senderMail . ">\n" .
					  "MIME-Version: 1.0\n";

		if($this->replyTo) $this->header .= "Reply-To: " . $this->replyTo . "\n";
		if($this->cc) $this->header .= "Cc: " . $this->cc . "\n";
		if($this->bcc) $this->header .= "Bcc: " . $this->bcc . "\n";
		if($this->saveDir) $this->header .= "Subject: " . $this->subject . "\n";

		switch(strtolower($this->priority)) {
			case 'high': $priority = 1; $ms_priority = 'high'; break;
			case 'low': $priority = 5; $ms_priority = 'low'; break;
			default: $priority = 3; $ms_priority = 'normal'; break;
		}

		/*
		$this->header .= "X-Priority: $priority\n" .
					   "X-MSMail-Priority: $ms_priority\n";
		*/

		if(count($this->attachments)) {
			$this->header .= "Content-Type: multipart/mixed; boundary=\"" . $this->uid1 . "\"\n\n" .
							 "This is a multi-part message in MIME format.\n\n" .
							 "--" . $this->uid1 . "\n";
		}

		if($this->type == 'HTML') {
			/*
			$this->header .= "Content-Type: multipart/alternative; boundary=\"" . $this->uid3 . "\"\n\n" .
							 "--" . $this->uid3 . "\n";
			$this->header .= "Content-Type: text/plain; " .
							 "charset=\"" . $this->charSet . "\"\n" .
							 "Content-Transfer-Encoding: 8bit\n\n" .
							 preg_replace("/(\s*\r?\n\s*){2}/", '\\1\\1', @strip_tags($this->body)) . "\n\n" .
							 "--" . $this->uid3 . "\n";
			*/
			if(count($this->inline)) {
				$this->header .= "Content-Type: multipart/related; boundary=\"" . $this->uid2 . "\"\n\n" .
							   "--" . $this->uid2 . "\n";
			}
		}

		$this->header .= "Content-Type: text/" . (($this->type == 'HTML') ? 'html' : 'plain') . "; " .
					   "charset=\"" . $this->charSet . "\"\n\n";

    }

    function build_footer() {
      $atts = $ftypes = array();
      $this->footer = '';

      foreach($this->attachments as $att) {
        if($att && $att != 'none') {
          if($fp = @fopen($att, 'rb')) {
            $filename = basename(str_replace('\\', '/', $att));
            $file = fread($fp, filesize($att));
            fclose($fp);

            $ext = substr($filename, strrpos($filename, '.') + 1);
            $ftypes[$filename] = $this->mimeTypes[$ext] ? $this->mimeTypes[$ext] : $this->mimeTypes['bin'];
            $atts[$filename] = chunk_split(base64_encode($file));
          }
        }
      }

      if(count($this->inline)) {
        while(list($ftype, $arr) = each($this->inline)) {
          if(count($arr)) while(list($inlName, $data) = each($arr)) {
            $inlType = $this->mimeTypes[$ftype] ? $this->mimeTypes[$ftype] : $this->mimeTypes['bin'];
            $this->footer .= "--" . $this->uid2 . "\n" .
                             "Content-Type: $inlType; name=\"$inlName\"\n" .
                             "Content-ID: <$inlName>\n" .
                             "Content-Disposition: inline; filename=\"$inlName\"\n" .
                             "Content-Transfer-Encoding: base64\n\n" .
                             "$data\n\n";
          }
        }
        $this->footer .= "--" . $this->uid2 . "--\n\n";
      }

      if($this->type == 'HTML') $this->footer .= "--" . $this->uid3 . "--" . ($atts ? "\n\n" : '');

      if(count($atts)) {
        while(list($filename, $file) = each($atts)) {
          $this->footer .= "--" . $this->uid1 . "\n" .
                           'Content-Type: ' . $ftypes[$filename] . "; name=\"$filename\"\n" .
                           "Content-Disposition: attachment; filename=\"$filename\"\n" .
                           "Content-Transfer-Encoding: base64\n\n" .
                           "$file\n\n";
        }
        $this->footer .= "--" . $this->uid1 . "--";
      }
    }

    function send($recipients) {
      $ok = false;

      if($this->created) {
        $this->build_header();
        $mimemail = $this->header . $this->body . "\n\n";
		
        if(is_array($recipients)) {
          $rec = join(', ', $recipients);
          $recipients = $rec;
        }

        if($this->saveDir) {
          $file = $this->saveDir . '/mail_' . ($this->cnt + 1) . '.eml';

          if($fp = @fopen($file, 'w')) {
            $mimemail = "To: $recipients\n" . $mimemail;

            if(!fwrite($fp, $mimemail, strlen($mimemail))) {
              $this->error = "Could not write to \"$file\"\n";
            }
            fclose($fp);
            $ok = true;
          }
          else $this->error = "Could not open \"$file\"\n";
        }
        else {
          $mimemail = "To: $recipients\n" . $mimemail;
          $php_ver = phpversion();
          if(function_exists('ini_get')) $safe_mode = ini_get('safe_mode');
          else $safe_mode = get_cfg_var('safe_mode');

          if($php_ver < '4.0.5' || ($php_ver >= '4.2.3' && $safe_mode)) {
            $ok = mail($recipients, $this->subject, '', $mimemail);
          }
          else {
            $options = ($this->useQueue ? '-odq ' : '') . '-i -f ' . $this->senderMail;
            $ok = mail($recipients, $this->subject, '', $mimemail, $options);
          }

		  if(!$ok) {
            $this->error = 'Error while sending e-mail';
            if(!strstr($recipients, ',')) $this->error .= " to \"$recipients\"\n";
            else $this->error .= "\n";
          }
        }
        $this->cnt++;
        $this->subject = $this->subjectLine;
        $this->body = $this->bodyText;
      }
      else $this->error = "MIME mail not created yet\n";

      return $ok;
    }

    function create() {
      $this->inline = array();
	  
      if(strlen($this->body) < 100) {
        $file = str_replace('\\', '/', $this->body);

        if($fp = @fopen($file, 'r')) {
          $this->body = fread($fp, filesize($file));
          fclose($fp);
          $this->documentRoot= dirname($file);
        }
      }

      if($this->type == 'HTML') $this->check_body();
      $this->make_boundaries();
      $this->build_footer();
      $this->subjectLine = $this->subject;
      $this->bodyText = $this->body;
      $this->created = true;
    }
  }
?>
