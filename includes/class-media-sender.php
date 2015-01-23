<?php

// Prevent direct file access
if( ! defined("MCE4M_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MCE4M_Media_Sender {

	/**
	* @var string
	*/
	var $webhook_secret;

	/**
	* @var S3
	*/
	var $s3;

	/**
	* Holds the plugin settings
	* @var string
	*/
	private $plugin_settings;

	/**
	 * The length of time (in seconds) that the media link should be good.
	 * @var int
	 */
	var $link_lifetime;

	/** 
	* The data received from the webhook including email and list id.
	* @var array
	*/
	var $webhook_data;

	/**
	* Constructor
	*/
	public function __construct( $webhook_secret ) {

		// set some object properties
		$this->webhook_secret = mce4m_get_webhook_secret();
		$this->plugin_settings = mce4m_get_options();
		$this->link_lifetime = (60 * 60) * 24;  // 24 hours
		$this->webhook_data = $_POST['data'];

		// validate the webhook secret
		if ( !$this->secret_is_valid( $webhook_secret ) ) {
			error_log('MCE4M_Media_Sender::construct()  Webhook secret did not match.');
			return;
		}

		$this->s3 = new S3( mce4m_get_aws_key_id(), mce4m_get_aws_private_key() );

		$this->send_media_mail();

	}

	/**
	* Validates webhook secret
	* @param string $webhook_secret
	* @return boolean
	*/
	private function secret_is_valid( $webhook_secret ) {
		return $this->webhook_secret === $webhook_secret;
	}

	/**
	* Gets a signed link to the media file
	* @return string The url 
	*/
	private function get_signed_link( $lifetime ) {
		$s3 = $this->s3;

		$bucket = $this->plugin_settings['aws_bucket_name'];
		$filedata = json_decode( $this->plugin_settings['media_file'] );

		return $s3->getAuthenticatedUrl(
			$bucket,
			'mce4m/' . $filedata->name,
			$lifetime
		);
	}

	/**
	* Sends the media using Wordpress's wp_mail() function
	*   http://codex.wordpress.org/Function_Reference/wp_mail
	*/
	private function send_media_mail() {

		// webhook data
		$data = $this->webhook_data;

		// prepare email data
		$to = $data['email'];
		$subject = 'Subscription confirmed! Here is your free mp3.';
		$headers[] = 'From: OK Go <webmaster@okgo.net>';
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$message = file_get_contents( MCE4M_PLUGIN_DIR . '/includes/views/media-mail.html');
		$message = str_replace('[signed_link]', $this->get_signed_link( $this->link_lifetime ), $message);

		// send email
		wp_mail( $to, $subject, $message, $headers );
	}

}

?>