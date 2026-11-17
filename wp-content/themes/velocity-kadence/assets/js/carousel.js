/**
 * Carousel: a Section with the class vm-carousel whose Query Loop scrolls
 * sideways.
 *
 * The list already scrolls on its own, by touch, trackpad or keyboard. This
 * script adds a previous and a next button when the items do not fit, and
 * removes them when they do. Each button moves the list by the items in view
 * and is switched off at its end of the list.
 *
 * The address of the arrow image and the two button labels come from
 * inc/carousel.php, in window.vmCarousel.
 */
( function () {
	'use strict';

	var settings = window.vmCarousel || {};

	function setUp( carousel ) {
		var track = carousel.querySelector( '.wp-block-post-template' );
		var inner = carousel.querySelector( ':scope > .kt-inside-inner-col' ) || carousel;

		if ( ! track ) {
			return;
		}

		// The list can be scrolled with the arrow keys once it has focus.
		track.setAttribute( 'tabindex', '0' );

		var arrows = document.createElement( 'div' );
		arrows.className = 'vm-carousel__arrows';

		function button( direction, label ) {
			var el = document.createElement( 'button' );
			el.type = 'button';
			el.className = 'vm-carousel__arrow vm-carousel__arrow--' + direction;
			el.setAttribute( 'aria-label', label );

			var img = document.createElement( 'img' );
			img.src = settings.arrow || '';
			img.alt = '';
			img.width = 24;
			img.height = 24;
			el.appendChild( img );

			el.addEventListener( 'click', function () {
				var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
				track.scrollBy( {
					left: ( 'next' === direction ? 1 : -1 ) * track.clientWidth,
					behavior: reduce ? 'auto' : 'smooth',
				} );
			} );

			arrows.appendChild( el );
			return el;
		}

		var previous = button( 'previous', settings.previous || 'Previous' );
		var next = button( 'next', settings.next || 'Next' );

		function update() {
			var room = track.scrollWidth - track.clientWidth;

			// Everything fits: no arrows.
			if ( room <= 1 ) {
				if ( arrows.parentNode ) {
					arrows.parentNode.removeChild( arrows );
				}
				return;
			}

			if ( ! arrows.parentNode ) {
				inner.appendChild( arrows );
			}

			previous.disabled = track.scrollLeft <= 1;
			next.disabled = track.scrollLeft >= room - 1;
		}

		track.addEventListener( 'scroll', update, { passive: true } );
		window.addEventListener( 'resize', update );
		update();
	}

	Array.prototype.forEach.call( document.querySelectorAll( '.vm-carousel' ), setUp );
}() );
