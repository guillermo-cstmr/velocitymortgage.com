/**
 * Frequently asked questions: Details blocks with the class vm-faq.
 *
 * Within a group of them, opening one closes the others, and the open one
 * cannot be closed by clicking it again. So one answer is always visible.
 *
 * A group is every vm-faq that shares the same parent element. Without this
 * script the blocks still work as normal, independent Details blocks.
 */
( function () {
	'use strict';

	document.addEventListener( 'click', function ( event ) {
		var summary = event.target.closest( 'details.vm-faq > summary' );

		if ( ! summary ) {
			return;
		}

		var current = summary.parentElement;

		// Already open: keep it open.
		if ( current.open ) {
			event.preventDefault();
			return;
		}

		// About to open: close its siblings.
		Array.prototype.forEach.call( current.parentElement.children, function ( sibling ) {
			if ( sibling !== current && sibling.matches( 'details.vm-faq' ) ) {
				sibling.open = false;
			}
		} );
	} );
}() );
