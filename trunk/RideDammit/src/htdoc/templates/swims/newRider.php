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
?>
<body>
<?php $pageTitle = "New ".S_PERSON; include("header.inc.html"); ?>
<H1>Become a new <?php echo S_PERSON; ?></H1>
<p>
Welcome, if you know the authorization code, you can add yourself
as a <?php echo S_PERSON; ?> on this site.  If not, please contact
the site administrator for instructions.
</p>
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
  <p class="tinyPrint">
  <!-- Hint: Authorization code hint here --></p>
  <input type="submit" name="submit" value="Submit">
                     <br>
                     </form>
  </center>
  <p>
  <a href="../showRides.php<?php
  echo encodeGet($getVars) ?>">Back to <?php echo S_EVENTS; ?></a>
<?php include("footer.inc.html"); ?>
</body>
