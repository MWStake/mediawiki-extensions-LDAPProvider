<?php

namespace MediaWiki\Extension\LDAPProvider;

use GlobalVarConfig;

class Config extends GlobalVarConfig {

	const CLIENT_REGISTRY = 'ClientRegistry';
	const DOMAIN_CONFIGS = 'DomainConfigs';
	const CACHE_TYPE = 'CacheType';
	const CACHE_TIME = 'CacheTime';

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
