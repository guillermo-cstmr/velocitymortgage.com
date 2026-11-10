<?php

if ( ! class_exists( 'Extended_CPT' ) ) {
	require_once VELOCITY_PATH . 'lib/extended-cpts/extended-cpts.php';
}

require_once VELOCITY_CMB2_PATH;

class Velocity_Careers {

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

		$careers = register_extended_post_type(
			'velocity-career',
			array(
				'menu_icon' => 'dashicons-nametag',
				'supports'  => array( 'title', 'thumbnail', 'editor', 'excerpt' ),
				'show_in_rest' => true,
				'has_archive' => false,
			),
			array(
				'singular' => __( 'Career', 'velocitymu' ),
				'plural'   => __( 'Careers', 'velocitymu' ),
				'slug'      => 'careers',

			)
		);

	}

	/**
	 * Custom field setup
	 */
	public static function custom_field_setup() {

		$career_meta_box = new_cmb2_box( array(
			'id'            => 'career_meta_box',
			'title'         => __( 'Career Details', 'velocitymu' ),
			'object_types'  => array( 'velocity-career' ), // Post type
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
		) );

		$career_meta_box->add_field( array(
			'name'        => __( 'City/Cities', 'velocitymu' ),
			'id'          => 'career-city',
			'type'        => 'textarea',
			'description' => __( 'City is displayed on the Career Archive page', 'velocitymu' ),
		) );

		$career_meta_box->add_field( array(
			'name'        => __( 'Short Description', 'velocitymu' ),
			'id'          => 'career-short-description',
			'type'        => 'textarea',
			'description' => __( 'Short description is displayed underneath the Job Title', 'velocitymu' ),
		) );

	}

	public static function add_shortcodes() {

		add_shortcode( 'velocity_career_cities', array( __CLASS__, 'cities' ) );
		add_shortcode( 'velocity_career_short_description', array( __CLASS__, 'short_description' ) );

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

Velocity_Careers::hooks();
