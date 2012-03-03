/**
GOOGLEMAP/GEOCODER PLUG-IN
MAPSCRIPT.JS script file
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================
 * @author Chris Donalds
 * ---------------------------------------------
 * Sample Calls
 * 
 *  --- HEADER CALL ---
 *
 *  Place this value before calling the header.php file:
 *
 *   - $incl = "googlemap";
 * 
 *  --- CREATING NEW MAPS ---
 *  
 *  <div id="mapbox"></div>		// note: do not change the id "mapbox". it is a reusable placeholder div
 *  <script type="text/javascript" language="JavaScript">
 * 		updateMap(lat, lon, 'balloontag', 'options', zoom, maptype, 'listenfor');
 *  </script>
 *  
 *  Prepares a new map and centers the initial point
 * 	where:
 * 		- lat = latitude of center point (float [-90 to 90]) (ie. Canadian border is roughly at 49.0000 degrees)
 * 		- lon = longitude of center point (float [-180 to 180]) (ie. Kelowna is at -119.4934 degrees)
 * 		- balloontag = HTML content displayed in balloon popup (string)
 * 		- options = optional parameters (string [click, mouseover, mouseout, draggable, click draggable])
 * 		- zoom = zoom factor (integer [0 - 13]) (ie. A good start is 10)
 * 		- maptype = maptype value (G_HYBRID_MAP, G_NORMAL_MAP, G_SATELLITE_MAP)
 * 		- listenfor = event to listen for (string [clickaddpoint, zoom_changed])
 * 	notes:
 * 		- DO NOT change the id of the map box dynamically, it is a reusable placeholder div
 * 		- WIDTH and HEIGHT are required in order to create the map and set an initial center point
 *   	- if the map is not displayed check that the LAT and LON are not reversed
 * 		- if the map div is not displayed check your CSS
 * 		- DO NOT skip parameters, use an empty quote ('') to pass a blank parameter
 * 
 *  --- CREATING NEW POINT ---
 *  
 *  <script type="text/javascript" language="JavaScript">
 *  	createMarker(point, 'html', 'option');
 *  </script>
 *  
 *  Creates a new map point with options
 *  where:
 *  	- point = GMap point (object [new GLatLng(lat, lon)])
 *  	- html = HTML content placed in openInfoWindowHtml popup balloon (string)
 *  	- option = various point options (string [null, click, mouseover, mouseout, draggable])
 *
 *  - OR -
 *
 *  <script type="text/javascript" language="JavaScript">
 *      addCoord(latitude, longitude, balloontag, balloontagstyle, options);
 *  </script>
 *
 *  where:
 *      - latitude and longitude = valid geological coordinates
 *      - balloontag = HTML content
 *      - balloontagstyle = custom CSS styles (leave empty for default)
 *      - options = various point options (string [null, click, mouseover, mouseout, draggable])
 *
 *  --- GETTING ROUTE DIRECTIONS AND DRAWING POLYLINE ---
 *  
 *  call to:
 *  	javascript:getDirections('route-div', 'searchfrom-field', 'searchto-field', 'locale', travelmode, avoidhwys)
 *  
 *  where:
 *  	- locale = en_US, en_CA, fr_CA... (string)
 *  	- travelmode = type of travel (constant = G_TRAVEL_MODE_DRIVING, G_TRAVEL_MODE_WALKING)
 *  	- avoidhwys = true to avoid highways

 *  --- TO GET LAT/LON FROM ADDRESS ---
 *  
 *  Include the geocoder.php file and call this function:
 *  
 *  php call to:
 *      list($precision, $lat, $lon) = geocode($location);
 *
 *  --- MOVE/CREATE POINTER TO/AT ADDRESS ---
 *
 *  Centers the map at the provided address.  Address can be any valid geocodeable address.
 *
 *  call to:
 *      showAddress(address, zoom, newmarker, options);
 *
 *  where:
 *      - address = valid geocodeable address
 *      - zoom = zoom factor
 *      - newmarker = true if you want to create a new pointer, false to move the current pointer
 *      - options = any of 'draggable' to allow marker dragging,
 *                         'showaddress' to display address in balloon
 *                         'updatecoord' to update value attribute of DOM object with id of 'latlon'
 */

var map = null;
var mapobj = null;
var gdir;
var geocoder = null;
var addressMarker;
var cmarker = null;

function createMarker(point, html, options){
    if (options.indexOf('draggable') >= 0){
        // display a draggable point
        var marker = new GMarker(point, {draggable: true});
        marker.enableDragging();
        GEvent.addListener(marker, "drag", function(){
            var latloncoord = cmarker.getPoint().toUrlValue();
            var latloncoordparts = latloncoord.split(",");
            document.getElementById("latlon").value = latloncoord;
            if(document.getElementById("lat")) document.getElementById("lat").value = latloncoordparts[0];
            if(document.getElementById("lon")) document.getElementById("lon").value = latloncoordparts[1];
        });
    }else{
        // just display point
        var marker = new GMarker(point);
    }

    if (html != '') {
        var event = '';
        if (options.indexOf('click') >= 0) {
            event = 'click';
        }else if (options.indexOf('dblclick') >= 0) {
            event = 'dblclick';
        }else if (options.indexOf('mouseover') >= 0) {
            event = 'mouseover';
        }else if (options.indexOf('mousedown') >= 0) {
            event = 'mousedown';
        }else if (options.indexOf('mouseout') >= 0) {
            event = 'mouseout';
        }
        if (event != '') {
            // click to display infowindow
            GEvent.addListener(marker, event, function(){
                marker.openInfoWindowHtml(html, {maxWidth: '250'});
            });
        }else{
            // just display infowindow
            marker.openInfoWindowHtml(html);
        }
    }
    return marker;
}

function addCoord(latitude, longitude, balloontag, balloontagstyle, options){
    var point = new GLatLng(latitude, longitude);
    if (balloontag != "") {
        if(balloontagstyle == '') balloontagstyle = 'width: 150px; height: 100%; text-align: left;';
        cmarker = createMarker(point, '<div style="' + balloontagstyle + '">' + balloontag + '<\/div>', options);
    }else if (options != "") {
        cmarker = createMarker(point, '', options);
    }else{
        cmarker = createMarker(point, '', '');
    }
    map.addOverlay(cmarker);
}

function updateMap(latitude, longitude, balloontag, options, zoom, maptype, listenfor){
    if (GBrowserIsCompatible()) {
        //create map
        document.getElementById("mapbox").style.display = "block";
        var mapobj = document.getElementById("mapbox");
        map = new GMap(mapobj);
        if(zoom == '' || parseInt(zoom) == 0 || isNaN(zoom)) zoom = 11;
        map.setCenter(new GLatLng(latitude, longitude), zoom);

        //change maptype
        if(maptype != "") map.setMapType(maptype);

        //add controls
        map.addControl(new GLargeMapControl());
        map.addControl(new GMapTypeControl());

        //add geocoder object
        geocoder = new GClientGeocoder();

        //add center point
        if(options != 'hidepoint'){
			if(latitude == 0 || longitude == 0){
				latitude = 50.6174650;
				longitude = -116.0722380;
			}
            var point = new GLatLng(latitude, longitude);
            if (balloontag != "") {
                cmarker = createMarker(point, '<div style="width: 150px; height: 100%; text-align: left;">' + balloontag + '<\/div>', options);
            }else if (options != "") {
                cmarker = createMarker(point, '', options);
            }else{
                cmarker = createMarker(point, '', '');
            }
            map.addOverlay(cmarker);
        }
        
        //handle map object listener
        if (listenfor.indexOf('clickaddpoint')>=0) {
            GEvent.addListener(map, "click", function(overlay, latlng){
                if (latlng) {
                    //user clicked on map, get latlng value from point
                    coords = latlng.toString();
                    var coord = coords.split(", ");
                    coord[0] = coord[0].substr(1);
                    coord[1] = coord[1].substr(0, coord[0].length - 1);
                    //add new point and pass options to it
                    var point = new GLatLng(coord[0], coord[1]);
                    var marker = createMarker(point, '', options)
                    map.addOverlay(marker);
                }
            });
        }
        if (listenfor.indexOf('zoom_changed') >= 0) {
            GEvent.addListener(map, "zoomend", function(){
                document.getElementById("zoom").value = map.getZoom();
            });
        }
    }else{
        alert("Sorry, the Google Maps API is not compatible with your browser.");
    }
}	

function getDirections(divbox, fromfield, tofield, locale, travelmode, avoidhwys) {
    gDivBox = document.getElementById(divbox);
    gFrom = document.getElementById(fromfield);
    gTo = document.getElementById(tofield);
    if (gDivBox && gFrom && gTo) {
        if (locale == null)	locale = "en_US";
        if (travelmode == null)	travelmode = G_TRAVEL_MODE_DRIVING;
        gDivBox.innerHTML = "";
        gdir = new GDirections(map, gDivBox);
        gdir.load("from: " + gFrom.value + " to: " + gTo.value, {
                "locale": locale
        }, {
                "travelMode": travelmode
        });
    }
}

function showAddress(address, zoom, newmarker, options) {
    if(newmarker != true) {
        map.removeOverlay(cmarker);
    }
    geocoder.getLatLng(
        address,
        function(point) {
            if (!point) {
                alert(address + " not found");
            } else {
                map.setCenter(point, zoom);
                if(options != "" && options != null){
                    cmarker = createMarker(point, '', options);
                }else{
                    cmarker = createMarker(point, '', '');
                }
                map.addOverlay(cmarker);
                if(options.indexOf('showaddress') >= 0){
                    cmarker.openInfoWindowHtml(address);
                }
                if(options.indexOf('updatecoord') >= 0){
                    var latloncoord = cmarker.getPoint().toUrlValue();
                    var latloncoordparts = latloncoord.split(",");
                    document.getElementById("latlon").value = latloncoord;
                    if(document.getElementById("lat")) document.getElementById("lat").value = latloncoordparts[0];
                    if(document.getElementById("lon")) document.getElementById("lon").value = latloncoordparts[1];
                }
            }
        }
    );
}

function handleErrors(){
    if (gdir.getStatus().code == G_GEO_UNKNOWN_ADDRESS)
        alert("No corresponding geographic location could be found for one of the specified addresses. This may be due to the fact that the address is relatively new, or it may be incorrect.\nError code: " + gdir.getStatus().code);
    else if (gdir.getStatus().code == G_GEO_SERVER_ERROR)
        alert("A geocoding or directions request could not be successfully processed, yet the exact reason for the failure is not known.\n Error code: " + gdir.getStatus().code);
    else if (gdir.getStatus().code == G_GEO_MISSING_QUERY)
        alert("The HTTP q parameter was either missing or had no value. For geocoder requests, this means that an empty address was specified as input. For directions requests, this means that no query was specified in the input.\n Error code: " + gdir.getStatus().code);
    //   else if (gdir.getStatus().code == G_UNAVAILABLE_ADDRESS)  <--- Doc bug... this is either not defined, or Doc is wrong
    //     alert("The geocode for the given address or the route for the given directions query cannot be returned due to legal or contractual reasons.\n Error code: " + gdir.getStatus().code);
    else if (gdir.getStatus().code == G_GEO_BAD_KEY)
        alert("The given key is either invalid or does not match the domain for which it was given. \n Error code: " + gdir.getStatus().code);
    else if (gdir.getStatus().code == G_GEO_BAD_REQUEST)
        alert("A directions request could not be successfully parsed.\n Error code: " + gdir.getStatus().code);
    else alert("An unknown error occurred.");
}

function onGDirectionsLoad(){ 
	// Use this function to access information about the latest load()
	// results.
	
	// e.g.
	// document.getElementById("getStatus").innerHTML = gdir.getStatus().code;
	// and yada yada yada...
}
