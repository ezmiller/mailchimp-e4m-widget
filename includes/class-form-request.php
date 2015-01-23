<?php

if( ! defined("MCE4M_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MCE4M_Form_Request {

	/**
	 * @var int
	 */
	var $form_instance;

	/**
	 * @var array
	 */
	var $posted_data = array();

	/**
	 * @var string
	 */
	private $error_code = 'error';

	/**
	 * @var bool
	 */
	private $success = false;

	/**
	 * @var string
	 */
	private $subscribe_to_list = null;

	/**
	 * @var MCE4M_API
	 */
    private $api;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get the MailChimp API
		$this->api = mce4m_get_api();

		// Store form instance of submitted form
		$this->form_instance = absint( $_POST['_mce4m_form_instance'] );

		// Store list to subscribe to
		$this->subscribe_to_list = $_POST['_mce4m_subscribe_to_list'];

		// call submit in init action
		add_action( 'init', array( $this, 'submit' ), 2 );

	}

	/**
	 * Submits form data to MailChimp
	 */
	public function submit() {

		// Detect caching plugin
		$using_caching = ( defined( 'WP_CACHE' ) && WP_CACHE );

		// Validate form nonce
		if ( ! $using_caching && ( ! isset( $_POST['_mce4m_form_nonce'] ) || ! wp_verify_nonce( $_POST['_mce4m_form_nonce'], '_mce4m_form_nonce' ) ) ) {
			$this->error_code = 'invalid_nonce';
			return false;
		}

		// Ensure honeypot was not filed, see http://devgrow.com/simple-php-honey-pot/
		if ( isset( $_POST['_mce4m_required_but_not_really'] ) && ! empty( $_POST['_mce4m_required_but_not_really'] ) ) {
			$this->error_code = 'spam';
			return false;
		}

		// Get & sanitize form data
		$this->sanitize_form_data();
		$data = $this->get_posted_data();

		// Validate the email
		if ( !isset($data['EMAIL']) || !is_string($data['EMAIL']) || !is_email($data['EMAIL']) ) {
			$this->error_code = 'invalid_email';
			return false;
		}

		// Subscribe the email to the list
		$this->success = $this->subscribe( $data['EMAIL'] );

		exit( json_encode( $this->success ) );
	}

	/**
	 * Subscribe the email to the list
	 * @return boolean|string True if subscribe succeeded, 
	 *     'already_subscribed' if email already in list, False if other error.
	 */
	private function subscribe( $email ) {

		// set the list to scribe email to
		$list = $this->subscribe_to_list;

		// attempt to subscribe the email
		$success = $this->api->subscribe( $list, $email );

		// determine what to return
		if ( $success === true ) {
			return true;
		}
		else if ( $success === 'already_subscribed' ) {
			return $success;
		}
		else {
			return false;
		}
	}

	/**
	 * Sanitize posted data
	 */
	private function sanitize_form_data() {

		$data = array();

		// fields (if any) to ignore
		$ignored_fields = array();

		foreach( $_POST as $key => $value ) {

			// Sanitize key
			$key = trim( strtoupper( $key ) );

			// Skip field if it starts with _ or if it's in ignored_fields array
			if( $key[0] === '_' || in_array( $key, $ignored_fields ) ) {
				continue;
			}

			// Sanitize value if it's scalar
			$value = ( is_scalar( $value ) ) ? sanitize_text_field( $value ) : $value;

			// Add value to array
			$data[ $key ] = $value;
		}

		// strip slashes on everything
		$data = stripslashes_deep( $data );

		// store data somewhere safe
		$this->posted_data = $data;

	}

	/**
	* Returns posted data
	* @return array
	*/
	public function get_posted_data() {
		return $this->posted_data;
	}

}

?>