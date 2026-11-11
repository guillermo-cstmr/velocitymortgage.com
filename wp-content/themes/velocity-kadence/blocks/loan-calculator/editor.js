/**
 * Loan scenario calculator: editor preview.
 *
 * The block has no settings. The editor shows the real markup, rendered by
 * render.php, so it looks the same as on the site. The rules and the copy are
 * changed in config.php, not in the editor.
 */
( function ( blocks, element, serverSideRender, blockEditor ) {
	'use strict';

	var el = element.createElement;

	blocks.registerBlockType( 'velocity/loan-calculator', {
		edit: function () {
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( serverSideRender, { block: 'velocity/loan-calculator' } )
			);
		},
		save: function () {
			return null;
		},
	} );
}( window.wp.blocks, window.wp.element, window.wp.serverSideRender, window.wp.blockEditor ) );
