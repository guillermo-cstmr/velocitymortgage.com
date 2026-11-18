/**
 * Carousel: a Section with the class vm-carousel whose list scrolls
 * sideways. The list is a Kadence query, or a Section with the class
 * vm-carousel-track that holds the cards.
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
		// The list: a Kadence query's, or a Section that holds the cards.
		var track = carousel.querySelector( '.kb-query-grid-wrap, .vm-carousel-track > .kt-inside-inner-col' );
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
					left: ( 'next' === direction ? 1 : -1 ) * step(),
					behavior: reduce ? 'auto' : 'smooth',
				} );
			} );

			arrows.appendChild( el );
			return el;
		}

		// One card and its gap when cards are wider than half the list, or a
		// full view of cards otherwise.
		function step() {
			var first = track.firstElementChild;
			var second = first && first.nextElementSibling;
			var card = first && second ? second.getBoundingClientRect().left - first.getBoundingClientRect().left : 0;

			return card > track.clientWidth / 2 ? card : track.clientWidth;
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
