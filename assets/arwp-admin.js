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

/**
 * Show/hide conditional settings sections based on the selected Organization
 * type's data-groups attribute (e.g. the Local Business section only when a
 * local_business subtype is selected).
 */
function syncArwpConditionalSections() {
	var select = document.getElementById( 'arwp-schema-org-type' );

	if ( ! select ) {
		return;
	}

	var selected = select.options[ select.selectedIndex ];
	var groups = selected && selected.getAttribute( 'data-groups' ) ? selected.getAttribute( 'data-groups' ).split( ' ' ) : [];

	document.querySelectorAll( '.arwp-conditional' ).forEach( function ( section ) {
		var sectionGroups = section.getAttribute( 'data-groups' ).split( ' ' );
		var visible = sectionGroups.some( function ( group ) {
			return groups.indexOf( group ) !== -1;
		} );
		section.style.display = visible ? '' : 'none';
	} );
}

var arwpOrgType = document.getElementById( 'arwp-schema-org-type' );

if ( arwpOrgType ) {
	arwpOrgType.addEventListener( 'change', syncArwpConditionalSections );
	syncArwpConditionalSections();
}

/**
 * Organization logo media-library picker. Bound on load so wp.media (enqueued
 * later in the footer) is defined by the time the handler attaches.
 */
window.addEventListener( 'load', function () {
	var logoInput = document.getElementById( 'arwp-schema-org-logo' );
	var logoButton = document.getElementById( 'arwp-logo-upload' );

	if ( ! logoInput || ! logoButton || ! window.wp || ! wp.media ) {
		return;
	}

	logoButton.addEventListener( 'click', function ( e ) {
		e.preventDefault();

		var frame = wp.media( {
			title: 'Select Logo Image',
			multiple: false,
			library: { type: 'image' }
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			logoInput.value = attachment.url;
		} );

		frame.open();
	} );
} );
