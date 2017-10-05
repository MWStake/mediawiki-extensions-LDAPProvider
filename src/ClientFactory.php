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

	public static function getInstance() {
		if( self::$instance === null ) {
			self::$instance = new self( $GLOBALS );
		}
		return self::$instance;
	}

	/**
	 *
	 * @param string $domain
	 * @return \MediaWiki\Extension\LDAPProvider\Client
	 */
	public function getForDomain( $domain ) {
		if( !isset( $this->clients[$domain] ) ) {
			if( !isset( $this->domainClientFactories[$domain] ) ) {
				throw new \MWException( "No client factory set for domain '$domain'" );
			}
			$callback = $this->domainClientFactories[$domain];
			$this->clients[$domain] = $callback();

			if( $this->clients[$domain] instanceof Client === false ) {
				throw new \MWException( "Client factory for domain '$domain' did not return a valid Client object" );
			}
		}
		return $this->clients[$domain];
	}
}