<?php

// Prevent direct file access
if( ! defined("MCE4M_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
* Handles plugin settings, uses WP settings API: http://codex.wordpress.org/Settings_API
*/
class MCE4M_Admin {

	/**
	* Holds the values to be used in the fields callbacks
	*/
    private $options;

	/**
	* Constructor
	*/
	public function __construct() {
		error_log('MCE4M_Admin::construct()');

		error_log(print_r($_FILES,true));

		// check for file upload
		if ( isset( $_FILES['files'] ) ) {
			error_log('MCE4M_Admin::construct()  File upload detected.');
			new MCE4M_Media_Uploader();
		}

		$this->setup();

	}

	/**
	* Registers hooks for the admin
	*/
	private function setup() {

		add_action( 'admin_init', array( $this, 'initialize' ) );
		add_action( 'admin_menu', array( $this, 'setup_menus' ) );
		add_action( 'admin_notices', array( $this, 'notices') );

		// enqueue scripts for media uploader
		add_action( 'admin_enqueue_scripts', array( $this, 'enqeue_media_uploader_scripts' ) );

	}

	/**
	* Initialize
	*/
	public function initialize() {

		register_setting(
			'mce4m_settings',
			'mce4m',
			array($this, 'validate_settings')
		);

		// api key section
		add_settings_section(
			'mailchimp-api-key',
			'MailChimp API Key',
			array( $this, 'mailchimp_api_key_section_info'),
			'mailchimp-e4m-settings'
		);
		add_settings_field(
			'api_key',
			'API Key',
			array( $this, 'api_key_callback' ),
			'mailchimp-e4m-settings',
			'mailchimp-api-key'
		);

		// aws settings
		add_settings_section(
			'mce4m-aws-settings',
			'Amazon AWS Settings',
			null,
			'mailchimp-e4m-settings'
		);
		add_settings_field(
			'aws_key_id',
			'Key ID',
			array( $this, 'aws_key_id_callback' ),
			'mailchimp-e4m-settings',
			'mce4m-aws-settings'
		);
		add_settings_field(
			'aws_private_key',
			'Private Key',
			array( $this, 'aws_private_key_callback' ),
			'mailchimp-e4m-settings',
			'mce4m-aws-settings'
		);
		add_settings_field(
			'aws_bucket_name',
			'AWS Bucket',
			array( $this, 'aws_bucket_name_callback' ),
			'mailchimp-e4m-settings',
			'mce4m-aws-settings'
		);

		// media upload section
		add_settings_section(
			'mce4m-media-upload',
			'Media File 4 Exchange',
			array( $this, 'mce4m_media_upload_section_info' ),
			'mailchimp-e4m-settings'
		);
		add_settings_field(
			'media_file',
			'Choose New File',
			array( $this, 'media_file_callback' ),
			'mailchimp-e4m-settings',
			'mce4m-media-upload'
		);

		// hidden settings
		add_settings_section(
			'mce4m-hidden-settings',
			'',
			null,
			'mailchimp-e4m-settings'
		);
		add_settings_field(
			'webhook_secret',
			'',
			array( $this, 'webhook_secret_callback' ),
			'mailchimp-e4m-settings',
			'mce4m-hidden-settings'
		);

	}

	/**
	* Sets up the admin menu
	*/
	public function setup_menus() {
		add_submenu_page(
			'options-general.php',
			'MailChimp E4M Settings',
			'MailChimp E4M',
			'manage_options',
			'mailchimp-e4m-settings',
			array($this, 'show_settings')
		);
	}

	/**
	* Loads the scripts necessary for using the WP media uploader
	*    See: http://is.gd/dyVGLM
	*/
	public function enqeue_media_uploader_scripts() {

		// register the plugin's admin script
        wp_register_script(
        	'mce4m-admin',
        	MCE4M_PLUGIN_URL . 'js/mailchimp-e4m-admin.js', 
        	array('jquery','media-upload','thickbox')
        );
        wp_enqueue_script( 'mce4m-admin' );

	}

	/**
	* Validates the General settings
	*
	* @param array $settings
	* @return array
	*/
	public function validate_settings( $settings ) {

		error_log(print_r($settings,true));

		if ( isset( $settings['api_key'] ) ) {
			$settings['api_key'] = sanitize_text_field( $settings['api_key'] );
		}
		if ( isset( $settings['webhook_secret'] ) ) {
			$settings['webhook_secret'] = sanitize_text_field( $settings['webhook_secret'] );
		}
		if ( isset( $settings['aws_key_id'] ) ) {
			$settings['aws_key_id'] = sanitize_text_field( $settings['aws_key_id'] );
		}
		if ( isset( $settings['aws_private_key'] ) ) {
			$settings['aws_private_key'] = sanitize_text_field( $settings['aws_private_key'] );
		}
		if ( isset( $settings['aws_bucket_name'] ) ) {
			$settings['aws_bucket_name'] = sanitize_text_field( $settings['aws_bucket_name'] );
		}
		if ( isset( $settings['media_file'] ) ) {
			$settings['media_file'] = $settings['media_file'];
		}

		return $settings;
	}

	/**
	* Displays the settings page
	*/
	public function show_settings() {
		$this->options = mce4m_get_options();
		require_once MCE4M_PLUGIN_DIR . 'includes/views/settings.php';
	}

	/**
	* Prints info for the MailChimp API Settings section
	*/
	public function mailchimp_api_key_section_info() {
		printf( 'Get your MailChimp API key <a href="https://admin.mailchimp.com/account/api" target="_blank">here</a>' );
	}

	/**
	* Prints the input field for the api key field
	*/
    public function api_key_callback() {
        printf(
            '<input type="text" placeholder="MailChimp API Key" id="api_key" name="mce4m[api_key]" value="%s" />',
            isset( $this->options['api_key'] ) ? esc_attr( $this->options['api_key']) : ''
        );
    }

    /**
    * Prints the *hidden* input field for the webhook secret.
    * Note: this value is set when the plugin is activated.
    */
    public function webhook_secret_callback() {
    	printf(
    		'<input type="hidden" id="webhook_secret" name="mce4m[webhook_secret]" value="%s" />',
    		isset( $this->options['webhook_secret'] ) ? esc_attr( $this->options['webhook_secret']) : ''
    	);
    }

    /**
    * Prints the *hidden* input field for the AWS key id.
    * Note: this value is set when the plugin is activated.
    */
    public function aws_key_id_callback() {
    	printf(
    		'<input type="text" id="aws_key_id" name="mce4m[aws_key_id]" value="%s" />',
    		isset( $this->options['aws_key_id'] ) ? esc_attr( $this->options['aws_key_id']) : ''
    	);
    }

    /**
    * Prints the *hidden* input field for the AWS private key.
    * Note: this value is set when the plugin is activated.
    */
    public function aws_private_key_callback() {
    	printf(
    		'<input type="text" id="aws_private_key" name="mce4m[aws_private_key]" value="%s" />',
    		isset( $this->options['aws_private_key'] ) ? esc_attr( $this->options['aws_private_key']) : ''
    	);
    }

    public function aws_bucket_name_callback() {
    	printf(
    		'<input type="text" id="aws_bucket_name" name="mce4m[aws_bucket_name]" value="%s" />',
    		isset( $this->options['aws_bucket_name'] ) ? esc_attr( $this->options['aws_bucket_name']) : ''
    	);
    }

    /**
    * Prints the input field for uploading a media file
    */
    public function mce4m_media_upload_section_info() {

    	error_log( print_r($this->options,true) );
    	error_log( isset( $this->options['media_file'] ) );

    	if ( !isset( $this->options['media_file'] ) ) {
	 		printf( 'No media file has been uploaded yet.' );
	 		return;
	 	}

	 	$file = json_decode( $this->options['media_file'] );

	 	// set up table to display current media file info
	 	$c = '<table style="width:600px" class="widefat">';
	 	$c .= '<thead>';
	 	$c .= '<th class="row-title">Media File Info</th><th></th>';
	 	$c .= '</thead>';
	 	$c .= '<tbody>';
    	$c .= '<tr>';
    	$c .= '<td class="row-title"><label for="tablecell">Name</label></td>';
    	$c .= '<td>' . $file->name . '</td>';
    	$c .= '</tr>';
    	$c .= '<tr>';
    	$c .= '<td class="row-title"><label for="tablecell">Type</label></td>';
    	$c .= '<td>' . $file->mime_type . '</td>';
    	$c .= '</tr>';
    	$c .= '<tr>';
    	$c .= '<td class="row-title"><label for="tablecell">Size</label></td>';
    	$c .= '<td>' . $file->size . '</td>';
    	$c .= '</tr>';
    	$c .= '</tr>';
    	$c .= '<tr>';
    	$c .= '<td class="row-title"><label for="tablecell">Link</label></td>';
    	$c .= '<td><a href="' . esc_url( $file->url ) . '" target="_blank">Download File</a></td>';
    	$c .= '</tr>';
    	$c .= '</tbody>';
    	$c .= '</table>';

    	print( $c );
    }

    /**
    * Prints the file upload field for uploading the media file to be exchanged
    */
    public function media_file_callback() {
	    $c = '<input id="media_file_button" type="button" class="button" value="Upload" />';
    	$c .= '<input style="margin-left: 10px" type="file" id="media_file_select" /><br/>';
    	$c .= '<progress style="display:none; width: 200px"></progress>';
    	$c .= '<input type="hidden" id="media_file_input" name="mce4m[media_file]" value="%s"/>';

    	printf( $c, isset( $this->options['media_file'] ) ? esc_attr( $this->options['media_file']) : '' );
    }

    /**
    * Displays admin notices where nesessary
    */
    public function notices() {
    	$this->options = mce4m_get_options();
    	if ( empty($this->options['api_key']) ) {
    		?>
    		<div class="updated">
        		<p>
	    		<?php
		    		_e(
						'MailChimp API Key not yet set. Please go to MailChimp E4M settings.',
						'mailchimp-e4m-widget'
					);
				?>
				</p>
    		</div> <?php
    	}
    }

}

?>