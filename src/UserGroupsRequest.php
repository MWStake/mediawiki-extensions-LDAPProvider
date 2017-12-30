<?php

namespace MediaWiki\Extension\LDAPProvider;

class UserGroupsRequest {

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
	protected $groupBaseDN = '';

	/**
	 *
	 * @param Client $ldapClient
	 * @param \Config $config
	 */
	public function __construct( $ldapClient, $config ) {
		$this->ldapClient = $ldapClient;
		$this->config = $config;
		$this->groupBaseDN = $config->get( ClientConfig::GROUP_BASE_DN );
	}

	/**
	 * @param string $username
	 * @return GroupList
	 */
	public function getUserGroups( $username ) {
		// TODO: Implement
	}
}
