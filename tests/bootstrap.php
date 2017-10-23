<?php

if ( PHP_SAPI !== 'cli' ) {
	die( 'Not an entry point' );
}

$mwdir = getenv( "MW_INSTALL_PATH" );
if ( $mwdir === false && isset( $IP ) ) {
	$mwdir = $IP;
}

if ( !file_exists( "$mwdir/includes/AutoLoader.php" ) ) {
	die( "Please set MW_INSTALL_PATH to your MediaWiki installation.\n" );
}

global $wgAutoloadClasses;
$wgAutoloadClasses = [];
require "$mwdir/includes/AutoLoader.php";
require "$mwdir/tests/common/TestsAutoLoader.php";
$autoload = require "$mwdir/vendor/autoload.php";
