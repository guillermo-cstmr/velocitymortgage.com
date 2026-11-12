<?php

if ( ! class_exists( 'Extended_CPT' ) ) {
	require_once VELOCITY_PATH . 'lib/extended-cpts/extended-cpts.php';
}

require_once VELOCITY_CMB2_PATH;

/**
 * Broker Toolkit documents.
 *
 * Each document is a post with its PDF, and optionally a White Label PDF.
 * The Broker Toolkit page lists them with Kadence query loops, one per
 * section, in the order set by dragging them in the admin list.
 *
 * The page never shows the PDF address. Each button links to the download
 * address of its document, which sends the PDF only once the visitor has
 * unlocked the toolkit (Gravity Form 14, remembered by a cookie). See
 * resource-modal.php for the form side.
 */
class Velocity_Toolkit {

	const POST_TYPE = 'velocity-toolkit-doc';

	const TAXONOMY = 'velocity-toolkit-section';

	const FORM_ID = 14;

	const COOKIE = 'velocity_toolkit_unlocked';

	function __construct() {}

	public static function hooks() {

		add_action( 'init', array( __CLASS__, 'cpt_setup' ) );
		add_action( 'cmb2_admin_init', array( __CLASS__, 'info_meta_box' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'fill_links' ), 99 );
		add_action( 'wp_ajax_velocity_toolkit', array( __CLASS__, 'download' ) );
		add_action( 'wp_ajax_nopriv_velocity_toolkit', array( __CLASS__, 'download' ) );
		add_action( 'gform_after_submission_' . self::FORM_ID, array( __CLASS__, 'unlock' ) );
		add_filter( 'gform_confirmation_' . self::FORM_ID, array( __CLASS__, 'confirmation' ), 20 );
		add_filter( 'wpseo_sitemap_exclude_post_type', array( __CLASS__, 'yoast_sitemap_exclude' ), 10, 2 );
		add_filter( 'wpseo_accessible_post_types', array( __CLASS__, 'yoast_hide' ) );
	}

	/**
	 * Custom Post Type and Taxonomy set up
	 *
	 * Public, because Kadence query loops only list public post types, but
	 * documents have no page of their own and stay out of search.
	 */
	public static function cpt_setup() {

		register_extended_post_type(
			self::POST_TYPE,
			array(
				'menu_icon'           => 'dashicons-media-document',
				'supports'            => array( 'title', 'excerpt', 'thumbnail', 'page-attributes' ),
				'show_in_rest'        => true,
				'public'              => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_in_nav_menus'   => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'hierarchical'        => false,
				'menu_position'       => 26,
				'labels'              => array(
					'menu_name' => __( 'Broker Toolkit', 'velocitymu' ),
				),
				'admin_cols'          => array(
					'section' => array(
						'taxonomy' => self::TAXONOMY,
					),
					'locked'  => array(
						'title'    => __( 'Unlock', 'velocitymu' ),
						'function' => array( __CLASS__, 'admin_col_locked' ),
					),
				),
			),
			array(
				'singular' => __( 'Toolkit Document', 'velocitymu' ),
				'plural'   => __( 'Toolkit Documents', 'velocitymu' ),
				'slug'     => 'toolkit-document',
			)
		);

		register_extended_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			array(
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'public'            => true,
				'publicly_queryable' => false,
				'rewrite'           => false,
				'show_in_nav_menus' => false,
			),
			array(
				'singular' => __( 'Section', 'velocitymu' ),
				'plural'   => __( 'Sections', 'velocitymu' ),
				'slug'     => 'toolkit-section',
			)
		);
	}

	/**
	 * Files and options of each document.
	 */
	public static function info_meta_box() {

		$info_meta_box = new_cmb2_box( array(
			'id'           => 'velocity_toolkit_meta_box',
			'title'        => __( 'Document', 'velocitymu' ),
			'object_types' => array( self::POST_TYPE ),
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true,
		) );

		$info_meta_box->add_field( array(
			'name'    => __( 'PDF', 'velocitymu' ),
			'id'      => 'velocity_toolkit_file',
			'type'    => 'file',
			'desc'    => __( 'The Velocity branded file. To replace it, upload the new PDF here.', 'velocitymu' ),
			'options' => array( 'url' => false ),
			'text'    => array( 'add_upload_file_text' => __( 'Choose PDF', 'velocitymu' ) ),
			'query_args' => array( 'type' => 'application/pdf' ),
		) );

		$info_meta_box->add_field( array(
			'name'    => __( 'White Label PDF', 'velocitymu' ),
			'id'      => 'velocity_toolkit_file_wl',
			'type'    => 'file',
			'desc'    => __( 'Optional. When set, the document shows a second button for the White Label version.', 'velocitymu' ),
			'options' => array( 'url' => false ),
			'text'    => array( 'add_upload_file_text' => __( 'Choose PDF', 'velocitymu' ) ),
			'query_args' => array( 'type' => 'application/pdf' ),
		) );

		$info_meta_box->add_field( array(
			'name' => __( 'Requires unlock', 'velocitymu' ),
			'id'   => 'velocity_toolkit_locked',
			'type' => 'checkbox',
			'desc' => __( 'The visitor fills the toolkit form once before downloading. Leave unticked for files anyone can download, like the forms library.', 'velocitymu' ),
		) );

		$info_meta_box->add_field( array(
			'name' => __( 'New', 'velocitymu' ),
			'id'   => 'velocity_toolkit_new',
			'type' => 'checkbox',
			'desc' => __( 'Shows the "New" tag.', 'velocitymu' ),
		) );

		$info_meta_box->add_field( array(
			'name'       => __( 'Request ID', 'velocitymu' ),
			'id'         => 'velocity_toolkit_request_id',
			'type'       => 'text_medium',
			'desc'       => __( 'Saved with each form entry and sent to Salesforce, to know which file was asked for. Filled from the title when empty. Do not change it once the document is in use.', 'velocitymu' ),
			'sanitization_cb' => array( __CLASS__, 'sanitize_request_id' ),
		) );
	}

	/**
	 * Request IDs are short, lowercase, letters, numbers and dashes.
	 */
	public static function sanitize_request_id( $value ) {
		return substr( sanitize_title( (string) $value ), 0, 60 );
	}

	/**
	 * Stores the download addresses the buttons link to:
	 * velocity_toolkit_link, and velocity_toolkit_link_wl when there is a
	 * White Label file.
	 */
	public static function fill_links( $post_id ) {

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( '' === (string) get_post_meta( $post_id, 'velocity_toolkit_request_id', true ) ) {
			$slug = get_post_field( 'post_name', $post_id );
			if ( ! $slug ) {
				$slug = sanitize_title( get_the_title( $post_id ) );
			}
			if ( $slug ) {
				update_post_meta( $post_id, 'velocity_toolkit_request_id', self::sanitize_request_id( $slug ) );
			}
		}

		foreach ( array( '' => 'velocity_toolkit_file_id', 'wl' => 'velocity_toolkit_file_wl_id' ) as $version => $file_key ) {
			$link_key = 'velocity_toolkit_link' . ( $version ? '_' . $version : '' );
			if ( get_post_meta( $post_id, $file_key, true ) ) {
				update_post_meta( $post_id, $link_key, self::link( $post_id, $version ) );
			} else {
				delete_post_meta( $post_id, $link_key );
			}
		}
	}

	/**
	 * Download address of a document. It carries the request ID so the form
	 * can record which file was asked for, and "locked" so the page script
	 * knows to open the form first.
	 */
	public static function link( $post_id, $version = '' ) {

		$args = array(
			'action' => 'velocity_toolkit',
			'doc'    => $post_id,
			'rid'    => get_post_meta( $post_id, 'velocity_toolkit_request_id', true ),
		);

		if ( 'wl' === $version ) {
			$args['version'] = 'wl';
			$args['rid']    .= 'wl';
		}

		if ( get_post_meta( $post_id, 'velocity_toolkit_locked', true ) ) {
			$args['locked'] = 1;
		}

		return add_query_arg( $args, admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Sends the PDF, or, for a locked document the visitor has not unlocked,
	 * goes back to the page and opens the form.
	 */
	public static function download() {

		$post_id = isset( $_GET['doc'] ) ? absint( $_GET['doc'] ) : 0;
		$version = ( isset( $_GET['version'] ) && 'wl' === $_GET['version'] ) ? 'wl' : '';

		// Old links only know the request ID.
		if ( ! $post_id && ! empty( $_GET['rid'] ) ) {
			$found = self::find_by_request_id( sanitize_title( wp_unslash( $_GET['rid'] ) ) );
			if ( $found ) {
				list( $post_id, $version ) = $found;
			}
		}

		if ( ! $post_id || self::POST_TYPE !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
			wp_die( esc_html__( 'This file is no longer available.', 'velocitymu' ), '', array( 'response' => 404 ) );
		}

		if ( get_post_meta( $post_id, 'velocity_toolkit_locked', true ) && ! self::is_unlocked() ) {
			$back = wp_get_referer();
			if ( ! $back ) {
				$back = home_url( '/' );
			}
			$rid = get_post_meta( $post_id, 'velocity_toolkit_request_id', true ) . ( $version ? 'wl' : '' );
			wp_safe_redirect( strtok( $back, '#' ) . '#pdfrequest&resource_id=' . rawurlencode( $rid ) );
			exit;
		}

		$file_id = absint( get_post_meta( $post_id, $version ? 'velocity_toolkit_file_wl_id' : 'velocity_toolkit_file_id', true ) );
		$path    = $file_id ? get_attached_file( $file_id ) : '';

		if ( ! $path || ! is_readable( $path ) ) {
			// File not on this server, for example uploads served from elsewhere.
			$url = $file_id ? wp_get_attachment_url( $file_id ) : '';
			if ( ! $url ) {
				wp_die( esc_html__( 'This file is no longer available.', 'velocitymu' ), '', array( 'response' => 404 ) );
			}
			nocache_headers();
			wp_redirect( $url );
			exit;
		}

		// Sent as a download: the visitor stays on the page, and the file's
		// address is never shown.
		nocache_headers();
		header( 'Content-Type: ' . ( get_post_mime_type( $file_id ) ? get_post_mime_type( $file_id ) : 'application/pdf' ) );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( wp_basename( $path ) ) . '"' );
		header( 'Content-Length: ' . filesize( $path ) );
		header( 'X-Robots-Tag: noindex' );
		while ( ob_get_level() ) {
			ob_end_clean();
		}
		readfile( $path );
		exit;
	}

	/**
	 * Whether this visitor has unlocked the toolkit.
	 */
	public static function is_unlocked() {
		return ! empty( $_COOKIE[ self::COOKIE ] );
	}

	/**
	 * Remembers the unlock for a year, on this browser.
	 */
	public static function unlock() {

		if ( headers_sent() ) {
			return;
		}

		setcookie( self::COOKIE, '1', array(
			'expires'  => time() + YEAR_IN_SECONDS,
			'path'     => COOKIEPATH ? COOKIEPATH : '/',
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => false,
			'samesite' => 'Lax',
		) );
	}

	/**
	 * One confirmation for every document. The page script starts the
	 * download of the file that was asked for.
	 */
	public static function confirmation( $confirmation ) {

		if ( is_array( $confirmation ) ) {
			return $confirmation;
		}

		return '<div class="vm-toolkit-confirmation"><p>'
			. esc_html__( 'Your form has been submitted! An account executive will follow up to help you put these files to work.', 'velocitymu' )
			. '</p></div>';
	}

	/**
	 * Document lookup by request ID, for the old #pdfrequest links.
	 */
	public static function find_by_request_id( $request_id ) {

		$find = function ( $id ) {
			$ids = get_posts( array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => 'velocity_toolkit_request_id',
				'meta_value'     => $id,
			) );
			return $ids ? $ids[0] : 0;
		};

		$post_id = $find( $request_id );
		if ( $post_id ) {
			return array( $post_id, '' );
		}

		// White Label versions use the document's ID followed by "wl".
		if ( 'wl' === substr( $request_id, -2 ) ) {
			$post_id = $find( substr( $request_id, 0, -2 ) );
			if ( $post_id && get_post_meta( $post_id, 'velocity_toolkit_file_wl_id', true ) ) {
				return array( $post_id, 'wl' );
			}
		}

		return null;
	}

	/**
	 * Admin list column.
	 */
	public static function admin_col_locked() {
		echo get_post_meta( get_the_ID(), 'velocity_toolkit_locked', true ) ? esc_html__( 'Yes', 'velocitymu' ) : '—';
	}

	/**
	 * Documents have no page, so they stay out of Yoast.
	 */
	public static function yoast_sitemap_exclude( $exclude, $post_type ) {
		return self::POST_TYPE === $post_type ? true : $exclude;
	}

	public static function yoast_hide( $post_types ) {
		unset( $post_types[ self::POST_TYPE ] );
		return $post_types;
	}

}

Velocity_Toolkit::hooks();
