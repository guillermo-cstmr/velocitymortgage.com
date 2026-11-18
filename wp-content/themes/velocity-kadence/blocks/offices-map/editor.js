/**
 * Offices map: editor.
 *
 * The offices are listed in the block's settings, in the sidebar. Each one has
 * a city, an address, and the latitude and longitude of that address. When an
 * address is typed or changed, the editor asks the site for its coordinates
 * (inc/offices.php, /velocity/v1/geocode) and fills them in.
 *
 * The canvas shows the real map, rendered by render.php, so a pin is seen in
 * its place as soon as it has coordinates. The preview is wrapped in Disabled:
 * the cards only open on the site.
 */
( function ( blocks, element, serverSideRender, blockEditor, components, apiFetch, url ) {
	'use strict';

	var el = element.createElement;
	var useState = element.useState;

	function Edit( props ) {
		var offices = props.attributes.offices || [];
		// The list as it is now, for answers that arrive after the editor moved on.
		var latest = element.useRef( offices );
		latest.current = offices;
		var state = useState( {} );
		var status = state[ 0 ];
		var setStatus = state[ 1 ];
		// Which office panels are open. Kept here, not left to the panel: its own
		// default would close a new office as soon as its city got a first letter.
		var openState = useState( {} );
		var open = openState[ 0 ];
		var setOpen = openState[ 1 ];

		function save( next ) {
			props.setAttributes( { offices: next } );
		}

		function change( index, values ) {
			save(
				latest.current.map( function ( office, i ) {
					return i === index ? Object.assign( {}, office, values ) : office;
				} )
			);
		}

		function note( index, text ) {
			var next = Object.assign( {}, status );
			next[ index ] = text;
			setStatus( next );
		}

		function move( index, by ) {
			var next = offices.slice();
			var office = next.splice( index, 1 )[ 0 ];
			next.splice( index + by, 0, office );
			save( next );
			setStatus( {} );
			var moved = {};
			moved[ index + by ] = true;
			setOpen( moved );
		}

		// Looks the address up, unless its coordinates are already the ones for it.
		function locate( index, always ) {
			var office = offices[ index ];
			var address = ( office.address || '' ).trim();

			if ( ! address || ( ! always && office.located === address && office.lat && office.lng ) ) {
				return;
			}

			note( index, 'Looking up the address…' );

			apiFetch( { path: url.addQueryArgs( '/velocity/v1/geocode', { address: address } ) } )
				.then( function ( result ) {
					if ( result && result.found ) {
						change( index, { lat: String( result.lat ), lng: String( result.lng ), located: address } );
						note(
							index,
							'city' === result.precision
								? 'The street was not found, so the pin is on the city. On this map that is the same place.'
								: 'Address found. The pin is in place.'
						);
					} else {
						change( index, { located: '' } );
						note( index, 'Address not found. Check it, or type the latitude and longitude by hand.' );
					}
				} )
				.catch( function () {
					note( index, 'The address could not be looked up right now. Try again, or type the latitude and longitude by hand.' );
				} );
		}

		function panel( office, index ) {
			var hasPin = office.lat && office.lng;

			return el(
				components.PanelBody,
				{
					key: index,
					title: office.city || 'New office',
					opened: !! open[ index ],
					onToggle: function () {
						var next = Object.assign( {}, open );
						next[ index ] = ! open[ index ];
						setOpen( next );
					},
				},
				el(
					'div',
					{ style: { display: 'flex', flexDirection: 'column', gap: '16px' } },
				el( components.TextControl, {
					label: 'City',
					help: 'The title of the card. For example: Las Vegas, NV',
					value: office.city || '',
					onChange: function ( value ) {
						change( index, { city: value } );
					},
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				} ),
				el( components.TextareaControl, {
					label: 'Address',
					help: 'As it should read on the card, one line per row: street and suite first, then city, state and ZIP code. The pin is placed from it.',
					rows: 3,
					value: office.address || '',
					onChange: function ( value ) {
						change( index, { address: value } );
					},
					onBlur: function () {
						locate( index, false );
					},
					__nextHasNoMarginBottom: true,
				} ),
				status[ index ]
					? el( 'p', { style: { margin: 0 }, role: 'status' }, status[ index ] )
					: ( ! hasPin && office.address ? el( 'p', { style: { margin: 0 } }, 'This office has no pin yet.' ) : null ),
				el(
					components.Button,
					{
						variant: 'secondary',
						style: { alignSelf: 'flex-start' },
						disabled: ! ( office.address || '' ).trim(),
						onClick: function () {
							locate( index, true );
						},
					},
					'Look up the address'
				),
				el( components.TextControl, {
					label: 'Latitude',
					value: office.lat || '',
					onChange: function ( value ) {
						change( index, { lat: value } );
					},
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				} ),
				el( components.TextControl, {
					label: 'Longitude',
					help: 'Both are filled in from the address. To type them by hand: on Google Maps, right click the building and click the two numbers to copy them.',
					value: office.lng || '',
					onChange: function ( value ) {
						change( index, { lng: value } );
					},
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				} ),
				el( components.TextControl, {
					label: 'Move pin right',
					type: 'number',
					help: 'Optional. Only to separate pins that sit on top of each other. In pixels of the full size map, which is 904 wide. A negative number moves it left.',
					value: office.moveRight || '',
					onChange: function ( value ) {
						change( index, { moveRight: value } );
					},
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				} ),
				el( components.TextControl, {
					label: 'Move pin down',
					type: 'number',
					help: 'Optional. A negative number moves it up.',
					value: office.moveDown || '',
					onChange: function ( value ) {
						change( index, { moveDown: value } );
					},
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				} ),
				el(
					components.Flex,
					{ justify: 'flex-start', wrap: true },
					el(
						components.Button,
						{
							variant: 'tertiary',
							disabled: 0 === index,
							onClick: function () {
								move( index, -1 );
							},
						},
						'Move up'
					),
					el(
						components.Button,
						{
							variant: 'tertiary',
							disabled: index === offices.length - 1,
							onClick: function () {
								move( index, 1 );
							},
						},
						'Move down'
					),
					el(
						components.Button,
						{
							variant: 'tertiary',
							isDestructive: true,
							onClick: function () {
								save(
									offices.filter( function ( office, i ) {
										return i !== index;
									} )
								);
								setStatus( {} );
								setOpen( {} );
							},
						},
						'Remove'
					)
				)
				)
			);
		}

		var named = offices.filter( function ( office ) {
			return ( office.city || '' ).trim();
		} );

		return el(
			'div',
			blockEditor.useBlockProps(),
			el(
				blockEditor.InspectorControls,
				null,
				el(
					element.Fragment,
					null,
					offices.map( panel ),
					el(
						components.PanelBody,
						{ title: 'Add an office', initialOpen: true },
						el(
							components.Button,
							{
								variant: 'primary',
								onClick: function () {
									save( offices.concat( [ { city: '', address: '', lat: '', lng: '' } ] ) );
									var added = {};
									added[ offices.length ] = true;
									setOpen( added );
								},
							},
							'Add office'
						)
					)
				)
			),
			named.length
				? el(
					components.Disabled,
					null,
					el( serverSideRender, { block: 'velocity/offices-map', attributes: props.attributes } )
				)
				: el(
					components.Placeholder,
					{
						icon: 'location-alt',
						label: 'Offices Map',
						instructions: 'Add the offices in the block settings, in the sidebar. Each one gets a pin on the map, placed from its address.',
					}
				)
		);
	}

	blocks.registerBlockType( 'velocity/offices-map', {
		edit: Edit,
		save: function () {
			return null;
		},
	} );
}( window.wp.blocks, window.wp.element, window.wp.serverSideRender, window.wp.blockEditor, window.wp.components, window.wp.apiFetch, window.wp.url ) );
