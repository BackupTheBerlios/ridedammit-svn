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
   $ride->fromLocalizedTime(
                  unFixQuotes($HTTP_POST_VARS["date"]). " " .
                  unFixQuotes($HTTP_POST_VARS["hour"]));
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
            $getVars["rideID"] = $ride->f_rideID;
            header("Location: ../showRide.php".encodeGet($getVars));
            return;
         }
      }
      else
      {
         $ride->f_rideID = $rideID;
         $errMsg = $ride->update($password);
         if ( ! $errMsg )
         {
            $getVars["rideID"] = $ride->f_rideID;
            header("Location: ../showRide.php".encodeGet($getVars));
            return;
         }
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

$pageTitle = $newRide?"New ".S_EVENT:S_EVENT." Edit";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?php echo S_SITE_TITLE." ".$pageTitle; ?></title>
  <link rel="stylesheet" href="<?php echo $RD_baseURL ?>/default.css">
  <meta http-equiv="content-type"
 content="text/html; charset=ISO-8859-1">
</head>
<?php
include("templates/$RD_template/editRide.php");
?>
</html>
