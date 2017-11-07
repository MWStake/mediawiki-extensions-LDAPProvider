<?php

namespace MediaWiki\Extension\LDAPProvider\Maintenance;

$maintPath = ( getenv( 'MW_INSTALL_PATH' ) !== false
			  ? getenv( 'MW_INSTALL_PATH' )
			  : __DIR__ . '/../../..' ) . '/maintenance/Maintenance.php';
if ( !file_exists( $maintPath ) ) {
	echo "Please set the environment variable MW_INSTALL_PATH "
		. "to your MediaWiki installation.\n";
	exit( 1 );
}
require_once $maintPath;

use \Maintenance;

class ConvertLdapAuthenticationConfig extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addOption( 'output', 'Whether or not to output additional information', true, false );
	}

	protected $newConfig = [];

	protected $oldConfigVarNames = [
		'wgLDAPServerNames' => 'connection.server',
		'wgLDAPProxyAgent' => 'connection.user',
		'wgLDAPProxyAgentPassword' => 'connection.password',
		'wgLDAPOptions' => 'connection.options',
		'wgLDAPPort' => 'connection.port',
		'wgLDAPEncryptionType' => 'connection.enctype',
		'wgLDAPBaseDNs' => 'connection.basedn',
		'wgLDAPPreferences' => 'userinfo.attributes-map',
		'wgLDAPRequiredGroups' => 'authorization.rules.groups.required',
		'wgLDAPExcludedGroups' => 'authorization.rules.groups.excluded'
	];

	public function execute() {
		foreach( $this->oldConfigVarNames as $varName => $newSettingPath ) {
			$this->addToNewConfig( $varName, $newSettingPath );
		}
		$this->writeJSONFile();
	}

	protected function writeJSONFile() {
		$file = new \SplFileInfo( $this->getOption( 'output' ) );
		$filename = $file->getPathname();
		if( $file->isDir() ) {
			$filename .= '/'.wfWikiID().'.ldap.json';
		}

		file_put_contents(
			$filename,
			\FormatJson::encode( $this->newConfig, true )
		);
	}

	protected function addToNewConfig( $varName, $newSettingPath ) {
		if( !isset( $GLOBALS[$varName] ) ) {
			return;
		}
		foreach( $GLOBALS[$varName] as $domain => $oldConfig ) {
			#$newConfig = $this->getNewConfig( "$domain.$newSettingPath" );
			$parts = explode( '.', "$domain.$newSettingPath" );
			$config =& $this->newConfig;
			foreach( $parts as $part ) {
				if( !isset( $config[$part] ) ) {
					$config[$part] = [];
				}
				$config =& $config[$part];
			}
			$config = $oldConfig;
		}
	}

	protected function getNewConfig( $newSettingPath ) {
		$parts = explode( '.', $newSettingPath );
		$config =& $this->newConfig;
		foreach( $parts as $part ) {
			if( !isset( $config[$part] ) ) {
				$config[$part] = [];
			}
			$config =& $config[$part];
		}
		return $config;
	}

}

$maintClass = 'MediaWiki\\Extension\\LDAPProvider\\Maintenance\\ConvertLdapAuthenticationConfig';
require_once( RUN_MAINTENANCE_IF_MAIN );