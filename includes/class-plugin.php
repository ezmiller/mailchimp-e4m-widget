<?php

// Prevent direct file access
if( ! defined("MCE4M_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MCE4M_Plugin {

	/**
	 * @var MCE4M_API
	 */
	private $api = null;

	/**
	* @var MCE4M_Form
	*/
	private $form_manager = null;

	/**
	* Constructor
	*/
	public function __construct() {

        spl_autoload_register( array( $this, 'autoload') );

        // initialize form manager
        add_action( 'init', array( $this, 'get_form_manager') );

        // widget
        add_action( 'widgets_init', array( $this, 'register_widget' ) );

	}

	/**
	* Load all necessary included libraries
	* @return bool
	*/
	private function autoload($class_name) {

		static $classes = null;

		if ($classes === null) {

			$classes = array(
				'MCE4M_API'				=>	'class-api.php',
				'MCE4M_Widget'			=>	'class-widget.php',
				'MCE4M_Form_Request'	=>  'class-form-request.php',
				'MCE4M_Form_Manager'	=>  'class-form-manager.php',
				'MCE4M_Media_Sender'	=> 	'class-media-sender.php',
				'MCE4M_Media_Uploader'	=>	'class-media-uploader.php',
				'S3'					=> 	'class-s3.php'
			);

		}

		if( isset( $classes[$class_name] ) ) {
            require_once MCE4M_PLUGIN_DIR . 'includes/' . $classes[$class_name];
            return true;
        }

        return false;

	}

	/**
	* Returns the MailChim API wrapper
	* @return MCE4M_API
	*/
	public function get_api() {

		if ( $this->api === null ) {
			$opts = mce4m_get_options();
			$this->api = new MCE4M_API( $opts['api_key'] );
		}

		return $this->api;
	}

	/**
	* Returns the form manager class
	* @return MCE4M_Form
	*/
	public function get_form_manager() {

		if ( $this->form_manager == null ) {
			$this->form_manager = new MCE4M_Form_Manager();
		}

		return $this->form_manager;
	}

	/**
	* Registers the MCE4M Wiget with Wordpress
	*/
	public function register_widget() {
		register_widget( 'MCE4M_Widget' );
	    wp_register_script(
	        'mailchimp-e4m-widget',
	        MCE4M_PLUGIN_URL . 'js/mailchimp-e4m-widget.js',
	        null,
	        null,
	        true
	    );
	    wp_localize_script( 'mailchimp-e4m-widget', 'siteUrl', get_site_url() );
	}

}

?>