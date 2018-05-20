<?php

namespace MediaWiki\Extension\LDAPProvider;

use Config;

class UserGroupsRequest {

	/**
	 * @var Client
	 */
	protected $ldapClient = null;

	/**
	 * @var Config
	 */
	protected $config = null;

	/**
	 * @var string
	 */
	protected $groupBaseDN = '';

	/**
	 * @param Client $ldapClient to use
	 * @param Config $config will be delivered here
	 */
	public function __construct( $ldapClient, Config $config ) {
		$this->ldapClient = $ldapClient;
		$this->config = $config;
		$this->groupBaseDN = $config->get( ClientConfig::GROUP_BASE_DN );
	}

	/**
	 * @param string $username to get the groups for
	 * @return GroupList
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function getUserGroups( $username ) {
		// TODO: Implement
	}
}
