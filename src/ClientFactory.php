<?php

namespace MediaWiki\Extension\LDAPProvider;

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

	protected function __construct( &$globals ) {
		$this->domainClientFactories = $globals['LDAPProviderClientRegistry'];
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
		if( self::$instance === null ) {
			self::$instance = new self( $GLOBALS );
		}
		return self::$instance;
	}

	/**
	 *
	 * @param string $domain
	 * @return Client
	 */
	public function getForDomain( $domain ) {
		if( !isset( $this->clients[$domain] ) ) {
			if( !isset( $this->domainClientFactories[$domain] ) ) {
				$clientConfig = DomainConfigFactory::getInstance()->factory( $domain, 'connection' );
				$this->clients[$domain] = new Client( $clientConfig );
			}
			else {
				$callback = $this->domainClientFactories[$domain];
				$this->clients[$domain] = $callback();
			}

			if( $this->clients[$domain] instanceof Client === false ) {
				throw new \MWException( "Client factory for domain '$domain' did not return a valid Client object" );
			}
		}
		return $this->clients[$domain];
	}
}