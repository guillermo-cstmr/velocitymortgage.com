/**
 * Mobile and tablet menu (Kadence Off Canvas) below the sticky header.
 *
 * - The panel opens under the header instead of covering it. The header's
 *   height changes as it shrinks on scroll, so its bottom edge is measured
 *   each time the menu opens and saved as --vm-header-bottom (style.css,
 *   "Header").
 * - Kadence's trigger only opens the menu. Since the hamburger stays visible
 *   above the panel, a click on it while the menu is open closes it, and the
 *   icon turns into an X (style.css).
 * - The menu opens with the submenu of the page being viewed already open.
 *   Kadence opens it when the page loads, then closes every submenu on the
 *   next click anywhere else, and the click on the hamburger is one.
 */
( function () {
	const triggers = document.querySelectorAll( '.wp-block-kadence-header .wp-block-kadence-off-canvas-trigger' );
	if ( ! triggers.length ) {
		return;
	}

	const root = document.documentElement;
	const visible = ( el ) => el.getBoundingClientRect().width > 0;

	const measure = () => {
		const trigger = [ ...triggers ].find( visible );
		if ( ! trigger ) {
			return;
		}
		const bar = trigger.closest( '.kb-header-sticky-wrapper' ) || trigger.closest( '.wp-block-kadence-header' );
		const bottom = Math.max( 0, Math.round( bar.getBoundingClientRect().bottom ) );
		root.style.setProperty( '--vm-header-bottom', bottom + 'px' );
	};

	const openCurrent = () => {
		document.querySelectorAll( '.kb-off-canvas-inner .wp-block-kadence-navigation .menu-item-has-children' ).forEach( ( item ) => {
			const submenu = item.querySelector( ':scope > ul.sub-menu' );
			if ( ! submenu || ! item.querySelector( '.current-menu-item' ) ) {
				return;
			}
			item.classList.add( 'menu-item--toggled-on' );
			submenu.classList.add( 'toggle-show' );
			const toggle = item.querySelector( ':scope > .kb-link-wrap > .kb-nav-dropdown-toggle-btn' );
			if ( toggle ) {
				toggle.setAttribute( 'aria-expanded', 'true' );
			}
		} );
	};

	triggers.forEach( ( trigger ) => {
		// Capture phase: runs before Kadence's own click handler.
		trigger.addEventListener(
			'click',
			( event ) => {
				if ( 'true' === trigger.getAttribute( 'aria-expanded' ) ) {
					event.stopImmediatePropagation();
					const header = trigger.closest( '.wp-block-kadence-header' );
					const close = header && header.querySelector( '.kb-off-canvas-close' );
					if ( close ) {
						close.click();
					}
					return;
				}
				measure();
				// After Kadence has closed the submenus for this same click.
				window.setTimeout( openCurrent, 30 );
			},
			true
		);
	} );

	window.addEventListener( 'resize', measure );
} )();
