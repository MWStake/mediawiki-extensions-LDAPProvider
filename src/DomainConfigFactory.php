<?php

namespace MediaWiki\Extensions\LDAPProvider;

use ExtensionRegistry;
use FormatJson;
use Hashconfig;
use MWException;
use MediaWiki\MediaWikiServices;
use MultiConfig;

class DomainConfigFactory {

	const DEFAULT_CONF_ATTR = 'LDAPProviderDefaultSettings';

	/**
	 *
	 * @var DomainConfigFactory
	 */
	protected static $instance = null;

	/**
	 *
	 * @var array
	 */
	protected $config = null;

	/**
	 * @param string $path to config file
	 */
	public function __construct( $path ) {
		if ( !is_readable( $path ) ) {
			throw new MWException(
				wfMessage( 'ldapprovider-domain-config-not-found' )->params( $path )->plain()
			);
		}
		$this->config = FormatJson::decode(
			file_get_contents( $path ),
			true
		);

		if ( $this->config === false || count( $this->config ) === 0 ) {
			throw new MWException(
				"Could not parse configuration file '$path'!"
			);
		}
	}

	/**
	 * Returns a specific configuration from the common LDAP configuration file
	 * referenced in "$LDAPProviderDomainConfigs"
	 * @param string $domain to use
	 * @param string $section to get
	 * @return Config
	 */
	public function factory( $domain, $section ) {
		if ( !isset( $this->config[$domain] ) ) {
			throw new MWException(
				"No configuration available for domain '$domain'!"
			);
		}
		if ( !isset( $this->config[$domain][$section] ) ) {
			throw new MWException(
				"No section '$section' found in configuration for "
				. "domain '$domain'!"
			);
		}

		$extRegistry = ExtensionRegistry::getInstance();
		$defaultConfig
			= $extRegistry->getAttribute( static::DEFAULT_CONF_ATTR );
		$defaultSectionConf = [];
		if ( isset( $defaultConfig[$section] ) ) {
			$defaultSectionConf = $defaultConfig[$section];
		}

		return new MultiConfig( [
			new HashConfig( $this->config[$domain][$section] ),
			new HashConfig( $defaultSectionConf )
		] );
	}

	/**
	 * Returns all configured domains
	 * @return string[]
	 */
	public function getConfiguredDomains() {
		return array_keys( $this->config );
	}

	/**
	 * Accessor for the singleton object
	 * @return DomainConfigFactory
	 */
	public static function getInstance() {
		if ( self::$instance === null ) {
			$extensionConfig = MediaWikiServices::getInstance()
				->getConfigFactory()->makeConfig( 'ldapprovider' );
			self::$instance = new self(
				$extensionConfig->get( Config::DOMAIN_CONFIGS )
			);
		}
		return self::$instance;
	}
}
