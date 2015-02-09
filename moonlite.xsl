<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<html>
			<head>
				<link rel="stylesheet" href="moon.css" type="text/css" />
				<title>the iohelix moon lite page</title>
			</head>
			<body>
				<h1>moon data right now (lite)</h1>

				<h2>julian day</h2>
				<p class="h2"><xsl:value-of select="data/julianDay" />  (rounded to two decimal places)</p>

				<h2>moon data</h2>
				<p class="h2">
					elongation to sun- <xsl:value-of select="data/moon/elongationToSun" />&#176;<br />
					percentage illuminated- <xsl:value-of select="data/moon/percentIlluminated" />&#37;<br />
					age- <xsl:value-of select="data/moon/age" /> of <xsl:value-of select="data/moon/length" /> days old<br />
					phase- <xsl:value-of select="data/moon/phase" />
				</p>

				<h3>next major phase</h3>
				<p class="h3">
					phase- <xsl:value-of select="data/moon/nextPhase/phase" /><br />
					<xsl:value-of select="data/moon/nextPhase/year" />-<xsl:value-of select="data/moon/nextPhase/month" />-<xsl:value-of select="data/moon/nextPhase/day" /> &#160; <xsl:value-of select="data/moon/nextPhase/hour" />&#58;<xsl:value-of select="data/moon/nextPhase/minute" /><br />
					days to phase- <xsl:value-of select="data/moon/nextPhase/daysToPhase" /> days
				</p>

				<hr class="end"/>

				<h4>feed info</h4>
				<p class="h4">
					last source update- <xsl:value-of select="data/feed/srcUpdate" /><br />
					source version- <xsl:value-of select="data/feed/srcversion" /><br /><br />

					last xml update- <xsl:value-of select="data/feed/xmlUpdate" /><br />
					xml version- <xsl:value-of select="data/feed/xmlVersion" /><br /><br />

					last xsl update- 2005-06-08<br />
					xsl version- C<br /><br />

					execution time- <xsl:value-of select="data/feed/executionTime" />ms
				</p>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>