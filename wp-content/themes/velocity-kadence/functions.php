<?php
/**
 * Velocity Kadence child theme.
 *
 * Keep this file small. Each concern lives in its own file under inc/.
 * Custom post types and fields do NOT belong here: they live in the
 * must-use plugin so content survives a theme switch.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

define( 'VELOCITY_KADENCE_DIR', get_stylesheet_directory() );
define( 'VELOCITY_KADENCE_URI', get_stylesheet_directory_uri() );

require_once VELOCITY_KADENCE_DIR . '/inc/tokens.php';
require_once VELOCITY_KADENCE_DIR . '/inc/styleguide.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once VELOCITY_KADENCE_DIR . '/inc/class-velocity-cli.php';
}

/**
 * Load the child theme stylesheet after Kadence's own styles.
 */
function vm_enqueue_styles() {
	$file = VELOCITY_KADENCE_DIR . '/style.css';

	wp_enqueue_style(
		'velocity-kadence',
		VELOCITY_KADENCE_URI . '/style.css',
		array(),
		file_exists( $file ) ? (string) filemtime( $file ) : null
	);
}
add_action( 'wp_enqueue_scripts', 'vm_enqueue_styles', 20 );
