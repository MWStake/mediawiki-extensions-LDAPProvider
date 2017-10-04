<?php

namespace MediaWiki\Extension\LDAPProvider;

class Config extends \MultiConfig {

	const INI_FILE = 'INIFile';
	const SERVER = 'server';
	const USER = 'user';
	const PASSWORD = 'pass';
	const BASE_DN = 'basedn';

	public function __construct() {
		$globalVarsConfig = new \GlobalVarConfig( 'LDAPProvider' );
		$iniFileConfig = $this->makeINIFileConfig(
			$globalVarsConfig->get( self::INI_FILE )
		);

		parent::__construct( [
			$globalVarsConfig,
			$iniFileConfig
		] );
	}

	/**
	 * Factory method for MediaWikiServices
	 * @return Config
	 */
	public static function newInstance() {
		return new self();
	}

	/**
	 * Given ini file, get object.
	 *
	 * @param string $iniFilePath path to ini file
	 * @return HashConfig
	 */
	protected function makeINIFileConfig( $iniFilePath ) {
		$iniConfig = parse_ini_file( $iniFilePath );
		return new \HashConfig( $iniConfig );
	}

}