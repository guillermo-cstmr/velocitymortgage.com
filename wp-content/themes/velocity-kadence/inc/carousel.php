<?php
/**
 * Carousel: a Section with the class vm-carousel whose list scrolls
 * sideways, with a previous and a next arrow when its items do not fit. The
 * list is a Kadence query, or a Section with the class vm-carousel-track
 * that holds the cards.
 *
 * The rules are in style.css, "vm-carousel". The arrows are added by
 * assets/js/carousel.js. See docs/components.md, "Carousel".
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the script only on pages that have a carousel.
 *
 * @param string $html Rendered block.
 * @return string
 */
function vm_enqueue_carousel( $html ) {
	if ( is_admin() || false === strpos( $html, 'vm-carousel' ) || wp_script_is( 'velocity-kadence-carousel' ) ) {
		return $html;
	}

	$file = VELOCITY_KADENCE_DIR . '/assets/js/carousel.js';
	wp_enqueue_script(
		'velocity-kadence-carousel',
		VELOCITY_KADENCE_URI . '/assets/js/carousel.js',
		array(),
		file_exists( $file ) ? (string) filemtime( $file ) : null,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	wp_localize_script(
		'velocity-kadence-carousel',
		'vmCarousel',
		array(
			'arrow'    => VELOCITY_KADENCE_URI . '/assets/images/icon-arrow-back.svg',
			'previous' => __( 'Previous', 'velocity-kadence' ),
			'next'     => __( 'Next', 'velocity-kadence' ),
		)
	);

	return $html;
}
add_filter( 'render_block_kadence/column', 'vm_enqueue_carousel' );
