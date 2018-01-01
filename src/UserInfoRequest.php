<?php

namespace MediaWiki\Extensions\LDAPProvider;

use Config;

class UserInfoRequest {

	/**
	 *
	 * @var Client
	 */
	protected $ldapClient = null;

	/**
	 *
	 * @var Config
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
	 * @param Client $ldapClient to use
	 * @param Config $config for retrieving config from
	 */
	public function __construct( Client $ldapClient, Config $config ) {
		$this->ldapClient = $ldapClient;
		$this->config = $config;
		$this->userBaseDN = $config->get( ClientConfig::USER_BASE_DN );
		$this->searchAttribute = $config->get(
			ClientConfig::USER_DN_SEARCH_ATTR
		);
	}

	/**
	 * @param string $username to get info for
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
