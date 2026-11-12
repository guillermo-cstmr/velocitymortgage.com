<?php

if ( ! class_exists( 'Extended_CPT' ) ) {
	require_once VELOCITY_PATH . 'lib/extended-cpts/extended-cpts.php';
}

require_once VELOCITY_CMB2_PATH;

/**
 * Case studies.
 *
 * A case study tells the story of a funded deal. When it is linked to a
 * deal in Properties, it copies the deal's program, purpose, property type,
 * AE, region, amount, date, days to close and location, and keeps them in
 * sync when the deal changes. Without a deal, those are entered by hand.
 *
 * The copies live on the case study itself because Kadence query cards,
 * filters and sorting only read the post being listed.
 */
class Velocity_Case_Studies {

	const POST_TYPE = 'velocity-case-study';

	const TAXONOMIES = array(
		'velocity-property-program',
		'velocity-property-purpose',
		'velocity-property-type',
		'velocity-property-ae',
		'velocity-property-region',
	);

	/**
	 * Deal field => case study field.
	 */
	const DEAL_FIELDS = array(
		'velocity_property_loan_amount'   => 'velocity_case_study_loan_amount',
		'velocity_property_date_funded'   => 'velocity_case_study_date',
		'velocity_property_days_to_close' => 'velocity_case_study_days_to_close',
		'velocity_property_location'      => 'velocity_case_study_location',
	);

	function __construct() {}

	public static function hooks() {

		add_action( 'init', array( __CLASS__, 'cpt_setup' ) );
		add_action( 'init', array( __CLASS__, 'taxonomy_setup' ), 20 );
		add_action( 'cmb2_admin_init', array( __CLASS__, 'info_meta_box' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'refresh' ), 99 );
		add_action( 'save_post_velocity-property', array( __CLASS__, 'refresh_from_deal' ), 99 );
		add_filter( 'wpseo_metabox_prio', array( __CLASS__, 'yoast_below' ) );
	}

	/**
	 * Keeps Yoast below the deal box on case studies, so the deal is the
	 * first thing under the content.
	 */
	public static function yoast_below( $priority ) {

		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( $screen && self::POST_TYPE === $screen->post_type ) {
				return 'low';
			}
		}

		return $priority;
	}

	/**
	 * Custom Post Type set up
	 */
	public static function cpt_setup() {

		register_extended_post_type(
			self::POST_TYPE,
			array(
				'menu_icon'    => 'dashicons-portfolio',
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'show_in_rest' => true,
				'has_archive'  => 'case-studies',
			),
			array(
				'singular' => __( 'Case Study', 'velocitymu' ),
				'plural'   => __( 'Case Studies', 'velocitymu' ),
				'slug'     => 'case-study',
			)
		);
	}

	/**
	 * Shares the deal taxonomies, so Featured Deals and Case Studies filter
	 * with the same terms.
	 */
	public static function taxonomy_setup() {

		foreach ( self::TAXONOMIES as $taxonomy ) {
			register_taxonomy_for_object_type( $taxonomy, self::POST_TYPE );
		}
	}

	/**
	 * Deal link and the deal at a glance fields.
	 */
	public static function info_meta_box() {

		$post_id = 0;
		if ( isset( $_GET['post'] ) ) {
			$post_id = absint( $_GET['post'] );
		} elseif ( isset( $_POST['post_ID'] ) ) {
			$post_id = absint( $_POST['post_ID'] );
		}

		$deal_id = $post_id ? absint( get_post_meta( $post_id, 'velocity_case_study_deal', true ) ) : 0;

		$info_meta_box = new_cmb2_box( array(
			'id'           => 'velocity_case_study_meta_box',
			'title'        => __( 'Deal at a glance', 'velocitymu' ),
			'object_types' => array( self::POST_TYPE ),
			'context'      => 'normal',
			'priority'     => 'high',
			'closed'       => false,
			'show_names'   => true,
		) );

		$info_meta_box->add_field( array(
			'name'       => __( 'Deal', 'velocitymu' ),
			'id'         => 'velocity_case_study_deal',
			'type'       => 'select',
			'desc'       => __( 'The deal in Properties this story is about. Its program, purpose, property type, AE, region, amount, date, days to close and location are copied here on save, and the fields below are locked. Choose none to enter them by hand.', 'velocitymu' ),
			'options_cb' => array( __CLASS__, 'deal_options' ),
			'after'      => $deal_id ? '<p><a href="' . esc_url( get_edit_post_link( $deal_id ) ) . '">' . esc_html__( 'Edit the deal', 'velocitymu' ) . '</a></p>' : '',
		) );

		$locked = $deal_id ? array( 'readonly' => 'readonly' ) : array();
		$note   = $deal_id ? __( 'From the deal.', 'velocitymu' ) . ' ' : '';

		$info_meta_box->add_field( array(
			'name'       => __( 'Loan Amount', 'velocitymu' ),
			'id'         => 'velocity_case_study_loan_amount',
			'type'       => 'text_small',
			'desc'       => $note . __( 'Value of the loan, ex. 1,250,000', 'velocitymu' ),
			'attributes' => $locked,
		) );

		$info_meta_box->add_field( array(
			'name'        => __( 'Deal Date', 'velocitymu' ),
			'id'          => 'velocity_case_study_date',
			'type'        => $deal_id ? 'text_small' : 'text_date',
			'desc'        => $note . __( 'Date the deal funded', 'velocitymu' ),
			'date_format' => 'Y-m-d',
			'attributes'  => $locked,
		) );

		$info_meta_box->add_field( array(
			'name'       => __( 'Days to Close', 'velocitymu' ),
			'id'         => 'velocity_case_study_days_to_close',
			'type'       => 'text_small',
			'desc'       => $note . __( 'Number of days from application to funding, ex. 21', 'velocitymu' ),
			'attributes' => array_merge( array( 'type' => 'number', 'min' => '0' ), $locked ),
		) );

		$info_meta_box->add_field( array(
			'name'       => __( 'Location', 'velocitymu' ),
			'id'         => 'velocity_case_study_location',
			'type'       => 'text_medium',
			'desc'       => $note . __( 'City and state of location, ex. Phoenix, AZ', 'velocitymu' ),
			'attributes' => $locked,
		) );
	}

	/**
	 * Options for the deal select.
	 */
	public static function deal_options() {

		$options = array( '' => __( '— None, enter by hand —', 'velocitymu' ) );
		$deals   = get_posts( array(
			'post_type'      => 'velocity-property',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		foreach ( $deals as $deal ) {
			$label  = $deal->post_title;
			$amount = get_post_meta( $deal->ID, 'velocity_property_loan_amount', true );
			if ( $amount ) {
				$label .= ' — $' . $amount;
			}
			if ( 'publish' !== $deal->post_status ) {
				$label .= ' (' . $deal->post_status . ')';
			}
			$options[ $deal->ID ] = $label;
		}

		return $options;
	}

	/**
	 * Copies the deal into the case study, then fills the display fields.
	 */
	public static function refresh( $post_id ) {

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		$deal_id = absint( get_post_meta( $post_id, 'velocity_case_study_deal', true ) );

		if ( $deal_id && 'velocity-property' === get_post_type( $deal_id ) ) {

			foreach ( self::TAXONOMIES as $taxonomy ) {
				$terms = wp_get_object_terms( $deal_id, $taxonomy, array( 'fields' => 'ids' ) );
				if ( ! is_wp_error( $terms ) ) {
					wp_set_object_terms( $post_id, $terms, $taxonomy );
				}
			}

			foreach ( self::DEAL_FIELDS as $deal_field => $case_field ) {
				$value = get_post_meta( $deal_id, $deal_field, true );
				if ( '' === $value || false === $value ) {
					delete_post_meta( $post_id, $case_field );
				} else {
					update_post_meta( $post_id, $case_field, $value );
				}
			}
		}

		self::fill_display_fields( $post_id );
	}

	/**
	 * When a deal is saved, refreshes the case studies linked to it.
	 */
	public static function refresh_from_deal( $deal_id ) {

		if ( wp_is_post_revision( $deal_id ) || wp_is_post_autosave( $deal_id ) ) {
			return;
		}

		$case_studies = get_posts( array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => 'velocity_case_study_deal',
			'meta_value'     => $deal_id,
		) );

		foreach ( $case_studies as $case_study_id ) {
			self::refresh( $case_study_id );
		}
	}

	/**
	 * Plain text and image fields that Kadence dynamic content can show:
	 * program name and page, purpose, and the AE's name, title and photo.
	 */
	public static function fill_display_fields( $post_id ) {

		$values = array(
			'velocity_case_study_program'     => '',
			'velocity_case_study_program_url' => '',
			'velocity_case_study_purpose'     => '',
			'velocity_case_study_ae_name'     => '',
			'velocity_case_study_ae_title'    => '',
			'velocity_case_study_ae_photo'    => '',
			'velocity_case_study_ae_photo_id' => '',
		);

		$program = self::first_term( $post_id, 'velocity-property-program' );
		if ( $program ) {
			$values['velocity_case_study_program']     = $program->name;
			$values['velocity_case_study_program_url'] = get_term_meta( $program->term_id, 'velocity_program_page', true );
		}

		$purpose = self::first_term( $post_id, 'velocity-property-purpose' );
		if ( $purpose ) {
			$values['velocity_case_study_purpose'] = $purpose->name;
		}

		$ae = self::first_term( $post_id, 'velocity-property-ae' );
		if ( $ae ) {
			$values['velocity_case_study_ae_name'] = $ae->name;
			$profile = absint( get_term_meta( $ae->term_id, 'velocity_property_ae_profile', true ) );
			if ( $profile ) {
				$values['velocity_case_study_ae_title'] = get_post_meta( $profile, 'velocity_ae_title', true );
				$photo_id = get_post_thumbnail_id( $profile );
				if ( $photo_id ) {
					$values['velocity_case_study_ae_photo_id'] = $photo_id;
					$values['velocity_case_study_ae_photo']    = wp_get_attachment_image_url( $photo_id, 'medium' );
				}
			}
		}

		foreach ( $values as $key => $value ) {
			if ( '' === $value || false === $value || null === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	/**
	 * First term of a taxonomy on a post, or null.
	 */
	public static function first_term( $post_id, $taxonomy ) {

		$terms = get_the_terms( $post_id, $taxonomy );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return null;
		}

		return $terms[0];
	}

}

Velocity_Case_Studies::hooks();
