<?php

namespace MediaWiki\Extension\LDAPProvider\Tests;

use HashConfig;
use MediaWiki\Extension\LDAPProvider\Client;
use MediaWiki\Extension\LDAPProvider\ClientConfig;
use MediaWiki\Extension\LDAPProvider\ClientFactory;
use PHPUnit_Framework_TestCase;

class ClientTest extends PHPUnit_Framework_TestCase {
	public function testUserCanBind() {
		$mockBulder = $this->getMockBuilder(
			'\MediaWiki\Extension\LDAPProvider\PlatformFunctionWrapper'
		);
		$mockFunctionWrapper = $mockBulder->setMethods(
			[ 'ldap_bind', 'ldap_connect' ]
		)->getMock();
		$mockFunctionWrapper->expects( $this->any() )
			->method( 'ldap_bind' )->willReturn( 'MockBindResponse' );

		$client = new Client( $this->makeClientConfig(), $mockFunctionWrapper );
		$result = $client->canBindAs( 'SomeUserName', 'SomePassword' );

		$this->assertEquals( 'MockBindResponse', $result );
	}

	public function testSearch() {
		$this->maybeDefineLDAPConstants();

		$mockBulder = $this->getMockBuilder(
			'\MediaWiki\Extension\LDAPProvider\PlatformFunctionWrapper'
		);
		$mockFunctionWrapper = $mockBulder->setMethods( [
			'ldap_get_entries', 'ldap_connect', 'ldap_set_option',
			'ldap_bind', 'ldap_search'
		] )->getMock();
		$mockFunctionWrapper->expects( $this->any() )
			->method( 'ldap_get_entries' )
			->willReturn( 'MockGetEntriesResponse' );
		$mockFunctionWrapper->expects( $this->any() )
			->method( 'ldap_search' )->willReturn( true );

		$client = new Client( $this->makeClientConfig(), $mockFunctionWrapper );
		$result = $client->search( 'SomeSearch' );

		$this->assertEquals( 'MockGetEntriesResponse', $result );
	}

	protected function makeClientConfig() {
		return new HashConfig( [
			ClientConfig::SERVER => 'TestServer',
			ClientConfig::USER => 'TestUser',
			ClientConfig::PASSWORD => 'TestPassword',
			ClientConfig::PORT => 'TestPort',
			ClientConfig::BASE_DN => 'TestDN'
		] );
	}

	public function maybeDefineLDAPConstants() {
		$requiredConstants = [
			'LDAP_OPT_PROTOCOL_VERSION',
			'LDAP_OPT_REFERRALS'
		];

		foreach ( $requiredConstants as $constName ) {
			if ( !defined( $constName ) ) {
				define( $constName, 0 );
			}
		}
	}

	/**
	 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
	 */
	public function testClientFactory() {
		// @codingStandardsIgnoreStart
		global $LDAPProviderDomainConfigs;
		// @codingStandardsIgnoreEnd
		$LDAPProviderDomainConfigs = __DIR__ . '/data/testconfig.json';

		$factory = ClientFactory::getInstance();
		$this->assertinstanceof(
			'MediaWiki\Extension\LDAPProvider\ClientFactory', $factory
		);
		$client = $factory->getForDomain( "LDAP" );
		$this->assertinstanceof(
			'MediaWiki\Extension\LDAPProvider\Client', $client
		);
	}
}
