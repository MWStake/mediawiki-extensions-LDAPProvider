<?php

namespace MediaWiki\Extension\LDAPProvider\Hook;

use Config;
use GlobalVarConfig;
use IContextSource;
use MediaWiki\Extension\LDAPProvider\ClientFactory;
use MediaWiki\Extension\LDAPProvider\DomainConfigFactory;
use MediaWiki\Extension\LDAPProvider\UserDomainStore;
use MediaWiki\MediaWikiServices;
use RequestContext;
use User;

abstract class UserLoggedIn {

	/**
	 *
	 * @var User
	 */
	protected $user = null;

	/**
	 *
	 * @var ContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var Client
	 */
	protected $ldapClient = null;

	/**
	 *
	 * @var string
	 */
	protected $domain = '';

	/**
	 *
	 * @var Config
	 */
	protected $domainConfig = null;

	/**
	 *
	 * @param IContextSource $context we're operating in
	 * @param Config $config accessor
	 * @param User $user we're talking about
	 */
	public function __construct(
		IContextSource $context, Config $config, User $user
	) {
		$this->context = $context;
		$this->config = $config;
		$this->user = $user;
	}

	/**
	 *
	 * @param User $user we're going to process
	 * @return bool
	 */
	public static function callback( User $user ) {
		$handler = new static(
			static::makeContext(),
			static::makeConfig(),
			$user
		);
		return $handler->process();
	}

	/**
	 *
	 * @return bool
	 */
	public function process() {
		if ( !$this->findDomainForUser() ) {
			return true;
		};
		$this->createLdapClientForDomain();
		$this->setSuitableDomainConfig();

		return $this->doProcess();
	}

	/**
	 * @return boolean
	 */
	abstract protected function doProcess();

	/**
	 * Can be overriden by subclass
	 * @return IContextSource
	 */
	protected static function makeContext() {
		return RequestContext::getMain();
	}

	/**
	 * Can be overriden by subclass
	 * @return Config
	 */
	protected static function makeConfig() {
		return new GlobalVarConfig();
	}

	/**
	 *
	 * @return bool
	 */
	protected function findDomainForUser() {
		$userDomainStore = new UserDomainStore(
			MediaWikiServices::getInstance()->getDBLoadBalancer()
		);

		$this->domain = $userDomainStore->getDomainForUser( $this->user );
		if ( $this->domain === null ) {
			return false;
		}
		return true;
	}

	/**
	 * Fill out our ldapClient member
	 */
	protected function createLdapClientForDomain() {
		$ldapClientFactory = ClientFactory::getInstance();

		$this->ldapClient = $ldapClientFactory->getForDomain( $this->domain );
	}

	/**
	 * Set up our domainConfig member
	 */
	protected function setSuitableDomainConfig() {
		$this->domainConfig = DomainConfigFactory::getInstance()->factory(
			$this->domain,
			$this->getDomainConfigSection()
		);
	}

	/**
	 * @return string
	 */
	abstract protected function getDomainConfigSection();
}
