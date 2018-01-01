<?php

namespace MediaWiki\Extensions\LDAPProvider\Tests;

use HashConfig;
use MediaWiki\Extensions\LDAPProvider\ClientConfig;
use MediaWiki\Extensions\LDAPProvider\EncType;
use MediaWiki\Extensions\LDAPProvider\Serverlist;
use PHPUnit_Framework_TestCase;

class ServerlistTest extends PHPUnit_Framework_TestCase {

	/**
	 *
	 * @param \Config $config
	 * @param string $expected
	 *
	 * @dataProvider provideConfigs
	 */
	public function testToString( $config, $expected ) {
		$config = new HashConfig( $config );
		$serverlist = new Serverlist( $config );

		$this->assertEquals( $expected, (string)$serverlist );
	}

	public function provideConfigs() {
		return [
			'only-one-server' => [
				[
					ClientConfig::SERVER => 'ldap.company.tld'
				],
				'ldap://ldap.company.tld:389'
			],
			'one-server-and-ssl' => [
				[
					ClientConfig::SERVER => 'ldap.company.tld',
					ClientConfig::ENC_TYPE => EncType::SSL
				],
				'ldaps://ldap.company.tld:636'
			],
			'two-servers-and-ssl' => [
				[
					ClientConfig::SERVER
					=> 'ldap1.company.tld ldap2.company.tld',
					ClientConfig::ENC_TYPE => EncType::SSL
				],
				'ldaps://ldap1.company.tld:636 ldaps://ldap2.company.tld:636'
			],
			'two-servers-and-ldapi' => [
				[
					ClientConfig::SERVER
					=> 'ldap1.company.tld ldap2.company.tld',
					ClientConfig::ENC_TYPE => EncType::LDAPI
				],
				'ldapi://ldap1.company.tld:389 ldapi://ldap2.company.tld:389'
			],
			'one-server-and-ssl-with-non-standard-port' => [
				[
					ClientConfig::SERVER => 'ldap.company.tld',
					ClientConfig::ENC_TYPE => EncType::SSL,
					ClientConfig::PORT => '12345'
				],
				'ldaps://ldap.company.tld:12345'
			],
		];
	}
}
