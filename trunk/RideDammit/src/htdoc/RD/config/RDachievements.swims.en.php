<?php
/*
Copyright(c) 2004 Nathan P Sharp

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

//The following set is centric around my home town.  Feel free to 
//modify for your locale.  You need to always leave in a 0 distance,
//enter your distances in kilometers, and put them in sorted order.

/*NPS: The following list of distances is from the RideDammit! 
   version of this site and isn't appropriate for swims.  Until
   myself or someone else has time to make a decent list of 
   swimming achievements (English Channel anyone?), this is here
   but the achievements are not printed out in the template for
   SwimDammit. */

$RDdistanceClasses = array(
new RDdistanceClass(0,"Nada",""),
new RDdistanceClass(1, "your first kilometer", ""),
new RDdistanceClass(1.609, "your first mile", ""),
new RDdistanceClass(12,"The Huckleberry Trail, Montgomery County VA", "http://www.montva.com/facilities.php?op=detail&ID=37"),
new RDdistanceClass(20.42,"so far that if you yelled it'd take 1 minute for me to hear you",""),
new RDdistanceClass(36.2,"all the passage in the longest cave in Virginia, Sugar Run Cave System", ""),
new RDdistanceClass(51.75,"from Blacksburg to Ferrum, VA", ""),
new RDdistanceClass(69.7,"from Blacksburg to Roanoke, VA", ""),
new RDdistanceClass(72,"The W&OD (Arlington to Purcellville, VA)","http://www.traillink.com/TL_Active_Pages/TrailSearch/default.asp?Action=DisplayDetails&ID=944&Trail=W%26OD+Railroad+Regional+Park&SearchQueryString=Action%3DStateSearch%26Keyword%3D%26State%3DVA%26Activity%3D%26UseOther%3D%26AS%5FState%3D%26AS%5FCounties%3D%26AS%5FActivities%3D%26AS%5FSurfaces%3D%26AS%5FLength%3D"),
new RDdistanceClass(92,"The New River Trail (Pulaski to Galax, VA)","http://www.traillink.com/TL_Active_Pages/TrailSearch/default.asp?Action=DisplayDetails&ID=589&Trail=New+River+Trail+State+Park&SearchQueryString=Action%3DStateSearch%26Keyword%3D%26State%3DVA%26Activity%3D%26UseOther%3D%26AS%5FState%3D%26AS%5FCounties%3D%26AS%5FActivities%3D%26AS%5FSurfaces%3D%26AS%5FLength%3D"),
new RDdistanceClass(97,"the longest cave in Central America (Chiquibul)", ""),
new RDdistanceClass(100,"a Metric Century",""),
new RDdistanceClass(129.8,"from Blacksburg, VA to Lewisburg, WV",""),
new RDdistanceClass(160.9,"an English Century",""),
new RDdistanceClass(200,"across a hydrogen atom if you were the size of a proton", ""),
new RDdistanceClass(226.4,"from Blacksburg, VA to Charleston, WV", ""),
new RDdistanceClass(299.0,"from Blacksburg, VA to Elkins, WV", ""),
new RDdistanceClass(312.7,"Seattle to Portland Bike Classic",""),
new RDdistanceClass(348.7,"from Blacksburg, VA to Richmond, VA", ""),
new RDdistanceClass(434.2,"from Blacksburg, VA to Washington DC", ""),
new RDdistanceClass(505,"from Blacksburg, VA to Wilderness Rd, Lexington,KY", ""),
new RDdistanceClass(579.4,"all the passage in the longest cave, Mammoth Cave", ""),
new RDdistanceClass(637,"from Blacksburg, VA to Holden Beach, NC", ""),
new RDdistanceClass(643.7, "around a city block 1000 times", ""),
new RDdistanceClass(787.6,"from Blacksburg, VA to New York City", ""),
new RDdistanceClass(1000,"a Thousand Kilometers",""),
new RDdistanceClass(1097.7,"from Blacksburg, VA to Chicago", ""),
new RDdistanceClass(1141.7, "from Blacksburg, VA to Disney World", ""),
new RDdistanceClass(1225.0,"so far that if you yelled, it't take 1 hour for me to hear you",""),
new RDdistanceClass(1338.4, "from Blacksburg, VA to New Orleans", ""),
new RDdistanceClass(1500,"as far as light goes in 5 micro seconds", ""),
new RDdistanceClass(1609,"a Thousand Miles",""),
new RDdistanceClass(1786.0,"quarter way around Pluto",""),
new RDdistanceClass(2145.2,"The Great Rivers (IA to LA)","http://www.adventurecycling.org/routes/greatrivers.cfm"),
new RDdistanceClass(2541.2,"The Western Express (CA to CO)","http://www.adventurecycling.org/routes/westernexpress.cfm"),
new RDdistanceClass(2732.4,"quarter way around the moon",""),
new RDdistanceClass(2954.8,"The Pacific Coast (BC to CA)","http://www.adventurecycling.org/routes/pacificcoast.cfm"),
new RDdistanceClass(3572.0,"half way around Pluto",""),
new RDdistanceClass(3950.9,"The Great Parks (AB to CO)","http://www.adventurecycling.org/routes/greatparks.cfm"),
new RDdistanceClass(4007.3,"The Great Divide (MT to NM)","http://www.adventurecycling.org/routes/greatdivide.cfm"),
new RDdistanceClass(4079.7,"The Atlantic Coast (ME to FL)","http://www.adventurecycling.org/routes/atlanticcoast.cfm"),
new RDdistanceClass(5083.9,"The Southern Tier (CA to FL)","http://www.adventurecycling.org/routes/southerntier.cfm"),
new RDdistanceClass(5464.8,"half way around the moon",""),
new RDdistanceClass(6835.7,"The TransAmerica Trail (VA to OR)","http://www.adventurecycling.org/routes/transamerica.cfm"),
new RDdistanceClass(6912.1,"The Northern Tier (WA to ME)","http://www.adventurecycling.org/routes/northerntier.cfm"),
new RDdistanceClass(7144.0,"around Pluto",""),
new RDdistanceClass(10018,"quarter way around the world (equatorial circumference)",""),
new RDdistanceClass(10930,"around the moon", ""),
new RDdistanceClass(20037,"half way around the world (equatorial circumference)",""),
new RDdistanceClass(40075,"around the world (equatorial circumference)",""),
);

?>
