<?php
/**
 * Filters panel: a Section with the class vm-filters that holds the Filter,
 * Sort and Filter Reset blocks of a Kadence query.
 *
 * On phones the panel folds into one button, "Filter and sort", so the
 * fields do not push the results down the screen. It opens on a tap and
 * closes again once a choice is made.
 *
 * The rules are in style.css, "vm-filters". The button is added by
 * assets/js/filters.js: without the script the fields simply stay open. See
 * docs/components.md, "The Featured Deals page".
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the script only on pages that have a filters panel.
 *
 * @param string $html Rendered block.
 * @return string
 */
function vm_enqueue_filters( $html ) {
	if ( is_admin() || false === strpos( $html, 'vm-filters' ) || wp_script_is( 'velocity-kadence-filters' ) ) {
		return $html;
	}

	$file = VELOCITY_KADENCE_DIR . '/assets/js/filters.js';
	wp_enqueue_script(
		'velocity-kadence-filters',
		VELOCITY_KADENCE_URI . '/assets/js/filters.js',
		array(),
		file_exists( $file ) ? (string) filemtime( $file ) : null,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	wp_localize_script(
		'velocity-kadence-filters',
		'vmFilters',
		array(
			'label' => __( 'Filter and sort', 'velocity-kadence' ),
		)
	);

	return $html;
}
add_filter( 'render_block_kadence/column', 'vm_enqueue_filters' );
