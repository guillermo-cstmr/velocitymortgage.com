<?php
/**
 * WP-CLI commands for the rebuild.
 *
 * Kept compatible with PHP 7.4: the WP Engine SSH Gateway can run an older
 * PHP than the website does.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tools for the Velocity rebuild.
 */
class Velocity_CLI {

	/**
	 * Front-end post types from the old Kadence and Beaver Builder builds.
	 * Every post of these types is removed.
	 *
	 * @var string[]
	 */
	const POST_TYPES_TO_REMOVE = array(
		'kadence_element',
		'kadence_header',
		'kadence_navigation',
		'kadence_query',
		'kadence_query_card',
		'kadence_conversions',
		'wp_block',
		'wp_navigation',
		'wp_global_styles',
		'custom_css',
		'fl-builder-template',
		'fl-theme-layout',
		'fl-builder-history',
	);

	/**
	 * Synced patterns that code depends on. Never removed.
	 * "Insights CTA" is read by the velocity-insights plugin.
	 *
	 * @var string[]
	 */
	const PROTECTED_BLOCK_TITLES = array( 'Insights CTA' );

	/**
	 * Published pages that are removed instead of emptied.
	 *
	 * @var string[]
	 */
	const PAGE_SLUGS_TO_REMOVE = array( 'example-content' );

	/**
	 * Pages whose content is left untouched. Legal text.
	 *
	 * @var string[]
	 */
	const PAGE_SLUGS_TO_KEEP = array( 'privacy', 'terms-of-use' );

	/**
	 * Options that hold the old design. Backed up to a file, then deleted.
	 *
	 * Kadence Blocks rebuilds its two options from the active palette on the
	 * next page load, so they show up again on a later dry run. That is expected.
	 *
	 * @var string[]
	 */
	const OPTIONS_TO_REMOVE = array(
		'kadence_global_palette',
		'kadence_blocks_colors',
		'kadence_blocks_design_tokens_palette_sync',
		'theme_mods_kadence-child',
		'theme_mods_velocity-bb-child',
		'theme_mods_twentyseventeen',
	);

	/**
	 * Whether changes are applied. False means dry run.
	 *
	 * @var bool
	 */
	private $execute = false;

	/**
	 * Whether posts are deleted permanently instead of trashed.
	 *
	 * @var bool
	 */
	private $force = false;

	/**
	 * Removes the old front end and keeps all the content.
	 *
	 * Without --execute it only reports what it would do.
	 *
	 * What it removes:
	 * - Kadence Elements, header, navigations, queries, query cards and popups.
	 * - Synced patterns, old global styles and Additional CSS.
	 * - Beaver Builder layouts, templates and their post meta.
	 * - Draft pages and the pages listed as obsolete.
	 * - Classic nav menus.
	 * - The saved palette and the old themes' Customizer settings.
	 *
	 * What it empties: every other published page keeps its ID, URL and SEO
	 * data, and loses its content and its per-page Kadence settings.
	 *
	 * What it never touches: posts, custom post types, taxonomies, media,
	 * forms, form entries, users, plugin settings, the Privacy and Terms pages.
	 *
	 * ## OPTIONS
	 *
	 * [--execute]
	 * : Apply the changes. Requires the velocity-kadence theme to be active.
	 *
	 * [--force]
	 * : Delete posts permanently instead of moving them to the trash.
	 *
	 * ## EXAMPLES
	 *
	 *     wp velocity cleanup
	 *     wp velocity cleanup --execute
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Flags.
	 */
	public function cleanup( $args, $assoc_args ) {
		$this->execute = (bool) WP_CLI\Utils\get_flag_value( $assoc_args, 'execute', false );
		$this->force   = (bool) WP_CLI\Utils\get_flag_value( $assoc_args, 'force', false );

		if ( $this->execute && 'velocity-kadence' !== get_stylesheet() ) {
			WP_CLI::error( 'Activate the velocity-kadence theme first. Running this with the old theme active would wipe its live settings.' );
		}

		WP_CLI::log( $this->execute ? 'EXECUTING. Changes are being applied.' : 'DRY RUN. Nothing is changed. Add --execute to apply.' );
		WP_CLI::log( 'Site: ' . home_url() . ' | Active theme: ' . get_stylesheet() );

		if ( $this->execute ) {
			$this->backup_options();
		}

		$totals = array(
			'Old front-end posts removed' => $this->remove_front_end_posts(),
			'Pages removed'               => $this->remove_pages(),
			'Pages emptied'               => $this->empty_pages(),
			'Beaver Builder meta rows'    => $this->remove_beaver_meta(),
			'Nav menus removed'           => $this->remove_nav_menus(),
			'Options removed'             => $this->remove_options(),
		);

		WP_CLI::log( '' );
		WP_CLI::log( '== Summary ==' );
		foreach ( $totals as $label => $count ) {
			WP_CLI::log( sprintf( '%-30s %d', $label, $count ) );
		}

		if ( $this->execute ) {
			wp_cache_flush();
			WP_CLI::success( 'Cleanup applied.' );
		} else {
			WP_CLI::success( 'Dry run finished. Nothing was changed.' );
		}
	}

	/**
	 * Save the options that are about to be deleted to a JSON file in uploads.
	 */
	private function backup_options() {
		$data = array();

		foreach ( self::OPTIONS_TO_REMOVE as $name ) {
			$value = get_option( $name, null );
			if ( null !== $value ) {
				$data[ $name ] = $value;
			}
		}

		$uploads = wp_upload_dir();
		$file    = trailingslashit( $uploads['basedir'] ) . 'velocity-cleanup-options-' . gmdate( 'Ymd-His' ) . '.json';

		if ( false === file_put_contents( $file, wp_json_encode( $data, JSON_PRETTY_PRINT ) ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			WP_CLI::error( 'Could not write the options backup to ' . $file . '. Nothing was changed.' );
		}

		WP_CLI::log( 'Options backup written to ' . $file );
	}

	/**
	 * Remove one post, or report it on a dry run.
	 *
	 * @param object $post Row with ID, post_type, post_status and post_title.
	 * @param string $verb What is being done, for the log.
	 */
	private function remove_post( $post, $verb = 'remove' ) {
		WP_CLI::log( sprintf( '  %s  #%d  %-20s %-8s %s', $verb, $post->ID, $post->post_type, $post->post_status, $post->post_title ) );

		if ( ! $this->execute ) {
			return;
		}

		if ( $this->force ) {
			wp_delete_post( $post->ID, true );
		} else {
			wp_trash_post( $post->ID );
		}
	}

	/**
	 * Remove every post of the old front-end post types.
	 *
	 * Queries the table directly because some of these post types are not
	 * registered any more (Beaver Builder is not active).
	 *
	 * @return int Number of posts.
	 */
	private function remove_front_end_posts() {
		global $wpdb;

		WP_CLI::log( '' );
		WP_CLI::log( '== Old front-end posts ==' );

		$placeholders = implode( ',', array_fill( 0, count( self::POST_TYPES_TO_REMOVE ), '%s' ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$posts = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_type, post_status, post_title FROM {$wpdb->posts} WHERE post_type IN ($placeholders) AND post_status NOT IN ('trash','auto-draft','inherit') ORDER BY post_type, ID", self::POST_TYPES_TO_REMOVE ) );

		$count = 0;
		foreach ( $posts as $post ) {
			if ( 'wp_block' === $post->post_type && in_array( $post->post_title, self::PROTECTED_BLOCK_TITLES, true ) ) {
				WP_CLI::log( sprintf( '  keep    #%d  wp_block  %s (used by code)', $post->ID, $post->post_title ) );
				continue;
			}

			$this->remove_post( $post );
			++$count;
		}

		return $count;
	}

	/**
	 * All pages that are not trashed.
	 *
	 * @return object[]
	 */
	private function get_pages() {
		global $wpdb;

		return $wpdb->get_results( "SELECT ID, post_type, post_status, post_title, post_name, LENGTH(post_content) AS content_length FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status NOT IN ('trash','auto-draft','inherit') ORDER BY ID" );
	}

	/**
	 * Whether a page is removed rather than emptied.
	 *
	 * @param object $page Page row.
	 * @return bool
	 */
	private function is_page_removed( $page ) {
		return 'publish' !== $page->post_status || in_array( $page->post_name, self::PAGE_SLUGS_TO_REMOVE, true );
	}

	/**
	 * Remove draft pages and the pages listed as obsolete.
	 *
	 * @return int Number of pages.
	 */
	private function remove_pages() {
		WP_CLI::log( '' );
		WP_CLI::log( '== Pages removed (drafts and obsolete) ==' );

		$count = 0;
		foreach ( $this->get_pages() as $page ) {
			if ( ! $this->is_page_removed( $page ) ) {
				continue;
			}

			$this->remove_post( $page );
			++$count;
		}

		return $count;
	}

	/**
	 * Empty the content of the published pages that stay.
	 *
	 * The page keeps its ID, slug, parent and SEO meta. WordPress stores the
	 * old content as a revision.
	 *
	 * @return int Number of pages.
	 */
	private function empty_pages() {
		global $wpdb;

		WP_CLI::log( '' );
		WP_CLI::log( '== Pages emptied (ID, URL and SEO data stay) ==' );

		$count = 0;
		foreach ( $this->get_pages() as $page ) {
			if ( $this->is_page_removed( $page ) ) {
				continue;
			}

			if ( in_array( $page->post_name, self::PAGE_SLUGS_TO_KEEP, true ) ) {
				WP_CLI::log( sprintf( '  keep    #%d  /%s/  %s (content untouched)', $page->ID, $page->post_name, $page->post_title ) );
				continue;
			}

			// Already empty: nothing to do. Keeps the command safe to run twice.
			if ( 0 === (int) $page->content_length ) {
				continue;
			}

			WP_CLI::log( sprintf( '  empty   #%d  /%s/  %s (%d bytes)', $page->ID, $page->post_name, $page->post_title, $page->content_length ) );
			++$count;

			if ( ! $this->execute ) {
				continue;
			}

			wp_update_post(
				array(
					'ID'           => $page->ID,
					'post_content' => '',
				)
			);

			// Per-page Kadence settings: title, layout, transparent header.
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE %s", $page->ID, $wpdb->esc_like( '_kad_' ) . '%' ) );
			clean_post_cache( $page->ID );
		}

		return $count;
	}

	/**
	 * Remove Beaver Builder layout data left on posts.
	 *
	 * @return int Number of meta rows.
	 */
	private function remove_beaver_meta() {
		global $wpdb;

		WP_CLI::log( '' );
		WP_CLI::log( '== Beaver Builder post meta ==' );

		$like  = $wpdb->esc_like( '_fl_builder' ) . '%';
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", $like ) );

		WP_CLI::log( sprintf( '  remove  %d rows with a meta key starting with _fl_builder', $count ) );

		if ( $this->execute && $count ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", $like ) );
		}

		return $count;
	}

	/**
	 * Remove classic nav menus. The new header uses Kadence navigation blocks.
	 *
	 * @return int Number of menus.
	 */
	private function remove_nav_menus() {
		WP_CLI::log( '' );
		WP_CLI::log( '== Classic nav menus ==' );

		$menus = wp_get_nav_menus();

		foreach ( $menus as $menu ) {
			WP_CLI::log( sprintf( '  remove  %s (%d items)', $menu->name, $menu->count ) );

			if ( $this->execute ) {
				wp_delete_nav_menu( $menu->term_id );
			}
		}

		return count( $menus );
	}

	/**
	 * Remove the options that hold the old design.
	 *
	 * @return int Number of options that existed.
	 */
	private function remove_options() {
		WP_CLI::log( '' );
		WP_CLI::log( '== Options ==' );

		$count = 0;
		foreach ( self::OPTIONS_TO_REMOVE as $name ) {
			if ( null === get_option( $name, null ) ) {
				WP_CLI::log( '  absent  ' . $name );
				continue;
			}

			WP_CLI::log( '  remove  ' . $name );
			++$count;

			if ( $this->execute ) {
				delete_option( $name );
			}
		}

		return $count;
	}
}

WP_CLI::add_command( 'velocity', 'Velocity_CLI' );
