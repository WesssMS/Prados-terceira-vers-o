/**
 * Tribe QR Generate
 *
 * @since 4.7.5
 * @type {{}}
 */
var tribe_ticket_plus_qr = tribe_ticket_plus_qr || {}; // eslint-disable-line
( function( $, obj ) {
	/**
	 * Selectors used for configuration and setup
	 *
	 * @since 5.6.2
	 * @type {Object}
	 */
	obj.selectors = {
		apiKey: '.tec-tickets__admin-etp-app-settings-generate-api-key',
		apiKeyMsg: '.tribe-generate-qr-api-key-msg',
		apiKeyInput: '[name="tickets-plus-qr-options-api-key"]',
		qrCodeImage: '#connection_qr_code',
		refreshWarning: '#tec-tickets__admin-etp-app-settings-confirmation-text',
	};

	obj.init = function() {
		obj.init_generate();
	};

	/**
	 * Initialize QR Generate
	 *
	 * @since 4.7.5
	 * @since 5.6.2 Introduced QR code image connection settings.
	 */
	obj.init_generate = function() {
		obj.$generate_key = $( obj.selectors.apiKey );

		this.$generate_key.on( 'click', function( e ) {
			e.preventDefault();
			const confirmed = confirm( $( obj.selectors.refreshWarning ).text() );
			if ( ! confirmed ) {
				return;
			}
			obj.qr_ajax();
		} );
	};

	/**
	 * AJAX to Generate and Save QR Key
	 *
	 * @since 4.7.5
	 */
	obj.qr_ajax = function() {
		obj.$generate_key_msg = $( obj.selectors.apiKeyMsg );
		obj.$generate_key_input = $( obj.selectors.apiKeyInput );
		obj.$qr_code_img = $( obj.selectors.qrCodeImage );

		const request = {
			action: 'tribe_tickets_plus_generate_api_key',
			confirm: tribe_qr.generate_qr_nonce,
		};

		obj.$generate_key.prop( 'disabled', true );
		obj.$qr_code_img.css( 'opacity', 0.1 );
		obj.$generate_key_input.val( '--------' );

		// Send our request
		$.post(
			ajaxurl,
			request,
			function( results ) {
				if ( results.success ) {
					obj.$generate_key_msg.html( '<p class="optin-success">' + results.data.msg + '</p>' );
					obj.$generate_key_input.val( results.data.key );
					obj.$qr_code_img.attr( 'src', results.data.qr_src );
				} else {
					obj.$generate_key_msg.html( '<p class="optin-fail">' + results.data + '</p>' );
				}
				obj.$generate_key.prop( 'disabled', false );
				obj.$qr_code_img.css( 'opacity', 1 );
			},
		);
	};

	$( obj.init );
} )( jQuery, tribe_ticket_plus_qr );
