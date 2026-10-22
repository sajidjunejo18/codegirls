/**
 * Voices edit screen (inc/voices-admin.php): image pickers, live preview,
 * searchable skills, and the heading / paragraph story builder.
 */
( function () {
	'use strict';

	var form = document.getElementById( 'post' );
	var T = window.cgVoice || { skills: [], i18n: {} };
	if ( ! form || ! document.querySelector( '.cgv' ) ) {
		return;
	}

	function el( tag, cls, text ) {
		var n = document.createElement( tag );
		if ( cls ) { n.className = cls; }
		if ( text ) { n.textContent = text; }
		return n;
	}

	/* ---------- Layout (design A): header actions, name in card 1, trash under the sidebar ---------- */
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
	/* ---------- Required fields: name, photo, job title ---------- */
	var tried = false;
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
		var name = document.getElementById( 'title' ), photo = form.querySelector( 'input[name="_thumbnail_id"]' ), role = document.getElementById( 'cg_role' ), bad = [];
		if ( name && ! setError( name, name.parentNode, name.value.trim() ? '' : 'Please enter her full name.' ) ) { bad.push( name ); }
		if ( photo && ! setError( photo, document.querySelector( '[data-cgv-photo]' ), parseInt( photo.value, 10 ) > 0 ? '' : 'Please choose her photo.' ) ) { bad.push( document.querySelector( '[data-cgv-photo] button' ) ); }
		if ( role && ! setError( role, role.parentNode, role.value.trim() ? '' : 'Please enter her job title.' ) ) { bad.push( role ); }
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

	var saveBtn = document.querySelector( '[data-cgv-save]' );
	if ( saveBtn ) {
		saveBtn.addEventListener( 'click', function () {
			tried = true;
			if ( ! validate( true ) ) { return; }
			var publish = document.getElementById( 'publish' );
			if ( publish ) { publish.click(); } else { form.requestSubmit ? form.requestSubmit() : form.submit(); }
		} );
	}
	var slot = document.querySelector( '[data-cgv-name]' ), nameInput = document.getElementById( 'title' );
	if ( slot && nameInput ) {
		var lab = el( 'label', 'cgv-label', 'Full name ' ), star = el( 'span', 'cgv-req', '*' );
		star.setAttribute( 'aria-hidden', 'true' );
		lab.appendChild( star );
		lab.htmlFor = 'title';
		nameInput.required = true;
		nameInput.setAttribute( 'aria-required', 'true' );
		slot.appendChild( lab );
		slot.appendChild( nameInput );
		nameInput.classList.add( 'cgv-name' );
	}
	var trash = document.querySelector( '[data-cgv-trash]' ), side = document.getElementById( 'side-sortables' );
	if ( trash && side ) { side.parentNode.appendChild( trash ); trash.hidden = false; }

	/* ---------- Live preview ---------- */
	function out( key, value ) {
		document.querySelectorAll( '[data-out="' + key + '"]' ).forEach( function ( o ) {
			if ( 'photo' === key ) {
				o.src = value || '';
				o.hidden = ! value;
			} else {
				o.textContent = value;
			}
		} );
	}
	var title = document.getElementById( 'title' );
	if ( title ) {
		title.addEventListener( 'input', function () { out( 'name', title.value ); } );
	}
	document.querySelectorAll( '[data-bind]' ).forEach( function ( i ) {
		i.addEventListener( 'input', function () { out( i.getAttribute( 'data-bind' ), i.value ); } );
	} );
	var intro = document.querySelector( '.cgv-intro' ), count = document.querySelector( '[data-cgv-count]' );
	function countIntro() {
		if ( intro && count ) { count.textContent = '(' + intro.value.length + ' / 600)'; }
	}
	if ( intro ) { intro.addEventListener( 'input', countIntro ); countIntro(); }

	/* ---------- Image pickers (photo, banner) ---------- */
	document.querySelectorAll( '[data-cg-image]' ).forEach( function ( box ) {
		var input = box.querySelector( 'input' ), preview = box.querySelector( '.cgv-image__preview' ),
			btn = box.querySelector( 'button[data-pick]' ), clear = box.querySelector( '[data-clear]' ),
			isPhoto = '_thumbnail_id' === input.name, frame;
		function open() {
			if ( ! window.wp || ! wp.media ) { return; }
			if ( ! frame ) {
				frame = wp.media( { library: { type: 'image' }, multiple: false } );
				frame.on( 'select', function () {
					var img = frame.state().get( 'selection' ).first().toJSON(), s = img.sizes || {},
						url = ( s.large || s.medium_large || s.medium || img ).url, tag = el( 'img' );
					tag.src = url;
					tag.alt = '';
					input.value = img.id;
					preview.innerHTML = '';
					preview.appendChild( tag );
					btn.textContent = T.i18n.change;
					clear.hidden = false;
					if ( isPhoto ) { out( 'photo', ( s.medium || img ).url ); }
					input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				} );
			}
			frame.open();
		}
		box.querySelectorAll( '[data-pick]' ).forEach( function ( p ) { p.addEventListener( 'click', open ); } );
		clear.addEventListener( 'click', function () {
			input.value = input.getAttribute( 'data-empty' );
			preview.innerHTML = '<span class="cgv-image__empty">+</span>';
			btn.textContent = T.i18n.choose;
			clear.hidden = true;
			if ( isPhoto ) { out( 'photo', '' ); }
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
	} );

	/* ---------- Skills: searchable dropdown with chips ---------- */
	var sk = document.querySelector( '[data-cgv-skills]' );
	if ( sk ) {
		var hidden = sk.querySelector( 'input[type=hidden]' ), chips = sk.querySelector( '.cgv-skills__chips' ),
			search = sk.querySelector( '.cgv-skills__search' ), list = sk.querySelector( '.cgv-skills__list' ),
			chosen = hidden.value.split( ',' ).map( function ( s ) { return s.trim(); } ).filter( Boolean ),
			options = T.skills.slice(), active = -1;

		var has = function ( arr, v ) {
			return arr.some( function ( a ) { return a.toLowerCase() === v.toLowerCase(); } );
		};
		function sync() {
			hidden.value = chosen.join( ', ' );
			chips.innerHTML = '';
			chosen.forEach( function ( s, i ) {
				var c = el( 'span', 'cgv-chip', s ), x = el( 'button', 'cgv-chip__x', '×' );
				x.type = 'button';
				x.setAttribute( 'aria-label', 'Remove ' + s );
				x.addEventListener( 'click', function () { chosen.splice( i, 1 ); sync(); search.focus(); } );
				c.appendChild( x );
				chips.appendChild( c );
			} );
		}
		function add( v ) {
			v = v.trim().replace( /\s+/g, ' ' );
			if ( ! v ) { return; }
			var match = options.filter( function ( o ) { return o.toLowerCase() === v.toLowerCase(); } )[ 0 ];
			v = match || v;
			if ( ! has( chosen, v ) ) { chosen.push( v ); }
			if ( ! match ) { options.push( v ); }
			search.value = '';
			sync();
			render();
		}
		function render() {
			var q = search.value.trim().toLowerCase(), items = [];
			options.forEach( function ( o ) {
				if ( ! has( chosen, o ) && ( ! q || o.toLowerCase().indexOf( q ) > -1 ) ) { items.push( o ); }
			} );
			items.sort( function ( a, b ) {
				return ( a.toLowerCase().indexOf( q ) === 0 ? 0 : 1 ) - ( b.toLowerCase().indexOf( q ) === 0 ? 0 : 1 );
			} );
			items = items.slice( 0, 40 );
			list.innerHTML = '';
			items.forEach( function ( o ) {
				var li = el( 'li', 'cgv-opt-item', o );
				li.setAttribute( 'role', 'option' );
				li.addEventListener( 'mousedown', function ( e ) { e.preventDefault(); add( o ); } );
				list.appendChild( li );
			} );
			if ( q && ! has( options, search.value.trim() ) ) {
				var n = el( 'li', 'cgv-opt-item cgv-opt-item--new', T.i18n.add.replace( '%s', search.value.trim() ) );
				n.setAttribute( 'role', 'option' );
				n.addEventListener( 'mousedown', function ( e ) { e.preventDefault(); add( search.value ); } );
				list.appendChild( n );
			} else if ( ! items.length ) {
				list.appendChild( el( 'li', 'cgv-opt-item cgv-opt-item--none', T.i18n.none ) );
			}
			active = -1;
			list.hidden = document.activeElement !== search;
			search.setAttribute( 'aria-expanded', String( ! list.hidden ) );
		}
		function move( d ) {
			var li = list.querySelectorAll( '.cgv-opt-item:not(.cgv-opt-item--none)' );
			if ( ! li.length ) { return; }
			active = ( active + d + li.length ) % li.length;
			li.forEach( function ( x, i ) { x.classList.toggle( 'is-active', i === active ); } );
			li[ active ].scrollIntoView( { block: 'nearest' } );
		}
		search.addEventListener( 'focus', render );
		search.addEventListener( 'input', render );
		search.addEventListener( 'blur', function () { list.hidden = true; search.setAttribute( 'aria-expanded', 'false' ); } );
		search.addEventListener( 'keydown', function ( e ) {
			if ( 'ArrowDown' === e.key ) { e.preventDefault(); move( 1 ); }
			else if ( 'ArrowUp' === e.key ) { e.preventDefault(); move( -1 ); }
			else if ( 'Enter' === e.key || ',' === e.key ) {
				e.preventDefault(); // Never submit the whole form from here.
				var cur = list.querySelectorAll( '.cgv-opt-item:not(.cgv-opt-item--none)' )[ active ];
				if ( cur && ! cur.classList.contains( 'cgv-opt-item--new' ) ) { add( cur.textContent ); } else { add( search.value ); }
			} else if ( 'Backspace' === e.key && ! search.value && chosen.length ) {
				chosen.pop(); sync(); render();
			}
		} );
		sk.querySelector( '.cgv-skills__box' ).addEventListener( 'click', function ( e ) {
			if ( ! e.target.closest( '.cgv-chip__x' ) ) { search.focus(); }
		} );
		sync();
	}

	/* ---------- Story builder: heading / paragraph blocks ---------- */
	var store = form.querySelector( 'input[name="cg_sections"]' ), wrap = document.querySelector( '[data-cgv-blocks]' );
	if ( store && wrap ) {
		var blocks;
		try { blocks = JSON.parse( store.value || '[]' ); } catch ( e ) { blocks = []; }

		// Only text, links, bold and line breaks survive (pasted Word / web formatting is dropped).
		function clean( node ) {
			var outHtml = '';
			node.childNodes.forEach( function ( c ) {
				if ( 3 === c.nodeType ) {
					outHtml += c.textContent.replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
				} else if ( 1 === c.nodeType ) {
					var t = c.nodeName.toLowerCase();
					if ( 'br' === t ) { outHtml += '<br>'; }
					else if ( 'a' === t && c.getAttribute( 'href' ) ) { outHtml += '<a href="' + c.getAttribute( 'href' ).replace( /"/g, '&quot;' ) + '">' + clean( c ) + '</a>'; }
					else if ( 'strong' === t || 'b' === t ) { outHtml += '<strong>' + clean( c ) + '</strong>'; }
					else if ( 'div' === t || 'p' === t ) { outHtml += ( outHtml ? '<br><br>' : '' ) + clean( c ); }
					else { outHtml += clean( c ); }
				}
			} );
			return outHtml;
		}
		function save() {
			var data = [];
			wrap.querySelectorAll( '.cgv-block' ).forEach( function ( b ) {
				if ( 'heading' === b.dataset.type ) {
					data.push( { type: 'heading', text: b.querySelector( 'input' ).value } );
				} else {
					data.push( { type: 'text', html: clean( b.querySelector( '.cgv-para' ) ) } );
				}
			} );
			store.value = JSON.stringify( data );
		}
		function linkSelection( para ) {
			var sel = window.getSelection();
			if ( ! sel.rangeCount || sel.isCollapsed || ! para.contains( sel.anchorNode ) ) { window.alert( T.i18n.sel ); return; }
			var url = window.prompt( T.i18n.url, 'https://' );
			if ( ! url || 'https://' === url ) { return; }
			if ( ! /^(https?:|mailto:|tel:|\/)/i.test( url ) ) { url = 'https://' + url; }
			document.execCommand( 'createLink', false, url );
			save();
		}
		function block( b ) {
			var li = el( 'li', 'cgv-block cgv-block--' + b.type ), bar = el( 'div', 'cgv-block__bar' );
			li.dataset.type = b.type;
			bar.appendChild( el( 'span', 'cgv-block__kind', 'heading' === b.type ? 'Heading' : 'Paragraph' ) );
			var tools = el( 'span', 'cgv-block__tools' ), body;
			if ( 'heading' === b.type ) {
				body = el( 'input', 'cgv-heading' );
				body.type = 'text';
				body.value = b.text || '';
				body.placeholder = 'e.g. Before CodeGirls';
				body.addEventListener( 'keydown', function ( e ) { if ( 'Enter' === e.key ) { e.preventDefault(); } } );
			} else {
				body = el( 'div', 'cgv-para' );
				body.contentEditable = 'true';
				body.setAttribute( 'role', 'textbox' );
				body.setAttribute( 'aria-multiline', 'true' );
				body.dataset.placeholder = 'Write or paste the paragraph here…';
				body.innerHTML = b.html || '';
				body.addEventListener( 'paste', function ( e ) {
					e.preventDefault(); // Paste as plain text, so fonts and colours never come along.
					var text = ( e.clipboardData || window.clipboardData ).getData( 'text/plain' );
					document.execCommand( 'insertText', false, text );
				} );
				var bold = el( 'button', 'button button-small', 'B' ), link = el( 'button', 'button button-small', '🔗 Link' ), unlink = el( 'button', 'button button-small', 'Unlink' );
				[ bold, link, unlink ].forEach( function ( x ) { x.type = 'button'; x.addEventListener( 'mousedown', function ( e ) { e.preventDefault(); } ); } );
				bold.title = 'Bold';
				bold.addEventListener( 'click', function () { document.execCommand( 'bold' ); save(); } );
				link.addEventListener( 'click', function () { linkSelection( body ); } );
				unlink.addEventListener( 'click', function () { document.execCommand( 'unlink' ); save(); } );
				tools.appendChild( bold );
				tools.appendChild( link );
				tools.appendChild( unlink );
			}
			body.addEventListener( 'input', save );
			[ [ '↑', -1, 'Move up' ], [ '↓', 1, 'Move down' ] ].forEach( function ( m ) {
				var mv = el( 'button', 'button button-small', m[ 0 ] );
				mv.type = 'button';
				mv.title = m[ 2 ];
				mv.addEventListener( 'click', function () {
					var sib = m[ 1 ] < 0 ? li.previousElementSibling : li.nextElementSibling;
					if ( sib ) { wrap.insertBefore( li, m[ 1 ] < 0 ? sib : sib.nextElementSibling ); save(); }
				} );
				tools.appendChild( mv );
			} );
			var rm = el( 'button', 'button-link button-link-delete cgv-block__rm', 'Remove' );
			rm.type = 'button';
			rm.addEventListener( 'click', function () {
				var empty = 'heading' === b.type ? ! body.value.trim() : ! body.textContent.trim();
				if ( empty || window.confirm( T.i18n.remove ) ) { li.remove(); save(); }
			} );
			tools.appendChild( rm );
			bar.appendChild( tools );
			li.appendChild( bar );
			li.appendChild( body );
			return li;
		}
		blocks.forEach( function ( b ) { wrap.appendChild( block( b ) ); } );
		document.querySelectorAll( '[data-add]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var li = block( { type: btn.getAttribute( 'data-add' ) } );
				wrap.appendChild( li );
				li.querySelector( 'input, .cgv-para' ).focus();
				save();
			} );
		} );
		form.addEventListener( 'submit', save );
	}
}() );
