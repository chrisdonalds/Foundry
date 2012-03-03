/**
LIVECLOCK PLUG-IN
Script file
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================
*/
var gmtOffsetHours = null;
var gmtOffsetMinutes = null;
var clockElem = null;
var timerID;
var showSeconds = false;

function setClock(gmt, clockDiv, showSecs){
	if(!isNaN(gmt) && gmt != null){
		gmt = parseFloat(gmt);
		if(parseInt(gmt) != gmt){
			gmtOffsetHours = parseInt(gmt);
			gmtOffsetMinutes = 60 * (gmt - gmtOffsetHours);
		}else{
			gmtOffsetHours = parseInt(gmt);
		}
	}
	showSeconds = (showSecs == true);
	if(document.getElementById(clockDiv)) {
		clockElem = "#" + clockDiv;
	}
}

function updateClock(){
    var currentTime = new Date();
    var currentHours = currentTime.getHours() + gmtOffsetHours;
    var currentMinutes = currentTime.getMinutes() + gmtOffsetMinutes;
    var currentSeconds = currentTime.getSeconds();

    // Pad the minutes and seconds with leading zeros, if required
    currentMinutes = (currentMinutes < 10 ? "0" : "") + currentMinutes;
    currentSeconds = (currentSeconds < 10 ? "0" : "") + currentSeconds;

    // Choose either "AM" or "PM" as appropriate
    var timeOfDay = (currentHours < 12) ? "AM" : "PM";

    // Convert the hours component to 12-hour format if needed
    currentHours = (currentHours > 12) ? currentHours - 12 : currentHours;

    // Convert an hours component of "0" to "12"
    currentHours = (currentHours == 0) ? 12 : currentHours;

    // Compose the string for display
	var currentTimeString;
	if(showSeconds){
		currentTimeString = currentHours + ":" + currentMinutes + ":" + currentSeconds + " " + timeOfDay;
	}else{
		currentTimeString = currentHours + ":" + currentMinutes + " " + timeOfDay;
	}

    $(clockElem + ' .lc_time').html(currentTimeString);
	//clearInterval(timerID);
 }

$(document).ready(function(){
	if(clockElem != null && gmtOffsetHours != null){
		updateClock();
		var interval = ((showSeconds) ? 1000 : 60 * 1000);
		timerID = setInterval('updateClock()', 1000);
	}else if(clockElem != null){
		$(clockElem).html('--:--');
	}
});
