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

class RDdistanceClass
{
   var $distance;
   var $text;
   var $lnk;

   function RDdistanceClass($d, $t, $l)
   {
      global $units;

      $this->distance = $units->metricToSetting($d);
      $this->text = $t;
      $this->lnk = $l;
   }
   
   function asOutput()
   {
      if ( $this->lnk != "" )
      {
         return "<a href=\"".$this->lnk."\">".$this->text."</a>";
      }
      return $this->text;
   }
}

include("RD/config/RDachievements.".$RD_strings.".php");

function getDistanceClass($dist)
{
   global $RDdistanceClasses;

   $high = count($RDdistanceClasses);
   $low = 0;
   while ( $high-$low > 1 )
   {
      $probe = (int)(($high + $low ) / 2);
      if ( $RDdistanceClasses[$probe]->distance < $dist )
      {
         $low = $probe;
      }
      else
      {
         $high = $probe;
      }
   }
   if ( $high < count($RDdistanceClasses) &&
         $RDdistanceClasses[$high]->distance <= $dist )
      return $RDdistaneClasses[$high];
   return $RDdistanceClasses[$low];
}
