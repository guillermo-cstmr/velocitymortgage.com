/**
 * "Reveal lines" block style: each line of a heading slides up from behind
 * a mask, tilted a few degrees and straightening as it lands, one line after
 * the other, when the heading enters the screen.
 *
 * Every word is wrapped in a mask, and words are numbered by the line they
 * sit on, so the effect follows the real line breaks on every screen size.
 * Each word also starts lower the further right it sits on its line, so the
 * whole line looks rotated around its left end, not each word on its own.
 * The words stay inline, so the heading still wraps normally on resize.
 * See inc/animations.php and the "Reveal lines" rules in style.css.
 */
( function () {
	const headings = document.querySelectorAll( '.is-style-vm-reveal-lines' );
	if ( ! headings.length ) {
		return;
	}

	const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// Keep in sync with --vm-reveal-angle in style.css.
	const TILT = Math.tan( ( 6 * Math.PI ) / 180 );

	const reveal = ( heading ) => heading.classList.add( 'vm-reveal-in' );

	if ( reduceMotion || ! ( 'IntersectionObserver' in window ) ) {
		headings.forEach( ( heading ) => heading.classList.add( 'vm-reveal-ready', 'vm-reveal-in' ) );
		return;
	}

	const wrapWords = ( node ) => {
		[ ...node.childNodes ].forEach( ( child ) => {
			if ( Node.TEXT_NODE === child.nodeType ) {
				if ( ! child.textContent.trim() ) {
					return;
				}
				const fragment = document.createDocumentFragment();
				child.textContent.split( /(\s+)/ ).forEach( ( part ) => {
					if ( ! part ) {
						return;
					}
					if ( ! part.trim() ) {
						fragment.appendChild( document.createTextNode( part ) );
						return;
					}
					const mask = document.createElement( 'span' );
					const word = document.createElement( 'span' );
					mask.className = 'vm-line-mask';
					word.className = 'vm-line-word';
					word.textContent = part;
					mask.appendChild( word );
					fragment.appendChild( mask );
				} );
				child.replaceWith( fragment );
			} else if ( Node.ELEMENT_NODE === child.nodeType && 'BR' !== child.tagName ) {
				wrapWords( child );
			}
		} );
	};

	const prepare = ( heading ) => {
		// One accessible name for the whole heading, read as plain text.
		const copy = heading.cloneNode( true );
		copy.querySelectorAll( 'br' ).forEach( ( br ) => br.replaceWith( ' ' ) );
		heading.setAttribute( 'aria-label', copy.textContent.replace( /\s+/g, ' ' ).trim() );

		wrapWords( heading );

		let line = -1;
		let lastTop = null;
		let lineLeft = 0;
		heading.querySelectorAll( '.vm-line-mask' ).forEach( ( mask ) => {
			const box = mask.getBoundingClientRect();
			const top = Math.round( box.top );
			if ( null === lastTop || top > lastTop + 2 ) {
				line++;
				lastTop = top;
				lineLeft = box.left;
			}
			mask.firstChild.style.setProperty( '--vm-line', line );
			mask.firstChild.style.setProperty( '--vm-drop', Math.round( ( box.left - lineLeft ) * TILT ) + 'px' );
		} );

		heading.classList.add( 'vm-reveal-ready' );
	};

	headings.forEach( prepare );

	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					reveal( entry.target );
					observer.unobserve( entry.target );
				}
			} );
		},
		{ rootMargin: '0px 0px -10% 0px' }
	);

	// Wait a frame so the hidden starting position is painted first.
	window.requestAnimationFrame( () => headings.forEach( ( heading ) => observer.observe( heading ) ) );
} )();
