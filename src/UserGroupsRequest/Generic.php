<?php

namespace MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

use MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

class Generic extends UserGroupsRequest {

	/**
	 * @param string $username to get the groups for
	 * @return GroupList
	 */
	function getUserGroups( $username ) {
		throw new \Exception("blah");
	}
}
