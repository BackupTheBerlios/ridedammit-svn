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
   if ( md5($code) != "06bb7a61c77a708358276be9bc4a4b62"  )
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
  <title>Ride Dammit! New Rider </title>
  <link rel="stylesheet" href="<?php echo $RD_baseURL ?>/default.css">
  <meta http-equiv="content-type"
 content="text/html; charset=ISO-8859-1">
</head>
<body>
<?php $pageTitle = "New Rider"; include("header.inc.html"); ?>
<H1>Become a new rider</H1>
<p>I welcome anyone that I know to use this site for logging your
miles.  I threw it all together in evenings and so forth, so
there are still some breakable stuff in here, be forewarned.
Hope you enjoy it and let me know if you have any questions
or suggestions!  If you don't know my middle name (which is
the "authorization code" below), drop
me a line and I'll be happy to let you in.</p>
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
<center>
<form method="post" action="<?php
   echo $HTTP_SERVER_VARS["PHP_SELF"].encodeGet($getVars) ?>">
  <table class="tbEdit">
   <tbody>
      <tr>
         <td class="tbEditHeader">First Name<br>
         </td>
         <td class="tbEditBody">
            <input type="text" name="firstName"
               size="50" maxlen="100" value="<?php
                  echo str_replace("\"","&quot;",$rider->f_firstName)
                  ?>">
         </td>
      </tr>
      <tr>
         <td class="tbEditHeader">Last Name<br>
         </td>
         <td class="tbEditBody">
            <input type="text" name="lastName"
               size="50" maxlen="100" value="<?php
                  echo str_replace("\"","&quot;",$rider->f_lastName)
                  ?>">
         </td>
      </tr>
      <tr>
         <td class="tbEditHeader">Password<br>
         </td>
         <td class="tbEditBody">
            <input type="password" name="password"
               size="20" maxlen="30">
         </td>
      </tr>
      <tr>
         <td class="tbEditHeader">Verify<br>
         </td>
         <td class="tbEditBody">
            <input type="password" name="verify"
               size="20" maxlen="30">
         </td>
      </tr>
      <tr>
         <td class="tbEditHeader">Authorization Code
         </td>
         <td class="tbEditBody">
            <input type="password" name="code"
               size="20" maxlen="30">
         </td>
      </tr>


    </tbody>
  </table>
  <p class="tinyPrint">Hint: Authorization code is my middle name uncapitalized</p>
  <input type="submit" name="submit" value="Submit">
                     <br>
                     </form>
  </center>
  <p>
  <a href="../showRides.php<?php
  echo encodeGet($getVars) ?>">Back to rides</a>
<?php include("footer.inc.html"); ?>
</body>
</html>
