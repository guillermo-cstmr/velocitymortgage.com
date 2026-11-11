/**
 * Starts row background videos once the page has finished loading.
 *
 * inc/background-video.php leaves the video without a source, so the poster
 * paints first and the video never competes with the page. Visitors who ask
 * for reduced motion or data saving keep the poster and get no play button.
 */
( function () {
	const videos = document.querySelectorAll( 'video[data-vm-src]' );
	if ( ! videos.length ) {
		return;
	}

	const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	const saveData = !! ( navigator.connection && navigator.connection.saveData );

	if ( reduceMotion || saveData ) {
		videos.forEach( ( video ) => {
			const buttons = video.parentNode.querySelector( '.kb-background-video-buttons-wrapper' );
			if ( buttons ) {
				buttons.hidden = true;
			}
		} );
		return;
	}

	const start = () => {
		videos.forEach( ( video ) => {
			video.muted = true;
			video.src = video.dataset.vmSrc;

			// Respect a click on Pause made before the video started.
			const play = video.parentNode.querySelector( '.kb-background-video-play' );
			if ( play && 'none' !== play.style.display ) {
				return;
			}

			const playing = video.play();
			if ( playing && playing.catch ) {
				playing.catch( () => {} );
			}
		} );
	};

	if ( 'complete' === document.readyState ) {
		start();
	} else {
		window.addEventListener( 'load', start, { once: true } );
	}
} )();
