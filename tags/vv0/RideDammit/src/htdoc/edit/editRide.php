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

//Stupid PHP includes:
chdir("..");
require("RD/RDdbclasses.php");

$rideID=(int)$HTTP_GET_VARS["rideID"];

unset($getVars);
$getVars["units"]=$units->unitsString();

$newRide = ($rideID == 0);

$ride = new RDride(DBConnect(), $units);

//Check for form submit and handle it here
if ( $HTTP_POST_VARS["submit"] )
{
   //Fill in the info they submitted.
   $ride->f_riderID = (int)$HTTP_POST_VARS["rider"];
   $ride->f_date = unFixQuotes($HTTP_POST_VARS["date"]). " " .
                  unFixQuotes($HTTP_POST_VARS["hour"]);
   $ride->f_distance = (double)$HTTP_POST_VARS["distance"];
   $ride->f_maxSpeed = (double)$HTTP_POST_VARS["maxSpeed"];
   $ride->f_time = unFixQuotes($HTTP_POST_VARS["time"]);
   $ride->f_locationID = (int)$HTTP_POST_VARS["location"];
   $ride->f_temperature = (double)$HTTP_POST_VARS["temperature"];
   $ride->f_wind = unFixQuotes($HTTP_POST_VARS["wind"]);
   $ride->f_sky = unFixQuotes($HTTP_POST_VARS["sky"]);
   $ride->f_effortLevel = unFixQuotes($HTTP_POST_VARS["effort"]);
   $ride->f_bikeID = (int)$HTTP_POST_VARS["bike"];
   $ride->f_notes = unFixQuotes($HTTP_POST_VARS["notes"]);

   //Check for password
   $fixedPassword = unFixQuotes($HTTP_POST_VARS["password"]);
   $riderTest = new RDrider(DBConnect(),$units);
   if ( ! $riderTest->checkPerms($ride->f_riderID, $fixedPassword) )
   {
      $errMsg = "Invalid Password";
   }

   if ( ! $errMsg && $HTTP_POST_VARS["bike"] == 0 )
   {
      //New Bike, create it
      $newBike = new RDbike(DBConnect());
      $newBike->f_bike = unFixQuotes($HTTP_POST_VARS["newBike"]);
      $newBike->f_computerSetting = (int)$HTTP_POST_VARS["computerSetting"];
      $newBike->f_riderID = $ride->f_riderID;
      $errMsg = $newBike->insertNew();
      $ride->f_bikeID = $newBike->f_bikeID;
   }
   if ( ! $errMsg && $HTTP_POST_VARS["location"] == 0 )
   {
      $newLocation = new RDlocation(DBConnect());
      $newLocation->f_location = unFixQuotes($HTTP_POST_VARS["newLocation"]);
      $newLocation->f_description = unFixQuotes($HTTP_POST_VARS["newDescription"]);
      $newLocation->f_type = unFixQuotes($HTTP_POST_VARS["newLocationType"]);
      $errMsg = $newLocation->insertNew();
      $ride->f_locationID = $newLocation->f_locationID;
   }

   if ( ! $errMsg )
   {

      if( $newRide )
      {
         $errMsg = $ride->insertNew(unFixQuotes($HTTP_POST_VARS["password"]));
         if ( ! $errMsg )
         {
            $normMsg = "Ride Inserted";
            $rideID = $ride->f_rideID;
            $newRide = 0;
         }
      }
      else
      {
         $ride->f_rideID = $rideID;
         $errMsg = $ride->update($password);
         if ( ! $errMsg )
            $normMsg = "Ride Updated";
      }
   }
}

//Get the data from the db if we aren't doing a new
//ride and there was no error (on error, we want the
//values the user entered before to still be present)
if ( ! $newRide && ! $errMsg )
{
   $errMsg = $ride->getDB_id($rideID);

   if ( ! $ride->f_rideID || $errMsg )
   {
      //NPS: What o what to do with these people?  How about
      //we send them back to the original page.
      Header("Location:$RD_baseURL/showRides.php".encodeGet($getVars));
      return;
   }
}
//On non-new rides the rest of the time, just fill in the rider.
else if ( ! $newRide )
{
   $ride->f_rideID = $rideID;
   $subErrMsg = $ride->getRiderInfo();
   if ( $subErrMsg )
   {
      //NPS: report this?
      error_log($subErrMsg, 0);
   }
}

if ( $ride->f_rideID )
   $getVars["rideID"] = $ride->f_rideID;

if ( $newRide )
{
   if ( ! $ride->f_date )
      $ride->f_date = date("Y-m-d H:i");
   if ( ! $ride->f_time )
      $ride->f_time = "00:00:00";
}

$bikes = getAllObjects(new RDbike(DBConnect()));
$locations = getAllObjects(new RDlocation(DBConnect()));
$riders = getAllObjects(new RDrider(DBConnect(), $units));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>Ride Dammit! <?php echo $newRide?"New Ride":"Ride Edit" ?></title>
  <link rel="stylesheet" href="<?php echo $RD_baseURL ?>/default.css">
  <meta http-equiv="content-type"
 content="text/html; charset=ISO-8859-1">
</head>
<body>
<table border=0 cellpadding=0 cellspacing=0>
 <tr>
  <td><img src="../RideDammit.png" width="64" height="64"></td>
  <td><H1>Ride Dammit! <?php echo $newRide?"New Ride":"Ride Edit" ?></H1></td>
 </tr>
</table>
<?php
if ( $errMsg )
{
   echo "<p class=\"errors\">Error: $errMsg</p>\n";
}
if ( $normMsg )
{
   echo "<p>".$normMsg."</p>\n";
}
?>
<p class="tinyPrint">You are entering data in <?php echo $units->unitsString() ?>
  units.  <a href="<?php
   echo $HTTP_SERVER_VARS["PHP_SELF"];
   $tgetVars = $getVars;
   $opposite = $units->opposite();
   $tgetVars["units"] = $opposite->unitsString();
   echo encodeGet($tgetVars);
   echo "\">Use ".$opposite->unitsString()." instead.</a>";
   ?>
</p>
<form method="post" action="<?php
   echo $HTTP_SERVER_VARS["PHP_SELF"].encodeGet($getVars) ?>">

  <table class="tbEdit" width="95%">
                       <tbody>
                         <tr>
                           <td class="tbEditHeader" width="15%">Rider<br>
                           </td>
                           <td class="tbEditBody" width="25%"><?php
      if ( $newRide )
      {
         echo "<select name=\"rider\">\n";
         foreach ( $riders as $rider )
         {
            echo "<option value=\"$rider->f_riderID\"";
            if ( $ride->f_riderID == $rider->f_riderID )
               echo " selected";
            echo ">".
              fixFieldForTextArea($rider->f_lastName.", ".$rider->f_firstName).
              "</option>\n";
         }
         echo "</select>\n";
      }
      else
      {
         echo "<input type=\"hidden\" name=\"rider\" value=\"".
            $ride->f_riderID."\">";
         echo fixFieldForHTML($ride->f_riderID_firstName." ".
               $ride->f_riderID_lastName,0);
      }
        ?>
                           </td>
                           <td class="tbEditHeader" width="10%">
                              Password
                           </td>
                           <td class="tbEditBody" width="50%">
                              <input type="password" name="password"
                                    size="12" maxlen="30">
                           </td>
                         </tr>
                         <tr>
                           <td class="tbEditHeader">Date<br>
                           </td>
                           <td class="tbEditBody"><input
 type="text" name="date" value="<?php
                              echo $ride->getDatePart()
                           ?>" size="12" maxlen="15"><br>
                           </td>
                           <td class="tbEditHeader">Hour<br>
                           </td>
                           <td class="tbEditBody"><input
 type="text" name="hour" value="<?php
                              echo $ride->getTimePart()
                           ?>" size="10" maxlen="12"><br>
                           </td>
                         </tr>
                         <tr>
                           <td class="tbEditHeader">Bike<br>
                           </td>
                           <td class="tbEditBody">

        <select name="bike">
        <option value="0">New Bike --&gt;</option>
        <?php
          foreach ( $bikes as $bike )
          {
             echo "<option value=\"$bike->f_bikeID\"";
             if ( $ride->f_bikeID == $bike->f_bikeID )
                echo " selected";
             echo ">".
               fixFieldForTextArea($bike->f_bike).
               "</option>\n";
          }
        ?>
        </select>
                    <br>
                           </td>
                           <td class="tbEditBody2" rowspan="1" colspan="2">


        <table class="tbEditSubTable" cellpadding="2" cellspacing="0" border="0" width="100%">
                        <tbody>
                          <tr>
                            <td class="tbEditBody2" width="25%">New Bike</td>
                            <td class="tbEditBody2"><input type="text"
 name="newBike" value="" size="25" maxlen="100"><br>
                            </td>
                          </tr>
                          <tr>
                            <td class="tbEditBody2">Computer Setting</td>
                            <td class="tbEditBody2"><input type="text"
 name="computerSetting" size="10" maxlen="10">
                              </td>
                          </tr>
          </tbody>
        </table>
                           </td>
                                      </tr>
                         <tr>
                           <td class="tbEditHeader">Location<br>
                           </td>
                           <td class="tbEditBody">



        <select name="location">
        <option value="0">New Location --&gt;</option>
        <?php
           foreach ( $locations as $location )
           {
              echo "<option value=\"$location->f_locationID\"";
              if ( $ride->f_locationID == $location->f_locationID )
                 echo " selected";
              echo ">".
                  fixFieldForTextArea($location->f_location).
                  "</option>";
           }
        ?>
        </select>
                           <br>
                           </td>
                           <td class="tbEditBody2" rowspan="1" colspan="2">



        <table class="tbEditSubTable" cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tbody>
                              <tr>
                                <td class="tbEditBody2" width="25%">New Location</td>
                                <td class="tbEditBody2"><input type="text"
 name="newLocation" value="" size="25" maxlen="100">               </td>
                              </tr>
                              <tr>
                                <td class="tbEditBody2">Type</td>
                                <td class="tbEditBody2">



              <select name="newLocationType">
              <option value="Road">Road</option>
              <option value="Trail">Trail</option>
              <option value="Offroad">Offroad</option>
              <option value="Mixed">Mixed</option>
              </select>
                              <br>
                              </td>
                              </tr>
                              <tr>
                                <td class="tbEditBody2">Description<br>
                                </td>
                                <td class="tbEditBody2"><textarea
 name="newDescription" wrap="virtual" rows="4" cols="40"></textarea></td>
                              </tr>



          </tbody>


        </table>
                          </td>
                                  </tr>
                         <tr>
                           <td class="tbEditHeader">Distance<br>
                           </td>
                           <td class="tbEditBody"><input
 type="text" name="distance" value="<?
            echo number_format($ride->f_distance,2)
         ?>" size="12" maxlen="15"><br>
                           </td>
                           <td class="tbEditHeader">Length<br>
                          </td>
                           <td class="tbEditBody"><input
 type="text" name="time" value="<?php
            echo $ride->f_time
         ?>" size="12" maxlen="15"><br>
                           </td>
                         </tr>
                         <tr>
                           <td class="tbEditHeader">Max Speed<br>
                           </td>
                           <td class="tbEditBody"><input
 type="text" name="maxSpeed" value="<?php
            echo number_format($ride->f_maxSpeed,2)
         ?>" size="12" maxlen="15"><br>
                           </td>
                           <td class="tbEditHeader">Effort<br>
                          </td>
                           <td class="tbEditBody">
       <?php
        echo drawSelect("effort", $availableEfforts, $ride->f_effortLevel);
       ?>
                   <br>
                           </td>
                         </tr>
          <tr>
            <td class="tbEditHeader">Temp<br>
            </td>
            <td class="tbEditBody"><input type="text"
 name="temperature" value="<?php
         echo number_format($ride->f_temperature, 1)
         ?>" size="12" maxlen="15"><br>
            </td>
            <td class="tbEditHeader" rowspan="3" colspan="1">Notes<br>
            </td>
            <td class="tbEditBody" rowspan="3" colspan="1"><textarea
 name="notes" wrap="virtual" rows="4" cols="40"><?php
      echo fixFieldForTextArea($ride->f_notes)
      ?></textarea><br>
            </td>
          </tr>
          <tr>
            <td class="tbEditHeader">Sky<br>
            </td>
            <td class="tbEditBody">
      <?php echo drawSelect("sky", $availableSkies, $ride->f_sky) ?>
            <br>
            </td>
          </tr>
          <tr>
            <td class="tbEditHeader">Wind<br>
            </td>
            <td class="tbEditBody">
         <?php echo drawSelect("wind", $availableWinds, $ride->f_wind) ?>
            <br>
            </td>
          </tr>
                                                                        
                                                  
    </tbody>                                                            
  </table>
  <BR>
  <input type="submit" name="submit" value="Submit">
  <input type="reset" name="reset" value="Reset">
                     <br>
                     </form>

  <p>
  <a href="../showRides.php<?php
  unset($getVars["rideID"]);
  if ( $ride->f_riderID )
     $getVars["riderID"] = $ride->f_riderID;
  echo encodeGet($getVars) ?>">Back to rides</a>

</body>
</html>
