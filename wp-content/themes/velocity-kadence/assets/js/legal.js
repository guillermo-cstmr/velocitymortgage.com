/**
 * The list of sections of a legal page (Kadence's Table of Contents with the
 * class vm-legal-toc). Two things the block does not do:
 *
 * 1. On a phone and a tablet the list is a bar that opens on a press
 *    (vm-legal-toc--bar). It closes again when one of its links is pressed.
 *
 * 2. A press on a link lands on the heading. Kadence works out where to
 *    scroll before it scrolls, but the header gets shorter as soon as the
 *    page moves, so from the top of the page the heading ended up under the
 *    header. Once the scroll stops, this puts the heading in its place.
 */
( function () {
	'use strict';

	// Space between the heading and what covers the top of the screen.
	var AIR = 24;

	/**
	 * Where the free part of the screen starts: under the fixed header and,
	 * on a phone and a tablet, under the bar.
	 *
	 * @return {number} Pixels from the top of the screen.
	 */
	function coveredTop() {
		var top = 0;

		document.querySelectorAll( '.kb-header-container, .vm-legal-toc--bar' ).forEach( function ( el ) {
			var style = window.getComputedStyle( el );
			var rect = el.getBoundingClientRect();
			var holds = 'fixed' === style.position || 'sticky' === style.position || el.classList.contains( 'vm-legal-toc--bar' );

			// Only what sits at the top of the screen counts.
			if ( holds && rect.height > 0 && rect.top < 200 && rect.bottom > top ) {
				top = rect.bottom;
			}
		} );

		return top;
	}

	/**
	 * Wait until the page stops scrolling, then run a function.
	 *
	 * @param {Function} done What to run.
	 */
	function whenScrollStops( done ) {
		var last = window.scrollY;
		var still = 0;
		var tries = 0;
		var timer = window.setInterval( function () {
			tries++;
			still = window.scrollY === last ? still + 1 : 0;
			last = window.scrollY;

			if ( still >= 3 || tries > 80 ) {
				window.clearInterval( timer );
				done();
			}
		}, 50 );
	}

	// In the capture phase, so the bar closes before Kadence measures.
	document.addEventListener( 'click', function ( event ) {
		var link = event.target.closest ? event.target.closest( '.vm-legal-toc a[href^="#"]' ) : null;
		var bar, toggle, target;

		if ( ! link ) {
			return;
		}

		bar = link.closest( '.vm-legal-toc--bar.kb-toc-toggle-active' );
		toggle = bar ? bar.querySelector( '.kb-table-of-contents-toggle' ) : null;
		if ( toggle ) {
			toggle.click();
		}

		target = document.getElementById( decodeURIComponent( link.getAttribute( 'href' ).slice( 1 ) ) );
		if ( ! target ) {
			return;
		}

		// Let the scroll start before watching for its end.
		window.setTimeout( function () {
			whenScrollStops( function () {
				var off = target.getBoundingClientRect().top - ( coveredTop() + AIR );

				if ( Math.abs( off ) > 4 ) {
					window.scrollBy( { top: off, left: 0, behavior: 'smooth' } );
				}
			} );
		}, 150 );
	}, true );
}() );
