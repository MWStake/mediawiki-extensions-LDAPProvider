<?php

namespace MediaWiki\Extension\LDAPProvider;

use Wikimedia\Rdbms\LoadBalancer;
use User;

class UserDomainStore {

	/**
	 *
	 * @var LoadBalancer
	 */
	protected $loadbalancer = null;

	/**
	 * @param LoadBalancer $loadbalancer to use
	 */
	public function __construct( LoadBalancer $loadbalancer ) {
		$this->loadbalancer = $loadbalancer;
	}

	/**
	 * @param User $user to get domain for
	 * @return string|null
	 */
	public function getDomainForUser( User $user ) {
		$userId = $user->getId();
		if ( $userId != 0 ) {
			$dbr = $this->loadbalancer->getConnection( DB_REPLICA );
			$row = $dbr->selectRow(
				'ldap_domains',
				[ 'domain' ],
				[ 'user_id' => $userId ],
				__METHOD__ );

			if ( $row ) {
				return $row->domain;
			}
		}

		return null;
	}

	/**
	 * @param string $user to set
	 * @param string $domain to set user to
	 * @return bool
	 */
	public function setDomainForUser( $user, $domain ) {
		$userId = $user->getId();
		if ( $userId != 0 ) {
			$dbw = $this->loadbalancer->getConnection( DB_MASTER );
			$olddomain = $this->getDomainForUser( $user );
			if ( $olddomain ) {
				return $dbw->update(
					'ldap_domains',
					[ 'domain' => $domain ],
					[ 'user_id' => $userId ],
					__METHOD__
				);
			} else {
				return $dbw->insert(
					'ldap_domains',
					[
						'domain' => $domain,
						'user_id' => $userId
					],
					__METHOD__
				);
			}
		}
		return false;
	}
}
