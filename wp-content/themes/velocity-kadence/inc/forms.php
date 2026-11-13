<?php
/**
 * Shared forms.
 *
 * Pages do not embed a Gravity Form by its number. They use the shortcode
 * [vm_form name="contact"] or [vm_form name="newsletter"], usually through
 * the patterns "Contact form" and "Newsletter sign-up". The shortcode finds
 * the right form on each environment, so a page keeps working when it is
 * moved between them, and the consent wording lives in one place.
 *
 * The look of every Gravity Form is in style.css, "Forms".
 *
 * See docs/components.md, "Forms".
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Title of the newsletter form. It is created by "wp velocity seed-forms",
 * so its number differs between environments and it is found by this title.
 */
const VM_NEWSLETTER_FORM_TITLE = 'Newsletter Signup';

/**
 * Number of the "Contact Us Page" form. The same on staging and production.
 */
const VM_CONTACT_FORM_ID = 12;

/**
 * Gravity Form number for a shared form.
 *
 * @param string $name "contact" or "newsletter".
 * @return int Form ID, or 0 when it does not exist.
 */
function vm_form_id( $name ) {
	if ( ! class_exists( 'GFAPI' ) ) {
		return 0;
	}

	if ( 'contact' === $name ) {
		return GFAPI::form_id_exists( VM_CONTACT_FORM_ID ) ? VM_CONTACT_FORM_ID : 0;
	}

	if ( 'newsletter' === $name ) {
		return (int) GFFormsModel::get_form_id( VM_NEWSLETTER_FORM_TITLE );
	}

	return 0;
}

/**
 * [vm_form name="contact"]: a shared form.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function vm_form_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'name' => 'contact' ), $atts, 'vm_form' );
	$name = sanitize_key( $atts['name'] );
	$id   = vm_form_id( $name );

	if ( ! $id ) {
		// Visible only to editors, so a missing form is noticed while building.
		return current_user_can( 'edit_posts' )
			? '<p><em>' . esc_html( sprintf( 'The "%s" form does not exist here. Run: wp velocity seed-forms --execute', $name ) ) . '</em></p>'
			: '';
	}

	$form = gravity_form( $id, false, false, false, null, true, 0, false );
	$note = '';

	if ( 'newsletter' === $name ) {
		$note = sprintf(
			'<p class="vm-form__note">By subscribing you agree to marketing communications from Velocity. Review our <a href="%s">Privacy Policy</a>.</p>',
			esc_url( home_url( '/privacy/' ) )
		);
	}

	return '<div class="vm-form vm-form--' . esc_attr( $name ) . '">' . $form . $note . '</div>';
}
add_shortcode( 'vm_form', 'vm_form_shortcode' );

/**
 * Adds the arrow of the site's buttons to every form's submit button.
 *
 * @param string $button Button HTML.
 * @return string
 */
function vm_form_submit_button( $button ) {
	if ( false !== strpos( $button, 'vm-form__arrow' ) ) {
		return $button;
	}

	$arrow = '<svg class="vm-form__arrow" aria-hidden="true" focusable="false" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';

	return preg_replace(
		'/(<button\b[^>]*\bid=(["\'])gform_submit_button_\d+\2[^>]*>)(.*?)(<\/button>)/is',
		'$1<span>$3</span>' . $arrow . '$4',
		$button,
		1
	);
}
add_filter( 'gform_submit_button', 'vm_form_submit_button', 20 );

/**
 * Pattern category for the site's own patterns, in the patterns folder.
 */
function vm_register_pattern_category() {
	register_block_pattern_category( 'velocity', array( 'label' => __( 'Velocity', 'velocity-kadence' ) ) );
}
add_action( 'init', 'vm_register_pattern_category' );
