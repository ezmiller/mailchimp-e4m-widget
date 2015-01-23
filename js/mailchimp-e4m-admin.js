(function($) {
	// wrap code in closure so we can use $

	$(document).ready(function() {

		$('#media_file_button').on('click', function(e) {
	        e.preventDefault();

	        var file = document.getElementById('media_file_select').files[0];
	        var formData = false;
	        var reader = new FileReader();

	        if ( window.FormData ) {
	        	formdata = new FormData();
	        }

	        // create file reader event to submit file when loaded
	        reader.onloadend = function() {
	        	if ( formdata ) {
	        		formdata.append('files[]', file);
	        	}
	        	// submitFile( file, reader.result );
	        	submitFile( formdata );
	        }

	        if ( file ) {
	        	reader.readAsDataURL( file );
	        }

	    });

	    var submitFile = function ( formdata ) {

	    	$('progress').show();

	    	$.ajax({
	    		url: '', // submitting with current url = mce4m settings page
				type: "POST",
				data: formdata,
				cache: false,
				processData: false,
				contentType: false,
				xhr: function() {
					var myXhr = $.ajaxSettings.xhr();
					if ( myXhr.upload ) {
						myXhr.upload.addEventListener('progress', progressHandler, false);
					}
					return myXhr;
				},
				success: function(resp) {

					// decode response
					resp = $.parseJSON( resp );

					if ( resp == false ) {
						errorMessage();
						return;
					}

					$('#media_file_input').val( JSON.stringify(resp) );

					$('#submit').trigger('click');

				},
				error: function(xhr, ajaxOptions, thrownError) {
					errorMessage();
				}
	    	});
	    }

	    var progressHandler = function(e) {
	    	console.log('progress...');
	    	if ( e.lengthComputable ) {
	    		$('progress').attr({value:e.loaded, max:e.total});
	    	}
	    }

	    var errorMessage = function() {
	    	alert('There was an error uploading the file. Please contact your site administrator.');
	    }


	});

})(jQuery);