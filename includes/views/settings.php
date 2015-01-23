<?php

// Prevent direct file access
if( ! defined("MCE4M_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

?>
<div id="mce4m" class="wrap mce4m-settings">
	<h2>MailChimp E4M Settings</h2>
	<div id="mce4m-content">
		<form action="options.php" method="post">
			<?php settings_fields( 'mce4m_settings' ); ?>
			<?php do_settings_sections( 'mailchimp-e4m-settings' ); ?>
			<?php do_settings_sections( 'mce4m-aws-settings' ); ?>
			<?php do_settings_sections( 'mce4m-media-upload' ); ?>
			<?php do_settings_sections( 'mce4m-hidden-settings' ); ?>
			<?php submit_button() ?>
		</form>
	</div>
</div>