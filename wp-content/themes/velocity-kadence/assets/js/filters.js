/**
 * Filters panel (a Section with the class vm-filters).
 *
 * Two things, both over Kadence's own blocks:
 *
 * 1. Every dropdown of the panel gets a menu drawn by the theme, in place of
 *    the browser's own list. The filters keep the look of a field; the Sort
 *    block becomes a pill, "Sort By". Kadence's dropdown stays in the page,
 *    hidden, and is what Kadence reads: choosing in the menu sets its value.
 *
 * 2. On phones the panel folds into one button, "Filter and sort". It closes
 *    by itself once a filter or a sort is chosen, so the results are in view
 *    again. The button shows how many filters are in use, so a closed panel
 *    does not hide that the list is filtered.
 *
 * The rules are in style.css, "vm-filters".
 */
( function () {
	const label = ( window.vmFilters && window.vmFilters.label ) || 'Filter and sort';
	let count = 0;
	let closeOpen = null;

	// One menu open at a time; a click anywhere else closes it.
	document.addEventListener( 'click', ( event ) => {
		if ( closeOpen && ! event.target.closest( '.vm-select' ) ) {
			closeOpen();
		}
	} );

	const menuFor = ( select ) => {
		const wrap = select.parentNode;
		const isSort = select.classList.contains( 'kb-sort' );
		const first = select.options[ 0 ];

		const button = document.createElement( 'button' );
		button.type = 'button';
		button.className = 'vm-select__button';
		button.setAttribute( 'aria-haspopup', 'listbox' );
		button.setAttribute( 'aria-expanded', 'false' );

		// The pill always reads "Sort By". A filter reads its choice.
		const text = () => ( isSort ? first : select.options[ select.selectedIndex ] || first ).text;
		button.textContent = text();

		const menu = document.createElement( 'ul' );
		menu.className = 'vm-select__menu';
		menu.id = 'vm-select-' + ++count;
		menu.hidden = true;
		menu.setAttribute( 'role', 'listbox' );
		menu.setAttribute( 'aria-label', select.getAttribute( 'aria-label' ) || first.text );
		button.setAttribute( 'aria-controls', menu.id );

		const items = [ ...select.options ]
			.filter( ( option ) => ! isSort || '' !== option.value )
			.map( ( option ) => {
				const item = document.createElement( 'li' );
				item.className = 'vm-select__option';
				item.textContent = option.text;
				item.dataset.value = option.value;
				item.tabIndex = -1;
				item.setAttribute( 'role', 'option' );
				menu.appendChild( item );
				return item;
			} );

		const close = ( focus ) => {
			menu.hidden = true;
			button.setAttribute( 'aria-expanded', 'false' );
			if ( closeOpen === close ) {
				closeOpen = null;
			}
			if ( true === focus ) {
				button.focus();
			}
		};

		const open = () => {
			if ( closeOpen ) {
				closeOpen();
			}
			// Read each time: the reset button empties the dropdown.
			items.forEach( ( item ) => item.setAttribute( 'aria-selected', item.dataset.value === select.value ? 'true' : 'false' ) );
			menu.hidden = false;
			button.setAttribute( 'aria-expanded', 'true' );
			closeOpen = close;
			( items.find( ( item ) => item.dataset.value === select.value ) || items[ 0 ] ).focus();
		};

		const choose = ( item ) => {
			select.value = item.dataset.value;
			button.textContent = text();
			close( true );
			select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		};

		button.addEventListener( 'click', () => ( menu.hidden ? open() : close() ) );

		menu.addEventListener( 'click', ( event ) => {
			const item = event.target.closest( '.vm-select__option' );
			if ( item ) {
				choose( item );
			}
		} );

		menu.addEventListener( 'keydown', ( event ) => {
			const at = items.indexOf( document.activeElement );
			const to = { ArrowDown: at + 1, ArrowUp: at - 1, Home: 0, End: items.length - 1 }[ event.key ];

			if ( undefined !== to ) {
				event.preventDefault();
				items[ Math.max( 0, Math.min( items.length - 1, to ) ) ].focus();
			} else if ( 'Enter' === event.key || ' ' === event.key ) {
				event.preventDefault();
				if ( at > -1 ) {
					choose( items[ at ] );
				}
			} else if ( 'Escape' === event.key ) {
				close( true );
			} else if ( 'Tab' === event.key ) {
				close();
			}
		} );

		select.after( button, menu );
		wrap.classList.add( 'vm-select' );
		wrap.classList.toggle( 'vm-select--sort', isSort );
	};

	document.querySelectorAll( '.vm-filters' ).forEach( ( panel, index ) => {
		const inner = panel.querySelector( ':scope > .kt-inside-inner-col' );
		if ( ! inner ) {
			return;
		}

		// Kadence redraws the filters after every change: a dropdown that is
		// new, and so has no menu yet, gets one.
		const menus = () => panel.querySelectorAll( 'select' ).forEach( ( select ) => {
			const button = select.nextElementSibling;
			if ( ! button || ! button.classList.contains( 'vm-select__button' ) ) {
				menuFor( select );
			}
		} );
		menus();
		new MutationObserver( menus ).observe( inner, { childList: true, subtree: true } );

		inner.id = inner.id || 'vm-filters-' + ( index + 1 );

		const button = document.createElement( 'button' );
		button.type = 'button';
		button.className = 'vm-filters__toggle';
		button.setAttribute( 'aria-expanded', 'false' );
		button.setAttribute( 'aria-controls', inner.id );

		const text = document.createElement( 'span' );
		button.appendChild( text );
		panel.insertBefore( button, inner );

		const update = () => {
			const used = [ ...panel.querySelectorAll( 'select' ) ].filter( ( field ) => '' !== field.value ).length;
			text.textContent = used ? label + ' (' + used + ')' : label;

			// The reset empties the dropdowns: the fields read their choice again.
			panel.querySelectorAll( '.vm-select:not(.vm-select--sort) > select' ).forEach( ( select ) => {
				const option = select.options[ select.selectedIndex ] || select.options[ 0 ];
				select.nextElementSibling.textContent = option.text;
			} );
		};

		const toggle = ( open ) => {
			panel.classList.toggle( 'is-open', open );
			button.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		};

		button.addEventListener( 'click', () => toggle( ! panel.classList.contains( 'is-open' ) ) );

		// A choice was made: fold the panel, once Kadence has redrawn it.
		panel.addEventListener( 'change', () => {
			toggle( false );
			window.setTimeout( update, 1200 );
		} );

		// A reset button: the panel's, or the one in the message for no results.
		( panel.closest( '.wp-block-kadence-query' ) || panel ).addEventListener( 'click', ( event ) => {
			if ( event.target.closest( '.wp-block-kadence-query-filter-reset' ) ) {
				toggle( false );
				window.setTimeout( update, 1200 );
			}
		} );

		panel.classList.add( 'vm-filters--folds' );
		update();

		// A link that is already filtered: Kadence sets the dropdowns from the
		// address once it starts, after this script.
		window.addEventListener( 'load', () => window.setTimeout( update, 300 ) );
	} );
} )();
