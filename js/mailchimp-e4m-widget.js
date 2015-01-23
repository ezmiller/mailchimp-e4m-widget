$(document).ready(function() {

	$('.mce4m-form').submit(function(e) {
		e.preventDefault();

		var postData = $(this).serializeArray();

		// make ajax call to php file that handles submit
		$.ajax({
			url: siteUrl,
			type: "POST",
			data: postData,
			success: function(resp) {

				var $mBox,
					$msg,
					$form = $('#mce4m-form-'+postData[3].value),
					$container = $form.parent();

				// decode response
				resp = $.parseJSON(resp);

				// append msg box
				$container.append('<div id="mce4m-message-box"></div>');
				$mBox = $container.find('#mce4m-message-box');

				// add msg area to msg box
				$mBox.html('<div id="mce4m-message"></div>');
				$msg = $mBox.find('#mce4m-message');

				// determine msg to give
				if ( resp === true ) {
					$msg.html('<span>Success!</span><br/><span>Please check your email for a confirmation link.</span>');
				} else if ( resp === 'already_subscribed' ) {
					$msg.html('<span>Sorry!</span><br/><span>This email has already been subscribed to our list.</span>');
				} else {
					$msg.html('<span>Oops!</span><br/><span>There seems to have been a problem submitting your email.  Please try again.</span>');
				}

				// handle diplay/hiding of msg
				$mBox.fadeIn('fast', function () {
					$msg.vAlign();
				});

				// hide msg & remove
				$mBox.delay(2000).hide('fast', function() {
					$mBox.remove();
				});

				// reset the form
				$form[0].reset();	// reset the form
			},
			error: function(xhr, ajaxOptions, thrownError) {
				console.log('error');

				var $mBox,
					$msg,
					$form = $('#mce4m-form-'+postData[3].value),
					$container = $form.parent();

				// append msg box
				$container.append('<div id="mce4m-message-box"></div>');
				$mBox = $container.find('#mce4m-message-box');

				// add msg to msg box
               	$mBox.html('<div id="mce4m-message"><span>Oops!<span><br/><span>There seems to have been a problem submitting your email.  Please try again.</span></div>');
               	$msg = $mBox.find('mce4m-message')

				// fade in
				$mBox.fadeIn('fast', function () {
					$msg.vAlign();
				});

				// hide msg & remove
				$mBox.delay(2000).hide('fast', function() {
					$mBox.remove();
				});

				$form[0].reset();  // reset the form
        	}
		});

	});
});