<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">
<html>
<head>
	<link rel="stylesheet" href="moon.css" type="text/css" />
	<title>the iohelix moonlite page</title>
</head>
<body>

	<h1>moon data right now (lite)</h1>

<xsl:if test="data/feed/message != ''">
	<h2>IMPORTANT MESSAGE</h2>
	<xsl:value-of select="data/feed/message" />
</xsl:if>

	<h2>julian day</h2>
	<p class="h2"><xsl:value-of select="data/julianDay" />  (rounded to two decimal places)</p>
	<hr />

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
		unix- <xsl:value-of select="data/moon/nextPhase/unix" /><br />
		<xsl:value-of select="data/moon/nextPhase/year" />-<xsl:value-of select="data/moon/nextPhase/month" />-<xsl:value-of select="data/moon/nextPhase/day" /> &#160; <xsl:value-of select="data/moon/nextPhase/hour" />&#58;<xsl:value-of select="data/moon/nextPhase/minute" /><br />
		days to phase- <xsl:value-of select="data/moon/nextPhase/daysToPhase" /> days
	</p>

	<hr class="end"/>

	<h4>feed info</h4>
	<div class="feed">
	<p class="h4 feed">
		last source update- <xsl:value-of select="data/feed/srcUpdate" /><br />
		source version- <xsl:value-of select="data/feed/srcversion" /><br /><br />

		last xml update- <xsl:value-of select="data/feed/xmlUpdate" /><br />
		xml version- <xsl:value-of select="data/feed/xmlVersion" /><br /><br />

		last xsl update- 2015-02-02<br />
		xsl version- C<br /><br />

		execution time- <xsl:value-of select="data/feed/executionTime" />ms
	</p></div>

</body>
</html>
</xsl:template>
</xsl:stylesheet>