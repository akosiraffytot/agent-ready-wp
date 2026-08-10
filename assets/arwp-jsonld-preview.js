/**
 * Agent Ready WP — live JSON-LD preview on the JSON-LD settings page.
 */
( function () {
	var form = document.querySelector( 'form[action="options.php"]' );
	var output = document.getElementById( 'arwp-jsonld-output' );
	var copyButton = document.getElementById( 'arwp-copy-jsonld' );
	var validateLink = document.getElementById( 'arwp-validate-jsonld' );
	var currentJson = '';

	if ( ! form || ! output ) {
		return;
	}

	var preview = output.closest( '.arwp-jsonld-preview' );

	/**
	 * Split a JSON string into syntax tokens (keys, strings, booleans, numbers).
	 */
	function tokenize( json ) {
		var tokens = [];
		var re = /("(?:\\.|[^"\\])*")(\s*:)?|\b(true|false|null)\b|(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)|([^])/g;
		var match;

		while ( ( match = re.exec( json ) ) !== null ) {
			if ( match[1] !== undefined ) {
				tokens.push( { type: match[2] ? 'key' : 'string', text: match[0] } );
			} else if ( match[3] !== undefined ) {
				tokens.push( { type: 'bool', text: match[0] } );
			} else if ( match[4] !== undefined ) {
				tokens.push( { type: 'number', text: match[0] } );
			} else {
				tokens.push( { type: 'plain', text: match[0] } );
			}
		}

		return tokens;
	}

	function render( json ) {
		try {
			JSON.parse( json );
		} catch ( e ) {
			output.textContent = json;
			return;
		}

		var fragment = document.createDocumentFragment();

		tokenize( json ).forEach( function ( token ) {
			var span = document.createElement( 'span' );
			span.className = 'arwp-json-' + token.type;
			span.textContent = token.text;
			fragment.appendChild( span );
		} );

		output.textContent = '';
		output.appendChild( fragment );
	}

	function setLoading( loading ) {
		if ( preview ) {
			preview.classList.toggle( 'is-loading', loading );
		}
	}

	function setButtonsEnabled( enabled ) {
		if ( copyButton ) {
			copyButton.disabled = ! enabled;
		}

		if ( validateLink ) {
			validateLink.classList.toggle( 'button-disabled', ! enabled );
			validateLink.setAttribute( 'aria-disabled', enabled ? 'false' : 'true' );
		}
	}

	function updateValidateLink() {
		if ( validateLink && currentJson ) {
			var url = 'https://validator.schema.org/?code=' + encodeURIComponent( currentJson );
			validateLink.href = url.length > 14000
				? 'https://validator.schema.org/#url=' + encodeURIComponent( ArwpPreview.pageUrl )
				: url;
		}
	}

	function refresh() {
		var data = new FormData( form );
		data.append( 'action', 'arwp_preview_jsonld' );
		data.append( 'nonce', ArwpPreview.nonce );

		setLoading( true );
		setButtonsEnabled( false );

		fetch( ArwpPreview.ajaxUrl, {
			method: 'POST',
			body: data,
			credentials: 'same-origin'
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( result ) {
				if ( result.success && result.data && result.data.schema ) {
					currentJson = JSON.stringify( result.data.schema, null, 2 );
					render( currentJson );
					updateValidateLink();
					setButtonsEnabled( true );
				}
				setLoading( false );
			} )
			.catch( function () {
				setLoading( false );
			} );
	}

	var debounceTimer = null;

	function debouncedRefresh() {
		clearTimeout( debounceTimer );
		debounceTimer = setTimeout( refresh, 400 );
	}

	form.addEventListener( 'input', debouncedRefresh );
	form.addEventListener( 'change', debouncedRefresh );

	refresh();

	if ( copyButton ) {
		var copyLabel = copyButton.getAttribute( 'data-copy' ) || 'Copy';
		var copiedLabel = copyButton.getAttribute( 'data-copied' ) || 'Copied';

		copyButton.addEventListener( 'click', function () {
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( currentJson ).then( function () {
					copyButton.textContent = copiedLabel;
					setTimeout( function () {
						copyButton.textContent = copyLabel;
					}, 1500 );
				} );
				return;
			}

			var textarea = document.createElement( 'textarea' );
			textarea.value = currentJson;
			textarea.style.position = 'fixed';
			textarea.style.opacity = '0';
			document.body.appendChild( textarea );
			textarea.select();
			document.execCommand( 'copy' );
			document.body.removeChild( textarea );

			copyButton.textContent = copiedLabel;
			setTimeout( function () {
				copyButton.textContent = copyLabel;
			}, 1500 );
		} );
	}
} )();
