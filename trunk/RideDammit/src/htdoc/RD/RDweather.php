<?php
/*
Copyright(c) 2006 Nathan P Sharp

This file is part of Ride Dammit!.

Ride Dammit! is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Ride Dammit! is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Ride Dammit!; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function insertWeatherGrab($station, $units)
{
?>
<script language="javascript">
/* 
 Weather Station definitions:
 
 http://www.nws.noaa.gov/tg/siteloc.shtml
 
 */
var rdWeatherReq;

function loadXMLDoc(url) {
    rdWeatherReq = false;
    // branch for native XMLHttpRequest object
    if(window.XMLHttpRequest) {
    	try {
			rdWeatherReq = new XMLHttpRequest();
        } catch(e) {
			rdWeatherReq = false;
        }
    // branch for IE/Windows ActiveX version
    } else if(window.ActiveXObject) {
       	try {
        	rdWeatherReq = new ActiveXObject("Msxml2.XMLHTTP");
      	} catch(e) {
        	try {
          		rdWeatherReq = new ActiveXObject("Microsoft.XMLHTTP");
        	} catch(e) {
          		rdWeatherReq = false;
        	}
		}
    }
	if(rdWeatherReq) {
		rdWeatherReq.onreadystatechange = rdWeatherProcessReqChange;
		rdWeatherReq.open("GET", url, true);
		rdWeatherReq.send("");
	}
}

function rdWeatherProcessReqChange() {
    // only if req shows "loaded"
    if (rdWeatherReq.readyState == 4) {
        // only if "OK"
        if (rdWeatherReq.status == 200) {
           var t = rdWeatherReq.responseText.match(/[ \n\r\t](..[^\d]?)\d*[ \n\r\t](\d\d)\/\d\d[\n\r\t ]A/);
	   document.forms['editRide'].temperature.value = t[2]*<?php 
             echo ( $units->isMetric() )?"1":"9.0/5 + 32" ?>;
	   var sky = "Clear";
	   if ( t[1] == "SKC" ) 
	      sky = "Clear";
	   else if ( t[1] == "FEW" )
	      sky = "Some";
	   else if ( t[1] == "SCT" )
	      sky = "Partly";
	   else if ( t[1] == "BKN" )
	      sky = "Mostly";
	   else if ( t[1] == "OVC" )
	      sky = "Cloudy";
	   else if ( t[1] == "VV" )
	      sky = "Some";
	   document.forms['editRide'].sky.value = sky;
        } else {
            //alert("There was a problem retrieving the XML data:\n" +
            //  req.statusText);
        }
    }
}

//loadXMLDoc("http://weather.noaa.gov/pub/data/observations/metar/stations/KPSK.TXT");
loadXMLDoc("../getObservations.php?location=<?php echo $station ?>");

</script>
<?php
}
