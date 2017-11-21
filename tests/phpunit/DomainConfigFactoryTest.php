<?php

namespace MediaWiki\Extension\LDAPProvider\Tests;

use MediaWiki\Extension\LDAPProvider\DomainConfigFactory;
use MediaWiki\Extension\LDAPProvider\ClientConfig;

class DomainConfigFactoryTest extends \MediaWikiTestCase {

	public function testDefaultConfig() {
		$dcf = $this->makeDomainConfigFactory();
		$config = $dcf->factory( 'LDAP', 'connection' );

		$this->assertEquals( 'clear', $config->get( ClientConfig::ENC_TYPE ) );
		$this->assertEquals( 'someDN', $config->get( ClientConfig::USER_BASE_DN ) );
	}

	public function testArbitrarySection() {
		$dcf = $this->makeDomainConfigFactory();
		$config = $dcf->factory( 'LDAP', 'some-arbitrary-section' );

		$this->assertEquals( 42, $config->get( 'conf1' ) );
	}

	/**
	 * @expectedException \ConfigException
	 */
	public function testExceptionOnMissingDefault() {
		$dcf = $this->makeDomainConfigFactory();
		$config = $dcf->factory( 'LDAP', 'some-arbitrary-section' );
		$configWithNoDefault = $config->get( 'conf2' );
	}

	/**
	 *
	 * @return DomainConfigFactory
	 */
	protected function makeDomainConfigFactory() {
		return new DomainConfigFactory( __DIR__ . '/data/testconfig.json' );
	}

}