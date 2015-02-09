<?php

$time = microtime(true); // get microtime for execution time of script

/*-----------------------------------------------------------------------------*\
   benjam's astronomical feed script XML wrapper lite       started: 2004-12-28
   http://iohelix.net                                      finished: 2005-01-11
   benjam@iohelix.net                                  last updated: 2005-07-06

   used to wrap up the data given by moonelite.php into a nice, easy to use XML
   format.

   usage:
   http://iohelix.net/moon/moonlite.php?z={your time zone}

   where time zone differences East of Greenwich Meridian are positive.
   use integer hours for time zone differences.
   ie: -4 = 4 hours behind UT

   eg: http://iohelix.net/moon/moonlite.php?z=-7

   gives data for the Mountain Standard Time Zone
						 UTC-7 (UTC-6 = MDT)

\*-----------------------------------------------------------------------------*/

// update info
$xmlupdt = '2015-02-02';
$xmlvers = 'C';

$date = date('Y-m-d H:i:s');

// if no z value is set, default to UTC (z=0)
if ( isset($_GET['z']) ) {
  $z = (float) $_GET['z'];
}
else {
  $z = 0;
}

require 'moonelite.php'; // run the engine

// round all of the values used
$JD        = round( $JD,        2 ); // julian day
$aws       = round( $aws,       2 ); // angle with sun
$k_aws     = round( $k_aws,     1 ); // percent illuminated
$age       = round( $age,       2 ); // age of moon
$length    = round( $length,    2 ); // length of current lunation
$ph_daysto = round( $ph_daysto, 2 ); // days to major phase


header('Content-type: text/xml', true); // tell the browser it's an XML file
// print out the stuff
// in order for the Rainmeter skins to work, this format CANNOT be modified.
// not even the tiniest bit
echo <<< EOF
<?xml version="1.0" encoding="UTF-8"?>
<!-- generator="the iohelix moonlite script" -->
<?xml-stylesheet type="text/xsl" href="moonlite.xsl"?>
<data>
	<julianDay>{$JD}</julianDay>

	<moon>
		<elongationToSun>{$aws}</elongationToSun>
		<percentIlluminated>{$k_aws}</percentIlluminated>
		<age>{$age}</age>
		<phase>{$phaset}</phase>
		<length>{$length}</length>

		<nextPhase>
			<phase>{$ph_next}</phase>
			<year>{$ph_year}</year>
			<month>{$ph_month}</month>
			<day>{$ph_day}</day>
			<hour>{$ph_hour}</hour>
			<minute>{$ph_minute}</minute>
			<unix>{$ph_unix}</unix>
			<daysToPhase>{$ph_daysto}</daysToPhase>
		</nextPhase>
	</moon>

	<feed>
		<srcUpdate>{$updated}</srcUpdate>
		<srcversion>{$version}</srcversion>
		<xmlUpdate>{$xmlupdt}</xmlUpdate>
		<xmlVersion>{$xmlvers}</xmlVersion>
		<!-- <message></message> -->
		<xmlCached>{$date}</xmlCached>
EOF;

$time -= microtime(true); // figure the calculation time
echo "\n\t\t<executionTime>".(-$time)."</executionTime>\n\t</feed>\n</data>"; // and display it

