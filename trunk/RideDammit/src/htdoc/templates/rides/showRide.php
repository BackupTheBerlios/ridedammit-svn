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
?>
<body>
<?php $pageTitle = S_EVENT." Details"; include("header.inc.html"); ?>
<?php
if ( $msg )
{
   echo "<p class='errors'>".fixFieldForHTML($msg,0)."</p>\n";
}
?>
<center>
<table class="tbLog" width="95%" cellspacing="2" cellpadding="2">
                <tbody>
                   <tr>
                 <td class="tbLogHeader">Date</td>
                     <td class="tbLogBody">
                     <?php echo $ride->getDatePart()." ".
                                $ride->getTimePart() ?></td>
                 <td class="tbLogHeader">Time</td>
                     <td class="tbLogBody"><?php
                        echo $ride->f_time ?></td>
                   </tr>
                   <tr>
                 <td class="tbLogHeader"><?php echo S_PERSON; ?></td>
                     <td class="tbLogBody"><?php
                        echo fixFieldForHTML(
                               $ride->f_riderID_firstName." ".
                               $ride->f_riderID_lastName, 0) ?></td>
                 <td class="tbLogHeader">Distance</td>
                     <td class="tbLogBody"><?php
                       echo number_format($ride->f_distance,2)." ".
                            $units->distanceString() ?></td>
                   </tr>
                   <tr>
                 <td class="tbLogHeader">Bike</td>
                     <td class="tbLogBody"><?php
                        echo fixFieldForHTML($ride->f_bikeID_bike,0) ?></td>
                 <td class="tbLogHeader">Avg Speed</td>
                     <td class="tbLogBody"><?php
                        echo number_format($ride->c_avgSpeed,2)." ".
                             $units->velocityString() ?></td>
                   </tr>
                   <tr>
                 <td class="tbLogHeader">Location</td>
                     <td class="tbLogBody"><?php
                        echo "<img width='16' height='16' src=\"".
                           "templates/".$RD_template."/loc".
                           locationTypeToNumber($ride->f_locationID_type).
                           ".png\">&nbsp; ".
                           fixFieldForHTML($ride->f_locationID_location,0).
                           " (".$ride->f_locationID_type.")" ?></td>
                 <td class="tbLogHeader">Max Speed</td>
                     <td class="tbLogBody"><?php
                        echo number_format($ride->f_maxSpeed,2)." ".
                             $units->velocityString() ?></td>
                   </tr>
                   <tr>
                 <td class="tbLogHeader">Effort</td>
                     <td class="tbLogBody"><?php
                        echo $ride->f_effortLevel ?></td>
                 <td class="tbLogHeader">Temp</td>
                     <td class="tbLogBody"><?php
                        echo "<img width='10' height='16' src=\"".
                          getImgForTemp($ride->f_temperature, $units).
                          "\">&nbsp; ".
                           number_format($ride->f_temperature,1).
                             $units->tempString() ?></td>
                   </tr>
                   <tr>
                 <td class="tbLogHeader">Wind</td>
                     <td class="tbLogBody"><?php
                       echo "<img width='16' height='16' src=\"w".
                             $ride->f_wind. ".png\">&nbsp; ".
                             $ride->f_wind ?></td>
                 <td class="tbLogHeader">Sky</td>
                     <td class="tbLogBody"><?php
                        echo "<img width='25' height='16' src=\"s".
                         $ride->f_sky. ".png\">&nbsp; ".
                        $ride->f_sky ?></td>
                   </tr>
                <tr>
               <td class="tbLogBody2" colspan="4"><?php
                  echo fixFieldForHTML($ride->f_notes,1) ?>&nbsp;</td>
              </tr>

  </tbody>
</table>
</center>
<p>
 <a href="edit/editRide.php<?php echo encodeGet($getVars) ?>">
        Edit this <?php echo S_EVENT; ?></a>
        &nbsp;&nbsp;&nbsp;&nbsp;
 <a href="showRides.php<?php echo encodeGet($getVars) ?>">Back to
      <?php echo S_EVENTS; ?></a> </p>
<p>
<?php
drawUnitsLinks();
echo "</p>\n";

include("footer.inc.html"); ?>

</body>
