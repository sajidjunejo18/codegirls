/**
 * CodeGirls theme scripts. Dependency-free.
 */
( function () {
	'use strict';

	// File input label (hire form).
	var file = document.querySelector( '.cg-form__file' );
	if ( file ) {
		file.addEventListener( 'change', function () {
			var out = document.querySelector( '.cg-form__file-name' );
			out.textContent = file.files.length ? file.files[ 0 ].name : 'No File Chosen';
		} );
	}

	// "Copy bank details" buttons.
	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '[data-copy]' );
		if ( ! btn || ! navigator.clipboard ) {
			return;
		}
		var src = btn.closest( 'article' ).querySelector( btn.getAttribute( 'data-copy' ) );
		var label = btn.textContent;
		navigator.clipboard.writeText( src.innerText.replace( /
{2,}/g, '
' ) ).then( function () {
			btn.textContent = 'Copied!';
			setTimeout( function () { btn.textContent = label; }, 1800 );
		} );
	} );

	// Mobile menu toggle.
	var header = document.getElementById( 'cg-header' );
	if ( ! header ) {
		return;
	}
	var toggle = header.querySelector( '.cg-header__toggle' );

	function setOpen( open ) {
		header.classList.toggle( 'is-open', open );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	}

	toggle.addEventListener( 'click', function () {
		setOpen( ! header.classList.contains( 'is-open' ) );
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key && header.classList.contains( 'is-open' ) ) {
			setOpen( false );
			toggle.focus();
		}
	} );

	// Close the panel when a menu link is chosen (in-page anchors).
	header.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( '.cg-nav a, .cg-header__cta a' ) ) {
			setOpen( false );
		}
	} );
}() );
