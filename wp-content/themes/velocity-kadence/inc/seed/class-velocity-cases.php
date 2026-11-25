<?php
/**
 * Builds Case Studies as block content: the list at /case-studies/ and the
 * page of a case study.
 *
 * Both addresses belong to a post type (Case Studies), not to a page, so
 * their content is two Kadence Elements (Kadence > Elements): "Case
 * Studies" and "Case Studies: case study". Like the pages,
 * `wp velocity seed-cases` only produces their first version: after that
 * they are edited in the block editor.
 *
 * They are built like News and Insights (class-velocity-insights.php), with
 * the filters and the card of Featured Deals. What a query or a block has
 * no setting for is in inc/case-studies.php.
 *
 * Figma: page "Resources", sections "Case Studies" and "Case Studies Post".
 * Copy: the page's copy document (V1).
 *
 * Kept compatible with PHP 7.4, see class-velocity-cli.php.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Case Studies seed content.
 */
class Velocity_Cases {

	const POST_TYPE = 'velocity-case-study';

	/**
	 * The program the design asks for besides the four of 1-4 units, for the
	 * case studies of multifamily and commercial deals.
	 */
	const EXTRA_PROGRAM = 'Multifamily & Commercial';

	/**
	 * The filters of the list: label, taxonomy, text of the empty choice.
	 *
	 * @return array[] By key.
	 */
	private static function filters() {
		return array(
			'type'    => array( 'Property Type', 'velocity-property-type', 'All types' ),
			'purpose' => array( 'Loan Purpose', 'velocity-property-purpose', 'All purposes' ),
			'program' => array( 'Loan Program', 'velocity-property-program', 'All programs' ),
		);
	}

	/**
	 * What Kadence keeps about the list's filters, next to the query. See
	 * Velocity_Insights::facets().
	 *
	 * @param int $query_id Query post ID.
	 * @return array[]
	 */
	public static function facets( $query_id ) {
		$facets = array();

		foreach ( self::filters() as $key => $filter ) {
			$settings = array(
				'source'        => 'taxonomy',
				'fieldType'     => 'post_field',
				'taxonomy'      => $filter[1],
				'post_field'    => 'post_type',
				'include'       => array(),
				'exclude'       => array(),
				'type'          => 'query-filter',
				'customField'   => '',
				'customMetaKey' => '',
			);

			$facets[] = array(
				'hash'       => Velocity_Insights::hash( wp_json_encode( $settings ) ),
				'attributes' => wp_json_encode(
					array_merge(
						$settings,
						array(
							'uniqueID' => Velocity_Seed::uid( $query_id, 'filter-' . $key ),
							'slug'     => $key,
						)
					)
				),
			);
		}

		return $facets;
	}

	/**
	 * The lists: the featured case study, the list of all of them, and the
	 * three under a case study. "role" is what inc/case-studies.php reads.
	 *
	 * The card of the list is the one Featured Deals shows its case studies
	 * with, so the two pages show a case study the same way.
	 *
	 * @return array[] By key, as Velocity_Seed::query_meta() takes them.
	 */
	public static function queries() {
		$card = array(
			'post_type'  => self::POST_TYPE,
			'order_by'   => 'date',
			'order'      => 'desc',
			'columns'    => array( '3', '2', '1' ),
			'column_gap' => array( '24', '20', '20' ),
			'row_gap'    => array( '24', '20', '20' ),
			'card'       => array( 'Velocity_Deals_Page', 'story_card' ),
			'card_key'   => 'story',
			'card_title' => 'Case Studies: case study card',
		);

		return array(
			'featured' => array(
				'title'      => 'Case Studies: featured case study',
				'post_type'  => self::POST_TYPE,
				'order_by'   => 'date',
				'order'      => 'desc',
				'per_page'   => '1',
				'columns'    => array( '1', '1', '1' ),
				'column_gap' => array( '0', '0', '0' ),
				'row_gap'    => array( '0', '0', '0' ),
				'card'       => array( __CLASS__, 'featured_card' ),
				'meta'       => array( '_vm_query_role' => 'cases-featured' ),
			),
			'index'    => array_merge(
				$card,
				array(
					'title'    => 'Case Studies: case studies',
					'per_page' => '9',
					'content'  => array( __CLASS__, 'index_content' ),
					'facets'   => array( __CLASS__, 'facets' ),
					'meta'     => array_merge(
						array( '_vm_query_role' => 'cases-index' ),
						// The look of the filter fields, as on Featured Deals.
						self::field_style()
					),
				)
			),
			'related'  => array_merge(
				$card,
				array(
					'title'    => 'Case Studies: you may also like',
					'per_page' => '3',
					'meta'     => array( '_vm_query_role' => 'cases-related' ),
				)
			),
		);
	}

	/**
	 * The look of the filter fields: settings of the query, under Style. The
	 * same as the gallery of Featured Deals.
	 *
	 * @return array Post meta.
	 */
	private static function field_style() {
		$deals = Velocity_Deals_Page::queries();

		return $deals['gallery']['meta'];
	}

	/**
	 * A figure of the featured case study: a small label over its value.
	 *
	 * @param int    $card_id Query card post ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $label   Label.
	 * @param string $field   Dynamic field of the value.
	 * @return string
	 */
	private static function figure( $card_id, $key, $label, $field ) {
		return Velocity_Seed::column(
			$card_id,
			'figure-' . $key,
			Velocity_Home::t(
				$card_id,
				'figure-' . $key . '-label',
				$label,
				array(
					'size'   => array( 14, '', '' ),
					'lh'     => 1.1,
					'color'  => '#606060',
					'margin' => array( '0', '', '8', '' ),
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'figure-' . $key . '-value',
				'',
				array(
					'lh'      => 1.47,
					'color'   => '#1b1b1b',
					'margin'  => array( '0', '', '0', '' ),
					'dynamic' => $field,
				)
			),
			array(
				'inQueryBlock' => true,
				'metadata'     => array( 'name' => $label ),
			)
		);
	}

	/**
	 * The featured case study: photo, program, title and summary; under
	 * them its amount, purpose and date, and the button to the story.
	 *
	 * @param int $card_id Query card post ID.
	 * @return string
	 */
	public static function featured_card( $card_id ) {
		$gray = '#666666';

		$details =
			Velocity_Home::t(
				$card_id,
				'featured-program',
				'',
				array(
					'size'    => array( 14, '', '' ),
					'lh'      => 1.1,
					'color'   => $gray,
					'margin'  => array( '0', '', '20', '' ),
					'dynamic' => 'post|vm_case_featured_label',
					'class'   => 'vm-hide-empty',
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'featured-title',
				'',
				array(
					'tag'     => 'h2',
					'class'   => 'vm-lines-3',
					'size'    => array( 56, 40, 30 ),
					'lh'      => 1.1,
					'color'   => Velocity_Seed::color( 'palette3' ),
					'margin'  => array( '0', '', '20', '' ),
					'dynamic' => 'post|post_title',
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'featured-excerpt',
				'',
				array(
					'color'   => $gray,
					'margin'  => array( '0', '', '0', '' ),
					'dynamic' => 'post|post_excerpt',
					'class'   => 'vm-hide-empty vm-lines-2',
				)
			);

		$top = Velocity_Seed::row(
			$card_id,
			'featured-row',
			array(
				Velocity_Seed::column(
					$card_id,
					'featured-photo-col',
					Velocity_Insights::photo( $card_id, 'featured-photo', array( 12, 12, 12, 12 ), 'large' ),
					array(
						'inQueryBlock' => true,
						'metadata'     => array( 'name' => 'Photo' ),
					)
				),
				Velocity_Seed::column(
					$card_id,
					'featured-text-col',
					$details,
					array(
						'id'                => 2,
						'verticalAlignment' => 'middle',
						'inQueryBlock'      => true,
						'metadata'          => array( 'name' => 'Program, title and summary' ),
					)
				),
			),
			array(
				// 581px and 459px of the design's 1088.
				'firstColumnWidth'  => 54.5,
				'secondColumnWidth' => 45.5,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 48, 32, 24 ),
				'verticalAlignment' => 'middle',
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				'padding'           => array( '0', '', '0', '' ),
				'inQueryBlock'      => true,
				'metadata'          => array( 'name' => 'Photo and title' ),
			)
		);

		$figures = Velocity_Seed::column(
			$card_id,
			'featured-figures',
			self::figure( $card_id, 'amount', 'Loan amount', 'post|vm_case_amount' ) . "\n\n" .
			self::figure( $card_id, 'purpose', 'Loan Purpose', 'post|vm_case_purpose' ) . "\n\n" .
			self::figure( $card_id, 'date', 'Date', 'post|vm_case_date' ),
			array(
				'direction'      => array( 'horizontal', '', '' ),
				'wrapContent'    => array( 'wrap', '', '' ),
				'gutter'         => array( 48, 32, 24 ),
				'gutterVariable' => array( 'custom', 'custom', 'custom' ),
				'flexGrow'       => array( 1, '', '' ),
				// vm-figures keeps the three together, under the photo, each as wide as its text.
				'className'      => 'vm-figures',
				'inQueryBlock'   => true,
				'metadata'       => array( 'name' => 'Amount, purpose and date' ),
			)
		);

		// The same two columns as the row above, so the button starts where the
		// title does. The figures sit under the photo, 32px in.
		$bottom = Velocity_Seed::row(
			$card_id,
			'featured-bottom',
			array(
				Velocity_Seed::column(
					$card_id,
					'featured-figures-col',
					$figures,
					array(
						'verticalAlignment' => 'middle',
						'padding'           => array( '0', '0', '0', '32' ),
						'mobilePadding'     => array( '0', '0', '0', '0' ),
						'inQueryBlock'      => true,
						'metadata'          => array( 'name' => 'Figures' ),
					)
				),
				Velocity_Seed::column(
					$card_id,
					'featured-button-col',
					Velocity_Insights::post_button( $card_id, 'featured', 'Read the Full Story' ),
					array(
						'id'                => 2,
						'verticalAlignment' => 'middle',
						'inQueryBlock'      => true,
						'metadata'          => array( 'name' => 'Button' ),
					)
				),
			),
			array(
				'firstColumnWidth'  => 54.5,
				'secondColumnWidth' => 45.5,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 48, 32, 24 ),
				'verticalAlignment' => 'middle',
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				'padding'           => array( '33', '', '0', '' ),
				'inQueryBlock'      => true,
				'metadata'          => array( 'name' => 'Figures and button' ),
			)
		);

		return Velocity_Seed::column(
			$card_id,
			'featured',
			Velocity_Home::t(
				$card_id,
				'featured-label',
				'Featured',
				array(
					'size'   => array( 14, '', '' ),
					'lh'     => 1.1,
					'color'  => '#b04c11',
					'margin' => array( '0', '', '33', '' ),
				)
			) . "\n\n" . $top . "\n\n" . $bottom,
			array(
				'background'    => Velocity_Seed::color( 'palette7' ),
				'borderRadius'  => array( 20, 20, 20, 20 ),
				'padding'       => array( '48', '86', '56', '86' ),
				'tabletPadding' => array( '40', '32', '40', '32' ),
				'mobilePadding' => array( '24', '20', '32', '20' ),
				'inQueryBlock'  => true,
				'metadata'      => array( 'name' => 'Featured' ),
			)
		);
	}

	/**
	 * What goes inside the list's query: the filters, the sort and "Clear"
	 * in their panel, the cards, the message for no results and the pages.
	 * The panel is the one of Featured Deals (vm-filters).
	 *
	 * @param int $query_id Query post ID.
	 * @param int $card_id  Query card post ID.
	 * @return string Inner blocks of the kadence/query block.
	 */
	public static function index_content( $query_id, $card_id ) {
		$controls = array();

		foreach ( self::filters() as $key => $filter ) {
			$controls[] = Velocity_Seed::block(
				'kadence/query-filter',
				array(
					'uniqueID'    => Velocity_Seed::uid( $query_id, 'filter-' . $key ),
					'placeholder' => $filter[2],
					'taxonomy'    => $filter[1],
					'label'       => $filter[0],
					'slug'        => $key,
					// "Hide items when result count is zero": deals and case studies
					// share these taxonomies, and without it a filter offers the
					// terms the other one uses.
					'hideWhenEmptyCount' => true,
				)
			);
		}

		$controls[] = Velocity_Seed::block(
			'kadence/query-sort',
			array(
				'uniqueID'    => Velocity_Seed::uid( $query_id, 'sort' ),
				'sortItems'   => array( 'post_date', 'meta_value' ),
				'meta_value'  => array(
					'textDesc'    => 'Amount (High - Low)',
					'textAsc'     => 'Amount (Low - High)',
					'showDesc'    => true,
					'showAsc'     => true,
					'metaKey'     => 'velocity_case_study_loan_amount_number',
					'metaKeyType' => 'NUMERIC',
				),
				'post_date'   => array(
					'textDesc'    => 'Date (Newest)',
					'textAsc'     => 'Date (Oldest)',
					'showDesc'    => true,
					'showAsc'     => true,
					'metaKey'     => '',
					'metaKeyType' => '',
				),
				'placeholder' => 'Sort By',
			)
		);

		$controls[] = Velocity_Seed::block(
			'kadence/query-filter-reset',
			array(
				'uniqueID'        => Velocity_Seed::uid( $query_id, 'reset' ),
				'text'            => 'Clear',
				// Brand Navy and Deep Rust, as plain colors.
				'color'           => '#010739',
				'background'      => 'rgba(0,0,0,0)',
				'colorHover'      => '#bb5013',
				'backgroundHover' => 'rgba(0,0,0,0)',
			)
		);

		$panel = Velocity_Seed::column(
			$query_id,
			'controls',
			implode( "\n\n", $controls ),
			array(
				'background'        => '#f8e8de',
				'borderStyle'       => Velocity_Home::border( Velocity_Seed::color( 'palette1' ) ),
				'borderRadius'      => array( 19, 19, 19, 19 ),
				'padding'           => array( '24', '28', '24', '28' ),
				'mobilePadding'     => array( '20', '20', '20', '20' ),
				'direction'         => array( 'horizontal', '', 'vertical' ),
				'wrapContent'       => array( 'wrap', '', '' ),
				'verticalAlignment' => 'bottom',
				'gutter'            => array( 16, '', 12 ),
				'gutterVariable'    => array( 'custom', 'custom', 'custom' ),
				'rowGapVariable'    => array( '', '', 'custom' ),
				'rowGap'            => array( '', '', 12 ),
				'margin'            => array( '0', '', '32', '' ),
				// Shown once there are more than ten case studies. See
				// inc/case-studies.php and style.css.
				'className'         => 'vm-filters',
				'metadata'          => array( 'name' => 'Filters and sort' ),
			)
		);

		return $panel . "\n\n" .
			Velocity_Seed::block(
				'kadence/query-card',
				array(
					'uniqueID' => Velocity_Seed::uid( $query_id, 'card' ),
					'id'       => (int) $card_id,
				)
			) . "\n\n" .
			Velocity_Deals_Page::no_results( $query_id, 'No case studies match those filters.' ) . "\n\n" .
			Velocity_Deals_Page::pagination( $query_id, 'block' );
	}

	/**
	 * A section that holds one Kadence query.
	 *
	 * @param int    $post_id Element ID.
	 * @param string $key     Key for the unique IDs.
	 * @param int    $query   Query post ID.
	 * @param string $name    Name shown in the editor's list view.
	 * @param array  $attrs   Row settings.
	 * @param string $before  Blocks above the query.
	 * @return string
	 */
	private static function query_section( $post_id, $key, $query, $name, $attrs, $before = '' ) {
		return Velocity_Home::section(
			$post_id,
			$key,
			( $before ? $before . "\n\n" : '' ) .
			Velocity_Seed::block(
				'kadence/query',
				array(
					'uniqueID' => Velocity_Seed::uid( $post_id, $key . '-query' ),
					'id'       => (int) $query,
				)
			),
			$name,
			$attrs
		);
	}

	/**
	 * Every section of the list, in page order: hero, the featured case
	 * study, the list, and the call to action.
	 *
	 * @param int   $post_id Element ID.
	 * @param int[] $queries Query post IDs by key.
	 * @return string
	 */
	public static function archive( $post_id, $queries ) {
		$white = Velocity_Seed::color( 'palette9' );
		$navy  = Velocity_Seed::color( 'palette3' );

		$hero = Velocity_Home::section(
			$post_id,
			'hero',
			Velocity_Seed::row(
				$post_id,
				'hero-inner',
				array(
					Velocity_Seed::column(
						$post_id,
						'hero-col',
						Velocity_Home::t(
							$post_id,
							'hero-title',
							'Deals Brokers Are Closing With Velocity',
							array(
								'tag'    => 'h1',
								'align'  => 'center',
								'color'  => $white,
								'margin' => array( '0', '', '24', '' ),
								'class'  => 'is-style-vm-reveal-lines',
							)
						) . "\n\n" .
						Velocity_Home::subtitle(
							$post_id,
							'hero',
							'Learn how brokers like you are using Velocity to find the right program for their clients and get their deals funded across every property type and program.',
							array(
								'align' => 'center',
								'color' => $white,
							)
						)
					),
				),
				array(
					'maxWidth' => 998,
					'padding'  => array( '0', '', '0', '' ),
					'metadata' => array( 'name' => 'Title and intro' ),
				)
			),
			'Hero',
			array(
				'bgColor'       => $navy,
				'padding'       => array( '69', '', '60', '' ),
				'mobilePadding' => array( '48', '', '32', '' ),
			)
		);

		$featured = self::query_section(
			$post_id,
			'featured',
			$queries['featured'],
			'Featured case study',
			array(
				'bgColor'       => $navy,
				'padding'       => array( '0', '', '64', '' ),
				'mobilePadding' => array( '0', '', '48', '' ),
				// Hides while no case study is featured.
				'className'     => 'vm-needs-items',
			)
		);

		$index = self::query_section(
			$post_id,
			'index',
			$queries['index'],
			'Case studies',
			array(
				'anchor'        => 'case-studies',
				'padding'       => array( '64', '', '64', '' ),
				'mobilePadding' => array( '48', '', '48', '' ),
			)
		);

		$close = Velocity_Home::cta_band(
			$post_id,
			'Ready to Close a Deal of Your Own?',
			array( array( 'Become a Partner', home_url( '/become-a-partner/' ) ) ),
			1280,
			array(
				'padding'       => array( '32', '', '96', '' ),
				'mobilePadding' => array( '16', '', '64', '' ),
			)
		);

		return implode( "\n\n", array( $hero, $featured, $index, $close ) );
	}

	/**
	 * Every section of the page of a case study, in page order.
	 *
	 * - Top: the way back, the photo, and beside it the program (a link to
	 *   its page), the title, the summary, who closed the deal and the links
	 *   to share it.
	 * - The strip "Deal at a glance", and the card of the account executive,
	 *   both printed by inc/case-studies.php from the case study's fields.
	 * - The case study itself, as written in the editor. vm-article sets the
	 *   look of its blocks, as in an article of News and Insights.
	 * - The links to share again, and "You may also like".
	 *
	 * @param int   $post_id Element ID.
	 * @param int[] $queries Query post IDs by key.
	 * @return string
	 */
	public static function single( $post_id, $queries ) {
		$uid = Velocity_Seed::uid( $post_id, 'top-photo' );

		$photo = Velocity_Seed::block(
			'kadence/image',
			array(
				'ratio'           => 'land169',
				'useRatio'        => true,
				'linkDestination' => 'none',
				'uniqueID'        => $uid,
				'marginDesktop'   => array( '0', '', '0', '' ),
				'borderRadius'    => array( 12, 12, 12, 12 ),
				'kadenceDynamic'  => array(
					'url' => array(
						'enable'   => true,
						'source'   => '',
						'field'    => 'post|post_featured_image',
						'custom'   => '',
						'para'     => '',
						'before'   => 'large',
						'after'    => '',
						'fallback' => '',
					),
				),
			),
			sprintf(
				'<figure class="wp-block-kadence-image kb-image%1$s kb-image-is-ratio-size"><div class="kb-is-ratio-image kb-image-ratio-land169"><img src="%2$s" alt="" class="kb-img"/></div></figure>',
				$uid,
				esc_url( includes_url( 'images/blank.gif' ) )
			)
		);

		$details =
			"<!-- wp:shortcode -->\n[vm_case_program]\n<!-- /wp:shortcode -->\n\n" .
			Velocity_Home::t(
				$post_id,
				'top-title',
				'',
				array(
					'tag'     => 'h1',
					'size'    => array( 46, 36, 30 ),
					'lh'      => 1.1,
					'color'   => Velocity_Seed::color( 'palette3' ),
					'margin'  => array( '0', '', '20', '' ),
					'dynamic' => 'post|post_title',
				)
			) . "\n\n" .
			Velocity_Home::t(
				$post_id,
				'top-excerpt',
				'',
				array(
					'margin'  => array( '0', '', '20', '' ),
					// Only a summary that was written, as in an article.
					'dynamic' => 'post|vm_insight_excerpt',
					'class'   => 'vm-hide-empty',
				)
			) . "\n\n" .
			Velocity_Home::t(
				$post_id,
				'top-closed',
				'',
				array(
					'size'       => array( 14, '', '' ),
					'weight'     => '600',
					'lh'         => 1.5,
					'color'      => '#b04c11',
					'background' => '#fce7d3',
					'padding'    => array( '4', '8', '4', '8' ),
					'radius'     => array( 3, 3, 3, 3 ),
					'margin'     => array( '0', '', '20', '' ),
					'dynamic'    => 'post|vm_case_closed_by',
					'class'      => 'vm-tag vm-hide-empty',
				)
			) . "\n\n" .
			"<!-- wp:shortcode -->\n[vm_insight_share]\n<!-- /wp:shortcode -->";

		$back = Velocity_Home::t(
			$post_id,
			'top-back',
			'<a href="' . esc_url( home_url( '/case-studies/' ) ) . '">&lt; Case Studies</a>',
			array(
				'weight'     => '600',
				'lh'         => 1.1,
				'link'       => Velocity_Seed::color( 'palette1' ),
				'link_hover' => Velocity_Seed::color( 'palette2' ),
				'margin'     => array( '0', '', '29', '' ),
			)
		);

		$top = Velocity_Home::section(
			$post_id,
			'top',
			Velocity_Seed::row(
				$post_id,
				'top-inner',
				array(
					Velocity_Seed::column(
						$post_id,
						'top-inner-col',
						$back . "\n\n" .
						Velocity_Seed::row(
							$post_id,
							'top-row',
							array(
								Velocity_Seed::column( $post_id, 'top-photo-col', $photo, array( 'metadata' => array( 'name' => 'Photo' ) ) ),
								Velocity_Seed::column(
									$post_id,
									'top-text-col',
									$details,
									array(
										'id'                => 2,
										'verticalAlignment' => 'middle',
										'metadata'          => array( 'name' => 'Program, title, summary and who closed it' ),
									)
								),
							),
							array(
								'firstColumnWidth'  => 60,
								'secondColumnWidth' => 40,
								'columnGutter'      => 'custom',
								'customGutter'      => array( 48, 32, 24 ),
								'verticalAlignment' => 'middle',
								'tabletLayout'      => 'row',
								'mobileLayout'      => 'row',
								'padding'           => array( '0', '', '0', '' ),
								'metadata'          => array( 'name' => 'Photo and title' ),
							)
						)
					),
				),
				array(
					'maxWidth' => 1088,
					'padding'  => array( '0', '', '0', '' ),
					'metadata' => array( 'name' => 'Back link, photo and title' ),
				)
			),
			'Top of the case study',
			array(
				'padding'       => array( '48', '', '48', '' ),
				'mobilePadding' => array( '32', '', '32', '' ),
			)
		);

		$body = Velocity_Home::section(
			$post_id,
			'body',
			// The strip runs the full width of the screen. See style.css, "vm-glance".
			"<!-- wp:shortcode -->\n[vm_case_glance]\n<!-- /wp:shortcode -->\n\n" .
			Velocity_Seed::row(
				$post_id,
				'body-inner',
				array(
					Velocity_Seed::column(
						$post_id,
						'body-col',
						"<!-- wp:shortcode -->\n[vm_case_ae]\n<!-- /wp:shortcode -->\n\n" .
						Velocity_Seed::block(
							'kadence/dynamichtml',
							array(
								'uniqueID' => Velocity_Seed::uid( $post_id, 'body-content' ),
								'field'    => 'post|post_content',
								'metadata' => array( 'name' => 'The case study' ),
							)
						),
						array( 'className' => 'vm-article vm-case' )
					),
				),
				array(
					'maxWidth' => 748,
					'padding'  => array( '70', '', '0', '' ),
					'metadata' => array( 'name' => 'Case study column' ),
				)
			),
			'Case study',
			array(
				'padding'       => array( '0', '', '32', '' ),
				'mobilePadding' => array( '0', '', '24', '' ),
			)
		);

		$share = Velocity_Home::section(
			$post_id,
			'share',
			Velocity_Seed::row(
				$post_id,
				'share-inner',
				array(
					Velocity_Seed::column(
						$post_id,
						'share-col',
						"<!-- wp:shortcode -->\n[vm_insight_share]\n<!-- /wp:shortcode -->",
						array( 'className' => 'vm-article-end' )
					),
				),
				array(
					'maxWidth' => 762,
					'padding'  => array( '0', '', '0', '' ),
					'metadata' => array( 'name' => 'Share' ),
				)
			),
			'Share',
			array(
				'padding'       => array( '18', '', '80', '' ),
				'mobilePadding' => array( '8', '', '56', '' ),
			)
		);

		$related = self::query_section(
			$post_id,
			'related',
			$queries['related'],
			'You may also like',
			array(
				'padding'       => array( '0', '', '64', '' ),
				'mobilePadding' => array( '0', '', '48', '' ),
				'className'     => 'vm-needs-items',
			),
			Velocity_Home::t(
				$post_id,
				'related-title',
				'You may also like',
				array(
					'tag'    => 'h2',
					'align'  => 'center',
					'margin' => array( '0', '', '30', '' ),
				)
			)
		);

		return implode( "\n\n", array( $top, $body, $share, $related ) );
	}
}
