<?php

namespace MediaWiki\Extension\LDAPProvider\Hook;

use MediaWiki\Extension\LDAPProvider\UserDomainStore;
use MediaWiki\MediaWikiServices;
use MediaWiki\Extension\LDAPProvider\ClientFactory;
use MediaWiki\Extension\LDAPProvider\DomainConfigFactory;

abstract class UserLoggedIn {

	/**
	 *
	 * @var \User
	 */
	protected $user = null;

	/**
	 *
	 * @var \ContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var \MediaWiki\Extension\LDAPProvider\Client
	 */
	protected $ldapClient = null;

	/**
	 *
	 * @var string
	 */
	protected $domain = '';

	/**
	 *
	 * @var \Config
	 */
	protected $domainConfig = null;

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param \User $user
	 */
	public function __construct( $context, $config, $user ) {
		$this->context = $context;
		$this->config = $config;
		$this->user = $user;
	}

	/**
	 *
	 * @param \User $user
	 * @return boolean
	 */
	public static function callback( $user ) {
		$handler = new static(
			static::makeContext(),
			static::makeConfig(),
			$user
		);
		return $handler->process();
	}

	/**
	 *
	 * @return boolean
	 */
	public function process() {
		if( !$this->findDomainForUser() ) {
			return true;
		};
		$this->createLdapClientForDomain();
		$this->setSuitableDomainConfig();

		return $this->doProcess();
	}

	/**
	 * @return boolean
	 */
	protected abstract function doProcess();

	/**
	 * Can be overriden by subclass
	 * @return \IContextSource
	 */
	protected static function makeContext() {
		return \RequestContext::getMain();
	}

	/**
	 * Can be overriden by subclass
	 * @return \Config
	 */
	protected static function makeConfig() {
		return new \GlobalVarConfig();
	}

	/**
	 *
	 * @return boolean
	 */
	protected function findDomainForUser() {
		$userDomainStore = new UserDomainStore(
			MediaWikiServices::getInstance()->getDBLoadBalancer()
		);

		$this->domain = $userDomainStore->getDomainForUser( $this->user );
		if( $this->domain === null ) {
			return false;
		}
		return true;
	}

	protected function createLdapClientForDomain() {
		$ldapClientFactory = ClientFactory::getInstance();

		$this->ldapClient = $ldapClientFactory->getForDomain( $this->domain );
	}

	protected function setSuitableDomainConfig() {
		$this->domainConfig = DomainConfigFactory::getInstance()->factory(
			$this->domain,
			$this->getDomainConfigSection()
		);
	}

	/**
	 * @return string
	 */
	protected abstract function getDomainConfigSection();
}