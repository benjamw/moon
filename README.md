# moon
An XML moon data feed generator

this project contains everything you will need to power rainmeter
moon skins on your local machine.

contents
---------------------------------
- lbr.php:        calculates the position of the sun (needed by both engines)
- moonelite.php:  the engine for the moonlite feed (the one used by both rainmeter skins)
- moonengine.php: the engine for the moonfeed feed (not complete, but functional)
- moonfeed.php:   the wrapper for the original moon feed (not complete, but functional)
- moonfeed.xsl:   the transformation sheet for moonfeed (not complete, but functional)
- moonlite.php:   the wrapper for the lite version of the feed (the one used by both rainmeter skins)
- moonlite.xsl:   the transformation sheet for moonlite
- README.md:      this file
- update.html:    the page you are taken to in case of an xml feed layout change


upload these files to your localhost and change the variables in the moon skins to reflect the new location

i.e.
change http://iohelix.net/moon/  -->  http://localhost/your/path/moon/

this project does not contain the rainmeter moon skins, but both can be downloaded from my site:
http://iohelix.net/moon/MoonLite.zip
http://iohelix.net/moon/MoonShine.zip
(both case sensitive)


the original feed is included, and has much more information than the lite version, but it is not complete yet and the xsl is not complete either.

thanks for the interest and enjoy !!

if you make any major changes to the script that may benefit others, submit a pull request and i may implement the changes in future versions.