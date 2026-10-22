/**
 * Recognitions edit screen (inc/recognitions-admin.php): layout, picture + badge pickers,
 * live card preview and required-field checks.
 */
( function () {
	'use strict';

	var form = document.getElementById( 'post' );
	var T = ( window.cgRecognition && window.cgRecognition.i18n ) || {};
	if ( ! form || ! document.querySelector( '.cgv' ) ) {
		return;
	}

	function el( tag, cls, text ) {
		var n = document.createElement( tag );
		if ( cls ) { n.className = cls; }
		if ( text ) { n.textContent = text; }
		return n;
	}

	/* ---------- Layout: header, title inside card 1, trash under the sidebar ---------- */
	document.body.classList.add( 'cgv-screen' );
	var head = document.querySelector( '[data-cgv-head]' ), h1 = document.querySelector( '.wrap h1.wp-heading-inline' );
	if ( head && h1 ) {
		var bar = el( 'div', 'cgv-titlebar' ), left = el( 'div' );
		h1.parentNode.insertBefore( bar, h1 );
		left.appendChild( h1 );
		var addNew = document.querySelector( '.wrap .page-title-action' );
		if ( addNew ) { left.appendChild( addNew ); }
		left.appendChild( head.querySelector( '.cgv-head__sub' ) );
		bar.appendChild( left );
		bar.appendChild( head.querySelector( '.cgv-head__actions' ) );
		head.remove();
	}
	var saveBtn = document.querySelector( '[data-cgv-save]' ), tried = false;
	var slot = document.querySelector( '[data-cgv-name]' ), nameInput = document.getElementById( 'title' );
	if ( slot && nameInput ) {
		var lab = el( 'label', 'cgv-label', 'Title ' ), star = el( 'span', 'cgv-req', '*' );
		star.setAttribute( 'aria-hidden', 'true' );
		lab.appendChild( star );
		lab.htmlFor = 'title';
		slot.appendChild( lab );
		slot.appendChild( nameInput );
		nameInput.classList.add( 'cgv-name' );
		nameInput.required = true;
		nameInput.setAttribute( 'aria-required', 'true' );
	}
	var trash = document.querySelector( '[data-cgv-trash]' ), side = document.getElementById( 'side-sortables' );
	if ( trash && side ) { side.parentNode.appendChild( trash ); trash.hidden = false; }

	/* ---------- Live card preview ---------- */
	function out( key, value ) {
		document.querySelectorAll( '[data-out="' + key + '"]' ).forEach( function ( o ) {
			if ( 'photo' === key || 'badge' === key ) {
				o.src = value || '';
				o.hidden = ! value;
				if ( 'photo' === key ) {
					var empty = o.parentNode.querySelector( '.cgr-card__empty' );
					if ( empty ) { empty.hidden = !! value; }
				}
			} else {
				o.textContent = value;
			}
		} );
	}
	function isUrl( v ) {
		v = v.trim();
		if ( ! v ) { return true; }
		try { var u = new URL( /^https?:\/\//i.test( v ) ? v : 'https://' + v ); return u.hostname.indexOf( '.' ) > 0; } catch ( e ) { return false; }
	}
	if ( nameInput ) {
		nameInput.addEventListener( 'input', function () { out( 'name', nameInput.value || 'Title of the award' ); } );
	}
	var urlInput = document.getElementById( 'cg_url' ), btnPrev = document.querySelector( '[data-out-url]' );
	if ( urlInput && btnPrev ) {
		urlInput.addEventListener( 'input', function () { btnPrev.hidden = ! urlInput.value.trim(); } );
	}

	/* ---------- Image pickers (award picture, badge) ---------- */
	document.querySelectorAll( '[data-cg-image]' ).forEach( function ( box ) {
		var input = box.querySelector( 'input' ), preview = box.querySelector( '.cgv-image__preview' ),
			btn = box.querySelector( 'button[data-pick]' ), clear = box.querySelector( '[data-clear]' ),
			key = '_thumbnail_id' === input.name ? 'photo' : 'badge', frame;
		function open() {
			if ( ! window.wp || ! wp.media ) { return; }
			if ( ! frame ) {
				frame = wp.media( { library: { type: 'image' }, multiple: false } );
				frame.on( 'select', function () {
					var img = frame.state().get( 'selection' ).first().toJSON(), s = img.sizes || {}, tag = el( 'img' );
					tag.src = ( s.large || s.medium_large || s.medium || s.thumbnail || img ).url;
					tag.alt = '';
					input.value = img.id;
					preview.innerHTML = '';
					preview.appendChild( tag );
					btn.textContent = T.changeImage;
					clear.hidden = false;
					out( key, ( s.medium || s.thumbnail || img ).url );
					input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				} );
			}
			frame.open();
		}
		box.querySelectorAll( '[data-pick]' ).forEach( function ( p ) { p.addEventListener( 'click', open ); } );
		clear.addEventListener( 'click', function () {
			input.value = input.getAttribute( 'data-empty' );
			preview.innerHTML = '<span class="cgv-image__empty">+</span>';
			btn.textContent = T.chooseImage;
			clear.hidden = true;
			out( key, '' );
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
	} );

	/* ---------- Required fields: title, award picture; website must look like an address ---------- */
	function setError( field, wrap, msg ) {
		var err = wrap.querySelector( ':scope > .cgv-error' );
		if ( msg ) {
			if ( ! err ) { err = el( 'p', 'cgv-error' ); err.id = ( field.id || field.name ) + '-error'; err.setAttribute( 'role', 'alert' ); wrap.appendChild( err ); }
			err.textContent = msg;
			wrap.classList.add( 'cgv-invalid' );
			field.setAttribute( 'aria-invalid', 'true' );
			field.setAttribute( 'aria-describedby', err.id );
		} else {
			if ( err ) { err.remove(); }
			wrap.classList.remove( 'cgv-invalid' );
			field.removeAttribute( 'aria-invalid' );
		}
		return ! msg;
	}
	function validate( focus ) {
		var pic = form.querySelector( 'input[name="_thumbnail_id"]' ), bad = [];
		if ( nameInput && ! setError( nameInput, nameInput.parentNode, nameInput.value.trim() ? '' : T.title ) ) { bad.push( nameInput ); }
		if ( pic && ! setError( pic, document.querySelector( '[data-cgv-photo]' ), parseInt( pic.value, 10 ) > 0 ? '' : T.picture ) ) { bad.push( document.querySelector( '[data-cgv-photo] button' ) ); }
		// The link is optional on Recognitions, required on Press (inc/press-admin.php sets urlRequired).
		var urlOk = isUrl( urlInput ? urlInput.value : '' ) && ( ! T.urlRequired || ( urlInput && urlInput.value.trim() ) );
		if ( urlInput && ! setError( urlInput, urlInput.parentNode, urlOk ? '' : T.url ) ) { bad.push( urlInput ); }
		if ( bad.length && focus ) {
			bad[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'center' } );
			bad[ 0 ].focus( { preventScroll: true } );
		}
		return ! bad.length;
	}
	form.addEventListener( 'submit', function ( e ) {
		tried = true;
		if ( ! validate( true ) ) { e.preventDefault(); e.stopImmediatePropagation(); }
	}, true );
	form.addEventListener( 'input', function () { if ( tried ) { validate( false ); } } );
	form.addEventListener( 'change', function () { if ( tried ) { validate( false ); } } );
	if ( saveBtn ) {
		saveBtn.addEventListener( 'click', function () {
			tried = true;
			if ( ! validate( true ) ) { return; }
			var publish = document.getElementById( 'publish' );
			if ( publish ) { publish.click(); } else { form.requestSubmit ? form.requestSubmit() : form.submit(); }
		} );
	}
}() );
