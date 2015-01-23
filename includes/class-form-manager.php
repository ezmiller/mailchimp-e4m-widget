<?php

if( ! defined("MCE4M_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
* This class takes care of all form related functionality
*/
class MCE4M_Form_Manager {

	/**
	* @var int
	*/
	var $instance = 1;

	/**
	* @var MCE4M_Form_Request|boolean
	*/
	var $form_request = false;

	/**
	 * Constructor
	 */
	public function __construct() {

		// check to see if a form submit has occurred
		if ( isset( $_POST['_mce4m_form_submit'] ) ) {
			$this->form_request = new MCE4M_Form_Request();
		}

		// check for webhook call
		if ( isset($_GET['_mce4m_webhook_secret']) ) {
			$this->media_sender = new MCE4M_Media_Sender( $_GET['_mce4m_webhook_secret'] );
		}

	}

	/**
	* Returns the form markup
	* @return string
	*/
	public function get_form( $subscribe_to_list ) {

		$c  = '<form method="post" id="mce4m-form-' . $this->get_form_instance() . '" class="mce4m-form"><div>';
		$c .= '<input id="email" type="email" name="email" placeholder="email address"/>';
		$c .= '<input type="hidden" name="_mce4m_subscribe_to_list" value="' . $subscribe_to_list . '"/>';
		$c .= '<input type="hidden" name="_mce4m_form_submit" value="1"/>';
		$c .= '<input type="hidden" name="_mce4m_form_instance" value="' . $this->get_form_instance() . '"/>';
		$c .= '<input type="hidden" name="_mce4m_form_nonce" value="' . wp_create_nonce( '_mce4m_form_nonce' ) . '"/>';
		$c .= '<textarea name="_mce4m_required_but_not_really" style="display: none !important;"></textarea>';
		$c .= '<button id="e4m-submit" type="submit" tabindex="2" class="button-link">Submit</button>';
		$c .= '</div></form>';
		$c .= '</form>';

		// increase the instance number to handle cases in which there is more than one widget on a page
		$this->instance++;

		return $c;
	}

	/**
	* Returns the current number of form instances
	* @return int
	*/
	private function get_form_instance() {
		return $this->instance;
	}

}

?>