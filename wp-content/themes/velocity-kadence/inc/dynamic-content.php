<?php
/**
 * Fields added to Kadence's dynamic content.
 *
 * Kadence prints a custom field as it is stored. A field that needs another
 * shape on the page gets an entry here: it shows up in the dynamic content
 * list of a Text (Adv), under Post, and Kadence asks this file for its value.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add the theme's fields to the list of dynamic text fields.
 *
 * @param array $options Groups of fields, as Kadence lists them.
 * @return array
 */
function vm_dynamic_text_fields( $options ) {
	$options[] = array(
		'label'   => __( 'Velocity', 'velocity-kadence' ),
		'options' => array(
			array(
				'value' => 'post|vm_career_cities',
				'label' => __( 'Career: cities on one line', 'velocity-kadence' ),
			),
		),
	);

	return $options;
}
add_filter( 'kadence_block_pro_dynamic_text_fields_options', 'vm_dynamic_text_fields' );

/**
 * "Career: cities on one line": the "City/Cities" field of a career, which
 * is typed one city per line, as a single line with a dot between cities.
 *
 * @param string $output  Value so far.
 * @param int    $item_id ID of the post being shown.
 * @return string
 */
function vm_dynamic_career_cities( $output, $item_id ) {
	$cities = preg_split( '#<br\s*/?>|[\r\n]+#i', (string) get_post_meta( (int) $item_id, 'career-city', true ) );
	$cities = array_filter( array_map( 'trim', array_map( 'wp_strip_all_tags', (array) $cities ) ) );

	return esc_html( implode( ' · ', $cities ) );
}
add_filter( 'kadence_dynamic_content_core_post_vm_career_cities_render', 'vm_dynamic_career_cities', 10, 2 );
