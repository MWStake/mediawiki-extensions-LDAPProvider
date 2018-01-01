<?php

namespace MediaWiki\Extensions\LDAPProvider\Tests;

use MediaWiki\Extensions\LDAPProvider\TestClient;
use PHPUnit_Framework_TestCase;

class TestClientTest extends PHPUnit_Framework_TestCase {
	/**
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public function testCallbacks() {
		$testClient = new TestClient( [
			'canBindAs' => function ( $username, $password ) {
				return strtoupper( $username );
			},
			'search' => function ( $match, $attribs ) {
				return strtoupper( $match );
			}
		] );

		$this->assertEquals(
			'USER',
			$testClient->canBindAs( 'User', 'Somepass' ),
			'Should have executed the "canBindAs" callback'
		);

		$this->assertEquals(
			'SOME SEARCH QUERY',
			$testClient->search( 'Some search query' ),
			'Should have executed the "canBindAs" callback'
		);
	}
}
