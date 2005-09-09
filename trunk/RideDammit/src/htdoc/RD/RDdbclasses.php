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

require("RD/config/RDbootstrap.php");
require("RD/QueryAPI.php");
require("RD/config/RDstrings.".$RD_strings.".php");

define("DB_RIDES", DB_TABLEBASE."_rides");
define("DB_RIDERS", DB_TABLEBASE."_riders");
define("DB_LOCATIONS", DB_TABLEBASE."_locations");
define("DB_BIKES", DB_TABLEBASE."_bikes");

//----------------------------------------------------------------------------
// COMMONLY USED FUNCTIONS

/*************************************************
 * Called when there is an error connecting to the
 * database.
 ************************************************/
function noDatabase($action, $message)
{
   error_log("Error on $action: $message", 0);
   die("There was an error with the database.<p>".
      "If you are seeing this message instead of the ".
      "site, either your RDbootstrap.php file is incorrect, ".
      "or the database hasn't been created.  See your web ".
      "server's log file for the specific error message, or ".
      "the RideDammit! documentation for help creating a ".
      "database.");
}

/*************************************************
 * Converts a number of integer seconds into a
 * string of the form "HH:MM:SS".  Does not
 * handle negative time.
 *
 * @param seconds Integer number of seconds
 ************************************************/
function time_format($seconds)
{
  $secs = $seconds % 60;
  $seconds = (int)($seconds/60);
  $mins = $seconds % 60;
  $seconds = (int)($seconds/60);
  return sprintf("%02d:%02d:%02d", $seconds, $mins, $secs);
}

/*************************************************
 * Figures out the correct image file to use for
 * a particular temperature.
 * @param temp (double) Temperature to compute for.
 *     Expected to be in client's current view units.
 * @param units (RDunits) Units conversion class for
 *     the current view.
 * @return The string filename of the correct image
 *     file.  No path information included.
 ************************************************/
function getImgForTemp($temp, $units)
{
   $temp = $units->settingToCelsius($temp);
   //NPS: Make 35 deg celsius full on hot,
   //and start the scale at 0 deg.  Scale
   //to a number between 0 and 4.
   $temp = (int)($temp*4/35);
   if ( $temp > 4 ) $temp = 4;
   if ( $temp < 0 ) $temp = 0;
   return "temp$temp.png";
}

/*************************************************
 * adds slashes to quotes IFF magic quotes are not turned on.
 ************************************************/
function fixQuotes($inp)
{
  if ( get_magic_quotes_gpc() )
  {
    return $inp;
  }
  else
  {
    return AddSlashes($inp);
  }
}

/*************************************************
 * removes slashes to quotes IFF magic quotes are turned on.
 ************************************************/
function unFixQuotes($inp)
{
   if ( get_magic_quotes_gpc() )
      return StripSlashes($inp);
   else
      return $inp;
}

/*************************************************
 * Utility function which issues a select query to the DB and returns the
 * first row returned, then frees the result.  Useful when you know that
 * there will only be one row.
 * Automatically "die()"s if there is a MYSQL error.
 ************************************************/
function singleQuery($conn, $query)
{
 $result = @mysql_query($query, $conn);
 if ( $result )
 {
  $retVal = mysql_fetch_array($result);
  mysql_free_result($result);
  return $retVal;
 }
 error_log(mysql_error(),0);
 error_log("Query was: $query",0);
 die ("Can't query DB");
}

/*************************************************
 * Utility function which issues a select query to the DB and returns the
 * mysql result identifier.  Useful in order to be consistent w/ the
 * singleQuery function.
 * Automatically "die()"s if there is a MYSQL error.
 ************************************************/
function normalQuery($conn, $query)
{
 $result = @mysql_query($query, $conn);
 if ( ! $result )
 {
   error_log(mysql_error(),0);
   error_log("Query was: $query",0);
   die ("Can't query DB");
 }
 return $result;
}

/*************************************************
 * Returns the first line of a string, or the
 * first $maxLen characters, whichever is shorter.
 * If the string is truncated, adds the string
 * "..." to the end.
 ************************************************/
function firstLine($inp, $maxLen)
{
   $inp = trim($inp);
   $initialLen = strlen($inp);
   if ( $maxLen > $initialLen ) $maxLen = $initialLen;
   $loc = strpos($inp, "\n");
   if ( $loc == false ) $loc = $maxLen;
   $loc2 = strpos($inp, "\r");
   if ( $loc2 == false ) $loc2 = $maxLen;
   if ( $loc > $loc2 ) $loc = $loc2;
   if ( $loc > $maxLen ) $loc = $maxLen;

   return substr($inp, 0, $loc).
         (($loc < $initialLen)?" ...":"");
}

/*************************************************
 * prepare plain text for html display.
 * Clobbers ampersands, less than, greater than, and replaces
 * newlines with <br>.
 *
 * NPS: Per discussion w/ Eric, change to ignore single
 * newlines and replace double newlines w/ <p>?
 *
 * @param doBrackets (boolean)If true, will process
 *  special square bracket identifiers.  If false, will
 *  simply remove square bracket identifiers.
 ************************************************/
function fixFieldForHtml($inp, $doBrackets)
{
   $outp = str_replace("&", "&amp;", $inp);
   $outp = str_replace("<", "&lt;", $outp);
   $outp = str_replace(">", "&gt;", $outp);
   $outp = str_replace("\n", "<br/>", $outp);
   $outp = str_replace("\r", "", $outp);
   $outp = str_replace("  ", "&nbsp;&nbsp;", $outp);
   if ( $doBrackets )
   {
      $outp = preg_replace("/\[hk ([0-9]+)\]/",
                        "<img src=\"/photos/image.php?format=web&image=\$1\">",
                        $outp);
   }
   //Clobber any unknown brackets
   $outp = preg_replace("/\[[^\[]*\]/", "", $outp);
   return $outp;
}

/*************************************************
 * prepare plain text for entry into a <textarea> field.
 ************************************************/
function fixFieldForTextArea($inp)
{
   $outp = str_replace("&", "&amp;", $inp);
   $outp = str_replace("<", "&lt;", $outp);
   $outp = str_replace(">", "&gt;", $outp);
   return $outp;
}

/*************************************************
 * prepare plain text for RSS use.
 * NPS: TODO: Need to read up on RSS and make
 * this work correctly, if anyone ever really wants
 * the rss feed.
 ************************************************/
function fixFieldForRSS($inp)
{
   $outp = str_replace("&", "&amp;", $inp);
   $outp = str_replace("<", "&lt;", $outp);
   $outp = str_replace(">", "&gt;", $outp);
   $outp = str_replace("\n", "<br/>", $outp);
   $outp = str_replace("\r", "", $outp);
   return $outp;
}

/*************************************************
 * Takes an associative array and converts it
 * into a string appropriate to tag onto the end
 * of a URL request.  If the input array is empty,
 * returns a blank string.
 * e.g.:
 *  <?php $gVars["units"] = "english";
 *        $gVars["riderID"] = 3; ?>
 *  <a href="bla.php<?php encodeGet($gVars) ?>">...
 *
 * (produces: "bla.php?units=english&riderID=3")
 ************************************************/
function encodeGet($vars)
{
   $result = "";

   $first = 1;
   foreach ( $vars as $key => $val )
   {
      if ( $first )
      {
         $result .= "?";
         $first = 0;
      }
      else
      {
         $result .= "&";
      }
      $result .= rawurlencode($key) . "=" . rawurlencode($val);
   }
   return $result;
}

/*************************************************
 * Constants which define available enumerated
 * column settings for various database fields
 ************************************************/
$availableEfforts = array(
   "Low",
   "Mild",
   "Medium",
   "Hard",
   "Racing");

$availableSkies = array(
   "Clear",
   "Some",
   "Partly",
   "Mostly",
   "Cloudy");

$availableWinds = array(
   "Still",
   "Mild",
   "Medium",
   "Breezy",
   "Gusty" );

/*************************************************
 * Returns a string which contains HTML select
 * tag and sub-option tags for a list of available
 * elements.
 *
 * @param name (string) What to "name" the select tag.
 * @param elems (array) Value and Text to display for
 *   each <option> tag.
 * @param selected (string) If this string is equal
 *   to one of the <option> tag's value, that option
 *   will be selected
 ************************************************/
function drawSelect($name, $elems, $selected)
{
   $return = "<select name=\"$name\">\n";
   foreach ( $elems as $elem )
   {
      $return .= "<option value=\"$elem\"";
      if ( $elem == $selected )
         $return .= " selected";
      $return .= ">$elem</option>\n";
   }
   $return .= "</select>\n";
   return $return;
}

/*********************************************************
* Draws a link to the given page with the given get vars,
* only with the units swapped out for $newUnits.  The
* link text will be the human readable units name.  The
* href is not added if $curUnits is equal to $newUnits.
********************************************************/
function drawUnitLink($newUnits, $curUnits, $url, $getVars)
{
   if ( $newUnits->mode != $curUnits->mode)
   {
      echo "<a href=\"$url";
      $tgetVars = $getVars;
      $tgetVars["units"] = $newUnits->unitsString();
      echo encodeGet($tgetVars);
      echo "\">";
   }
   echo $newUnits->unitsString();
   if ( $newUnits->mode != $curUnits->mode)
   {
      echo "</a>\n";
   }
}

/*********************************************************
* Draws the standard units switcher code which re-loads
* the current page with the requested units.
********************************************************/
function drawUnitsLinks()
{
   global $units;

   echo "Use [";
   drawUnitLink(new RDunits(UNIT_METRIC), $units,
                  $_SERVER["PHP_SELF"], $_GET);
   echo ", ";
   drawUnitLink(new RDunits(UNIT_ENGLISH), $units,
                  $_SERVER["PHP_SELF"], $_GET);
   echo "] units\n";
}


/*************************************************
 * Utility function which takes any database object
 * below which has a no-argument queryAll() function
 * and calls said function, then creates an array
 * of those objects for each row found.
 ************************************************/
function getAllObjects($obj)
{
   $result = $obj->queryAll();
   while ( $obj->parseNextRow($result) )
   {
      $set[] = $obj;
   }
   return $set;
}

//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Classes
//
//variable naming note:
// variables that begin with "f_" represent a field in the associated table.
// variables that begin with "c_" represent computed values that are
//  computed automatically when reading the DB and ignored when writing
//  to the database.
// variables that begin with "j_" are fields that are joined on from
//  other tables when reading the db.  They are not written back to the
//  database when writing. (NOTE, as of this moment, some of the joined
//  fields are currently mis-named as f_ due to legacy code.

/*************************************************
 * Class which represents a rider
 ************************************************/
class RDrider
{
   var $conn;
   var $units;

   var $f_riderID;
   var $f_firstName;
   var $f_lastName;

   var $c_totalTime;
   var $c_totalDist;
   var $c_avgDist;
   var $c_maxDist;
   var $c_maxSpeed;
   var $c_avgSpeed;
   var $c_maxAvgSpeed;
   var $c_numRides;

   function RDrider($conn, $units)
   {
      $this->conn = $conn;
      $this->units = $units;
   }

   function getDB_id($riderID)
   {
      $riderID = (int)$riderID; //security
      $result = singleQuery($this->conn,
         "select * from ".DB_RIDERS." where riderID=".
         (int)$riderID);
      if ( ! $result )
      {
         return "No rider by that id found.";
      }

      return $this->parseRow($result);
   }

   /*************************************************
    * Inserts the current data as a new record.
    * f_riderID is filled in as the newly created ID.
    * You must pass in the new user's password.
    ************************************************/
   function insertNew($newPword)
   {
      normalQuery($this->conn,
              "insert into ".DB_RIDERS.
              " (firstName, lastName, pword) ".
              " values(\"".addSlashes($this->f_firstName).
              "\", \"". addSlashes($this->f_lastName) .
              "\", password(\"". addSlashes($newPword) .
              "\")  )");
      $this->f_riderID = mysql_insert_id();
   }

   /*************************************************
    * Check whether the password for a rider is
    * correct.  Does not use any class variables (
    * effectively a static function).
    ************************************************/
   function checkPerms($riderID, $password)
   {
      $result = singleQuery($this->conn,
         "select riderID from ".DB_RIDERS.
         " where riderID=".
            (int)$riderID." and pword=password('".
            addSlashes($password)."')");
      return $result["riderID"]==$riderID;
   }

   function queryAll($query = NULL)
   {
      $whereClause = "";
      if ( $query )
      {
         $whereClause = "where ".$query->toString();
      }
      $result = normalQuery($this->conn,
         "select * from ".DB_RIDERS.
         " $whereClause order by lastName, firstName");
      return $result;
   }

   /*************************************************
    * Same as query all, but compute overall statistics
    * for each rider at the same time.
    ************************************************/
   function queryAllStats($query = NULL)
   {
      $whereClause = "";
      if ( $query )
      {
         $whereClause = "where ".$query->toString();
      }
      $result = normalQuery($this->conn,
         "select ".DB_RIDERS.".*,".
               "sum(distance) as tDist, ".
               " sum(TIME_TO_SEC(time)) as tTime, ".
               " max(maxSpeed) as maxSpeed, ".
               " avg(distance) as aDist, ".
               " max(distance) as mDist, ".
               " max(distance*3600/TIME_TO_SEC(time)) as mAvgSpeed, ".
               " count(rideID) as numRides ".
               " from ".DB_RIDERS.
               "  left join ".DB_RIDES.
               "   on ".DB_RIDES.".riderID=".DB_RIDERS.".riderID ".
               "  left join ".DB_LOCATIONS.
               "   on ".DB_RIDES.".locationID=".DB_LOCATIONS.".locationID ".
               $whereClause.
               " group by ".DB_RIDERS.".riderID ".
               " order by ".DB_RIDERS.".lastName, ".
               "   ".DB_RIDERS.".firstName");
      return $result;
   }


   function parseNextRow($result)
   {
      $next = mysql_fetch_array($result);
      if ( $next )
      {
         $this->parseRow($next);
         return 1;
      }
      else
      {
         return 0;
      }
   }

   function parseRow($result)
   {
      $this->f_riderID = $result["riderID"];
      $this->f_firstName = $result["firstName"];
      $this->f_lastName = $result["lastName"];
      if ( isset($result["tTime"] ) )
      {
         $this->c_totalTime = $result["tTime"];
         $this->c_totalDist = $this->units->metricToSetting($result["tDist"]);
         $this->c_maxSpeed = $this->units->metricToSetting($result["maxSpeed"]);
         if ( $this->c_totalTime > 0 )
            $this->c_avgSpeed = $this->c_totalDist/$this->c_totalTime*3600;
         else
            $this->c_avgSpeed = 0;
         $this->c_numRides = $result["numRides"];
                        $this->c_mAvgSpeed = $this->units->metricToSetting($result["mAvgSpeed"]);
                        $this->c_mDist = $this->units->metricToSetting($result["mDist"]);
                        $this->c_aDist = $this->units->metricToSetting($result["aDist"]);
      }
   }
}

/*************************************************
 * Class which represents a bike
 ************************************************/
class RDbike
{
   var $conn;

   var $f_bikeID;
   var $f_bike;
   var $f_riderID;
   var $j_riderID_firstName;
   var $j_riderID_lastName;

   function RDbike($conn)
   {
      $this->conn = $conn;
   }

   /*************************************************
    * Inserts the current data as a new record.
    * f_bikeID is filled in as the newly created ID.
    ************************************************/
   function insertNew()
   {
      if ( ! isset($this->f_bike) ||
            strlen($this->f_bike)==0 )
      {
         return "Can't add bike, none specified.";
      }
      normalQuery($this->conn,
              "insert into ".DB_BIKES." (bike, riderID) ".
              " values(\"".addSlashes($this->f_bike).
              "\", ". (int)$this->f_riderID .
              "  )");
      $this->f_bikeID = mysql_insert_id();
   }

   function getDB_id($bikeID)
   {
      $bikeID = (int)$bikeID; //security
      $result = singleQuery($this->conn,
         "select ".DB_BIKES.".*, ".
            "    firstName, ".
            "    lastName ".
            "   from ".DB_BIKES." ".
            "    left join ".DB_RIDERS.
            "     on ".DB_BIKES.".riderID=".DB_RIDERS.".riderID ".
            "   where bikeID=$bikeID");
      if ( ! $result )
      {
         return "No bike by that id found.";
      }

      return $this->parseRow($result);
   }

   function queryAll()
   {
      $result = normalQuery($this->conn,
         "select ".DB_BIKES.".*, ".
            "    firstName, ".
            "    lastName ".
            "   from ".DB_BIKES.
            "    left join ".DB_RIDERS.
            "     on ".DB_BIKES.".riderID=".DB_RIDERS.".riderID ".
            "   order by lastName, firstName, bike");
      return $result;
   }

   function parseNextRow($result)
   {
      $next = mysql_fetch_array($result);
      if ( $next )
      {
         $this->parseRow($next);
         return 1;
      }
      else
      {
         return 0;
      }
   }

   function parseRow($result)
   {
      $this->f_bikeID = $result[bikeID];
      $this->f_bike = $result[bike];
      $this->f_riderID = $result[riderID];
      $this->j_riderID_firstName = $result[firstName];
      $this->j_riderID_lastName = $result[lastName];
   }
}

/*************************************************
 * Convert location strings to numbers.  Should 
 * be static methods of RDlocation, but you know
 * PHP and classes...
 ************************************************/
function locationTypeToNumber($lt)
{
   global $RDlocationTypes;
   //Let it default to the first type
   return (int)array_search($lt, $RDlocationTypes);
}

function numberToLocationType($n)
{
   global $RDlocationTypes;
   //NPS: Default to the first type
   if ( $n < 0 || $n >= count($RDlocationTypes) )
      return $RDlocationTypes[0];
   return $RDlocationTypes[$n];
}

/*************************************************
 * Class which represents a location
 ************************************************/
class RDlocation
{
   var $conn;

   var $f_locationID;
   var $f_location;
   var $f_description;
   var $f_type;

   function RDlocation($conn)
   {
      $this->conn = $conn;
   }

   /*************************************************
    * Inserts the current data as a new record.
    * f_locationID is filled in as the newly created ID.
    ************************************************/
   function insertNew()
   {
      if ( ! isset($this->f_location) ||
            strlen($this->f_location) == 0 )
      {
         return "Can't add location, none specified.";
      }
      normalQuery($this->conn,
              "insert into ".DB_LOCATIONS.
              " (location, description, type) ".
              " values(\"".addSlashes($this->f_location).
              "\", \"". addSlashes($this->f_description) .
              "\", ". locationTypeToNumber($this->f_type) .
              " )");
      $this->f_locationID = mysql_insert_id();
   }

   function getDB_id($locationID)
   {
      $locationID = (int)$locationID; //security
      $result = singleQuery($this->conn,
         "select * ".
            "   from ".DB_LOCATIONS.
            "   where locationID=$locationID");
      if ( ! $result )
      {
         return "No location by that id found.";
      }

      return $this->parseRow($result);
   }

   function queryAll()
   {
      $result = normalQuery($this->conn,
            "select * from ".DB_LOCATIONS." order by type, location");
      return $result;
   }

   function parseNextRow($result)
   {
      $next = mysql_fetch_array($result);
      if ( $next )
      {
         $this->parseRow($next);
         return 1;
      }
      else
      {
         return 0;
      }
   }

   function parseRow($result)
   {
      $this->f_locationID = $result["locationID"];
      $this->f_location = $result["location"];
      $this->f_description = $result["description"];
      $this->f_type = numberToLocationType($result["type"]);
   }
}


/*************************************************
 * Class which represents a ride
 ************************************************/
class RDride
{
   var $conn;
   var $units;

   var $f_rideID;
   var $f_riderID;
   var $f_riderID_firstName;
   var $f_riderID_lastName;
   var $f_date;
   var $f_distance;
   var $f_maxSpeed;
   var $f_time;
   var $f_timeSecs;
   var $f_locationID;
   var $f_locationID_location;
   var $f_locationID_description;
   var $f_locationID_type;
   var $f_temperature;
   var $f_wind;
   var $f_sky;
   var $f_effortLevel;
   var $f_bikeID;
   var $f_bikeID_bike;
   var $f_notes;

   var $c_avgSpeed;

   function RDride($conn, $units)
   {
      $this->conn = $conn;
      $this->units = $units;
   }
   
   /*************************************************
    * Validates fields and check for access.  Pretty
    * minimal right now...
    ************************************************/
   function _validateFields($password)
   {
      //Check Perms first
      $rider = new RDrider($this->conn, $this->units);
      if ( ! $rider->checkPerms($this->f_riderID, $password) )
      {
         return "Invalid password";
      }
      //Allow for length w/o hours
      if ( preg_match('/^([^:]*):([^:]*)$/', $this->f_time, $matches) )
      {
         //Do a computation to try and decide if they
         //left off hours or seconds.  Assume hours
         //left off and see if average speed is reasonable
         $seconds = $matches[2] + $matches[1]*60;
         $avg = $this->f_distance*3600.0/$seconds;
         if ( $avg > C_MAX_SPEED )
            $this->f_time = $this->f_time.":00";
         else
            $this->f_time = "00:".$this->f_time;
      }
      return "";
   }

   /*************************************************
    * Inserts the current data as a new record.
    * f_rideID is filled in as the newly created ID.
    * You must supply the correct password for the
    * rider identified in f_riderID
    ************************************************/
   function insertNew($password)
   {
      if ( $msg = $this->_validateFields($password) )
      {
         return $msg;
      }
      normalQuery($this->conn,
              "insert into ".DB_RIDES." ".
              "(riderID, date, distance, maxSpeed, time, ".
              "locationID, temperature, wind, sky, effortLevel, ".
              "bikeID, notes) values ( ".
              (int)$this->f_riderID .
              ", \"". addSlashes($this->f_date) .
              "\", ". $this->units->settingToMetric((double)$this->f_distance) .
              ", ". $this->units->settingToMetric((double)$this->f_maxSpeed) .
              ", \"". addSlashes($this->f_time) .
              "\", ". (int)$this->f_locationID .
              ", ". $this->units->settingToCelsius((double)$this->f_temperature) .
              ", \"". addSlashes($this->f_wind) .
              "\", \"". addSlashes($this->f_sky) .
              "\", \"". addSlashes($this->f_effortLevel) .
              "\", ". (int)$this->f_bikeID .
              ", \"". addSlashes($this->f_notes) .
              "\")");
      $this->f_rideID = mysql_insert_id();
   }

   /*************************************************
    * Updates an existing ride with the data in this
    * instance.  f_rideID should be filled in with a
    * valid ride.  This function does not allow you
    * to change the riderID of a ride.  You must
    * supply the correct password for the riderID
    * in order to have permission to change the ride.
    ************************************************/
   function update($password)
   {
      if ( $msg = $this->_validateFields($password) )
      {
         return $msg;
      }
      $result = normalQuery($this->conn,
             "update ".DB_RIDES." set ".
              "date=\"". addSlashes($this->f_date) .
              "\", distance=". $this->units->settingToMetric((double)$this->f_distance) .
              ", maxSpeed=". $this->units->settingToMetric((double)$this->f_maxSpeed) .
              ", time=\"". addSlashes($this->f_time) .
              "\", locationID=". (int)$this->f_locationID .
              ", temperature=". $this->units->settingToCelsius((double)$this->f_temperature) .
              ", wind=\"". addSlashes($this->f_wind) .
              "\", sky=\"". addSlashes($this->f_sky) .
              "\", effortLevel=\"". addSlashes($this->f_effortLevel) .
              "\", bikeID=". (int)$this->f_bikeID .
              ", notes=\"". addSlashes($this->f_notes) .
              "\" where rideID=".(int)$this->f_rideID.
              " and riderID=".(int)$this->f_riderID);
      $rows = mysql_affected_rows($this->conn);
      if ( ! $rows )
      {
         return "Error updating, perhaps rider changed?";
      }
   }

   function getDB_id($rideID)
   {
      $rideID = (int)$rideID; //security
      $result = singleQuery($this->conn,
         $this->_selectPart().
         $this->_fromJoinPart().
         "  where rideID=$rideID");

      if ( ! $result )
      {
         return "No ride number $rideID found.";
      }

      return $this->parseRow($result);
   }

   /**
    * Utility function which reads the rider
    * information for a ride without changing anything
    * else in the class.  Affects only f_riderID* variables.
    */
   function getRiderInfo()
   {
      //Take the easy way out ;-)
      $rideTmp = new RDride($this->conn, $this->units);
      $errMsg = $rideTmp->getDB_id($this->f_rideID);
      if ( $errMsg )
         return $errMsg;
      $this->f_riderID = $rideTmp->f_riderID;
      $this->f_riderID_firstName = $rideTmp->f_riderID_firstName;
      $this->f_riderID_lastName = $rideTmp->f_riderID_lastName;
   }

   /*************************************************
    * Returns the select part of a select statement
    * for this table.
    ************************************************/
   function _selectPart()
   {
      return
         "select ".DB_RIDES.".*, ".
         "       bike,".
         "       location, ".
         "       ".DB_LOCATIONS.".description, ".
         "       ".DB_LOCATIONS.".type, ".
         "       firstName, ".
         "       lastName, ".
         "       distance*3600/TIME_TO_SEC(time) as avgSpeed, ".
         "       TIME_TO_SEC(time) as timeSecs ";
   }

   /*************************************************
    * Returns the from and join parts of a select statement
    * for this table.
    ************************************************/
   function _fromJoinPart()
   {
      return
         "  from ".DB_RIDES.
         "    left join ".DB_RIDERS.
         "     on ".DB_RIDERS.".riderID=".DB_RIDES.".riderID".
         "    left join ".DB_LOCATIONS.
         "     on ".DB_LOCATIONS.".locationID=".DB_RIDES.".locationID ".
         "    left join ".DB_BIKES.
         "     on ".DB_BIKES.".bikeID=".DB_RIDES.".bikeID ";
   }

   /*************************************************
    * Querys all the rides for a particular rider, or
    * all riders.  Similar to queryAll() in other
    * db classes, only more restrictive.
    * @deprecated in favor of queryRides().
    * @param riderID The id of the rider to read
    *   rides for.  If 0 or a string, will return rides
    *   for all riders.
    * @param start Starting record to read
    * @param num Number of records to read, max.
    ************************************************/
   function queryRidesForRider($riderID, $start, $num)
   {
      $riderID = (int)$riderID; //security
      $start = (int) $start;
      $num = (int) $num;
      $whereClause = "";
      if ( $riderID )
      {
         $whereClause = "where ".DB_RIDES.".riderID=$riderID";
      }
      $result = normalQuery($this->conn,
         $this->_selectPart().
         $this->_fromJoinPart().
         $whereClause.
         " order by date desc limit $start,$num");
      return $result;
   }

   /*************************************************
    * Querys all the rides for a particular rider, or
    * all riders.  Similar to queryAll() in other
    * db classes, only more restrictive.
    * @param riderID The id of the rider to read
    *   rides for.  If 0 or a string, will return rides
    *   for all riders.
    * @param start Starting record to read
    * @param num Number of records to read, max.
    ************************************************/
   function queryRides($query, $start, $num)
   {
      $start = (int) $start;
      $num = (int) $num;
      $whereClause = "";
      if ( $query )
      {
         $whereClause = "where ".$query->toString();
      }
      $result = normalQuery($this->conn,
         $this->_selectPart().
         $this->_fromJoinPart().
         $whereClause.
         " order by date desc limit $start,$num");
      return $result;
   }

   function parseNextRow($result)
   {
      $next = mysql_fetch_array($result);
      if ( $next )
      {
         $this->parseRow($next);
         return 1;
      }
      else
      {
         return 0;
      }
   }

   function parseRow($result)
   {
      $this->f_rideID = $result[rideID];
      $this->f_riderID = $result[riderID];
      $this->f_riderID_firstName = $result[firstName];
      $this->f_riderID_lastName = $result[lastName];
      $this->f_date = $result[date];
      $this->f_distance = $this->units->metricToSetting($result[distance]);
      $this->f_maxSpeed = $this->units->metricToSetting($result[maxSpeed]);
      $this->f_time = $result[time];
      $this->f_timeSecs = $result[timeSecs];
      $this->f_locationID = $result[locationID];
      $this->f_locationID_location = $result[location];
      $this->f_locationID_description = $result[description];
      $this->f_locationID_type = numberToLocationType($result[type]);
      $this->f_temperature = $this->units->celsiusToSetting($result[temperature]);
      $this->f_wind = $result[wind];
      $this->f_sky = $result[sky];
      $this->f_effortLevel = $result[effortLevel];
      $this->f_bikeID = $result[bikeID];
      $this->f_bikeID_bike = $result[bike];
      $this->f_notes = $result[notes];
      $this->c_avgSpeed = $this->units->metricToSetting($result[avgSpeed]);
   }

   /*************************************************
    * The f_date field parameter is in MYSQL form.
    * The following functions give and take it in
    * localized forms
    ************************************************/

   /*************************************************
    * @return The date part of the date/time of the ride
    ************************************************/
   function getDatePart()
   {
      if ( $this->f_date == "" || $this->f_date == "0000-00-00 00:00:00" )
         return "";
      $tm = strtotime($this->f_date);
      if ( $tm === -1 ) 
         return "";
      return strftime("%x", $tm);
   }

   /*************************************************
    * @return The time part of the date/time of the ride
    ************************************************/
   function getTimePart()
   {
      if ( $this->f_date == "" || $this->f_date == "0000-00-00 00:00:00" )
         return "";
      $tm = strtotime($this->f_date);
      if ( $tm === -1 )
         return "";
      return strftime("%I:%M %p", $tm);
   }
   
   function fromLocalizedTime($date)
   {
      $tm = strtotime($date);
      if ( $tm === -1 )
      {
         $this->f_date = "0000-00-00 00:00:00";
      }
      else
      {
         $this->f_date = strftime("%Y-%m-%d %T", $tm);
      }
   }
}


define("UNIT_METRIC", 1);
define("UNIT_ENGLISH", 3);

/*************************************************
 * Class which represents the current units in
 * effect and utilities for converting and printing
 * out unit texts.
 ************************************************/
class RDunits
{
   var $mode;

   /*************************************************
    * @param $setting What type of units to use. May
    *   be a string or one of the constants defined
    *   above.
    ************************************************/
   function RDunits($setting)
   {
      if ( isset($setting) && 
           ( (strcasecmp($setting,S_ENGLISH_UNITS) == 0) ||
             ($setting == UNIT_ENGLISH) ) )
      {
         $this->mode = UNIT_ENGLISH;
      }
      else
      {
         $this->mode = UNIT_METRIC;
         //NPS: Yea, I hate not having an error for invalid
         //input.
      }
   }

   function metricToSetting($in)
   {
      if ( $this->mode == UNIT_ENGLISH )
      {
         return $in * C_METRIC_TO_ENGLISH_DIST; //0.6213712;
      }
      return $in;
   }

   function settingToMetric($in)
   {
      if ( $this->mode == UNIT_ENGLISH )
      {
         return $in * C_ENGLISH_TO_METRIC_DIST; //1.609344;
      }
      return $in;
   }

   function celsiusToSetting($in)
   {
      if ( $this->mode == UNIT_ENGLISH )
      {
         return (($in * 9/5)+32);
      }
      return $in;
   }

   function settingToCelsius($in)
   {
      if ( $this->mode == UNIT_ENGLISH )
      {
         return (($in-32)*5/9);
      }
      return $in;
   }

   function isMetric()
   {
      return $this->mode == UNIT_METRIC;
   }

   function isEnglish()
   {
      return $this->mode == UNIT_ENGLISH;
   }

   function unitsString()
   {
      switch ( $this->mode )
      {
      case UNIT_ENGLISH:
         return S_ENGLISH_UNITS;
      case UNIT_METRIC:
         return S_METRIC_UNITS;
      }
   }

   function distanceString()
   {
      switch ( $this->mode )
      {
      case UNIT_ENGLISH:
         return S_ENGLISH_DISTANCE;
      case UNIT_METRIC:
         return S_METRIC_DISTANCE;
      }
   }

   function velocityString()
   {
      switch ( $this->mode )
      {
      case UNIT_ENGLISH:
         return S_ENGLISH_VELOCITY;
      case UNIT_METRIC:
         return S_METRIC_VELOCITY;
      }
   }

   function tempString()
   {
      switch ( $this->mode )
      {
      case UNIT_ENGLISH:
         return S_ENGLISH_TEMP;
      case UNIT_METRIC:
         return S_METRIC_TEMP;
      }
   }
}

function explode_int($pattern, $string)
{
   $bla = explode($pattern, $string);
   $bla2 = array();
   foreach( $bla as $abla )
   {
      $bla2[] = (int)$abla;
   }
   return $bla2;
}

/******************************************************
 * This class represents the current query specified by
 * the user and the ability to read it from GET vars
 * and to create a QueryObj which represents the Where
 * clause
 *****************************************************/
class RDquery
{
   var $riderID;
   var $locationID;
   var $effort;
   var $bikeID;
   var $beforeDate;
   var $afterDate;

   /**
    * Expects a "get variables" type array with settings
    * for all the various things you can search on
    */
   function RDQuery($getVars)
   {
      $this->riderID = (int)$getVars["riderID"];
      $this->locationID = unFixQuotes($getVars["locID"]);
      $this->effort = $getVars["effort"];
      $this->bikeID = (int)$getVars["bikeID"];
      $this->beforeDate = unFixQuotes($getVars["beforeDate"]);
      $this->afterDate = unFixQuotes($getVars["afterDate"]);
   }

   /**
    * Internal function to and together two where clauses
    */
   function _andOn($currentWhere, $newWhere)
   {
      if ($currentWhere)
         return new QueryBinaryOp($currentWhere, "and", $newWhere);
      else
         return $newWhere;
   }

   /**
    * Builds a QueryObj which represents the current query.
    * If nothing is to be searched upon, returns NULL.
    * If a queryObj is passed in, the current query will
    * be "and"ed on.
    */
   function getWhereStatement($currentWhere = NULL)
   {
      $currentWhere = $currentWhere;
      if ( $this->riderID > 0 )
      {
         $newClause = new QueryBinaryOp(
              new QueryColumnRef(DB_RIDES.".riderID"),
              "=",
              new QueryIntLiteral($this->riderID));
         $currentWhere = $this->_andOn($currentWhere, $newClause);
      }
      if ( ((int)$this->locationID) > 0 )
      {
         $newClause = new QueryBinaryOp(
              new QueryColumnRef(DB_RIDES.".locationID"),
              "=",
              new QueryIntLiteral((int)$this->locationID));
         $currentWhere = $this->_andOn($currentWhere, $newClause);
      }
      elseif ( $this->locationID )
      {
         $newClause = new QueryBinaryOp(
              new QueryColumnRef(DB_LOCATIONS.".type"),
              "=",
              new QueryIntLiteral(
                     locationTypeToNumber($this->locationID)));
         $currentWhere = $this->_andOn($currentWhere, $newClause);
      }
      if ( $this->bikeID > 0 )
      {
         $newClause = new QueryBinaryOp(
              new QueryColumnRef(DB_RIDES.".bikeID"),
              "=",
              new QueryIntLiteral($this->bikeID));
         $currentWhere = $this->_andOn($currentWhere, $newClause);
      }
      //TODO: effort and dates.
      return $currentWhere;
   }

}

//------------------------------------------------
// Go ahead and create a units class with the current units per the
// GET vars.
$units = new RDunits($_GET["units"]);

include("RD/RDdistances.php");


