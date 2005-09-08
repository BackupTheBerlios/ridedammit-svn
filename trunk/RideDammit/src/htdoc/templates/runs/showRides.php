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
<BODY>

<?php $pageTitle=""; include("header.inc.html"); ?>

<p class="tinyPrint">You can
<A HREF="edit/newRider.php<?php
 echo encodeGet($getVars);
 ?>">
sign up here to use this system</a>
to keep track of your own <?php echo S_EVENTS; ?>.</p>
Show <?php echo S_EVENTS; ?> for:
<FORM METHOD="GET" ACTION="<?php echo $HTTP_SERVER_VARS["PHP_SELF"] ?>">
<INPUT TYPE="HIDDEN" NAME="units" VALUE="<?php echo $units->unitsString() ?>">

<?php
//-----------------------------------------------
//RIDER Dropdown
?>

<SELECT NAME="riderID">
<?php
  echo "<OPTION VALUE='0' ";
  if ( $currentQuery->riderID <= 0 )
     echo "SELECTED";
  echo ">All ".S_PERSONS."</OPTION>\n";

  $riders = new RDrider(DBconnect(), $units);
  $results = $riders->queryAll();
  while ( $riders->parseNextRow($results) )
  {
     echo "<OPTION VALUE='$riders->f_riderID' ";
     if ( $currentQuery->riderID == $riders->f_riderID )
        echo "SELECTED";
     echo ">";
     echo fixFieldForTextArea(
               $riders->f_lastName.", ".$riders->f_firstName);
     echo "</OPTION>\n";
  }
?>
</SELECT>

<?php
//-----------------------------------------------
//Location Dropdown
?>

<SELECT NAME="locID">
<?php
  echo "<OPTION VALUE='0' ";
  echo ">All Locations</OPTION>\n";

  $locs = new RDlocation(DBconnect());
  $results = $locs->queryAll();
  $currType = "";
  while ( $locs->parseNextRow($results) )
  {
     if ( $locs->f_type != $currType )
     {
        echo "<OPTION VALUE='$locs->f_type' ";
        if ( $currentQuery->locationID == $locs->f_type )
           echo "SELECTED";
        echo ">";
        echo fixFieldForTextArea("All ". $locs->f_type);
        echo "</OPTION>\n";
        $currType = $locs->f_type;
     }
     echo "<OPTION VALUE='$locs->f_locationID' ";
     if ( $currentQuery->locationID == $locs->f_locationID )
        echo "SELECTED";
     echo ">";
     echo fixFieldForTextArea(" - ".
               $locs->f_location);
     echo "</OPTION>\n";
  }
?>
</SELECT>

<INPUT TYPE="SUBMIT" VALUE="Go">
&nbsp;&nbsp;&nbsp;<A HREF="edit/editRide.php<?php
 unset($getVars["rideID"]);
 echo encodeGet($getVars);
 ?>"><img border=0 src="newRide.png" width="16" height="16">
 Enter New <?php echo S_EVENT; ?></a>
</FORM>


<?php
//-------------------------------------------------
// MAIN TABLE
?>

<TABLE class="tbLog" width="100%" cellspacing="2px" cellpadding="2px">
<TR>
 <TH class="tbLogHeader">Date</TH>
 <TH class="tbLogHeader"><?php echo S_PERSON; ?></TH>
 <TH class="tbLogHeader">Location</TH>
 <TH class="tbLogHeader">Dist<br><?php
      echo $units->distanceString() ?></TH>
 <TH class="tbLogHeader">Time</TH>
 <TH class="tbLogHeader">Pace<br>min</TH>
 <TH class="tbLogHeader">Effort</TH>
</TR>
<?php
   $rides = new RDride(DBconnect(), $units);
   $result = $rides->queryRides($currentQuery->getWhereStatement(), $start, $num + 1);
   $totalTime = 0;
   $totalDist = 0;
   $numRides = 0; //Not really num rides, used to see if more data is available.
   while ( $rides->parseNextRow($result) )
   {
      $numRides++;
      if ( $numRides > $num )
      {
         break;
      }
      echo "<TR>";
      echo "<TD class=\"tbLogBody\">".
         "<a href=\"showRide.php";
      $tgetvars = $getVars;
      $tgetvars["rideID"]=$rides->f_rideID;
      echo encodeGet($tgetvars).
         "\">".$rides->getDatePart()." ".$rides->getTimePart()."</a>&nbsp;".
         "<img width='10' height='16' src=\"".
         getImgForTemp($rides->f_temperature, $units).
           "\">".
         "<img width='25' height='16' src=\"s".
           $rides->f_sky. ".png\">".
         "<img width='16' height='16' src=\"w".
           $rides->f_wind. ".png\">".
           "</TD>";
      echo "<TD class=\"tbLogBody\">".
            fixFieldForHTML($rides->f_riderID_firstName." ".
                            $rides->f_riderID_lastName,0)."</TD>";
      echo "<TD class=\"tbLogBody\">".
           "<img width='16' height='16' src=\"templates/".
            $RD_template."/loc".
            locationTypeToNumber($rides->f_locationID_type).
            ".png\">&nbsp; ".
            fixFieldForHTML($rides->f_locationID_location,0)."</TD>";
      echo "<TD class=\"tbLogBody\">".
               number_format($rides->f_distance,2)."</TD>";
      echo "<TD class=\"tbLogBody\">$rides->f_time</TD>";
      echo "<TD class=\"tbLogBody\">".
               number_format(60.0/$rides->c_avgSpeed,2)."</TD>";
      echo "<TD class=\"tbLogBody\">$rides->f_effortLevel</TD>";
      echo "</TR>\n";
      echo "<TR>";
      echo "<TD class=\"tbLogBody2\" colspan='7'>".
            fixFieldForHTML(firstLine($rides->f_notes, 70), 0)."&nbsp;</TD>".
           "</TR>\n";
      $totalTime += $rides->f_timeSecs;
      $totalDist += $rides->f_distance;
   }

   echo "<TR>";
   echo "<TD class=\"tbLogFootHead\"><b>Sum/Avg of Displayed:</b></TD>";
   echo "<TD class=\"tbLogFoot\">&nbsp;</TD>";
   echo "<TD class=\"tbLogFoot\">&nbsp;</TD>";
   echo "<TD class=\"tbLogFoot\">".
            number_format($totalDist,2)."</TD>";
   echo "<TD class=\"tbLogFoot\">".
            time_format($totalTime)."</TD>";
   $avgSpd = ($totalTime > 0)?($totalDist*3600/$totalTime):0;
   echo "<TD class=\"tbLogFoot\">".
            number_format(60.0/$avgSpd,2)."</TD>";
   echo "<TD class=\"tbLogFoot\">&nbsp;</TD>";
   echo "</TR>\n";
?>
</TABLE>
<BR>

<?php
//---------------------------------------------------
//Next and back buttons

if ( $start )
{
   $tgetVars = $getVars;
   $tgetVars["start"] = $start - $num;
   if ( $tgetVars["start"] < 0 ) $tgetVars["start"] = 0;
   echo "<a href=\"".$HTTP_SERVER_VARS["PHP_SELF"].encodeGet($tgetVars)."\"><< Back</a>".
        "&nbsp;&nbsp;&nbsp;&nbsp;\n";
}
if ( $numRides > $num )
{
   $tgetVars = $getVars;
   $tgetVars["start"] = $start + $num;
   echo "<a href=\"".$HTTP_SERVER_VARS["PHP_SELF"].encodeGet($tgetVars)."\">Next >></a>";
}

//-----------------------------------------------
//Rider Summaries

?>

<p>
<h3><?php echo S_PERSON; ?> Summaries</h3>
<?php
if ( $currentQuery->getWhereStatement() )
{
   echo "(using query specified at page top)<br>";
}
?>
<!-- <p><a href="riderStats.php">View Graphs</a></p> -->
<table class="tbLog" width="80%" border=0 cellpadding=3 cellSpacing=3>
 <tbody>
  <tr>
   <td class="tbLogHeader"><?php echo S_PERSON; ?></td>
   <td class="tbLogHeader"><?php echo S_EVENTS; ?></td>
   <td class="tbLogHeader">Tot Dist<br><?php echo $units->distanceString() ?></td>
   <td class="tbLogHeader">Avg Dist<br><?php echo $units->distanceString() ?></td>
   <td class="tbLogHeader">Max Dist<br><?php echo $units->distanceString() ?></td>
   <td class="tbLogHeader">Time</td>
   <td class="tbLogHeader">Pace<br>min</td>
        <td class="tbLogHeader">Best Pace<br>min</td>
  </tr>
<?php
$riders = new RDrider(DBConnect(), $units);
$results = $riders->queryAllStats($currentQuery->getWhereStatement());

$riderCount = 0;
$orides = 0;
$otime = 0;
$odist = 0;
$omAvgSpeed = 0;
$omDist = 0;
$oaDist = 0;
while ( $riders->parseNextRow($results) )
{
   $riderCount++;
   echo "<tr>\n";
   echo " <td class=\"tbLogHeader\">".
         fixFieldForHTML($riders->f_firstName." ".$riders->f_lastName,0).
         "</td>\n";
   echo " <td class=\"tbLogBody\">".
         $riders->c_numRides."</td>\n";
   echo " <td class=\"tbLogBody\">".
         number_format($riders->c_totalDist,2)."</td>\n";
   echo " <td class=\"tbLogBody\">".
         number_format($riders->c_aDist,2)."</td>\n";
   echo " <td class=\"tbLogBody\">".
         number_format($riders->c_mDist,2)."</td>\n";
   echo " <td class=\"tbLogBody\">".
         time_format($riders->c_totalTime)."</td>\n";
   echo " <td class=\"tbLogBody\">".
         number_format(60.0/$riders->c_avgSpeed,2)."</td>\n";
   echo " <td class=\"tbLogBody\">".
         number_format(60.0/$riders->c_mAvgSpeed,2)."</td>\n";
   echo "</tr>\n";
   echo "<tr>\n";
   $tdc = getDistanceClass($riders->c_totalDist);
   echo " <td class=\"tbLogBody2\" colspan=\"8\">".
         "You've run ".$tdc->asOutput();
   echo "</td>\n";
   echo "</tr>\n";
   $orides += $riders->c_numRides;
   $otime += $riders->c_totalTime;
   $odist += $riders->c_totalDist;
   $omDist = ( $riders->c_mDist > $omDist ) ? $riders->c_mDist : $omDist;
   $omAvgSpeed = ($riders->c_mAvgSpeed > $omAvgSpeed) ? $riders->c_mAvgSpeed : $omAvgSpeed;
   $oaDist += $riders->c_aDist * $riders->c_numRides;
}
if ( $riderCount > 1 )
{
   echo "<tr>\n";
   echo " <td class=\"tbLogHeader\">".
         "All Together".
         "</td>\n";
   echo " <td class=\"tbLogBody\">".
         $orides."</td>\n";
   echo " <td class=\"tbLogBody\">".
         number_format($odist,2)."</td>\n";
   echo " <td class=\"tbLogBody\">".
                        number_format($oaDist/$orides,2)."</td>\n";
   echo " <td class=\"tbLogBody\">".
                   number_format($omDist,2)."</td>\n";
   echo " <td class=\"tbLogBody\">".
         time_format($otime)."</td>\n";
   echo " <td class=\"tbLogBody\">".
         (($otime > 0 ) ?
         number_format($otime/$odist/60,2) :
         "0.00").
         "</td>\n";
   echo " <td class=\"tbLogBody\">".
                   number_format(60.0/$omAvgSpeed,2)."</td>\n";
   echo "</tr>\n";
   echo "<tr>\n";
   echo " <td class=\"tbLogBody2\" colspan=\"8\">";
   $tdc = getDistanceClass($odist);
   echo "Together you've run ".$tdc->asOutput();
   echo "</td></tr>\n";
}

?>
 </tbody>
</table>
<p class="tinyPrint"><!-- NPS: Taking out rss feed for now
  <a href="rss.php<?php
  echo encodeGet($getVars);
  ?>">RSS Feed for current listing</a>
&nbsp;&nbsp;&nbsp; -->
<?php
   drawUnitsLinks();
?>
</p>
<?php include("footer.inc.html"); ?>
</BODY>
