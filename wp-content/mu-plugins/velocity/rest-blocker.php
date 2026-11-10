<?php
/**
 * Plugin Name: VegasGeek Rest Blocker
 * Plugin URI: https://vegasgeek.com
 * Description: Blocks the user and media REST endpoints for security purposes.
 * Version: 1.0.0
 * Author: VegasGeek
 * Author URI: https://vegasgeek.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vg-rest-blocker
 *
 * @package vg-rest-blocker
 */

/**
 * REST Endpoint Blocker
 *
 * Blocks configured REST endpoints with a 403. Endpoint list is stored in the
 * 'rest_hider_hidden_endpoints' option as an array of route => bool.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block REST requests for any route flagged in the hidden-endpoints option.
 *
 * @param mixed           $result  Current dispatch result.
 * @param WP_REST_Server  $server  REST server instance.
 * @param WP_REST_Request $request Incoming request.
 * @return mixed WP_Error on blocked routes, original $result otherwise.
 */
function vg_rest_blocker_filter_hidden_rest_endpoints( $result, $server, $request ) {
	$blocked_prefixes = array(
		'/wp/v2/users',
		'/wp/v2/media',
	);

	$route = $request->get_route();

	foreach ( $blocked_prefixes as $prefix ) {
		if ( str_starts_with( $route, $prefix ) ) {
			return new WP_Error(
				'forbidden',
				__( 'This REST endpoint is not available.', 'vg-rest-blocker' ),
				array( 'status' => 403 )
			);
		}
	}

	return $result;
}
add_action(
	'rest_api_init',
	function () {
		add_filter( 'rest_pre_dispatch', 'vg_rest_blocker_filter_hidden_rest_endpoints', 10, 3 );
	}
);
