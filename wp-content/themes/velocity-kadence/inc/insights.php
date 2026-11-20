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
 * - "insights-related": under an article, the latest articles of its topics.
 *   Kadence leaves the article being read out by itself.
 *
 * A query with no role is left alone.
 *
 * The page of an article is a second element, "News and Insights: article".
 * Three of its parts are printed by this file, since no block reads them:
 * the links to share it ([vm_insight_share]), the box about its author
 * ([vm_insight_author]) and the newsletter bar that comes up at the bottom
 * of the screen halfway through the article.
 *
 * A press release has no author: an article under the topic "Press Release"
 * shows no "Written by" and no author box.
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

	if ( 'insights-related' === $role && is_singular( 'insight' ) ) {
		$topics = wp_get_post_terms( get_queried_object_id(), 'insight_topic', array( 'fields' => 'ids' ) );

		if ( $topics && ! is_wp_error( $topics ) ) {
			$query['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'insight_topic',
					'field'    => 'term_id',
					'terms'    => array_map( 'intval', $topics ),
				),
			);
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

/**
 * Slug of the topic that marks a press release.
 */
const VM_INSIGHT_PRESS_TOPIC = 'press-release';

/**
 * Whether an article is a press release: it is filed under that topic.
 *
 * @param int $post_id Insight ID.
 * @return bool
 */
function vm_insight_is_press( $post_id ) {
	return has_term( VM_INSIGHT_PRESS_TOPIC, 'insight_topic', $post_id );
}

/**
 * [vm_insight_share]: links to share the article being read on LinkedIn, X
 * and Reddit, by email, and to copy its address.
 *
 * @return string
 */
function vm_insight_share_shortcode() {
	$post_id = get_queried_object_id();

	if ( ! $post_id || ! is_singular() ) {
		return '';
	}

	$url   = rawurlencode( get_permalink( $post_id ) );
	$title = rawurlencode( html_entity_decode( get_the_title( $post_id ), ENT_QUOTES, 'UTF-8' ) );
	$icons = array(
		'linkedin' => '<path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5ZM3 9.75h4v11.25H3V9.75Zm6.5 0h3.8v1.6h.06c.53-.95 1.83-1.95 3.76-1.95 4.02 0 4.88 2.5 4.88 6.1V21h-4v-4.9c0-1.25-.02-2.86-1.8-2.86-1.8 0-2.08 1.36-2.08 2.77V21h-4V9.75Z"/>',
		'x'        => '<path d="M17.75 3h3.07l-6.7 7.63L22 21h-6.17l-4.83-6.3L5.47 21H2.4l7.17-8.17L2 3h6.33l4.37 5.76L17.75 3Zm-1.08 16.17h1.7L7.4 4.74H5.58l11.09 14.43Z"/>',
		'reddit'   => '<path d="M22 12.1a2.2 2.2 0 0 0-3.74-1.57 10.9 10.9 0 0 0-5.6-1.72l.95-4.42 3.1.66a1.57 1.57 0 1 0 .17-.98l-3.6-.77a.5.5 0 0 0-.6.38l-1.1 5.13a10.9 10.9 0 0 0-5.8 1.72A2.2 2.2 0 1 0 3.3 14a4.2 4.2 0 0 0-.05.66c0 3.4 3.92 6.14 8.75 6.14s8.75-2.75 8.75-6.14c0-.22-.02-.44-.05-.66A2.2 2.2 0 0 0 22 12.1ZM7 13.6a1.57 1.57 0 1 1 3.14 0 1.57 1.57 0 0 1-3.14 0Zm8.72 4.14c-1.07 1.07-3.12 1.15-3.72 1.15s-2.65-.08-3.72-1.15a.4.4 0 0 1 .57-.57c.68.67 2.12.91 3.15.91s2.47-.24 3.15-.91a.4.4 0 0 1 .57.57Zm-.3-2.57a1.57 1.57 0 1 1 0-3.14 1.57 1.57 0 0 1 0 3.14Z"/>',
		'email'    => '<path d="M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm9 8.1L4.2 7H4v.9l8 6.2 8-6.2V7h-.2L12 13.1Z"/>',
		'link'     => '<path d="M10.6 13.4a1 1 0 0 1 0 1.4 1 1 0 0 1-1.4 0 5 5 0 0 1 0-7.07l3.53-3.54a5 5 0 0 1 7.08 7.08l-1.5 1.49a7 7 0 0 0-.4-2.42l.48-.49a3 3 0 1 0-4.24-4.24L10.6 9.15a3 3 0 0 0 0 4.25Zm2.8-4.2a1 1 0 0 1 1.4 0 5 5 0 0 1 0 7.07l-3.53 3.54a5 5 0 0 1-7.08-7.08l1.5-1.49c-.02.82.12 1.64.4 2.42l-.48.49a3 3 0 1 0 4.24 4.24l3.54-3.54a3 3 0 0 0 0-4.24 1 1 0 0 1 0-1.4Z"/>',
	);
	$links = array(
		'linkedin' => array( __( 'Share on LinkedIn (opens in a new tab)', 'velocity-kadence' ), 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url ),
		'x'        => array( __( 'Share on X (opens in a new tab)', 'velocity-kadence' ), 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title ),
		'reddit'   => array( __( 'Share on Reddit (opens in a new tab)', 'velocity-kadence' ), 'https://www.reddit.com/submit?url=' . $url . '&title=' . $title ),
		'email'    => array( __( 'Share by email', 'velocity-kadence' ), 'mailto:?subject=' . $title . '&body=' . $url ),
	);

	$html = '';
	foreach ( $links as $key => $link ) {
		$html .= sprintf(
			'<li><a href="%1$s"%2$s aria-label="%3$s"><svg aria-hidden="true" focusable="false" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">%4$s</svg></a></li>',
			esc_url( $link[1] ),
			'email' === $key ? '' : ' target="_blank" rel="noopener noreferrer"',
			esc_attr( $link[0] ),
			$icons[ $key ]
		);
	}

	// Copies the address. Without the script it is a plain link to the article.
	$html .= sprintf(
		'<li><a href="%1$s" class="vm-share__copy" aria-label="%2$s" data-copied="%3$s"><svg aria-hidden="true" focusable="false" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">%4$s</svg></a></li>',
		esc_url( get_permalink( $post_id ) ),
		esc_attr__( 'Copy the link', 'velocity-kadence' ),
		esc_attr__( 'Link copied', 'velocity-kadence' ),
		$icons['link']
	);

	return '<ul class="vm-share" aria-label="' . esc_attr__( 'Share this article', 'velocity-kadence' ) . '">' . $html . '</ul>';
}
add_shortcode( 'vm_insight_share', 'vm_insight_share_shortcode' );

/**
 * Two fields for the author of an article, in the user's profile (Users >
 * Profile, "News and Insights author"): the position and the photo.
 * WordPress has a name and a biography for a user, and neither of these.
 */
function vm_insight_author_fields() {
	if ( ! function_exists( 'new_cmb2_box' ) ) {
		return;
	}

	$box = new_cmb2_box(
		array(
			'id'               => 'vm_insight_author',
			'title'            => __( 'News and Insights author', 'velocity-kadence' ),
			'object_types'     => array( 'user' ),
			'show_names'       => true,
			'new_user_section' => 'add-new-user',
		)
	);

	$box->add_field(
		array(
			'name' => __( 'News and Insights author', 'velocity-kadence' ),
			'desc' => __( 'Shown under the articles this person writes. The name is "Display name publicly as" and the biography is "Biographical Info", both above.', 'velocity-kadence' ),
			'id'   => 'vm_author_heading',
			'type' => 'title',
		)
	);

	$box->add_field(
		array(
			'name' => __( 'Position', 'velocity-kadence' ),
			'desc' => __( 'Shown after the name: "Name, Position". Leave empty to show the name alone.', 'velocity-kadence' ),
			'id'   => 'vm_author_position',
			'type' => 'text',
		)
	);

	$box->add_field(
		array(
			'name'         => __( 'Photo', 'velocity-kadence' ),
			'desc'         => __( 'A square photo works best: it is shown round. Leave empty to show no photo.', 'velocity-kadence' ),
			'id'           => 'vm_author_photo',
			'type'         => 'file',
			'options'      => array( 'url' => false ),
			'query_args'   => array( 'type' => 'image' ),
			'preview_size' => 'thumbnail',
		)
	);
}
add_action( 'cmb2_admin_init', 'vm_insight_author_fields' );

/**
 * [vm_insight_author]: the box about the author of the article being read.
 *
 * The name is the author's "Display name publicly as" and the biography
 * their "Biographical Info", from Users. The position and the photo are the
 * two fields above. What is empty is left out: no photo, no picture; no
 * biography, no "Read Full Bio". A press release has no box.
 *
 * @return string
 */
function vm_insight_author_shortcode() {
	$post = get_queried_object();

	if ( ! $post instanceof WP_Post || ! is_singular() || vm_insight_is_press( $post->ID ) ) {
		return '';
	}

	$author = get_userdata( (int) $post->post_author );

	if ( ! $author ) {
		return '';
	}

	$bio      = trim( (string) $author->description );
	$position = trim( (string) get_user_meta( $author->ID, 'vm_author_position', true ) );
	$photo_id = (int) get_user_meta( $author->ID, 'vm_author_photo_id', true );
	$photo    = $photo_id ? wp_get_attachment_image( $photo_id, 'thumbnail', false, array( 'class' => 'vm-author__photo', 'alt' => '' ) ) : '';
	$name     = '<span class="vm-author__name">' . esc_html( $author->display_name . ( '' !== $position ? ', ' . $position : '' ) ) . '</span>';

	if ( '' === $bio ) {
		return '<div class="vm-author"><div class="vm-author__head">' . $photo . '<span class="vm-author__text">' . $name . '</span></div></div>';
	}

	return '<details class="vm-author"><summary class="vm-author__head">' . $photo .
		'<span class="vm-author__text">' . $name . '<span class="vm-author__more">' . esc_html__( 'Read Full Bio', 'velocity-kadence' ) . '</span></span></summary>' .
		'<div class="vm-author__bio">' . wp_kses_post( wpautop( $bio ) ) . '</div></details>';
}
add_shortcode( 'vm_insight_author', 'vm_insight_author_shortcode' );

/**
 * The newsletter bar of an article.
 *
 * It is printed at the end of the page's content, right above the footer,
 * and stays hidden until half of the article has been read. Once shown it
 * holds on to the bottom of the screen by itself ("sticky" in style.css), and
 * at the end of the page it rests where it is printed, above the footer. It
 * shows once per visit: closing it, or having seen it, keeps it away until
 * the browser is closed. See assets/js/insight.js.
 */
function vm_insight_news_bar() {
	if ( ! is_singular( 'insight' ) ) {
		return;
	}

	$form = do_shortcode( '[vm_form name="newsletter"]' );

	if ( '' !== trim( $form ) ) {
		printf(
			'<aside class="vm-news-bar" id="vm-news-bar" aria-label="%1$s" hidden><div class="vm-news-bar__inner"><p class="vm-news-bar__title">%2$s</p><div class="vm-news-bar__form">%3$s</div><button type="button" class="vm-news-bar__close" aria-label="%4$s"><svg aria-hidden="true" focusable="false" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 5l14 14M19 5L5 19"/></svg></button></div></aside>',
			esc_attr__( 'Newsletter', 'velocity-kadence' ),
			esc_html__( 'Subscribe to Velocity’s Newsletter', 'velocity-kadence' ),
			$form, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Gravity Forms markup.
			esc_attr__( 'Close', 'velocity-kadence' )
		);
	}
}
add_action( 'kadence_before_footer', 'vm_insight_news_bar' );

/**
 * The script of the page of an article: it opens the newsletter bar, and
 * copies the article's address for the "copy the link" icon.
 */
function vm_insight_script() {
	if ( ! is_singular( 'insight' ) ) {
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
add_action( 'wp_enqueue_scripts', 'vm_insight_script' );
