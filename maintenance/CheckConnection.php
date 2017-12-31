<?php

namespace MediaWiki\Extension\LDAPProvider\Maintenance;

use Maintenance;
use MediaWiki\Extension\LDAPProvider\ClientFactory;

$maintPath = ( getenv( 'MW_INSTALL_PATH' ) !== false
			  ? getenv( 'MW_INSTALL_PATH' )
			  : __DIR__ . '/../../..' ) . '/maintenance/Maintenance.php';
if ( !file_exists( $maintPath ) ) {
	echo "Please set the environment variable MW_INSTALL_PATH "
		. "to your MediaWiki installation.\n";
	exit( 1 );
}
require_once $maintPath;

class CheckConnection extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addOption(
			'config', 'The json config to test', true, true, "c"
		);
		$this->addOption( 'domain', 'The domain to test', true, true, "d" );
		$this->addArg( 'search', 'Search to execute.', true );
	}

	/**
	 * Where the action happens
	 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
	 */
	public function execute() {
		// @codingStandardsIgnoreStart
		global $LDAPProviderDomainConfigs;
		// @codingStandardsIgnoreEnd

		$LDAPProviderDomainConfigs = $this->getOption( "config" );
		$factory = ClientFactory::getInstance();
		$client = $factory->getForDomain( $this->getOption( "domain" ) );
		var_dump( $client->search( $this->getArg( "search" ) ) );
	}

	/**
	 * Add an old global variable to the config
	 *
	 * @param string $varName the global variable name to get
	 * @param string $newSettingPath where the new storage place is
	 * @SuppressWarnings(SuperGlobals)
	 */
	protected function addToNewConfig( $varName, $newSettingPath ) {
		if ( !isset( $GLOBALS[$varName] ) ) {
			return;
		}
		foreach ( $GLOBALS[$varName] as $domain => $oldConfig ) {
			$parts = explode( '.', "$domain.$newSettingPath" );
			$config =& $this->newConfig;
			foreach ( $parts as $part ) {
				if ( !isset( $config[$part] ) ) {
					$config[$part] = [];
				}
				$config =& $config[$part];
			}
			$config = $oldConfig;
		}
	}
}

$maintClass = __NAMESPACE__ . '\\CheckConnection';
require_once RUN_MAINTENANCE_IF_MAIN;
