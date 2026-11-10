<?php
/**
 * Plugin Name: Velocity Insights
 * Description: Adds the Insights article section (/insights/), topic filtering, per-article SEO fields, a bottom-of-article CTA, and a draft → review → publish workflow for the agency (CSTMR).
 * Version:     1.5.0
 * Author:      Velocity Mortgage
 * Requires at least: 6.2
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Settings — edit these before uploading
 * ---------------------------------------------------------------------- */

// Final directory name. Change to 'blog' if that is what's agreed.
// After changing it on a live site: Settings → Permalinks → Save.
define( 'VMI_SLUG', 'insights' );

// Default articles per page on the index (can be changed in wp-admin → Insights → Settings).
define( 'VMI_PER_PAGE', 9 );

function vmi_per_page() {
	$n = absint( get_option( 'vmi_per_page', VMI_PER_PAGE ) );
	return $n > 0 ? $n : VMI_PER_PAGE;
}

// Show topic filter links above the index list (nice-to-have in the ticket).
define( 'VMI_TOPIC_FILTER', true );

// Use this plugin's own index/article templates instead of the theme's
// generic archive.php/single.php. Only applies to classic (non-block) themes.
// Turn on if the theme's default layout doesn't show image/excerpt/author/date.
define( 'VMI_USE_PLUGIN_TEMPLATES', false );

// Who can publish Insights and who gets review emails is chosen in
// wp-admin → Insights → Publishers (no code change needed).

// Title of the synced pattern Marketing edits to change the CTA (Appearance → Editor → Patterns,
// or Posts → Patterns / wp-admin/edit.php?post_type=wp_block).
define( 'VMI_CTA_PATTERN_TITLE', 'Insights CTA' );

// Where the default CTA button goes (same as the header "Get a Loan Quote" button).
define( 'VMI_CTA_URL', 'https://www.generatevelocity.com/#/SignIn' );


/* -------------------------------------------------------------------------
 * Post type + taxonomy
 * ---------------------------------------------------------------------- */

add_action( 'init', 'vmi_register', 5 );
function vmi_register() {
	// Registered before the post type so /insights/topic/... resolves before /insights/{article}/.
	register_taxonomy(
		'insight_topic',
		array( 'insight' ),
		array(
			'labels'            => array(
				'name'          => 'Topics',
				'singular_name' => 'Topic',
				'menu_name'     => 'Topics',
				'all_items'     => 'All Topics',
				'add_new_item'  => 'Add New Topic',
				'edit_item'     => 'Edit Topic',
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => VMI_SLUG . '/topic', 'with_front' => false ),
		)
	);

	register_post_type(
		'insight',
		array(
			'labels'          => array(
				'name'               => 'Insights',
				'singular_name'      => 'Insight',
				'menu_name'          => 'Insights',
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New Article',
				'edit_item'          => 'Edit Article',
				'new_item'           => 'New Article',
				'view_item'          => 'View Article',
				'all_items'          => 'All Articles',
				'search_items'       => 'Search Articles',
				'not_found'          => 'No articles found.',
				'not_found_in_trash' => 'No articles found in Trash.',
			),
			'public'          => true,
			'has_archive'     => VMI_SLUG,
			// with_front=false keeps the URL at /insights/ even if the permalink base is e.g. /press/.
			'rewrite'         => array( 'slug' => VMI_SLUG, 'with_front' => false ),
			'show_in_rest'    => true, // block editor
			'menu_position'   => 5,
			'menu_icon'       => 'dashicons-lightbulb',
			'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions' ),
			'taxonomies'      => array( 'insight_topic' ),
			// Own capabilities (edit_insights, publish_insights, ...). Granted dynamically in
			// vmi_user_caps(): anyone who can edit posts can draft; only chosen Publishers can publish.
			'capability_type' => array( 'insight', 'insights' ),
			'map_meta_cap'    => true,
		)
	);
}

/* -------------------------------------------------------------------------
 * Activation: rewrite rules + agency role
 * ---------------------------------------------------------------------- */

register_activation_hook( __FILE__, 'vmi_activate' );
function vmi_activate() {
	vmi_register();
	flush_rewrite_rules();

	// Contributor + media uploads (plain Contributors can't upload featured images).
	// Can draft and submit for review, cannot publish.
	if ( ! get_role( 'insights_contributor' ) ) {
		add_role(
			'insights_contributor',
			'Insights Contributor (Agency)',
			array(
				'read'         => true,
				'edit_posts'   => true,
				'delete_posts' => true,
				'upload_files' => true,
			)
		);
	}
	vmi_sync_role_caps();
}

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/*
 * Store the basic (non-publishing) Insights capabilities on the roles themselves.
 * WordPress builds author dropdowns (Quick Edit, block editor) from role
 * capabilities in the database, so they must exist there - not only in the
 * dynamic vmi_user_caps() filter. Publishing stays limited to chosen Publishers.
 */
define( 'VMI_CAPS_VERSION', 2 );

function vmi_sync_role_caps() {
	foreach ( wp_roles()->role_objects as $role ) {
		if ( $role->has_cap( 'edit_posts' ) ) {
			$role->add_cap( 'edit_insights' );
			$role->add_cap( 'delete_insights' );
		}
		if ( $role->has_cap( 'edit_others_posts' ) ) {
			$role->add_cap( 'edit_others_insights' );
			$role->add_cap( 'read_private_insights' );
		}
	}
	update_option( 'vmi_caps_version', VMI_CAPS_VERSION );
}

add_action( 'admin_init', 'vmi_maybe_sync_role_caps' );
function vmi_maybe_sync_role_caps() {
	if ( (int) get_option( 'vmi_caps_version' ) !== VMI_CAPS_VERSION ) {
		vmi_sync_role_caps();
	}
}

/* -------------------------------------------------------------------------
 * Index query: newest first, paginated
 * ---------------------------------------------------------------------- */

add_action( 'pre_get_posts', 'vmi_index_query' );
function vmi_index_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'insight' ) || $query->is_tax( 'insight_topic' ) ) {
		$query->set( 'posts_per_page', vmi_per_page() );
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}
}

/* -------------------------------------------------------------------------
 * Topic filter links above the index list
 * ---------------------------------------------------------------------- */

function vmi_topic_filter_html() {
	// Topic counts only include public articles, so Publishers/Admins also see topics
	// that so far only have Private or draft articles (handy for testing before launch).
	$terms = get_terms(
		array(
			'taxonomy'   => 'insight_topic',
			'hide_empty' => ! current_user_can( 'read_private_insights' ),
		)
	);
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}
	$current = is_tax( 'insight_topic' ) ? get_queried_object_id() : 0;

	$out  = '<nav class="vmi-topics" aria-label="Filter articles by topic">';
	$out .= sprintf(
		'<a href="%s"%s>All</a>',
		esc_url( get_post_type_archive_link( 'insight' ) ),
		$current ? '' : ' aria-current="page"'
	);
	foreach ( $terms as $term ) {
		$out .= sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( get_term_link( $term ) ),
			$current === $term->term_id ? ' aria-current="page"' : '',
			esc_html( $term->name )
		);
	}
	return $out . '</nav>';
}

add_action( 'loop_start', 'vmi_topic_filter_on_loop' );
function vmi_topic_filter_on_loop( $query ) {
	static $done = false;
	if ( $done || ! VMI_TOPIC_FILTER || vmi_plugin_templates_active() || ! $query->is_main_query() ) {
		return;
	}
	if ( is_post_type_archive( 'insight' ) || is_tax( 'insight_topic' ) ) {
		$done = true;
		echo vmi_topic_filter_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in builder.
	}
}

/* -------------------------------------------------------------------------
 * CTA block at the bottom of each article
 * ---------------------------------------------------------------------- */

function vmi_cta_html() {
	// 1) Marketing-editable synced pattern named "Insights CTA", if it exists.
	$pattern = get_posts(
		array(
			'post_type'      => 'wp_block',
			'title'          => VMI_CTA_PATTERN_TITLE,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
		)
	);
	if ( $pattern ) {
		return '<div class="vmi-cta">' . do_shortcode( do_blocks( $pattern[0]->post_content ) ) . '</div>';
	}

	// 2) Default CTA (approved by David): used unless an "Insights CTA" pattern exists.
	$html = '<div class="vmi-cta">'
		. '<h2>Ready to finance your next investment property?</h2>'
		. '<p>Talk to a Velocity loan specialist about your scenario.</p>'
		. '<p><a class="vmi-cta__button" href="' . esc_url( VMI_CTA_URL ) . '">Get a Loan Quote</a></p>'
		. '</div>';

	return apply_filters( 'vmi_cta_html', $html );
}

add_filter( 'the_content', 'vmi_append_cta', 20 );
function vmi_append_cta( $content ) {
	if ( is_singular( 'insight' ) && in_the_loop() && is_main_query() && ! vmi_plugin_templates_active() ) {
		$content .= vmi_cta_html();
	}
	return $content;
}

/* -------------------------------------------------------------------------
 * Byline (author) box
 *
 * The block editor's own Author dropdown loads its list through
 * /wp-json/wp/v2/users, which this site's security rules block, so it shows
 * nothing. This box builds the list on the server instead and saves through
 * WordPress's standard "post_author_override" field.
 * ---------------------------------------------------------------------- */

add_action( 'add_meta_boxes_insight', 'vmi_add_byline_box' );
function vmi_add_byline_box( $post ) {
	if ( current_user_can( 'edit_others_insights' ) ) {
		add_meta_box( 'vmi_byline', 'Byline (Author)', 'vmi_render_byline_box', 'insight', 'side', 'high' );
	}
}

function vmi_render_byline_box( $post ) {
	$current = $post->post_author ? (int) $post->post_author : get_current_user_id();
	echo '<p style="margin-top:0"><label for="post_author_override">Shown as the author on the article and listing card.</label></p>';
	wp_dropdown_users(
		array(
			'capability'       => array( 'edit_insights' ),
			'name'             => 'post_author_override',
			'id'               => 'post_author_override',
			'selected'         => $current,
			'include_selected' => true,
			'show'             => 'display_name_with_login',
			'class'            => 'widefat',
		)
	);
	echo '<p class="description">Change a user\'s public name under Users → Profile → "Display name publicly as".</p>';
}

/* -------------------------------------------------------------------------
 * SEO title + meta description
 * Skipped automatically if Yoast, Rank Math, AIOSEO or SEOPress is active
 * (use that plugin's fields instead).
 * ---------------------------------------------------------------------- */

function vmi_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

add_action( 'add_meta_boxes_insight', 'vmi_add_seo_box' );
function vmi_add_seo_box() {
	if ( vmi_seo_plugin_active() ) {
		return;
	}
	add_meta_box( 'vmi_seo', 'SEO', 'vmi_render_seo_box', 'insight', 'normal', 'high' );
}

function vmi_render_seo_box( $post ) {
	wp_nonce_field( 'vmi_seo_save', 'vmi_seo_nonce' );
	$title = get_post_meta( $post->ID, '_vmi_seo_title', true );
	$desc  = get_post_meta( $post->ID, '_vmi_seo_description', true );
	?>
	<p>
		<label for="vmi_seo_title"><strong>SEO title</strong> (about 50–60 characters; leave blank to use the article title)</label><br>
		<input type="text" id="vmi_seo_title" name="vmi_seo_title" value="<?php echo esc_attr( $title ); ?>" style="width:100%" maxlength="120">
	</p>
	<p>
		<label for="vmi_seo_description"><strong>Meta description</strong> (about 150–160 characters; leave blank to use the excerpt)</label><br>
		<textarea id="vmi_seo_description" name="vmi_seo_description" rows="3" style="width:100%" maxlength="320"><?php echo esc_textarea( $desc ); ?></textarea>
	</p>
	<?php
}

add_action( 'save_post_insight', 'vmi_save_seo_box' );
function vmi_save_seo_box( $post_id ) {
	if ( ! isset( $_POST['vmi_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vmi_seo_nonce'] ) ), 'vmi_seo_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['vmi_seo_title'] ) ) {
		update_post_meta( $post_id, '_vmi_seo_title', sanitize_text_field( wp_unslash( $_POST['vmi_seo_title'] ) ) );
	}
	if ( isset( $_POST['vmi_seo_description'] ) ) {
		update_post_meta( $post_id, '_vmi_seo_description', sanitize_textarea_field( wp_unslash( $_POST['vmi_seo_description'] ) ) );
	}
}

add_filter( 'pre_get_document_title', 'vmi_document_title', 20 );
function vmi_document_title( $title ) {
	if ( vmi_seo_plugin_active() || ! is_singular( 'insight' ) ) {
		return $title;
	}
	$custom = get_post_meta( get_queried_object_id(), '_vmi_seo_title', true );
	return $custom ? $custom : $title;
}

add_action( 'wp_head', 'vmi_meta_description', 1 );
function vmi_meta_description() {
	if ( vmi_seo_plugin_active() || ! is_singular( 'insight' ) ) {
		return;
	}
	$post_id = get_queried_object_id();
	$desc    = get_post_meta( $post_id, '_vmi_seo_description', true );
	if ( ! $desc ) {
		$desc = wp_strip_all_tags( get_the_excerpt( $post_id ) );
	}
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( wp_html_excerpt( $desc, 320 ) ) . '">' . "\n";
	}
}

/* -------------------------------------------------------------------------
 * Publishing permissions
 *
 * - Anyone who can edit posts (Contributors, Insights Contributor, Authors,
 *   Editors, Admins) can create drafts and submit them for review.
 * - Editors/Admins can also edit other people's DRAFTS.
 * - Only the users ticked under Insights → Publishers can publish, schedule,
 *   or edit/delete published Insights. If nobody is ticked yet, Administrators
 *   act as Publishers so the site never gets locked out.
 * ---------------------------------------------------------------------- */

function vmi_publisher_ids() {
	$ids = get_option( 'vmi_publishers', array() );
	return is_array( $ids ) ? array_map( 'absint', $ids ) : array();
}

function vmi_is_publisher( $user_id, $allcaps ) {
	$ids = vmi_publisher_ids();
	if ( empty( $ids ) ) {
		return ! empty( $allcaps['manage_options'] );
	}
	return in_array( (int) $user_id, $ids, true );
}

add_filter( 'user_has_cap', 'vmi_user_caps', 10, 4 );
function vmi_user_caps( $allcaps, $caps, $args, $user ) {
	if ( ! empty( $allcaps['edit_posts'] ) ) {
		$allcaps['edit_insights']   = true;
		$allcaps['delete_insights'] = true;
	}
	if ( ! empty( $allcaps['edit_others_posts'] ) ) {
		$allcaps['edit_others_insights']  = true;
		$allcaps['read_private_insights'] = true;
	}
	if ( $user && vmi_is_publisher( $user->ID, $allcaps ) ) {
		foreach ( array(
			'edit_insights',
			'delete_insights',
			'edit_others_insights',
			'read_private_insights',
			'publish_insights',
			'edit_published_insights',
			'delete_published_insights',
			'edit_private_insights',
			'delete_private_insights',
			'delete_others_insights',
		) as $cap ) {
			$allcaps[ $cap ] = true;
		}
	}
	return $allcaps;
}

function vmi_publisher_emails() {
	$ids = vmi_publisher_ids();
	$args = $ids ? array( 'include' => $ids ) : array( 'role' => 'administrator' );
	$args['fields'] = array( 'user_email' );
	return wp_list_pluck( get_users( $args ), 'user_email' );
}

/* Settings screen: Insights → Publishers (Administrators only). */

add_action( 'admin_init', 'vmi_register_settings' );
function vmi_register_settings() {
	register_setting(
		'vmi_settings',
		'vmi_publishers',
		array(
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => function ( $value ) {
				return array_values( array_filter( array_map( 'absint', (array) $value ) ) );
			},
		)
	);
	register_setting(
		'vmi_settings',
		'vmi_per_page',
		array(
			'type'              => 'integer',
			'default'           => VMI_PER_PAGE,
			'sanitize_callback' => function ( $value ) {
				$n = absint( $value );
				return ( $n >= 1 && $n <= 50 ) ? $n : VMI_PER_PAGE;
			},
		)
	);
}

add_action( 'admin_menu', 'vmi_settings_menu' );
function vmi_settings_menu() {
	add_submenu_page( 'edit.php?post_type=insight', 'Insights Settings', 'Settings', 'manage_options', 'vmi-publishers', 'vmi_render_settings' );
}

function vmi_render_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$selected   = vmi_publisher_ids();
	$candidates = get_users(
		array(
			'role__in' => array( 'administrator', 'editor' ),
			'orderby'  => 'display_name',
		)
	);
	?>
	<div class="wrap">
		<h1>Insights Settings</h1>
		<h2>Publishers</h2>
		<p>Only the people ticked here can <strong>publish</strong>, schedule, or change <strong>published</strong> Insights articles, and they are the ones emailed when an article is submitted for review.<br>
		Everyone else (other Editors/Admins and CSTMR) can still write drafts and click <em>Submit for Review</em>.</p>
		<?php if ( empty( $selected ) ) : ?>
			<div class="notice notice-warning inline"><p>Nobody is selected yet, so all <strong>Administrators</strong> can publish and receive review emails. Tick the Marketing publishers below.</p></div>
		<?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'vmi_settings' ); ?>
			<table class="widefat striped" style="max-width:760px">
				<thead><tr><th style="width:40px"></th><th>Name</th><th>Email</th><th>Role</th></tr></thead>
				<tbody>
				<?php foreach ( $candidates as $u ) : ?>
					<tr>
						<td><input type="checkbox" id="vmi-pub-<?php echo esc_attr( $u->ID ); ?>" name="vmi_publishers[]" value="<?php echo esc_attr( $u->ID ); ?>" <?php checked( in_array( $u->ID, $selected, true ) ); ?>></td>
						<td><label for="vmi-pub-<?php echo esc_attr( $u->ID ); ?>"><?php echo esc_html( $u->display_name ); ?></label></td>
						<td><?php echo esc_html( $u->user_email ); ?></td>
						<td><?php echo esc_html( implode( ', ', array_map( 'ucfirst', $u->roles ) ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<h2 style="margin-top:32px">Listing page</h2>
			<p>
				<label for="vmi_per_page"><strong>Articles per page on /<?php echo esc_html( VMI_SLUG ); ?>/</strong></label><br>
				<input type="number" id="vmi_per_page" name="vmi_per_page" min="1" max="50" value="<?php echo esc_attr( vmi_per_page() ); ?>" class="small-text">
				<span class="description">Default 9. Set it to 2 temporarily to test pagination with only a few articles.</span>
			</p>
			<?php submit_button( 'Save Settings' ); ?>
		</form>
	</div>
	<?php
}

add_action( 'admin_notices', 'vmi_publishers_notice' );
function vmi_publishers_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'insight' !== $screen->post_type || ! current_user_can( 'manage_options' ) || vmi_publisher_ids() ) {
		return;
	}
	printf(
		'<div class="notice notice-info"><p>Choose who can publish Insights: <a href="%s">Insights → Settings</a>. Until then, all Administrators can publish.</p></div>',
		esc_url( admin_url( 'edit.php?post_type=insight&page=vmi-publishers' ) )
	);
}

/* -------------------------------------------------------------------------
 * Email reviewers when an article is submitted for review
 * ---------------------------------------------------------------------- */

// Runs after the article, its featured image, excerpt and topics are all saved.
add_action( 'wp_after_insert_post', 'vmi_notify_pending', 10, 4 );
function vmi_notify_pending( $post_id, $post, $update, $post_before ) {
	if ( 'insight' !== $post->post_type || 'pending' !== $post->post_status ) {
		return;
	}
	if ( $post_before && 'pending' === $post_before->post_status ) {
		return; // Already pending (e.g. saved again) - don't email twice.
	}

	$to = array_filter( vmi_publisher_emails(), 'is_email' );
	if ( ! $to ) {
		return;
	}

	$title   = wp_strip_all_tags( get_the_title( $post ) );
	$subject = sprintf( '[Insights] Ready for review: %s', $title );
	$html    = vmi_review_email_html( $post );
	$text    = vmi_review_email_text( $post );

	// Plain-text version for email apps that don't show HTML.
	$alt = function ( $phpmailer ) use ( $text ) {
		$phpmailer->AltBody = $text;
	};
	add_action( 'phpmailer_init', $alt );
	wp_mail( $to, $subject, $html, array( 'Content-Type: text/html; charset=UTF-8' ) );
	remove_action( 'phpmailer_init', $alt );
}

/** Data shared by the HTML and plain-text emails. */
function vmi_review_email_data( $post ) {
	$topics = get_the_terms( $post, 'insight_topic' );
	return array(
		'site'      => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		'title'     => wp_strip_all_tags( get_the_title( $post ) ),
		'author'    => get_the_author_meta( 'display_name', $post->post_author ),
		'submitted' => wp_date( get_option( 'date_format' ) . ' \a\t ' . get_option( 'time_format' ) ),
		'topics'    => ( $topics && ! is_wp_error( $topics ) ) ? implode( ', ', wp_list_pluck( $topics, 'name' ) ) : '—',
		'excerpt'   => wp_trim_words( wp_strip_all_tags( has_excerpt( $post ) ? $post->post_excerpt : strip_shortcodes( $post->post_content ) ), 40 ),
		'image'     => get_the_post_thumbnail_url( $post, 'medium_large' ),
		'review'    => admin_url( 'post.php?post=' . $post->ID . '&action=edit' ),
		'preview'   => get_preview_post_link( $post ),
		'queue'     => admin_url( 'edit.php?post_type=insight&post_status=pending' ),
	);
}

function vmi_review_email_html( $post ) {
	$d      = vmi_review_email_data( $post );
	$navy   = '#040A3A';
	$accent = '#F28C28';
	$font   = "font-family:Arial,Helvetica,sans-serif;";

	$row = function ( $label, $value ) use ( $font ) {
		return '<tr><td style="' . $font . 'padding:6px 0;color:#6B7280;font-size:13px;width:110px;vertical-align:top;">' . esc_html( $label ) . '</td>'
			. '<td style="' . $font . 'padding:6px 0;color:#111827;font-size:14px;">' . esc_html( $value ) . '</td></tr>';
	};

	ob_start();
	?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?php echo esc_html( $d['title'] ); ?></title></head>
<body style="margin:0;padding:0;background:#F3F4F6;">
<div style="display:none;max-height:0;overflow:hidden;">New Insights article from <?php echo esc_html( $d['author'] ); ?> is waiting for your review.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F3F4F6;">
<tr><td align="center" style="padding:24px 12px;">
	<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#FFFFFF;border-radius:8px;overflow:hidden;border:1px solid #E5E7EB;">
		<tr><td style="background:<?php echo $navy; ?>;padding:22px 32px;<?php echo $font; ?>">
			<div style="color:#FFFFFF;font-size:18px;font-weight:bold;"><?php echo esc_html( $d['site'] ); ?></div>
			<div style="color:#AEB7D6;font-size:12px;letter-spacing:1px;text-transform:uppercase;margin-top:4px;">Insights &middot; Review request</div>
		</td></tr>
		<tr><td style="height:4px;background:<?php echo $accent; ?>;line-height:4px;font-size:0;">&nbsp;</td></tr>
		<tr><td style="padding:28px 32px 8px;<?php echo $font; ?>">
			<p style="margin:0 0 6px;color:#6B7280;font-size:13px;">A new article is ready for your review</p>
			<h1 style="margin:0 0 16px;color:<?php echo $navy; ?>;font-size:22px;line-height:1.3;"><?php echo esc_html( $d['title'] ); ?></h1>
			<?php if ( $d['image'] ) : ?>
				<img src="<?php echo esc_url( $d['image'] ); ?>" alt="" width="536" style="display:block;width:100%;max-width:536px;height:auto;border-radius:6px;margin:0 0 16px;">
			<?php endif; ?>
			<?php if ( $d['excerpt'] ) : ?>
				<p style="margin:0 0 20px;color:#374151;font-size:15px;line-height:1.55;"><?php echo esc_html( $d['excerpt'] ); ?></p>
			<?php endif; ?>
			<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-top:1px solid #E5E7EB;border-bottom:1px solid #E5E7EB;margin:0 0 24px;">
				<?php
				echo $row( 'Submitted by', $d['author'] ); // phpcs:ignore
				echo $row( 'Submitted', $d['submitted'] ); // phpcs:ignore
				echo $row( 'Topic', $d['topics'] ); // phpcs:ignore
				?>
			</table>
			<table role="presentation" cellpadding="0" cellspacing="0"><tr>
				<td style="border-radius:4px;background:<?php echo $accent; ?>;">
					<a href="<?php echo esc_url( $d['review'] ); ?>" style="<?php echo $font; ?>display:inline-block;padding:12px 22px;color:#FFFFFF;font-size:15px;font-weight:bold;text-decoration:none;border-radius:4px;">Review &amp; publish</a>
				</td>
				<td style="width:12px;">&nbsp;</td>
				<td style="border-radius:4px;border:1px solid <?php echo $navy; ?>;">
					<a href="<?php echo esc_url( $d['preview'] ); ?>" style="<?php echo $font; ?>display:inline-block;padding:11px 20px;color:<?php echo $navy; ?>;font-size:15px;font-weight:bold;text-decoration:none;border-radius:4px;">Preview article</a>
				</td>
			</tr></table>
			<p style="margin:20px 0 0;color:#6B7280;font-size:12px;line-height:1.5;">
				Buttons not working? Copy this link into your browser:<br>
				<a href="<?php echo esc_url( $d['review'] ); ?>" style="color:<?php echo $navy; ?>;word-break:break-all;"><?php echo esc_html( $d['review'] ); ?></a>
			</p>
		</td></tr>
		<tr><td style="padding:20px 32px 28px;<?php echo $font; ?>">
			<p style="margin:0;color:#9CA3AF;font-size:12px;line-height:1.5;border-top:1px solid #F3F4F6;padding-top:16px;">
				You're receiving this because you're an Insights Publisher on <?php echo esc_html( $d['site'] ); ?>.
				See everything waiting for review: <a href="<?php echo esc_url( $d['queue'] ); ?>" style="color:#6B7280;">Pending articles</a>.
			</p>
		</td></tr>
	</table>
</td></tr>
</table>
</body></html>
	<?php
	return ob_get_clean();
}

function vmi_review_email_text( $post ) {
	$d = vmi_review_email_data( $post );
	return "A new Insights article is ready for your review.\n\n"
		. $d['title'] . "\n\n"
		. ( $d['excerpt'] ? $d['excerpt'] . "\n\n" : '' )
		. 'Submitted by: ' . $d['author'] . "\n"
		. 'Submitted: ' . $d['submitted'] . "\n"
		. 'Topic: ' . $d['topics'] . "\n\n"
		. 'Review & publish: ' . $d['review'] . "\n"
		. 'Preview: ' . $d['preview'] . "\n\n"
		. '— ' . $d['site'];
}

/* -------------------------------------------------------------------------
 * Optional plugin templates (classic themes only)
 * ---------------------------------------------------------------------- */

function vmi_plugin_templates_active() {
	return VMI_USE_PLUGIN_TEMPLATES && ! ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() );
}

add_filter( 'template_include', 'vmi_template_include', 99 );
function vmi_template_include( $template ) {
	if ( ! vmi_plugin_templates_active() ) {
		return $template;
	}
	if ( is_singular( 'insight' ) && ! locate_template( 'single-insight.php' ) ) {
		return plugin_dir_path( __FILE__ ) . 'templates/single-insight.php';
	}
	if ( ( is_post_type_archive( 'insight' ) || is_tax( 'insight_topic' ) ) && ! locate_template( 'archive-insight.php' ) ) {
		return plugin_dir_path( __FILE__ ) . 'templates/archive-insight.php';
	}
	return $template;
}

add_action( 'wp_enqueue_scripts', 'vmi_styles' );
function vmi_styles() {
	if ( is_singular( 'insight' ) || is_post_type_archive( 'insight' ) || is_tax( 'insight_topic' ) ) {
		wp_enqueue_style( 'velocity-insights', plugins_url( 'assets/insights.css', __FILE__ ), array(), '1.0.0' );
	}
}
