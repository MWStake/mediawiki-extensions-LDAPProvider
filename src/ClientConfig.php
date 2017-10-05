<?php

namespace MediaWiki\Extension\LDAPProvider;

class ClientConfig extends \HashConfig {
	const SERVER = 'server';
	const USER = 'user';
	const PASSWORD = 'pass';
	const BASE_DN = 'basedn';
	const OPTIONS = 'options';
	const PORT = 'port';
	const ENC_TYPE = 'enctype';
}
