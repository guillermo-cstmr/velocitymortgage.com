<?php
/**
 * The legal pages: Terms of Use and Privacy.
 *
 * Their content is blocks, built by wp velocity seed-legal-pages. The list
 * of sections is Kadence's Table of Contents with the class vm-legal-toc.
 *
 * This file loads assets/js/legal.js on the pages that have that list. The
 * script does two things the block does not: it closes the list of a phone
 * after a press on a link, and it lands the heading under the header.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the script only on a page that has the list of sections.
 *
 * @param string $html Rendered block.
 * @return string
 */
function vm_enqueue_legal( $html ) {
	if ( is_admin() || false === strpos( $html, 'vm-legal-toc' ) || wp_script_is( 'velocity-kadence-legal' ) ) {
		return $html;
	}

	$file = VELOCITY_KADENCE_DIR . '/assets/js/legal.js';
	wp_enqueue_script(
		'velocity-kadence-legal',
		VELOCITY_KADENCE_URI . '/assets/js/legal.js',
		array(),
		file_exists( $file ) ? (string) filemtime( $file ) : null,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	return $html;
}
add_filter( 'render_block_kadence/tableofcontents', 'vm_enqueue_legal' );

/**
 * A table that scrolls sideways on a small screen (vm-legal-table, and
 * vm-compare on the program pages) has to be reachable without a finger:
 * with the keyboard it takes the focus, so the arrow keys scroll it, and a
 * screen reader announces it as a region with a name.
 *
 * @param string $html Rendered block.
 * @return string
 */
function vm_scrollable_table( $html ) {
	if ( false === strpos( $html, 'vm-legal-table' ) && false === strpos( $html, 'vm-compare' ) ) {
		return $html;
	}

	$tags = new WP_HTML_Tag_Processor( $html );

	if ( $tags->next_tag( array( 'class_name' => 'kb-table-container' ) ) ) {
		$tags->set_attribute( 'tabindex', '0' );
		$tags->set_attribute( 'role', 'region' );
		$tags->set_attribute( 'aria-label', __( 'Table. On a small screen, scroll sideways to see all of it.', 'velocity-kadence' ) );
	}

	return $tags->get_updated_html();
}
add_filter( 'render_block_kadence/table', 'vm_scrollable_table' );
