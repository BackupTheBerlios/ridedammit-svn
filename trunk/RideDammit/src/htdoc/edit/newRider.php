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

$getVars=$HTTP_GET_VARS;

$rider = new RDrider(DBConnect(), $units);

//Check for form submit and handle it here
if ( $HTTP_POST_VARS["submit"] )
{
   //Fill in the info they submitted.
   $rider->f_firstName = unFixQuotes($HTTP_POST_VARS["firstName"]);
   $rider->f_lastName = unFixQuotes($HTTP_POST_VARS["lastName"]);
   $pword = unFixQuotes($HTTP_POST_VARS["password"]);
   $verify = unFixQuotes($HTTP_POST_VARS["verify"]);
   $code = unFixQuotes($HTTP_POST_VARS["code"]);

   //Check for code word
   if ( md5($code) != $RD_authCodeMD5  )
   {
      $errMsg = "Invalid Code!  No permission to create an account.";
   }

   //Check for password mismatch
   if ( ! $errMsg && ($pword != $verify) )
   {
      $errMsg = "Passwords do not match!  Please enter them again.";
   }

   if ( ! $errMsg )
   {
      $errMsg = $rider->insertNew($pword);
      if ( ! $errMsg )
      {
         //Inserted, send them back to the front!
         $getVars["riderID"] = $rider->f_riderID;
         header("Location: $RD_baseURL/showRides.php".encodeGet($getVars));
         return;
      }
   }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?php echo S_SITE_TITLE." New ".S_PERSON; ?></title>
  <link rel="stylesheet" href="<?php echo $RD_baseURL ?>/default.css">
  <meta http-equiv="content-type"
 content="text/html; charset=ISO-8859-1">
</head>
<?php
include("templates/$RD_template/newRider.php");
?>
</html>
