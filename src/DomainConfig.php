<?php

namespace MediaWiki\Extension\LDAPProvider;

class DomainConfig extends \GlobalVarConfig {

	protected $domain = '';

	/**
	 *
	 * @param string $domain
	 * @param string $baseVar
	 */
	public function __construct( $domain, $baseVar = 'LDAPProviderDomainConfigs' ) {
		$this->domain = $domain;

		parent::__construct( $baseVar );
	}

	/**
	 * Get a variable with a given prefix, if not the defaults.
	 *
	 * @param string $prefix Prefix to use on the variable, if one.
	 * @param string $name Variable name without prefix
	 * @return mixed
	 */
	protected function getWithPrefix( $prefix, $name ) {
		return $GLOBALS[$prefix][$this->domain][$name];
	}

	/**
	 * Check if a variable with a given prefix is set
	 *
	 * @param string $prefix Prefix to use on the variable
	 * @param string $name Variable name without prefix
	 * @return bool
	 */
	protected function hasWithPrefix( $prefix, $name ) {
		return array_key_exists( $name, $GLOBALS[$prefix][$this->domain] );
	}
}