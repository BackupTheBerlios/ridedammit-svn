<?php
/*
Copyright(c) 2003-2004 Nathan P Sharp

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

/*
 This file dumps out the requested query in RSS form.  Accepts
 most of the same HTTP_GET_VARS as the showRides.php page.

 NPS: This functionality doesn't work.  Until I have an actual user
      of an RSS feed, this file won't be fixed.
 */
?>
<?php
 require('RD/RDdbclasses.php');

 //Initialize Variables and queries.
 $getVars = $HTTP_GET_VARS;
 $start = (int)$getVars["start"];
 $num = (int)$getVars["num"];
 if ( ! $num ) $num = 10;
 $currentQuery = new RDquery($getVars);

 unset($getVars);
 $getVars[units] = $units->unitsString();
 if ( $HTTP_GET_VARS[showRidesFor] )
    $getVars[showRidesFor] = $HTTP_GET_VARS[showRidesFor];

 //Create the URL to the real site that does
 //the same search.  Remember to clobber &
 $about = "$RD_baseURL/showRides.php".encodeGet($getVars);

 Header("Content-Type: text/xml");

 //Dump our rss header out

echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
?>
<rss version="2.00">
<channel>
  <title><?php echo htmlentities(S_SITE_TITLE) ?> RSS feed</title>
  <link><?php echo htmlentities($RD_baseURL."/showRides.php") ?></link>
  <description>RSS feed for <?php echo htmlentities(S_SITE_TITLE) ?></description>
<?php

$rides = new RDride(DBconnect(), $units);
$result = $rides->queryRides($currentQuery->getWhereStatement(), $start, $num);
while ( $rides->parseNextRow($result) )
{
   unset($tmpGetVars);
   $tmpGetVars["units"] = $units->unitsString();
   $tmpGetVars["rideID"] = $rides->f_rideID;
   $itemLink = "$RD_baseURL/showRide.php".encodeGet($tmpGetVars);
   $title = $rides->f_riderID_firstName." - ".$rides->getDatePart().", ".
         number_format($units->metricToSetting(
         $rides->f_distance),2)." ".$units->distanceString().", ".
         fixFieldForHtml($rides->f_locationID_location, false);
   echo "<item>\n";
   echo " <title>".htmlentities($title).
         "</title>\n";
   echo " <description>".htmlentities($title.
         fixFieldForHtml("\n\n".$rides->f_notes, true)).
         "</description>\n";
   echo " <link>".htmlentities($itemLink).
         "</link>\n";
   echo "</item>\n";
}

?>
</channel>
</rss>
