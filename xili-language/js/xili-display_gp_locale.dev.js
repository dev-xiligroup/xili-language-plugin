// script for xili-display_gp_locale
// since 2.22.6
//
(function($) {
	$(function() {

		// Setup a click handler to initiate the Ajax request and handle the response
		$('#language_name_list').change(function(evt) {
			$( '#gplocale-info' ).fadeOut();
			// Use jQuery's post method to send the request to the server. The 'ajaxurl' is a URL
			// provided by WordPress' Ajax API.
			$.post(ajaxurl, {

				action:	'display_gp_locale', // The function located in plugin.php for handling the request
				_ajax_nonce: $('#display-gp-locale-nonce').val(),// The security nonce
				wp_locale_slug: $('#language_name_list').val(),						// The slug of the locale with which we're working
				locale_names: $('#language_name_list option:selected').text()

			} ).always( function() {
				//spinner.hide();
			}).done( function( x ) {
				if ( ! x.success ) {
					$( '#gplocale-info' ).text( attachMediaBoxL10n.error );
				}
				$( '#gplocale-info' ).fadeIn();
				$( '#gplocale-info' ).html( x.data );
				$( '#language_alias' ).val( $('#iso_639_1').text() );//here because done

			}).fail( function() {
				$( '#gplocale-info' ).text( attachMediaBoxL10n.error );
			});


		});

	});
})(jQuery);