<?php

namespace MediaWiki\Extension\LDAPProvider;

use MWException;

class ClientFactory {

	/**
	 *
	 * @var Client[]
	 */
	protected $clients = [];

	/**
	 *
	 * @var callable[]
	 */
	protected $domainClientFactories = [];

	protected function __construct() {
		$this->domainClientFactories
			= Config::newInstance()->get( "ClientRegistry" );
	}

	/**
	 *
	 * @var ClientFactory
	 */
	protected static $instance = null;

	/**
	 * Accessor for the singleton object
	 * @return ClientFactory
	 */
	public static function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Given a domain, get a client
	 *
	 * @param string $domain to get
	 * @return Client
	 * @throws MWException
	 */
	public function getForDomain( $domain ) {
		if ( !isset( $this->clients[$domain] ) ) {
			if ( !isset( $this->domainClientFactories[$domain] ) ) {
				$clientConfig = DomainConfigFactory::getInstance()->factory(
					$domain,
					ClientConfig::DOMAINCONFIG_SECTION
				);
				$this->clients[$domain] = new Client( $clientConfig );
			} else {
				$callback = $this->domainClientFactories[$domain];
				$this->clients[$domain] = $callback();
			}
			if ( $this->clients[$domain] instanceof Client === false ) {
				throw new MWException(
					"Client factory for domain '$domain' did not "
					. "return a valid Client object"
				);
			}
		}
		return $this->clients[$domain];
	}
}
