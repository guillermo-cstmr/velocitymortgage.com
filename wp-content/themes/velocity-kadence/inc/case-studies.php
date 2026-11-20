<?php
/**
 * Case Studies: what their Kadence queries and blocks have no setting for.
 *
 * The list at /case-studies/ and the page of a case study are two Kadence
 * Elements (Kadence > Elements), built by wp velocity seed-cases and edited
 * with blocks. They follow News and Insights (inc/insights.php), and share
 * its article styles, its share links and its patterns.
 *
 * A case study keeps its figures in the box "Deal at a glance" of its editor
 * (the plugin's): the deal it is about, or the amount, date, days to close
 * and location typed by hand. Its program, purpose, property type and
 * account executive come from the deal, or from the boxes in the sidebar.
 *
 * This file adds:
 *
 * - "Featured case study", a box in the editor. One case study at a time is
 *   featured at the top of the list.
 * - What each Kadence query does by its role ("_vm_query_role"):
 *   "cases-featured" lists the featured one, "cases-index" every other one,
 *   "cases-related" the latest of the same program as the one being read,
 *   or the latest of any program when that one has fewer than three.
 * - The filters of the list show only once there are more than ten case
 *   studies, as the design notes.
 * - Three parts of the page of a case study that no block reads:
 *   [vm_case_program], its program as a link to the program's page;
 *   [vm_case_glance], the orange strip "Deal at a glance"; and
 *   [vm_case_ae], the card of the account executive who closed the deal.
 *
 * See docs/components.md, "Case Studies".
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * How many case studies the list needs before its filters show: more than
 * this many.
 */
const VM_CASES_FILTERS_FROM = 10;

/* -------------------------------------------------------------------------
 * Featured case study
 * ---------------------------------------------------------------------- */

/**
 * IDs of the case studies marked as featured, other than one.
 *
 * @param int $exclude Case study ID to leave out.
 * @return int[]
 */
function vm_case_featured_ids( $exclude = 0 ) {
	return get_posts(
		array(
			'post_type'    => 'velocity-case-study',
			'post_status'  => 'any',
			'meta_key'     => 'vm_case_featured', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'   => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'numberposts'  => -1,
			'fields'       => 'ids',
			'post__not_in' => $exclude ? array( (int) $exclude ) : array(),
		)
	);
}

/**
 * The featured case study: the ID of the published one that has the tick,
 * or 0 when none has.
 *
 * @return int
 */
function vm_case_featured_id() {
	static $id = null;

	if ( null === $id ) {
		$found = get_posts(
			array(
				'post_type'   => 'velocity-case-study',
				'post_status' => 'publish',
				'meta_key'    => 'vm_case_featured', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'  => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'numberposts' => 1,
				'fields'      => 'ids',
			)
		);

		$id = $found ? (int) $found[0] : 0;
	}

	return $id;
}

/**
 * The box in the editor of a case study.
 */
function vm_case_featured_box() {
	add_meta_box( 'vm_case_featured', __( 'Featured case study', 'velocity-kadence' ), 'vm_case_featured_box_render', 'velocity-case-study', 'side', 'high' );
}
add_action( 'add_meta_boxes_velocity-case-study', 'vm_case_featured_box' );

/**
 * Print the box: the tick, and which case study is featured now.
 *
 * @param WP_Post $post Case study.
 */
function vm_case_featured_box_render( $post ) {
	wp_nonce_field( 'vm_case_featured_save', 'vm_case_featured_nonce' );

	$others = vm_case_featured_ids( $post->ID );

	printf(
		'<p style="margin-top:0"><label><input type="checkbox" name="vm_case_featured" value="1"%s> %s</label></p>',
		checked( '1', get_post_meta( $post->ID, 'vm_case_featured', true ), false ),
		esc_html__( 'Feature this case study at the top of the Case Studies page', 'velocity-kadence' )
	);

	if ( $others ) {
		printf(
			'<p class="description">%s <a href="%s">%s</a>. %s</p>',
			esc_html__( 'Featured now:', 'velocity-kadence' ),
			esc_url( get_edit_post_link( $others[0] ) ),
			esc_html( get_the_title( $others[0] ) ),
			esc_html__( 'Ticking this box takes its place.', 'velocity-kadence' )
		);
	} else {
		echo '<p class="description">' . esc_html__( 'One case study is featured at a time.', 'velocity-kadence' ) . '</p>';
	}
}

/**
 * Save the tick. Featuring a case study takes the tick off the others.
 *
 * @param int $post_id Case study ID.
 */
function vm_case_featured_save( $post_id ) {
	if ( ! isset( $_POST['vm_case_featured_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vm_case_featured_nonce'] ) ), 'vm_case_featured_save' ) ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( empty( $_POST['vm_case_featured'] ) ) {
		delete_post_meta( $post_id, 'vm_case_featured' );
		return;
	}

	update_post_meta( $post_id, 'vm_case_featured', '1' );

	foreach ( vm_case_featured_ids( $post_id ) as $other ) {
		delete_post_meta( $other, 'vm_case_featured' );
	}
}
add_action( 'save_post_velocity-case-study', 'vm_case_featured_save' );

/* -------------------------------------------------------------------------
 * The queries
 * ---------------------------------------------------------------------- */

/**
 * Adjust the query of a Kadence query that has a role.
 *
 * @param array $query    WP_Query arguments, as Kadence built them.
 * @param array $settings The query's own settings.
 * @param int   $query_id Kadence query post ID.
 * @return array
 */
function vm_case_query_vars( $query, $settings, $query_id ) {
	$role = (string) get_post_meta( (int) $query_id, '_vm_query_role', true );

	if ( 'cases-featured' === $role ) {
		$featured = vm_case_featured_id();

		// No case study has the tick: the list is empty and its section hides.
		$query['post__in']       = array( $featured ? $featured : 0 );
		$query['posts_per_page'] = 1;
	}

	if ( 'cases-index' === $role ) {
		$featured = vm_case_featured_id();

		if ( $featured ) {
			$exclude               = isset( $query['post__not_in'] ) ? (array) $query['post__not_in'] : array();
			$exclude[]             = $featured;
			$query['post__not_in'] = array_values( array_unique( array_map( 'intval', $exclude ) ) );

			if ( ! empty( $query['post__in'] ) ) {
				$query['post__in'] = array_values( array_diff( array_map( 'intval', (array) $query['post__in'] ), array( $featured ) ) );

				// Every match was the featured one: list nothing, not everything.
				if ( ! $query['post__in'] ) {
					$query['post__in'] = array( 0 );
				}
			}
		}
	}

	if ( 'cases-related' === $role && is_singular( 'velocity-case-study' ) ) {
		$programs = wp_get_post_terms( get_queried_object_id(), 'velocity-property-program', array( 'fields' => 'ids' ) );

		// The same program, when it has at least three other case studies to
		// show. With fewer, the latest of any program fill the three places.
		$enough = false;

		if ( $programs && ! is_wp_error( $programs ) ) {
			$others = get_posts(
				array(
					'post_type'    => 'velocity-case-study',
					'post_status'  => 'publish',
					'numberposts'  => 3,
					'fields'       => 'ids',
					'post__not_in' => array( get_queried_object_id() ),
					'tax_query'    => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
						array(
							'taxonomy' => 'velocity-property-program',
							'field'    => 'term_id',
							'terms'    => array_map( 'intval', $programs ),
						),
					),
				)
			);
			$enough = count( $others ) >= 3;
		}

		if ( $enough ) {
			$query['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'velocity-property-program',
					'field'    => 'term_id',
					'terms'    => array_map( 'intval', $programs ),
				),
			);
		}
	}

	return $query;
}
add_filter( 'kadence_blocks_pro_query_loop_query_vars', 'vm_case_query_vars', 10, 3 );

/**
 * Mark the list while it has too few case studies for its filters: the
 * stylesheet hides the filters panel under this class.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function vm_case_body_class( $classes ) {
	if ( is_post_type_archive( 'velocity-case-study' ) ) {
		$count = wp_count_posts( 'velocity-case-study' );

		if ( (int) $count->publish <= VM_CASES_FILTERS_FROM ) {
			$classes[] = 'vm-cases-few';
		}
	}

	return $classes;
}
add_filter( 'body_class', 'vm_case_body_class' );

/* -------------------------------------------------------------------------
 * The page of a case study
 * ---------------------------------------------------------------------- */

/**
 * The case study being read, or null.
 *
 * @return WP_Post|null
 */
function vm_case_current() {
	$post = get_queried_object();

	return ( $post instanceof WP_Post && 'velocity-case-study' === $post->post_type && is_singular() ) ? $post : null;
}

/**
 * [vm_case_program]: the program of the case study being read, as a link to
 * the program's page when the program has one (Properties > Programs,
 * "Program page").
 *
 * @return string
 */
function vm_case_program_shortcode() {
	$post = vm_case_current();

	if ( ! $post ) {
		return '';
	}

	$name = trim( (string) get_post_meta( $post->ID, 'velocity_case_study_program', true ) );
	$url  = trim( (string) get_post_meta( $post->ID, 'velocity_case_study_program_url', true ) );

	if ( '' === $name ) {
		return '';
	}

	// The program has no "Program page" set: its page under Programs
	// Overview, if there is one by its name.
	if ( '' === $url ) {
		$url = vm_program_url( $name );
	}

	return '<p class="vm-case-program">' . ( '' !== $url ? '<a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a>' : esc_html( $name ) ) . '</p>';
}
add_shortcode( 'vm_case_program', 'vm_case_program_shortcode' );

/**
 * [vm_case_glance]: the strip "Deal at a glance" of the case study being
 * read: days to close, loan amount, loan purpose and deal date. A figure
 * that is empty is left out, and so is the whole strip when all are.
 *
 * @return string
 */
function vm_case_glance_shortcode() {
	$post = vm_case_current();

	if ( ! $post ) {
		return '';
	}

	$days    = trim( (string) get_post_meta( $post->ID, 'velocity_case_study_days_to_close', true ) );
	$figures = array(
		__( 'Closed in', 'velocity-kadence' )    => '' !== $days ? sprintf( /* translators: %s: number of days. */ _n( '%s day', '%s days', (int) $days, 'velocity-kadence' ), number_format_i18n( (int) $days ) ) : '',
		__( 'Loan amount', 'velocity-kadence' )  => vm_dynamic_case_amount( $post->ID ),
		__( 'Loan Purpose', 'velocity-kadence' ) => trim( (string) get_post_meta( $post->ID, 'velocity_case_study_purpose', true ) ),
		__( 'Deal Date', 'velocity-kadence' )    => vm_dynamic_case_date( $post->ID ),
	);

	$html = '';
	foreach ( $figures as $label => $value ) {
		if ( '' !== $value ) {
			$html .= '<div class="vm-glance__figure"><dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( $value ) . '</dd></div>';
		}
	}

	if ( '' === $html ) {
		return '';
	}

	return '<div class="vm-glance"><div class="vm-glance__inner"><p class="vm-glance__title">' . esc_html__( 'Deal at a glance', 'velocity-kadence' ) . '</p><dl class="vm-glance__figures">' . $html . '</dl></div></div>';
}
add_shortcode( 'vm_case_glance', 'vm_case_glance_shortcode' );

/**
 * [vm_case_ae]: the card of the account executive who closed the deal of
 * the case study being read: photo, name and title. Empty when the case
 * study has no account executive.
 *
 * @return string
 */
function vm_case_ae_shortcode() {
	$post = vm_case_current();

	if ( ! $post ) {
		return '';
	}

	$line = vm_dynamic_case_closed_by( $post->ID );

	if ( '' === $line ) {
		return '';
	}

	$photo_id = (int) get_post_meta( $post->ID, 'velocity_case_study_ae_photo_id', true );
	$photo    = $photo_id ? wp_get_attachment_image( $photo_id, 'medium', false, array( 'class' => 'vm-case-ae__photo', 'alt' => '' ) ) : '';

	return '<aside class="vm-case-ae">' . $photo . '<p class="vm-case-ae__text">' . esc_html( $line ) . '</p></aside>';
}
add_shortcode( 'vm_case_ae', 'vm_case_ae_shortcode' );

/**
 * The page of a case study has the share links of an article, and their
 * script (the "copy the link" icon).
 */
function vm_case_script() {
	if ( ! is_singular( 'velocity-case-study' ) ) {
		return;
	}

	$file = VELOCITY_KADENCE_DIR . '/assets/js/insight.js';
	wp_enqueue_script(
		'velocity-kadence-insight',
		VELOCITY_KADENCE_URI . '/assets/js/insight.js',
		array(),
		file_exists( $file ) ? (string) filemtime( $file ) : null,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'vm_case_script' );
