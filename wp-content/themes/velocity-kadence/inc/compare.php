<?php
/**
 * Comparison table: a Kadence Table with the classes vm-compare
 * vm-compare--pick shows two of its programs at a time on a phone, picked
 * with two menus above it.
 *
 * The rules are in style.css, "vm-compare--pick". The menus are added by
 * assets/js/compare.js. See docs/components.md, "Programs Overview".
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the script only on pages that have such a table.
 *
 * @param string $html Rendered block.
 * @return string
 */
function vm_enqueue_compare( $html ) {
	if ( is_admin() || false === strpos( $html, 'vm-compare--pick' ) || wp_script_is( 'velocity-kadence-compare' ) ) {
		return $html;
	}

	$file = VELOCITY_KADENCE_DIR . '/assets/js/compare.js';
	wp_enqueue_script(
		'velocity-kadence-compare',
		VELOCITY_KADENCE_URI . '/assets/js/compare.js',
		array(),
		file_exists( $file ) ? (string) filemtime( $file ) : null,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	wp_localize_script(
		'velocity-kadence-compare',
		'vmCompare',
		array(
			'first'  => __( 'First program to compare', 'velocity-kadence' ),
			'second' => __( 'Second program to compare', 'velocity-kadence' ),
		)
	);

	return $html;
}
add_filter( 'render_block_kadence/table', 'vm_enqueue_compare' );
