<?php

namespace MediaWiki\Extension\LDAPProvider;

class PlatformFunctionWrapper {

	/**
	 * Set the value of the given option
	 * @link http://php.net/manual/en/function.ldap-set-option.php
	 * @param resource $link_identifier <p>
	 * An LDAP link identifier, returned by <b>ldap_connect</b>.
	 * </p>
	 * @param int $option <p>
	 * The parameter <i>option</i> can be one of:
	 * <tr valign="top">
	 * <td>Option</td>
	 * <td>Type</td>
	 * <td>Available since</td>
	 * </tr>
	 * <tr valign="top">
	 * <td><b>LDAP_OPT_DEREF</b></td>
	 * <td>integer</td>
	 * <td></td>
	 * </tr>
	 * <tr valign="top">
	 * <td><b>LDAP_OPT_SIZELIMIT</b></td>
	 * <td>integer</td>
	 * <td></td>
	 * </tr>
	 * <tr valign="top">
	 * <td><b>LDAP_OPT_TIMELIMIT</b></td>
	 * <td>integer</td>
	 * <td></td>
	 * </tr>
	 * <tr valign="top">
	 * <td><b>LDAP_OPT_NETWORK_TIMEOUT</b></td>
	 * <td>integer</td>
	 * <td>PHP 5.3.0</td>
	 * </tr>
	 * <tr valign="top">
	 * <td><b>LDAP_OPT_PROTOCOL_VERSION</b></td>
	 * <td>integer</td>
	 * <td></td>
	 * </tr>
	 * <tr valign="top">
	 * <td><b>LDAP_OPT_ERROR_NUMBER</b></td>
	 * <td>integer</td>
	 * <td></td>
	 * </tr>
	 * <tr valign="top">
	 * <td><b>LDAP_OPT_REFERRALS</b></td>
	 * <td>bool</td>
	 * <td></td>
	 * </tr>
	 * <tr valign="top">
	 * <td><b>LDAP_OPT_RESTART</b></td>
	 * <td>bool</td>
	 * <td></td>
	 * </tr>
	 * <tr valign="top">
	 * <td><b>LDAP_OPT_HOST_NAME</b></td>
	 * <td>string</td>
	 * <td></td>
	 * </tr>
	 * <tr valign="top">
	 * <td><b>LDAP_OPT_ERROR_STRING</b></td>
	 * <td>string</td>
	 * <td></td>
	 * </tr>
	 * <tr valign="top">
	 * <td><b>LDAP_OPT_MATCHED_DN</b></td>
	 * <td>string</td>
	 * <td></td>
	 * </tr>
	 * <tr valign="top">
	 * <td><b>LDAP_OPT_SERVER_CONTROLS</b></td>
	 * <td>array</td>
	 * <td></td>
	 * </tr>
	 * <tr valign="top">
	 * <td><b>LDAP_OPT_CLIENT_CONTROLS</b></td>
	 * <td>array</td>
	 * <td></td>
	 * </tr>
	 * </p>
	 * <p>
	 * <b>LDAP_OPT_SERVER_CONTROLS</b> and
	 * <b>LDAP_OPT_CLIENT_CONTROLS</b> require a list of
	 * controls, this means that the value must be an array of controls. A
	 * control consists of an oid identifying the control,
	 * an optional value, and an optional flag for
	 * criticality. In PHP a control is given by an
	 * array containing an element with the key oid
	 * and string value, and two optional elements. The optional
	 * elements are key value with string value
	 * and key iscritical with boolean value.
	 * iscritical defaults to <b>FALSE</b>
	 * if not supplied. See draft-ietf-ldapext-ldap-c-api-xx.txt
	 * for details. See also the second example below.
	 * </p>
	 * @param mixed $newval <p>
	 * The new value for the specified <i>option</i>.
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 * @since 4.0.4
	 * @since 5.0
	 */
	public function ldap_set_option( $link_identifier, $option, $newval ) {
		return ldap_set_option( $link_identifier, $option, $newval );
	}

	/**
	 * Bind to LDAP directory
	 * @link http://php.net/manual/en/function.ldap-bind.php
	 * @param resource $link_identifier <p>
	 * An LDAP link identifier, returned by <b>ldap_connect</b>.
	 * </p>
	 * @param string $bind_rdn [optional]
	 * @param string $bind_password [optional]
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 * @since 4.0
	 * @since 5.0
	 */
	public function ldap_bind( $link_identifier, $bind_rdn, $bind_password ) {
		return ldap_bind( $link_identifier, $bind_rdn, $bind_password );
	}

	/**
	 * (PHP 4, PHP 5, PHP 7)<br/>
	 * Return the LDAP error message of the last LDAP command
	 * @link http://php.net/manual/en/function.ldap-error.php
	 * @param resource $link_identifier <p>
	 * An LDAP link identifier, returned by <b>ldap_connect</b>.
	 * </p>
	 * @return string string error message.
	 */
	public function ldap_error( $link_identifier ) {
		return ldap_error( $link_identifier );
	}

	/**
	 * Return the LDAP error number of the last LDAP command
	 * @link http://php.net/manual/en/function.ldap-errno.php
	 * @param resource $link_identifier <p>
	 * An LDAP link identifier, returned by <b>ldap_connect</b>.
	 * </p>
	 * @return int Return the LDAP error number of the last LDAP command for this
	 * link.
	 * @since 4.0
	 * @since 5.0
	 */
	public function ldap_errno( $link_identifier ) {
		return ldap_errno( $link_identifier );
	}

	/**
	 * (PHP 4 &gt;= 4.2.0, PHP 5, PHP 7)<br/>
	 * Start TLS
	 * @link http://php.net/manual/en/function.ldap-start-tls.php
	 * @param resource $link
	 * @return bool
	 */
	public function ldap_start_tls( $link ) {
		return ldap_start_tls( $link );
	}

	/**
	 * Search LDAP tree
	 * @link http://php.net/manual/en/function.ldap-search.php
	 * @param resource $link_identifier <p>
	 * An LDAP link identifier, returned by <b>ldap_connect</b>.
	 * </p>
	 * @param string $base_dn <p>
	 * The base DN for the directory.
	 * </p>
	 * @param string $filter <p>
	 * The search filter can be simple or advanced, using boolean operators in
	 * the format described in the LDAP documentation (see the Netscape Directory SDK for full
	 * information on filters).
	 * </p>
	 * @param array $attributes [optional] <p>
	 * An array of the required attributes, e.g. array("mail", "sn", "cn").
	 * Note that the "dn" is always returned irrespective of which attributes
	 * types are requested.
	 * </p>
	 * <p>
	 * Using this parameter is much more efficient than the default action
	 * (which is to return all attributes and their associated values).
	 * The use of this parameter should therefore be considered good
	 * practice.
	 * </p>
	 * @param int $attrsonly [optional] <p>
	 * Should be set to 1 if only attribute types are wanted. If set to 0
	 * both attributes types and attribute values are fetched which is the
	 * default behaviour.
	 * </p>
	 * @param int $sizelimit [optional] <p>
	 * Enables you to limit the count of entries fetched. Setting this to 0
	 * means no limit.
	 * </p>
	 * <p>
	 * This parameter can NOT override server-side preset sizelimit. You can
	 * set it lower though.
	 * </p>
	 * <p>
	 * Some directory server hosts will be configured to return no more than
	 * a preset number of entries. If this occurs, the server will indicate
	 * that it has only returned a partial results set. This also occurs if
	 * you use this parameter to limit the count of fetched entries.
	 * </p>
	 * @param int $timelimit [optional] <p>
	 * Sets the number of seconds how long is spend on the search. Setting
	 * this to 0 means no limit.
	 * </p>
	 * <p>
	 * This parameter can NOT override server-side preset timelimit. You can
	 * set it lower though.
	 * </p>
	 * @param int $deref [optional] <p>
	 * Specifies how aliases should be handled during the search. It can be
	 * one of the following:
	 * <b>LDAP_DEREF_NEVER</b> - (default) aliases are never
	 * dereferenced.
	 * @return resource a search result identifier or <b>FALSE</b> on error.
	 * @since 4.0
	 * @since 5.0
	 */
	public function ldap_search( $link_identifier, $base_dn, $filter, $attributes, $attrsonly, $sizelimit, $timelimit, $deref ) {
		return ldap_search( $link_identifier, $base_dn, $filter, $attributes, $attrsonly, $sizelimit, $timelimit, $deref );
	}

	/**
	 * Get all result entries
	 * @link http://php.net/manual/en/function.ldap-get-entries.php
	 * @param resource $link_identifier <p>
	 * An LDAP link identifier, returned by <b>ldap_connect</b>.
	 * </p>
	 * @param resource $result_identifier
	 * @return array a complete result information in a multi-dimensional array on
	 * success and <b>FALSE</b> on error.
	 * </p>
	 * <p>
	 * The structure of the array is as follows.
	 * The attribute index is converted to lowercase. (Attributes are
	 * case-insensitive for directory servers, but not when used as
	 * array indices.)
	 * <pre>
	 * return_value["count"] = number of entries in the result
	 * return_value[0] : refers to the details of first entry
	 * return_value[i]["dn"] = DN of the ith entry in the result
	 * return_value[i]["count"] = number of attributes in ith entry
	 * return_value[i][j] = NAME of the jth attribute in the ith entry in the result
	 * return_value[i]["attribute"]["count"] = number of values for
	 * attribute in ith entry
	 * return_value[i]["attribute"][j] = jth value of attribute in ith entry
	 * </pre>
	 * @since 4.0
	 * @since 5.0
	 */
	public function ldap_get_entries( $link_identifier, $result_identifier ) {
		return ldap_get_entries( $link_identifier, $result_identifier );
	}

	/**
	 * (PHP 4, PHP 5, PHP 7)<br/>
	 * Connect to an LDAP server
	 * @link http://php.net/manual/en/function.ldap-connect.php
	 * @param string $host [optional] <p>
	 * This field supports using a hostname or, with OpenLDAP 2.x.x and
	 * later, a full LDAP URI of the form ldap://hostname:port
	 * or ldaps://hostname:port for SSL encryption.
	 * </p>
	 * <p>
	 * You can also provide multiple LDAP-URIs separated by a space as one string
	 * </p>
	 * <p>
	 * Note that hostname:port is not a supported LDAP URI as the schema is missing.
	 * </p>
	 * @param int $port [optional] <p>
	 * The port to connect to. Not used when using LDAP URIs.
	 * </p>
	 * @return resource a positive LDAP link identifier when the provided hostname/port combination or LDAP URI
	 * seems plausible. It's a syntactic check of the provided parameters but the server(s) will not
	 * be contacted! If the syntactic check fails it returns <b>FALSE</b>.
	 * When OpenLDAP 2.x.x is used, <b>ldap_connect</b> will always
	 * return a resource as it does not actually connect but just
	 * initializes the connecting parameters. The actual connect happens with
	 * the next calls to ldap_* funcs, usually with
	 * <b>ldap_bind</b>.
	 * </p>
	 * <p>
	 * If no arguments are specified then the link identifier of the already
	 * opened link will be returned.
	 */
	public function ldap_connect( $host, $port ) {
		return ldap_connect( $host, $port );
	}
}
