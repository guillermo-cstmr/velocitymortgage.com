<?php
/**
 * Offices map: markup.
 *
 * The work is done in inc/offices.php: placing each office on the drawing
 * and printing the pins and the cards.
 *
 * @package Velocity_Kadence
 *
 * @var array $attributes Block attributes: offices.
 */

defined( 'ABSPATH' ) || exit;

$vm_map = vm_offices_map_html( isset( $attributes['offices'] ) ? $attributes['offices'] : array() );

if ( '' === $vm_map ) {
	return;
}

// $vm_map is built and escaped in inc/offices.php.
echo '<div ' . get_block_wrapper_attributes( array( 'class' => 'vm-offices' ) ) . '>' . $vm_map . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
