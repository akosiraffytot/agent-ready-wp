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
