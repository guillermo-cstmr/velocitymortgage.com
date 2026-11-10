<?php
/**
 * Living style guide at /?velocity-styleguide=1.
 *
 * It renders the tokens as the theme outputs them, so it can be compared
 * against the Figma style guide. It is a theme template, not a page: nothing
 * is stored in the database and it works on every environment after a deploy.
 *
 * Visible to logged-in users who can edit theme options, and to anyone on a
 * local or development environment. Never public on staging or production.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current visitor may see the style guide.
 *
 * @return bool
 */
function vm_can_view_styleguide() {
	if ( current_user_can( 'edit_theme_options' ) ) {
		return true;
	}

	return in_array( wp_get_environment_type(), array( 'local', 'development' ), true );
}

/**
 * Serve the style guide template when it is requested.
 */
function vm_maybe_render_styleguide() {
	if ( ! isset( $_GET['velocity-styleguide'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	if ( ! vm_can_view_styleguide() ) {
		return;
	}

	nocache_headers();
	add_filter( 'wp_robots', 'wp_robots_no_robots' );
	add_filter(
		'pre_get_document_title',
		static function () {
			return 'Style Guide';
		},
		100
	);

	require VELOCITY_KADENCE_DIR . '/templates/styleguide.php';
	exit;
}
add_action( 'template_redirect', 'vm_maybe_render_styleguide' );
