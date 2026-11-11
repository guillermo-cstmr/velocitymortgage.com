<?php
/**
 * Block styles for program images.
 *
 * The program photos are plain rectangles. The diagonal cut, the rounded
 * corners and the orange bar along the cut are drawn by these styles, so
 * anyone can replace a photo in the editor and keep the design. Select the
 * Image block, open Styles in the sidebar and pick one. On tablet and mobile,
 * where the image sits above the text, the photo shows whole with rounded
 * corners. The rules are in style.css, "Program images".
 *
 * See docs/components.md, "Program images".
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
