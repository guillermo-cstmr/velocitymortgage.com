/**
 * The page of an article of News and Insights.
 *
 * 1. The newsletter bar (inc/insights.php prints it, hidden) comes up from
 *    the bottom of the screen once half of the article has been read. It
 *    shows once per visit: after it showed, or was closed, it stays away
 *    until the browser is closed. It also stays away after a sign-up.
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
		const close = () => {
			bar.classList.remove( 'is-open' );
			window.setTimeout( () => {
				bar.hidden = true;
			}, 300 );
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
			window.requestAnimationFrame( () => bar.classList.add( 'is-open' ) );
		};

		window.addEventListener( 'scroll', check, { passive: true } );
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
		} );
	} );
} )();
