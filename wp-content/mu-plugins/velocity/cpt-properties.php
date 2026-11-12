<?php

if ( ! class_exists( 'Extended_CPT' ) ) {
	require_once VELOCITY_PATH . 'lib/extended-cpts/extended-cpts.php';
}

require_once VELOCITY_CMB2_PATH;

class Velocity_Properties {

	function __construct() {}

	public static function hooks() {

		add_action( 'init', array( __CLASS__, 'cpt_setup' ) );
		add_action( 'cmb2_admin_init', array( __CLASS__, 'info_meta_box' ) );
		add_action( 'cmb2_admin_init', array( __CLASS__, 'program_meta_box' ) );
		add_action( 'cmb2_admin_init', array( __CLASS__, 'ae_meta_box' ) );
		add_action( 'added_post_meta', array( __CLASS__, 'sync_loan_amount_number' ), 10, 4 );
		add_action( 'updated_post_meta', array( __CLASS__, 'sync_loan_amount_number' ), 10, 4 );
		add_action( 'deleted_post_meta', array( __CLASS__, 'delete_loan_amount_number' ), 10, 4 );
		add_shortcode( 'velocity_property_date', array( __CLASS__, 'date_shortcode' ) );
		add_shortcode( 'velocity_money_dollars', array( __CLASS__, 'money_shortcode' ) );
	}

	/**
	 * Custom Post Type and Taxonomy set up
	 */
	public static function cpt_setup() {

		$properties = register_extended_post_type(
			'velocity-property',
			array(
				'menu_icon'    => 'dashicons-building',
				'supports'     => array( 'title', 'thumbnail', 'editor' ),
				'show_in_rest' => true,
				'has_archive'  => 'featured-investment-property-loans',
			),
			array(
				'singular' => __( 'Property', 'velocitymu' ),
				'plural'   => __( 'Properties', 'velocitymu' ),
				'slug'     => 'property'
			)
		);

		register_extended_taxonomy(
			'velocity-property-type',
			'velocity-property',
			array(
				'hierarchical' => true,
				'show_in_rest' => true,
			),
			array(
				'singular' => __( 'Type', 'velocitymu' ),
				'plural'   => __( 'Types', 'velocitymu' ),
				'slug'     => 'property-type',
			)
		);

		register_extended_taxonomy(
			'velocity-property-purpose',
			'velocity-property',
			array(
				'hierarchical' => true,
				'show_in_rest' => true,
			),
			array(
				'singular' => __( 'Purpose', 'velocitymu' ),
				'plural'   => __( 'Purposes', 'velocitymu' ),
				'slug'     => 'property-purpose',
			)
		);

        register_extended_taxonomy(
			'velocity-property-program',
			'velocity-property',
			array(
				'hierarchical' => true,
				'show_in_rest' => true,
			),
			array(
				'singular' => __( 'Program', 'velocitymu' ),
				'plural'   => __( 'Programs', 'velocitymu' ),
				'slug'     => 'property-program',
			)
		);

        register_extended_taxonomy(
			'velocity-property-ae',
			'velocity-property',
			array(
				'hierarchical' => true,
				'show_in_rest' => true,
			),
			array(
				'singular' => __( 'AE', 'velocitymu' ),
				'plural'   => __( 'AEs', 'velocitymu' ),
				'slug'     => 'property-ae',
			)
		);

		register_extended_taxonomy(
			'velocity-property-region',
			'velocity-property',
			array(
				'hierarchical' => true,
				'show_in_rest' => true,
			),
			array(
				'singular' => __( 'Region', 'velocitymu' ),
				'plural'   => __( 'Regions', 'velocitymu' ),
				'slug'     => 'property-region',
			)
		);

	}

	/**
	 * Fields on each loan program, in Properties, Programs.
	 *
	 * A program's description and terms are written here once. The loan
	 * calculator and the program pages read them from here, so they never
	 * disagree.
	 */
	public static function program_meta_box() {

		$program = new_cmb2_box( array(
			'id'           => 'velocity_program_meta_box',
			'title'        => __( 'Program details', 'velocitymu' ),
			'object_types' => array( 'term' ),
			'taxonomies'   => array( 'velocity-property-program' ),
		) );

		$program->add_field( array(
			'name' => __( 'Short description', 'velocitymu' ),
			'id'   => 'velocity_program_description',
			'type' => 'textarea_small',
			'desc' => __( 'One sentence. Shown on the calculator result.', 'velocitymu' ),
		) );

		$program->add_field( array(
			'name' => __( 'Loan amounts', 'velocitymu' ),
			'id'   => 'velocity_program_loan_amounts',
			'type' => 'text_medium',
			'desc' => __( 'For example: $75K – $2MM', 'velocitymu' ),
		) );

		$program->add_field( array(
			'name' => __( 'Max LTV', 'velocitymu' ),
			'id'   => 'velocity_program_max_ltv',
			'type' => 'text_small',
			'desc' => __( 'For example: 75%', 'velocitymu' ),
		) );

		$program->add_field( array(
			'name' => __( 'Term', 'velocitymu' ),
			'id'   => 'velocity_program_term',
			'type' => 'text_medium',
			'desc' => __( 'For example: Fixed for 30 years', 'velocitymu' ),
		) );

		$program->add_field( array(
			'name' => __( 'Time to close', 'velocitymu' ),
			'id'   => 'velocity_program_time_to_close',
			'type' => 'text_medium',
			'desc' => __( 'For example: 2 weeks to 45 days', 'velocitymu' ),
		) );

		$program->add_field( array(
			'name' => __( 'Program page', 'velocitymu' ),
			'id'   => 'velocity_program_page',
			'type' => 'text_url',
			'desc' => __( 'Link to the page about this program. Leave empty until the page exists.', 'velocitymu' ),
		) );
	}

	/**
	 * Adds a meta box for quotes
	 */
	public static function info_meta_box() {

		$info_meta_box = new_cmb2_box( array(
			'id'            => 'info_meta_box',
			'title'         => __( 'Additional Information', 'velocitymu' ),
			'object_types'  => array( 'velocity-property' ), // Post type
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
		) );

		$info_meta_box->add_field( array(
			'name' => __( 'Property Type', 'velocitymu' ),
			'id'   => 'velocity_property_property_type',
			'type' => 'taxonomy_select',
			'desc' => 'Select a property type',
            'taxonomy' => 'velocity-property-type',
		) );

        $info_meta_box->add_field( array(
			'name' => __( 'Property Purpose', 'velocitymu' ),
			'id'   => 'velocity_property_property_purpose',
			'type' => 'taxonomy_select',
			'desc' => 'Select a property purpose',
            'taxonomy' => 'velocity-property-purpose',
		) );

        $info_meta_box->add_field( array(
			'name' => __( 'Loan Amount', 'velocitymu' ),
			'id'   => 'velocity_property_loan_amount',
			'type' => 'text_small',
			'desc' => __( 'Value of the loan', 'velocitymu' ),
		) );

        $info_meta_box->add_field( array(
			'name' => __( 'Location', 'velocitymu' ),
			'id'   => 'velocity_property_location',
			'type' => 'text_medium',
			'desc' => __( 'City and state of location, ex. Phoenix, AZ', 'velocitymu' ),
		) );
        
        $info_meta_box->add_field( array(
			'name' => __( 'Loan Program', 'velocitymu' ),
			'id'   => 'velocity_property_loan_program',
			'type' => 'taxonomy_select',
			'desc' => 'Select a loan program',
            'taxonomy' => 'velocity-property-program',
		) );

        $info_meta_box->add_field( array(
			'name' => __( 'Date Funded', 'velocitymu' ),
			'id'   => 'velocity_property_date_funded',
			'type' => 'text_date',
			'desc' => __( 'Month, day, and year of funding', 'velocitymu' ),
			'date_format' => 'Y-m-d',
		) );

        $info_meta_box->add_field( array(
			'name' => __( 'Broker Fee', 'velocitymu' ),
			'id'   => 'velocity_property_broker_fee',
			'type' => 'text_money',
			'desc' => __( 'Value of the broker fee', 'velocitymu' ),
		) );

        $info_meta_box->add_field( array(
			'name' => __( 'AE', 'velocitymu' ),
			'id'   => 'velocity_property_ae',
			'type' => 'taxonomy_select',
			'desc' => 'Select an AE',
            'taxonomy' => 'velocity-property-ae',
		) );

		$info_meta_box->add_field( array(
			'name' => __( 'AE Role', 'velocitymu' ),
			'id'   => 'velocity_property_ae_role',
			'type' => 'text_medium',
			'desc' => 'Role of the AE on the loan',
		) );

		$info_meta_box->add_field( array(
			'name' => __( 'Processed Details Text', 'velocitymu' ),
			'id'   => 'velocity_property_processed_details_text',
			'type' => 'text_medium',
			'desc' => 'Example: Closed in 15 days',
		) );

		$info_meta_box->add_field( array(
			'name'       => __( 'Days to Close', 'velocitymu' ),
			'id'         => 'velocity_property_days_to_close',
			'type'       => 'text_small',
			'desc'       => __( 'Number of days from application to funding, ex. 21', 'velocitymu' ),
			'attributes' => array(
				'type' => 'number',
				'min'  => '0',
			),
		) );
	}

	/**
	 * Links each AE term to its profile in AEs, for the photo and title.
	 */
	public static function ae_meta_box() {

		$ae = new_cmb2_box( array(
			'id'           => 'velocity_property_ae_meta_box',
			'title'        => __( 'AE profile', 'velocitymu' ),
			'object_types' => array( 'term' ),
			'taxonomies'   => array( 'velocity-property-ae' ),
		) );

		$ae->add_field( array(
			'name'       => __( 'AE profile', 'velocitymu' ),
			'id'         => 'velocity_property_ae_profile',
			'type'       => 'select',
			'desc'       => __( 'The page in AEs for this person. Case studies take the photo and title from it.', 'velocitymu' ),
			'options_cb' => array( __CLASS__, 'ae_profile_options' ),
		) );
	}

	/**
	 * Options for the AE profile select.
	 */
	public static function ae_profile_options() {

		$options = array( '' => __( '— None —', 'velocitymu' ) );
		$aes     = get_posts( array(
			'post_type'      => 'velocity-ae',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		foreach ( $aes as $ae ) {
			$options[ $ae->ID ] = $ae->post_title;
		}

		return $options;
	}

	/**
	 * Loan amounts are typed as text, ex. 1,120,000. A copy as a plain
	 * number is kept next to it, so lists can sort by amount.
	 */
	public static function sync_loan_amount_number( $meta_id, $post_id, $meta_key, $meta_value ) {

		$keys = array(
			'velocity_property_loan_amount'   => 'velocity_property_loan_amount_number',
			'velocity_case_study_loan_amount' => 'velocity_case_study_loan_amount_number',
		);

		if ( ! isset( $keys[ $meta_key ] ) ) {
			return;
		}

		$number = self::to_number( $meta_value );

		if ( null === $number ) {
			delete_post_meta( $post_id, $keys[ $meta_key ] );
			return;
		}

		update_post_meta( $post_id, $keys[ $meta_key ], $number );
	}

	/**
	 * Removes the number copy when the loan amount is removed.
	 */
	public static function delete_loan_amount_number( $meta_ids, $post_id, $meta_key, $meta_value ) {

		$keys = array(
			'velocity_property_loan_amount'   => 'velocity_property_loan_amount_number',
			'velocity_case_study_loan_amount' => 'velocity_case_study_loan_amount_number',
		);

		if ( isset( $keys[ $meta_key ] ) ) {
			delete_post_meta( $post_id, $keys[ $meta_key ] );
		}
	}

	/**
	 * Turns a typed amount like $1,120,000.00 into 1120000.
	 */
	public static function to_number( $value ) {

		$value = preg_replace( '/[^0-9.]/', '', (string) $value );

		if ( '' === $value || ! is_numeric( $value ) ) {
			return null;
		}

		return (int) round( (float) $value );
	}

	/**
	 * Converts saved date to formatted
	 */
	public static function date_shortcode( $atts ) {

		$atts = shortcode_atts( array(
			'format' => 'M j, Y',
			'date' => date( 'Y-m-d', time() ),
		), $atts, 'date_shortcode' );

		return date( $atts['format'], strtotime( $atts['date'] ) );

	}

	/**
	 * Removes cents from dollar output
	 */
	public static function money_shortcode( $atts ) {

		$atts = shortcode_atts( array(
			'amount' => '1000',
		), $atts, 'money_shortcode' );

		return substr( $atts['amount'], 0, -3 );

	}

}

Velocity_Properties::hooks();
