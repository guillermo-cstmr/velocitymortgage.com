<?php
/**
 * Query Filters
 *
 * Custom query filters for modifying WordPress queries and integrations
 * with page builders like Kadence Blocks.
 *
 * @package Velocity_Mortgage
 * @since 1.0.0
 */

/**
 * Customize the Careers Query for the Kadence Query Loop Block.
 *
 * @param array $query_args   The existing query arguments.
 * @param array $ql_query_meta The query loop block meta data.
 * @param int   $ql_id        The query loop block ID.
 *
 * @return array Modified query arguments.
 */
function vm_filter_kadence_query_loop_careers( $query_args, $ql_query_meta, $ql_id ) {
	if ( 3246 === $ql_id ) {
		// Add a taxonomy query to filter posts by a specific category slug.
		$query_args = array(
			'post_type'   => 'velocity-career',
			'post_status' => 'publish',
		);
	}
	return $query_args;
}
add_filter( 'kadence_blocks_pro_query_loop_query_vars', 'vm_filter_kadence_query_loop_careers', 10, 3 );
