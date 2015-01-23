<?php

// Prevent direct file access
if( ! defined("MCE4M_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MCE4M_Widget extends WP_Widget {

    /**
	* Holds the MailChimp API wrapper.
	*/
    private $api;

    /**
	* Holds the secret key (nonce) used to verify the webhook.
	*/
    private $webhook_secret;

    /**
	 * Register widget with WordPress.
	 */
	function __construct() {

		parent::__construct(
			'MCE4M_Widget', // Base ID
			__( 'MailChimp E4M Widget', 'mailchimp-e4m-widget' ), // Name
			array( 'description' => __( 'Displays a MailChimp form that trades an email for a media file', 'mailchimp-e4m-widget' ), ) // Args
		);

		// load MailChimp API
		$this->api = mce4m_get_api();

		// retrieve webhook secret
		$this->webhook_secret = mce4m_get_webhook_secret();
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array   $args     Widget arguments.
	 * @param array   $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		$title = !empty($instance['title']) ? $instance['title'] : '';
		$subtitle = !empty($instance['subtitle']) ? $instance['subtitle'] : '';
		$list = !empty($instance['list']) ? $instance['list'] : '';

		// Enqueue the scripts to process the form
		wp_enqueue_script('mailchimp-e4m-widget');

		// make sure template function exits for generating form
		if ( ! function_exists( 'mce4m_get_form' ) ) {
			error_log('including helpers from widget');
			include_once MCE4M_PLUGIN_DIR . 'includes/functions/helpers.php';
		}

		$c = '';
		if ( !empty($title) && !empty($subtitle) ) {
			$c = '<h2 class="box-header">' . $title . '<br/>' . $subtitle . '</h2>';
		}
		if ( !empty($list) ) {
			$c .= mce4m_get_form( $list );
		}

		echo $c;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array   $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$lists = $this->api->get_lists();

		 // Set up some default widget settings.
        $defaults = array(
        	'title' => 'Join Our Mailing List',
        	'subtitle' => ' '
        );
        $instance = wp_parse_args( (array) $instance, $defaults );


		// MailChimp List Dropdown
		$c = '<p>';
		$c .= '<label for="' . $this->get_field_id( 'list' ) . '">';
		$c .= __( 'Subscribe Users To:', 'mailchimp-e4m-widget' ) . '</label>';
		$c .= '<select class="widefat" id="' . $this->get_field_id('list') . '"';
		$c .= 'name="' . $this->get_field_name('list') . '">';
		$c .= '<option value="">Choose a List</option>';
		foreach ($lists as $l) {
			$c .= '<option value="' . $l->id . '"';
			if ( !empty($instance) && $l->id === $instance['list'] ) {
                $c .= ' selected';
            }
			$c .= '>' . $l->name . '</option>';
		}
		$c .= '</select>';
		$c .= '</p>';

		// Widget Title/Header
		$c .= '<p>';
		$c .= '<label for="' . $this->get_field_id( 'title' ) . '">';
		$c .= __( 'Title:', 'mailchimp-e4m-widget' ) . '</label>';
		$c .= '<input class="widefat" id="' . $this->get_field_id('title') . '"';
		$c .= 'name="' . $this->get_field_name('title') . '" type="text"';
		$c .= ' value="' . $instance['title'] . '">';
		$c .= '</p>';

		// Widget Subtitle/Subheader
		$c .= '<p>';
		$c .= '<label for="' . $this->get_field_id( 'subtitle' ) . '">';
		$c .= __( 'Subtitle:', 'mailchimp-e4m-widget' ) . '</label>';
		$c .= '<input class="widefat" id="' . $this->get_field_id('subtitle') . '"';
		$c .= 'name="' . $this->get_field_name('subtitle') . '" type="text"';
		$c .= ' value="' . $instance['subtitle'] . '">';
		$c .= '</p>';

		echo $c;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array   $new_instance Values just sent to be saved.
	 * @param array   $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$this->unset_webhook( $old_instance['list'] );

		// update values
		$instance['list'] = ( ! empty( $new_instance['list'] ) ) ? sanitize_text_field( $new_instance['list'] ) : '';
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['subtitle'] = ( ! empty( $new_instance['subtitle'] ) ) ? sanitize_text_field( $new_instance['subtitle'] ) : '';

		$this->set_webhook( $instance['list'] );

		return $instance;
	}

	/**
	* Sets a webhook for the specified MailChimp list
	* @param string $list_id  The id of the MailChimp list
	* @return boolean  True|false based if webhook was sucessfully set.
	*/
	private function set_webhook( $list_id ) {
		error_log('MCE4M_Widget::set_webhook()  Called.');

		// the webhook url
		$url = get_site_url() . '/?_mce4m_webhook_secret=' . $this->webhook_secret;

		// check to see if webhook is already set
		$webhooks = $this->api->get_list_webhooks( $list_id );
		foreach ( $webhooks as $hook ) {
			if ( $hook->url === $url ) {
				return true;
			}
		}

		// if webhook not found, create it
		$success = $this->api->set_list_webhook( $list_id, $url, array(
			'subscribe' => true,
			'unsubscribe' => false,
			'profile' => false,
			'cleaned' => false,
			'upemail' => false,
			'campaign' => false
		 ) );

		return $success;

	}

	/**
	* Unsets a webhook for the specified MailChimp list
	* @param string $list_id The id of the MailChimp list
	* @return boolean True|false based if webhook was sucessfully unset.
	*/
	private function unset_webhook( $list_id ) {
		error_log('MCE4M_Widget::unset_webhook()  Called.');

		// the webhook url to unset
		$url = get_site_url() . '/?_mce4m_webhook_secret=' . $this->webhook_secret;

		// try to find the corresponding webhook
		$webhooks = $this->api->get_list_webhooks( $list_id );
		$found = null;
		foreach ( $webhooks as $hook ) {
			if ( $hook->url === $url ) {
				$found = $hook;
			}
		}

		// unset the webhook if found
		$success = false;
		if ( $found !== false ) {
			$success = $this->api->unset_list_webhook( $list_id, $found->url );
		}

		return $success;

	}



}

?>