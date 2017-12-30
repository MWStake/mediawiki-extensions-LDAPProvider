<?php

if ( PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );
ini_set( 'display_errors', 1 );

if (
	!class_exists( 'MediaWiki\\Extension\\LDAPProvider\\Client' )
	|| ( $version = MediaWiki\Extension\LDAPProvider\Client::getVersion() ) === null
) {
	die( "\nLDAPProvider is not available, please check your Composer or LocalSettings.\n" );
}

print sprintf( "\n%-20s%s\n", "LDAPProvider: ", $version );
