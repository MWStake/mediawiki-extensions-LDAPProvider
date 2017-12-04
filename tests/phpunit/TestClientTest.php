<?php

namespace MediaWiki\Extension\LDAPProvider\Tests;

use \MediaWiki\Extension\LDAPProvider\TestClient;

class TestClientTest extends \PHPUnit_Framework_TestCase {
	public function testCallbacks() {
		$testClient = new TestClient( [
			'canBindAs' => function( $username, $password ) {
				return strtoupper( $username );
			},
			'search' => function( $match, $attribs ) {
				return strtoupper( $match );
			}
		] );

		$this->assertEquals(
			'USER',
			$testClient->canBindAs( 'User', 'Somepass'),
			'Should have executed the "canBindAs" callback'
		);

		$this->assertEquals(
			'SOME SEARCH QUERY',
			$testClient->search( 'Some search query' ),
			'Should have executed the "canBindAs" callback'
		);
	}
}