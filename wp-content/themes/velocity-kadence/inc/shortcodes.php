<?php
/**
 * Shortcodes used inside block content.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * [vm_year]: the current year, for copyright lines.
 *
 * @return string
 */
function vm_year_shortcode() {
	return esc_html( wp_date( 'Y' ) );
}
add_shortcode( 'vm_year', 'vm_year_shortcode' );
