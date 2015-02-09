<?php

$time = microtime( ); // get microtime for execution time of script

/*-----------------------------------------------------------------------------*\
   benjam's astronomical feed script XML wrapper            started: 2004-12-28
   http://iohelix.net                                      finished: not yet
   benjam@iohelix.net                                  last updated: 2006-03-29

   used to wrap up the data given by moonengine.php into a nice, easy to use XML
   format.

   usage:
   http://iohelix.net/moon/moonfeed.php?lat={your latitude}&lon={your longitude}&z={your time zone}

   where North is positive, East is positive, and
   time zone differences East of Greenwich Meridian are positive
   use decimal hours for time zone differences
   ie: 4 hours, 30 minutes west of UT = -4.5

   ie: http://iohelix.net/moon/moonfeed.php?lat=40.76667&lon=-111.86667&z=-7

   gives data for Salt Lake City, UT, USA in the Mountain Standard Time Zone
				  N= 40 46'  W= 111 56'          UTC-7 (UTC-6 MDT)

\*-----------------------------------------------------------------------------*/

$xmlupdt = "2009-09-26";
$xmlvers = "B";

$date = date('Y-m-d H:i:s');

//$lat = $_GET['lat'];
//$lon = -$_GET['lon']; // calculation use astronomical longitude which is west positive

// if no z value is set, default to UTC (z=0)
if ( isset($_GET['z']) ) {
  $z = (int) $_GET['z'];
}
else {
  $z = 0;
}

define('IN_MOONFEED',true);
require("moonengine.php"); // run the engine

header('Content-type: text/xml', true); // tell the browser it's an XML file
// print out the stuff
print <<< EOF
<?xml version="1.0" encoding="iso-8859-1"?>
<!-- generator="the iohelix moon script" -->
<?xml-stylesheet type="text/xsl" href="moonfeed.xsl"?>
<data>
  <time>
	<julianDay>
	  <civil>{$JD}</civil>
	  <modified>{$MJD}</modified>
	  <ephemeris>{$JDE}</ephemeris>
	</julianDay>

	<deltaT>
	  <seconds>{$DeltaTsec}</seconds>
	  <minutes>{$DeltaTmin}</minutes>
	  <julian>{$DeltaT}</julian>
	</deltaT>

	<ut>
	  <year>{$yr}</year>
	  <month>{$mo}</month>
	  <day>{$dy}</day>
	  <hour>{$hr}</hour>
	  <minute>{$mn}</minute>
	</ut>

	<local> <!-- if timeLocal is off by one hour, you may need to take Daylight Saving Time into account with z= attribute -->
	  <year>{$yrL}</year>
	  <month>{$moL}</month>
	  <day>{$dyL}</day>
	  <hour>{$hrL}</hour>
	  <minute>{$mnL}</minute>
	</local>
  </time>

  <sun>
	<geocentricLongitude>{$lambda0}</geocentricLongitude>
	<geocentricLatitude>{$beta0}</geocentricLatitude>
	<centersDistance>{$R_km}</centersDistance>
	<rightAscension>{$alpha0}</rightAscension>
	<declination>{$delta0}</declination>
	<angularSize>{$theta0}</angularSize>
	<rise>
	  <julian>{$Sun["rise"]["JD"]}</julian>
	  <hour>{$Sun["rise"]["hour"]}</hour>
	  <minute>{$Sun["rise"]["minute"]}</minute>
	  <hourLocal>{$Sun["rise"]["hourL"]}</hourLocal>
	  <azimuthN>{$Sun["rise"]["azimuth"]}</azimuthN>
	</rise>
	<transit>
	  <julian>{$Sun["transit"]["JD"]}</julian>
	  <hour>{$Sun["transit"]["hour"]}</hour>
	  <minute>{$Sun["transit"]["minute"]}</minute>
	  <hourLocal>{$Sun["transit"]["hourL"]}</hourLocal>
	  <altitude>{$Sun["transit"]["altitude"]}</altitude>
	</transit>
	<set>
	  <julian>{$Sun["set"]["JD"]}</julian>
	  <hour>{$Sun["set"]["hour"]}</hour>
	  <minute>{$Sun["set"]["minute"]}</minute>
	  <hourLocal>{$Sun["set"]["hourL"]}</hourLocal>
	  <azimuthN>{$Sun["set"]["azimuth"]}</azimuthN>
	</set>
  </sun>

  <moon>
	<geocentricLongitude>{$lambda}</geocentricLongitude>
	<geocentricLatitude>{$beta}</geocentricLatitude>
	<centersDistance>{$Delta}</centersDistance>
	<equitorialHorizontalParallax>{$pi}</equitorialHorizontalParallax>
	<rightAscension>{$alpha}</rightAscension>
	<declination>{$delta}</declination>
	<angularSize>{$theta}</angularSize>
	<phaseAngle>{$i}</phaseAngle>
	<brightLimbAngle>{$chi}</brightLimbAngle>
	<percentIlluminatedPa>{$k}</percentIlluminatedPa>

	<elongationToSun>{$aws}</elongationToSun>
	<percentIlluminatedEts>{$k_aws}</percentIlluminatedEts>
	<age>{$age}</age>
	<phase>{$phaset}</phase>

	<prevPhase>
	  <phase>{$ph_prev}</phase>
	  <daysSincePhase>{$ph_daysfr}</daysSincePhase>
	</prevPhase>

	<nextPhase>
	  <local>
		<phase>{$ph_next}</phase>
		<JD>{$ph_JD}</JD>
		<JDE>{$ph_JDE}</JDE>
		<deltaT>{$ph_DeltaT}</deltaT>
		<year>{$ph_year}</year>
		<month>{$ph_month}</month>
		<day>{$ph_day}</day>
		<hour>{$ph_hour}</hour>
		<minute>{$ph_minute}</minute>
		<daysToPhase>{$ph_daysto}</daysToPhase>
	  </local>
	</nextPhase>

	<currentLunation>
	  <local>
		<newMoon>
		  <JD>{$PhaseWrap["New Moon"]["JD"]}</JD>
		  <JDE>{$PhaseWrap["New Moon"]["JDE"]}</JDE>
		  <deltaT>{$PhaseWrap["New Moon"]["DeltaT"]}</deltaT>
		  <year>{$PhaseWrap["New Moon"]["Year"]}</year>
		  <month>{$PhaseWrap["New Moon"]["Month"]}</month>
		  <day>{$PhaseWrap["New Moon"]["Day"]}</day>
		  <hour>{$PhaseWrap["New Moon"]["Hour"]}</hour>
		  <minute>{$PhaseWrap["New Moon"]["Minute"]}</minute>
		</newMoon>
		<firstQuarter>
		  <JD>{$PhaseWrap["First Quarter"]["JD"]}</JD>
		  <JDE>{$PhaseWrap["First Quarter"]["JDE"]}</JDE>
		  <deltaT>{$PhaseWrap["First Quarter"]["DeltaT"]}</deltaT>
		  <year>{$PhaseWrap["First Quarter"]["Year"]}</year>
		  <month>{$PhaseWrap["First Quarter"]["Month"]}</month>
		  <day>{$PhaseWrap["First Quarter"]["Day"]}</day>
		  <hour>{$PhaseWrap["First Quarter"]["Hour"]}</hour>
		  <minute>{$PhaseWrap["First Quarter"]["Minute"]}</minute>
		</firstQuarter>
		<fullMoon>
		  <JD>{$PhaseWrap["Full Moon"]["JD"]}</JD>
		  <JDE>{$PhaseWrap["Full Moon"]["JDE"]}</JDE>
		  <deltaT>{$PhaseWrap["Full Moon"]["DeltaT"]}</deltaT>
		  <year>{$PhaseWrap["Full Moon"]["Year"]}</year>
		  <month>{$PhaseWrap["Full Moon"]["Month"]}</month>
		  <day>{$PhaseWrap["Full Moon"]["Day"]}</day>
		  <hour>{$PhaseWrap["Full Moon"]["Hour"]}</hour>
		  <minute>{$PhaseWrap["Full Moon"]["Minute"]}</minute>
		</fullMoon>
		<lastQuarter>
		  <JD>{$PhaseWrap["Last Quarter"]["JD"]}</JD>
		  <JDE>{$PhaseWrap["Last Quarter"]["JDE"]}</JDE>
		  <deltaT>{$PhaseWrap["Last Quarter"]["DeltaT"]}</deltaT>
		  <year>{$PhaseWrap["Last Quarter"]["Year"]}</year>
		  <month>{$PhaseWrap["Last Quarter"]["Month"]}</month>
		  <day>{$PhaseWrap["Last Quarter"]["Day"]}</day>
		  <hour>{$PhaseWrap["Last Quarter"]["Hour"]}</hour>
		  <minute>{$PhaseWrap["Last Quarter"]["Minute"]}</minute>
		</lastQuarter>
		<newMoon2>
		  <JD>{$PhaseWrap["New Moon2"]["JD"]}</JD>
		  <JDE>{$PhaseWrap["New Moon2"]["JDE"]}</JDE>
		  <deltaT>{$PhaseWrap["New Moon2"]["DeltaT"]}</deltaT>
		  <year>{$PhaseWrap["New Moon2"]["Year"]}</year>
		  <month>{$PhaseWrap["New Moon2"]["Month"]}</month>
		  <day>{$PhaseWrap["New Moon2"]["Day"]}</day>
		  <hour>{$PhaseWrap["New Moon2"]["Hour"]}</hour>
		  <minute>{$PhaseWrap["New Moon2"]["Minute"]}</minute>
		</newMoon2>
	  </local>
	  <length>{$length}</length>
	</currentLunation>
  </moon>

  <feed>
	<srcUpdate>{$updated}</srcUpdate>
	<srcversion>{$version}</srcversion>
	<xmlUpdate>{$xmlupdt}</xmlUpdate>
	<xmlVersion>{$xmlvers}</xmlVersion>
	<xmlCached>{$date}</xmlCached>
EOF;

$time -= microtime(); // figure the calculation time
echo "\n    <executionTime>".-$time."</executionTime>\n  </feed>\n</data>"; // and display it

