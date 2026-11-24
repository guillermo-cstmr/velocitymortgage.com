/**
 * Comparison table on a phone: two programs at a time.
 *
 * A Kadence Table with the classes vm-compare vm-compare--pick has one
 * column per program. On a phone they do not fit, so the visitor picks two
 * with the menus this script adds above the table, and the table shows those
 * two side by side, with the name of each row above its values.
 *
 * Without this script, and on wider screens, the table is the one the block
 * prints: every column, scrolling sideways where it does not fit.
 *
 * The rules are in style.css, "vm-compare--pick". See docs/components.md.
 */
( function () {
	const phone = window.matchMedia( '(max-width: 767px)' );
	const text = window.vmCompare || {};

	const setup = ( box ) => {
		const table = box.querySelector( 'table' );
		const rows = table ? Array.from( table.rows ) : [];

		// The first row names the programs, after the cell that names the rows.
		const names = rows.length ? Array.from( rows[ 0 ].cells ).slice( 1 ).map( ( cell ) => cell.textContent.trim() ) : [];

		if ( names.length < 3 ) {
			return;
		}

		// The table is drawn as a grid on a phone, which makes some browsers
		// forget it is a table. The roles say it again.
		table.setAttribute( 'role', 'table' );
		Array.from( table.tBodies ).forEach( ( body ) => body.setAttribute( 'role', 'rowgroup' ) );
		rows.forEach( ( row ) => {
			row.setAttribute( 'role', 'row' );
			Array.from( row.cells ).forEach( ( cell ) => {
				const scope = cell.getAttribute( 'scope' );
				cell.setAttribute( 'role', 'col' === scope ? 'columnheader' : 'row' === scope ? 'rowheader' : 'cell' );
			} );
		} );

		// What the server set for a table that scrolls sideways.
		const region = [ 'tabindex', 'role', 'aria-label' ].map( ( name ) => [ name, box.getAttribute( name ) ] );

		const picked = [ 1, 2 ];
		const bar = document.createElement( 'div' );
		bar.className = 'vm-compare-bar';
		bar.hidden = true;

		const selects = [ 0, 1 ].map( ( slot ) => {
			const field = document.createElement( 'label' );
			const label = document.createElement( 'span' );
			const select = document.createElement( 'select' );

			field.className = 'vm-compare-bar__field';
			label.className = 'screen-reader-text';
			label.textContent = slot ? ( text.second || 'Second program to compare' ) : ( text.first || 'First program to compare' );
			names.forEach( ( name, index ) => select.add( new Option( name, String( index + 1 ) ) ) );
			select.value = String( picked[ slot ] );
			field.append( label, select );
			bar.append( field );

			return select;
		} );

		box.parentNode.insertBefore( bar, box );

		const draw = () => {
			const two = phone.matches;

			bar.hidden = ! two;
			box.classList.toggle( 'vm-compare--two', two );

			// A table that shows two columns does not scroll sideways.
			region.forEach( ( [ name, value ] ) => {
				if ( two || null === value ) {
					box.removeAttribute( name );
				} else {
					box.setAttribute( name, value );
				}
			} );

			rows.forEach( ( row ) => {
				Array.from( row.cells ).forEach( ( cell, index ) => {
					if ( ! index ) {
						return;
					}

					const slot = picked.indexOf( index );

					cell.classList.toggle( 'vm-compare-off', two && slot < 0 );
					cell.style.order = two && slot >= 0 ? String( slot + 1 ) : '';
				} );
			} );
		};

		selects.forEach( ( select, slot ) => {
			select.addEventListener( 'change', () => {
				const value = Number( select.value );
				const other = 1 - slot;

				// The program is already in the other menu: the two swap, so
				// the same program is never shown twice.
				if ( picked[ other ] === value ) {
					picked[ other ] = picked[ slot ];
					selects[ other ].value = String( picked[ other ] );
				}

				picked[ slot ] = value;
				draw();
			} );
		} );

		// The menus stay in view while the table scrolls under them. The site's
		// header comes back over the top of the screen when the visitor
		// scrolls up: the menus then sit right under it instead of behind it.
		const headers = Array.from( document.querySelectorAll( 'header .kb-header-container' ) );
		let waiting = false;

		const place = () => {
			waiting = false;

			if ( bar.hidden ) {
				return;
			}

			let top = 0;

			headers.forEach( ( header ) => {
				const rect = header.getBoundingClientRect();

				if ( 'fixed' === window.getComputedStyle( header ).position && rect.height && rect.bottom > top ) {
					top = rect.bottom;
				}
			} );

			bar.style.setProperty( '--vm-compare-top', Math.round( top ) + 'px' );
		};

		window.addEventListener(
			'scroll',
			() => {
				if ( ! waiting ) {
					waiting = true;
					window.requestAnimationFrame( place );
				}
			},
			{ passive: true }
		);

		phone.addEventListener( 'change', draw );
		draw();
	};

	const init = () => document.querySelectorAll( '.kb-table-container.vm-compare--pick' ).forEach( setup );

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
