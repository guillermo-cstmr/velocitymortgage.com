<?php
/**
 * Heading animation: the "Reveal lines" block style.
 *
 * Select a Text (Adv) or Heading block, open Styles in the sidebar and pick
 * "Reveal lines": each line slides up from behind a mask, one after the
 * other, when the heading enters the screen. assets/js/reveal-lines.js does
 * the work. Visitors who ask for reduced motion see the heading still.
 *
 * Cards and other blocks use Kadence's own Animate on Scroll settings
 * instead. See docs/components.md, "Animations".
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the style so it shows in the block's Styles panel.
 */
function vm_register_reveal_lines_style() {
	foreach ( array( 'kadence/advancedheading', 'core/heading' ) as $block ) {
		register_block_style(
			$block,
			array(
				'name'  => 'vm-reveal-lines',
				'label' => __( 'Reveal lines', 'velocity-kadence' ),
			)
		);
	}
}
add_action( 'init', 'vm_register_reveal_lines_style' );

/**
 * Load the script only on pages that use the style.
 *
 * @param string $html Rendered block.
 * @return string
 */
function vm_enqueue_reveal_lines( $html ) {
	if ( is_admin() || false === strpos( $html, 'is-style-vm-reveal-lines' ) ) {
		return $html;
	}

	$file = VELOCITY_KADENCE_DIR . '/assets/js/reveal-lines.js';
	wp_enqueue_script(
		'velocity-kadence-reveal-lines',
		VELOCITY_KADENCE_URI . '/assets/js/reveal-lines.js',
		array(),
		file_exists( $file ) ? (string) filemtime( $file ) : null,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	return $html;
}
add_filter( 'render_block', 'vm_enqueue_reveal_lines' );

/**
 * Mark the page as running JavaScript, so animated headings can wait hidden
 * for their script. If the script has not run after three seconds, the mark
 * is removed and the headings show as plain text.
 */
function vm_js_marker() {
	echo "<script>document.documentElement.classList.add('vm-js');setTimeout(function(){document.documentElement.classList.remove('vm-js');},3000);</script>\n";
}
add_action( 'wp_head', 'vm_js_marker', 0 );
