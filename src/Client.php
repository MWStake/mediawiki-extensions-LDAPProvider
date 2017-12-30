<?php

namespace MediaWiki\Extension\LDAPProvider;

use MediaWiki\Logger\LoggerFactory;

class Client {

	const VERSION = "1.0.0-alpha";

	/**
	 *
	 * @var resource
	 */
	protected $connection = null;

	/**
	 *
	 * @var \$config
	 */
	protected $config = null;

	/**
	 *
	 * @var PlatformFunctionWrapper
	 */
	protected $fw = null;

	/**
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger = null;

	/**
	 *
	 * @param \Config $config
	 * @param PlatformFunctionWrapper $fw
	 */
	public function __construct( $config, $fw = null ) {
		$this->config = $config;
		$this->fw = $fw;
		if ( $this->fw === null ) {
			$this->fw = new PlatformFunctionWrapper();
		}
		$this->logger = LoggerFactory::getInstance( __CLASS__ );
	}

	protected function init() {
		// Already initialized?
		if ( $this->connection !== null ) {
			return;
		}

		$this->initConnection();
		$this->setConnectionOptions();
		$this->maybeStartTLS();
		$this->establishBinding();
	}

	protected function initConnection() {
		$this->connection = $this->makeNewConnection();
	}

	protected function setConnectionOptions() {
		$this->fw->ldap_set_option( $this->connection, LDAP_OPT_PROTOCOL_VERSION, 3 );
		$this->fw->ldap_set_option( $this->connection, LDAP_OPT_REFERRALS, 0 );

		if ( $this->config->has( ClientConfig::OPTIONS ) ) {
			$options = $this->config->get( ClientConfig::OPTIONS );
			foreach ( $options  as $key => $value ) {
				$ret = $this->fw->ldap_set_option( $this->connection, constant( $key ), $value );
				if ( $ret === false ) {
					$message = 'Can\'t set option to LDAP connection!';
					$this->logger->debug( $message, [ $key, $value ] );
				}
			}
		}
	}

	protected function establishBinding() {
		$username = null;
		if ( $this->config->has( ClientConfig::USER ) ) {
			$username = $this->config->get( ClientConfig::USER );
		}
		$password = null;
		if ( $this->config->has( ClientConfig::PASSWORD ) ) {
			$password = $this->config->get( ClientConfig::PASSWORD );
		}

		$ret = $this->fw->ldap_bind( $this->connection, $password, $username );
		if ( $ret === false ) {
			$error = $this->fw->ldap_error( $this->connection );
			$errno = $this->fw->ldap_errno( $this->connection );
			throw new \MWException( "Could not bind to LDAP: ($errno) $error" );
		}
	}

	protected function maybeStartTLS() {
		if ( $this->config->has( ClientConfig::ENC_TYPE ) ) {
			$encType = $this->config->get( ClientConfig::ENC_TYPE );
			if ( $encType === EncType::TLS ) {
				$ret = $this->fw->ldap_start_tls( $this->connection );
				if ( $ret === false ) {
					throw new \MWException( 'Could not start TLS!' );
				}
			}
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

		$res = $this->fw->ldap_search(
			$this->connection,
			$basedn,
			$match,
			$attrs
		);

		if ( !$res ) {
			wfProfileOut( __METHOD__ );
			throw new \MWException(
				"Error in LDAP search: " . $this->fw->ldap_error( $this->connection )
			);
		}

		$entry = $this->fw->ldap_get_entries( $this->connection, $res );

		$runTime += microtime( true );
		wfProfileOut( __METHOD__ );
		$this->logger->debug( "Ran LDAP search for '$match' in $runTime seconds.\n" );

		return $entry;
	}

	protected $userInfos = [];

	/**
	 *
	 * @param string $username
	 * @param string $userBaseDN
	 * @return array
	 */
	public function getUserInfo( $username, $userBaseDN = '' ) {
		$cacheKey = $username.$userBaseDN;
		if ( isset( $this->userInfos[$cacheKey] ) ) {
			$this->userInfos[$cacheKey];
		}

		$userInfoRequest = new UserInfoRequest( $this, $this->config );
		$this->userInfos[$cacheKey] = $userInfoRequest->getUserInfo( $username );

		return $this->userInfos[$cacheKey];
	}

	/**
	 * Method to determine whether a LDAP password is valid for a specific user
	 * on the current connection
	 * @param string $username
	 * @param string $password
	 * @return boolan
	 */
	public function canBindAs( $username, $password ) {
		return $this->fw->ldap_bind( $this->makeNewConnection(), $password, $username );
	}

	/**
	 *
	 * @return resource
	 */
	protected function makeNewConnection() {
		\MediaWiki\suppressWarnings();
		$servers = (string)( new Serverlist( $this->config ) );
		$this->logger->debug( "Connecting to '$servers'" );
		$ret = $this->fw->ldap_connect( $servers );
		\MediaWiki\restoreWarnings();
		return $ret;
	}

	protected $userGroupLists = [];

	/**
	 *
	 * @param string $username
	 * @param string $groupBaseDN
	 * @return GroupList
	 */
	public function getUserGroups( $username, $groupBaseDN = '' ) {
		$cacheKey = $username.$groupBaseDN;
		if ( isset( $this->userInfos[$cacheKey] ) ) {
			$this->userInfos[$cacheKey];
		}

		$userInfoRequest = new UserGroupsRequest( $this, $this->config );
		$this->userInfos[$cacheKey] = $userInfoRequest->getUserGroups( $username );

		return $this->userInfos[$cacheKey];
	}

	/**
	 * @return string
	 */
	public static function getVersion() {
		return self::VERSION;
	}
}
