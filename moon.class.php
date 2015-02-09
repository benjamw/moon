<?php

/*
+---------------------------------------------------------------------------
|
|   moon.class.php (php 5.2+)
|
|   by Benjam Welker
|   http://iohelix.net
|
+---------------------------------------------------------------------------
|
|   > Moon Data module
|   > Date started: 2011-12-04
|	> Date original script started: 2004-12-01
|
|   > Module Version Number: 0.8.0
|
|	> Change log:
|		- 2011-12-04
|		Translation to OO started
|
+---------------------------------------------------------------------------
|
|	IMPORTANT LINKS
|
|	http://maia.usno.navy.mil/
|	ftp://maia.usno.navy.mil/ser7/deltat.data
|	http://www.php.net/manual/en/timezones.php
|
+---------------------------------------------------------------------------
|
|	CREDITS
|
|	all algorithms adapted from "Astronomical Algorithms" by Meeus, Jean
|	published by Willmann-Bell, Inc. (1998) ISBN- 0-943396-61-1
|	unless otherwise noted.
|
|	there is a more recent version with the algorithms already on disk...
|	wish i had had that one...   =/
|
+---------------------------------------------------------------------------
*/

// removed from the class because it's huge
require 'lbr.php';

class Moon
{

	/**
	 *		PROPERTIES
	 * * * * * * * * * * * * * * * * * * * * * * * * * * */

/* list of all global vars ----------------------------------------------------*\

--   $yrL, $moL, $dyL, $hrL, $mnL, $scL, $z
   $lambda, $beta, $Delta, $pi, $theta
   $Delta_psi, $Delta_epsilon, $epsilon0, $epsilon, $Omega
   $delta, $alpha
   $L, $B, $R, $R_km, $circle_dot, $beta0
   $lambda0, $alpha0, $delta0, $theta0, $R_km
   $giorno, $month, $year
   $year, $month, $montht, $giorno, $JDEm, $K
\*-----------------------------------------------------------------------------*/


	/** const property DELTA_T_SEC
	 *		The current delta T value in seconds
	 *
	 * @param float
	 * @see ftp://maia.usno.navy.mil/ser7/deltat.data
	 * @updated 2014-09-07
	 */
	const DELTA_T_SEC = 67.3890;




	/** public property local_tz
	 *		The local timezone string
	 *
	 * @param string
	 * @see http://www.php.net/manual/en/timezones.php
	 */
	public $local_tz = 'UTC';


	/** public property lambda
	 *		moon's geocentric longitude of center (ecliptic)
	 *
	 * @param float
	 */
	public $lambda;


	/** public property beta
	 *		moon's geocentric latitude of center (ecliptic)
	 *
	 * @param float
	 */
	public $beta;


	/** public property Delta
	 *		distance from earth center to moon center (km)
	 *
	 * @param float
	 */
	public $Delta;


	/** public property pi
	 *		moon equitorial horizontal parallax
	 *
	 * @param float
	 */
	public $pi;


	/** public property theta
	 *		angular size of moon
	 *
	 * @param float
	 */
	public $theta;



	/**
	 *		METHODS
	 * * * * * * * * * * * * * * * * * * * * * * * * * * */

	/** public function __construct
	 *		class constructor
	 *		sets the current time and some time variables
	 *		sets the user's timezone, if given
	 *
	 * @param string timezone
	 * @action instantiates object
	 * @return void
	 */
	public function __construct($users_timezone = 'UTC')
	{
		date_default_timezone_set('UTC');

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

$DeltaTmin = DELTA_T_SEC / 60;
$DeltaT = DELTA_T_SEC * 1.157407e-5;

if( $mo > 2 ) {
  $y = $yr + 4716;
  $m = $mo + 1;
} else {
  $y = $yr + 4715;
  $m = $mo + 13;
}

$d = $dy + (($hr + (($mn + ($sc / 60)) / 60)) / 24); // add seconds, minutes, and hours into day as decimal part

$JD  = ipart( 365.25 * $y ) + ipart( 30.6001 * $m ) + $d - 1537.5; // correct from 1901 - 2099 (B = -13)
$MJD = $JD - 2400000.5; // Modified Julian Day
$JDE = $JD + $DeltaT;

		try {
			$this->set_timezone($users_timezone);
		}
		catch (Exception $e) {
			$this->set_timezone('UTC');
		}
	}


	/** public function set_timezone
	 *		check the given timezone against the list of allowed
	 *		timezones, and if it doesn't match, throw an exception
	 *
	 * @param string timezone
	 * @return void
	 */
	public function set_timezone($timezone)
	{
		// check the given timezone against the list of allowed
		// timezones, and if it doesn't match, use UTC
 	}

}

