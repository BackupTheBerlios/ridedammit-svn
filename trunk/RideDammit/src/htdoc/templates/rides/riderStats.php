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
<?php $pageTitle = S_PERSON." Stats"; include("header.inc.html"); ?>

<H2><?php echo S_PERSON; ?> Stats for the Last 12 Months</H2>
<p>
</p>


<TABLE class="tbLog" width="820" cellspacing="2px" cellpadding="2px">
<?php

$riders = new RDrider(DBConnect(), $units);
$results = $riders->queryAllStats("");

$alternator = "";
while ( $riders->parseNextRow($results) )
{
   echo "<TR>\n";
   echo "<td width=400 height=300 class=\"tbLogBody".$alternator."\">";
   echo "<img src=\"graphs/". $riders->f_riderID .
           "dist.png\" width=400 height=300></td>\n";
   echo "<td width=20 height=300 >&nbsp;</td>\n";
   echo "<td width=400 heigth=300 class=\"tbLogBody".$alternator."\">";
   echo "<img src=\"graphs/". $riders->f_riderID .
           "time.png\" width=400 height=300></td>\n";
   echo "</TR>\n";
   if ( $alternator == "" )
      $alternator = "2";
   else
      $alternator = "";
}

?>
</TABLE>

  <p>
  <a href="showRides.php<?php
  echo encodeGet($getVars) ?>">Back to <?php echo S_EVENTS; ?></a>
<?php include("footer.inc.html"); ?>
</body>
