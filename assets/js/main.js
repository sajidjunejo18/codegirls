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

	// "/#contact" links: scroll to this page's own contact section when it has one,
	// instead of jumping to the homepage.
	document.addEventListener( 'click', function ( e ) {
		var link = e.target.closest( 'a[href$="#contact"]' );
		var target = document.getElementById( 'contact' );
		if ( ! link || ! target || link.hasAttribute( 'data-cg-enroll' ) || link.hasAttribute( 'data-cg-notify' ) ) {
			return;
		}
		e.preventDefault();
		target.scrollIntoView( { behavior: 'smooth', block: 'start' } );
		history.replaceState( null, '', '#contact' );
	} );

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

	// Pathway: fill the line and light up all five steps each time the section scrolls into view;
	// reset once it has fully left the screen so it can play again.
	var pathway = document.querySelector( '.pathway' );
	if ( pathway ) {
		if ( 'IntersectionObserver' in window && ! window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			new IntersectionObserver( function ( entries ) {
				var e = entries[ entries.length - 1 ];
				if ( e.isIntersecting && e.intersectionRatio >= 0.4 ) {
					pathway.classList.add( 'is-in' );
				} else if ( ! e.isIntersecting ) {
					pathway.classList.remove( 'is-in' );
				}
			}, { threshold: [ 0, 0.4 ] } ).observe( pathway );
		} else {
			pathway.classList.add( 'is-in' );
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

// Homepage hero video ([cg_hero_media]): the round button pauses / plays it.
// Visitors who ask for reduced motion get it paused from the start.
( function () {
	var video = document.querySelector( '[data-cg-hero-video]' ), btn = document.querySelector( '[data-cg-hero-toggle]' );
	if ( ! video || ! btn ) {
		return;
	}
	function sync() {
		var paused = video.paused;
		btn.classList.toggle( 'is-paused', paused );
		btn.setAttribute( 'aria-pressed', String( paused ) );
		btn.setAttribute( 'aria-label', btn.getAttribute( paused ? 'data-label-play' : 'data-label-pause' ) );
	}
	if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		video.removeAttribute( 'autoplay' );
		video.pause();
	}
	btn.addEventListener( 'click', function () {
		if ( video.paused ) {
			var p = video.play();
			if ( p && p.catch ) { p.catch( sync ); }
		} else {
			video.pause();
		}
	} );
	video.addEventListener( 'play', sync );
	video.addEventListener( 'pause', sync );
	sync();
}() );

// Site Stats numbers (2000+, 70%, 55K+ — managed in Settings → Site Stats) count up from 0
// when they come into view, like the live site. Anything printed with cg_stat_number_html()
// gets the shared .cg-counter class, so this runs on both the homepage hero and the
// Hire a Codegirl page stats. Without JavaScript, or for visitors who ask for reduced
// motion, the final numbers are simply shown.
( function () {
	var nums = document.querySelectorAll( '.cg-counter' );
	if ( ! nums.length || ! window.requestAnimationFrame || ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) ) {
		return;
	}
	var DURATION = 4000;
	function setup( el ) {
		var m = /^(\D*)([\d,.]+)(.*)$/.exec( el.textContent.trim() );
		if ( ! m ) { return null; }
		var final = el.textContent.trim(), end = parseFloat( m[ 2 ].replace( /,/g, '' ) ), shown = document.createElement( 'span' ), sr = document.createElement( 'span' );
		if ( isNaN( end ) ) { return null; }
		// Screen readers get the final number once; the animated digits are hidden from them.
		sr.className = 'screen-reader-text';
		sr.textContent = final;
		shown.setAttribute( 'aria-hidden', 'true' );
		shown.textContent = m[ 1 ] + '0' + m[ 3 ];
		el.textContent = '';
		el.appendChild( shown );
		el.appendChild( sr );
		return { shown: shown, pre: m[ 1 ], post: m[ 3 ], end: end, decimals: ( m[ 2 ].split( '.' )[ 1 ] || '' ).length, final: final };
	}
	function run( s ) {
		var t0 = null;
		function tick( t ) {
			if ( null === t0 ) { t0 = t; }
			var p = Math.min( 1, ( t - t0 ) / DURATION ), eased = 1 - Math.pow( 1 - p, 3 ), v = s.end * eased;
			s.shown.textContent = 1 === p ? s.final : s.pre + v.toFixed( s.decimals ) + s.post;
			if ( p < 1 ) { window.requestAnimationFrame( tick ); }
		}
		window.requestAnimationFrame( tick );
	}
	var jobs = [];
	nums.forEach( function ( el ) { var s = setup( el ); if ( s ) { jobs.push( { el: el, s: s } ); } } );
	if ( 'IntersectionObserver' in window ) {
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( ! e.isIntersecting ) { return; }
				io.unobserve( e.target );
				jobs.forEach( function ( j ) { if ( j.el === e.target ) { run( j.s ); } } );
			} );
		}, { threshold: 0.4 } );
		jobs.forEach( function ( j ) { io.observe( j.el ); } );
	} else {
		jobs.forEach( function ( j ) { run( j.s ); } );
	}
}() );
