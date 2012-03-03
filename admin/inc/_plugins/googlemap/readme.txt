GOOGLEMAP/GEOCODER PLUG-IN
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================

-- Inclusion --
$incl = 'googlemap';

-- Lat, Lon Coordinates --
list($precision, $lat, $lon) = geocode($location);

-- Trigger Example (uses JQuery dialog) --
<a href="#" onclick="jQuery('#mapdialog').dialog('open'); updateMap(<?= $lat?>, <?= $lon?>, '<?= $maphtml?>', 'click', 15, G_NORMAL_MAP, '', 'mapdiv'); return false">

-- JQuery Dialog Div Popup --
<div id="mapdialog" title="Where This Activity is Held..." style="display: none;">
	<div id="mapbox" style="text-align: right; height: 350px; width: 350px;"></div>
</div>

-- See more in mapscript.js file --
