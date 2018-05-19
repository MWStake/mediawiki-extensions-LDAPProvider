<?php

namespace MediaWiki\Extension\LDAPProvider;

class ClientConfig {
	const DOMAINCONFIG_SECTION = 'connection';
	const SERVER = 'server';
	const USER = 'user';
	const PASSWORD = 'pass';
	const BASE_DN = 'basedn';
	const GROUP_BASE_DN = 'groupbasedn';
	const USER_BASE_DN = 'userbasedn';
	const SEARCH_STRING = 'searchstring';
	const OPTIONS = 'options';
	const PORT = 'port';
	const ENC_TYPE = 'enctype';
	const USER_DN_SEARCH_ATTR = 'searchattribute';
	const USERINFO_USERNAME_ATTR = 'usernameattribute';
	const USERINFO_REALNAME_ATTR = 'realnameattribute';
	const USERINFO_EMAIL_ATTR = 'emailattribute';
}
