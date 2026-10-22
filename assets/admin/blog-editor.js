/**
 * Blog edit screen (inc/blog-admin.php): layout, picture picker, live card preview,
 * the Heading / Paragraph / Bullet list / Score cards article builder, and required-field checks.
 */
( function () {
	'use strict';

	var form = document.getElementById( 'post' );
	if ( ! form || ! document.querySelector( '.cgv' ) ) {
		return;
	}

	function el( tag, cls, text ) {
		var n = document.createElement( tag );
		if ( cls ) { n.className = cls; }
		if ( text ) { n.textContent = text; }
		return n;
	}
	function btn( label, title ) {
		var b = el( 'button', 'button button-small', label );
		b.type = 'button';
		if ( title ) { b.title = title; b.setAttribute( 'aria-label', title ); }
		return b;
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
	var nameInput = document.getElementById( 'title' ), slot = document.querySelector( '[data-cgv-name]' );
	if ( slot && nameInput ) {
		var lab = el( 'label', 'cgv-label', 'Title ' ), star = el( 'span', 'cgv-req', '*' );
		star.setAttribute( 'aria-hidden', 'true' );
		lab.appendChild( star );
		lab.htmlFor = 'title';
		slot.appendChild( lab );
		slot.appendChild( nameInput );
		nameInput.classList.add( 'cgv-name' );
		nameInput.required = true;
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
	if ( nameInput ) { nameInput.addEventListener( 'input', function () { out( 'name', nameInput.value || 'Blog title' ); } ); }
	document.querySelectorAll( '[data-bind]' ).forEach( function ( i ) {
		i.addEventListener( 'input', function () {
			var k = i.getAttribute( 'data-bind' ), v = i.value.trim();
			out( k, v || ( 'author' === k ? 'Author' : '' ) );
		} );
	} );
	var date = document.getElementById( 'cg_post_date' );
	if ( date ) {
		date.addEventListener( 'input', function () {
			var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec( date.value );
			if ( m ) { out( 'date', new Date( +m[ 1 ], +m[ 2 ] - 1, +m[ 3 ] ).toLocaleDateString( 'en-US', { month: 'short', day: 'numeric', year: 'numeric' } ) ); }
		} );
	}

	/* ---------- Picture picker ---------- */
	document.querySelectorAll( '[data-cg-image]' ).forEach( function ( box ) {
		var input = box.querySelector( 'input' ), preview = box.querySelector( '.cgv-image__preview' ),
			pick = box.querySelector( 'button[data-pick]' ), clear = box.querySelector( '[data-clear]' ), frame;
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
					pick.textContent = 'Change image';
					clear.hidden = false;
					out( 'photo', ( s.medium || img ).url );
					input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				} );
			}
			frame.open();
		}
		box.querySelectorAll( '[data-pick]' ).forEach( function ( p ) { p.addEventListener( 'click', open ); } );
		clear.addEventListener( 'click', function () {
			input.value = input.getAttribute( 'data-empty' );
			preview.innerHTML = '<span class="cgv-image__empty">+</span>';
			pick.textContent = 'Choose image';
			clear.hidden = true;
			out( 'photo', '' );
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
	} );

	/* ---------- Article builder ---------- */
	var store = form.querySelector( 'input[name="cg_blocks"]' ), wrap = document.querySelector( '[data-cgb-blocks]' );
	var KIND = { heading: 'Heading', text: 'Paragraph', list: 'Bullet list', scores: 'Score cards' };

	function esc( s ) { return s.replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' ); }
	// Only text, links, bold, italic and line breaks survive (pasted formatting is dropped).
	function clean( node ) {
		var html = '';
		node.childNodes.forEach( function ( c ) {
			if ( 3 === c.nodeType ) { html += esc( c.textContent ); return; }
			if ( 1 !== c.nodeType ) { return; }
			var t = c.nodeName.toLowerCase();
			if ( 'br' === t ) { html += '<br>'; }
			else if ( 'a' === t && c.getAttribute( 'href' ) ) { html += '<a href="' + c.getAttribute( 'href' ).replace( /"/g, '&quot;' ) + '">' + clean( c ) + '</a>'; }
			else if ( 'strong' === t || 'b' === t ) { html += '<strong>' + clean( c ) + '</strong>'; }
			else if ( 'em' === t || 'i' === t ) { html += '<em>' + clean( c ) + '</em>'; }
			else if ( 'div' === t || 'p' === t ) { html += ( html ? '<br><br>' : '' ) + clean( c ); }
			else { html += clean( c ); }
		} );
		return html;
	}
	function rich( html, placeholder, single ) {
		var d = el( 'div', 'cgv-para' + ( single ? ' cgb-item__text' : '' ) );
		d.contentEditable = 'true';
		d.setAttribute( 'role', 'textbox' );
		d.dataset.placeholder = placeholder;
		d.innerHTML = html || '';
		d.addEventListener( 'paste', function ( e ) {
			e.preventDefault();
			document.execCommand( 'insertText', false, ( e.clipboardData || window.clipboardData ).getData( 'text/plain' ) );
		} );
		if ( single ) { d.addEventListener( 'keydown', function ( e ) { if ( 'Enter' === e.key ) { e.preventDefault(); } } ); }
		return d;
	}
	function formatTools( getArea ) {
		var box = el( 'span', 'cgb-format' ), b = btn( 'B', 'Bold' ), i = btn( 'I', 'Italic' ), l = btn( '🔗 Link', 'Add link' ), u = btn( 'Unlink', 'Remove link' );
		[ b, i, l, u ].forEach( function ( x ) { x.addEventListener( 'mousedown', function ( e ) { e.preventDefault(); } ); box.appendChild( x ); } );
		b.addEventListener( 'click', function () { document.execCommand( 'bold' ); save(); } );
		i.addEventListener( 'click', function () { document.execCommand( 'italic' ); save(); } );
		u.addEventListener( 'click', function () { document.execCommand( 'unlink' ); save(); } );
		l.addEventListener( 'click', function () {
			var sel = window.getSelection(), area = sel.anchorNode && ( 1 === sel.anchorNode.nodeType ? sel.anchorNode : sel.anchorNode.parentNode ).closest( '.cgv-para' );
			if ( ! sel.rangeCount || sel.isCollapsed || ! area || ! getArea().contains( area ) ) { window.alert( 'First select the words that should become the link.' ); return; }
			var url = window.prompt( 'Paste the link (web address):', 'https://' );
			if ( ! url || 'https://' === url ) { return; }
			if ( ! /^(https?:|mailto:|tel:|\/)/i.test( url ) ) { url = 'https://' + url; }
			document.execCommand( 'createLink', false, url );
			save();
		} );
		return box;
	}

	function row( type, data, li ) {
		var r = el( 'div', 'cgb-item' ), rm = btn( '×', 'Remove this line' );
		if ( 'list' === type ) {
			r.appendChild( el( 'span', 'cgb-item__dot', '•' ) );
			r.appendChild( rich( data || '', 'List item…', true ) );
		} else {
			var l = el( 'input', 'cgb-score__label' ), v = el( 'input', 'cgb-score__value' );
			l.type = v.type = 'text';
			l.placeholder = 'Name, e.g. Arifa Batool';
			v.placeholder = 'Score, e.g. 95.73%';
			l.value = ( data && data.label ) || '';
			v.value = ( data && data.value ) || '';
			r.appendChild( l );
			r.appendChild( v );
		}
		rm.addEventListener( 'click', function () { r.remove(); save(); } );
		r.appendChild( rm );
		return r;
	}

	function block( b ) {
		var li = el( 'li', 'cgv-block cgv-block--' + ( 'heading' === b.type ? 'heading' : 'text' ) ), bar = el( 'div', 'cgv-block__bar' ), tools = el( 'span', 'cgv-block__tools' ), body;
		li.dataset.type = b.type;
		bar.appendChild( el( 'span', 'cgv-block__kind', KIND[ b.type ] ) );
		if ( 'heading' === b.type ) {
			body = el( 'input', 'cgv-heading' );
			body.type = 'text';
			body.value = b.text || '';
			body.placeholder = 'e.g. Our High Achievers!';
			body.addEventListener( 'keydown', function ( e ) { if ( 'Enter' === e.key ) { e.preventDefault(); } } );
		} else if ( 'text' === b.type ) {
			body = rich( b.html, 'Write or paste the paragraph here…' );
			tools.appendChild( formatTools( function () { return body; } ) );
		} else {
			body = el( 'div', 'cgb-items' + ( 'scores' === b.type ? ' cgb-items--scores' : '' ) );
			var items = ( b.items && b.items.length ) ? b.items : [ 'list' === b.type ? '' : {}, 'list' === b.type ? '' : {} ];
			items.forEach( function ( it ) { body.appendChild( row( b.type, it ) ); } );
			var more = el( 'button', 'button-link cgb-more', 'list' === b.type ? '+ Add item' : '+ Add card' );
			more.type = 'button';
			more.addEventListener( 'click', function () {
				var r = row( b.type );
				body.insertBefore( r, more );
				r.querySelector( 'input, .cgv-para' ).focus();
				save();
			} );
			body.appendChild( more );
			if ( 'list' === b.type ) { tools.appendChild( formatTools( function () { return body; } ) ); }
		}
		body.addEventListener( 'input', save );
		[ [ '↑', -1, 'Move up' ], [ '↓', 1, 'Move down' ] ].forEach( function ( m ) {
			var mv = btn( m[ 0 ], m[ 2 ] );
			mv.addEventListener( 'click', function () {
				var sib = m[ 1 ] < 0 ? li.previousElementSibling : li.nextElementSibling;
				if ( sib ) { wrap.insertBefore( li, m[ 1 ] < 0 ? sib : sib.nextElementSibling ); save(); }
			} );
			tools.appendChild( mv );
		} );
		var rm = el( 'button', 'button-link button-link-delete cgv-block__rm', 'Remove' );
		rm.type = 'button';
		rm.addEventListener( 'click', function () {
			if ( ! li.textContent.trim() && ! li.querySelector( 'input[value]:not([value=""])' ) || window.confirm( 'Remove this block?' ) ) { li.remove(); save(); }
		} );
		tools.appendChild( rm );
		bar.appendChild( tools );
		li.appendChild( bar );
		li.appendChild( body );
		return li;
	}

	function read() {
		var data = [];
		wrap.querySelectorAll( ':scope > .cgv-block' ).forEach( function ( b ) {
			var t = b.dataset.type;
			if ( 'heading' === t ) { data.push( { type: t, text: b.querySelector( 'input' ).value } ); }
			else if ( 'text' === t ) { data.push( { type: t, html: clean( b.querySelector( '.cgv-para' ) ) } ); }
			else if ( 'list' === t ) {
				data.push( { type: t, items: Array.prototype.map.call( b.querySelectorAll( '.cgb-item .cgv-para' ), function ( p ) { return clean( p ); } ) } );
			} else {
				data.push( { type: t, items: Array.prototype.map.call( b.querySelectorAll( '.cgb-item' ), function ( r ) {
					return { label: r.querySelector( '.cgb-score__label' ).value, value: r.querySelector( '.cgb-score__value' ).value };
				} ) } );
			}
		} );
		return data;
	}
	function save() { if ( store ) { store.value = JSON.stringify( read() ); } }

	if ( store && wrap ) {
		var initial;
		try { initial = JSON.parse( store.value || '[]' ); } catch ( e ) { initial = []; }
		initial.forEach( function ( b ) { wrap.appendChild( block( b ) ); } );
		document.querySelectorAll( '[data-add]' ).forEach( function ( a ) {
			a.addEventListener( 'click', function () {
				var li = block( { type: a.getAttribute( 'data-add' ) } );
				wrap.appendChild( li );
				li.querySelector( 'input, .cgv-para' ).focus();
				save();
			} );
		} );
		form.addEventListener( 'submit', save );
	}

	/* ---------- Required fields ---------- */
	var tried = false;
	function setError( field, wrapEl, msg ) {
		var err = wrapEl.querySelector( ':scope > .cgv-error' );
		if ( msg ) {
			if ( ! err ) { err = el( 'p', 'cgv-error' ); err.setAttribute( 'role', 'alert' ); wrapEl.appendChild( err ); }
			err.textContent = msg;
			wrapEl.classList.add( 'cgv-invalid' );
			field.setAttribute( 'aria-invalid', 'true' );
		} else {
			if ( err ) { err.remove(); }
			wrapEl.classList.remove( 'cgv-invalid' );
			field.removeAttribute( 'aria-invalid' );
		}
		return ! msg;
	}
	function articleText() {
		return read().some( function ( b ) {
			if ( 'heading' === b.type ) { return false; }
			if ( 'text' === b.type ) { return b.html.replace( /<[^>]+>/g, '' ).trim(); }
			return b.items.some( function ( i ) { return ( 'string' === typeof i ? i.replace( /<[^>]+>/g, '' ) : i.label + i.value ).trim(); } );
		} );
	}
	function validate( focus ) {
		var bad = [], cat = document.getElementById( 'cg_category' ), au = document.getElementById( 'cg_author' ),
			pic = form.querySelector( 'input[name="_thumbnail_id"]' ), art = document.querySelector( '[data-cgv-article]' );
		if ( nameInput && ! setError( nameInput, nameInput.parentNode, nameInput.value.trim() ? '' : 'Please enter the blog title.' ) ) { bad.push( nameInput ); }
		if ( cat && ! setError( cat, cat.parentNode, cat.value.trim() ? '' : 'Please choose a category.' ) ) { bad.push( cat ); }
		if ( au && ! setError( au, au.parentNode, au.value.trim() ? '' : 'Please choose the author.' ) ) { bad.push( au ); }
		if ( pic && ! setError( pic, document.querySelector( '[data-cgv-photo]' ), parseInt( pic.value, 10 ) > 0 ? '' : 'Please choose a picture.' ) ) { bad.push( document.querySelector( '[data-cgv-photo] button' ) ); }
		if ( art && ! setError( store, art, articleText() ? '' : 'Please write the article (at least one paragraph).' ) ) { bad.push( art.querySelector( '[data-add="text"]' ) ); }
		if ( bad.length && focus ) {
			bad[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'center' } );
			bad[ 0 ].focus( { preventScroll: true } );
		}
		return ! bad.length;
	}
	form.addEventListener( 'submit', function ( e ) {
		if ( e.submitter && 'save-post' === e.submitter.id ) { return; } // "Save Draft" needs no checks.
		tried = true;
		if ( ! validate( true ) ) { e.preventDefault(); e.stopImmediatePropagation(); }
	}, true );
	form.addEventListener( 'input', function () { if ( tried ) { validate( false ); } } );
	form.addEventListener( 'change', function () { if ( tried ) { validate( false ); } } );
	var saveBtn = document.querySelector( '[data-cgv-save]' );
	if ( saveBtn ) {
		saveBtn.addEventListener( 'click', function () {
			tried = true;
			save();
			if ( ! validate( true ) ) { return; }
			var publish = document.getElementById( 'publish' );
			if ( publish ) { publish.click(); } else { form.requestSubmit ? form.requestSubmit() : form.submit(); }
		} );
	}
}() );
