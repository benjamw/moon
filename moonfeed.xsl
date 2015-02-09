<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<html>
			<head>
				<link rel="stylesheet" href="moon.css" type="text/css" />
				<title>the iohelix moon feed page</title>
			</head>
			<body>
				<h1>Sun / Moon data right now</h1>

				<h2>Moon Data</h2>

				<h3>Current Location</h3>
				<p>
					Geocentric Longitude- <xsl:value-of select="data/moon/geocentricLongitude" /><br />
					Geocentric Latitude- <xsl:value-of select="data/moon/geocentricLatitude" /><br />
					Centers Distance (km)- <xsl:value-of select="data/moon/centersDistance" /><br />
					Right Ascension- <xsl:value-of select="data/moon/rightAscension" /><br />
					Declination- <xsl:value-of select="data/moon/declination" /><br />
					Angular Size- <xsl:value-of select="data/moon/angularSize" />
				</p>

				<h3>Phase</h3>
				<p>
					Phase- <xsl:value-of select="data/moon/phase" /><br />
					Age (days)- <xsl:value-of select="data/moon/age" /><br />
					Phase Angle- <xsl:value-of select="data/moon/phaseAngle" /><br />
					Bright Limb Angle- <xsl:value-of select="data/moon/brightLimbAngle" /><br />
					Percent Illuminated (Phase Angle)- <xsl:value-of select="data/moon/percentIlluminatedPa" /><br /><br />

					Elongation to Sun- <xsl:value-of select="data/moon/elongationToSun" /><br />
					Percent Illuminated (Elong. to Sun)- <xsl:value-of select="data/moon/percentIlluminatedEts" /><br /><br />

					Previous Phase- <xsl:value-of select="data/moon/prevPhase/phase" /><br />
					Days since- <xsl:value-of select="data/moon/prevPhase/daysSincePhase" />
				</p>

				<h3>Next Phase (local)</h3>
				<p>
					Phase- <xsl:value-of select="data/moon/nextPhase/local/phase" /><br />
					Date- <xsl:value-of select="data/moon/nextPhase/local/year" />|<xsl:value-of select="data/moon/nextPhase/local/month" />|<xsl:value-of select="data/moon/nextPhase/local/day" /> &#160; <xsl:value-of select="data/moon/nextPhase/local/hour" />:<xsl:value-of select="data/moon/nextPhase/local/minute" /><br />
					Julian- <xsl:value-of select="data/moon/nextPhase/local/JD" /><br />
					Julian (Ephemeris)- <xsl:value-of select="data/moon/nextPhase/local/JDE" /><br />
					&#916;T- <xsl:value-of select="data/moon/nextPhase/local/deltaT" /><br />
					Days to Phase- <xsl:value-of select="data/moon/nextPhase/local/daysToPhase" />
				</p>

				<h3>Current Lunation</h3>

				<h4>New Moon</h4>
				<p>
					Date- <xsl:value-of select="data/moon/currentLunation/local/newMoon/year" />|<xsl:value-of select="data/moon/currentLunation/local/newMoon/month" />|<xsl:value-of select="data/moon/currentLunation/local/newMoon/day" /> &#160; <xsl:value-of select="data/moon/currentLunation/local/newMoon/hour" />:<xsl:value-of select="data/moon/currentLunation/local/newMoon/minute" /><br />
					Julian- <xsl:value-of select="data/moon/currentLunation/local/newMoon/JD" /><br />
					Julian (Ephemeris)- <xsl:value-of select="data/moon/currentLunation/local/newMoon/JDE" /><br />
					&#916;T- <xsl:value-of select="data/moon/currentLunation/local/newMoon/deltaT" />
				</p>

				<h4>First Quarter</h4>
				<p>
					Date- <xsl:value-of select="data/moon/currentLunation/local/firstQuarter/year" />|<xsl:value-of select="data/moon/currentLunation/local/firstQuarter/month" />|<xsl:value-of select="data/moon/currentLunation/local/firstQuarter/day" /> &#160; <xsl:value-of select="data/moon/currentLunation/local/firstQuarter/hour" />:<xsl:value-of select="data/moon/currentLunation/local/firstQuarter/minute" /><br />
					Julian- <xsl:value-of select="data/moon/currentLunation/local/firstQuarter/JD" /><br />
					Julian (Ephemeris)- <xsl:value-of select="data/moon/currentLunation/local/firstQuarter/JDE" /><br />
					&#916;T- <xsl:value-of select="data/moon/currentLunation/local/firstQuarter/deltaT" />
				</p>

				<h4>Full Moon</h4>
				<p>
					Date- <xsl:value-of select="data/moon/currentLunation/local/fullMoon/year" />|<xsl:value-of select="data/moon/currentLunation/local/fullMoon/month" />|<xsl:value-of select="data/moon/currentLunation/local/fullMoon/day" /> &#160; <xsl:value-of select="data/moon/currentLunation/local/fullMoon/hour" />:<xsl:value-of select="data/moon/currentLunation/local/fullMoon/minute" /><br />
					Julian- <xsl:value-of select="data/moon/currentLunation/local/fullMoon/JD" /><br />
					Julian (Ephemeris)- <xsl:value-of select="data/moon/currentLunation/local/fullMoon/JDE" /><br />
					&#916;T- <xsl:value-of select="data/moon/currentLunation/local/fullMoon/deltaT" />
				</p>

				<h4>Last Quarter</h4>
				<p>
					Date- <xsl:value-of select="data/moon/currentLunation/local/lastQuarter/year" />|<xsl:value-of select="data/moon/currentLunation/local/lastQuarter/month" />|<xsl:value-of select="data/moon/currentLunation/local/lastQuarter/day" /> &#160; <xsl:value-of select="data/moon/currentLunation/local/lastQuarter/hour" />:<xsl:value-of select="data/moon/currentLunation/local/lastQuarter/minute" /><br />
					Julian- <xsl:value-of select="data/moon/currentLunation/local/lastQuarter/JD" /><br />
					Julian (Ephemeris)- <xsl:value-of select="data/moon/currentLunation/local/lastQuarter/JDE" /><br />
					&#916;T- <xsl:value-of select="data/moon/currentLunation/local/lastQuarter/deltaT" />
				</p>

				<h4>New Moon</h4>
				<p>
					Date- <xsl:value-of select="data/moon/currentLunation/local/newMoon2/year" />|<xsl:value-of select="data/moon/currentLunation/local/newMoon2/month" />|<xsl:value-of select="data/moon/currentLunation/local/newMoon2/day" /> &#160; <xsl:value-of select="data/moon/currentLunation/local/newMoon2/hour" />:<xsl:value-of select="data/moon/currentLunation/local/newMoon2/minute" /><br />
					Julian- <xsl:value-of select="data/moon/currentLunation/local/newMoon2/JD" /><br />
					Julian (Ephemeris)- <xsl:value-of select="data/moon/currentLunation/local/newMoon2/JDE" /><br />
					&#916;T- <xsl:value-of select="data/moon/currentLunation/local/newMoon2/deltaT" /><br /><br />

					Length (days)- <xsl:value-of select="data/moon/currentLunation/length" />
				</p>

				<hr />

				<h2>Sun Data</h2>
				<p>(needs some work, highly inaccurate)</p>

				<h3>Current Location</h3>
				<p>
					Geocentric Longitude- <xsl:value-of select="data/sun/geocentricLongitude" /><br />
					Geocentric Latitude- <xsl:value-of select="data/sun/geocentricLatitude" /><br />
					Centers Distance (km)- <xsl:value-of select="data/sun/centersDistance" /><br />
					Right Ascension- <xsl:value-of select="data/sun/rightAscension" /><br />
					Declination- <xsl:value-of select="data/sun/declination" /><br />
					Angular Size- <xsl:value-of select="data/sun/angularSize" />
				</p>

				<h3>Rise</h3>
				<p>
					UT- <xsl:value-of select="data/sun/rise/hour" />:<xsl:value-of select="data/sun/rise/minute" /><br />
					Local- <xsl:value-of select="data/sun/rise/hourLocal" />:<xsl:value-of select="data/sun/rise/minute" /><br />
					Julian- <xsl:value-of select="data/sun/rise/julian" /><br />
					Azimuth- <xsl:value-of select="data/sun/rise/azimuthN" />
				</p>

				<h3>Transit</h3>
				<p>
					UT- <xsl:value-of select="data/sun/transit/hour" />:<xsl:value-of select="data/sun/transit/minute" /><br />
					Local- <xsl:value-of select="data/sun/transit/hourLocal" />:<xsl:value-of select="data/sun/transit/minute" /><br />
					Julian- <xsl:value-of select="data/sun/transit/julian" /><br />
					Altitude- <xsl:value-of select="data/sun/transit/altitude" />
				</p>

				<h3>Set</h3>
				<p>
					UT- <xsl:value-of select="data/sun/set/hour" />:<xsl:value-of select="data/sun/set/minute" /><br />
					Local- <xsl:value-of select="data/sun/set/hourLocal" />:<xsl:value-of select="data/sun/set/minute" /><br />
					Julian- <xsl:value-of select="data/sun/set/julian" /><br />
					Azimuth- <xsl:value-of select="data/sun/set/azimuthN" />
				</p>

				<hr />

				<h2>Date and Time</h2>
				<p>
					UT- <xsl:value-of select="data/time/ut/year" />|<xsl:value-of select="data/time/ut/month" />|<xsl:value-of select="data/time/ut/day" />  &#160; <xsl:value-of select="data/time/ut/hour" />:<xsl:value-of select="data/time/ut/minute" /><br />
					Local- <xsl:value-of select="data/time/local/year" />|<xsl:value-of select="data/time/local/month" />|<xsl:value-of select="data/time/local/day" />  &#160; <xsl:value-of select="data/time/local/hour" />:<xsl:value-of select="data/time/local/minute" />
				</p>

				<hr />

				<h2>Julian Day</h2>
				<p>
					Civil- <xsl:value-of select="data/time/julianDay/civil" /><br />
					Modified- <xsl:value-of select="data/time/julianDay/modified" /><br />
					Ephemeris- <xsl:value-of select="data/time/julianDay/ephemeris" />
				</p>

				<hr />

				<h2>&#916;T</h2>
				<p>
					Seconds- <xsl:value-of select="data/time/deltaT/seconds" /><br />
					Minutes- <xsl:value-of select="data/time/deltaT/minutes" /><br />
					Julian- <xsl:value-of select="data/time/deltaT/julian" />
				</p>

				<hr class="end" />

				<h4>feed info</h4>
				<p class="h4">
					last source update- <xsl:value-of select="data/feed/srcUpdate" /><br />
					source version- <xsl:value-of select="data/feed/srcversion" /><br /><br />

					last xml update- <xsl:value-of select="data/feed/xmlUpdate" /><br />
					xml version- <xsl:value-of select="data/feed/xmlVersion" /><br /><br />

					last xsl update- 2009-09-26<br />
					xsl version- D<br /><br />

					execution time- <xsl:value-of select="data/feed/executionTime" />ms
				</p>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
