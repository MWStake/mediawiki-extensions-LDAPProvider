<?php

namespace MediaWiki\Extension\LDAPProvider\Tests;

use \MediaWiki\Extension\LDAPProvider\INIClientConfig;

class INIClientConfigTest extends \MediaWikiTestCase {

	public function testChangeIniFile() {
		$config = new INIClientConfig( __DIR__ . '/data/test.ini' );

		$this->assertEquals( 'PHPUNIT_SERVER', $config->get( INIClientConfig::SERVER ) );
	}
}
