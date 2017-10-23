<?php

namespace MediaWiki\Extension\LDAPProvider;

class Config extends \GlobalVarConfig {

	const MAIN_CLIENT_INI_FILE = 'MainClientINIFile';
	const CLIENT_REGISTRY = 'ClientRegistry';
	const DOMAIN_CONFIGS = 'DomainConfigs';

	public function __construct() {
		parent::__construct( 'LDAPProvider' );
	}

	/**
	 * Factory method for MediaWikiServices
	 * @return Config
	 */
	public static function newInstance() {
		return new self();
	}
}