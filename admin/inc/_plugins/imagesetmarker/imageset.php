<?php
	// search for imagesetmarker div
	$imgrec = getRec("editor_userpages_images", "*", "page_id = ".$this_page['id'], "rank", "");
	$imgnum = count($imgrec);
	if($imgnum > 0){
		$needle = '<hr class="imagesetmarker" title="Imageset Placeholder" />';
		$preg_needle = '/<hr class=\"imagesetmarker\" title=\"Imageset Placeholder\" \/>/';
		$pos = strpos($content, $needle);
		$imgcount = 0;
		$imgindx = 0;
		$imgcontent = "<div class=\"photos\">\n<ul>\n";
		while($pos !== false || $imgindx < $imgnum){
			$image = checkImagePath($imgrec[$imgindx]['image'], IMG_UPLOAD_FOLDER."editor_userpages_images", "");
			$thumb = checkImagePath($imgrec[$imgindx]['thumb'], THM_UPLOAD_FOLDER."editor_userpages_images", "");
			$imgindx++;
			if($image != IMG_UPLOAD_FOLDER && $imgcount < 3){
				// create 3-item image list
				$imgcontent .= "<li style=\"background:url(".WEB_URL.$thumb.") center;\"><a href=\"".WEB_URL.$image."\" title=\"\" class=\"lightbox\"></a></li>\n";
				$imgcount++;
			}
			if($imgcount == 3 || $imgindx >= $imgnum){
				// finalize and replace placeholder
				$imgcontent .= "</ul>\n</div>\n";
				$content = preg_replace($preg_needle, $imgcontent, $content, 1);
				// start over
				$imgcontent = "<div class=\"photos\">\n<ul>\n";
				$imgcount = 0;
				$pos = strpos($content, $needle);
			}
		}
	}
?>
