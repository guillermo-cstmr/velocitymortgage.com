<?php
/**
 * Fields added to Kadence's dynamic content.
 *
 * Kadence prints a custom field as it is stored, and has no field for a
 * taxonomy term as plain text. A value that needs another shape on the page
 * gets an entry here: it shows up in the dynamic content list of a Text
 * (Adv), in the group "Velocity", and Kadence asks this file for its value.
 *
 * To add one, add it to vm_dynamic_fields() with the function that prints it.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * The theme's dynamic fields. Key => label and the function that returns
 * the value for a post ID.
 *
 * @return array[]
 */
function vm_dynamic_fields() {
	return array(
		'vm_career_cities' => array( __( 'Career: cities on one line', 'velocity-kadence' ), 'vm_dynamic_career_cities' ),
		'vm_deal_closed'   => array( __( 'Deal: closing time', 'velocity-kadence' ), 'vm_dynamic_deal_closed' ),
		'vm_deal_title'    => array( __( 'Deal: property type and purpose', 'velocity-kadence' ), 'vm_dynamic_deal_title' ),
		'vm_deal_program'  => array( __( 'Deal: loan program', 'velocity-kadence' ), 'vm_dynamic_deal_program' ),
		'vm_deal_ae'       => array( __( 'Deal: account executive', 'velocity-kadence' ), 'vm_dynamic_deal_ae' ),
		'vm_deal_headline'     => array( __( 'Deal: amount and place', 'velocity-kadence' ), 'vm_dynamic_deal_headline' ),
		'vm_deal_terms'        => array( __( 'Deal: program and purpose', 'velocity-kadence' ), 'vm_dynamic_deal_terms' ),
		'vm_case_terms'        => array( __( 'Case study: program and purpose', 'velocity-kadence' ), 'vm_dynamic_case_terms' ),
		'vm_case_amount'       => array( __( 'Case study: loan amount', 'velocity-kadence' ), 'vm_dynamic_case_amount' ),
		'vm_case_date'         => array( __( 'Case study: date', 'velocity-kadence' ), 'vm_dynamic_case_date' ),
		'vm_toolkit_new'       => array( __( 'Toolkit: "New" tag', 'velocity-kadence' ), 'vm_dynamic_toolkit_new' ),
		'vm_toolkit_file_type' => array( __( 'Toolkit: file type', 'velocity-kadence' ), 'vm_dynamic_toolkit_file_type' ),
	);
}

/**
 * Add the theme's fields to the list of dynamic text fields.
 *
 * @param array $options Groups of fields, as Kadence lists them.
 * @return array
 */
function vm_dynamic_text_fields( $options ) {
	$fields = array();

	foreach ( vm_dynamic_fields() as $key => $field ) {
		$fields[] = array(
			'value' => 'post|' . $key,
			'label' => $field[0],
		);
	}

	$options[] = array(
		'label'   => __( 'Velocity', 'velocity-kadence' ),
		'options' => $fields,
	);

	return $options;
}
add_filter( 'kadence_block_pro_dynamic_text_fields_options', 'vm_dynamic_text_fields' );

/**
 * Answer Kadence when it asks for one of the theme's fields.
 */
function vm_register_dynamic_fields() {
	foreach ( vm_dynamic_fields() as $key => $field ) {
		add_filter(
			'kadence_dynamic_content_core_post_' . $key . '_render',
			static function ( $output, $item_id ) use ( $field ) {
				return esc_html( (string) call_user_func( $field[1], (int) $item_id ) );
			},
			10,
			2
		);
	}
}
add_action( 'init', 'vm_register_dynamic_fields' );

/**
 * The first term of a taxonomy for a post, as plain text.
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy name.
 * @return string Term name, or an empty string.
 */
function vm_first_term_name( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}

	return $terms[0]->name;
}

/**
 * The "City/Cities" field of a career, which is typed one city per line, as
 * a single line with a dot between cities.
 *
 * @param int $post_id Career ID.
 * @return string
 */
function vm_dynamic_career_cities( $post_id ) {
	$cities = preg_split( '#<br\s*/?>|[\r\n]+#i', (string) get_post_meta( $post_id, 'career-city', true ) );
	$cities = array_filter( array_map( 'trim', array_map( 'wp_strip_all_tags', (array) $cities ) ) );

	return implode( ' · ', $cities );
}

/**
 * How fast a deal closed: the property's "Processed Details Text" field.
 *
 * @param int $post_id Property ID.
 * @return string
 */
function vm_dynamic_deal_closed( $post_id ) {
	return trim( (string) get_post_meta( $post_id, 'velocity_property_processed_details_text', true ) );
}

/**
 * The title of a deal card: the property's type and purpose. A property
 * with neither falls back to its name.
 *
 * @param int $post_id Property ID.
 * @return string
 */
function vm_dynamic_deal_title( $post_id ) {
	$parts = array_filter(
		array(
			vm_first_term_name( $post_id, 'velocity-property-type' ),
			vm_first_term_name( $post_id, 'velocity-property-purpose' ),
		)
	);

	return $parts ? implode( ' · ', $parts ) : get_the_title( $post_id );
}

/**
 * The loan program of a deal.
 *
 * @param int $post_id Property ID.
 * @return string
 */
function vm_dynamic_deal_program( $post_id ) {
	return vm_first_term_name( $post_id, 'velocity-property-program' );
}

/**
 * The account executive of a deal.
 *
 * @param int $post_id Property ID.
 * @return string
 */
function vm_dynamic_deal_ae( $post_id ) {
	return vm_first_term_name( $post_id, 'velocity-property-ae' );
}

/**
 * A loan amount as it is typed, "1,120,000", with its dollar sign.
 *
 * @param string $amount Amount as stored.
 * @return string
 */
function vm_format_amount( $amount ) {
	$amount = trim( (string) $amount );

	if ( '' === $amount ) {
		return '';
	}

	return '$' === $amount[0] ? $amount : '$' . $amount;
}

/**
 * The title of a deal in the gallery: its amount and its place. A deal
 * with neither falls back to its name.
 *
 * @param int $post_id Property ID.
 * @return string
 */
function vm_dynamic_deal_headline( $post_id ) {
	$parts = array_filter(
		array(
			vm_format_amount( get_post_meta( $post_id, 'velocity_property_loan_amount', true ) ),
			trim( (string) get_post_meta( $post_id, 'velocity_property_location', true ) ),
		)
	);

	return $parts ? implode( ' · ', $parts ) : get_the_title( $post_id );
}

/**
 * The program and the purpose of a deal, on one line.
 *
 * @param int $post_id Property ID.
 * @return string
 */
function vm_dynamic_deal_terms( $post_id ) {
	return implode(
		' · ',
		array_filter(
			array(
				vm_first_term_name( $post_id, 'velocity-property-program' ),
				vm_first_term_name( $post_id, 'velocity-property-purpose' ),
			)
		)
	);
}

/**
 * The program and the purpose of a case study, on one line.
 *
 * @param int $post_id Case study ID.
 * @return string
 */
function vm_dynamic_case_terms( $post_id ) {
	return implode(
		' · ',
		array_filter(
			array(
				trim( (string) get_post_meta( $post_id, 'velocity_case_study_program', true ) ),
				trim( (string) get_post_meta( $post_id, 'velocity_case_study_purpose', true ) ),
			)
		)
	);
}

/**
 * The loan amount of a case study, with its dollar sign.
 *
 * @param int $post_id Case study ID.
 * @return string
 */
function vm_dynamic_case_amount( $post_id ) {
	return vm_format_amount( get_post_meta( $post_id, 'velocity_case_study_loan_amount', true ) );
}

/**
 * The date of a case study, as "Oct 14, 2026".
 *
 * @param int $post_id Case study ID.
 * @return string
 */
function vm_dynamic_case_date( $post_id ) {
	$time = strtotime( (string) get_post_meta( $post_id, 'velocity_case_study_date', true ) );

	return $time ? date_i18n( 'M j, Y', $time ) : '';
}

/**
 * The "New" tag of a toolkit document: the word, or nothing when the
 * document's "New" box is not ticked. The field itself holds "on".
 *
 * @param int $post_id Document ID.
 * @return string
 */
function vm_dynamic_toolkit_new( $post_id ) {
	return get_post_meta( $post_id, 'velocity_toolkit_new', true ) ? __( 'New', 'velocity-kadence' ) : '';
}

/**
 * The type of a toolkit document's file, as its extension in capitals:
 * PDF, XLSX.
 *
 * @param int $post_id Document ID.
 * @return string
 */
function vm_dynamic_toolkit_file_type( $post_id ) {
	$file = get_attached_file( (int) get_post_meta( $post_id, 'velocity_toolkit_file_id', true ) );

	return $file ? strtoupper( (string) pathinfo( $file, PATHINFO_EXTENSION ) ) : '';
}

/**
 * Mark a label that came out empty.
 *
 * A Text (Adv) with the class vm-tag is a small filled label, such as a
 * deal's closing time. When its dynamic content has no value, Kadence still
 * prints the label, as an empty colored box. This adds vm-tag--empty, which
 * style.css hides while keeping its space, so the titles of a row of cards
 * stay level.
 *
 * @param string $html Rendered block.
 * @return string
 */
function vm_mark_empty_tag( $html ) {
	if ( false === strpos( $html, 'vm-tag' ) || '' !== trim( wp_strip_all_tags( $html ) ) ) {
		return $html;
	}

	// A space gives the hidden label the height of a line of text.
	$html = preg_replace( '#</p>\s*$#', '&nbsp;</p>', $html, 1 );

	return preg_replace( '/\bvm-tag\b/', 'vm-tag vm-tag--empty', $html, 1 );
}
add_filter( 'render_block_kadence/advancedheading', 'vm_mark_empty_tag' );

/**
 * Leave out a text that came out empty.
 *
 * A Text (Adv) with the class vm-hide-empty is not printed when its dynamic
 * content has no value: the "New" tag of a document that is not new, or the
 * description of one that has none. Kadence would print it empty, with its
 * background and its margins.
 *
 * @param string $html Rendered block.
 * @return string
 */
function vm_hide_empty_text( $html ) {
	if ( false === strpos( $html, 'vm-hide-empty' ) || '' !== trim( wp_strip_all_tags( $html ) ) ) {
		return $html;
	}

	return '';
}
// Before vm_mark_empty_tag, which would fill the empty text with a space.
add_filter( 'render_block_kadence/advancedheading', 'vm_hide_empty_text', 9 );
