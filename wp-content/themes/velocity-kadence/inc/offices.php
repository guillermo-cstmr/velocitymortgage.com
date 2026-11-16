<?php
/**
 * Offices map: what is behind the "Offices Map" block (blocks/offices-map).
 *
 * The block holds the list of offices: a city, an address and the latitude
 * and longitude of that address. This file does three things for it:
 *
 * 1. Looks up the coordinates of an address, when an editor types one.
 *    It asks the US Census Bureau geocoder and, for a street it does not
 *    know, OpenStreetMap. Neither needs a key. Nothing is looked up when a
 *    visitor opens the page.
 * 2. Turns coordinates into a position on the map drawing.
 * 3. Prints the map, the pins and the cards.
 *
 * The drawing is assets/images/us-map.svg, exported from Figma, 904 by 585.
 * It follows the Albers USA projection, the usual one for maps of the United
 * States, with Alaska and Hawaii drawn smaller in the bottom left corner. So
 * a place is projected the standard way, and then scaled and moved onto the
 * drawing. The lower 48 states, Alaska and Hawaii each have their own scale
 * and offset, measured by comparing every state's outline in the drawing with
 * its projected outline. For the lower 48 they agree within about 3 pixels.
 *
 * If the drawing is ever replaced, these numbers have to be measured again.
 *
 * See docs/components.md, "Offices map".
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Size of the map drawing, in its own units.
 */
const VM_OFFICES_MAP_WIDTH  = 904;
const VM_OFFICES_MAP_HEIGHT = 585;

/**
 * Address lookup of the US Census Bureau.
 */
const VM_OFFICES_GEOCODER = 'https://geocoding.geo.census.gov/geocoder/locations/onelineaddress';

/**
 * Lookup by city and ZIP code, for streets the Census Bureau does not know.
 */
const VM_OFFICES_CITY_GEOCODER = 'https://nominatim.openstreetmap.org/search';

/**
 * The lines of an address, without empty ones.
 *
 * @param string $address Address as typed, one line per row.
 * @return array
 */
function vm_offices_lines( $address ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/', (string) $address ) ) ) );
}

/**
 * Latitude and longitude of a US address.
 *
 * First the exact address, in the US Census Bureau geocoder. It does not know
 * every street, office parks in particular. When it does not find one, the
 * last line of the address (city, state and ZIP code) is looked up in
 * OpenStreetMap's Nominatim instead. That puts the pin on the town, which on
 * a map of the whole country is the same place.
 *
 * @param string $address Address, on one or several lines.
 * @return array|false Array with lat, lng and precision ("address" or "city"),
 *                     or false when it is not found.
 */
function vm_offices_geocode( $address ) {
	$lines = vm_offices_lines( $address );

	if ( ! $lines ) {
		return false;
	}

	$data = vm_offices_fetch(
		VM_OFFICES_GEOCODER,
		array(
			'address'   => implode( ', ', $lines ),
			'benchmark' => 'Public_AR_Current',
			'format'    => 'json',
		)
	);

	if ( ! empty( $data['result']['addressMatches'][0]['coordinates'] ) ) {
		$point = $data['result']['addressMatches'][0]['coordinates'];

		return array(
			'lat'       => round( (float) $point['y'], 5 ),
			'lng'       => round( (float) $point['x'], 5 ),
			'precision' => 'address',
		);
	}

	$data = vm_offices_fetch(
		VM_OFFICES_CITY_GEOCODER,
		array(
			'q'            => end( $lines ),
			'countrycodes' => 'us',
			'format'       => 'jsonv2',
			'limit'        => 1,
		)
	);

	if ( isset( $data[0]['lat'], $data[0]['lon'] ) ) {
		return array(
			'lat'       => round( (float) $data[0]['lat'], 5 ),
			'lng'       => round( (float) $data[0]['lon'], 5 ),
			'precision' => 'city',
		);
	}

	return false;
}

/**
 * Asks a lookup service and returns its JSON answer.
 *
 * @param string $url  Service address.
 * @param array  $args Query arguments.
 * @return array|null Decoded answer, or null when the request failed.
 */
function vm_offices_fetch( $url, $args ) {
	$response = wp_remote_get(
		add_query_arg( array_map( 'rawurlencode', $args ), $url ),
		array( 'timeout' => 20 )
	);

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return null;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	return is_array( $data ) ? $data : null;
}

/**
 * The address lookup for the block editor: /wp-json/velocity/v1/geocode.
 *
 * The editor cannot ask the Census Bureau directly, because browsers block
 * that request. It asks the site, and the site asks the Census Bureau.
 */
function vm_offices_register_route() {
	register_rest_route(
		'velocity/v1',
		'/geocode',
		array(
			'methods'             => 'GET',
			'permission_callback' => static function () {
				return current_user_can( 'edit_pages' );
			},
			'args'                => array(
				'address' => array(
					'type'     => 'string',
					'required' => true,
				),
			),
			'callback'            => static function ( $request ) {
				$point = vm_offices_geocode( $request['address'] );

				return rest_ensure_response( $point ? array_merge( array( 'found' => true ), $point ) : array( 'found' => false ) );
			},
		)
	);
}
add_action( 'rest_api_init', 'vm_offices_register_route' );

/**
 * Albers equal-area conic projection of one point.
 *
 * Same result as d3-geo's geoConicEqualArea with these settings.
 *
 * @param float $lat   Latitude in degrees.
 * @param float $lng   Longitude in degrees.
 * @param array $setup parallels [south, north], rotate (degrees added to the
 *                     longitude), center [lng, lat], scale, translate [x, y].
 * @return array [x, y]
 */
function vm_offices_conic( $lat, $lng, $setup ) {
	$phi1 = deg2rad( $setup['parallels'][0] );
	$phi2 = deg2rad( $setup['parallels'][1] );
	$n    = ( sin( $phi1 ) + sin( $phi2 ) ) / 2;
	$c    = 1 + sin( $phi1 ) * ( 2 * $n - sin( $phi1 ) );
	$r0   = sqrt( $c ) / $n;

	$raw = static function ( $lambda, $phi ) use ( $n, $c, $r0 ) {
		$r = sqrt( $c - 2 * $n * sin( $phi ) ) / $n;

		return array( $r * sin( $lambda * $n ), $r0 - $r * cos( $lambda * $n ) );
	};

	// Longitude after the rotation, kept between -180 and 180 degrees.
	$lambda = fmod( $lng + $setup['rotate'] + 540, 360 ) - 180;

	$point  = $raw( deg2rad( $lambda ), deg2rad( $lat ) );
	$center = $raw( deg2rad( $setup['center'][0] ), deg2rad( $setup['center'][1] ) );

	return array(
		$setup['translate'][0] + $setup['scale'] * ( $point[0] - $center[0] ),
		$setup['translate'][1] - $setup['scale'] * ( $point[1] - $center[1] ),
	);
}

/**
 * Position of a place on the map drawing.
 *
 * @param float $lat Latitude in degrees.
 * @param float $lng Longitude in degrees.
 * @return array [x, y] in the drawing's units: 0 to 904 and 0 to 585.
 */
function vm_offices_project( $lat, $lng ) {
	// Albers USA, as d3-geo defines it: scale 1070, centered on 480, 250.
	$scale = 1070;

	if ( $lat > 50 && ( $lng < -129 || $lng > 170 ) ) {
		// Alaska, drawn at 35% of its size.
		$point = vm_offices_conic(
			$lat,
			$lng,
			array(
				'parallels' => array( 55, 65 ),
				'rotate'    => 154,
				'center'    => array( -2, 58.5 ),
				'scale'     => 0.35 * $scale,
				'translate' => array( 480 - 0.307 * $scale, 250 + 0.201 * $scale ),
			)
		);
		$fit   = array( 1.04782, -12.38, 1.12681, 25.80 );
	} elseif ( $lat < 30 && $lng < -150 ) {
		// Hawaii.
		$point = vm_offices_conic(
			$lat,
			$lng,
			array(
				'parallels' => array( 8, 18 ),
				'rotate'    => 157,
				'center'    => array( -3, 19.9 ),
				'scale'     => $scale,
				'translate' => array( 480 - 0.205 * $scale, 250 + 0.212 * $scale ),
			)
		);
		$fit   = array( 0.41018, -65.76, 0.38885, 313.36 );
	} else {
		// The lower 48 states.
		$point = vm_offices_conic(
			$lat,
			$lng,
			array(
				'parallels' => array( 29.5, 45.5 ),
				'rotate'    => 96,
				'center'    => array( -0.6, 38.7 ),
				'scale'     => $scale,
				'translate' => array( 480, 250 ),
			)
		);
		$fit   = array( 1.17046, -109.59, 1.16019, -16.17 );
	}

	// Scale and offset onto the drawing: x scale, x offset, y scale, y offset.
	return array(
		$fit[0] * $point[0] + $fit[1],
		$fit[2] * $point[1] + $fit[3],
	);
}

/**
 * The offices of a block, ready to print: each with its address lines and,
 * when it can be drawn, x and y as a percentage of the map.
 *
 * @param array $saved Offices as the block saves them.
 * @return array
 */
function vm_offices_prepare( $saved ) {
	$offices = array();

	foreach ( (array) $saved as $office ) {
		$city = isset( $office['city'] ) ? trim( (string) $office['city'] ) : '';

		if ( '' === $city ) {
			continue;
		}

		$item = array(
			'city'  => $city,
			'lines' => vm_offices_lines( isset( $office['address'] ) ? $office['address'] : '' ),
			'x'     => null,
			'y'     => null,
		);

		if ( isset( $office['lat'], $office['lng'] ) && is_numeric( $office['lat'] ) && is_numeric( $office['lng'] ) ) {
			$point = vm_offices_project( (float) $office['lat'], (float) $office['lng'] );
			$x     = ( $point[0] + ( isset( $office['moveRight'] ) ? (float) $office['moveRight'] : 0 ) ) / VM_OFFICES_MAP_WIDTH * 100;
			$y     = ( $point[1] + ( isset( $office['moveDown'] ) ? (float) $office['moveDown'] : 0 ) ) / VM_OFFICES_MAP_HEIGHT * 100;

			// A place outside the drawing gets no pin. It is still listed.
			if ( $x >= 0 && $x <= 100 && $y >= 0 && $y <= 100 ) {
				$item['x'] = round( $x, 2 );
				$item['y'] = round( $y, 2 );
			}
		}

		$offices[] = $item;
	}

	return $offices;
}

/**
 * An office card: city and address.
 *
 * @param array  $office Office.
 * @param string $tag    Wrapping element.
 * @param string $attrs  Extra HTML attributes, already escaped.
 * @return string
 */
function vm_offices_card( $office, $tag = 'div', $attrs = '' ) {
	return sprintf(
		'<%1$s class="vm-offices__card"%2$s><h3 class="vm-offices__city">%3$s</h3><p class="vm-offices__address">%4$s</p></%1$s>',
		$tag,
		$attrs,
		esc_html( $office['city'] ),
		implode( '<br>', array_map( 'esc_html', $office['lines'] ) )
	);
}

/**
 * The map with its pins and cards.
 *
 * @param array $saved Offices as the block saves them.
 * @return string HTML, without the block's wrapper. Empty when there are no offices.
 */
function vm_offices_map_html( $saved ) {
	$offices = vm_offices_prepare( $saved );

	if ( ! $offices ) {
		return '';
	}

	// The pin of the design: 24 by 31, orange with a navy dot.
	$pin  = '<svg class="vm-offices__pin-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 31.2" xmlns="http://www.w3.org/2000/svg"><path fill="#f18521" fill-rule="evenodd" clip-rule="evenodd" d="M12 31.2C7.4 26.2-.4 19 0 11.2.7 4.9 5.8 0 12 0s11.3 4.9 12 11.2c.3 8.5-6.8 14.4-12 20Z"/><ellipse fill="#2b3578" cx="11.9" cy="11.8" rx="4.9" ry="5.1"/></svg>';
	$pins = '';
	$list = '';
	$uid  = wp_unique_id( 'vm-office-' );

	foreach ( $offices as $i => $office ) {
		$list .= vm_offices_card( $office, 'li' );

		if ( null === $office['x'] ) {
			continue;
		}

		$id    = $uid . '-' . ( $i + 1 );
		$pins .= sprintf(
			'<div class="vm-offices__pin%7$s" style="left:%1$s%%;top:%2$s%%"><button type="button" class="vm-offices__pin-button" aria-expanded="false" aria-controls="%3$s">%4$s<span class="screen-reader-text">%5$s</span></button>%6$s</div>',
			esc_attr( $office['x'] ),
			esc_attr( $office['y'] ),
			esc_attr( $id ),
			$pin,
			/* translators: %s: office name, such as "Las Vegas, NV". */
			esc_html( sprintf( __( '%s office', 'velocity-kadence' ), $office['city'] ) ),
			vm_offices_card( $office, 'div', ' id="' . esc_attr( $id ) . '"' ),
			// Near a side of the map, the card opens towards the middle on narrower screens.
			$office['x'] < 25 ? ' vm-offices__pin--start' : ( $office['x'] > 75 ? ' vm-offices__pin--end' : '' )
		);
	}

	return sprintf(
		'<div class="vm-offices__map"><img class="vm-offices__drawing" src="%1$s" alt="" width="%2$d" height="%3$d" loading="lazy" decoding="async">%4$s</div><ul class="vm-offices__list">%5$s</ul>',
		esc_url( VELOCITY_KADENCE_URI . '/assets/images/us-map.svg' ),
		VM_OFFICES_MAP_WIDTH,
		VM_OFFICES_MAP_HEIGHT,
		$pins,
		$list
	);
}
