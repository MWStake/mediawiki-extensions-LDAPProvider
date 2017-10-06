<?php

namespace MediaWiki\Extension\LDAPProvider;

class UserDomainStore {

	/**
	 *
	 * @var \Wikimedia\Rdbms\LoadBalancer
	 */
	protected $loadbalancer = null;

	/**
	 *
	 * @param \Wikimedia\Rdbms\LoadBalancer $loadbalancer
	 */
	public function __construct( $loadbalancer ) {
		$this->loadbalancer = $loadbalancer;
	}

	/**
	 *
	 * @param \User $user
	 * @return string|null
	 */
	public function getDomainForUser( $user ) {
		$user_id = $user->getId();
		if ( $user_id != 0 ) {
			$dbr = $this->loadbalancer->getConnection( DB_REPLICA );
			$row = $dbr->selectRow(
				'ldap_domains',
				[ 'domain' ],
				[ 'user_id' => $user_id ],
				__METHOD__ );

			if ( $row ) {
				return $row->domain;
			}
		}

		return null;
	}

	/**
	 *
	 * @param type $user
	 * @param type $domain
	 * return boolean
	 */
	public function setDomainForUser( $user, $domain ) {
		$user_id = $user->getId();
		if ( $user_id != 0 ) {
			$dbw = $this->loadbalancer->getConnection( DB_MASTER );
			$olddomain = $this->getDomainForUser( $user );
			if ( $olddomain ) {
				return $dbw->update(
					'ldap_domains',
					[ 'domain' => $domain ],
					[ 'user_id' => $user_id ],
					__METHOD__
				);
			} else {
				return $dbw->insert(
					'ldap_domains',
					[
						'domain' => $domain,
						'user_id' => $user_id
					],
					__METHOD__
				);
			}
		}
		return false;
	}
}