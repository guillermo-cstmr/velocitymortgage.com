/**
 * The page of an article of News and Insights.
 *
 * 1. The newsletter bar (inc/insights.php prints it above the footer,
 *    hidden) shows once half of the article has been read. It shows once
 *    per visit: after it showed, or was closed, it stays away until the
 *    browser is closed. Keeping it at the bottom of the screen, and above
 *    the footer at the end of the page, is the stylesheet's work alone.
 *
 * 2. The "copy the link" icon of the share links copies the article's
 *    address and says so. Without this script it is a plain link.
 *
 * The rules are in style.css, "The page of an article".
 */
( function () {
	const KEY = 'vmNewsBarSeen';
	const bar = document.getElementById( 'vm-news-bar' );
	const article = document.querySelector( '.vm-article' );

	const seen = () => {
		try {
			return '1' === window.sessionStorage.getItem( KEY );
		} catch ( error ) {
			return false;
		}
	};

	const remember = () => {
		try {
			window.sessionStorage.setItem( KEY, '1' );
		} catch ( error ) {
			// Private browsing: the bar may show again on the next article.
		}
	};

	if ( bar && article && ! seen() ) {
		// While the bar is open, the page keeps what has the keyboard's focus
		// above it: a link reached with Tab is not left hidden under the bar.
		const clear = ( open ) => {
			document.documentElement.style.scrollPaddingBottom = open ? bar.offsetHeight + 16 + 'px' : '';
		};

		// The bar fades out first and leaves the page after: the footer moves up
		// into its place only once the bar can no longer be seen.
		const close = () => {
			bar.classList.add( 'is-closing' );
			window.setTimeout( () => {
				bar.hidden = true;
				bar.classList.remove( 'is-open', 'is-closing' );
				clear( false );
			}, 220 );
		};

		const check = () => {
			const box = article.getBoundingClientRect();
			const read = ( window.innerHeight - box.top ) / box.height;

			if ( read < 0.5 ) {
				return;
			}

			window.removeEventListener( 'scroll', check );
			remember();
			bar.hidden = false;
			// The next frame, so the bar slides in instead of appearing.
			window.requestAnimationFrame( () => {
				bar.classList.add( 'is-open' );
				clear( true );
			} );
		};

		window.addEventListener( 'scroll', check, { passive: true } );
		window.addEventListener( 'resize', () => {
			if ( bar.classList.contains( 'is-open' ) ) {
				clear( true );
			}
		} );
		bar.querySelector( '.vm-news-bar__close' ).addEventListener( 'click', close );
		document.addEventListener( 'keydown', ( event ) => {
			if ( 'Escape' === event.key && bar.classList.contains( 'is-open' ) && bar.contains( document.activeElement ) ) {
				close();
			}
		} );
	}

	document.addEventListener( 'click', ( event ) => {
		const link = event.target.closest && event.target.closest( '.vm-share__copy' );

		if ( ! link || ! navigator.clipboard ) {
			return;
		}

		event.preventDefault();
		navigator.clipboard.writeText( link.href ).then( () => {
			const label = link.getAttribute( 'aria-label' );

			link.classList.add( 'is-copied' );
			link.setAttribute( 'aria-label', link.dataset.copied || label );
			window.setTimeout( () => {
				link.classList.remove( 'is-copied' );
				link.setAttribute( 'aria-label', label );
			}, 2000 );
		} ).catch( () => {
			// The browser refused to copy. Nothing to do: the address is in the bar.
		} );
	} );
} )();
