<?php
/**
 * Background videos on Kadence rows, without hurting Core Web Vitals.
 *
 * The video is set in the row's Background settings, like any background.
 * This file only changes how it loads:
 *
 * - The row's background image is the video poster. It is preloaded with high
 *   priority when the row is the first block, so the hero paints fast and the
 *   poster is what Largest Contentful Paint measures.
 * - The video file is not requested until the page has finished loading
 *   (assets/js/bg-video.js), so it never competes with the page itself.
 * - Visitors who ask for reduced motion or data saving keep the poster.
 * - Uploads of videos over 2 MB are refused with a message on how to compress.
 *
 * See docs/components.md, "Hero video".
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Largest video, in bytes, that can be uploaded to the media library.
 *
 * @return int
 */
function vm_max_video_bytes() {
	return (int) apply_filters( 'vm_max_video_bytes', 2 * MB_IN_BYTES );
}

/**
 * Hold back the video source of row backgrounds until the page has loaded.
 *
 * @param array $args       HTML attributes of the video element.
 * @param array $attributes Row block attributes.
 * @return array
 */
function vm_defer_row_video( $args, $attributes ) {
	$type = isset( $attributes['backgroundVideoType'] ) && $attributes['backgroundVideoType'] ? $attributes['backgroundVideoType'] : 'local';
	if ( 'local' !== $type || empty( $args['src'] ) ) {
		return $args;
	}

	$args['data-vm-src'] = $args['src'];
	$args['preload']     = 'none';
	unset( $args['src'], $args['autoplay'] );

	$file = VELOCITY_KADENCE_DIR . '/assets/js/bg-video.js';
	wp_enqueue_script(
		'velocity-kadence-bg-video',
		VELOCITY_KADENCE_URI . '/assets/js/bg-video.js',
		array(),
		file_exists( $file ) ? (string) filemtime( $file ) : null,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	return $args;
}
add_filter( 'kadence_blocks_row_video_attrs', 'vm_defer_row_video', 10, 2 );

/**
 * Preload the background image of the first row on the page.
 *
 * A background image, or a video poster, is found late by the browser. When
 * the page opens with a row that has one, it is almost always the largest
 * thing on screen, so it is fetched first.
 */
function vm_preload_first_row_background() {
	if ( ! is_singular() ) {
		return;
	}

	$post = get_queried_object();
	if ( ! $post instanceof WP_Post || ! has_blocks( $post ) ) {
		return;
	}

	foreach ( parse_blocks( $post->post_content ) as $block ) {
		if ( empty( $block['blockName'] ) ) {
			continue;
		}

		// Only the first real block counts: anything lower is below the fold.
		if ( 'kadence/rowlayout' === $block['blockName'] && ! empty( $block['attrs']['bgImg'] ) ) {
			printf(
				'<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n",
				esc_url( $block['attrs']['bgImg'] )
			);
		}
		return;
	}
}
add_action( 'wp_head', 'vm_preload_first_row_background', 1 );

/**
 * Refuse video uploads that are too heavy for a page background.
 *
 * @param array $file Upload data from WordPress.
 * @return array
 */
function vm_limit_video_upload( $file ) {
	$type = isset( $file['type'] ) ? (string) $file['type'] : '';
	if ( 0 !== strpos( $type, 'video/' ) || empty( $file['size'] ) || $file['size'] <= vm_max_video_bytes() ) {
		return $file;
	}

	$file['error'] = sprintf(
		/* translators: 1: size of the uploaded video, 2: size limit. */
		__( 'This video is %1$s. Videos must be %2$s or less so pages stay fast. Compress it with HandBrake (see "Hero video" in the theme docs), or host long videos on YouTube or Vimeo.', 'velocity-kadence' ),
		size_format( $file['size'], 1 ),
		size_format( vm_max_video_bytes() )
	);

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'vm_limit_video_upload' );
add_filter( 'wp_handle_sideload_prefilter', 'vm_limit_video_upload' );
