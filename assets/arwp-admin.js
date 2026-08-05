/**
 * Agent Ready WP — admin toggle handling.
 */
function syncArwpSubmenu( input ) {
	var slug = input.getAttribute( 'data-settings-slug' );

	if ( ! slug ) {
		return;
	}

	var link = document.querySelector( '#adminmenu .wp-submenu a[href$="page=' + slug + '"]' );

	if ( link && link.closest( 'li' ) ) {
		link.closest( 'li' ).style.display = input.checked ? '' : 'none';
	}
}

function syncArwpCard( input ) {
	var card = input.closest( '.arwp-card' );
	var settingsLink = card ? card.querySelector( '.arwp-card-settings' ) : null;

	if ( settingsLink ) {
		settingsLink.style.display = input.checked ? '' : 'none';
	}
}

document.querySelectorAll( '.arwp-toggle' ).forEach( function ( input ) {
	input.addEventListener( 'change', function () {
		var self = this;

		self.disabled = true;

		var data = new FormData();
		data.append( 'action', 'arwp_toggle_module' );
		data.append( 'module', self.getAttribute( 'data-module' ) );
		data.append( 'enabled', self.checked ? '1' : '0' );
		data.append( 'nonce', ArwpAdmin.nonce );

		fetch( ArwpAdmin.ajaxUrl, {
			method: 'POST',
			body: data,
			credentials: 'same-origin'
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( result ) {
				if ( result.success ) {
					syncArwpCard( self );
					syncArwpSubmenu( self );
				} else {
					self.checked = ! self.checked;
				}
				self.disabled = false;
			} )
			.catch( function () {
				self.checked = ! self.checked;
				self.disabled = false;
			} );
	} );

	syncArwpCard( input );
	syncArwpSubmenu( input );
} );
