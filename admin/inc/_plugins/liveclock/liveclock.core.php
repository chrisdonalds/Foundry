<?php
/*
LIVECLOCK PLUG-IN
Web Template 3.0
========================================
Display customizable analog or digital clock
*/

function showClock($id, $gmt, $dstobserved, $class = '', $extrastyle = '', $showSeconds = false){
	if($class == '') $class = 'lc_text';
	if(is_null($gmt)) $gmt = 'null';
	if($extrastyle != '') $extrastyle = ' style="'.$extrastyle.'"';
	$showSecs = (($showSeconds) ? 'true' : 'false');

	if(date("I") == 1 && $dstobserved){
		$dstoffset = 1;	// dst
		switch ($gmt){
			case -4.5:
				$tz = "NDT"; break;
			case -5:
				$tz = "EDT"; break;
			case -6:
				$tz = "CDT"; break;
			case -7:
				$tz = "MDT"; break;
			case -8:
				$tz = "PDT"; break;
		}
	}else{
		$dstoffset = 0;	// st
		switch ($gmt){
			case -4.5:
				$tz = "NT"; break;
			case -5:
				$tz = "EST"; break;
			case -6:
				$tz = "CST"; break;
			case -7:
				$tz = "MST"; break;
			case -8:
				$tz = "PST"; break;
		}
	}
	$caption = "GMT ".$gmt." ".$tz." ".$caption;
	if(intval($gmt) != 0){
		$localgmt = abs(intval(date("O") / 100)) + $dstoffset + $gmt;
	}else{
		$localgmt = 'null';
	}
?>
	<div id="<?=$id?>" class="liveclock_default <?=$class?>"<?=$extrastyle?>>
		<span class="lc_time"></span>
		<span class="lc_caption"><?=$caption?></span>
	</div>
	<script type="text/Javascript">
		setClock(<?=$localgmt?>, '<?=$id?>', <?=$showSecs?>);
	</script>
<?
}
?>


