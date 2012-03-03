<?php
/*
 * CD-CAL MINI PLUG-IN
 * Author: Chris Donalds <chrisd@navigatormm.com>
 * ----------------------------------------------
 * Populates calendar with database event data
 * Requires: inc/_config/configs.php
 * 			 inc/_core/common.php
 * 			 inc/_lib/db/db_connection.class.php
 * 			 inc/_lib/db/db_common.class.php
 * 			 inc/_lib/db/db_wrapper.class.php
 * 			 call to inc/_plugins/cdcal/cdcal.css in html head
 *
 * Calendar data table req'd fields: start_date, start_time, itemtitle, description
 */

/*----------------- USER-CONFIG DATA ------------------*/

$show_weeknums			= false;			// display week numbers in left-most column
$startofweek			= 0;				// 0 = sunday, 1 = monday
$monthlinknames			= false;			// display month links as names rather than arrows or buttons
$monthlinkbuttons		= true;				// display month links as form buttons
$show_buttonyear		= false;			// display year buttons
$show_longdays			= false;			// display full day name
// event links (one set per line: "key" => array("tbl" => "table", "link" => "page", "popup" => true if showing popup))
if(!isset($event_action)){
	$event_action		= array(
						 "events" => array("tbl" => DB_TABLE_PREFIX."events", "link" => "", "popup" => true, "crit" => " AND published=1"),
						 );
}
if(!isset($event_content)) $event_content = "<input type=\"hidden\" name=\"day[{id}]\" id=\"day{id}\" value=\"*\"/>\n";
if(!isset($noevent_content)) $noevent_content = "<input type=\"hidden\" name=\"day[{id}]\" id=\"day{id}\" value=\"\"/>\n";
if(!isset($calendar_id)) $calendar_id = "cdcalmini";
if(!isset($calrec_provided)) $calrec_provided = false;

/*------------------------------------------------------*/
/*------------ DO NOT EDIT BELOW THIS LINE -------------*/
/*------------------------------------------------------*/

$max_cells = 41;
$endofweek = ($startofweek + 6) & 7;
$weekdays = array("shortname" => array("S", "M", "T", "W", "T", "F", "S"),
				  "longname"  => array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"));

$curdate 	= date("Y-m-d");
if(!isset($curmonth))
	(isset($_GET['m'])) ? $curmonth = $_GET['m'] : $curmonth = date("m");
if(!isset($curyear))
	(isset($_GET['y'])) ? $curyear = $_GET['y'] : $curyear = date("Y");
if($curmonth > 1){
	$prevmonth = $curmonth - 1; $prevyear = $curyear;
}else{
	$prevmonth = 12; $prevyear = $curyear - 1;
}
if($curmonth < 12){
	$nextmonth = $curmonth + 1; $nextyear = $curyear;
}else{
	$nextmonth = 1; $nextyear = $curyear + 1;
}
$month 			= array("cur" => array(), "prev" => array(), "next" => array());

/*---------------- DB CALENDAR EVENTS ------------------*/

// get calendar events

if(!$calrec_provided){
	$calrec = array();
	foreach($event_action as $key => $evac){
		$crit = "start_date LIKE '$curyear-$curmonth-%'".$evac["crit"];
		$calrec += getRec($evac["tbl"], "*, '".$key."' as type", $crit, "start_date", "");
	}
}

// reassociate event keys as dates, use a counter to ensure unique keys for multiple events on same date
$counter = 0;
foreach ($calrec as $key => $value){
	$dt = $value['start_date'];
	$events[$dt][$counter] = $value;
	$counter++;
}

//printr($events);
/*-------------------- MONTH ARRAYS --------------------*/

$month["cur"]["num"] = $curmonth;
$month["cur"]["year"] = $curyear;
$month["cur"]["shortname"] = date("M", mktime(0, 0, 0, $curmonth, 1, $curyear));
$month["cur"]["longname"] = date("F", mktime(0, 0, 0, $curmonth, 1, $curyear));
$month["cur"]["startudt"] = mktime(0, 0, 0, $curmonth, 1, $curyear);
$month["cur"]["endudt"] = mktime(0, 0, 0, $curmonth, date("t", mktime(0,0,0,($curmonth+1),0,$curyear)), $curyear);
$month["cur"]["startday"] = 1;
$month["cur"]["endday"] = date("t", $month["cur"]["endudt"]);
$month["cur"]["startdow"] = date("w", $month["cur"]["startudt"]);
$month["cur"]["enddow"] = ($month["cur"]["startdow"] + $month["cur"]["endday"] - 1) % 7;
$month["cur"]["startcell"] = $month["cur"]["startdow"];
$month["cur"]["endcell"] = ($month["cur"]["startdow"] + $month["cur"]["endday"]) - 1;

$month["prev"]["num"] = $prevmonth;
$month["prev"]["year"] = $prevyear;
$month["prev"]["shortname"] = date("M", mktime(0, 0, 0, $prevmonth, 1, $prevyear));
$month["prev"]["longname"] = date("F", mktime(0, 0, 0, $prevmonth, 1, $prevyear));
$month["prev"]["endudt"] = mktime(0, 0, 0, $curmonth, 0, $curyear);
$month["prev"]["endday"] = date("t", $month["prev"]["endudt"]);
$month["prev"]["enddow"] = date("w", $month["prev"]["endudt"]);
$month["prev"]["endcell"] = $month["prev"]["enddow"];

$month["next"]["num"] = $nextmonth;
$month["next"]["year"] = $nextyear;
$month["next"]["shortname"] = date("M", mktime(0, 0, 0, $nextmonth, 1, $nextyear));
$month["next"]["longname"] = date("F", mktime(0, 0, 0, $nextmonth, 1, $nextyear));
$month["next"]["startudt"] = mktime(0, 0, 0, $nextmonth, 1, $nextyear);
$month["next"]["startday"] = 1;
$month["next"]["startdow"] = date("w", $month["next"]["startudt"]);
$month["next"]["startcell"] = $month["cur"]["endcell"] + 1;

/*--------------- BUILD CELL CONTENTS ---------------------*/

$cells = array();
$day = 1 - ($month["cur"]["startdow"] + $startofweek);	// start -x number of days before start of month

for($i = 0; $i <= $max_cells; $i++) {
	$cells[$i]["id"] = $i;
	$cells[$i]["udt"] = mktime(0, 0, 0, $month["cur"]["num"], $day, $month["cur"]["year"]);
	$sdate = date("Y-m-d", $cells[$i]["udt"]);
	$cells[$i]["start_date"] = $sdate;
	$cells[$i]["start_date_real"] = date("M j, Y", strtotime($sdate));
	if($day < 1){
		// prev
		$cells[$i]["inmonth"] = "prev";
	}elseif($day > $month["cur"]["endday"]){
		// next
		$cells[$i]["inmonth"] = "next";
	}else{
		// current
		$cells[$i]["inmonth"] = "cur";
	}
	$cells[$i]["event"]		= array();
	$cells[$i]["event"] 	= $events[$sdate];
	$cells[$i]["istoday"] 	= ($sdate == date("Y-m-d"));
	$cells[$i]["weekday"] 	= date("w", strtotime($sdate));

	// prevent creating a full week for the next month
	if($cells[$i]["inmonth"] == "next" && $cells[$i]["weekday"] == $startofweek) {
		array_pop($cells);
		$max_cells = $i-1;
		break;
	}
	$day++;
}

/*------------------ BUILD CALENDAR --------------------*/

$basepage =  $_SERVER['PHP_SELF'];
($calendar_link != "") ? $calendar_link .= "&" : $calendar_link = "?";
$pagename = $basepage.$calendar_link."m=%02d&y=%02d";

print "<table class=\"calendar-wrapper width_tablebody\" id=\"$calendar_id\">\n";
print "<tbody>\n";

// header
print "<tr>\n";
print "<td class=\"calendar-month-leftbuttons height_tblrowmonth width_tblcells\">";
if($monthlinknames){
	print "&lt;&nbsp;<a href=\"".sprintf($pagename, $month["prev"]["num"], $month["prev"]["year"])."\" title=\"Back one month\" alt=\"Back one month\">".$month["prev"]["longname"]."</a>";
	if($show_buttonyear) print "<br />&lt;&nbsp;<a href=\"".sprintf($pagename, $month["cur"]["num"], ($month["cur"]["year"]-1))."\" title=\"Back one year\" alt=\"Back one year\">".($curyear-1)."</a>";
}elseif($monthlinkbuttons){
	if($show_buttonyear) print "<input type=\"button\" name=\"prevyear\" onclick=\"this.form.action='".sprintf($pagename, $month["cur"]["num"], ($month["cur"]["year"]-1))."'; submitform();\" title=\"Back one year\" alt=\"Back one year\" value=\"&lt;&lt;\"/>&nbsp;";
	print "<input type=\"button\" name=\"prevmnth\" onclick=\"this.form.action='".sprintf($pagename, $month["prev"]["num"], $month["prev"]["year"])."'; submitform();\" title=\"Back one month\" alt=\"Back one month\" value=\"&lt;\"/>";
}else{
	if($show_buttonyear) print "<a href=\"".sprintf($pagename, $month["cur"]["num"], ($month["cur"]["year"]-1))."\" title=\"Back one year\" alt=\"Back one year\">&lt;&lt;</a>&nbsp;";
	print "<a href=\"".sprintf($pagename, $month["prev"]["num"], $month["prev"]["year"])."\" title=\"Back one month\" alt=\"Back one month\">&lt;</a>";
}
print "</td>\n";

print "<td class=\"calendar-month-header height_tblrowmonth width_tblcellmonthhdr\" colspan=\"".(5 + intval($show_weeknums))."\">";
print "<span>".$month["cur"]["longname"]."&nbsp;".$month["cur"]["year"]."</span>";
print "</td>\n";

print "<td class=\"calendar-month-rightbuttons height_tblrowmonth width_tblcells\">";
if($monthlinknames){
	print "<a href=\"".sprintf($pagename, $month["next"]["num"], $month["next"]["year"])."\" title=\"Forward one month\" alt=\"Forward one month\">".$month["next"]["longname"]."</a>&nbsp;&gt;";
	if($show_buttonyear) print "<br /><a href=\"".sprintf($pagename, $month["cur"]["num"], ($month["cur"]["year"]+1))."\" title=\"Forward one year\" alt=\"Forward one year\">".($curyear+1)."</a>&nbsp;&gt;&gt;";
}elseif($monthlinkbuttons){
	print "<input type=\"button\" name=\"nextmnth\" onclick=\"this.form.action='".sprintf($pagename, $month["next"]["num"], $month["next"]["year"])."'; submitform();\" title=\"Forward one month\" alt=\"Forward one month\" value=\"&gt;\"/>";
	if($show_buttonyear) print "&nbsp;<input type=\"button\" name=\"nextyear\" onclick=\"this.form.action='".sprintf($pagename, $month["cur"]["num"], ($month["cur"]["year"]+1))."';  submitform();\" title=\"Forward one year\" alt=\"Forward one year\" value=\"&gt;&gt;\"/>";
}else{
	print "<a href=\"".sprintf($pagename, $month["next"]["num"], $month["next"]["year"])."\" title=\"Forward one month\" alt=\"Forward one month\">&gt;</a>";
	if($show_buttonyear) print "&nbsp;<a href=\"".sprintf($pagename, $month["cur"]["num"], ($month["cur"]["year"]+1))."\" title=\"Forward one year\" alt=\"Forward one year\">&gt;&gt;</a>";
}
print "</tr>\n\n";

// weekday bar
print "<tr>\n";
if($show_weeknums){
	print "<td class=\"calendar-weekhdr-weeknum height_tblrowweekday width_tblcells\">";
	print "&nbsp;</td>\n";
}
for($i=0; $i<7; $i++){
	print "<td class=\"calendar-weekhdr-weekday height_tblrowweekday width_tblcells\">";
	$dow = ($startofweek + $i) % 7;
	($show_longdays) ? print $weekdays["longname"][$dow] : print $weekdays["shortname"][$dow];
	print "</td>\n";
}
print "</tr>\n\n";

// cells
foreach($cells as $cell){
	if($cell["weekday"] == $startofweek) print "<tr>\n";

	// print week number
	if($show_weeknums && $cell["weekday"] == $startofweek) {
		$cellwidth = "eighthwidth";
		$wk = date("W", $cell["udt"]);
		print "<td class=\"calendar-cell-weeknum calendar-cell-$cellwidth height_tblcellweeknum width_tblcells\">$wk</td>";
	}else{
		$cellwidth = "seventhwidth";
	}

	// print cell contents
	if($cell["istoday"]) {
		$today = " calendar-cell-curhdr-today";
	}else{
		$today = "";
	}

	switch($cell["inmonth"]){
		case "prev":
			print "<td class=\"calendar-cell-all calendar-cell-prev calendar-cell-$cellwidth height_tblcells\"\">";
			print "<div class=\"calendar-cell-allhdr calendar-cell-prevhdr$today height_tblcellhdr\">";
			print date("j", $cell["udt"]);
			print "</div>";
			print "</td>\n";
			break;
		case "cur":
			$dayofmonth = date("j", $cell["udt"]);
			$eventid = "";
			foreach($cell["event"] as $event){
				$eventid = $event["id"];
				$evtime	 = $event["start_time"];
			}
			print "<td class=\"calendar-cell-all calendar-cell-cur calendar-cell-$cellwidth height_tblcells\">";
			if($eventid != "") {
				$evc = str_replace(array("{id}", "{day}", "{time}"), array($eventid, $dayofmonth, $evtime), $event_content);
				$evd = str_replace(array("{id}", "{day}", "{time}"), array($eventid, $dayofmonth, $evtime), $event_div_js);
				print "<div id=\"cell{$dayofmonth}\" class=\"calendar-cell-allhdr calendar-cell-curhdr-sel$today height_tblcellhdr\"{$evd}>";
				print $dayofmonth;
				print $evc;
			}else{
				$evc = str_replace(array("{id}", "{day}", "{time}"), array($eventid, $dayofmonth, $evtime), $noevent_content);
				$evd = str_replace(array("{id}", "{day}", "{time}"), array($eventid, $dayofmonth, $evtime), $noevent_div_js);
				print "<div id=\"cell{$dayofmonth}\" class=\"calendar-cell-allhdr calendar-cell-curhdr-nosel$today height_tblcellhdr\"{$evd}>";
				print $dayofmonth;
				print $evc;
			}
			print "</div>";
			print "</td>\n";
			break;
		case "next":
			print "<td class=\"calendar-cell-all calendar-cell-next calendar-cell-$cellwidth height_tblcells\"\">";
			print "<div class=\"calendar-cell-allhdr calendar-cell-nexthdr$today height_tblcellhdr\">";
			print date("j", $cell["udt"]);
			print "</div>";
			print "</td>\n";
			break;
	}

	if($cell["weekday"] == $endofweek) print "</tr>\n";
}

print "</tbody>\n";
print "</table>\n";

/*------------------------------------------------------*/
?>
