/**
 * Offices map: shows an office's card when its pin is hovered, focused or
 * clicked. A click keeps the card open until another pin is clicked, the
 * Escape key is pressed, or the visitor clicks elsewhere.
 *
 * On small screens the cards are listed under the map (style.css), so the
 * pins are only decoration there and are taken out of the keyboard order.
 */
( function () {
	'use strict';

	var small = window.matchMedia( '(max-width: 767px)' );

	document.querySelectorAll( '.vm-offices' ).forEach( function ( map ) {
		var pins = Array.prototype.slice.call( map.querySelectorAll( '.vm-offices__pin' ) );
		var kept = null;

		function show( pin, on ) {
			pin.classList.toggle( 'is-open', on );
			pin.querySelector( '.vm-offices__pin-button' ).setAttribute( 'aria-expanded', on ? 'true' : 'false' );
		}

		function closeAll( except ) {
			pins.forEach( function ( pin ) {
				if ( pin !== except ) {
					show( pin, false );
				}
			} );
		}

		pins.forEach( function ( pin ) {
			var button = pin.querySelector( '.vm-offices__pin-button' );

			pin.addEventListener( 'mouseenter', function () {
				if ( small.matches ) {
					return;
				}
				closeAll( pin );
				show( pin, true );
			} );

			pin.addEventListener( 'mouseleave', function () {
				if ( kept !== pin ) {
					show( pin, false );
				}
				if ( kept ) {
					show( kept, true );
				}
			} );

			button.addEventListener( 'focus', function () {
				if ( small.matches ) {
					return;
				}
				closeAll( pin );
				show( pin, true );
			} );

			button.addEventListener( 'blur', function () {
				if ( kept !== pin ) {
					show( pin, false );
				}
			} );

			button.addEventListener( 'click', function () {
				if ( small.matches ) {
					return;
				}
				kept = kept === pin ? null : pin;
				closeAll( pin );
				show( pin, true );
			} );
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( kept && ! kept.contains( event.target ) ) {
				kept = null;
				closeAll( null );
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key ) {
				kept = null;
				closeAll( null );
			}
		} );

		// On small screens the pins cannot be focused or announced: the list has the same content.
		function setReach() {
			pins.forEach( function ( pin ) {
				var button = pin.querySelector( '.vm-offices__pin-button' );

				if ( small.matches ) {
					button.setAttribute( 'tabindex', '-1' );
					button.setAttribute( 'aria-hidden', 'true' );
					show( pin, false );
				} else {
					button.removeAttribute( 'tabindex' );
					button.removeAttribute( 'aria-hidden' );
				}
			} );

			if ( small.matches ) {
				kept = null;
			}
		}

		setReach();
		if ( small.addEventListener ) {
			small.addEventListener( 'change', setReach );
		}
	} );
}() );
