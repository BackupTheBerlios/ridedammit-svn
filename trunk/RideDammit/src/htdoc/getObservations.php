<?php
 if ( isset($_GET["location"]) &&
      strlen($_GET["location"]) == 4 )
 {
   session_start();
   if ( isset($_SESSION["weatherCacheLoc"]) &&
        $_SESSION["weatherCacheLoc"] == $_GET["location"] &&
	$_SESSION["weatherCacheTime"] > time() - 15*60 )
   {
      error_log("Getting weather from session",0);
      echo $_SESSION["weatherCache"];
      return;
   }
   session_write_close();
   error_log("Reading weather from NOAA",0);
   $fp = fopen("http://weather.noaa.gov/pub/data/observations/metar/stations/".$_GET["location"].".TXT","r");
   $result = fread($fp, 1024*8);
   fclose($fp);
   session_start();
   $_SESSION["weatherCacheLoc"] = $_GET["location"];
   $_SESSION["weatherCacheTime"] = time();
   $_SESSION["weatherCache"] = $result;
   error_log("GOT: " . $results . " :TOG",0);
   session_write_close();
   echo $result;
 }
 else
 {
   error_log("Location not specified correctly in getObservations.php: ".$_GET["location"], 0);
 }


?>
