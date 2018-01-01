<?php

namespace MediaWiki\Extensions\LDAPProvider;

class ClientConfig {
	const DOMAINCONFIG_SECTION = 'connection';
	const SERVER = 'server';
	const USER = 'user';
	const PASSWORD = 'pass';
	const BASE_DN = 'basedn';
	const GROUP_BASE_DN = 'groupbasedn';
	const USER_BASE_DN = 'userbasedn';
	const USER_DN_SEARCH_ATTR = 'userdnsearchattribute';
	const OPTIONS = 'options';
	const PORT = 'port';
	const ENC_TYPE = 'enctype';
}
