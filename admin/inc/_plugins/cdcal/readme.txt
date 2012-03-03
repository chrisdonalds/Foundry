CD-CAL & CD-CAL MINI PLUG-IN
Web Template 3.0
========================================

-- Inclusion --
$incl = 'cdcal';
        or
$incl = 'cdcalmini';
$cdcal_xcss	= 'defaultsize.css';                        //Name of cd-cal css file

-- Implementation --
$calendar_id     = 'calendar id string";
$event_action	 = array(
                    'events' => array(
                        'tbl' =>    'events table name',
                        'link' =>   'page link url',
                        'popup' =>  true|false (true causes link click to popup jQuery box),
                        'crit' =>   'sql criteria'
                    )
                 );
$event_content	 = '<input type="hidden" name="modday[{day}]" id="modday{day}" value="{time}" />\n';
$noevent_content = '<input type="hidden" name="modday[{day}]" id="modday{day}" value="" />\n';
$event_div_js	 = 'javascript added to divs where event is displayed';
$noevent_div_js	 = 'javascript added to divs where no event is displayed';
$calendar_link	 = '?call-back url query string';
$calrec_provided = true (true if calendar recordset is populated prior to including cdcal.php)

include (SITE_PATH.ADMIN_FOLDER.INC_FOLDER.'_plugins/cdcal/cdcal.php');
    or
include (SITE_PATH.ADMIN_FOLDER.INC_FOLDER.'_plugins/cdcal/cdcalmini.php');

-- Plug-in Settings (in-file) --
$show_weeknums			= false;			// display week numbers in left-most column
$startofweek			= 0;				// 0 = sunday, 1 = monday
$monthlinknames			= false;			// display month links as names rather than arrows or buttons
$monthlinkbuttons		= true;				// display month links as form buttons
$show_buttonyear		= false;			// display year buttons
$show_longdays			= false;			// display full day name

