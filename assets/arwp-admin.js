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
 * Show/hide conditional settings sections based on the UNION of every selected
 * Organization type's data-groups (e.g. Location + E-Commerce sections when
 * ClothingStore + LocalBusiness are selected).
 */
function syncArwpConditionalSections() {
	var tokens = document.querySelectorAll( '#arwp-org-type-tokens input[name="arwp_schema_org_type[]"]' );
	var groups = [];

	tokens.forEach( function ( input ) {
		var inputGroups = input.getAttribute( 'data-groups' );

		if ( ! inputGroups ) {
			return;
		}

		inputGroups.split( ' ' ).forEach( function ( group ) {
			if ( groups.indexOf( group ) === -1 ) {
				groups.push( group );
			}
		} );
	} );

	document.querySelectorAll( '.arwp-conditional' ).forEach( function ( section ) {
		var sectionGroups = section.getAttribute( 'data-groups' ).split( ' ' );
		var visible = sectionGroups.some( function ( group ) {
			return groups.indexOf( group ) !== -1;
		} );
		section.style.display = visible ? '' : 'none';
	} );
}

/**
 * Organization type token picker. The server-rendered hidden select
 * (#arwp-org-type-source) is the data source; picked types render as removable
 * chips with a hidden input per value so options.php receives an array.
 */
( function () {
	var picker = document.getElementById( 'arwp-org-type-picker' );

	if ( ! picker ) {
		return;
	}

	var tokensEl = document.getElementById( 'arwp-org-type-tokens' );
	var search = document.getElementById( 'arwp-org-type-search' );
	var listEl = document.getElementById( 'arwp-org-type-list' );
	var source = document.getElementById( 'arwp-org-type-source' );
	var noMatch = search.getAttribute( 'data-no-match' ) || 'No matching types';
	var selected = {};

	function triggerPreviewUpdate() {
		var form = tokensEl.closest( 'form' );

		if ( form && typeof form.dispatchEvent === 'function' ) {
			form.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		}
	}

	function removeToken( span, value ) {
		span.remove();
		delete selected[ value ];
		search.value = '';
		buildList( '' );
		syncArwpConditionalSections();
		triggerPreviewUpdate();
	}

	tokensEl.querySelectorAll( '.arwp-org-type-token' ).forEach( function ( span ) {
		var input = span.querySelector( 'input[name="arwp_schema_org_type[]"]' );
		var remove = span.querySelector( '.arwp-org-type-remove' );

		if ( ! input ) {
			return;
		}

		selected[ input.value ] = true;

		if ( remove ) {
			remove.addEventListener( 'click', function () {
				removeToken( span, input.value );
			} );
		}
	} );

	function addToken( value, label, groups ) {
		if ( selected[ value ] ) {
			return;
		}

		selected[ value ] = true;

		var span = document.createElement( 'span' );
		span.className = 'arwp-org-type-token';

		var text = document.createElement( 'span' );
		text.textContent = label;
		span.appendChild( text );

		var input = document.createElement( 'input' );
		input.type = 'hidden';
		input.name = 'arwp_schema_org_type[]';
		input.value = value;
		input.setAttribute( 'data-groups', groups );
		span.appendChild( input );

		var remove = document.createElement( 'button' );
		remove.type = 'button';
		remove.className = 'arwp-org-type-remove';
		remove.setAttribute( 'aria-label', 'Remove ' + label );
		remove.innerHTML = '&times;';
		remove.addEventListener( 'click', function () {
			removeToken( span, value );
		} );
		span.appendChild( remove );

		tokensEl.appendChild( span );
		search.value = '';
		buildList( '' );
		syncArwpConditionalSections();
		triggerPreviewUpdate();
	}

	function buildList( filter ) {
		listEl.innerHTML = '';
		filter = filter.trim();

		Array.prototype.forEach.call( source.querySelectorAll( 'optgroup' ), function ( group ) {
			var matches = [];

			Array.prototype.forEach.call( group.querySelectorAll( 'option' ), function ( opt ) {
				var text = opt.textContent;
				var match = ! filter || text.toLowerCase().indexOf( filter.toLowerCase() ) !== -1;

				if ( match && ! selected[ opt.value ] ) {
					matches.push( opt );
				}
			} );

			if ( ! matches.length ) {
				return;
			}

			var header = document.createElement( 'div' );
			header.className = 'arwp-org-type-group-label';
			header.textContent = group.getAttribute( 'label' );
			listEl.appendChild( header );

			matches.forEach( function ( opt ) {
				var button = document.createElement( 'button' );
				button.type = 'button';
				button.className = 'arwp-org-type-option';
				button.textContent = opt.textContent;
				button.dataset.value = opt.value;
				button.dataset.groups = opt.getAttribute( 'data-groups' ) || '';
				button.addEventListener( 'click', function () {
					addToken( button.dataset.value, button.textContent, button.dataset.groups );
				} );
				listEl.appendChild( button );
			} );
		} );

		if ( ! listEl.children.length ) {
			var empty = document.createElement( 'div' );
			empty.className = 'arwp-org-type-empty';
			empty.textContent = noMatch;
			listEl.appendChild( empty );
		}

		listEl.hidden = false;
	}

	search.addEventListener( 'input', function () {
		buildList( search.value );
	} );

	search.addEventListener( 'focus', function () {
		buildList( search.value );
	} );

	search.addEventListener( 'keydown', function ( event ) {
		if ( 'Enter' === event.key ) {
			var first = listEl.querySelector( '.arwp-org-type-option' );

			if ( first ) {
				first.click();
				event.preventDefault();
			}
		} else if ( 'Backspace' === event.key && ! search.value ) {
			var tokens = tokensEl.querySelectorAll( '.arwp-org-type-token' );

			if ( tokens.length ) {
				tokens[ tokens.length - 1 ].querySelector( '.arwp-org-type-remove' ).click();
			}
		} else if ( 'Escape' === event.key ) {
			listEl.hidden = true;
		}
	} );

	document.addEventListener( 'click', function ( event ) {
		if ( ! picker.contains( event.target ) ) {
			listEl.hidden = true;
		}
	} );

	syncArwpConditionalSections();
} )();

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
