<?php
/*-----------------------------------------------------------------------------*\
   benjam's astronomical feed engine script - lite          started: 2004-12-01
   http://iohelix.net                                      finished: 2005-01-11
   benjam@iohelix.net                                  last updated: 2015-02-02

   most minor comments have been removed, full comments can be found in the
   full engine script, moonengine.php, found in the moon.zip file
\*-----------------------------------------------------------------------------*/

$updated = '2015-02-02';
$version = '1.04';

/*-----------------------------------------------------------------------------*\
   get current Julian Day (JD) and Ephemeris Time (JDE)
\*-----------------------------------------------------------------------------*/

// set the timezone
date_default_timezone_set('UTC');

// make a time object for now...  NOW ! (in UT)
$UTC = time( );

// break it up so we can use it
$yr = (int) date('Y', $UTC);
$mo = (int) date('n', $UTC);
$dy = (int) date('j', $UTC);
$hr = (int) date('G', $UTC);
$mn = (int) date('i', $UTC);
$sc = (int) date('s', $UTC);

// http://maia.usno.navy.mil/
// ftp://maia.usno.navy.mil/ser7/deltat.data
$DeltaT = 64.9082 * 1.157407e-5; // delta T last updated 2006-03-30
$DeltaT = 65.7736 * 1.157407e-5; // delta T last updated 2009-09-26
$DeltaT = 66.4829 * 1.157407e-5; // delta T last updated 2011-09-01
$DeltaT = 67.6925 * 1.157407e-5; // delta T last updated 2012-06-01 (data from 2012-04-01, with added leap second)
$DeltaT = 67.3890 * 1.157407e-5; // delta T last updated 2014-09-06

if ( $mo > 2 ) {
	$y = $yr + 4716;
	$m = $mo + 1;
}
else {
	$y = $yr + 4715;
	$m = $mo + 13;
}

$d = $dy + ( $hr + ( $mn + ( $sc / 60 ) ) / 60 ) / 24;

$JD  = ipart( 365.25 * $y ) + ipart( 30.6001 * $m ) + $d - 1537.5;
$JDE = $JD + $DeltaT;


/*-----------------------------------------------------------------------------*\
   calculate position of the moon
\*-----------------------------------------------------------------------------*/

$T  = ( $JDE - 2451545 ) / 36525;
$T2 = $T  * $T;
$T3 = $T2 * $T;
$T4 = $T3 * $T;

$Lp = round( fnred( 218.3164477 + 481267.88123421 * $T - 0.0015786 * $T2 + $T3 /   538841 - $T4 /  65194000 ), 6 );
$D  = round( fnred( 297.8501921 + 445267.1114034  * $T - 0.0018819 * $T2 + $T3 /   545868 - $T4 / 113065000 ), 6 );
$M  = round( fnred( 357.5291092 +  35999.0502909  * $T - 0.0001536 * $T2 + $T3 / 24490000 ), 6 );
$Mp = round( fnred( 134.9633964 + 477198.8675055  * $T + 0.0087414 * $T2 + $T3 /    69699 - $T4 /  14712000 ), 6 );
$F  = round( fnred(  93.2720950 + 483202.0175233  * $T - 0.0036539 * $T2 - $T3 /  3526000 + $T4 / 863310000 ), 6 );

$E  = round( 1 - 0.002516 * $T - 0.0000074 * $T2, 6 );
$E2 = $E * $E;

$A1 = round( fnred( 119.75 +    131.849 * $T ), 2 );
$A2 = round( fnred(  53.09 + 479264.290 * $T ), 2 );
$A3 = round( fnred( 313.45 + 481266.484 * $T ), 2 );

// calculation for longitude
$Sigma_l =      6288774 * dsin(  0  *$D  +0  *$M  +1  *$Mp  +0  *$F )
		 +      1274027 * dsin(  2  *$D  +0  *$M  -1  *$Mp  +0  *$F )
		 +       658314 * dsin(  2  *$D  +0  *$M  +0  *$Mp  +0  *$F )
		 +       213618 * dsin(  0  *$D  +0  *$M  +2  *$Mp  +0  *$F )
		 - $E  * 185116 * dsin(  0  *$D  +1  *$M  +0  *$Mp  +0  *$F )
		 -       114332 * dsin(  0  *$D  +0  *$M  +0  *$Mp  +2  *$F )
		 +        58793 * dsin(  2  *$D  +0  *$M  -2  *$Mp  +0  *$F )
		 + $E  *  57066 * dsin(  2  *$D  -1  *$M  -1  *$Mp  +0  *$F )
		 +        53322 * dsin(  2  *$D  +0  *$M  +1  *$Mp  +0  *$F )
		 + $E  *  45758 * dsin(  2  *$D  -1  *$M  +0  *$Mp  +0  *$F )
		 - $E  *  40923 * dsin(  0  *$D  +1  *$M  -1  *$Mp  +0  *$F )
		 -        34720 * dsin(  1  *$D  +0  *$M  +0  *$Mp  +0  *$F )
		 - $E  *  30383 * dsin(  0  *$D  +1  *$M  +1  *$Mp  +0  *$F )
		 +        15327 * dsin(  2  *$D  +0  *$M  +0  *$Mp  -2  *$F )
		 -        12528 * dsin(  0  *$D  +0  *$M  +1  *$Mp  +2  *$F )
		 +        10980 * dsin(  0  *$D  +0  *$M  +1  *$Mp  -2  *$F )
		 +        10675 * dsin(  4  *$D  +0  *$M  -1  *$Mp  +0  *$F )
		 +        10034 * dsin(  0  *$D  +0  *$M  +3  *$Mp  +0  *$F )
		 +         8548 * dsin(  4  *$D  +0  *$M  -2  *$Mp  +0  *$F )
		 - $E  *   7888 * dsin(  2  *$D  +1  *$M  -1  *$Mp  +0  *$F )
		 - $E  *   6766 * dsin(  2  *$D  +1  *$M  +0  *$Mp  +0  *$F )
		 -         5163 * dsin(  1  *$D  +0  *$M  -1  *$Mp  +0  *$F )
		 + $E  *   4987 * dsin(  1  *$D  +1  *$M  +0  *$Mp  +0  *$F )
		 + $E  *   4036 * dsin(  2  *$D  -1  *$M  +1  *$Mp  +0  *$F )
		 +         3994 * dsin(  2  *$D  +0  *$M  +2  *$Mp  +0  *$F )
		 +         3861 * dsin(  4  *$D  +0  *$M  +0  *$Mp  +0  *$F )
		 +         3665 * dsin(  2  *$D  +0  *$M  -3  *$Mp  +0  *$F )
		 - $E  *   2689 * dsin(  0  *$D  +1  *$M  -2  *$Mp  +0  *$F )
		 -         2602 * dsin(  2  *$D  +0  *$M  -1  *$Mp  +2  *$F )
		 + $E  *   2390 * dsin(  2  *$D  -1  *$M  -2  *$Mp  +0  *$F )
		 -         2348 * dsin(  1  *$D  +0  *$M  +1  *$Mp  +0  *$F )
		 + $E2 *   2236 * dsin(  2  *$D  -2  *$M  +0  *$Mp  +0  *$F )
		 - $E  *   2120 * dsin(  0  *$D  +1  *$M  +2  *$Mp  +0  *$F )
		 - $E2 *   2069 * dsin(  0  *$D  +2  *$M  +0  *$Mp  +0  *$F )
		 + $E2 *   2048 * dsin(  2  *$D  -2  *$M  -1  *$Mp  +0  *$F )
		 -         1773 * dsin(  2  *$D  +0  *$M  +1  *$Mp  -2  *$F )
		 -         1595 * dsin(  2  *$D  +0  *$M  +0  *$Mp  +2  *$F )
		 + $E  *   1215 * dsin(  4  *$D  -1  *$M  -1  *$Mp  +0  *$F )
		 -         1110 * dsin(  0  *$D  +0  *$M  +2  *$Mp  +2  *$F )
		 -          892 * dsin(  3  *$D  +0  *$M  -1  *$Mp  +0  *$F )
		 - $E  *    810 * dsin(  2  *$D  +1  *$M  +1  *$Mp  +0  *$F )
		 + $E  *    759 * dsin(  4  *$D  -1  *$M  -2  *$Mp  +0  *$F )
		 - $E2 *    713 * dsin(  0  *$D  +2  *$M  -1  *$Mp  +0  *$F )
		 - $E2 *    700 * dsin(  2  *$D  +2  *$M  -1  *$Mp  +0  *$F )
		 + $E  *    691 * dsin(  2  *$D  +1  *$M  -2  *$Mp  +0  *$F )
		 + $E  *    596 * dsin(  2  *$D  -1  *$M  +0  *$Mp  -2  *$F )
		 +          549 * dsin(  4  *$D  +0  *$M  +1  *$Mp  +0  *$F )
		 +          537 * dsin(  0  *$D  +0  *$M  +4  *$Mp  +0  *$F )
		 + $E  *    520 * dsin(  4  *$D  -1  *$M  +0  *$Mp  +0  *$F )
		 -          487 * dsin(  1  *$D  +0  *$M  -2  *$Mp  +0  *$F )
		 - $E  *    399 * dsin(  2  *$D  +1  *$M  +0  *$Mp  -2  *$F )
		 -          381 * dsin(  0  *$D  +0  *$M  +2  *$Mp  -2  *$F )
		 + $E  *    351 * dsin(  1  *$D  +1  *$M  +1  *$Mp  +0  *$F )
		 -          340 * dsin(  3  *$D  +0  *$M  -2  *$Mp  +0  *$F )
		 +          330 * dsin(  4  *$D  +0  *$M  -3  *$Mp  +0  *$F )
		 + $E  *    327 * dsin(  2  *$D  -1  *$M  +2  *$Mp  +0  *$F )
		 - $E2 *    323 * dsin(  0  *$D  +2  *$M  +1  *$Mp  +0  *$F )
		 + $E  *    299 * dsin(  1  *$D  +1  *$M  -1  *$Mp  +0  *$F )
		 +          294 * dsin(  2  *$D  +0  *$M  +3  *$Mp  +0  *$F )
		 + 3958 * dsin( $A1 )
		 + 1962 * dsin( $Lp - $F )
		 +  318 * dsin( $A2 );
$Sigma_l = round( $Sigma_l );

// calculation for latitude
$Sigma_b =    5128122 * dsin(  0  *$D  +0  *$M  +0  *$Mp  +1  *$F )
		 +     280602 * dsin(  0  *$D  +0  *$M  +1  *$Mp  +1  *$F )
		 +     277693 * dsin(  0  *$D  +0  *$M  +1  *$Mp  -1  *$F )
		 +     173237 * dsin(  2  *$D  +0  *$M  +0  *$Mp  -1  *$F )
		 +      55413 * dsin(  2  *$D  +0  *$M  -1  *$Mp  +1  *$F )
		 +      46271 * dsin(  2  *$D  +0  *$M  -1  *$Mp  -1  *$F )
		 +      32573 * dsin(  2  *$D  +0  *$M  +0  *$Mp  +1  *$F )
		 +      17198 * dsin(  0  *$D  +0  *$M  +2  *$Mp  +1  *$F )
		 +       9266 * dsin(  2  *$D  +0  *$M  +1  *$Mp  -1  *$F )
		 +       8822 * dsin(  0  *$D  +0  *$M  +2  *$Mp  -1  *$F )
		 + $E  * 8216 * dsin(  2  *$D  -1  *$M  +0  *$Mp  -1  *$F )
		 +       4324 * dsin(  2  *$D  +0  *$M  -2  *$Mp  -1  *$F )
		 +       4200 * dsin(  2  *$D  +0  *$M  +1  *$Mp  +1  *$F )
		 - $E  * 3359 * dsin(  2  *$D  +1  *$M  +0  *$Mp  -1  *$F )
		 + $E  * 2463 * dsin(  2  *$D  -1  *$M  -1  *$Mp  +1  *$F )
		 + $E  * 2211 * dsin(  2  *$D  -1  *$M  +0  *$Mp  +1  *$F )
		 + $E  * 2065 * dsin(  2  *$D  -1  *$M  -1  *$Mp  -1  *$F )
		 - $E  * 1870 * dsin(  0  *$D  +1  *$M  -1  *$Mp  -1  *$F )
		 +       1828 * dsin(  4  *$D  +0  *$M  -1  *$Mp  -1  *$F )
		 - $E  * 1794 * dsin(  0  *$D  +1  *$M  +0  *$Mp  +1  *$F )
		 -       1749 * dsin(  0  *$D  +0  *$M  +0  *$Mp  +3  *$F )
		 - $E  * 1565 * dsin(  0  *$D  +1  *$M  -1  *$Mp  +1  *$F )
		 -       1491 * dsin(  1  *$D  +0  *$M  +0  *$Mp  +1  *$F )
		 - $E  * 1475 * dsin(  0  *$D  +1  *$M  +1  *$Mp  +1  *$F )
		 - $E  * 1410 * dsin(  0  *$D  +1  *$M  +1  *$Mp  -1  *$F )
		 - $E  * 1344 * dsin(  0  *$D  +1  *$M  +0  *$Mp  -1  *$F )
		 -       1335 * dsin(  1  *$D  +0  *$M  +0  *$Mp  -1  *$F )
		 +       1107 * dsin(  0  *$D  +0  *$M  +3  *$Mp  +1  *$F )
		 +       1021 * dsin(  4  *$D  +0  *$M  +0  *$Mp  -1  *$F )
		 +        833 * dsin(  4  *$D  +0  *$M  -1  *$Mp  +1  *$F )
		 +        777 * dsin(  0  *$D  +0  *$M  +1  *$Mp  -3  *$F )
		 +        671 * dsin(  4  *$D  +0  *$M  -2  *$Mp  +1  *$F )
		 +        607 * dsin(  2  *$D  +0  *$M  +0  *$Mp  -3  *$F )
		 +        596 * dsin(  2  *$D  +0  *$M  +2  *$Mp  -1  *$F )
		 + $E  *  491 * dsin(  2  *$D  -1  *$M  +1  *$Mp  -1  *$F )
		 -        451 * dsin(  2  *$D  +0  *$M  -2  *$Mp  +1  *$F )
		 +        439 * dsin(  0  *$D  +0  *$M  +3  *$Mp  -1  *$F )
		 +        422 * dsin(  2  *$D  +0  *$M  +2  *$Mp  +1  *$F )
		 +        421 * dsin(  2  *$D  +0  *$M  -3  *$Mp  -1  *$F )
		 - $E  *  366 * dsin(  2  *$D  +1  *$M  -1  *$Mp  +1  *$F )
		 - $E  *  351 * dsin(  2  *$D  +1  *$M  +0  *$Mp  +1  *$F )
		 +        331 * dsin(  4  *$D  +0  *$M  +0  *$Mp  +1  *$F )
		 + $E  *  315 * dsin(  2  *$D  -1  *$M  +1  *$Mp  +1  *$F )
		 + $E2 *  302 * dsin(  2  *$D  -2  *$M  +0  *$Mp  -1  *$F )
		 -        283 * dsin(  0  *$D  +0  *$M  +1  *$Mp  +3  *$F )
		 - $E  *  229 * dsin(  2  *$D  +1  *$M  +1  *$Mp  -1  *$F )
		 + $E  *  223 * dsin(  1  *$D  +1  *$M  +0  *$Mp  -1  *$F )
		 + $E  *  223 * dsin(  1  *$D  +1  *$M  +0  *$Mp  +1  *$F )
		 - $E  *  220 * dsin(  0  *$D  +1  *$M  -2  *$Mp  -1  *$F )
		 - $E  *  220 * dsin(  2  *$D  +1  *$M  -1  *$Mp  -1  *$F )
		 -        185 * dsin(  1  *$D  +0  *$M  +1  *$Mp  +1  *$F )
		 + $E  *  181 * dsin(  2  *$D  -1  *$M  -2  *$Mp  -1  *$F )
		 - $E  *  177 * dsin(  0  *$D  +1  *$M  +2  *$Mp  +1  *$F )
		 +        176 * dsin(  4  *$D  +0  *$M  -2  *$Mp  -1  *$F )
		 + $E  *  166 * dsin(  4  *$D  -1  *$M  -1  *$Mp  -1  *$F )
		 -        164 * dsin(  1  *$D  +0  *$M  +1  *$Mp  -1  *$F )
		 +        132 * dsin(  4  *$D  +0  *$M  +1  *$Mp  -1  *$F )
		 -        119 * dsin(  1  *$D  +0  *$M  -1  *$Mp  -1  *$F )
		 + $E  *  115 * dsin(  4  *$D  -1  *$M  +0  *$Mp  -1  *$F )
		 + $E2 *  107 * dsin(  2  *$D  -2  *$M  +0  *$Mp  +1  *$F )
		 - 2235 * dsin( $Lp )
		 +  382 * dsin( $A3 )
		 +  175 * dsin( $A1 - $F )
		 +  175 * dsin( $A1 + $F )
		 +  127 * dsin( $Lp - $Mp )
		 -  115 * dsin( $Lp + $Mp );
$Sigma_b = round( $Sigma_b );

$lambda = $Lp + ( $Sigma_l / 1000000 );
$beta   = $Sigma_b / 1000000;


/*-----------------------------------------------------------------------------*\
   calculate nutation
\*-----------------------------------------------------------------------------*/

$T  = ( $JDE - 2451545 ) / 36525;
$T2 = $T  * $T;
$T3 = $T2 * $T;
$T4 = $T3 * $T;

$D     = round( fnred( 297.85036 + 445267.111480 * $T - 0.0019142 * $T2 + $T3 / 189474 ), 4 );
$M     = round( fnred( 357.52772 +  35999.050340 * $T - 0.0001603 * $T2 + $T3 / 300000 ), 4 );
$Mp    = round( fnred( 134.96298 + 477198.867398 * $T + 0.0086972 * $T2 + $T3 /  56250 ), 4 );
$F     = round( fnred(  93.27191 + 483202.017538 * $T - 0.0036825 * $T2 - $T3 / 327270 ), 4 );
$Omega = round( fnred( 125.04452 -   1934.136261 * $T + 0.0020708 * $T2 + $T3 / 450000 ), 4 );

// calculation for nutation in longitude
$Delta_psi =(( -171996 -174.2 * $T ) * dsin(   0  *$D  +0  *$M  +0  *$Mp  +0  *$F  +1  *$Omega )
		   + (  -13187   -1.6 * $T ) * dsin(  -2  *$D  +0  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
		   + (   -2274   -0.2 * $T ) * dsin(   0  *$D  +0  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
		   + (    2062   +0.2 * $T ) * dsin(   0  *$D  +0  *$M  +0  *$Mp  +0  *$F  +2  *$Omega )
		   + (    1426   -3.4 * $T ) * dsin(   0  *$D  +1  *$M  +0  *$Mp  +0  *$F  +0  *$Omega )
		   + (     712   +0.1 * $T ) * dsin(   0  *$D  +0  *$M  +1  *$Mp  +0  *$F  +0  *$Omega )
		   + (    -517   +1.2 * $T ) * dsin(  -2  *$D  +1  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
		   + (    -386   -0.4 * $T ) * dsin(   0  *$D  +0  *$M  +0  *$Mp  +2  *$F  +1  *$Omega )
		   + (    -301             ) * dsin(   0  *$D  +0  *$M  +1  *$Mp  +2  *$F  +2  *$Omega )
		   + (     217   -0.5 * $T ) * dsin(  -2  *$D  -1  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
		   + (    -158             ) * dsin(  -2  *$D  +0  *$M  +1  *$Mp  +0  *$F  +0  *$Omega )
		   + (     129   +0.1 * $T ) * dsin(  -2  *$D  +0  *$M  +0  *$Mp  +2  *$F  +1  *$Omega )
		   + (     123             ) * dsin(   0  *$D  +0  *$M  -1  *$Mp  +2  *$F  +2  *$Omega )
		   + (      63             ) * dsin(   2  *$D  +0  *$M  +0  *$Mp  +0  *$F  +0  *$Omega )
		   + (      63   +0.1 * $T ) * dsin(   0  *$D  +0  *$M  +1  *$Mp  +0  *$F  +1  *$Omega )
		   + (     -59             ) * dsin(   2  *$D  +0  *$M  -1  *$Mp  +2  *$F  +2  *$Omega )
		   + (     -58   -0.1 * $T ) * dsin(   0  *$D  +0  *$M  -1  *$Mp  +0  *$F  +1  *$Omega )
		   + (     -51             ) * dsin(   0  *$D  +0  *$M  +1  *$Mp  +2  *$F  +1  *$Omega )
		   + (      48             ) * dsin(  -2  *$D  +0  *$M  +2  *$Mp  +0  *$F  +0  *$Omega )
		   + (      46             ) * dsin(   0  *$D  +0  *$M  -2  *$Mp  +2  *$F  +1  *$Omega )
		   + (     -38             ) * dsin(   2  *$D  +0  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
		   + (     -31             ) * dsin(   0  *$D  +0  *$M  +2  *$Mp  +2  *$F  +2  *$Omega )
		   + (      29             ) * dsin(   0  *$D  +0  *$M  +2  *$Mp  +0  *$F  +0  *$Omega )
		   + (      29             ) * dsin(  -2  *$D  +0  *$M  +1  *$Mp  +2  *$F  +2  *$Omega )
		   + (      26             ) * dsin(   0  *$D  +0  *$M  +0  *$Mp  +2  *$F  +0  *$Omega )
		   + (     -22             ) * dsin(  -2  *$D  +0  *$M  +0  *$Mp  +2  *$F  +0  *$Omega )
		   + (      21             ) * dsin(   0  *$D  +0  *$M  -1  *$Mp  +2  *$F  +1  *$Omega )
		   + (      17   -0.1 * $T ) * dsin(   0  *$D  +2  *$M  +0  *$Mp  +0  *$F  +0  *$Omega )
		   + (      16             ) * dsin(   2  *$D  +0  *$M  -1  *$Mp  +0  *$F  +1  *$Omega )
		   + (     -16   +0.1 * $T ) * dsin(  -2  *$D  +2  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
		   + (     -15             ) * dsin(   0  *$D  +1  *$M  +0  *$Mp  +0  *$F  +1  *$Omega )
		   + (     -13             ) * dsin(  -2  *$D  +0  *$M  +1  *$Mp  +0  *$F  +1  *$Omega )
		   + (     -12             ) * dsin(   0  *$D  -1  *$M  +0  *$Mp  +0  *$F  +1  *$Omega )
		   + (      11             ) * dsin(   0  *$D  +0  *$M  +2  *$Mp  -2  *$F  +0  *$Omega )
		   + (     -10             ) * dsin(   2  *$D  +0  *$M  -1  *$Mp  +2  *$F  +1  *$Omega )
		   + (      -8             ) * dsin(   2  *$D  +0  *$M  +1  *$Mp  +2  *$F  +2  *$Omega )
		   + (       7             ) * dsin(   0  *$D  +1  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
		   + (      -7             ) * dsin(  -2  *$D  +1  *$M  +1  *$Mp  +0  *$F  +0  *$Omega )
		   + (      -7             ) * dsin(   0  *$D  -1  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
		   + (      -7             ) * dsin(   2  *$D  +0  *$M  +0  *$Mp  +2  *$F  +1  *$Omega )
		   + (       6             ) * dsin(   2  *$D  +0  *$M  +1  *$Mp  +0  *$F  +0  *$Omega )
		   + (       6             ) * dsin(  -2  *$D  +0  *$M  +2  *$Mp  +2  *$F  +2  *$Omega )
		   + (       6             ) * dsin(  -2  *$D  +0  *$M  +1  *$Mp  +2  *$F  +1  *$Omega )
		   + (      -6             ) * dsin(   2  *$D  +0  *$M  -2  *$Mp  +0  *$F  +1  *$Omega )
		   + (      -6             ) * dsin(   2  *$D  +0  *$M  +0  *$Mp  +0  *$F  +1  *$Omega )
		   + (       5             ) * dsin(   0  *$D  -1  *$M  +1  *$Mp  +0  *$F  +0  *$Omega )
		   + (      -5             ) * dsin(  -2  *$D  -1  *$M  +0  *$Mp  +2  *$F  +1  *$Omega )
		   + (      -5             ) * dsin(  -2  *$D  +0  *$M  +0  *$Mp  +0  *$F  +1  *$Omega )
		   + (      -5             ) * dsin(   0  *$D  +0  *$M  +2  *$Mp  +2  *$F  +1  *$Omega )
		   + (       4             ) * dsin(  -2  *$D  +0  *$M  +2  *$Mp  +0  *$F  +1  *$Omega )
		   + (       4             ) * dsin(  -2  *$D  +1  *$M  +0  *$Mp  +2  *$F  +1  *$Omega )
		   + (       4             ) * dsin(   0  *$D  +0  *$M  +1  *$Mp  -2  *$F  +0  *$Omega )
		   + (      -4             ) * dsin(  -1  *$D  +0  *$M  +1  *$Mp  +0  *$F  +0  *$Omega )
		   + (      -4             ) * dsin(  -2  *$D  +1  *$M  +0  *$Mp  +0  *$F  +0  *$Omega )
		   + (      -4             ) * dsin(   1  *$D  +0  *$M  +0  *$Mp  +0  *$F  +0  *$Omega )
		   + (       3             ) * dsin(   0  *$D  +0  *$M  +1  *$Mp  +2  *$F  +0  *$Omega )
		   + (      -3             ) * dsin(   0  *$D  +0  *$M  -2  *$Mp  +2  *$F  +2  *$Omega )
		   + (      -3             ) * dsin(  -1  *$D  -1  *$M  +1  *$Mp  +0  *$F  +0  *$Omega )
		   + (      -3             ) * dsin(   0  *$D  +1  *$M  +1  *$Mp  +0  *$F  +0  *$Omega )
		   + (      -3             ) * dsin(   0  *$D  -1  *$M  +1  *$Mp  +2  *$F  +2  *$Omega )
		   + (      -3             ) * dsin(   2  *$D  -1  *$M  -1  *$Mp  +2  *$F  +2  *$Omega )
		   + (      -3             ) * dsin(   0  *$D  +0  *$M  +3  *$Mp  +2  *$F  +2  *$Omega )
		   + (      -3             ) * dsin(   2  *$D  -1  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
		  )* 2.77777777778e-8;

// calculation for nutation in obliquity
$Delta_epsilon =(( 92025 +8.9 * $T ) * dcos(   0  *$D  +0  *$M  +0  *$Mp  +0  *$F  +1  *$Omega )
			   + (  5736 -3.1 * $T ) * dcos(  -2  *$D  +0  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
			   + (   977 -0.5 * $T ) * dcos(   0  *$D  +0  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
			   + (  -895 +0.5 * $T ) * dcos(   0  *$D  +0  *$M  +0  *$Mp  +0  *$F  +2  *$Omega )
			   + (    54 -0.1 * $T ) * dcos(   0  *$D  +1  *$M  +0  *$Mp  +0  *$F  +0  *$Omega )
			   + (    -7           ) * dcos(   0  *$D  +0  *$M  +1  *$Mp  +0  *$F  +0  *$Omega )
			   + (   224 -0.6 * $T ) * dcos(  -2  *$D  +1  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
			   + (   200           ) * dcos(   0  *$D  +0  *$M  +0  *$Mp  +2  *$F  +1  *$Omega )
			   + (   129 -0.1 * $T ) * dcos(   0  *$D  +0  *$M  +1  *$Mp  +2  *$F  +2  *$Omega )
			   + (   -95 +0.3 * $T ) * dcos(  -2  *$D  -1  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
			   + (   -70           ) * dcos(  -2  *$D  +0  *$M  +0  *$Mp  +2  *$F  +1  *$Omega )
			   + (   -53           ) * dcos(   0  *$D  +0  *$M  -1  *$Mp  +2  *$F  +2  *$Omega )
			   + (   -33           ) * dcos(   0  *$D  +0  *$M  +1  *$Mp  +0  *$F  +1  *$Omega )
			   + (    26           ) * dcos(   2  *$D  +0  *$M  -1  *$Mp  +2  *$F  +2  *$Omega )
			   + (    32           ) * dcos(   0  *$D  +0  *$M  -1  *$Mp  +0  *$F  +1  *$Omega )
			   + (    27           ) * dcos(   0  *$D  +0  *$M  +1  *$Mp  +2  *$F  +1  *$Omega )
			   + (   -24           ) * dcos(   0  *$D  +0  *$M  -2  *$Mp  +2  *$F  +1  *$Omega )
			   + (    16           ) * dcos(   2  *$D  +0  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
			   + (    13           ) * dcos(   0  *$D  +0  *$M  +2  *$Mp  +2  *$F  +2  *$Omega )
			   + (   -12           ) * dcos(  -2  *$D  +0  *$M  +1  *$Mp  +2  *$F  +2  *$Omega )
			   + (   -10           ) * dcos(   0  *$D  +0  *$M  -1  *$Mp  +2  *$F  +1  *$Omega )
			   + (    -8           ) * dcos(   2  *$D  +0  *$M  -1  *$Mp  +0  *$F  +1  *$Omega )
			   + (     7           ) * dcos(  -2  *$D  +2  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
			   + (     9           ) * dcos(   0  *$D  +1  *$M  +0  *$Mp  +0  *$F  +1  *$Omega )
			   + (     7           ) * dcos(  -2  *$D  +0  *$M  +1  *$Mp  +0  *$F  +1  *$Omega )
			   + (     6           ) * dcos(   0  *$D  -1  *$M  +0  *$Mp  +0  *$F  +1  *$Omega )
			   + (     5           ) * dcos(   2  *$D  +0  *$M  -1  *$Mp  +2  *$F  +1  *$Omega )
			   + (     3           ) * dcos(   2  *$D  +0  *$M  +1  *$Mp  +2  *$F  +2  *$Omega )
			   + (    -3           ) * dcos(   0  *$D  +1  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
			   + (     3           ) * dcos(   0  *$D  -1  *$M  +0  *$Mp  +2  *$F  +2  *$Omega )
			   + (     3           ) * dcos(   2  *$D  +0  *$M  +0  *$Mp  +2  *$F  +1  *$Omega )
			   + (    -3           ) * dcos(  -2  *$D  +0  *$M  +2  *$Mp  +2  *$F  +2  *$Omega )
			   + (    -3           ) * dcos(  -2  *$D  +0  *$M  +1  *$Mp  +2  *$F  +1  *$Omega )
			   + (     3           ) * dcos(   2  *$D  +0  *$M  -2  *$Mp  +0  *$F  +1  *$Omega )
			   + (     3           ) * dcos(   2  *$D  +0  *$M  +0  *$Mp  +0  *$F  +1  *$Omega )
			   + (     3           ) * dcos(  -2  *$D  -1  *$M  +0  *$Mp  +2  *$F  +1  *$Omega )
			   + (     3           ) * dcos(  -2  *$D  +0  *$M  +0  *$Mp  +0  *$F  +1  *$Omega )
			   + (     3           ) * dcos(   0  *$D  +0  *$M  +2  *$Mp  +2  *$F  +1  *$Omega )
			  )* 2.77777777778e-8;

$epsilon0 = 23.4392911111 - .013004166667 * $T - 1.63888888889e-7 * $T2 + 5.03611111111e-7 * $T3;
$epsilon  = $Delta_epsilon + $epsilon0;


/*-----------------------------------------------------------------------------*\
   transform moon location from ecliptical to equitorial
\*-----------------------------------------------------------------------------*/

$lambda += round( $Delta_psi, 6 );

$alpha = fnred( rad2deg( atan2( ( dsin( $lambda ) * dcos( round( $epsilon, 6 ) ) - dtan( $beta ) * dsin( round( $epsilon, 6 ) ) ), ( dcos( $lambda ) ) ) ) );
$delta = rad2deg( asin( dsin( $beta ) * dcos( round( $epsilon, 6 ) ) + dcos( $beta ) * dsin( round( $epsilon, 6 ) ) * dsin( $lambda ) ) );


/*-----------------------------------------------------------------------------*\
   calculate heliocentric position of the earth (VSOP87)
\*-----------------------------------------------------------------------------*/

require 'lbr.php';

$circle_dot = fnred( rad2deg( $L ) + 180 );
$beta0      = fnred( -rad2deg( $B ) );

// convert R to km
$R_km = 149597900 * $R;


/*-----------------------------------------------------------------------------*\
   transform location from heliocentric earth to geocentric sun
\*-----------------------------------------------------------------------------*/

$T  = ( $JDE - 2451545 ) / 36525;
$T2 = $T  * $T;
$T3 = $T2 * $T;
$T4 = $T3 * $T;

// convert to FK5 system
$lambdap = $circle_dot - 1.397 * $T - 0.00031 * $T2;

$Delta_circle_dot = -2.5091666666667e-5;
$Delta_beta0      = 1.0877777777778e-5 * ( dcos( $lambdap ) - dsin( $lambdap ) );

$beta0 += $Delta_beta0;

// correct for nutation and abberation
$lambda0 = $Delta_psi + $circle_dot;

$e     = 0.016708634 - 0.000042037 * $T - 0.0000001267 * $T2 + 0.00000000014 * $T3;
$a0    = 1.000001018;
$kappa = 0.0056942222222224;

$kappa0   = $kappa * $a0 * ( 1 - $e * $e );
$kappa0p  = 0.00569161111111;
$lambda0 += -( $kappa0p / $R ) + $Delta_circle_dot;

$epsilon0 = $epsilon + 0.00256 * dcos( $Omega );


/*-----------------------------------------------------------------------------*\
   transform sun location from ecliptical to equitorial
\*-----------------------------------------------------------------------------*/
$alpha0 = fnred( rad2deg( atan2( ( dsin( $lambda0 ) * dcos( round( $epsilon0, 6 ) ) - dtan( $beta0 ) * dsin( round( $epsilon0, 6 ) ) ), ( dcos( $lambda0 ) ) ) ) );
$delta0 = rad2deg( asin( dsin( $beta0 ) * dcos( round( $epsilon0, 6 ) ) + dcos( $beta0 ) * dsin( round( $epsilon0, 6 ) ) * dsin( $lambda0 ) ) );


/*-----------------------------------------------------------------------------*\
   calculate illumination of moon using elongation to sun
\*-----------------------------------------------------------------------------*/

$Delta_lambda0 = 360 - $lambda0;
$Delta_lambda  = 360 - $lambda;

$aws = fnred( $Delta_lambda0 - $Delta_lambda );

$k_aws = ( 0.5 * dsin( $aws - 90 ) + 0.5 ) * 100;

// calculate current textual phase
if     ( (  10 <= $aws ) && ( $aws <  85 ) ) $phaset = 'Waxing Crescent';
elseif ( (  85 <= $aws ) && ( $aws <  95 ) ) $phaset = 'First Quarter';
elseif ( (  95 <= $aws ) && ( $aws < 170 ) ) $phaset = 'Waxing Gibbous';
elseif ( ( 170 <= $aws ) && ( $aws < 190 ) ) $phaset = 'Full Moon';
elseif ( ( 190 <= $aws ) && ( $aws < 265 ) ) $phaset = 'Waning Gibbous';
elseif ( ( 265 <= $aws ) && ( $aws < 275 ) ) $phaset = 'Last Quarter';
elseif ( ( 275 <= $aws ) && ( $aws < 350 ) ) $phaset = 'Waning Crescent';
else                                         $phaset = 'New Moon';


/*-----------------------------------------------------------------------------*\
   calculate dates for major phases of the moon
\*-----------------------------------------------------------------------------*/

// changes the $year, $month, and $giorno (decimal day) variables to reflect the given JDE
function jdgg( $jde ) {

	// set the three important ones to global so we can use them
	// giorno = day
	global $giorno, $month, $year;

	$var1 = $jde + 0.5;
	$zz   = ipart( $var1 );
	$ff   = fpart( $var1 );

	if ( $zz < 2299161 ) {
		$aa = $zz;
	} else {
		$alfa = ipart( ( $zz - 1867216.25 ) / ( 36524.25 ) );
		$aa = $zz + 1 + $alfa - ipart( $alfa / 4 );
	}

	$bb = $aa + 1524;
	$cc = ipart( ( $bb - 122.1 ) / ( 365.25 ) );
	$dd = ipart( 365.25 * $cc);
	$ee = ipart( ( $bb - $dd ) / ( 30.6001 ) );
	$giorno = $bb - $dd - ipart( 30.6001 * $ee ) + $ff;

	if ( $ee < 13.5 ) {
		$month = $ee - 1;
	} else {
		$month = $ee - 13;
	}

	if ( $month > 2.5 ) {
		$year = $cc - 4716;
	} else {
		$year = $cc - 4715;
	}
}

//**********************************

// changes the JDE variable to the day of the phase ($phase) for the current lunation ($K)
function moonph( $phase, $K ) {

	global $JDEm;

	$W = 0;

	if ( 3 == $phase ) {
		$K += 0.50;
	}

	$T  = $K / 1236.85;
	$T2 = $T  * $T;
	$T3 = $T2 * $T;
	$T4 = $T3 * $T;

	$JDEm = 2451550.09765 + 29.530588853 * $K + 0.0001337 * $T2 - 0.00000015 * $T3 + 0.00000000073 * $T4;

	$M  = fnred(   2.5534 +  29.10535669 * $K - 0.0000218 * $T2 - 0.00000011 * $T3 );
	$M1 = fnred( 201.5643 + 385.81693528 * $K + 0.0107438 * $T2 + 0.00001239 * $T3 - 0.000000058 * $T4 );
	$F  = fnred( 160.7108 + 390.67050274 * $K - 0.0016341 * $T2 - 0.00000227 * $T3 + 0.000000011 * $T4 );
	$O  = fnred( 124.7746 - 1.56375580 * $K + 0.0020691 * $T2 + 0.00000215 * $T3 );
	$E  = 1 - 0.002516 * $T - 0.0000074 * $T2;

	$E2 = $E * $E;

	$tmp1 = 0;

	if ( 1 == $phase ) {
		$tmp1 = 0.17241 * $E *  dsin( $M )
			  - 0.40720 *       dsin( $M1 )
			  + 0.01608 *       dsin( 2 * $M1 )
			  + 0.01039 *       dsin( 2 * $F )
			  + 0.00739 * $E  * dsin( $M1 - $M )
			  - 0.00514 * $E  * dsin( $M1 + $M )
			  + 0.00208 * $E2 * dsin( 2 * $M )
			  - 0.00111 *       dsin( $M1 - 2 * $F )
			  - 0.00057 *       dsin( $M1 + 2 * $F )
			  + 0.00056 * $E  * dsin( 2 * $M1 + $M )
			  - 0.00042 *       dsin( 3 * $M1 )
			  + 0.00042 * $E  * dsin( $M + 2 * $F )
			  + 0.00038 * $E  * dsin( $M - 2 * $F )
			  - 0.00024 * $E  * dsin( 2 * $M1 - $M )
			  - 0.00017 *       dsin( $O )
			  - 0.00007 *       dsin( $M1 + 2 * $M )
			  + 0.00004 *       dsin( 2 * $M1 - 2 * $F )
			  + 0.00004 *       dsin( 3 * $M )
			  + 0.00003 *       dsin( $M1 + $M - 2 * $F )
			  + 0.00003 *       dsin( 2 * $M1 + 2 * $F )
			  - 0.00003 *       dsin( $M1 + $M + 2 * $F )
			  + 0.00003 *       dsin( $M1 - $M + 2 * $F )
			  - 0.00002 *       dsin( $M1 - $M - 2 * $F )
			  - 0.00002 *       dsin( 3 * $M1 + $M )
			  + 0.00002 *       dsin( 4 * $M1 );
	}

	if ( 3 == $phase ) {
		$tmp1 = 0.17302 * $E  * dsin( $M )
			  - 0.40614 *       dsin( $M1 )
			  + 0.01614 *       dsin( 2 * $M1 )
			  + 0.01043 *       dsin( 2 * $F )
			  + 0.00734 * $E  * dsin( $M1 - $M )
			  - 0.00515 * $E  * dsin( $M1 + $M )
			  + 0.00209 * $E2 * dsin( 2 * $M )
			  - 0.00111 *       dsin( $M1 - 2 * $F )
			  - 0.00057 *       dsin( $M1 + 2 * $F )
			  + 0.00056 * $E  * dsin( 2 * $M1 + $M )
			  - 0.00042 *       dsin( 3 * $M1 )
			  + 0.00042 * $E  * dsin( $M + 2 * $F )
			  + 0.00038 * $E  * dsin( $M - 2 * $F )
			  - 0.00024 * $E  * dsin( 2 * $M1 - $M )
			  - 0.00017 *       dsin( $O )
			  - 0.00007 *       dsin( $M1 + 2 * $M )
			  + 0.00004 *       dsin( 2 * $M1 - 2 * $F )
			  + 0.00004 *       dsin( 3 * $M )
			  + 0.00003 *       dsin( $M1 + $M - 2 * $F )
			  + 0.00003 *       dsin( 2 * $M1 + 2 * $F )
			  - 0.00003 *       dsin( $M1 + $M + 2 * $F )
			  + 0.00003 *       dsin( $M1 - $M + 2 * $F )
			  - 0.00002 *       dsin( $M1 - $M - 2 * $F )
			  - 0.00002 *       dsin( 3 * $M1 + $M )
			  + 0.00002 *       dsin( 4 * $M1 );
	}

	$A1  = 299.77 +  0.107408 * $K - 0.009173 * $T2;
	$A2  = 251.88 +  0.016321 * $K;
	$A3  = 251.83 + 26.651886 * $K;
	$A4  = 349.42 + 36.412478 * $K;
	$A5  =  84.66 + 18.206239 * $K;
	$A6  = 141.74 + 53.303771 * $K;
	$A7  = 207.14 +  2.453732 * $K;
	$A8  = 154.84 +  7.306860 * $K;
	$A9  =  34.52 + 27.261239 * $K;
	$A10 = 207.19 +  0.121824 * $K;
	$A11 = 291.34 +  1.844379 * $K;
	$A12 = 161.72 + 24.198154 * $K;
	$A13 = 239.56 + 25.513099 * $K;
	$A14 = 331.55 +  3.592518 * $K;

	$tmp2 = 325 * dsin(  $A1 ) + 165 * dsin(  $A2 ) + 164 * dsin(  $A3 )
		  + 126 * dsin(  $A4 ) + 110 * dsin(  $A5 ) +  62 * dsin(  $A6 )
		  +  60 * dsin(  $A7 ) +  56 * dsin(  $A8 ) +  47 * dsin(  $A9 )
		  +  42 * dsin( $A10 ) +  40 * dsin( $A11 ) +  37 * dsin( $A12 )
		  +  35 * dsin( $A13 ) +  23 * dsin( $A14 );

	$tmp2 = $tmp2 * 0.000001;

	$JDEm = $JDEm + $tmp1 + $tmp2 + $W;
}

//********************************
//       MAIN PROGRAM            *
//********************************


function mainmoonphase( $phase ) {

	// giorno = day
	global $year, $month, $giorno, $JDEm, $JDm, $K;

	moonph( $phase, $K );
	jdgg( $JDEm );

	$DeltaTm = ( pow($JDEm - 2382148, 2) / 41048480 - 15 ) / 60 / 1.63;

	$ora    = fpart( $giorno ) * 24; // hour
	$minuto = fpart( $ora ) * 60 - $DeltaTm; // minute

	$day    = ipart( $giorno );
	$hour   = ipart( $ora );
	$minute = round( $minuto, 0 );

	$JDm = cal2jd($year, $month, $day, $hour, $minute);
	$phase = array(
		'JD'     => $JDm,
		'Year'   => ipart( $year ),
		'Month'  => pad( $month ),
		'Day'    => pad( $day ),
		'Hour'   => pad( $hour ),
		'Minute' => pad( $minute ),
		'Unix'   => mktime($hour, $minute, 0, $month, $day, $year),
	);

	return $phase;
}

$year  = $yr;
$month = $mo;
$d     = $dy;

$year   += ($month - 0.5) / 12;
$K       = ipart( ($year - 2000) * 12.3685 ) + 5; // add 5 to make sure were in the current lunation, we'll remove some below

mainmoonphase( 1 );
while ( $JD < $JDm ) {
	$K--;
	mainmoonphase( 1 );
}

$PhaseWrap = array(
	'New Moon'  => mainmoonphase( 1 ),
	'Full Moon' => mainmoonphase( 3 ),
);

$K++;
$PhaseWrap += array(
	'New Moon2' => mainmoonphase( 1 )
);

// calculate age of current moon
$age = $JD - $PhaseWrap['New Moon']['JD'];

// calculate length of current lunation
$length = $PhaseWrap['New Moon2']['JD'] - $PhaseWrap['New Moon']['JD'];

// set next major phase for display
if ( ( 0 < $aws ) && ( $aws <= 180 ) ) {
	$ph_next   = 'Full Moon';
	$ph_year   = $PhaseWrap['Full Moon']['Year'];
	$ph_month  = $PhaseWrap['Full Moon']['Month'];
	$ph_day    = $PhaseWrap['Full Moon']['Day'];
	$ph_hour   = $PhaseWrap['Full Moon']['Hour'];
	$ph_minute = $PhaseWrap['Full Moon']['Minute'];
	$ph_unix   = $PhaseWrap['Full Moon']['Unix'];
	$ph_daysto = $PhaseWrap['Full Moon']['JD'] - $JD;
}
else {
	$ph_next = 'New Moon';
	$ph_year   = $PhaseWrap['New Moon2']['Year'];
	$ph_month  = $PhaseWrap['New Moon2']['Month'];
	$ph_day    = $PhaseWrap['New Moon2']['Day'];
	$ph_hour   = $PhaseWrap['New Moon2']['Hour'];
	$ph_minute = $PhaseWrap['New Moon2']['Minute'];
	$ph_unix   = $PhaseWrap['New Moon2']['Unix'];
	$ph_daysto = $PhaseWrap['New Moon2']['JD'] - $JD;
}


/*-----------------------------------------------------------------------------*\
   extra basic functions
\*-----------------------------------------------------------------------------*/

function fnred( $x ) {
	return $x - 360 * floor( $x / 360 );
}

function dsin( $x ) {
	return sin( deg2rad( fnred( $x ) ) );
}

function dcos( $x ) {
	return cos( deg2rad( fnred( $x ) ) );
}

function dtan( $x ) {
	return tan( deg2rad( fnred( $x ) ) );
}

function pad( $x ) {
	return str_pad((string) $x, 2, '0', STR_PAD_LEFT);
}

function sign( $x ) {
	if ( 0 == $x ) return 0;
	return ( $x / abs( $x ) );
}

function ipart( $x ) {
	$s = sign( $x );
	$t = floor( abs( $x ) );
	return $t * $s;
}

function fpart( $x ) {
	$s = sign( $x );
	$f = floor( abs( $x ) );
	$t = abs( $x ) - $f;
	return $t * $s;
}

function cal2jd( $y, $m, $d, $h, $mn ) {
	if ( $m > 2 ) {
		$y += 4716;
		$m += 1;
	}
	else {
		$y += 4715;
		$m += 13;
	}

	$d += ( $h + ( $mn / 60 ) ) / 24;

	$JD  = ipart( 365.25 * $y ) + ipart( 30.6001 * $m ) + $d - 1537.5;
	return $JD;
}

