/**
 * Agent Ready WP — admin bar "Validate Schema": opens validator.schema.org
 * prefilled with the rendered @graph, mirroring the settings page preview.
 */
( function () {
	var node = document.getElementById( 'wp-admin-bar-arwp-validate-schema' );
	var link = node && node.querySelector( 'a' );

	if ( ! link ) {
		return;
	}

	var scripts = document.querySelectorAll( 'script[type="application/ld+json"]' );
	var schema = null;

	for ( var i = 0; i < scripts.length; i++ ) {
		try {
			var parsed = JSON.parse( scripts[ i ].textContent );

			if ( parsed && parsed[ '@graph' ] ) {
				schema = parsed;
				break;
			}
		} catch ( e ) {
			continue;
		}
	}

	if ( ! schema ) {
		if ( node.parentNode ) {
			node.parentNode.removeChild( node );
		}
		return;
	}

	var json = JSON.stringify( schema, null, 2 );

	link.addEventListener( 'click', function ( e ) {
		e.preventDefault();
		window.open( 'https://validator.schema.org/?code=' + encodeURIComponent( json ), '_blank' );
	} );
} )();
