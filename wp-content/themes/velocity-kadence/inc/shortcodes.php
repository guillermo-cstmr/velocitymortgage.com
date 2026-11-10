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
 * Run [vm_deal_info] while its block renders.
 *
 * WordPress normally expands shortcodes after all blocks have rendered. By
 * then a Query Loop has finished and the current post is the page again, so
 * the shortcode would describe the wrong post. Expanding it here, while the
 * loop is on its post, gives it the right one.
 *
 * @param string $content Rendered block content.
 * @return string
 */
function vm_render_deal_shortcode_in_loop( $content ) {
	if ( false === strpos( $content, '[vm_deal_info' ) ) {
		return $content;
	}

	// The Shortcode block wraps its text in a paragraph. The card text is a
	// div with its own paragraphs, so that wrapper has to go.
	$content = preg_replace( '#^\s*<p>\s*(\[vm_deal_info[^\]]*\])\s*</p>\s*$#', '$1', $content );

	return do_shortcode( $content );
}
add_filter( 'render_block_core/shortcode', 'vm_render_deal_shortcode_in_loop' );
