<?php

namespace MediaWiki\Extension\LDAPProvider\Tests;

use MediaWiki\Extension\LDAPProvider\UserDomainStore;

/**
 * @group Database
 */
class UserDomainStoreTest extends \MediaWikiTestCase {
	protected function setUp() {
		$this->tablesUsed[] = 'ldap_domains';
		parent::setUp();

		$this->db->insert( 'ldap_domains', [
			'domain' => 'SOMEDOMAIN',
			'user_id' => self::getTestSysop()->getUser()->getId()
		] );
	}

	public function testGetDomainForUser() {
		$store = new UserDomainStore(
			\MediaWiki\MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
		$domain = $store->getDomainForUser( self::getTestSysop()->getUser() );

		$this->assertEquals( 'SOMEDOMAIN', $domain, 'Should deliver the domain' );
	}

	public function testSetDomainForUser() {
		$store = new UserDomainStore(
			\MediaWiki\MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
		$store->setDomainForUser( self::getTestUser()->getUser() , 'ANOTHERDOMAIN' );
		$this->assertSelect(
			'ldap_domains',
			[ 'domain' ],
			[ 'user_id' => self::getTestUser()->getUser()->getId() ],
			[
				[ 'ANOTHERDOMAIN' ]
			],
			'Should have saved domain to database'
		);
	}
}