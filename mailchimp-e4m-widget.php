<?php
/*
Plugin Name: MailChimp E4M Wdiget
Plugin URI: https://mc4wp.com/
Description: Email 4 Media widget using MailChimp
Version: 0.1
Author: Ethan Miller
Author URI: http://code-cuts.com
License: GPL v3

MailChimp for WordPress
Copyright (C) 2012-2013, Danny van Kooten, hi@dannyvankooten.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/* ******************************************************************* /
// Development Notes
/* ******************************************************************* /
* 12/15-2014 - Rough draft finished (v.01)
*	Still to be done
*	+ Ensure that the plugin deletes old files when new file uploaded
*   + Make sure that admin panel works when, on new install, user
*	  adds all new data, at once. I.e. file upload may not work if
*     all AWS info not yet saved before file upload attempted.
*   + Set it up so it works with cloudfront?
*   + Add instructions about how to create the AWS Policy for S3
/* ******************************************************************* */


// Prevent direct file access
if( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

function mce4m_activate() {

	// save a secret key for use later to secure mailchimp webhook
	add_option( 'mce4m', array(
		'webhook_secret' => md5( time() ),
	) );

}
register_activation_hook( __FILE__, 'mce4m_activate' );

function mce4m_deactivate() {

	// remove options
	delete_option( 'mce4m' );

}
register_deactivation_hook( __FILE__, 'mce4m_deactivate' );

/**
* Loads the MailChimp E4M files
*
* @return boolean True if the plugin files were loaded, otherwise false.
*/
function mce4m_initialize() {

	// Bootstrap the plugin
	define( 'MCE4M_VERSION', '0.1' );
	define( 'MCE4M_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'MCE4M_PLUGIN_URL', plugins_url( '/' , __FILE__ ) );
	define( 'MCE4M_PLUGIN_FILE', __FILE__ );

	require_once MCE4M_PLUGIN_DIR . 'includes/functions/helpers.php';
	require_once MCE4M_PLUGIN_DIR . 'includes/class-plugin.php';
	$GLOBALS['mce4m_plugin'] = new MCE4M_Plugin();

	if ( is_admin() ) {

		// Admin setup
		require_once MCE4M_PLUGIN_DIR . 'includes/class-admin.php';
		new MCE4M_Admin();

	}

}
add_action( 'plugins_loaded', 'mce4m_initialize', 20 );

?>