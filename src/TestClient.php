<?php

namespace MediaWiki\Extension\LDAPProvider;

class TestClient extends Client {

	/**
	 *
	 * @var callable
	 */
	protected $canBindAsCallback = null;

	/**
	 *
	 * @var callable
	 */
	protected $searchCallback = null;

	/**
	 *
	 * @param callable $canBindAsCallback
	 * @param callable $searchCallback
	 */
	public function __construct( $canBindAsCallback, $searchCallback ) {
		$this->canBindAsCallback = $canBindAsCallback;
		$this->searchCallback = $searchCallback;
	}

	public function canBindAs( $username, $password ) {
		return call_user_func( $this->canBindAsCallback, $username, $password );
	}

	public function search( $match, $basedn = null, $attrs = array() ) {
		return call_user_func( $this->searchCallback, $match, $basedn, $attrs );
	}
}