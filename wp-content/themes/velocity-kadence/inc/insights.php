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
 * The address of a topic (/news-insights/topic/...) goes to the list with that
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
 * The section was at /insights/ until 2026-11-25 and is at /news-insights/
 * since. An address under the old one that is no longer found goes to the
 * same place under the new one, so links and bookmarks keep working.
 */
function vm_insights_old_address_redirect() {
	if ( ! is_404() || ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	$path = (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$rest = 0 === strpos( $path, $home ) ? substr( $path, strlen( $home ) ) : ltrim( $path, '/' );

	if ( 'insights' !== $rest && 0 !== strpos( $rest, 'insights/' ) ) {
		return;
	}

	$list = get_post_type_archive_link( 'insight' );

	if ( ! $list ) {
		return;
	}

	wp_safe_redirect( trailingslashit( $list ) . ltrim( substr( $rest, strlen( 'insights' ) ), '/' ), 301 );
	exit;
}
add_action( 'template_redirect', 'vm_insights_old_address_redirect', 1 );

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
	// The design's icons: Font Awesome Free 5.15.4 by @fontawesome,
	// https://fontawesome.com, license https://fontawesome.com/license/free
	// (icons: CC BY 4.0). The X is the brand's own mark. Each: view box, path.
	$icons = array(
		'linkedin' => array( '0 0 448 512', 'M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z' ),
		'x' => array( '0 0 24 24', 'M17.75 3h3.07l-6.7 7.63L22 21h-6.17l-4.83-6.3L5.47 21H2.4l7.17-8.17L2 3h6.33l4.37 5.76L17.75 3Zm-1.08 16.17h1.7L7.4 4.74H5.58l11.09 14.43Z' ),
		'reddit' => array( '0 0 512 512', 'M201.5 305.5c-13.8 0-24.9-11.1-24.9-24.6 0-13.8 11.1-24.9 24.9-24.9 13.6 0 24.6 11.1 24.6 24.9 0 13.6-11.1 24.6-24.6 24.6zM504 256c0 137-111 248-248 248S8 393 8 256 119 8 256 8s248 111 248 248zm-132.3-41.2c-9.4 0-17.7 3.9-23.8 10-22.4-15.5-52.6-25.5-86.1-26.6l17.4-78.3 55.4 12.5c0 13.6 11.1 24.6 24.6 24.6 13.8 0 24.9-11.3 24.9-24.9s-11.1-24.9-24.9-24.9c-9.7 0-18 5.8-22.1 13.8l-61.2-13.6c-3-.8-6.1 1.4-6.9 4.4l-19.1 86.4c-33.2 1.4-63.1 11.3-85.5 26.8-6.1-6.4-14.7-10.2-24.1-10.2-34.9 0-46.3 46.9-14.4 62.8-1.1 5-1.7 10.2-1.7 15.5 0 52.6 59.2 95.2 132 95.2 73.1 0 132.3-42.6 132.3-95.2 0-5.3-.6-10.8-1.9-15.8 31.3-16 19.8-62.5-14.9-62.5zM302.8 331c-18.2 18.2-76.1 17.9-93.6 0-2.2-2.2-6.1-2.2-8.3 0-2.5 2.5-2.5 6.4 0 8.6 22.8 22.8 87.3 22.8 110.2 0 2.5-2.2 2.5-6.1 0-8.6-2.2-2.2-6.1-2.2-8.3 0zm7.7-75c-13.6 0-24.6 11.1-24.6 24.9 0 13.6 11.1 24.6 24.6 24.6 13.8 0 24.9-11.1 24.9-24.6 0-13.8-11-24.9-24.9-24.9z' ),
		'email' => array( '0 0 512 512', 'M464 64H48C21.49 64 0 85.49 0 112v288c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V112c0-26.51-21.49-48-48-48zm0 48v40.805c-22.422 18.259-58.168 46.651-134.587 106.49-16.841 13.247-50.201 45.072-73.413 44.701-23.208.375-56.579-31.459-73.413-44.701C106.18 199.465 70.425 171.067 48 152.805V112h416zM48 400V214.398c22.914 18.251 55.409 43.862 104.938 82.646 21.857 17.205 60.134 55.186 103.062 54.955 42.717.231 80.509-37.199 103.053-54.947 49.528-38.783 82.032-64.401 104.947-82.653V400H48z' ),
		'link' => array( '0 0 512 512', 'M326.612 185.391c59.747 59.809 58.927 155.698.36 214.59-.11.12-.24.25-.36.37l-67.2 67.2c-59.27 59.27-155.699 59.262-214.96 0-59.27-59.26-59.27-155.7 0-214.96l37.106-37.106c9.84-9.84 26.786-3.3 27.294 10.606.648 17.722 3.826 35.527 9.69 52.721 1.986 5.822.567 12.262-3.783 16.612l-13.087 13.087c-28.026 28.026-28.905 73.66-1.155 101.96 28.024 28.579 74.086 28.749 102.325.51l67.2-67.19c28.191-28.191 28.073-73.757 0-101.83-3.701-3.694-7.429-6.564-10.341-8.569a16.037 16.037 0 0 1-6.947-12.606c-.396-10.567 3.348-21.456 11.698-29.806l21.054-21.055c5.521-5.521 14.182-6.199 20.584-1.731a152.482 152.482 0 0 1 20.522 17.197zM467.547 44.449c-59.261-59.262-155.69-59.27-214.96 0l-67.2 67.2c-.12.12-.25.25-.36.37-58.566 58.892-59.387 154.781.36 214.59a152.454 152.454 0 0 0 20.521 17.196c6.402 4.468 15.064 3.789 20.584-1.731l21.054-21.055c8.35-8.35 12.094-19.239 11.698-29.806a16.037 16.037 0 0 0-6.947-12.606c-2.912-2.005-6.64-4.875-10.341-8.569-28.073-28.073-28.191-73.639 0-101.83l67.2-67.19c28.239-28.239 74.3-28.069 102.325.51 27.75 28.3 26.872 73.934-1.155 101.96l-13.087 13.087c-4.35 4.35-5.769 10.79-3.783 16.612 5.864 17.194 9.042 34.999 9.69 52.721.509 13.906 17.454 20.446 27.294 10.606l37.106-37.106c59.271-59.259 59.271-155.699.001-214.959z' ),
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
			'<li><a href="%1$s"%2$s aria-label="%3$s"><svg aria-hidden="true" focusable="false" width="24" height="24" viewBox="%4$s" fill="currentColor"><path d="%5$s"/></svg></a></li>',
			esc_url( $link[1] ),
			'email' === $key ? '' : ' target="_blank" rel="noopener noreferrer"',
			esc_attr( $link[0] ),
			esc_attr( $icons[ $key ][0] ),
			esc_attr( $icons[ $key ][1] )
		);
	}

	// Copies the address. Without the script it is a plain link to the article.
	$html .= sprintf(
		'<li><a href="%1$s" class="vm-share__copy" aria-label="%2$s" data-copied="%3$s"><svg aria-hidden="true" focusable="false" width="18" height="18" viewBox="%4$s" fill="currentColor"><path d="%5$s"/></svg></a></li>',
		esc_url( get_permalink( $post_id ) ),
		esc_attr__( 'Copy the link', 'velocity-kadence' ),
		esc_attr__( 'Link copied', 'velocity-kadence' ),
		esc_attr( $icons['link'][0] ),
		esc_attr( $icons['link'][1] )
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
