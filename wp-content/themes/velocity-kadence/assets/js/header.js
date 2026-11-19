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

/**
 * Desktop menu: a dropdown opens under the mouse as well as on a click.
 *
 * "Broker Programs", "Resources" and "Company" open on a click ("Open
 * dropdown on click", in the link's settings), which is what a finger and the
 * keyboard need. Kadence offers a click or the mouse, not both. This adds the
 * mouse: the pointer over the item opens its dropdown, and leaving it closes
 * it. Both go through Kadence's own toggle button, so the dropdown is the
 * same one and its state is announced the same way.
 *
 * While the mouse holds a dropdown open, a click on its name leaves it open:
 * without this, the click of someone used to clicking would close it.
 *
 * Only a mouse counts (not a finger or a pen), and only the horizontal menu:
 * the mobile and tablet menu is vertical and stays click only.
 */
( function () {
	const items = document.querySelectorAll( '.wp-block-kadence-header .wp-block-kadence-navigation .kb-nav-link-sub-click' );

	items.forEach( ( item ) => {
		const toggle = item.querySelector( ':scope > .kb-link-wrap > .kb-nav-dropdown-toggle-btn' );
		const name = item.querySelector( ':scope > .kb-link-wrap > .kb-nav-link-content' );
		if ( ! toggle || ! name ) {
			return;
		}

		let held = false;
		const horizontal = () => ! item.closest( '.is-vertical' ) && ! item.closest( '.kb-off-canvas-inner' );
		const open = () => item.classList.contains( 'menu-item--toggled-on' );

		item.addEventListener( 'pointerenter', ( event ) => {
			if ( 'mouse' !== event.pointerType || ! horizontal() ) {
				return;
			}
			held = true;
			if ( ! open() ) {
				toggle.click();
			}
		} );

		item.addEventListener( 'pointerleave', ( event ) => {
			if ( 'mouse' !== event.pointerType || ! held ) {
				return;
			}
			held = false;
			if ( open() ) {
				toggle.click();
			}
		} );

		// Capture phase: runs before Kadence's own click handler.
		name.addEventListener(
			'click',
			( event ) => {
				if ( held && open() ) {
					event.preventDefault();
					event.stopImmediatePropagation();
				}
			},
			true
		);
	} );
} )();
