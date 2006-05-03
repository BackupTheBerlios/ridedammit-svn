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

require("RD/RDdbclasses.php");

//Initialize Variables and queries.
$getVars = $_GET;
if ( isset($getVars["start"] ))
   $start = (int)$getVars["start"];
if ( isset($getVars["num"] ))
   $num = (int)$getVars["num"];
if ( ! $num ) $num = 10;
$currentQuery = new RDquery($getVars);


//HTML HEADER
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<TITLE><?php echo S_SITE_TITLE; ?></TITLE>
<link rel="stylesheet" href="<?php echo $RD_baseURL ?>/default.css">
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
<meta content="PHP, MySQL, free, open, source, RD, ridedammit, ride, dammit, bike, swimming, biking, riding, log, swim, run" name="keywords" />
</HEAD>
<?php
include("templates/$RD_template/showRides.php");
?>
</HTML>
