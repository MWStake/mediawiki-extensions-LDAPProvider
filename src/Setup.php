<?php

namespace MediaWiki\Extension\LDAPProvider;

class Setup {
	public static function onRegistration() {
		$GLOBALS['LDAPProviderClientRegistry'] = [
			'LDAP' => function() {
				$configFactory = \MediaWiki\MediaWikiServices::getInstance()->getConfigFactory();
				$extensionConfig = $configFactory->makeConfig( 'ldapprovider' );
				$mainClientConfig = new INIClientConfig(
					$extensionConfig->get( Config::MAIN_CLIENT_INI_FILE )
				);

				return new Client( $mainClientConfig );
			}
		];
	}

	/**
	 * Add tables to database
	 * @param \DatabaseUpdater $updater
	 * @return boolean
	 */
	public static function onLoadExtensionSchemaUpdates( $updater ) {
		$base = dirname( __DIR__ );
		switch ( $updater->getDB()->getType() ) {
			case 'mysql':
			case 'sqlite':
				$updater->addExtensionTable( 'ldap_domains', "$base/schema/ldap-mysql.sql" );
				break;
			case 'postgres':
				$updater->addExtensionTable( 'ldap_domains', "$base/schema/ldap-postgres.sql" );
				break;
		}
		return true;
	}
}