<?php

if ( ! class_exists( 'Extended_CPT' ) ) {
	require_once VELOCITY_PATH . 'lib/extended-cpts/extended-cpts.php';
}

require_once VELOCITY_CMB2_PATH;

class Velocity_Account_Executive {

	function __construct() {}

	public static function hooks() {

		add_action( 'init', array( __CLASS__, 'cpt_setup' ) );
		add_action( 'cmb2_admin_init', array( __CLASS__, 'custom_field_setup' ) );
		add_action( 'init', array( __CLASS__, 'add_shortcodes' ) );

	}

	/**
	 * Custom Post Type and Taxonomy set up
	 */
	public static function cpt_setup() {

		$account_execs = register_extended_post_type(
			'velocity-ae',
			array(
				'menu_icon'    => 'dashicons-businessperson',
				'supports'     => array( 'title', 'thumbnail' ),
				'show_in_rest' => true,
				'has_archive'  => false,
				'rewrite'      => array(
					'slug'       => 'ae',
					'with_front' => false,
				),
			),
			array(
				'singular' => __( 'AE', 'velocitymu' ),
				'plural'   => __( 'AEs', 'velocitymu' ),
				'slug'     => 'account-executives',

			)
		);
	}

	/**
	 * Custom field setup
	 */
	public static function custom_field_setup() {

		$account_execs_meta_box = new_cmb2_box( array(
			'id'           => 'account_executive_meta_box',
			'title'        => __( 'Account Executive Details', 'velocitymu' ),
			'object_types' => array( 'velocity-ae' ), // Post type
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true, // Show field names on the left
		) );

		$account_execs_meta_box->add_field( array(
			'name' => __( 'First Name', 'velocitymu' ),
			'id'   => 'velocity_ae_first_name',
			'type' => 'text',
			'desc' => __( '', 'velocitymu' ),
		) );

		$account_execs_meta_box->add_field( array(
			'name' => __( 'Last Name', 'velocitymu' ),
			'id'   => 'velocity_ae_last_name',
			'type' => 'text',
			'desc' => __( '', 'velocitymu' ),
		) );

		$account_execs_meta_box->add_field( array(
			'name' => __( 'Title', 'velocitymu' ),
			'id'   => 'velocity_ae_title',
			'type' => 'text',
			'desc' => __( '', 'velocitymu' ),
		) );

		$account_execs_meta_box->add_field( array(
			'name' => __( 'Phone', 'velocitymu' ),
			'id'   => 'velocity_ae_phone',
			'type' => 'text',
			'desc' => __( '', 'velocitymu' ),
		) );

		$account_execs_meta_box->add_field( array(
			'name' => __( 'Email', 'velocitymu' ),
			'id'   => 'velocity_ae_email',
			'type' => 'text_email',
			'desc' => __( '', 'velocitymu' ),
		) );

		$account_execs_meta_box->add_field( array(
			'name'        => __( 'Paragraph Text', 'velocitymu' ),
			'id'          => 'account_executive_paragraph_text',
			'type'        => 'textarea',
			'description' => __( 'Limited to 1000 characters', 'velocitymu' ),
			'attributes'  => array(
				'maxlength' => 1000,
			),
		) );

		$account_execs_meta_box->add_field( array(
			'name' => __( 'LinkedIn', 'velocitymu' ),
			'id'   => 'velocity_ae_linkedin',
			'type' => 'text_url',
			'desc' => __( '', 'velocitymu' ),
		) );

		$account_execs_meta_box->add_field( array(
			'name' => __( 'Facebook', 'velocitymu' ),
			'id'   => 'velocity_ae_facebook',
			'type' => 'text_url',
			'desc' => __( '', 'velocitymu' ),
		) );

		$account_execs_meta_box->add_field( array(
			'name' => __( 'X / Twitter', 'velocitymu' ),
			'id'   => 'velocity_ae_x',
			'type' => 'text_url',
			'desc' => __( '', 'velocitymu' ),
		) );

		$account_execs_meta_box->add_field( array(
			'name' => __( 'Instagram', 'velocitymu' ),
			'id'   => 'velocity_ae_instagram',
			'type' => 'text_url',
			'desc' => __( '', 'velocitymu' ),
		) );

		$account_execs_meta_box->add_field( array(
			'name' => __( 'YouTube', 'velocitymu' ),
			'id'   => 'velocity_ae_youtube',
			'type' => 'text_url',
			'desc' => __( '', 'velocitymu' ),
		) );
	}

	public static function add_shortcodes() {

		// add_shortcode( 'velocity_career_cities', array( __CLASS__, 'cities' ) );
		// add_shortcode( 'velocity_career_short_description', array( __CLASS__, 'short_description' ) );

	}

	public static function cities() {

		global $post;
		$city = get_post_meta( $post->ID, 'career-city', true );
		return wpautop( $city );

	}

	public static function short_description() {

		global $post;
		$city = get_post_meta( $post->ID, 'career-short-description', true );
		return wpautop( $city );

	}

}

Velocity_Account_Executive::hooks();

/**
 * Limit the account executive paragraph text to 1000 characters.
 */
add_filter( 'cmb2_sanitize_account_executive_paragraph_text', function( $value ) {
	return mb_substr( (string) $value, 0, 1000 );
} );

/**
 * Force newlines to line breaks in the Executive Paragraph.
 */
add_filter( 'kadence_dynamic_content_render', function( $output, $item_id, $origin, $group, $field, $para, $custom ) {
	if ( $custom === 'account_executive_paragraph_text' ) {
		$output = nl2br( esc_html( $output ) );
	}
	return $output;
}, 10, 7 );

/**
 * Prefill gravity form field with email address of the account executive being viewed.
 */
add_filter( 'gform_field_value_prefill_ae_email', 'gf_prefill_ae_email' );
function gf_prefill_ae_email( $value ) {
	// get the current post ID.
	$post_id = get_the_ID();

	// get the email meta value for the current post.
	$email = get_post_meta( $post_id, 'velocity_ae_email', true );

	// return the email value to prefill the form field.
	return $email;
}

/**
 * Prefill gravity form field with first name of the account executive being viewed.
 */
add_filter( 'gform_field_value_prefill_ae_first_name', 'gf_prefill_ae_first_name' );
function gf_prefill_ae_first_name( $value ) {
	// get the current post ID.
	$post_id = get_the_ID();

	// get the first name meta value for the current post.
	$first_name = get_post_meta( $post_id, 'velocity_ae_first_name', true );

	// return the first name value to prefill the form field.
	return $first_name;
}
