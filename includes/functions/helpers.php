<?php

// Prevent direct file access
if( ! defined("MCE4M_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
* Gets options setting from database
*
* @param string $key
* @return array
*/
function mce4m_get_options( $key = null ) {
	static $options = null;

	if ( $options === null ) {

		$defaults = array(
			'api_key' => '',
			'webhook_secret' => ''
		);

		$options = get_option( 'mce4m', false );

		// add option to database
		if ( false === $options ) {
			add_option( 'mce4m', $defaults );
		}

		$options = array_merge( $defaults, (array) $options );

	}

	if ( $key !== null ) {
		return $options[$key];
	}

	return $options;
}

/**
* Gets the plugin's static webhook secret
* @return string
*/
function mce4m_get_webhook_secret() {
	$o = mce4m_get_options();
	return $o['webhook_secret'];
}

/**
* Gets the AWS key id
* @return string
*/
function mce4m_get_aws_key_id() {
	$o = mce4m_get_options();
	return $o['aws_key_id'];
}

/**
* Gets the AWS private key
* @return string
*/
function mce4m_get_aws_private_key() {
	$o = mce4m_get_options();
	return $o['aws_private_key'];
}

/**
* Returns the MailChimp API wrapper
*
* @return MCE4M_API
*/
function mce4m_get_api() {
	global $mce4m_plugin;
	return $mce4m_plugin->get_api();
}

/**
 * Returns the E4M form.
 * @return string
 */
function mce4m_get_form( $subscribe_to_list ) {
	global $mce4m_plugin;
	return $mce4m_plugin->get_form_manager()->get_form( $subscribe_to_list );
}

?>