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
	 * @param array $fullDNs the full DNs to handle
	 */
	public function __construct( $fullDNs ) {
		$this->fullDNs = $fullDNs;
		$this->shortNames = $this->makeShortNames();
	}

	/**
	 *
	 * @return array
	 */
	public function getShortNames() {
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
