<?php

namespace MediaWiki\Extension\LDAPProvider;

class Setup {
	public static function onRegistration() {
		$GLOBALS['LDAPProviderClientRegistry'] = [
			'*' => function() {
				$configFactory = \MediaWiki\MediaWikiServices::getInstance()->getConfigFactory();
				$extensionConfig = $configFactory->makeConfig( 'ldapprovider' );
				$mainClientConfig = new INIClientConfig(
					$extensionConfig->get( Config::MAIN_CLIENT_INI_FILE )
				);

				return new Client( $mainClientConfig );
			}
		];
	}
}

