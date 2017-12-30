<?php

namespace MediaWiki\Extension\LDAPProvider;

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
	 * @param string $path
	 */
	public function __construct( $path ) {
		if ( !is_readable( $path ) ) {
			throw new \MWException( "Could not access configuration file '$path'!" );
		}
		$this->config = \FormatJson::decode(
			file_get_contents( $path ),
			true
		);

		if ( $this->config === false ) {
			throw new \MWException( "Could not parse configuration file '$path'!" );
		}
	}

	/**
	 * Returns a specific configuration from the common LDAP configuration file
	 * referenced in "$LDAPProviderDomainConfigs"
	 * @param string $domain
	 * @param string $section
	 * @return \Config
	 */
	public function factory( $domain, $section ) {
		if ( !isset( $this->config[$domain] ) ) {
			throw new \MWException( "No configuration available for domain '$domain'!" );
		}
		if ( !isset( $this->config[$domain][$section] ) ) {
			throw new \MWException( "No section '$section' found in configuration for domain '$domain'!" );
		}

		$extRegistry = \ExtensionRegistry::getInstance();
		$defaultConfig = $extRegistry->getAttribute( static::DEFAULT_CONF_ATTR );
		$defaultSectionConf = [];
		if ( isset( $defaultConfig[$section] ) ) {
			$defaultSectionConf = $defaultConfig[$section];
		}

		return new \MultiConfig( [
			new \HashConfig( $this->config[$domain][$section] ),
			new \HashConfig( $defaultSectionConf )
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
			$extensionConfig = \MediaWiki\MediaWikiServices::getInstance()
				->getConfigFactory()->makeConfig( 'ldapprovider' );
			self::$instance = new self(
				$extensionConfig->get( Config::DOMAIN_CONFIGS )
			);
		}
		return self::$instance;
	}
}
