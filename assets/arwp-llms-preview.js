/**
 * Agent Ready WP — live /llms.txt preview on the LLMS.TXT settings page.
 */
( function () {
	var form = document.querySelector( 'form[action="options.php"]' );
	var output = document.getElementById( 'arwp-llms-output' );
	var copyButton = document.getElementById( 'arwp-copy-llms' );
	var currentContent = '';
	var controller = null;

	if ( ! form || ! output ) {
		return;
	}

	function setCopyEnabled( enabled ) {
		if ( copyButton ) {
			copyButton.disabled = ! enabled;
		}
	}

	function refresh() {
		var data = new FormData( form );
		data.append( 'action', 'arwp_preview_llms' );
		data.append( 'nonce', ArwpLlmsPreview.nonce );

		if ( controller ) {
			controller.abort();
		}

		controller = new AbortController();

		fetch( ArwpLlmsPreview.ajaxUrl, {
			method: 'POST',
			body: data,
			credentials: 'same-origin',
			signal: controller.signal
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( result ) {
				if ( result.success && result.data && typeof result.data.content === 'string' ) {
					currentContent = result.data.content;
					output.textContent = currentContent;
					setCopyEnabled( true );
				} else {
					setCopyEnabled( false );
				}
			} )
			.catch( function () {
				// Aborted requests and network errors leave the last good preview in place.
			} );
	}

	var debounceTimer = null;

	function debouncedRefresh() {
		clearTimeout( debounceTimer );
		debounceTimer = setTimeout( refresh, 300 );
	}

	form.addEventListener( 'input', debouncedRefresh );
	form.addEventListener( 'change', debouncedRefresh );

	refresh();

	if ( copyButton ) {
		var copyLabel = copyButton.getAttribute( 'data-copy' ) || 'Copy';
		var copiedLabel = copyButton.getAttribute( 'data-copied' ) || 'Copied';

		copyButton.addEventListener( 'click', function () {
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( currentContent ).then( function () {
					copyButton.textContent = copiedLabel;
					setTimeout( function () {
						copyButton.textContent = copyLabel;
					}, 1500 );
				} );
				return;
			}

			var textarea = document.createElement( 'textarea' );
			textarea.value = currentContent;
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
