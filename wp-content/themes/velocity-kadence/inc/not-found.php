<?php
/**
 * The "page not found" screen.
 *
 * Its content is a Kadence Element, "404 page" (Kadence > Elements), built by
 * wp velocity seed-404 and edited with blocks.
 *
 * This file adds the one thing blocks cannot do: the link "previous page"
 * goes one step back in the browser's history. Any link with the class
 * vm-back does, whenever the tab has a page before this one, however the
 * visitor got here (a link, a bookmark, an address typed in). Only a tab that
 * opened on the broken address has nothing to go back to: there the link
 * keeps its own address, the home page.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print the script on the 404 screen only.
 */
function vm_not_found_back_link() {
	if ( ! is_404() ) {
		return;
	}

	$script = "document.addEventListener('click',function(e){var a=e.target.closest&&e.target.closest('a.vm-back');if(!a||e.defaultPrevented||e.button||e.metaKey||e.ctrlKey||e.shiftKey){return;}if(window.history.length>1){e.preventDefault();window.history.back();}});";

	wp_print_inline_script_tag( $script, array( 'id' => 'vm-back-link' ) );
}
add_action( 'wp_footer', 'vm_not_found_back_link' );
