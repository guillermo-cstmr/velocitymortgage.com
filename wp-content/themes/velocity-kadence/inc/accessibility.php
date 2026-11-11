<?php
/**
 * Accessibility fixes that no block setting covers.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Name every Kadence navigation after its menu title.
 *
 * A page has several menus (header, footer columns, legal) and screen readers
 * list them all as "navigation". Kadence's Navigation block has no setting for
 * an accessible name, so the title of the menu (Appearance > Navigation, or the
 * menu's own title in the editor) is used. Rename the menu to change it.
 *
 * @param string $html  Rendered block.
 * @param array  $block Parsed block.
 * @return string
 */
function vm_label_navigation( $html, $block ) {
	$id = isset( $block['attrs']['id'] ) ? (int) $block['attrs']['id'] : 0;
	if ( ! $id || preg_match( '/<nav\b[^>]*\baria-label="[^"]+"/', $html ) ) {
		return $html;
	}

	$title = trim( wp_strip_all_tags( (string) get_post_field( 'post_title', $id ) ) );
	if ( '' === $title ) {
		return $html;
	}

	$label = 'aria-label="' . esc_attr( $title ) . '"';

	// Kadence prints an empty aria-label: fill it. Otherwise add one.
	if ( preg_match( '/<nav\b[^>]*\baria-label=""/', $html ) ) {
		return preg_replace_callback(
			'/(<nav\b[^>]*?)\baria-label=""/',
			static function ( $m ) use ( $label ) {
				return $m[1] . $label;
			},
			$html,
			1
		);
	}

	return preg_replace_callback(
		'/<nav\b/',
		static function () use ( $label ) {
			return '<nav ' . $label;
		},
		$html,
		1
	);
}
add_filter( 'render_block_kadence/navigation', 'vm_label_navigation', 10, 2 );
