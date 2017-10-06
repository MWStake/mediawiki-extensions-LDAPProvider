<?php

namespace MediaWiki\Extension\LDAPProvider;

class TestClient extends Client {
	protected $canBindAsCallback = null;

	protected $searchCallback = null;

	public function __construct( $canBindAsCallback, $searchCallback ) {
		$this->canBindAsCallback = $canBindAsCallback;
		$this->searchCallback = $searchCallback;
	}

	public function canBindAs( $username, $password ) {
		return $this->canBindAsCallback( $username, $password );
	}

	public function search( $match, $attrs = array() ) {
		return $this->searchCallback( $match, $attrs );
	}
}