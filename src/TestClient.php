<?php

namespace MediaWiki\Extension\LDAPProvider;

class TestClient extends Client {

	/**
	 *
	 * @var callable[]
	 */
	protected $callbacks = [];

	/**
	 *
	 * @param callable[] $searchCallback
	 */
	public function __construct( $callbacks ) {
		$this->callbacks = $callbacks;
	}

	public function canBindAs( $username, $password ) {
		return call_user_func( $this->callbacks['canBindAs'], $username, $password );
	}

	public function search( $match, $basedn = null, $attrs = [] ) {
		return call_user_func( $this->callbacks['search'], $match, $basedn, $attrs );
	}

	public function getUserGroups( $user, $groupBaseDN = '' ) {
		return call_user_func( $this->callbacks['getUserGroups'], $user, $groupBaseDN = '' );
	}

	public function getUserInfo( $username, $userBaseDN = '' ) {
		return call_user_func( $this->callbacks['getUserInfo'], $username, $userBaseDN = '' );
	}
}
