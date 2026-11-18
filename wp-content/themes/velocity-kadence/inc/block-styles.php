<?php
/**
 * Block styles: looks picked in a block's Styles panel.
 *
 * Program images
 * --------------
 * The program photos are plain rectangles. The diagonal cut, the rounded
 * corners and the orange bar along the cut are drawn by these styles, so
 * anyone can replace a photo in the editor and keep the design. Select the
 * Image block, open Styles in the sidebar and pick one. On tablet and mobile,
 * where the image sits above the text, the photo shows whole with rounded
 * corners. The rules are in style.css, "Program images".
 *
 * See docs/components.md, "Program images".
 *
 * Timeline
 * --------
 * A line with dots beside a run of texts. See vm_register_timeline_styles()
 * below and docs/components.md, "Timeline".
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the program image styles on Kadence's Image block.
 */
function vm_register_program_image_styles() {
	$styles = array(
		'vm-cut-left'       => __( 'Program: cut left', 'velocity-kadence' ),
		'vm-cut-right'      => __( 'Program: cut right', 'velocity-kadence' ),
		'vm-cut-right-wide' => __( 'Program: wide cut right', 'velocity-kadence' ),
	);

	foreach ( $styles as $name => $label ) {
		register_block_style(
			'kadence/image',
			array(
				'name'  => $name,
				'label' => $label,
			)
		);
	}
}
add_action( 'init', 'vm_register_program_image_styles' );

/**
 * Register the timeline styles.
 *
 * "Timeline" goes on a Section and draws a line down its left side.
 * "Timeline point" goes on a text inside that Section and draws a dot on the
 * line. The rules are in style.css, "Timeline". See docs/components.md.
 */
function vm_register_timeline_styles() {
	register_block_style(
		'kadence/column',
		array(
			'name'  => 'vm-timeline',
			'label' => __( 'Timeline', 'velocity-kadence' ),
		)
	);

	foreach ( array( 'kadence/advancedheading', 'core/paragraph' ) as $block ) {
		register_block_style(
			$block,
			array(
				'name'  => 'vm-timeline-point',
				'label' => __( 'Timeline point', 'velocity-kadence' ),
			)
		);
	}
}
add_action( 'init', 'vm_register_timeline_styles' );
