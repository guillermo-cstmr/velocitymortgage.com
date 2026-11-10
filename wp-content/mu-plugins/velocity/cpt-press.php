<?php

if ( ! class_exists( 'Extended_CPT' ) ) {
	require_once VELOCITY_PATH . 'lib/extended-cpts/extended-cpts.php';
}

require_once VELOCITY_CMB2_PATH;

class Velocity_Press {

	function __construct() {}

	public static function hooks() {

		add_action( 'init', array( __CLASS__, 'cpt_setup' ) );
		add_action( 'cmb2_admin_init', array( __CLASS__, 'quote_meta_box' ) );
		add_shortcode( 'velocity_press_quote', array( __CLASS__, 'quote_shortcode' ) );

	}

	/**
	 * Custom Post Type and Taxonomy set up
	 */
	public static function cpt_setup() {

		$careers = register_extended_post_type(
			'velocity-press',
			array(
				'menu_icon'    => 'dashicons-megaphone',
				'supports'     => array( 'title', 'thumbnail', 'editor', 'excerpt' ),
				'show_in_rest' => true,
			),
			array(
				'singular' => __( 'Press Release', 'velocitymu' ),
				'plural'   => __( 'Press Releases', 'velocitymu' ),
				'slug'     => 'press'
			)
		);

		register_extended_taxonomy(
			'velocity-press-category',
			'velocity-press',
			array(
				'hierarchical' => true,
				'show_in_rest' => true,
			),
			array(
				'singular' => __( 'Category', 'velocitymu' ),
				'plural'   => __( 'Categories', 'velocitymu' ),
				'slug'     => 'press-category',
			)
		);

		register_extended_taxonomy(
			'velocity-press-tag',
			'velocity-press',
			array(
				'hierarchical' => false,
				'show_in_rest' => true,
			),
			array(
				'singular' => __( 'Tag', 'velocitymu' ),
				'plural'   => __( 'Tags', 'velocitymu' ),
				'slug'     => 'press-tag',
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
			'object_types'  => array( 'velocity-press' ), // Post type
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

	/**
	 * Adds a shortcode for quote
	 */
	public static function quote_shortcode() {

		$post_id = get_the_ID();

		$image = wp_get_attachment_image( get_post_meta( $post_id, 'velocity_quote_headshot_id', true ), 'medium' );
		$quote = get_post_meta( $post_id, 'velocity_quote_quote', true );
		$name = get_post_meta( $post_id, 'velocity_quote_name', true );

		if ( ! $image && ! $quote && ! $name ) {
			return '';
		}

		if ( $quote ) {
			$quote = '"' . $quote . '"';
		}

		return sprintf(
			'<aside class="velocity-press-quote">
				<div class="fl-post-image">%s</div>
				<blockquote>%s</blockquote>
				<p>%s</p>
			</aside>',
			$image,
			esc_html( $quote ),
			esc_html( $name )
		);

	}

}

Velocity_Press::hooks();
