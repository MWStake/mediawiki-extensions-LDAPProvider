<?php

namespace MediaWiki\Extension\LDAPProvider;

use MediaWiki\Extension\LDAPProvider\ClientConfig;

class UserInfoRequest {

	/**
	 *
	 * @var Client
	 */
	protected $ldapClient = null;

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var string
	 */
	protected $userBaseDN = '';

	/**
	 *
	 * @var string
	 */
	protected $searchAttribute = '';

	/**
	 *
	 * @param Client $ldapClient
	 * @param \Config $config
	 */
	public function __construct( $ldapClient, $config ) {
		$this->ldapClient = $ldapClient;
		$this->config = $config;
		$this->userBaseDN = $config->get( ClientConfig::USER_BASE_DN );
		$this->searchAttribute = $config->get( ClientConfig::USER_DN_SEARCH_ATTR );
	}

	/**
	 * @param string $username
	 * @return array
	 */
	public function getUserInfo( $username ) {
		$escapedUserName = new EscapedString( $username );
		// We need to do a subbase search for the entry
		$filter = "({$this->searchAttribute}=$escapedUserName)";

		// We explicitly put "memberof" here because it's an operational
		// attribute in some servers.
		$attributes = [ "*", "memberof" ];

		$entry = $this->ldapClient->search(
			$filter,
			$this->userBaseDN,
			$attributes
		);

		return $entry;
	}
}