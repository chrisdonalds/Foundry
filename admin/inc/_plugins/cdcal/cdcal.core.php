<?php
/*
 * CD-CAL PLUG-IN
 * Author: Chris Donalds <chrisd@navigatormm.com>
 * ----------------------------------------------
 * Populates calendar with database event data
 * Requires: inc/_config/configs.php
 * 			 inc/_core/common.php
 * 			 inc/_lib/db/db_connection.class.php
 * 			 inc/_lib/db/db_common.class.php
 * 			 inc/_lib/db/db_wrapper.class.php
 * 			 call to inc/_plugins/cdcal/cdcal.css in html head
 * 			 JQuery UI Dialog
 *
 * Calendar data table req'd fields: start_date, itemtitle, description
 * Calendar data table optional fields: end_date, start_time, end_time, link, support_doc
 */

/*----------------- USER-CONFIG DATA ------------------*/

$show_weeknums			= false;			// display week numbers in left-most column
$startofweek			= 0;				// 0 = sunday, 1 = monday
$monthlinknames			= true;				// display month buttons as names rather than arrows
$monthlinkbuttons		= true;				// display month links as form buttons
$show_buttonyear		= false;			// display year buttons
$show_longdays			= true;				// display full day name
$show_todayjump			= true;				// display link to today's date
// array of special dates (key = "Y-m-d", value = "Note")
$specialdays			= array("(.*)12-25" => "Christmas");
// array of disabled dates (key = "Y-m-d")
$disableddays			= array("(.*)12-25", "(.*)01-01");
// event links (one set per line: "key" => array("tbl" => "table", "link" => "page", "popup" => true if showing popup))
if(!isset($event_action)){
	$event_action		= array(
						 "events" => array("tbl" => DB_TABLE_PREFIX."events", "flds" => "", "link" => "", "popup" => true, "crit" => " AND published=1"),
						 );
	$event_div_title	= "Calendar Event";
}
// $eventpopupbox is hidden popup box with event content
$eventpopupdiv			= "<div id=\"event{id}\" class=\"calendar-div-hidden\"><p>{event}</p></div>\n";
// $eventpopupanchor is anchor that calls the above div
$eventpopupanchor		= "<a href=\"#top\" onclick=\"document.getElementById('cdcaldialog').innerHTML = document.getElementById('event{id}').innerHTML; jQuery('#cdcaldialog').dialog('open');\">";
if(!isset($calendar_id)) $calendar_id = "cdcal";
if(!isset($calrec_provided)) $calrec_provided = false;

/*------------------------------------------------------*/
/*------------ DO NOT EDIT BELOW THIS LINE -------------*/
/*------------------------------------------------------*/

$max_cells = 41;
$endofweek = ($startofweek + 6) & 7;
$weekdays = array("shortname" => array("Sun", "Mon", "Tues", "Wed", "Thurs", "Fri", "Sat"),
				  "longname"  => array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"));

$curdate 	= date("Y-m-d");
(isset($_GET['m'])) ? $curmonth = $_GET['m'] : $curmonth = date("m");
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
		$crit = "d.start_date LIKE '$curyear-$curmonth-%'".$evac["crit"];
		if($evac["joinflds"]){
			//$table1, $table2, $fields, $joinfields, $jointype, $crit, $order, $limit
			$calrec += getRecJoin($evac["tbl"][0], $evac["tbl"][1], $evac["flds"].", '".$key."' as type", $evac["joinflds"], $evac["jointype"], $crit, "d.start_date", "");
		}else{
			if($evac["flds"] != "") $evac["flds"] = ", ".$evac["flds"];
			$calrec += getRec($evac["tbl"], "*, '".$key."' as type", $crit, "d.start_date", "");
		}
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
	$cells[$i]["event"] 	= $events[$sdate];
	$cells[$i]["istoday"] 	= ($sdate == date("Y-m-d"));
	$cells[$i]["weekday"] 	= date("w", strtotime($sdate));
	$cells[$i]["special"] 	= $specialdays[$sdate];
	if(isset($disableddays[$sdate])) $cells[$i]["disabled"] = true;

	// prevent creating a full week for the next month
	if($cells[$i]["inmonth"] == "next" && $cells[$i]["weekday"] == $startofweek) {
		array_pop($cells);
		$max_cells = $i-1;
		break;
	}
	$day++;
}

/*------------------ BUILD CALENDAR --------------------*/

$basepage =  basename($_SERVER['PHP_SELF']);
($calendar_link != "") ? $calendar_link .= "&" : $calendar_link = "?";
$pagename = WEB_URL.$basepage.$calendar_link."m=%02d&y=%02d";

print "<table class=\"calendar-wrapper width_tablebody\" id=\"$calendar_id\">\n";
print "<tbody>\n";

// header
print "<tr>\n";
print "<td class=\"calendar-month-leftbuttons height_tblrowmonth width_tblcells\">";
if($monthlinknames){
	print "&lt;&nbsp;<a href=\"".sprintf($pagename, $month["prev"]["num"], $month["prev"]["year"])."\" title=\"Back one month\" alt=\"Back one month\">".$month["prev"]["longname"]."</a>";
	if($show_buttonyear) print "<br />&lt;&nbsp;<a href=\"".sprintf($pagename, $month["cur"]["num"], ($month["cur"]["year"]-1))."\" title=\"Back one year\" alt=\"Back one year\">".($curyear-1)."</a>";
}else{
	if($show_buttonyear) print "<a href=\"".sprintf($pagename, $month["cur"]["num"], ($month["cur"]["year"]-1))."\" title=\"Back one year\" alt=\"Back one year\">&lt;&lt;</a>&nbsp;";
	print "<a href=\"".sprintf($pagename, $month["prev"]["num"], $month["prev"]["year"])."\" title=\"Back one month\" alt=\"Back one month\">&lt;</a>";
}
print "</td>\n";

print "<td class=\"calendar-month-header height_tblrowmonth width_tblcellmonthhdr\" colspan=\"".(5 + intval($show_weeknums))."\">";
print "<h2>".$month["cur"]["longname"]."&nbsp;".$month["cur"]["year"]."</h2>";
print "</td>\n";

print "<td class=\"calendar-month-rightbuttons height_tblrowmonth width_tblcells\">";
if($monthlinknames){
	print "<a href=\"".sprintf($pagename, $month["next"]["num"], $month["next"]["year"])."\" title=\"Forward one month\" alt=\"Forward one month\">".$month["next"]["longname"]."</a>&nbsp;&gt;";
	if($show_buttonyear) print "<br /><a href=\"".sprintf($pagename, $month["cur"]["num"], ($month["cur"]["year"]+1))."\" title=\"Forward one year\" alt=\"Forward one year\">".($curyear+1)."</a>&nbsp;&gt;&gt;";
}else{
	print "<a href=\"".sprintf($pagename, $month["next"]["num"], $month["next"]["year"])."\" title=\"Forward one month\" alt=\"Forward one month\">&gt;</a>";
	if($show_buttonyear) print "&nbsp;<a href=\"".sprintf($pagename, $month["cur"]["num"], ($month["cur"]["year"]+1))."\" title=\"Forward one year\" alt=\"Forward one year\">&gt;&gt;</a>";
}
print "</td>\n";
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
$e_link = array();
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
		$today = "-today";
	}else{
		$today = "";
	}

	switch($cell["inmonth"]){
		case "prev":
			print "<td class=\"calendar-cell-all calendar-cell-prev calendar-cell-$cellwidth height_tblcells\" id=\"cell".$cell["id"]."\">";
			print "<div class=\"calendar-cell-allhdr calendar-cell-prevhdr$today\">";
			print date("j", $cell["udt"]);
			print "</div>";
			print "</td>\n";
			break;
		case "cur":
			print "<td class=\"calendar-cell-all calendar-cell-cur calendar-cell-$cellwidth height_tblcells\" id=\"cell".$cell["id"]."\">";
			print "<div class=\"calendar-cell-allhdr calendar-cell-curhdr$today\">";
			print date("j", $cell["udt"]);
			print "</div>";
			($cell["disabled"]) ? $dis = " calendar-cell-disabled" : $dis = "";
			print "<div class=\"calendar-cell-event$today calendar-cell-curdate$dis height_tblcellevent\">";
			if($cell["special"]) print "<span class=\"calendar-cell-special$dis\">".$cell["special"]."</span><br />";

			$eventcount = 0;
			$eventdata = "";

			if(!is_array($cell['event'])) continue;

			foreach($cell["event"] as $event){
				if($event["itemtitle"] != "") {
					$eventcount++;
					$e_id = $cell["id"]."e".$eventcount;
					$evac = $event_action[$event["type"]];

					if($eventcount < 4){
						if($evac["popup"] == false && $evac["link"] != ""){
							// page link
							$e_link["s"] = "<a href=\"".WEB_URL.$evac["link"].$event["code"]."\">"; $e_link["e"] = "</a>";
						}elseif($evac["popup"] == true){
							// popup data
							$descr = preg_replace("/^(<p>)+/i", "", $event["description"]);
							$descr = preg_replace("/(<\/p>)+$/i", "", $descr);
							$eventdata_popup  = "<div class=\"calendar-popup-contents calendar-clearfix\">";
							$eventdata_popup .= "<p class=\"calendar-popup-contents-title\">".$event["itemtitle"]."</p>";
							$eventdata_popup .= "<br/><span class=\"calendar-popup-contents-label\">When</span>";
							$eventdata_popup .= "<span class=\"calendar-popup-contents-data\">".date("F d, Y", strtotime($event["start_date"]));
							if($event["end_date"] != "") $eventdata_popup .= " to ".date("F d, Y", strtotime($event["end_date"]));
							if($event["start_time"] != "") $eventdata_popup .= ", ".date("g:ia", strtotime($event["start_time"]));
							$eventdata_popup .= "</span>";
							if($event["location"] != "" || $event["address"] != "") {
								$where = $event["location"].$event["address"];
								$eventdata_popup .= "<br/><span class=\"calendar-popup-contents-label\">Where</span>";
								$eventdata_popup .= "<span class=\"calendar-popup-contents-data\">";
								$eventdata_popup .= "<a href=\"http://maps.google.ca/maps?f=q&source=s_q&hl=en&geocode=&q=". urlencode($where). "&z=16\" target=\"_blank\">".$where."</a></span>";
							}
							$eventdata_popup .= "<br/><span class=\"calendar-popup-contents-label\">What</span>";
							$eventdata_popup .= "<span class=\"calendar-popup-contents-data\">".$descr."<br />";
							if($event["support_doc"] != "") {
								$filepath = pathinfo($event["support_doc"]);
								$ext = $filepath["extension"];
								$eventdata_popup .= "<br />View: <a href=\"".WEB_URL.FILE_UPLOAD_FOLDER.DB_TABLE_PREFIX."calendar/".$filepath["basename"]."\">";
								switch ($ext){
									case ($ext == "doc" || $ext == "docx"):
										$eventdata_popup .= "Word Document";
										break;
									case ($ext == "xls" || $ext == "xlsx"):
										$eventdata_popup .= "Excel Spreadsheet";
										break;
									case ($ext == "pdf"):
										$eventdata_popup .= "Adobe PDF Document";
										break;
									case ($ext == "jpg" || $ext == "jpeg" || $ext == "gif" || $ext == "png"):
										$eventdata_popup .= "Image/Picture";
										break;
									case ($ext == "wav" || $ext == "mpg" || $ext == "mp3" || $ext == "mp4" || $ext == "wmv" || $ext == "wma"):
										$eventdata_popup .= "Audo/Video File";
										break;
								}
								$eventdata_popup .= "</a>";
							}
							if($event["link"] != "") {
								$eventdata_popup .= "<br /><a href=\"".$event["link"]."\" target=\"_blank\">Visit ".$event["link"]." for more details</a>";
							}
							$eventdata_popup .= "</span>\n";
							if($event["image"] != "" && $event["thumb"] != "") {
								$thumb = checkThumbPath($event["thumb"], THM_UPLOAD_FOLDER.DB_TABLE_PREFIX."events", "");
								$image = checkImagePath($event["image"], IMG_UPLOAD_FOLDER.DB_TABLE_PREFIX."events", "");
								if($thumb != THM_UPLOAD_FOLDER) {
									$eventdata_popup .= "<br/>";
									//$eventdata_popup .= "<div class=\"gallery-img\" style=\"background:url(".WEB_URL.$thumb.") center no-repeat;\">";
									//$eventdata_popup .= "	<a href=\"".WEB_URL.$image."\" rel=\"gallery\" title=\"".$event['itemtitle']."\"></a>";
									//$eventdata_popup .= "</div>";
									$eventdata_popup .= "<div style=\"padding-top: 15px; width: auto; margin: 15px auto; clear: both;\"><a href=\"".WEB_URL.$image."\" rel=\"gallery\" title=\"\"><img src=\"".WEB_URL.$thumb."\" /></a></div>\n";
								}
							}
							$eventdata_popup .= "</div>\n";

							// finalize link
							$eventpopupanchor = preg_replace("/{date}/", $cell["start_date_real"], $eventpopupanchor);
							$e_link["s"] = preg_replace("/{id}/", $e_id, $eventpopupanchor); $e_link["e"] = "</a>";

							// store popup contents in eventdiv array for mass displaying later
							if($eventpopupdiv != "") {
								$eventdiv[$e_id] = preg_replace("/{event}/", $eventdata_popup, $eventpopupdiv);
								$eventdiv[$e_id] = preg_replace("/{id}/", $e_id, $eventdiv[$e_id]);
							}
						}else{
							// plain text (no linking)
							$e_link["s"] = ""; $e_link["e"] = "";
						}

						// finalize display of cell contents
						if($eventdata != "") $eventdata .= "<br>";
						$eventdata = preg_replace("/{date}/", date("M d, Y", $cell['udt']), $eventdata);
						$eventdata .= $e_link["s"].$event["itemtitle"].$e_link["e"];
					}
				}
			}
			// cell data
			print $eventdata;
			print "</div></td>\n";
			break;
		case "next":
			print "<td class=\"calendar-cell-all calendar-cell-next calendar-cell-$cellwidth height_tblcells\" id=\"cell".$cell["id"]."\">";
			print "<div class=\"calendar-cell-allhdr calendar-cell-nexthdr$today height_tblcellhdr\">";
			print date("j", $cell["udt"]);
			print "</div>";
			print "</td>\n";
			break;
	}

	if($cell["weekday"] == $endofweek) print "</tr>\n";
}

if($show_todayjump) {
	print "<tr><td class=\"calendar-todayjump\" colspan=\"".(7 + intval($show_weeknums))."\">";
	print "<a href=\"".sprintf($pagename, date("n"), date("Y"))."\">Return to Today's Date</a>";
	print "</td>\n</tr>\n";
}

print "</tbody>\n";
print "</table>\n";

if(count($eventdiv) > 0) {
	foreach($eventdiv as $ediv) print $ediv;
	print "<div id=\"cdcaldialog\" style=\"display: none\" title=\"".$event_div_title."\"></div>\n";
}

/*------------------------------------------------------*/
?>
