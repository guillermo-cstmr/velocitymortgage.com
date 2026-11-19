<?php
/**
 * News and Insights: what its Kadence queries have no setting for.
 *
 * The page that lists the articles is a Kadence Element with two Kadence
 * queries (Kadence > Queries), built by wp velocity seed-insights and edited
 * with blocks. Each query has a role, kept beside it in the field
 * "_vm_query_role", and this file acts on it:
 *
 * - "insights-featured": lists the one article with "Feature this article"
 *   ticked in its editor. The plugin Velocity Insights keeps that tick in
 *   the field "vmi_featured" and takes it off the article featured before.
 * - "insights-index": the list of every article, minus the featured one,
 *   which is already at the top of the page.
 *
 * A query with no role is left alone.
 *
 * The address of a topic (/insights/topic/...) goes to the list with that
 * topic chosen, so there is one page that lists articles, not one per topic.
 *
 * See docs/components.md, "News and Insights".
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * The featured article: the ID of the published article that has the tick,
 * or 0 when none has.
 *
 * @return int
 */
function vm_insights_featured_id() {
	static $id = null;

	if ( null === $id ) {
		$found = get_posts(
			array(
				'post_type'        => 'insight',
				'post_status'      => 'publish',
				'meta_key'         => 'vmi_featured', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'       => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'numberposts'      => 1,
				'fields'           => 'ids',
				'orderby'          => 'date',
				'order'            => 'DESC',
				'suppress_filters' => false,
			)
		);

		$id = $found ? (int) $found[0] : 0;
	}

	return $id;
}

/**
 * Adjust the query of a Kadence query that has a role.
 *
 * @param array $query    WP_Query arguments, as Kadence built them.
 * @param array $settings The query's own settings.
 * @param int   $query_id Kadence query post ID.
 * @return array
 */
function vm_insights_query_vars( $query, $settings, $query_id ) {
	$role = (string) get_post_meta( (int) $query_id, '_vm_query_role', true );

	if ( 'insights-featured' === $role ) {
		$featured = vm_insights_featured_id();

		// No article has the tick: the list is empty and its section hides.
		$query['post__in']       = array( $featured ? $featured : 0 );
		$query['posts_per_page'] = 1;
	}

	if ( 'insights-index' === $role ) {
		$featured = vm_insights_featured_id();

		if ( $featured ) {
			$exclude                = isset( $query['post__not_in'] ) ? (array) $query['post__not_in'] : array();
			$exclude[]              = $featured;
			$query['post__not_in']  = array_values( array_unique( array_map( 'intval', $exclude ) ) );

			if ( ! empty( $query['post__in'] ) ) {
				$query['post__in'] = array_values( array_diff( array_map( 'intval', (array) $query['post__in'] ), array( $featured ) ) );

				// Every match was the featured article: list nothing, not everything.
				if ( ! $query['post__in'] ) {
					$query['post__in'] = array( 0 );
				}
			}
		}
	}

	return $query;
}
add_filter( 'kadence_blocks_pro_query_loop_query_vars', 'vm_insights_query_vars', 10, 3 );

/**
 * Send the address of a topic to the list of articles, filtered by it.
 *
 * "topic" is the name of the Topics filter in the list's query (its "slug",
 * in the filter's settings), and Kadence reads the topic's ID from it.
 */
function vm_insights_topic_redirect() {
	if ( ! is_tax( 'insight_topic' ) ) {
		return;
	}

	$term = get_queried_object();
	$list = get_post_type_archive_link( 'insight' );

	if ( ! $term || empty( $term->term_id ) || ! $list ) {
		return;
	}

	wp_safe_redirect( add_query_arg( 'topic', (int) $term->term_id, $list ) . '#articles', 301 );
	exit;
}
add_action( 'template_redirect', 'vm_insights_topic_redirect' );
