<?php
/*
Copyright(c) 2003 Nathan P Sharp

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

 unset($getVars);
 $getVars[units] = $units->unitsString();
 if ( $HTTP_GET_VARS[showRidesFor] )
    $getVars[showRidesFor] = $HTTP_GET_VARS[showRidesFor];

 //Create the URL to the real site that does
 //the same search.  Remember to clobber &
 $about = "$RD_baseURL/showRides.php".encodeGet($getVars);

 Header("Content-Type: text/xml");

 //Dump our rss header out

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<?xml-stylesheet href=\"http://www.w3.org/2000/08/w3c-synd/style.css\" type=\"text/css\"?>\n";
?>
<rdf:RDF xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:hr="http://www.w3.org/2000/08/w3c-synd/#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/">
<channel rdf:about="<?php echo $about; ?>">
  <title>Ride Dammit! RSS feed</title>
  <link><?php echo $about; ?></link>
  <description>RSS feed for Ride Dammit!</description>
<?php
/*
  <image>
    <url><?php echo "$conf->baseURL$conf->siteIconSmall"; ?></url>
    <link><?php echo "$conf->baseURL$conf->siteIconURL"; ?></link>
    <description><?php echo $conf->siteTitle; ?></description>
  </image>
*/
?>
  <items>
   <rdf:Seq>
<?php

$rides = new RDride(DBconnect(), $units);
$result = $rides->queryRidesForRider($HTTP_GET_VARS[showRidesFor]);
while ( $rides->parseNextRow($result) )
{
   unset($tmpGetVars);
   $tmpGetVars[units] = $units->unitsString();
   $tmpGetVars[rideID] = $rides->f_rideID;
   echo "<rdf:li rdf:resource=\"$RD_baseURL/showRide.php".
        encodeGet($tmpGetVars).
        "\"/>\n";
}

?>
   </rdf:Seq>

  </items>
 </channel>

<?php

//reset result
mysql_data_seek($result, 0);
while ($rides->parseNextRow($result))
{
   unset($tmpGetVars);
   $tmpGetVars[units] = $units->unitsString();
   $tmpGetVars[rideID] = $rides->f_rideID;
   echo "<item rdf:about=\"$RD_baseURL/showRide.php".
      encodeGet($tmpGetVars).
      "\">\n";
   $title = $rides->f_date.", ".$rides->f_time.", ".
         number_format($rides->f_distance,2).", ".
         fixFieldForRSS($rides->f_locationID_location);
   echo " <title>".$title.
         "</title>\n";
   echo " <description>".$title.
         fixFieldForRSS("\n\n".$rides->f_notes).
         "</description>\n";
   echo " <link>$RD_baseURL/showRide.php".
          encodeGet($tmpGetVars).
         "</link>\n";
/*
   echo " <image>\n";
   echo "  <url>$conf->baseURL$imageURL?image=$data[imageID]&amp;format=thumb</url>\n";
   echo "  <link>$conf->baseURL$topURL?dir=".
        "$data[dirID]&amp;image=$data[imageID]</link>\n";
   echo "  <width>$width</width>\n";
   echo "  <height>$height</height>\n";
   echo "  <description>".fixFieldForHtml($data[name])."</description>\n";
   echo " </image>\n";
*/
   echo "</item>\n";
}

?>

</rdf:RDF>
