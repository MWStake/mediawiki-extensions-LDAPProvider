<?php

namespace MediaWiki\Extension\LDAPProvider;

class GroupList {

	/**
	 *
	 * @var array
	 */
	protected $shortNames = [];

	/**
	 *
	 * @var array
	 */
	protected $fullDNs = [];

	/**
	 * @var array
	 */
	protected $mwNames;

	/**
	 * @var array
	 */
	protected $fullMaap;

	/**
	 * @param array $fullDNs the full DNs to handle
	 */
	public function __construct( $fullDNs ) {
		$this->fullDNs = $fullDNs;
	}

	/**
	 *
	 * @return array
	 */
	public function getShortNames() {
		if ( !$this->shortNames ) {
			$this->shortNames = $this->makeShortNames();
		}
		return $this->shortNames;
	}

	/**
	 *
	 * @return array
	 */
	public function getFullDNs() {
		return $this->fullDNs;
	}

	/**
	 * Group names to be used in MediaWiki
	 *
	 * @return array
	 */
	public function getMWNames() {
		if ( !$this->mwNames ) {
			$this->mwNames = array_filter( array_map( function( $group ) {
				$map = $this->getDNMap();
				if ( isset( $map[$group] ) ) {
					return $map[$group];
				}
			}, $this->getFullDNs() ) );
		}
	}

	/**
	 * Get the list of Groups that are mananaged by LDAPGroups
	 * @param string $domain
	 * @return array
	 */
	public function getGroups() {
		if ( !$this->domain && !$this->findDomainForUser() ) {
			throw new MWException( "No Domain found" );
		}

		if ( !isset( $this->map[$this->domain] ) ) {
			$groupMap = Config::newInstance()->get( "GroupRegistry" );
			if ( !isset( $groupMap[$this->domain] ) ) {
				$this->map[$this->domain]
					= DomainConfigFactory::getInstance()->factory(
					$this->domain,
					$this->getDomainConfigSection()
				);
			} else {
				$this->map[$this->domain] = $groupMap[$this->domain];
			}
		}

		return $this->map[$this->domain];
	}

	/**
	 *
	 * @return array
	 */
	protected function makeShortNames() {
		$shortNames = [];
		foreach ( $this->fullDNs as $fullDN ) {
			$dnAttrs = explode( ',', strtolower( $fullDN ) );
			if ( isset( $dnAttrs[0] ) ) {
				$dnAttrs = explode( '=', $dnAttrs[0] );
				if ( isset( $dnAttrs[1] ) ) {
					$shortNames[] = strtolower( $dnAttrs[1] );
				}
			}
		}
		return $shortNames;
	}
}
