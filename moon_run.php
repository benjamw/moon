<?php

// make sure it's the right directory
$dir = dirname(__FILE__);
chdir($dir);

if ( ! empty($argv[1]) && ('hevy' === $argv[1])) {
	// the hevy version
	ob_start( );
	include '../moocow/moonfeed.php';
	$moon = ob_get_clean( );
	file_put_contents('moonfeed.xml', $moon);
	echo $moon;
}
else {
	// the lite version
	ob_start( );
	include '../moocow/moonlite.php';
	$moon = ob_get_clean( );
	file_put_contents('moonlite.xml', $moon);
	echo $moon;
}
