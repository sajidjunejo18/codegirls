/**
 * CodeGirls theme scripts. Dependency-free.
 */
( function () {
	'use strict';

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
