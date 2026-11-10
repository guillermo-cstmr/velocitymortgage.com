<?php

if ( ! class_exists( 'Extended_CPT' ) ) {
	require_once VELOCITY_PATH . 'lib/extended-cpts/extended-cpts.php';
}

require_once VELOCITY_CMB2_PATH;

class Velocity_Staff {

	function __construct() {}

	public static function hooks() {

		add_action( 'init', array( __CLASS__, 'cpt_setup' ) );
		add_action( 'cmb2_admin_init', array( __CLASS__, 'staff_meta_box' ) );
		// add_shortcode( 'velocity_property_date', array( __CLASS__, 'date_shortcode' ) );
		// add_shortcode( 'velocity_money_dollars', array( __CLASS__, 'money_shortcode' ) );
	}

	/**
	 * Custom Post Type and Taxonomy set up
	 */
	public static function cpt_setup() {

		$properties = register_extended_post_type(
			'velocity-staff',
			array(
				'menu_icon'    => 'dashicons-groups',
				'supports'     => array( 'title', 'thumbnail', 'editor', 'page-attributes' ),
				'show_in_rest' => true,
				'has_archive'  => false,
			),
			array(
				'singular' => __( 'Staff', 'velocitymu' ),
				'plural'   => __( 'Staff', 'velocitymu' ),
				'slug'     => 'staff'
			)
		);

		register_extended_taxonomy(
			'velocity-staff-type',
			'velocity-staff',
			array(
				'hierarchical' => true,
				'show_in_rest' => true,
			),
			array(
				'singular' => __( 'Type', 'velocitymu' ),
				'plural'   => __( 'Types', 'velocitymu' ),
				'slug'     => 'staff-type',
			)
		);
	}

	/**
	 * Adds a meta box for quotes
	 */
	public static function staff_meta_box() {

		$staff_meta_box = new_cmb2_box( array(
			'id'            => 'staff_meta_box',
			'title'         => __( 'Additional Information', 'velocitymu' ),
			'object_types'  => array( 'velocity-staff' ), // Post type
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
		) );

		$staff_meta_box->add_field( array(
			'name' => __( 'Staff Title (short)', 'velocitymu' ),
			'id'   => 'velocity_staff_title_short',
			'type' => 'text',
			'desc' => __( '', 'velocitymu' ),
		) );

		$staff_meta_box->add_field( array(
			'name' => __( 'Staff Title (long)', 'velocitymu' ),
			'id'   => 'velocity_staff_title_long',
			'type' => 'text',
			'desc' => __( '', 'velocitymu' ),
		) );

		$staff_meta_box->add_field( array(
			'name' => __( 'Staff Phone', 'velocitymu' ),
			'id'   => 'velocity_staff_phone',
			'type' => 'text',
			'desc' => __( '', 'velocitymu' ),
		) );

		$staff_meta_box->add_field( array(
			'name' => __( 'Staff Email', 'velocitymu' ),
			'id'   => 'velocity_staff_email',
			'type' => 'text',
			'desc' => __( '', 'velocitymu' ),
		) );

		$staff_meta_box->add_field( array(
			'name' => __( 'Staff LinkedIn URL', 'velocitymu' ),
			'id'   => 'velocity_staff_linkedin_url',
			'type' => 'text',
			'desc' => __( '', 'velocitymu' ),
		) );
	}

	/**
	 * Converts saved date to formatted
	 */
	// public static function date_shortcode( $atts ) {

	// 	$atts = shortcode_atts( array(
	// 		'format' => 'M j, Y',
	// 		'date' => date( 'Y-m-d', time() ),
	// 	), $atts, 'date_shortcode' );

	// 	return date( $atts['format'], strtotime( $atts['date'] ) );

	// }
}

Velocity_Staff::hooks();
