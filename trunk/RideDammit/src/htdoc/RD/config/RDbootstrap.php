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

 //Be sure to not end the base URL with a trailing slash
 $RD_baseURL = "http://myip.org/rides";

 //Configure the connection to the database below.  Be sure to fill in
 //the hostname, username, and password used to access the database.
 //To avoid having a globally available variable, just modify the function
 //below
 $RD_dbName = "ridesDB";

 //What template directory to use.
 $RD_template = "rides";
 //What strings and achievement includes to use.
 $RD_strings = "rides.en";
 
 //The authorization passcode for generating new accounts
 //Really all the software uses is $RD_authCodeMD5, so if
 //you want the site to be slightly more secure, just fill
 //that in and leave $RD_authCode empty.
 $RD_authCode = "enterCodeHere";
 $RD_authCodeMD5 = md5($RD_authCode);
 //$RD_authCodeMD5 = "4F4b7a61c77a708358276be9ba4a4b88";

 //Database Base table name. Used so multiple sites can have the
 //same database
 define("DB_TABLEBASE", "rides");

 function DBconnect()
 {
  global $RD_connection;
  global $RD_dbName;
  if ( !isset( $RD_connection )  )
  {
    $RD_connection = mysql_connect("localhost", "userID", "secretpassword")
                     or noDatabase("connect", mysql_error());
    mysql_select_db($RD_dbName, $RD_connection)
                     or noDatabase("select", mysql_error());
  }
  return $RD_connection;
 }

?>
