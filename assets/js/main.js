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
		navigator.clipboard.writeText( src.innerText.replace( /\n{2,}/g, '\n' ) ).then( function () {
			btn.textContent = 'Copied!';
			setTimeout( function () { btn.textContent = label; }, 1800 );
		} );
	} );

	// Course popups: Enroll Now ([data-cg-enroll]) and Notify Me ([data-cg-notify]).
	function openModal( dialog, trigger ) {
		if ( ! dialog || 'function' !== typeof dialog.showModal ) {
			return false;
		}
		var course = trigger.getAttribute( 'data-course' ) || '';
		var title = dialog.querySelector( '[data-cg-title]' );
		if ( title && course ) {
			title.textContent = course;
		}
		var hidden = dialog.querySelector( '[data-cg-course]' );
		if ( hidden ) {
			hidden.value = course;
		}
		var slots = dialog.querySelector( '[data-cg-slots]' );
		if ( slots ) {
			while ( slots.options.length > 1 ) {
				slots.remove( 1 );
			}
			( trigger.getAttribute( 'data-slots' ) || '' ).split( '|' ).forEach( function ( s ) {
				if ( s ) {
					slots.add( new Option( s, s ) );
				}
			} );
			var phase = dialog.querySelector( '[data-cg-phase]' );
			if ( phase ) {
				phase.textContent = trigger.getAttribute( 'data-phase' ) ? '(' + trigger.getAttribute( 'data-phase' ).toUpperCase() + ')' : '';
			}
		}
		dialog.showModal();
		document.documentElement.classList.add( 'cg-modal-open' );
		return true;
	}

	document.addEventListener( 'click', function ( e ) {
		var t = e.target.closest( '[data-cg-enroll], [data-cg-notify], [data-cg-close]' );
		if ( ! t ) {
			return;
		}
		if ( t.hasAttribute( 'data-cg-close' ) ) {
			t.closest( 'dialog' ).close();
			return;
		}
		var dialog = document.getElementById( t.hasAttribute( 'data-cg-enroll' ) ? 'cg-enroll' : 'cg-notify' );
		if ( openModal( dialog, t ) ) {
			e.preventDefault(); // Without JS/dialog support the link falls back to the contact form.
		}
	} );

	Array.prototype.forEach.call( document.querySelectorAll( 'dialog.cg-modal' ), function ( d ) {
		d.addEventListener( 'close', function () {
			document.documentElement.classList.remove( 'cg-modal-open' );
		} );
		// Click on the dimmed backdrop closes the popup.
		d.addEventListener( 'click', function ( e ) {
			if ( e.target === d ) {
				d.close();
			}
		} );
	} );

	// Result toast.
	var toast = document.querySelector( '[data-cg-toast]' );
	if ( toast ) {
		var hide = function () {
			toast.hidden = true;
		};
		toast.querySelector( '[data-cg-toast-close]' ).addEventListener( 'click', hide );
		setTimeout( hide, 9000 );
		if ( window.history && history.replaceState ) {
			history.replaceState( null, '', location.pathname + location.hash );
		}
	}

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
