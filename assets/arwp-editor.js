(function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var checkbox = document.getElementById( 'arwp-service-enabled' );

		if ( ! checkbox ) {
			return;
		}

		var fields = document.querySelector( '.arwp-service-fields' );

		if ( ! fields ) {
			return;
		}

		function sync() {
			fields.style.display = checkbox.checked ? '' : 'none';
		}

		checkbox.addEventListener( 'change', sync );
		sync();
	} );
})();

/**
 * Post meta box image media-library picker. Bound on load so wp.media
 * (enqueued later in the footer) is defined by the time the handler attaches.
 */
window.addEventListener( 'load', function () {
	var input = document.getElementById( 'arwp-schema-post-image' );
	var button = document.getElementById( 'arwp-post-image-upload' );

	if ( ! input || ! button || ! window.wp || ! wp.media ) {
		return;
	}

	button.addEventListener( 'click', function ( e ) {
		e.preventDefault();

		var frame = wp.media( {
			title: 'Select Image',
			multiple: false,
			library: { type: 'image' }
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			input.value = attachment.url;
		} );

		frame.open();
	} );
} );
