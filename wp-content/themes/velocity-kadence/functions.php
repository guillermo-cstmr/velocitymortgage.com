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
require_once VELOCITY_KADENCE_DIR . '/inc/shortcodes.php';
require_once VELOCITY_KADENCE_DIR . '/inc/styleguide.php';
require_once VELOCITY_KADENCE_DIR . '/inc/background-video.php';
require_once VELOCITY_KADENCE_DIR . '/inc/accessibility.php';
require_once VELOCITY_KADENCE_DIR . '/inc/animations.php';
require_once VELOCITY_KADENCE_DIR . '/inc/block-styles.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once VELOCITY_KADENCE_DIR . '/inc/class-velocity-seed.php';
	require_once VELOCITY_KADENCE_DIR . '/inc/class-velocity-home.php';
	require_once VELOCITY_KADENCE_DIR . '/inc/class-velocity-cli.php';
}

/**
 * Blocks that ship with the theme. Each one is a folder under blocks/.
 */
function vm_register_blocks() {
	register_block_type( VELOCITY_KADENCE_DIR . '/blocks/loan-calculator' );
}
add_action( 'init', 'vm_register_blocks' );

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

/**
 * Load the small script behind the frequently asked questions.
 */
function vm_enqueue_scripts() {
	$file = VELOCITY_KADENCE_DIR . '/assets/js/faq.js';

	wp_enqueue_script(
		'velocity-kadence-faq',
		VELOCITY_KADENCE_URI . '/assets/js/faq.js',
		array(),
		file_exists( $file ) ? (string) filemtime( $file ) : null,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'vm_enqueue_scripts', 20 );

/**
 * Load the script that places the mobile menu under the sticky header.
 */
function vm_enqueue_header_script() {
	$file = VELOCITY_KADENCE_DIR . '/assets/js/header.js';

	wp_enqueue_script(
		'velocity-kadence-header',
		VELOCITY_KADENCE_URI . '/assets/js/header.js',
		array(),
		file_exists( $file ) ? (string) filemtime( $file ) : null,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'vm_enqueue_header_script', 20 );

/**
 * Load the same stylesheet inside the block editor, so buttons, the header
 * and the footer look in the editor the way they do on the site.
 */
function vm_enqueue_editor_styles() {
	if ( is_admin() ) {
		vm_enqueue_styles();
	}
}
add_action( 'enqueue_block_assets', 'vm_enqueue_editor_styles', 20 );
