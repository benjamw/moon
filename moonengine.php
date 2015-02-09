<?php
/*-----------------------------------------------------------------------------*\
   benjam's astornomical feed script engine                 started: 2004-12-01
   http://iohelix.net                                      finished: not yet
   benjam@iohelix.net                                  last updated: 2006-03-29

   all algorithms adapted from "Astronomical Algorithms" by Meeus, Jean
   published by Willmann-Bell, Inc. (1998) ISBN- 0-943396-61-1
   unless otherwise noted.
   there is a more recent version with the algorithms already on disk...

   wish i had had that one...   =/
\*-----------------------------------------------------------------------------*/

$updated = "2006-03-29";
$version = "1.03";

/*-----------------------------------------------------------------------------*\

   debugging notes:
   when this comment format is seen:
   /* debugging ----
   ... some
   ... code
   ... stuff
   // comment stuff */                                     /* <--- disregard this

   then all you need to do is add a / to the /* debugging ---- line like this:
   //* debugging ---
   and it will enable the debugging code
   change it back to disable the debugging code.

\*-----------------------------------------------------------------------------*/

/* list of all global vars ----------------------------------------------------*\

   $yrL, $moL, $dyL, $hrL, $mnL, $scL, $z
   $lambda, $beta, $Delta, $pi, $theta
   $Delta_psi, $Delta_epsilon, $epsilon0, $epsilon, $Omega
   $delta, $alpha
   $L, $B, $R, $R_km, $circle_dot, $beta0
   $lambda0, $alpha0, $delta0, $theta0, $R_km
   $giorno, $month, $year
   $year, $month, $montht, $giorno, $JDEm, $K
\*-----------------------------------------------------------------------------*/

// keep debug output out of xml feed
$DEBUG = false;
if ( ! defined('IN_MOONFEED'))
{
	$DEBUG = true;
	call("--- DEBUGGING ---");
}

/*-----------------------------------------------------------------------------*\
   get current Julian Day (JD) and Ephemeris Time (JDE)
\*-----------------------------------------------------------------------------*/

// if we are running this file alone (debugging only, there is normally no output),
// and no z attribute is given, default to UTC (z=0)
if ( isset($z) ) {
  // do nothing
}
elseif ( isset($_GET['z']) ) {
  $z = $_GET['z'];
}
else {
  $z = 0;
}

// get the time zone of the server and check for Daylight Saving Time
$Z0 = date("Z")/3600;
$I  = date("I");
//$Z0 = $Z0 - $I; // subtract DST from local time zone

// make a time object for now...  NOW ! (as UT)
$UTC = mktime( date("G") - $Z0, date("i"), date("s"), date("n"), date("j"), date("Y") );

// break it up so we can use it
$yr = date("Y", $UTC);
$mo = date("m", $UTC);
$dy = date("d", $UTC);
$hr = date("H", $UTC);
$mn = date("i", $UTC);
$sc = date("s", $UTC);

// make this a function so we can convert times easily
// input all as UT to convert to local
function local( $yr, $mo, $dy, $hr, $mn, $sc, $z ) {

  // set the vars we need to use to global
  global $yrL, $moL, $dyL, $hrL, $mnL, $scL;

  // make a time object in the users native time zone
  $Local = mktime( $hr + $z, $mn, $sc, $mo, $dy, $yr );

  // break it up, so we can use it
  $yrL = date("Y", $Local);
  $moL = date("m", $Local);
  $dyL = date("d", $Local);
  $hrL = date("H", $Local);
  $mnL = date("i", $Local);
  $scL = date("s", $Local);
}

/* debugging ----
$yr = "2005";
$mo = "01";
$dy = "06";
$hr = "12";
$mn = "00";
$sc = "00";
// Noon, January 6th, 2005 */

/* find DeltaT before we mess with the vars
$year = $yr + ( 30.6001 * $mo + $dy ) / 365.25; // get the fractional part of the year
$u = ($year - 2000)/100; // and use centuries since 2000
$DeltaTsec = 65.0 + $u * ( 76.15 + 41.6 * $u ); // to figure DeltaT
// commented out for DeltaT algorithm interchangeability */

/* my own simplistic way of finding DeltaT from given tables (should be roughly accurate for a couple of years)
// until a better method is located
$DeltaTsec = ( ( $yr - 2005 ) + 66 );
// commented out for DeltaT algorithm interchangeability */

//* or we'll just use the value taken from the internet, cuz a good algorithm has not been found
// http://maia.usno.navy.mil/
// ftp://maia.usno.navy.mil/ser7/deltat.data
// DeltaT = TT - UT1 = 32.184 - (UT1 - TAI)
$DeltaTsec = 64.9082; // delta T last updated 2006-03-30
$DeltaTsec = 65.7736; // delta T last updated 2009-09-26
$DeltaTsec = 66.4829; // delta T last updated 2011-09-01
$DeltaTsec = 67.6925; // delta T last updated 2012-06-01 (data from 2012-04-01, with added leap second)
$DeltaTsec = 67.3890; // delta T last updated 2014-09-06
$DeltaTmin = $DeltaTsec / 60;
$DeltaT = $DeltaTsec * 1.157407e-5;
// commented out for DeltaT algorithm interchangeability */

if( $mo > 2 ) {
  $y = $yr + 4716;
  $m = $mo + 1;
} else {
  $y = $yr + 4715;
  $m = $mo + 13;
}

$d = $dy + ( $hr + ( $mn + ( $sc / 60 ) ) / 60 ) / 24; // add seconds, minutes, and hours into day as decimal part

$JD  = ipart( 365.25 * $y ) + ipart( 30.6001 * $m ) + $d - 1537.5; // correct from 1901 - 2099 (B = -13)
$MJD = $JD - 2400000.5; // Modified Julian Day
$JDE = $JD + $DeltaT;
//$JDE = 2448724.5; // debugging (Apr 12, 1992 0h TD)
//$JDE = 2448908.5; // debugging (Oct 13.0, 1992 TD)


function moonpos( $JDE ) { global $DEBUG;
/*-----------------------------------------------------------------------------*\
   calculate position of the moon

   lambda = geocentric longitude of center (ecliptic)
   beta   = geocentric latitude of center (ecliptic)
   Delta  = distance from earth center to moon center (km)
   pi     = equitorial horizontal parallax
   theta  = angular size of moon

\*-----------------------------------------------------------------------------*/

  // set the vars we need to use to global
  global $lambda, $beta, $Delta, $pi, $theta;

  $T  = ( $JDE - 2451545 ) / 36525; // J centuries from J2000.0
  $T2 = $T  * $T; //
  $T3 = $T2 * $T; // for ease of use
  $T4 = $T3 * $T; //

  $Lp = round( fnred( 218.3164477 + 481267.88123421 * $T - 0.0015786 * $T2 + $T3 /   538841 - $T4 /  65194000 ), 6 ); // Moon's mean longitude
  $D  = round( fnred( 297.8501921 + 445267.1114034  * $T - 0.0018819 * $T2 + $T3 /   545868 - $T4 / 113065000 ), 6 ); // Mean elongation of Moon
  $M  = round( fnred( 357.5291092 +  35999.0502909  * $T - 0.0001536 * $T2 + $T3 / 24490000 ), 6 );                   // Sun's mean anomaly
  $Mp = round( fnred( 134.9633964 + 477198.8675055  * $T + 0.0087414 * $T2 + $T3 /    69699 - $T4 /  14712000 ), 6 ); // Moon's mean anomaly
  $F  = round( fnred(  93.2720950 + 483202.0175233  * $T - 0.0036539 * $T2 - $T3 /  3526000 + $T4 / 863310000 ), 6 ); // Moon's argument of latitude

  $E  = round( 1 - 0.002516 * $T - 0.0000074 * $T2, 6 ); // Eccentricity correction factor
  $E2 = $E * $E; // for ease of use

  $A1 = round( fnred( 119.75 +    131.849 * $T ), 2 ); // Venus contribution
  $A2 = round( fnred(  53.09 + 479264.290 * $T ), 2 ); // Jupiter contribution
  $A3 = round( fnred( 313.45 + 481266.484 * $T ), 2 ); // Flattening of Earth contribution

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

  // calculation for distance
  $Sigma_r =0 -  20905355 * dcos(  0  *$D  +0  *$M  +1  *$Mp  +0  *$F )
		   -      3699111 * dcos(  2  *$D  +0  *$M  -1  *$Mp  +0  *$F )
		   -      2955968 * dcos(  2  *$D  +0  *$M  +0  *$Mp  +0  *$F )
		   -       569925 * dcos(  0  *$D  +0  *$M  +2  *$Mp  +0  *$F )
		   + $E  *  48888 * dcos(  0  *$D  +1  *$M  +0  *$Mp  +0  *$F )
		   -         3149 * dcos(  0  *$D  +0  *$M  +0  *$Mp  +2  *$F )
		   +       246158 * dcos(  2  *$D  +0  *$M  -2  *$Mp  +0  *$F )
		   - $E  * 152138 * dcos(  2  *$D  -1  *$M  -1  *$Mp  +0  *$F )
		   -       170733 * dcos(  2  *$D  +0  *$M  +1  *$Mp  +0  *$F )
		   - $E  * 204586 * dcos(  2  *$D  -1  *$M  +0  *$Mp  +0  *$F )
		   - $E  * 129620 * dcos(  0  *$D  +1  *$M  -1  *$Mp  +0  *$F )
		   +       108743 * dcos(  1  *$D  +0  *$M  +0  *$Mp  +0  *$F )
		   + $E  * 104755 * dcos(  0  *$D  +1  *$M  +1  *$Mp  +0  *$F )
		   +        10321 * dcos(  2  *$D  +0  *$M  +0  *$Mp  -2  *$F )
		   +        79661 * dcos(  0  *$D  +0  *$M  +1  *$Mp  -2  *$F )
		   -        34782 * dcos(  4  *$D  +0  *$M  -1  *$Mp  +0  *$F )
		   -        23210 * dcos(  0  *$D  +0  *$M  +3  *$Mp  +0  *$F )
		   -        21636 * dcos(  4  *$D  +0  *$M  -2  *$Mp  +0  *$F )
		   + $E  *  24208 * dcos(  2  *$D  +1  *$M  -1  *$Mp  +0  *$F )
		   + $E  *  30824 * dcos(  2  *$D  +1  *$M  +0  *$Mp  +0  *$F )
		   -         8379 * dcos(  1  *$D  +0  *$M  -1  *$Mp  +0  *$F )
		   - $E  *  16675 * dcos(  1  *$D  +1  *$M  +0  *$Mp  +0  *$F )
		   - $E  *  12831 * dcos(  2  *$D  -1  *$M  +1  *$Mp  +0  *$F )
		   -        10445 * dcos(  2  *$D  +0  *$M  +2  *$Mp  +0  *$F )
		   -        11650 * dcos(  4  *$D  +0  *$M  +0  *$Mp  +0  *$F )
		   +        14403 * dcos(  2  *$D  +0  *$M  -3  *$Mp  +0  *$F )
		   - $E  *   7003 * dcos(  0  *$D  +1  *$M  -2  *$Mp  +0  *$F )
		   + $E  *  10056 * dcos(  2  *$D  -1  *$M  -2  *$Mp  +0  *$F )
		   +         6322 * dcos(  1  *$D  +0  *$M  +1  *$Mp  +0  *$F )
		   - $E2 *   9884 * dcos(  2  *$D  -2  *$M  +0  *$Mp  +0  *$F )
		   + $E  *   5751 * dcos(  0  *$D  +1  *$M  +2  *$Mp  +0  *$F )
		   - $E2 *   4950 * dcos(  2  *$D  -2  *$M  -1  *$Mp  +0  *$F )
		   +         4130 * dcos(  2  *$D  +0  *$M  +1  *$Mp  -2  *$F )
		   - $E  *   3958 * dcos(  4  *$D  -1  *$M  -1  *$Mp  +0  *$F )
		   +         3258 * dcos(  3  *$D  +0  *$M  -1  *$Mp  +0  *$F )
		   + $E  *   2616 * dcos(  2  *$D  +1  *$M  +1  *$Mp  +0  *$F )
		   - $E  *   1897 * dcos(  4  *$D  -1  *$M  -2  *$Mp  +0  *$F )
		   - $E2 *   2117 * dcos(  0  *$D  +2  *$M  -1  *$Mp  +0  *$F )
		   + $E2 *   2354 * dcos(  2  *$D  +2  *$M  -1  *$Mp  +0  *$F )
		   -         1423 * dcos(  4  *$D  +0  *$M  +1  *$Mp  +0  *$F )
		   -         1117 * dcos(  0  *$D  +0  *$M  +4  *$Mp  +0  *$F )
		   - $E  *   1571 * dcos(  4  *$D  -1  *$M  +0  *$Mp  +0  *$F )
		   -         1739 * dcos(  1  *$D  +0  *$M  -2  *$Mp  +0  *$F )
		   -         4421 * dcos(  0  *$D  +0  *$M  +2  *$Mp  -2  *$F )
		   + $E2 *   1165 * dcos(  0  *$D  +2  *$M  +1  *$Mp  +0  *$F )
		   +         8752 * dcos(  2  *$D  +0  *$M  -1  *$Mp  -2  *$F );
  $Sigma_r = round( $Sigma_r );

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
  $Delta  = 385000.56 + ( $Sigma_r / 1000 );
  $pi     = rad2deg( asin( 6378.14 / $Delta ) );

  // calculate angular size of moon
  $a = 384401; // km of semi-major axis of moon
  $theta2 = 0.5181; // degrees when moon is at semi-major axis

  $theta = $Delta * $theta2 / $a;

  //* debugging ----
  if ($DEBUG) {
  echo "<pre>moonpos()\n-----------------------\n" // set JDE above to 2448724.5
	  ."JDE = ".$JDE."\n"
	  ."  T = ".$T."\n"
	  ." L' = ".$Lp."\n"
	  ."  D = ".$D."\n"
	  ."  M = ".$M."\n"
	  ." M' = ".$Mp."\n"
	  ."  F = ".$F."\n"
	  ." A1 = ".$A1."\n"
	  ." A2 = ".$A2."\n"
	  ." A3 = ".$A3."\n"
	  ."  E = ".$E."\n"
	  ." &Sigma;l = ".$Sigma_l."\n"
	  ." &Sigma;b = ".$Sigma_b."\n"
	  ." &Sigma;r = ".$Sigma_r."\n"
	  ."\n"
	  ."  &lambda; = ".$lambda."\n"
	  ."  &beta; = ".$beta."\n"
	  ."  &Delta; = ".$Delta."\n"
	  ."  &pi; = ".$pi."\n"
	  ."  &theta; = ".$theta."\n\n\n</pre>";
  }
  //*/

} // end of moonpos()


function nutat( $JDE ) { global $DEBUG;
/*-----------------------------------------------------------------------------*\
   calculate nutation

   Delta_psi     = nutation in longitude
   Delta_epsilon = nutation in obliquity
   epsilon0      = mean obliquity
   epsilon       = true obliquity
   Omega         = Longitude of Moon's ascending Node

\*-----------------------------------------------------------------------------*/

  // set the vars we need to use to global
  global $Delta_psi, $Delta_epsilon, $epsilon0, $epsilon, $Omega;

  $T  = ( $JDE - 2451545 ) / 36525; // J centuries from J2000.0
  $T2 = $T  * $T; //
  $T3 = $T2 * $T; // for ease of use
  $T4 = $T3 * $T; //

  $D     = round( fnred( 297.85036 + 445267.111480 * $T - 0.0019142 * $T2 + $T3 / 189474 ), 4 ); // Mean elongation of Moon from the Sun
  $M     = round( fnred( 357.52772 +  35999.050340 * $T - 0.0001603 * $T2 + $T3 / 300000 ), 4 ); // Sun's mean anomaly
  $Mp    = round( fnred( 134.96298 + 477198.867398 * $T + 0.0086972 * $T2 + $T3 /  56250 ), 4 ); // Moon's mean anomaly
  $F     = round( fnred(  93.27191 + 483202.017538 * $T - 0.0036825 * $T2 - $T3 / 327270 ), 4 ); // Moon's argument of latitude
  $Omega = round( fnred( 125.04452 -   1934.136261 * $T + 0.0020708 * $T2 + $T3 / 450000 ), 4 ); // Longitude of Moon's ascending Node

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
			)* 2.77777777778e-8; // this accounts for the coeffecients being in 0.0001" units

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
				)* 2.77777777778e-8; // this accounts for the coeffecients being in 0.0001" units

  $epsilon0 = 23.4392911111 - .013004166667 * $T - 1.63888888889e-7 * $T2 + 5.03611111111e-7 * $T3;
  $epsilon  = $Delta_epsilon + $epsilon0;

  //* debugging ----
  if ($DEBUG) {
  echo "<pre>nutat()\n-----------------------\n" // set JDE above to 2446895.5
	  ."JDE = ".$JDE."\n"
	  ."  T = ".$T."\n"
	  ."  D = ".$D."\n"
	  ."  M = ".$M."\n"
	  ." M' = ".$Mp."\n"
	  ."  F = ".$F."\n"
	  ."  &Omega; = ".$Omega."\n\n"
	  ." &Delta;&psi; = ".$Delta_psi." = ".dms($Delta_psi)."\n"
	  ." &Delta;&epsilon; = ".$Delta_epsilon." = ".dms($Delta_epsilon)."\n"
	  ." &epsilon;0 = ".$epsilon0." = ".dms($epsilon0)."\n"
	  ."  &epsilon; = ".$epsilon." = ".dms($epsilon)."\n\n\n</pre>";
  }
  //*/

} // end of nutat()


function moon2equit( $lambda, $beta, $Delta_psi, $epsilon ) { global $DEBUG;
/*-----------------------------------------------------------------------------*\
   transform moon location from ecliptical to equitorial

   alpha = right ascension
   delta = declination

\*-----------------------------------------------------------------------------*/

  // set the vars we need to use to global
  global $delta, $alpha;

  $lambda += round( $Delta_psi, 6 );

  $alpha = fnred( rad2deg( atan2( ( dsin( $lambda ) * dcos( round( $epsilon, 6 ) ) - dtan( $beta ) * dsin( round( $epsilon, 6 ) ) ), ( dcos( $lambda ) ) ) ) );
  $delta = rad2deg( asin( dsin( $beta ) * dcos( round( $epsilon, 6 ) ) + dcos( $beta ) * dsin( round( $epsilon, 6 ) ) * dsin( $lambda ) ) );

  //* debugging ----
  if ($DEBUG) {
  echo "<pre>moon2equit()\n-----------------------\n"
	  ."  &lambda; = ".$lambda."\n"
	  ."  &alpha; = ".$alpha." = ".hms($alpha)."\n" // divide by 15 for hours, not degrees
	  ."  &delta; = ".$delta." = ".dms($delta)."\n</pre>";
  }
  //*/

} // end of moon2equit()


function sunpos( $JDE ) { global $DEBUG;
/*-----------------------------------------------------------------------------*\
   calculate heliocentric position of the earth (VSOP87)

   L    = heliocentric longitude of center (ecliptic)
   B    = heliocentric latitude of center (ecliptic)
   R    = distance from earth center to sun center (AU)
   R_km = distance from earth center to sun center (km)

   circle_dot = true geometric longitude (ecliptic)
   beta0      = geocentric latitude of center (ecliptic)

\*-----------------------------------------------------------------------------*/

  // set the vars we need to use to global
  global $L, $B, $R, $R_km, $circle_dot, $beta0;

  require("lbr.php"); // taken out because it uses the full VSOP87 theory which is ~ 2500 lines of code   8-O

  $circle_dot = fnred( rad2deg( $L ) + 180 );
  $beta0      = fnred( -rad2deg( $B ) );

  // convert R to km
  $R_km = 149597900 * $R;

} // end of sunpos()


function sun2equit( $circle_dot, $beta0, $Delta_psi, $epsilon, $JDE, $R, $Omega ) { global $DEBUG;
/*-----------------------------------------------------------------------------*\
   transform location from heliocentric earth to geocentric sun

   lambda0 = true apparent geocentric longitude of center (ecliptic)

   alpha0  = right ascension
   delta0  = declination

   theta0  = angular size

\*-----------------------------------------------------------------------------*/

  // set the vars we need to use to global
  global $lambda0, $alpha0, $delta0, $theta0, $R_km, $L, $B;

  $T  = ( $JDE - 2451545 ) / 36525; // J centuries from J2000.0
  $T2 = $T  * $T; //
  $T3 = $T2 * $T; // for ease of use
  $T4 = $T3 * $T; //

  // convert to FK5 system
  $lambdap = $circle_dot - 1.397 * $T - 0.00031 * $T2;

  $Delta_circle_dot = -2.5091666666667e-5; // 0.09033"
  $Delta_beta0      = 1.0877777777778e-5 * ( dcos( $lambdap ) - dsin( $lambdap ) ); // 0.03916"

  $beta0 += $Delta_beta0;

  // correct for nutation and abberation
  $lambda0 = $Delta_psi + $circle_dot; // correction for nutation

  $e     = 0.016708634 - 0.000042037 * $T - 0.0000001267 * $T2 + 0.00000000014 * $T3; // earth's orbital eccentricity
  $a0    = 1.000001018; // earth's approximate semimajor axis (AU)
  $kappa = 0.0056942222222224; // constant of abberation

  $kappa0   = $kappa * $a0 * ( 1 - $e * $e );
  $kappa0p  = 0.00569161111111;
  $lambda0 += -( $kappa0p / $R ) + $Delta_circle_dot;

  // find angular size of sun (not very exact)
  $r1 = 149598500; // km at epoch 1990
  $theta1 = 0.533128; // degrees at epoch 1990

  // convert R to km
  $R_km = 149597900 * $R;

  $theta0 = $R_km * $theta1 / $r1;

  $epsilon0 = $epsilon + 0.00256 * dcos( $Omega );


  /*-----------------------------------------------------------------------------*\
	 transform sun location from ecliptical to equitorial
  \*-----------------------------------------------------------------------------*/
  $alpha0 = fnred( rad2deg( atan2( ( dsin( $lambda0 ) * dcos( round( $epsilon0, 6 ) ) - dtan( $beta0 ) * dsin( round( $epsilon0, 6 ) ) ), ( dcos( $lambda0 ) ) ) ) );
  $delta0 = rad2deg( asin( dsin( $beta0 ) * dcos( round( $epsilon0, 6 ) ) + dcos( $beta0 ) * dsin( round( $epsilon0, 6 ) ) * dsin( $lambda0 ) ) );

  //* debugging ----
  if ($DEBUG) {
  echo "<pre>sun2equit()\n-----------------------\n"
	  ."  L = ".$L." = ".rad2deg($L)." = ".fnred(rad2deg($L))." = ".dms(fnred(rad2deg($L)))."\n"
	  ."  B = ".$B." = ".rad2deg($B)." = ".dms(rad2deg($B))."\n"
	  ."  R = ".$R."\n\n"
	  ." O. = ".$circle_dot." = ".dms($circle_dot)."\n"
	  ." &beta;0 = ".$beta0." = ".dms($beta0)."\n\n"
	  ." &kappa;0 = ".$kappa0." = ".dms($kappa0)."\n"
	  ." &lambda;0 = ".$lambda0." = ".dms($lambda0)."\n\n"
	  ." &alpha;0 = ".$alpha0." = ".hms($alpha0)."\n"
	  ." &delta;0 = ".$delta0." = ".dms($delta0)."\n\n\n</pre>";
  }
  //*/

} // end of sun2equit()


nutat( $JDE ); // needed for $Delta_psi and $epsilon below
/*-----------------------------------------------------------------------------*\
   calculate transit (w/ culmination angle) times for the sun and moon
   (this is why the above functions are functions and not just procedural).

   Lon    = geographic longitude
   phi    = geographic latitude
   Theta0 = apparent sidereal time (UT) (degrees)

\*-----------------------------------------------------------------------------*/

// $phi = $lat;
// $Lon = -$lon;

//* debugging ----
// needed until input is implemented for lat-lon
$phi =  40.766666667;
$Lon = 111.866666667;
// salt lake city, ut, usa */

$JDc = floor($JD); // round to noon of prev. day (12h)
if ( ( $JD - $JDc ) < 0.5 ) $JDc--; // if its already past noon (= JD.0 < JD < JD.5 ), subtract one day
$JDc = $JDc + 0.5; // then add half day to get 0h of current day

$T  = ( ( $JDc + 0.5 ) - 2451545 ) / 36525; // J centuries from J2000.0 at 0h
$T2 = $T * $T;
$T3 = $T * $T2;

$Theta0 = fnred( 100.46061837 + 36000.770053608 * $T + 0.000387933 * $T2 - $T3 / 38710000 + ( $Delta_psi * dcos( $epsilon ) ) / 15 ); // sidereal time at greenwich in degrees

$pole = false; $noriseset=false;
if ( abs($phi) == 90 ) { // are we at a pole?
  $pole = true; // if so, the calculation will cause problems (divide by 0)
}

// save times and init array for sun transit time
$SA[0]["JD"] = $JDc - 1;
$SA[1]["JD"] = $JDc;
$SA[2]["JD"] = $JDc + 1;

// run positions for the sun
for ( $i = 0; $i <= 2; $i++ ) {
  sunpos( $SA[$i]["JD"] );
  sun2equit( $circle_dot, $beta0, $Delta_psi, $epsilon, $JDE, $R, $Omega );
  $SA[$i]["alpha"] = $alpha0;
  $SA[$i]["delta"] = $delta0;
}

if ( !$pole ) {
  $SA[0]["m"] = fnred1( ( $SA[1]["alpha"] + $Lon - $Theta0 ) / 360 );

  for ($j = 0; $j <= 3; $j++ ) { // repeat the calulations for each new modified value of m
	$SA[0]["theta0"] = fnred( $Theta0 + ( 360.985647 * $SA[0]["m"] ) );

	$SA[0]["n"] = $SA[0]["m"] + ( $DeltaT / 86400 );

	$SA[0]["alpha0"] = interpolate( $SA[0]["alpha"], $SA[1]["alpha"], $SA[2]["alpha"], $SA[0]["n"] );
	$SA[0]["delta0"] = interpolate( $SA[0]["delta"], $SA[1]["delta"], $SA[2]["delta"], $SA[0]["n"] );

	$SA[0]["H"] = fnred_180( $SA[0]["theta0"] - $Lon - $SA[0]["alpha0"] );

	$SA[0]["h"] = rad2deg( asin( ( dsin( $phi ) * dsin( $SA[0]["delta"] ) ) + ( dcos( $phi ) * dcos( $SA[0]["delta"] ) * dcos( $SA[0]["H"] ) ) ) );

	// add corrections to each of the m values
	$SA[0]["m"] = fnred1( $SA[0]["m"] + -$SA[0]["H"] / 360 ); // transit
  }

  // convert to hours and minutes
  $SA[0]["hour"]   = pad( floor( $SA[0]["m"] * 24 ) );
  $SA[0]["minute"] = pad( round( ( $SA[0]["m"] * 24 - $SA[0]["hour"] ) * 60, 0 ) );

  $SA[0]["hourL"]  = $SA[0]["hour"] + $z;
  if ( $SA[0]["hourL"] < 0 ) $SA[0]["hourL"] += 24;
  $SA[0]["hourL"]  = pad( $SA[0]["hourL"] );

  //* debugging ----
  if ($DEBUG) {
  echo "<pre>\n".$JD."\n".$JDE."\n";
  print_r($SA);
  echo "\n</pre>";
	}
  //*/
}

// save all values to the Sun array
$Sun["transit"]["hour"]     = $SA[0]["hour"];
$Sun["transit"]["minute"]   = $SA[0]["minute"];
$Sun["transit"]["hourL"]    = $SA[0]["hourL"];
$Sun["transit"]["altitude"] = $SA[0]["h"];
$Sun["transit"]["JD"]       = cal2jd( $yr, $mo, $dy, $SA[0]["hour"], $SA[0]["minute"] );


// save times and init array for moon transit time
$MA[0]["JD"] = $JDc - 1;
$MA[1]["JD"] = $JDc;
$MA[2]["JD"] = $JDc + 1;

// run positions for the moon
for ( $i = 0; $i <= 2; $i++ ) {
  moonpos( $MA[$i]["JD"] );
  moon2equit( $lambda, $beta, $Delta_psi, $epsilon );
  $MA[$i]["alpha"] = $alpha;
  $MA[$i]["delta"] = $delta;
}

if ( !$pole ) {
  $MA[0]["m"] = fnred1( ( $MA[1]["alpha"] + $Lon - $Theta0 ) / 360 );

  for ($j = 0; $j <= 3; $j++ ) { // repeat the calulations for each new modified value of m
	$MA[0]["theta0"] = fnred( $Theta0 + ( 360.985647 * $MA[0]["m"] ) );

	$MA[0]["n"] = $MA[0]["m"] + ( $DeltaT / 86400 );

	$MA[0]["alpha0"] = interpolate( $MA[0]["alpha"], $MA[1]["alpha"], $MA[2]["alpha"], $MA[0]["n"] );
	$MA[0]["delta0"] = interpolate( $MA[0]["delta"], $MA[1]["delta"], $MA[2]["delta"], $MA[0]["n"] );

	$MA[0]["H"] = fnred_180( $MA[0]["theta0"] - $Lon - $MA[0]["alpha0"] );

	$MA[0]["h"] = rad2deg( asin( ( dsin( $phi ) * dsin( $MA[0]["delta"] ) ) + ( dcos( $phi ) * dcos( $MA[0]["delta"] ) * dcos( $MA[0]["H"] ) ) ) );

	// add corrections to each of the m values
	$MA[0]["m"] = fnred1( $MA[0]["m"] + -$MA[0]["H"] / 360 );                                                                               // transit
  }

  // convert to hours and minutes
  $MA[0]["hour"]   = pad( floor( $MA[0]["m"] * 24 ) );
  $MA[0]["minute"] = pad( round( ( $MA[0]["m"] * 24 - $MA[0]["hour"] ) * 60, 0 ) );

  $MA[0]["hourL"]  = $MA[0]["hour"] + $z;
  if ( $MA[0]["hourL"] < 0 ) $MA[0]["hourL"] += 24;
  $MA[0]["hourL"]  = pad( $MA[0]["hourL"] );

  //* debugging ----
  if ($DEBUG) {
  echo "<pre>\n".$JD."\n".$JDE."\n";
  print_r($MA);
  echo "\n</pre>";
	}
  //*/
}


/*-----------------------------------------------------------------------------*\

   calculate rise & set (w/ azimuth angle) times for the sun and moon

\*-----------------------------------------------------------------------------*/

$JD1900 = $JD - 2415020; // Julian day relative to Noon, New Years Eve, 1899
$JD2000 = $JD - 2451545; // Julian day relative to Noon Jan 1, 2000

///////////////////////////////////////////////////////////////////////////////

$k1  = deg2rad( 15 * 1.0027379 );
$ra  = array();
$dec = array();

$Sunrise = 0;
$Sunset  = 0;

////////////////////////////////////////////

//* debugging ----
// needed until input method implemented for lat-lon
$lon = -111.866666667;
$lat =   40.766666667;
// salt lake city, utah, usa */


$lon = $lon / 360;
$tz  = $z / 24;
$tz  = 0;
$ct  = $JD2000 / 36525 + 1; // centuries since 1900.0

$t0  = lst( $lon, $JD2000, $tz );

// get sun's position
$JD2000 += $tz;
sun( $JD2000, $ct );
$ra0  = $rar;
$dec0 = $decr;

$JD2000 = $JD2000 + 1;
sun( $JD2000, $ct );
$ra1  = $rar;
$dec1 = $decr;

if ( $ra1 < $ra0 ) $ra1 += 2 * pi();

$ra[0]  = $ra0;
$dec[0] = $dec0;

for ( $k = 0; $k <= 23; $k++ ) {
  $ph = ( $k + 1 ) / 24;

  $ra[2]  = $ra0  + $ph * ( $ra1  - $ra0 );
  $dec[2] = $dec0 + $ph * ( $dec1 - $dec0 );

  test( $k, $t0, $lat, $ra, $dec );

  $ra[0]  = $ra[2];
  $dec[0] = $dec[2];
}

//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////


//
// LST at 0h zone time
//
function lst( $lon, $JD2000, $z ) { global $DEBUG;

  $s = 24110.5 + 8640184.812999999 * $JD2000 / 36525;
  $s = $s + 86636.6 * $z + 86400 * $lon;

  $s = $s / 86400;
  $s = $s - floor( $s );

  $t0 = deg2rad( $s * 360 );

  return $t0;
}

//
// test an hour for an event
//
function test( $k, $t0, $lat, $ra, $dec ) { global $DEBUG;

  global $v, $ha, $k1, $Sunrise, $Sunset, $Sun;

  $ha[0] = $t0 - $ra[0] + $k * $k1;
  $ha[2] = $t0 - $ra[2] + $k * $k1 + $k1;

  $ha[1]  = ( $ha[2]  + $ha[0]  ) / 2; // hour angle at half hour
  $dec[1] = ( $dec[2] + $dec[0] ) / 2; // declination at half hour

  $s = dsin( $lat );
  $c = dcos( $lat );
  $z = dcos( 90.833 );

  if ( 0 >= $k ) {
  $v[0] = $s * sin( $dec[0] ) + $c * cos( $dec[0] ) * cos( $ha[0] ) - $z;
  }

  $v[2] = $s * sin( $dec[2] ) + $c * cos( $dec[2] ) * cos( $ha[2] ) - $z;

  if ( sign( $v[0] ) == sign( $v[2] ) ) {
	$v[0] = $v[2];
	return;
  }

  $v[1] = $s * sin( $dec[1] ) + $c * cos( $dec[1] ) * cos( $ha[1] ) - $z;

  $a =  2 * $v[0] - 4 * $v[1] + 2 * $v[2];
  $b = -3 * $v[0] + 4 * $v[1] - $v[2];

  $d = $b * $b - 4 * $a * $v[0];

  if ( 0 > $d ) {
	$v[0] = $v[2];
	return;
  }

  $d = sqrt( $d );

  if ( ( 0 > $v[0] ) && ( 0 < $v[2] ) ) {
	$Sunrise = 1; $Sunset = 0;
  }

  if ( ( 0 < $v[0] ) && ( 0 > $v[2] ) ) {
	$Sunset = 1; $Sunrise = 0;
  }

  $e = ( -$b + $d ) / ( 2 * $a );

  if ( ( 1 < $e ) || ( 0 > $e ) ) $e = ( -$b - $d ) / ( 2 * $a );

  // time of an event

  $time = $k + $e + 1 / 120;

  $hr = floor( $time );
  $min = floor( ( $time - $hr ) * 60 );

  if ( 10 > $min ) $min = "0" . $min;

  if ( $Sunset ) {
	$Sun["set"]["hour"]   = pad( $hr );
	$Sun["set"]["minute"] = pad( $min );
  }
  else {
	$Sun["rise"]["hour"]   = pad( $hr );
	$Sun["rise"]["minute"] = pad( $min );
  }

  // azimuth of the sun at the event

  $hz = $ha[0] + $e * ( $ha[2] - $ha[0] );
  $nz = -cos( $dec[1] ) * sin( $hz );
  $dz = $c * sin( $dec[1] ) - $s * cos( $dec[1] ) * cos( $hz );

  $az = rad2deg( atan2( $nz, $dz ) );

  if ( $dz < 0 )   $az += 180;
  if ( $az < 0 )   $az += 360;
  if ( $az > 360 ) $az -= 360;

  if ( $Sunset ) {
	$Sun["set"]["azimuth"] = $az;
  }
  else {
	$Sun["rise"]["azimuth"] = $az;
  }

  $v[0] = $v[2];

}

//
// sun's position using fundamental arguments
// (Van Flandern & Pulkkinen, 1979)
//
function sun( $JD2000, $ct ) { global $DEBUG;

  global $rar, $decr;

  $lo  = 0.779072 + 0.00273790931 * $JD2000;
  $lo -= floor( $lo );
  $lo  = $lo * 2 * pi();

  $g  = 0.993126 + 0.0027377785 * $JD2000;
  $g -= floor( $g );
  $g  = $g * 2 * pi();

  $v  = 0.39785 * sin( $lo );
  $v -= 0.01 * sin( $lo - $g );
  $v += 0.00333 * sin( $lo + $g );
  $v -= 0.00021 * $ct * sin( $lo );

  $u  = 1 - 0.03349 * cos( $g );
  $u -= 0.00014 * cos( 2 * $lo );
  $u += 0.00008 * cos( $lo );

  $w  = -0.0001 - 0.04129 * sin( 2 * $lo );
  $w += 0.03211 * sin( $g );
  $w += 0.00104 * sin( 2 * $lo - $g );
  $w -= 0.00035 * sin( 2 * $lo + $g );
  $w -= 0.00008 * $ct * sin( $g );

  // compute sun's right ascension and declination
  $s    = $w / sqrt( $u - $v * $v );
  $rar  = $lo + atan( $s / sqrt( 1 - $s * $s ) );

  $s    = $v / sqrt( $u );
  $decr = atan( $s / sqrt( 1 - $s * $s ) );
}

// set the local hours for the rise and set
$Sun["set"]["hourL"]  = $Sun["set"]["hour"] + $z;
if ( $Sun["set"]["hourL"] < 0 ) $Sun["set"]["hourL"] += 24;
$Sun["set"]["hourL"] = pad( $Sun["set"]["hourL"] );

$Sun["rise"]["hourL"] = $Sun["rise"]["hour"] + $z;
if ( $Sun["rise"]["hourL"] < 0 ) $Sun["rise"]["hourL"] += 24;
$Sun["rise"]["hourL"] = pad( $Sun["rise"]["hourL"] );

$Sun["set"]["JD"]  = cal2jd( $yr, $mo, $dy, $Sun["set"]["hour"], $Sun["set"]["minute"] );
$Sun["rise"]["JD"] = cal2jd( $yr, $mo, $dy, $Sun["rise"]["hour"], $Sun["rise"]["minute"] );




















// run all the functions needed from this point on, one more time, using the current JDE
   moonpos( $JDE );
	 nutat( $JDE );
moon2equit( $lambda, $beta, $Delta_psi, $epsilon );
	sunpos( $JDE );
 sun2equit( $circle_dot, $beta0, $Delta_psi, $epsilon, $JDE, $R, $Omega );


/*-----------------------------------------------------------------------------*\
   calculate illuminated percent of moon using phase angle

   i   = phase angle
   k   = illuminated fraction
   chi = bright limb angle

\*-----------------------------------------------------------------------------*/


$psi = fnred180( rad2deg( acos( dcos( $beta ) * dcos( $lambda - $lambda0 ) ) ) );
$psi2 = fnred180( rad2deg( acos( dsin( $delta0 ) * dsin( $delta ) + dcos( $delta0 ) * dcos( $delta ) * dcos( $alpha0 - $alpha ) ) ) );

$i = fnred180( rad2deg( atan2( ( $R_km * dsin( $psi ) ), ( $Delta - $R_km * dcos( $psi ) ) ) ) );

$k = ( ( 1 + dcos( $i ) ) / 2 ) * 100;

$chi = fnred( rad2deg( atan2( ( dcos( $delta0 ) * dsin( $alpha0 - $alpha ) ), ( dsin( $delta0 ) * dcos( $delta ) - dcos( $delta0 ) * dsin( $delta ) * dcos( $alpha0 - $alpha ) ) ) ) );

//* debugging ----
if ($DEBUG) {
echo "<pre>--------------------\n-------------------\n  &psi; = ".$psi."\n &psi;2 = ".$psi2."\n  i = ".$i."\n\n  k = ".$k."%\n  &chi; = ".$chi."\n &theta;0 = ".$theta0."<pre>";
}
//*/

/*-----------------------------------------------------------------------------*\
   calculate illuminated percent of moon using elongation to sun

   the above calculations are incorrect by a small but annoying margin
   it was therefore necessary to create the following simplistic
   calculations based on basic geometric ideas and some common sense
   to get better accuracy based on the USNO ephemerides used for testing

   both values are available in the feed

   aws   = geocentric elongation angle of moon center to sun center
   k_aws = percent illuminated based on aws, not i

\*-----------------------------------------------------------------------------*/

$Delta_lambda0 = 360 - $lambda0; // find the angle of the sun from ecliptic "north"
$Delta_lambda  = 360 - $lambda;  // find the angle of the moon from ecliptic "north"

$aws = fnred( $Delta_lambda0 - $Delta_lambda ); // subtract to find the geocentric angle between the sun and moon

$k_aws = ( 1/2 * dsin( $aws - 90 ) + 1/2 ) * 100; // use that angle to find the percent illuminated

// calculate current textual phase
/*
   these values are away from the actual phase values by certain amounts that
   are by no means "official", as the "official" phase can last mere moments,
   with new, first and last quarter, and full being exact moments in time.
   the moon phase appears to change faster near the quarter phases, this is why
   there is less room to breathe with these phases when compared with the new
   and full moons, they are more noticable, whereas a sliver of black against
   a full moon is hardly noticable at all.
*/
if     ( (  10 <= $aws ) && ( $aws <  85 ) ) $phaset = "Waxing Crescent"; //   0 ->  90
elseif ( (  85 <= $aws ) && ( $aws <  95 ) ) $phaset = "First Quarter";   //  90         (+/-  5)
elseif ( (  95 <= $aws ) && ( $aws < 170 ) ) $phaset = "Waxing Gibbous";  //  90 -> 180
elseif ( ( 170 <= $aws ) && ( $aws < 190 ) ) $phaset = "Full Moon";       // 180         (+/- 10)
elseif ( ( 190 <= $aws ) && ( $aws < 265 ) ) $phaset = "Waning Gibbous";  // 180 -> 270
elseif ( ( 265 <= $aws ) && ( $aws < 275 ) ) $phaset = "Last Quarter";    // 270         (+/-  5)
elseif ( ( 275 <= $aws ) && ( $aws < 350 ) ) $phaset = "Waning Crescent"; // 270 -> 360
else   /*( 350 <= $aws ) && ( $aws < 10  )*/ $phaset = "New Moon";        // 360 (0)     (+/- 10)

/*-----------------------------------------------------------------------------*\
   calculate dates for various phases of the moon starting and ending with
   a New Moon.

   idea from simple.be with the code and algorithm being
   ported and adapted from MoonPhas.89p by Sergio Filippini
   which is based on Meeus' work.
   this was mostly not my code, but all comments are my own, so any reasoning is
   merely conjecture.

   K = current lunation

\*-----------------------------------------------------------------------------*/

// changes the $year, $month, and $giorno (decimal day) variables to reflect the given JDE
function jdgg( $jde ) { global $DEBUG;

  // set the three important vars to global so we can use them later
  global $giorno, $month, $year;

  $var1 = $jde + 0.5; // add a half-day to the date
  $zz   = ipart( $var1 ); // get the date portion of the date
  $ff   = fpart( $var1 ); // and the time portion

  if ( $zz < 2299161 ) { // if the date is before 1582-10-15
	$aa = $zz; // do nothing
  } else { // if the date is after 1582-10-15
	$alfa = ipart( ( $zz - 1867216.25 ) / ( 36524.25 ) ); // find out how many centuries since 400-02-29
	$aa = $zz + 1 + $alfa - ipart( $alfa / 4 ); // and add leap year corrections
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

  //* debugging ----
  if ($DEBUG) {
  echo "<br />jdgg(jde)<br />\n";
  echo "jde= ".$jde."<br />\n";
  echo "var1= ".$var1."<br />\n";
  echo "zz= ".$zz."<br />\n";
  echo "ff= ".$ff."<br />\n";
  echo "alfa= ".$alfa."<br />\n";
  echo "aa= ".$aa."<br />\n";
  echo "bb= ".$bb."<br />\n";
  echo "cc= ".$cc."<br />\n";
  echo "dd= ".$dd."<br />\n";
  echo "ee= ".$ee."<br />\n";
  echo "giorno= ".$giorno."<br />\n";
  echo "month= ".$month."<br />\n";
  echo "year= ".$year."<br />\n<hr />\n";
  }
  //*/
}

//**********************************

// changes the JDE variable to the day of the phase ($phase) for the current lunation ($K)
function moonph( $phase, $K) { global $DEBUG;

  global $JDEm;

  $W = 0;

  if ( 2 == $phase ) { // first quarter
   $K += 0.25;
  }
  if ( 3 == $phase ) { // full moon
   $K += 0.50;
  }
  if ( 4 == $phase ) { // last quarter
   $K += 0.75;
  }

  $T  = $K / 1236.85; // J centuries from J2000.0 based on lunations
  $T2 = $T * $T;  //
  $T3 = $T * $T2; // for ease of use
  $T4 = $T * $T3; //

  //* debugging ----
  if ($DEBUG) {
  echo "<br />moonph(phase,K)<br />\n";
  echo "phase= ".$phase."<br />\n";
  echo "K= ".$K."<br />\n";
  echo "T= ".$T."<br />\n";
  }
  //*/

  // add number of lunations and correction factor to julian day for 2000-01-06 to find current lunation date
  $JDEm = 2451550.09765 + 29.530588853 * $K + 0.0001337 * $T2 - 0.00000015 * $T3 + 0.00000000073 * $T4;

  $M  = fnred(   2.5534 +  29.10535669 * $K - 0.0000218 * $T2 - 0.00000011 * $T3 );                     // Sun's Mean Anomaly
  $M1 = fnred( 201.5643 + 385.81693528 * $K + 0.0107438 * $T2 + 0.00001239 * $T3 - 0.000000058 * $T4 ); // Moon's Mean Anomaly
  $F  = fnred( 160.7108 + 390.67050274 * $K - 0.0016341 * $T2 - 0.00000227 * $T3 + 0.000000011 * $T4 ); // Moon's argument of latitude
  $O  = fnred( 124.7746 -   1.56375580 * $K + 0.0020691 * $T2 + 0.00000215 * $T3 );                     // Longitude of Moon's ascending Node
  $E  = 1 - 0.002516 * $T - 0.0000074 * $T2;                                                            // Eccentricity correction factor

  //* debugging ----
  if ($DEBUG) {
  echo "M= ".$M."<br />\n";
  echo "M1= ".$M1."<br />\n";
  echo "F= ".$F."<br />\n";
  echo "O= ".$O."<br />\n";
  echo "E= ".$E."<br />\n";
  }
  //*/

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

  if ( 1 == $phase ) { // new moon
	$tmp1 = 0.17241 * $E *      dsin( $M )
		  - 0.40720 *           dsin( $M1 )
		  + 0.01608 *           dsin( 2 * $M1 )
		  + 0.01039 *           dsin( 2 * $F )
		  + 0.00739 * $E *      dsin( $M1 - $M )
		  - 0.00514 * $E *      dsin( $M1 + $M )
		  + 0.00208 * $E * $E * dsin( 2 * $M )
		  - 0.00111 *           dsin( $M1 - 2 * $F )
		  - 0.00057 *           dsin( $M1 + 2 * $F )
		  + 0.00056 * $E *      dsin( 2 * $M1 + $M )
		  - 0.00042 *           dsin( 3 * $M1 )
		  + 0.00042 * $E *      dsin( $M + 2 * $F )
		  + 0.00038 * $E *      dsin( $M - 2 * $F )
		  - 0.00024 * $E *      dsin( 2 * $M1 - $M )
		  - 0.00017 *           dsin( $O )
		  - 0.00007 *           dsin( $M1 + 2 * $M )
		  + 0.00004 *           dsin( 2 * $M1 - 2 * $F )
		  + 0.00004 *           dsin( 3 * $M )
		  + 0.00003 *           dsin( $M1 + $M - 2 * $F )
		  + 0.00003 *           dsin( 2 * $M1 + 2 * $F )
		  - 0.00003 *           dsin( $M1 + $M + 2 * $F )
		  + 0.00003 *           dsin( $M1 - $M + 2 * $F )
		  - 0.00002 *           dsin( $M1 - $M - 2 * $F )
		  - 0.00002 *           dsin( 3 * $M1 + $M )
		  + 0.00002 *           dsin( 4 * $M1 );
  }

  if ( 3 == $phase ) { // full moon
	$tmp1 = 0.17302 * $E *      dsin( $M )
		  - 0.40614 *           dsin( $M1 )
		  + 0.01614 *           dsin( 2 * $M1 )
		  + 0.01043 *           dsin( 2 * $F )
		  + 0.00734 * $E *      dsin( $M1 - $M )
		  - 0.00515 * $E *      dsin( $M1 + $M )
		  + 0.00209 * $E * $E * dsin( 2 * $M )
		  - 0.00111 *           dsin( $M1 - 2 * $F )
		  - 0.00057 *           dsin( $M1 + 2 * $F )
		  + 0.00056 * $E *      dsin( 2 * $M1 + $M )
		  - 0.00042 *           dsin( 3 * $M1 )
		  + 0.00042 * $E *      dsin( $M + 2 * $F )
		  + 0.00038 * $E *      dsin( $M - 2 * $F )
		  - 0.00024 * $E *      dsin( 2 * $M1 - $M )
		  - 0.00017 *           dsin( $O )
		  - 0.00007 *           dsin( $M1 + 2 * $M )
		  + 0.00004 *           dsin( 2 * $M1 - 2 * $F )
		  + 0.00004 *           dsin( 3 * $M )
		  + 0.00003 *           dsin( $M1 + $M - 2 * $F )
		  + 0.00003 *           dsin( 2 * $M1 + 2 * $F )
		  - 0.00003 *           dsin( $M1 + $M + 2 * $F )
		  + 0.00003 *           dsin( $M1 - $M + 2 * $F )
		  - 0.00002 *           dsin( $M1 - $M - 2 * $F )
		  - 0.00002 *           dsin( 3 * $M1 + $M )
		  + 0.00002 *           dsin( 4 * $M1 );
  }

  if ( ( 2 == $phase ) || ( 4 == $phase ) ) { // first and last quarters
	$tmp1 = 0.17172 * $E *      dsin( $M )
		  - 0.62801 *           dsin( $M1 )
		  - 0.01183 * $E *      dsin( $M1 + $M )
		  + 0.00862 *           dsin( 2 * $M1 )
		  + 0.00804 *           dsin( 2 * $F )
		  + 0.00454 * $E *      dsin( $M1 - $M )
		  + 0.00204 * $E * $E * dsin( 2 * $M )
		  - 0.00180 *           dsin( $M1 - 2 * $F )
		  - 0.00070 *           dsin( $M1 + 2 * $F )
		  - 0.00040 *           dsin( 3 * $M1 )
		  - 0.00034 * $E *      dsin( 2 * $M1 - $M )
		  + 0.00032 * $E *      dsin( $M + 2 * $F )
		  + 0.00032 * $E *      dsin( $M - 2 * $F )
		  - 0.00028 * $E * $E * dsin( $M1 + 2 * $M )
		  + 0.00027 * $E *      dsin( 2 * $M1 + $M )
		  - 0.00017 *           dsin( $O )
		  - 0.00005 *           dsin( $M1 - $M - 2 * $F )
		  + 0.00004 *           dsin( 2 * $M1 + 2 * $F )
		  - 0.00004 *           dsin( $M1 + $M + 2 * $F )
		  + 0.00004 *           dsin( $M1 - 2 * $M )
		  + 0.00003 *           dsin( $M1 + $M - 2 * $F )
		  + 0.00003 *           dsin( 3 * $M )
		  + 0.00002 *           dsin( 2 * $M1 - 2 * $F )
		  + 0.00002 *           dsin( $M1 - $M + 2 * $F )
		  - 0.00002 *           dsin( 3 * $M1 + $M );

	$W = 0.00306
	   - 0.00038 * $E * dcos( $M )
	   + 0.00026 *      dcos( $M1 )
	   - 0.00002 *      dcos( $M1 - $M )
	   + 0.00002 *      dcos( $M1 + $M )
	   + 0.00002 *      dcos( 2 * $F );

	if ( 4 == $phase ) { // last quarter
	  $W = -$W;
	}
  }

  $tmp2 = 325 * dsin(  $A1 ) + 165 * dsin(  $A2 ) + 164 * dsin(  $A3 )
		+ 126 * dsin(  $A4 ) + 110 * dsin(  $A5 ) +  62 * dsin(  $A6 )
		+  60 * dsin(  $A7 ) +  56 * dsin(  $A8 ) +  47 * dsin(  $A9 )
		+  42 * dsin( $A10 ) +  40 * dsin( $A11 ) +  37 * dsin( $A12 )
		+  35 * dsin( $A13 ) +  23 * dsin( $A14 );

  $tmp2 = $tmp2 * 0.000001;

  //* debugging ----
  if ($DEBUG) {
  echo "tmp1= ".$tmp1."<br />\n";
  echo "tmp2= ".$tmp2."<br />\n";
  echo "W= ".$W."<br />\n<hr />\n";
  }
  //*/

  $JDEm = $JDEm + $tmp1 + $tmp2 + $W; // update and save JDE
}

//********************************
//       MAIN PROGRAM            *
//********************************

function mainmoonphase( $phase ) { global $DEBUG;

  //* debugging ----
  if ($DEBUG) {
  echo "<br />mainmoonphase(phase)<br />";
  echo "phase= ".$phase."<br />\n";
  }
  //*/

  global $year, $month, $montht, $giorno, $JDEm, $JDm, $K;
  global $yrL, $moL, $dyL, $hrL, $mnL, $z;

  moonph($phase,$K);
  jdgg($JDEm);

  $DeltaTm = ( pow($JDEm-2382148,2) / 41048480 - 15 ) / 60 / 1.63;

  $ora    = fpart($giorno)*24;
  $minuto = fpart($ora)*60-$DeltaTm;

  $day    = ipart($giorno);
  $hour   = ipart($ora);
  $minute = round($minuto,0);

  $JDm = cal2jd($year,$month,$day,$hour,$minute);
  local( $year, $month, $day, $hour, $minute, 0, $z ); // convert UT to local time
  $phase = array(
  "JD"     => $JDm,
  "DeltaT" => $DeltaTm,
  "JDE"    => $JDEm,
  "Year"   => ipart( $yrL ),
  "Month"  => pad( $moL ),
  "Day"    => pad( $dyL ),
  "Hour"   => pad( $hrL ),
  "Minute" => pad( $mnL )
  );

  //* debugging ----
  if ($DEBUG) {
  echo "<br />DeltaTm= ".$DeltaTm."<br />\n";
  echo "ora= ".$ora."<br />\n";
  echo "minuto= ".$minuto."<br />\n";
  echo "day= ".$day."<br />\n";
  echo "hour= ".$hour."<br />\n";
  echo "minute= ".$minute."<br />\n";
  echo "JDm= ".$JDm."<br />\n";
  echo "phase= <pre>";
  print_r($phase);
  echo "</pre><br />\n<hr />\n";
  }
  //*/

  return $phase;
}

// get date vars to use
$year  = $yr;
$month = $mo;
$d     = $dy;

//* debugging ----
if ($DEBUG) {
echo "year= ".$year."<br />\n";
}
//*/

$montht  = $month; // set temp var
$year   += ($month-0.5)/12; // get decimal year with month accuracy
$K       = ipart(($year-2000)*12.3685) + 5; // get number of lunation since jan 2000, add 5 to make sure were in the current lunation, we'll remove some below

//* debugging ----
if ($DEBUG) {
echo "month= ".$month."<br />\n";
echo "d= ".$d."<br />\n";
echo "year= ".$year."<br />\n";
echo "K= ".$K."<br />\n";
}
//*/

mainmoonphase( 1 ); // run once to calculate JDm
while ( $JD < $JDm ) // if the new moon is in the future
{
 	$K--; // decrease K
 	//* debugging ----
 	if ($DEBUG) {
	echo "<br />K decreased !!<br />\n";
	}
	//*/
	mainmoonphase( 1 );
}

// run the script for the four phases of the current lunation
// and save to a wrapping array
$PhaseWrap = array(
"New Moon"      => mainmoonphase( 1 ),
"First Quarter" => mainmoonphase( 2 ),
"Full Moon"     => mainmoonphase( 3 ),
"Last Quarter"  => mainmoonphase( 4 )
);

$K++; // increase K to get the second new moon
// and add it to the end of the array
$PhaseWrap += array(
"New Moon2" => mainmoonphase( 1 ) // and get it
);

// calculate age of current moon
$age = $JD - $PhaseWrap["New Moon"]["JD"];

// calculate length of current lunation
$length = $PhaseWrap["New Moon2"]["JD"] - $PhaseWrap["New Moon"]["JD"];

// set next major phase for display
if ( ( 0 < $aws ) && ( $aws <= 180 ) ) {
  $ph_next   = "Full Moon";
  $ph_JD     = $PhaseWrap["Full Moon"]["JD"];
  $ph_JDE    = $PhaseWrap["Full Moon"]["JDE"];
  $ph_DeltaT = $PhaseWrap["Full Moon"]["DeltaT"];
  $ph_year   = $PhaseWrap["Full Moon"]["Year"];
  $ph_month  = $PhaseWrap["Full Moon"]["Month"];
  $ph_day    = $PhaseWrap["Full Moon"]["Day"];
  $ph_hour   = $PhaseWrap["Full Moon"]["Hour"];
  $ph_minute = $PhaseWrap["Full Moon"]["Minute"];
  $ph_daysto = $PhaseWrap["Full Moon"]["JD"] - $JD;
  $ph_prev   = "New Moon";
  $ph_daysfr = $JD - $PhaseWrap["New Moon"]["JD"];
} else { // 180 < $aws <= 360 (0)
  $ph_next   = "New Moon";
  $ph_JD     = $PhaseWrap["New Moon2"]["JD"];
  $ph_JDE    = $PhaseWrap["New Moon2"]["JDE"];
  $ph_DeltaT = $PhaseWrap["New Moon2"]["DeltaT"];
  $ph_year   = $PhaseWrap["New Moon2"]["Year"];
  $ph_month  = $PhaseWrap["New Moon2"]["Month"];
  $ph_day    = $PhaseWrap["New Moon2"]["Day"];
  $ph_hour   = $PhaseWrap["New Moon2"]["Hour"];
  $ph_minute = $PhaseWrap["New Moon2"]["Minute"];
  $ph_daysto = $PhaseWrap["New Moon2"]["JD"] - $JD;
  $ph_prev   = "Full Moon";
  $ph_daysfr = $JD - $PhaseWrap["Full Moon"]["JD"];
}

// once more to clean it up
local( $yr, $mo, $dy, $hr, $mn, $sc, $z );

//* debugging ----
if ($DEBUG) {
echo "<pre>\n";
print_r($PhaseWrap);
echo "\n</pre>";
echo $ph_daysto." days to ".$ph_next."<br />";
echo $ph_daysfr." days since ".$ph_prev;
}
//*/


/*-----------------------------------------------------------------------------*\
   extra basic functions
\*-----------------------------------------------------------------------------*/

function fnred( $x ) { // reduces values of degree angles to between 0 - 360
  return $x - 360 * floor( $x / 360 ); // the negative sign of ipart() messes this up, so use floor()
}
function fnred180( $x ) { // reduces values of degree angles to between 0 - 180
  return $x - 180 * floor( $x / 180 ); // the negative sign of ipart() messes this up, so use floor()
}
function fnred_180( $x ) { // reduces values of degree angles to between -180 - 180
  if ( sign( $x ) > 0 ) { // are we positive ?
	while ( $x >= 180 ) { // until we get below 180
	  $x -= 360; // keep subtracting 360
	}
  }
  else { // or are we negative ?
	while ( $x <= -180 ) { // until we get above -180
	  $x += 360; // keep adding 360
	}
  }
  return $x;
}
function fnred1( $x ) { // reduces values to between 0 - 1
  return $x - 1 * floor( $x ); // the negative sign of ipart() messes this up, so use floor()
}
function dsin( $x ) { // sine of degree angles
  return sin( deg2rad( fnred( $x ) ) );
}
function dcos( $x ) { // cosine of degree angles
  return cos( deg2rad( fnred( $x ) ) );
}
function dtan( $x ) { // tangent of degree angles
  return tan( deg2rad( fnred( $x ) ) );
}
function pad( $x ) { // pads the output with leading zeros
  $x = "".$x;                           // convert number to string
  if ( 1 == strlen( $x ) ) $x = "0".$x; // add a zero if only one digit
  if ( 0 == $x ) $x = "00";             // or convert to 00 if value is zero
  return $x;
}
function sign( $x ) { // returns the sign of $x, or 0
  if ( 0 == $x ) return 0; // if 0, division will cause errors, so return 0 now
  return ( $x / abs( $x ) );
}
function ipart( $x ) { // returns the integer part of a number
  $s = sign( $x );         // collect the sign value
  $t = floor( abs( $x ) ); // find the integer part without sign
  return $t * $s;          // and put the sign back on
}
function fpart( $x ) { // returns the fractional part of a number
  $s = sign( $x );         // collect the sign value
  $f = floor( abs( $x ) ); // find the integer part without sign
  $t = abs( $x ) - $f;     // subtract from original to get the fractional part
  return $t * $s;          // and put the sign back on
}
function dms( $x ) { // returns the degrees, minutes, and seconds of a decimal degree angle as a string
  $n = sign( $x );  // save sign of angle
  $x = abs( $x );   // get abs value for proper ipart functioning
  $d = ipart( $x ); // get integer part in degrees
  $t = $x - $d;     // get fractional part in degrees
  $t = $t * 60;     // convert fractional part to minutes
  $m = ipart( $t ); // get integer part in minutes
  $s = $t - $m;     // get fractional part of minutes
  $s = $s * 60;     // convert to decimal seconds

  if ( 0 > $n ) {   // get the sign as a string
	$ns = "-";
  } else {
	$ns = "";
  }
  return $ns . $d . "&deg;" . $m . "'" . round( $s, 3 ) . '"'; // return as string
}
function hms( $x ) { // returns hours, minutes, and seconds of a decimal hour as a string
  $x = $x / 15;     // convert to hours
  $n = sign( $x );  // save sign of angle
  $x = abs( $x );   // get abs value for proper ipart functioning
  $d = ipart( $x ); // get integer part in degrees
  $t = $x - $d;     // get fractional part in degrees
  $t = $t * 60;     // convert fractional part to minutes
  $m = ipart( $t ); // get integer part in minutes
  $s = $t - $m;     // get fractional part of minutes
  $s = $s * 60;     // convert to decimal seconds

  if ( 0 > $n ) {   // get the sign as a string
	$ns = "-";
  } else {
	$ns = "";
  }
  return $ns . $d . "h " . $m . "m " . round( $s, 3 ) . 's'; // return as string
}
function cal2jd( $y, $m, $d, $h, $mn ) { // calculate the Julian Day for a calendar date
  if( $m > 2 ) {
	$y += 4716;
	$m += 1;
  } else {
	$y += 4715;
	$m += 13;
  }

  $d += ( $h + ( $mn / 60 ) ) / 24; // add minutes, and hours into day as decimal part

  $JD  = ipart( 365.25 * $y ) + ipart( 30.6001 * $m ) + $d - 1537.5; // correct from 1901 - 2099 (B = -13)
  return $JD;
}
function interpolate( $y1, $y2, $y3, $n ) { // interpolate a value given three values
  $a = $y2 - $y1;
  $b = $y3 - $y2;
  $c = $b  - $a;
  $y = $y2 + ( $n / 2 ) * ( $a + $b + ( $c * $n ) );
  return $y;
}

if ( ! function_exists('call')) {
function call($var = 'NULLNULL')
{
	if ('NULLNULL' == $var)
	{
		echo "\n\n<span style=\"color:red;font-wieght:bold;\">*****</span>\n\n";
	}
	else
	{
		echo "\n\n<pre>\n";
		print_r($var);
		echo "\n</pre>\n\n";
	}
}
}

