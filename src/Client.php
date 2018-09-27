<?php

namespace MediaWiki\Extension\LDAPProvider;

use Config;
use MediaWiki\Extension\LDAPProvider\Config as LDAPConfig;
use MediaWiki\Logger\LoggerFactory;
use MWException;
use User;

class Client {

	const VERSION = "1.0.0-alpha";

	/**
	 *
	 * @var PlatformFunctionWrapper
	 */
	protected $connection = null;

	/**
	 *
	 * @var $config
	 */
	protected $config = null;

	/**
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger = null;

	protected $cache = null;
	protected $cacheTime = null;

	/**
	 * @param Config $config for fetching
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
		if ( $this->connection === null ) {
			$this->connection = new PlatformFunctionWrapper();
		}
		$this->logger = LoggerFactory::getInstance( __CLASS__ );
	}

	/**
	 * @param string $key bit to get
	 * @return mixed
	 */
	public function getConfig( $key ) {
		return $this->config->get( $key );
	}

	/**
	 * Handle initialization or recall saved connection
	 */
	protected function init() {
		// Already initialized?
		if ( $this->connection->isConnected() ) {
			return;
		}

		$this->makeNewConnection();
		$this->setupCache();
		$this->setConnectionOptions();
		$this->maybeStartTLS();
		$this->establishBinding();
		$this->setupCache();
	}

	/**
	 * @return resource
	 */
	protected function makeNewConnection() {
		$servers = (string)( new Serverlist( $this->config ) );
		$this->connection = PlatformFunctionWrapper::getConnection( $servers );
		if ( !$this->connection ) {
			throw new Exception( "Couldn't connect with $servers" );
		}
		return $this->connection;
	}

	/**
	 * Set standard configuration options
	 */
	protected function setConnectionOptions() {
		$options = [
			"LDAP_OPT_PROTOCOL_VERSION" => 3,
			"LDAP_OPT_REFERRALS" => 0
		];

		if ( $this->config->has( ClientConfig::OPTIONS ) ) {
			$options = array_merge(
				$options, $this->config->get( ClientConfig::OPTIONS )
			);
		}
		foreach ( $options  as $key => $value ) {
			$this->logger->debug( "Setting $key to $value" );
			$ret = $this->connection->setOption( constant( $key ), $value );
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
				$ret = $this->connection->startTLS();
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

		$ret = $this->connection->bind( $username, $password );
		if ( $ret === false ) {
			$error = $this->connection->error();
			$errno = $this->connection->errno();
			throw new MWException(
				"Could not bind to LDAP: ($errno) $error"
			);
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

		$runTime = -microtime( true );

		$res = $this->connection->search( $basedn, $match, $attrs );

		if ( !$res ) {
			throw new MWException(
				"Error in LDAP search: " . $this->connection->error() );
		}

		$entry = $this->connection->getEntries( $res );

		$runTime += microtime( true );
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
				$userInfoRequest = new UserInfoRequest(
					$this, $this->config
				);
				return $userInfoRequest->getUserInfo( $username );
			}
		);
	}

	/**
	 * Gets the searchstring for a user based upon settings for the domain.
	 * Returns a full DN for a user.
	 *
	 * @param string $username
	 * @return string
	 */
	private function getSearchString( $username ) {
		$searchString = $this->config->get( ClientConfig::SEARCH_STRING );
		if ( $searchString ) {
			// This is a straight bind
			$userdn = str_replace( "USER-NAME", $username, $searchString );
		} else {
			$userdn = $this->getUserDN( $username );
		}
		wfDebugLog( "LDAPProvider", "userdn is: $userdn" );
		return $userdn;
	}

	/**
	 * Gets the DN of a user based upon settings for the domain.
	 * This function will set $this->LDAPUsername
	 *
	 * @param string $username user
	 * @param string $searchattr how to find
	 * @return string
	 */
	public function getUserDN( $username, $searchattr = '' ) {
		$this->init();
		if ( ! $searchattr ) {
			$searchattr = $this->config->get(
				ClientConfig::USER_DN_SEARCH_ATTR
			);
		}
		// we need to do a subbase search for the entry
		$filter = "(" . $searchattr . "=" . $this->connection->escape( $username ) . ")";

		// We explicitly put memberof here because it's an operational
		// attribute in some servers.
		$attributes = [ "*", "memberof" ];
		$base = $this->config->get( ClientConfig::BASE_DN );
		$entry = $this->connection->search( $base, $filter, $attributes );
		if ( $this->connection->count( $entry ) == 0 ) {
			$this->fetchedUserInfo = false;
			$this->userInfo = null;
			return '';
		}
		$this->userInfo = $this->connection->getEntries( $entry );
		$this->fetchedUserInfo = true;
		if ( isset( $this->userInfo[0][$searchattr] ) ) {
			$username = $this->userInfo[0][$searchattr][0];
			$this->LDAPUsername = $username;
		}
		$userdn = $this->userInfo[0]["dn"];
		return $userdn;
	}

	/**
	 * Method to determine whether a LDAP password is valid for a
	 * specific user on the current connection
	 *
	 * @param string $username for user
	 * @param string $password for user
	 * @return bool
	 *
	 * @fixme two binds are done here, first is as admin in init()
	 */
	public function canBindAs( $username, $password ) {
		$this->init();
		$username = $this->getSearchString( $username );
		return $this->connection->bind( $username, $password);
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
				"ldap-provider", "user-info", $username, $groupBaseDN, time()
			),
			$this->cacheTime,
			function () use ( $username ) {
				$factoryCallback = $this->config->get( 'grouprequest' );
				$request = $factoryCallback( $this, $this->config );

				if ( $request instanceof UserGroupsRequest === false ) {
					throw new MWException( "Configured GroupRequest not valid" );
				}

				return $request->getUserGroups( $username );
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
