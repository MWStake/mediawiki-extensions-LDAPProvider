<?php

namespace MediaWiki\Extension\LDAPProvider\Tests;

use MediaWiki\MediaWikiServices;
use \MediaWiki\Extension\LDAPProvider\Config;

class ConfigTest extends \MediaWikiTestCase {

	public function testChangeIniFile() {
		$this->setMwGlobals(
			[ 'LDAPProviderINIFile' => __DIR__ . '/data/test.ini' ]
		);

		$config = new Config();

		$this->assertEquals( 'PHPUNIT_SERVER', $config->get( Config::SERVER ) );
	}

	public function testOverrideByGlobalVars() {
		$this->setMwGlobals(
			[ 'LDAPProvider'.Config::USER => 'THIS_TEST_USER' ]
		);

		$configFactory = \MediaWiki\MediaWikiServices::getInstance()->getConfigFactory();
		$config = $configFactory->makeConfig( 'ldapprovider' );

		$this->assertEquals( 'THIS_TEST_USER', $config->get( Config::USER ) );
		$this->assertEquals( 'password', $config->get( Config::PASSWORD ) );
	}
}
