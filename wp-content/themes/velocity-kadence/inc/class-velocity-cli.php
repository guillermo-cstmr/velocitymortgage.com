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
	 * Creates the header, the menus and the footer as Kadence block content.
	 *
	 * This builds the first version of each, so every environment starts from
	 * the same point. After that they are edited in the block editor:
	 * - Header: Kadence, Header, "Global Header".
	 * - Menus: Kadence, Navigation.
	 * - Footer: Kadence, Elements, "Global Footer".
	 *
	 * Without --execute it only reports what it would do. Anything that
	 * already exists is left alone, so editor changes are never overwritten.
	 *
	 * ## OPTIONS
	 *
	 * [--execute]
	 * : Create what is missing.
	 *
	 * [--force]
	 * : Also rebuild what already exists. This DISCARDS changes made in the
	 * editor to the header, the menus and the footer.
	 *
	 * ## EXAMPLES
	 *
	 *     wp velocity seed-chrome
	 *     wp velocity seed-chrome --execute
	 *
	 * @subcommand seed-chrome
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Flags.
	 */
	public function seed_chrome( $args, $assoc_args ) {
		$this->execute = (bool) WP_CLI\Utils\get_flag_value( $assoc_args, 'execute', false );
		$this->force   = (bool) WP_CLI\Utils\get_flag_value( $assoc_args, 'force', false );

		foreach ( array( 'kadence_header', 'kadence_navigation', 'kadence_element' ) as $type ) {
			if ( ! post_type_exists( $type ) ) {
				WP_CLI::error( 'Post type ' . $type . ' is not registered. Kadence Blocks and Kadence Pro must be active.' );
			}
		}

		WP_CLI::log( $this->execute ? 'EXECUTING.' : 'DRY RUN. Nothing is changed. Add --execute to apply.' );
		if ( $this->force ) {
			WP_CLI::warning( '--force: existing header, menus and footer are rebuilt. Editor changes to them are lost.' );
		}

		$ids = array();

		WP_CLI::log( '' );
		WP_CLI::log( '== Menus ==' );
		foreach ( Velocity_Seed::navigations() as $key => $nav ) {
			$meta = array();
			foreach ( $nav['meta'] as $name => $value ) {
				$meta[ '_kad_navigation_' . $name ] = $value;
			}

			$items       = $nav['items'];
			$ids[ $key ] = $this->seed_post(
				$key,
				'kadence_navigation',
				$nav['title'],
				static function ( $post_id ) use ( $items ) {
					return Velocity_Seed::navigation_content( $post_id, $items );
				},
				$meta
			);
		}

		WP_CLI::log( '' );
		WP_CLI::log( '== Header ==' );
		$header_id = $this->seed_post(
			'header',
			'kadence_header',
			'Global Header',
			static function ( $post_id ) use ( $ids ) {
				return Velocity_Seed::header_content( $post_id, $ids['nav-header'] );
			},
			array(
				'_kad_header_background' => array(
					'type'  => 'normal',
					'color' => 'palette3',
				),
			)
		);

		WP_CLI::log( '' );
		WP_CLI::log( '== Footer ==' );
		$this->seed_post(
			'footer',
			'kadence_element',
			'Global Footer',
			static function ( $post_id ) use ( $ids ) {
				return Velocity_Seed::footer_content( $post_id, $ids );
			},
			array(
				'_kad_element_type'              => 'default',
				'_kad_element_hook'              => 'replace_footer',
				'_kad_element_hook_priority'     => 10,
				'_kad_element_show_conditionals' => '[{"rule":"general|site","select":"all","ids":[],"subRule":"","subSelection":[],"mustMatch":false}]',
			)
		);

		if ( $this->execute ) {
			// Tell the Kadence theme to render the block header instead of its own.
			set_theme_mod( 'blocks_header', true );
			set_theme_mod( 'blocks_header_id', $header_id );
			wp_cache_flush();
			WP_CLI::success( 'Header, menus and footer are in place. Edit them in the block editor from now on.' );
		} else {
			WP_CLI::success( 'Dry run finished. Nothing was changed.' );
		}
	}

	/**
	 * Fills the home page with its sections as block content.
	 *
	 * It writes to the page set as the front page in Settings, Reading.
	 * Without --execute it only reports what it would do. A home page that
	 * already has content is left alone, so editor work is never overwritten.
	 *
	 * ## OPTIONS
	 *
	 * [--execute]
	 * : Write the content if the page is empty.
	 *
	 * [--force]
	 * : Replace the content even if the page has some. This DISCARDS what is
	 * there. WordPress keeps the old version as a revision.
	 *
	 * ## EXAMPLES
	 *
	 *     wp velocity seed-home
	 *     wp velocity seed-home --execute
	 *
	 * @subcommand seed-home
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Flags.
	 */
	public function seed_home( $args, $assoc_args ) {
		$this->execute = (bool) WP_CLI\Utils\get_flag_value( $assoc_args, 'execute', false );
		$this->force   = (bool) WP_CLI\Utils\get_flag_value( $assoc_args, 'force', false );

		$page_id = (int) get_option( 'page_on_front' );
		$page    = $page_id ? get_post( $page_id ) : null;

		if ( ! $page || 'page' !== $page->post_type ) {
			WP_CLI::error( 'No front page is set in Settings, Reading.' );
		}

		WP_CLI::log( $this->execute ? 'EXECUTING.' : 'DRY RUN. Nothing is changed. Add --execute to apply.' );
		WP_CLI::log( sprintf( 'Front page: #%d "%s", %d bytes of content.', $page_id, $page->post_title, strlen( $page->post_content ) ) );

		if ( '' !== trim( $page->post_content ) && ! $this->force ) {
			WP_CLI::success( 'The page already has content. Left as it is.' );
			return;
		}

		if ( ! $this->execute ) {
			WP_CLI::success( 'Dry run finished. The page would be filled.' );
			return;
		}

		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => wp_slash( Velocity_Home::content( $page_id ) ),
			)
		);

		WP_CLI::success( 'Home page content written. Edit it in the block editor from now on.' );
	}

	/**
	 * Fills in the details of each loan program, in Properties, Programs.
	 *
	 * Creates the programs the new site needs and does not have yet, and fills
	 * the empty fields of every program it has data for. A field that already
	 * has a value is never changed, so edits made in the admin are kept.
	 *
	 * Sources:
	 * - 30-Year Fixed, Fast50, Super Jumbo, Flex I/O: Figma, notes of the
	 *   homepage calculator.
	 * - Foreign Investor, ARV Pro: the old Loan Programs page. Those details
	 *   may be out of date.
	 * - FlexTerm, FlexPerm: no source. Left empty.
	 *
	 * ## OPTIONS
	 *
	 * [--execute]
	 * : Apply the changes.
	 *
	 * ## EXAMPLES
	 *
	 *     wp velocity seed-programs
	 *     wp velocity seed-programs --execute
	 *
	 * @subcommand seed-programs
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Flags.
	 */
	public function seed_programs( $args, $assoc_args ) {
		$this->execute = (bool) WP_CLI\Utils\get_flag_value( $assoc_args, 'execute', false );
		$taxonomy      = 'velocity-property-program';

		if ( ! taxonomy_exists( $taxonomy ) ) {
			WP_CLI::error( 'The program taxonomy is not registered. The Velocity must-use plugin must be active.' );
		}

		WP_CLI::log( $this->execute ? 'EXECUTING.' : 'DRY RUN. Nothing is changed. Add --execute to apply.' );

		// Slug => name and fields. Field keys are stored as velocity_program_{key}.
		$programs = array(
			'30-year-fixed'    => array(
				'name'   => '30-Year Fixed',
				'fields' => array(
					'description'   => 'Long-term financing for investors who plan to hold.',
					'loan_amounts'  => '$75K – $2MM',
					'max_ltv'       => '75%',
					'term'          => 'Fixed for 30 years',
					'time_to_close' => '2 weeks to 45 days',
				),
			),
			'fast50'           => array(
				'name'   => 'Fast50',
				'fields' => array(
					'description'   => 'Fixed for 30 years, for investors with plenty of equity, even with recent credit events.',
					'loan_amounts'  => 'Up to $2MM',
					'max_ltv'       => '50%',
					'term'          => 'Fixed for 30 years',
					'time_to_close' => '2 to 6 weeks',
				),
			),
			'super-jumbo'      => array(
				'name'   => 'Super Jumbo',
				'fields' => array(
					'description'   => 'The 30-Year Fixed at a larger size, for seasoned investors in major metro areas.',
					'loan_amounts'  => '$2MM – $10MM',
					'max_ltv'       => '65%',
					'term'          => 'Fixed for 30 years',
					'time_to_close' => '2 weeks to 45 days',
				),
			),
			'flex-io'          => array(
				'name'   => 'Flex I/O',
				'fields' => array(
					'description'   => 'A 24-month, interest-only bridge until your investor sells or refinances.',
					'loan_amounts'  => '$75K – $2MM',
					'max_ltv'       => '75%',
					'term'          => '24 months, interest only',
					'time_to_close' => '2 weeks to 45 days',
				),
			),
			'foreign-investor' => array(
				'name'   => 'Foreign Investor',
				'fields' => array(
					'description' => 'For foreign investors seeking investment properties in the U.S. No U.S. credit required.',
					'max_ltv'     => '65%',
					'term'        => 'Fixed for 30 years',
				),
			),
			'arv-pro'          => array(
				'name'   => 'ARV Pro',
				'fields' => array(
					'description' => 'A short-term loan that finances improvements based on the property’s as-repaired value.',
					'term'        => '1 year, interest only',
				),
			),
		);

		foreach ( $programs as $slug => $program ) {
			$term = get_term_by( 'slug', $slug, $taxonomy );

			if ( ! $term ) {
				WP_CLI::log( sprintf( '  create   %s', $program['name'] ) );

				if ( ! $this->execute ) {
					foreach ( $program['fields'] as $key => $value ) {
						WP_CLI::log( sprintf( '    fill   %-14s %s', $key, $value ) );
					}
					continue;
				}

				$created = wp_insert_term( $program['name'], $taxonomy, array( 'slug' => $slug ) );

				if ( is_wp_error( $created ) ) {
					WP_CLI::error( $created->get_error_message() );
				}

				$term_id = (int) $created['term_id'];
			} else {
				WP_CLI::log( sprintf( '  exists   %s (#%d, %d deals)', $term->name, $term->term_id, $term->count ) );
				$term_id = (int) $term->term_id;
			}

			foreach ( $program['fields'] as $key => $value ) {
				$meta_key = 'velocity_program_' . $key;
				$current  = $term ? (string) get_term_meta( $term_id, $meta_key, true ) : '';

				if ( '' !== trim( $current ) ) {
					WP_CLI::log( sprintf( '    keep   %-14s %s', $key, $current ) );
					continue;
				}

				WP_CLI::log( sprintf( '    fill   %-14s %s', $key, $value ) );

				if ( $this->execute ) {
					update_term_meta( $term_id, $meta_key, $value );
				}
			}
		}

		if ( $this->execute ) {
			WP_CLI::success( 'Programs are filled in. Edit them in Properties, Programs.' );
		} else {
			WP_CLI::success( 'Dry run finished. Nothing was changed.' );
		}
	}

	/**
	 * Create one seeded post, or leave it alone if it exists.
	 *
	 * @param string   $key     Seed key, stored in post meta to find it later.
	 * @param string   $type    Post type.
	 * @param string   $title   Post title.
	 * @param callable $content Builds the content from the post ID.
	 * @param array    $meta    Post meta to set.
	 * @return int Post ID, or 0 on a dry run for a post that does not exist.
	 */
	private function seed_post( $key, $type, $title, $content, $meta ) {
		$found = get_posts(
			array(
				'post_type'   => $type,
				'post_status' => array( 'publish', 'draft', 'private' ),
				'meta_key'    => Velocity_Seed::MARKER, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'  => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'numberposts' => 1,
				'fields'      => 'ids',
			)
		);

		$post_id = $found ? (int) $found[0] : 0;

		if ( $post_id && ! $this->force ) {
			WP_CLI::log( sprintf( '  exists   #%d  %s (left as it is)', $post_id, $title ) );
			return $post_id;
		}

		WP_CLI::log( sprintf( '  %s  %s  %s', $post_id ? 'rebuild' : 'create ', $type, $title ) );

		if ( ! $this->execute ) {
			return $post_id;
		}

		if ( ! $post_id ) {
			$post_id = wp_insert_post(
				array(
					'post_type'   => $type,
					'post_title'  => $title,
					'post_status' => 'publish',
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				WP_CLI::error( $post_id->get_error_message() );
			}

			update_post_meta( $post_id, Velocity_Seed::MARKER, $key );
		}

		// Block unique IDs include the post ID, so content is built after insert.
		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => wp_slash( call_user_func( $content, $post_id ) ),
			)
		);

		foreach ( $meta as $name => $value ) {
			update_post_meta( $post_id, $name, $value );
		}

		return (int) $post_id;
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
		$posts = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_type, post_status, post_title, post_name FROM {$wpdb->posts} WHERE post_type IN ($placeholders) AND post_status NOT IN ('trash','auto-draft','inherit') ORDER BY post_type, ID", self::POST_TYPES_TO_REMOVE ) );

		$count = 0;
		foreach ( $posts as $post ) {
			if ( 'wp_block' === $post->post_type && in_array( $post->post_title, self::PROTECTED_BLOCK_TITLES, true ) ) {
				WP_CLI::log( sprintf( '  keep    #%d  wp_block  %s (used by code)', $post->ID, $post->post_title ) );
				continue;
			}

			// Global styles that belong to the active theme.
			if ( 'wp_global_styles' === $post->post_type && 'wp-global-styles-' . get_stylesheet() === $post->post_name ) {
				WP_CLI::log( sprintf( '  keep    #%d  wp_global_styles  (active theme)', $post->ID ) );
				continue;
			}

			// The new header, menus and footer made by `wp velocity seed-chrome`.
			if ( get_post_meta( $post->ID, Velocity_Seed::MARKER, true ) ) {
				WP_CLI::log( sprintf( '  keep    #%d  %-20s %s (new build)', $post->ID, $post->post_type, $post->post_title ) );
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
