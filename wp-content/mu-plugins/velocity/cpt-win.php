<?php

if ( ! class_exists( 'Extended_CPT' ) ) {
	require_once VELOCITY_PATH . 'lib/extended-cpts/extended-cpts.php';
}

require_once VELOCITY_CMB2_PATH;

class Velocity_WIN {

	function __construct() {}

	public static function hooks() {

		add_action( 'init', array( __CLASS__, 'cpt_setup' ) );
		add_action( 'cmb2_admin_init', array( __CLASS__, 'quote_meta_box' ) );

	}

	/**
	 * Custom Post Type and Taxonomy set up
	 */
	public static function cpt_setup() {

		$careers = register_extended_post_type(
			'velocity-win',
			array(
				'supports'     => array( 'title', 'thumbnail', 'editor', 'excerpt' ),
				'show_in_rest' => true,
			),
			array(
				'singular' => __( 'WIN', 'velocitymu' ),
				'plural'   => __( 'WIN', 'velocitymu' ),
				'slug'     => 'win',
			)
		);

		register_extended_taxonomy(
			'velocity-win-category',
			'velocity-win',
			array(
				'hierarchical' => true,
				'show_in_rest' => true,
			),
			array(
				'singular' => __( 'Category', 'velocitymu' ),
				'plural'   => __( 'Categories', 'velocitymu' ),
				'slug'     => 'win-category',
			)
		);

		register_extended_taxonomy(
			'velocity-win-tag',
			'velocity-win',
			array(
				'hierarchical' => false,
				'show_in_rest' => true,
			),
			array(
				'singular' => __( 'Tag', 'velocitymu' ),
				'plural'   => __( 'Tags', 'velocitymu' ),
				'slug'     => 'win-tag',
			)
		);

	}

	/**
	 * Adds a meta box for quotes
	 */
	public static function quote_meta_box() {

		$quote_meta_box = new_cmb2_box( array(
			'id'            => 'quote_meta_box',
			'title'         => __( 'Quote', 'velocitymu' ),
			'object_types'  => array( 'velocity-win' ), // Post type
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
		) );

		$quote_meta_box->add_field( array(
			'name' => __( 'Headshot', 'velocitymu' ),
			'id'   => 'velocity_quote_headshot',
			'type' => 'file',
			'desc' => '',
		) );

		$quote_meta_box->add_field( array(
			'name' => __( 'Quote', 'velocitymu' ),
			'id'   => 'velocity_quote_quote',
			'type' => 'textarea',
			'desc' => __( 'Do not include quotation marks', 'velocitymu' ),
		) );

		$quote_meta_box->add_field( array(
			'name' => __( 'Name and Title', 'velocitymu' ),
			'id'   => 'velocity_quote_name',
			'type' => 'textarea',
			'desc' => '',
		) );

	}

}

Velocity_WIN::hooks();
