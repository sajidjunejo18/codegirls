/**
 * Reports edit screen (inc/reports-admin.php): layout, cover + PDF pickers,
 * live card preview and required-field checks.
 */
( function () {
	'use strict';

	var form = document.getElementById( 'post' );
	var T = ( window.cgReport && window.cgReport.i18n ) || {};
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
	var saveBtn = document.querySelector( '[data-cgv-save]' );
	var tried = false;
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
			if ( 'photo' === key ) {
				o.src = value || '';
				o.hidden = ! value;
				var empty = o.parentNode.querySelector( '.cgr-card__empty' );
				if ( empty ) { empty.hidden = !! value; }
			} else {
				o.textContent = value;
			}
		} );
	}
	function prettyDate( v ) {
		var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec( v || '' );
		if ( ! m ) { return ''; }
		return new Date( +m[ 1 ], +m[ 2 ] - 1, +m[ 3 ] ).toLocaleDateString( 'en-US', { year: 'numeric', month: 'long', day: 'numeric' } );
	}
	if ( nameInput ) {
		nameInput.addEventListener( 'input', function () { out( 'name', nameInput.value || 'Report title' ); } );
	}
	var date = document.getElementById( 'cg_report_date' );
	if ( date ) {
		date.addEventListener( 'input', function () { out( 'date', prettyDate( date.value ) ); } );
	}

	/* ---------- Cover image picker ---------- */
	document.querySelectorAll( '[data-cg-image]' ).forEach( function ( box ) {
		var input = box.querySelector( 'input' ), preview = box.querySelector( '.cgv-image__preview' ),
			btn = box.querySelector( 'button[data-pick]' ), clear = box.querySelector( '[data-clear]' ), isCover = '_thumbnail_id' === input.name, frame;
		function open() {
			if ( ! window.wp || ! wp.media ) { return; }
			if ( ! frame ) {
				frame = wp.media( { library: { type: 'image' }, multiple: false } );
				frame.on( 'select', function () {
					var img = frame.state().get( 'selection' ).first().toJSON(), s = img.sizes || {}, tag = el( 'img' );
					tag.src = ( s.large || s.medium_large || s.medium || img ).url;
					tag.alt = '';
					input.value = img.id;
					preview.innerHTML = '';
					preview.appendChild( tag );
					btn.textContent = T.changeImage;
					clear.hidden = false;
					if ( isCover ) { out( 'photo', ( s.medium || img ).url ); }
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
			if ( isCover ) { out( 'photo', '' ); }
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
	} );

	/* ---------- PDF picker ---------- */
	var pdfBox = document.querySelector( '[data-cgv-file]' );
	if ( pdfBox ) {
		var pdfInput = pdfBox.querySelector( 'input[name="cg_pdf"]' ), row = pdfBox.querySelector( '[data-cgr-file]' ),
			nameEl = pdfBox.querySelector( '[data-cgr-name]' ), metaEl = pdfBox.querySelector( '[data-cgr-meta]' ),
			view = pdfBox.querySelector( '[data-cgr-view]' ), pick = pdfBox.querySelector( '[data-cgr-pick]' ),
			clr = pdfBox.querySelector( '[data-cgr-clear]' ), pdfFrame, none = nameEl.textContent;
		pick.addEventListener( 'click', function () {
			if ( ! window.wp || ! wp.media ) { return; }
			if ( ! pdfFrame ) {
				pdfFrame = wp.media( { title: T.choosePdf, library: { type: 'application/pdf' }, button: { text: T.choosePdf }, multiple: false } );
				pdfFrame.on( 'select', function () {
					var f = pdfFrame.state().get( 'selection' ).first().toJSON();
					pdfInput.value = f.id;
					nameEl.textContent = f.filename || f.title;
					metaEl.textContent = f.filesizeHumanReadable || '';
					view.href = f.url;
					view.hidden = false;
					row.classList.add( 'has-file' );
					pick.textContent = T.changePdf;
					clr.hidden = false;
					pdfInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				} );
			}
			pdfFrame.open();
		} );
		clr.addEventListener( 'click', function () {
			pdfInput.value = '';
			nameEl.textContent = none;
			metaEl.textContent = '';
			view.hidden = true;
			row.classList.remove( 'has-file' );
			pick.textContent = T.choosePdf;
			clr.hidden = true;
			pdfInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
	}

	/* ---------- Required fields: title, cover image, PDF ---------- */
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
		var cover = form.querySelector( 'input[name="_thumbnail_id"]' ), pdf = form.querySelector( 'input[name="cg_pdf"]' ), bad = [];
		if ( nameInput && ! setError( nameInput, nameInput.parentNode, nameInput.value.trim() ? '' : T.title ) ) { bad.push( nameInput ); }
		if ( cover && ! setError( cover, document.querySelector( '[data-cgv-photo]' ), parseInt( cover.value, 10 ) > 0 ? '' : T.cover ) ) { bad.push( document.querySelector( '[data-cgv-photo] button' ) ); }
		if ( pdf && ! setError( pdf, pdfBox, pdf.value ? '' : T.pdf ) ) { bad.push( pdfBox.querySelector( '[data-cgr-pick]' ) ); }
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
