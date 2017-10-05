<?php

namespace MediaWiki\Extension\LDAPProvider;

class INIClientConfig extends ClientConfig {
	public function __construct( $iniFilePath ) {
		$iniConfig = parse_ini_file( $iniFilePath );
		parent::__construct( $iniConfig );
	}
}