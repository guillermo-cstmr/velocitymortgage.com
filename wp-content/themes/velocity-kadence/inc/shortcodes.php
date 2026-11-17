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
 * [vm_deal_info]: the text of a deal card, for a funded property.
 *
 * It goes inside a Query Loop over the property post type, in a Shortcode
 * block. It prints, when the property has them:
 * - the closing time, from the "Processed Details Text" field,
 * - the property type and purpose as the card title,
 * - the loan program,
 * - the account executive.
 *
 * The title links to the property. That link is the one stretched over the
 * whole card in style.css, so a card has a single link.
 *
 * Core blocks cannot print custom fields or unlinked taxonomy terms, which is
 * why this is a shortcode and not a set of blocks.
 *
 * @return string
 */
function vm_deal_info_shortcode() {
	$post_id = get_the_ID();

	if ( ! $post_id || 'velocity-property' !== get_post_type( $post_id ) ) {
		return '';
	}

	$closed  = trim( (string) get_post_meta( $post_id, 'velocity_property_processed_details_text', true ) );
	$type    = vm_first_term_name( $post_id, 'velocity-property-type' );
	$purpose = vm_first_term_name( $post_id, 'velocity-property-purpose' );
	$program = vm_first_term_name( $post_id, 'velocity-property-program' );
	$ae      = vm_first_term_name( $post_id, 'velocity-property-ae' );

	// The title is the type and purpose. A property with neither falls back to its name.
	$title_parts = array_filter( array( $type, $purpose ) );
	$title       = $title_parts ? implode( ' · ', $title_parts ) : get_the_title( $post_id );

	$out = '<div class="vm-deal-info">';

	if ( '' !== $closed ) {
		$out .= sprintf(
			'<p class="vm-deal-tag"><img src="%1$s" alt="" width="16" height="16" aria-hidden="true">%2$s</p>',
			esc_url( VELOCITY_KADENCE_URI . '/assets/images/icon-bolt.svg' ),
			esc_html( $closed )
		);
	} else {
		// No closing time: keep the tag's space, so titles line up across cards.
		$out .= '<p class="vm-deal-tag vm-deal-tag--empty" aria-hidden="true">&nbsp;</p>';
	}

	// Screen readers also hear the property's name, which tells the cards apart.
	$name = $title_parts ? '<span class="screen-reader-text">, ' . esc_html( get_the_title( $post_id ) ) . '</span>' : '';

	$out .= sprintf(
		'<h3 class="vm-deal-title"><a href="%1$s">%2$s%3$s</a></h3>',
		esc_url( get_permalink( $post_id ) ),
		esc_html( $title ),
		$name
	);

	if ( '' !== $program ) {
		$out .= '<p class="vm-deal-program">' . esc_html( $program ) . '</p>';
	}

	if ( '' !== $ae ) {
		$out .= '<p class="vm-deal-ae">' . esc_html( $ae ) . '</p>';
	}

	return $out . '</div>';
}
add_shortcode( 'vm_deal_info', 'vm_deal_info_shortcode' );

/**
 * [vm_staff_info]: the text of a person card, for a staff member.
 *
 * It goes inside a Query Loop over the staff post type, in a Shortcode block,
 * under the Featured Image block that shows the photo. It prints:
 * - the name, linked to the person's page,
 * - the "Staff Title (short)" field, which for sales directors is their office,
 * - a LinkedIn link, when the person has a LinkedIn address.
 *
 * The name link is stretched over the whole card in style.css. The LinkedIn
 * link sits above it, so both can be clicked.
 *
 * @return string
 */
function vm_staff_info_shortcode() {
	$post_id = get_the_ID();

	if ( ! $post_id || 'velocity-staff' !== get_post_type( $post_id ) ) {
		return '';
	}

	$name     = get_the_title( $post_id );
	$role     = trim( (string) get_post_meta( $post_id, 'velocity_staff_title_short', true ) );
	$linkedin = trim( (string) get_post_meta( $post_id, 'velocity_staff_linkedin_url', true ) );

	$out = sprintf(
		'<div class="vm-person-info"><h3 class="vm-person-name"><a href="%1$s">%2$s</a></h3>',
		esc_url( get_permalink( $post_id ) ),
		esc_html( $name )
	);

	if ( '' !== $role ) {
		$out .= '<p class="vm-person-role">' . esc_html( $role ) . '</p>';
	}

	// An address with no profile in it, such as "https://linkedin.com/", is a placeholder.
	if ( '' !== $linkedin && '' !== trim( (string) wp_parse_url( $linkedin, PHP_URL_PATH ), '/' ) ) {
		$out .= sprintf(
			'<a class="vm-person-linkedin" href="%1$s" target="_blank" rel="noopener noreferrer"><img src="%2$s" alt="" width="24" height="24"><span class="screen-reader-text">%3$s</span></a>',
			esc_url( $linkedin ),
			esc_url( VELOCITY_KADENCE_URI . '/assets/images/company/icon-linkedin.svg' ),
			/* translators: %s: person's name. */
			esc_html( sprintf( __( '%s on LinkedIn (opens in a new tab)', 'velocity-kadence' ), $name ) )
		);
	}

	return $out . '</div>';
}
add_shortcode( 'vm_staff_info', 'vm_staff_info_shortcode' );

/**
 * [vm_career_info]: the text of a position card, for a career.
 *
 * It goes inside a Query Loop over the careers post type, in a Shortcode
 * block. It prints the position, linked to its page, the "City/Cities" field
 * on one line and the "Short Description" field.
 *
 * @return string
 */
function vm_career_info_shortcode() {
	$post_id = get_the_ID();

	if ( ! $post_id || 'velocity-career' !== get_post_type( $post_id ) ) {
		return '';
	}

	// The cities are typed one per line. On the card they share a line.
	$cities = preg_split( '#<br\s*/?>|[\r\n]+#i', (string) get_post_meta( $post_id, 'career-city', true ) );
	$cities = array_filter( array_map( 'trim', array_map( 'wp_strip_all_tags', (array) $cities ) ) );
	$text   = trim( wp_strip_all_tags( (string) get_post_meta( $post_id, 'career-short-description', true ) ) );

	$out = sprintf(
		'<div class="vm-career-info"><h3 class="vm-career-title"><a href="%1$s">%2$s</a></h3>',
		esc_url( get_permalink( $post_id ) ),
		esc_html( get_the_title( $post_id ) )
	);

	if ( $cities ) {
		$out .= '<p class="vm-career-city">' . esc_html( implode( ' · ', $cities ) ) . '</p>';
	}

	if ( '' !== $text ) {
		$out .= '<p class="vm-career-text">' . esc_html( $text ) . '</p>';
	}

	return $out . '</div>';
}
add_shortcode( 'vm_career_info', 'vm_career_info_shortcode' );

/**
 * Run the card shortcodes while their block renders.
 *
 * WordPress normally expands shortcodes after all blocks have rendered. By
 * then a Query Loop has finished and the current post is the page again, so
 * the shortcode would describe the wrong post. Expanding it here, while the
 * loop is on its post, gives it the right one.
 *
 * @param string $content Rendered block content.
 * @return string
 */
function vm_render_card_shortcode_in_loop( $content ) {
	if ( ! preg_match( '#\[vm_(?:deal|staff|career)_info#', $content ) ) {
		return $content;
	}

	// The Shortcode block wraps its text in a paragraph. The card text is a
	// div with its own paragraphs, so that wrapper has to go.
	$content = preg_replace( '#^\s*<p>\s*(\[vm_(?:deal|staff|career)_info[^\]]*\])\s*</p>\s*$#', '$1', $content );

	return do_shortcode( $content );
}
add_filter( 'render_block_core/shortcode', 'vm_render_card_shortcode_in_loop' );
