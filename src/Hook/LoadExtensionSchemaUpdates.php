<?php

namespace MediaWiki\Extension\LDAPProvider\Hook;

class LoadExtensionSchemaUpdates {

	/**
	 *
	 * @var \DatabaseUpdater
	 */
	protected $updater = null;

	/**
	 *
	 * @param \DatabaseUpdater $updater
	 * @return bool
	 */
	public static function callback( $updater ) {
		$className = static::class;
		$hookHandler = new $className(
			$updater
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param \DatabaseUpdater $updater
	 */
	public function __construct( $updater ) {
		$this->updater = $updater;
	}

	public function process() {
		$base = dirname( dirname( __DIR__ ) );
		switch ( $this->updater->getDB()->getType() ) {
			case 'mysql':
			case 'sqlite':
				$this->updater->addExtensionTable( 'ldap_domains', "$base/schema/ldap-mysql.sql" );
				break;
			case 'postgres':
				$this->updater->addExtensionTable( 'ldap_domains', "$base/schema/ldap-postgres.sql" );
				break;
		}
		return true;
	}
}
