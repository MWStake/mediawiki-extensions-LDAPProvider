<?php

namespace MediaWiki\Extensions\LDAPProvider;

use Config;
use MediaWiki\Extensions\LDAPProvider\Config as LDAPConfig;
use MediaWiki\Logger\LoggerFactory;
use MWException;
use RequestContext;
use User;

class Client {

	const VERSION = "1.0.0-alpha";

	/**
	 *
	 * @var resource
	 */
	protected $connection = null;

	/**
	 *
	 * @var $config
	 */
	protected $config = null;

	/**
	 *
	 * @var PlatformFunctionWrapper
	 */
	protected $functionWrapper = null;

	/**
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger = null;

	protected $cache = null;
	protected $cacheTime = null;

	/**
	 * @param Config $config for fetching
	 * @param PlatformFunctionWrapper $functionWrapper optinal wrapper
	 */
	public function __construct( Config $config, $functionWrapper = null ) {
		$this->config = $config;
		$this->functionWrapper = $functionWrapper;
		if ( $this->functionWrapper === null ) {
			$this->functionWrapper = new PlatformFunctionWrapper();
		}
		$this->logger = LoggerFactory::getInstance( __CLASS__ );
	}

	/**
	 * Handle initialization or recall saved connection
	 */
	protected function init() {
		// Already initialized?
		if ( $this->connection !== null ) {
			return;
		}

		$this->initConnection();
		$this->setConnectionOptions();
		$this->maybeStartTLS();
		$this->establishBinding();
		$this->setupCache();
	}

	/**
	 * Set up connection from a new connection
	 */
	protected function initConnection() {
		$this->connection = $this->makeNewConnection();
	}

	/**
	 * @param bool $setOptions Set connection options after setting up
	 * connection or no.
	 * @return resource
	 */
	protected function makeNewConnection( $setOptions = false ) {
		\MediaWiki\suppressWarnings();
		$servers = (string)( new Serverlist( $this->config ) );
		$this->logger->debug( "Connecting to '$servers'" );
		$ret = $this->functionWrapper->ldap_connect( $servers );
		\MediaWiki\restoreWarnings();

		if ( $setOptions && $ret ) {
			$this->setConnectionOptions( $ret );
		}
		return $ret;
	}

	/**
	 * Set standard configuration options
	 *
	 * @param null|resource $conn alternative to $this->connection
	 */
	protected function setConnectionOptions( $conn = null ) {
		$options = [
			"LDAP_OPT_PROTOCOL_VERSION" => 3,
			"LDAP_OPT_REFERRALS" => 0
		];

		if ( !$conn ) {
			$conn = $this->connection;
		}

		if ( $this->config->has( ClientConfig::OPTIONS ) ) {
			$options = array_merge(
				$options, $this->config->get( ClientConfig::OPTIONS )
			);
		}
		foreach ( $options  as $key => $value ) {
			$ret = $this->functionWrapper->ldap_set_option(
				$conn, constant( $key ), $value
			);
			if ( $ret === false ) {
				$message = 'Cannot set option to LDAP connection!';
				$this->logger->debug( $message, [ $key, $value ] );
			}
		}
	}

	/**
	 * Start encrypted connection if so configured
	 */
	protected function maybeStartTLS() {
		if ( $this->config->has( ClientConfig::ENC_TYPE ) ) {
			$encType = $this->config->get( ClientConfig::ENC_TYPE );
			if ( $encType === EncType::TLS ) {
				$ret = $this->functionWrapper->ldap_start_tls(
					$this->connection
				);
				if ( $ret === false ) {
					throw new MWException( 'Could not start TLS!' );
				}
			}
		}
	}

	/**
	 * Make sure we can bind properly
	 */
	protected function establishBinding() {
		$this->init();
		$username = null;
		if ( $this->config->has( ClientConfig::USER ) ) {
			$username = $this->config->get( ClientConfig::USER );
		}
		$password = null;
		if ( $this->config->has( ClientConfig::PASSWORD ) ) {
			$password = $this->config->get( ClientConfig::PASSWORD );
		}

		$ret = $this->functionWrapper->ldap_bind(
			$this->connection, $username, $password
		);
		if ( $ret === false ) {
			$error = $this->functionWrapper->ldap_error( $this->connection );
			$errno = $this->functionWrapper->ldap_errno( $this->connection );
			throw new MWException( "Could not bind to LDAP: ($errno) $error" );
		}
		$this->isBound = true;
	}

	/**
	 * Use whatever sort of cache we've configured
	 */
	protected function setupCache() {
		if ( !$this->cache ) {
			$conf = new LDAPConfig();
			$cacheType = $conf->get( LDAPConfig::CACHE_TYPE );
			if ( defined( $cacheType ) ) {
				$cacheType = constant( $cacheType );
			}
			$this->cache = wfGetCache( $cacheType );
			$this->cacheTime = $conf->get( LDAPConfig::CACHE_TIME );
		}
	}

	/**
	 * Perform an LDAP search
	 * @param string $match desired in ldap search format
	 * @param string $basedn The base DN to search in
	 * @param array $attrs list of attributes to get, default to '*'
	 * @return array
	 */
	public function search( $match, $basedn = null, $attrs = [ "*" ] ) {
		$this->init();
		if ( $basedn === null ) {
			$basedn = $this->config->get( ClientConfig::BASE_DN );
		}

		wfProfileIn( __METHOD__ );
		$runTime = -microtime( true );

		$res = $this->functionWrapper->ldap_search(
			$this->connection,
			$basedn,
			$match,
			$attrs
		);

		if ( !$res ) {
			wfProfileOut( __METHOD__ );
			throw new MWException(
				"Error in LDAP search: "
				. $this->functionWrapper->ldap_error( $this->connection )
			);
		}

		$entry = $this->functionWrapper->ldap_get_entries(
			$this->connection, $res
		);

		$runTime += microtime( true );
		wfProfileOut( __METHOD__ );
		$this->logger->debug( "Ran LDAP search for '$match' in "
							  . "$runTime seconds.\n" );

		return $entry;
	}

	/**
	 * Get the user information
	 *
	 * @param string $username for user
	 * @param string $userBaseDN from configuration
	 * @return array
	 */
	public function getUserInfo( $username, $userBaseDN = '' ) {
		$this->init();
		return $this->cache->getWithSetCallback(
			$this->cache->makeKey(
				"ldap-provider", "user-info", $username, $userBaseDN
			),
			$this->cacheTime,
			function () use ( $username ) {
				$userInfoRequest = new UserInfoRequest( $this, $this->config );
				return $userInfoRequest->getUserInfo( $username );
			}
		);
	}

	/**
	 * Method to determine whether a LDAP password is valid for a specific user
	 * on the current connection
	 *
	 * @param string $username for user
	 * @param string $password for user
	 * @return boolan
	 */
	public function canBindAs( $username, $password ) {
		$this->init();
		$conn = $this->makeNewConnection( true );
		if ( $conn ) {
			return $this->functionWrapper->ldap_bind(
				$conn, $username, $password
			);
		}
		return false;
	}

	/**
	 * @param string $username for user
	 * @param string $groupBaseDN for group
	 * @return GroupList
	 */
	public function getUserGroups( $username, $groupBaseDN = '' ) {
		$this->init();
		return $this->cache->getWithSetCallback(
			$this->cache->makeKey(
				"ldap-provider", "user-info", $username, $groupBaseDN
			),
			$this->cacheTime,
			function () use ( $username ) {
				$userGroupsRequest = new UserGroupsRequest( $this, $this->config );
				return $userGroupsRequest->getUserGroups( $username );
			}
		);
	}

	/**
	 * @return string
	 */
	public static function getVersion() {
		return self::VERSION;
	}
}
