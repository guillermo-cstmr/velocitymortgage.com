<?php

if ( ! class_exists( 'Extended_CPT' ) ) {
	require_once VELOCITY_PATH . 'lib/extended-cpts/extended-cpts.php';
}

require_once VELOCITY_CMB2_PATH;

class Velocity_Properties {

	/**
	 * The taxonomy of the state a deal is in. It was made as "Region" and
	 * keeps that name inside, so nothing filed under it is lost.
	 */
	const STATE_TAXONOMY = 'velocity-property-region';

	function __construct() {}

	public static function hooks() {

		add_action( 'init', array( __CLASS__, 'cpt_setup' ) );
		add_action( 'cmb2_admin_init', array( __CLASS__, 'info_meta_box' ) );
		add_action( 'cmb2_admin_init', array( __CLASS__, 'program_meta_box' ) );
		add_action( 'cmb2_admin_init', array( __CLASS__, 'ae_meta_box' ) );
		add_action( 'added_post_meta', array( __CLASS__, 'sync_loan_amount_number' ), 10, 4 );
		add_action( 'updated_post_meta', array( __CLASS__, 'sync_loan_amount_number' ), 10, 4 );
		add_action( 'deleted_post_meta', array( __CLASS__, 'delete_loan_amount_number' ), 10, 4 );
		add_action( 'added_post_meta', array( __CLASS__, 'sync_state' ), 10, 4 );
		add_action( 'updated_post_meta', array( __CLASS__, 'sync_state' ), 10, 4 );
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
				// The list of deals is a page, Featured Deals, built in the block
				// editor. It has the address this archive used to have.
				'has_archive'  => false,
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
				'singular' => __( 'State', 'velocitymu' ),
				'plural'   => __( 'States', 'velocitymu' ),
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
			'desc' => __( 'City and state of location, ex. Phoenix, AZ. The deal is filed under that state, for the State filter of Featured Deals.', 'velocitymu' ),
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
	 * Files a deal under the state of its location, so the State filter of
	 * Featured Deals can list it. "Houston, TX" files the deal under Texas.
	 *
	 * A location with no state that can be read, like a city alone, changes
	 * nothing: the state ticked in the "States" box stays.
	 */
	public static function sync_state( $meta_id, $post_id, $meta_key, $meta_value ) {

		if ( 'velocity_property_location' !== $meta_key || 'velocity-property' !== get_post_type( $post_id ) ) {
			return;
		}

		self::set_state( $post_id, self::state_from_location( $meta_value ) );
	}

	/**
	 * Files a deal under a state, by its name. The state is created the first
	 * time a deal needs it.
	 *
	 * @return bool Whether the deal was filed.
	 */
	public static function set_state( $post_id, $state ) {

		if ( '' === $state || ! taxonomy_exists( self::STATE_TAXONOMY ) ) {
			return false;
		}

		$term = term_exists( $state, self::STATE_TAXONOMY );

		if ( ! $term ) {
			$term = wp_insert_term( $state, self::STATE_TAXONOMY );
		}

		if ( ! $term || is_wp_error( $term ) ) {
			return false;
		}

		// By ID: a name with a comma, like "Washington, D.C.", would be read
		// as two names.
		wp_set_object_terms( $post_id, array( (int) $term['term_id'] ), self::STATE_TAXONOMY );

		return true;
	}

	/**
	 * The state in a location as it is typed on a deal: "Houston, TX",
	 * "Washington, DC" or "Pennsylvania".
	 *
	 * @return string The state's name, or nothing if none can be read.
	 */
	public static function state_from_location( $location ) {

		$parts = explode( ',', (string) $location );
		$last  = strtoupper( trim( str_replace( '.', '', end( $parts ) ) ) );

		if ( '' === $last ) {
			return '';
		}

		foreach ( self::states() as $code => $name ) {
			if ( $last === $code || $last === strtoupper( $name ) ) {
				return $name;
			}
		}

		return '';
	}

	/**
	 * The states and the District of Columbia, by postal abbreviation.
	 */
	public static function states() {

		return array(
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'Washington, D.C.',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
		);
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
