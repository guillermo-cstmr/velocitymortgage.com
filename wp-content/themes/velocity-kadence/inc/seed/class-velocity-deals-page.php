<?php
/**
 * Builds the Featured Deals page as block content.
 *
 * Like the other pages, this only produces the first version of the page.
 * After `wp velocity seed-deals-page` the page is edited in the block
 * editor. What a block setting can express is set on the block.
 *
 * The deals are the posts under Properties. The gallery is a Kadence query
 * with Kadence's own filter, sort, reset and pagination blocks, which
 * update the list without reloading the page. The stories are a second
 * query, of case studies.
 *
 * Figma: page "Resources", section "Featured Deals". Copy: the page's copy
 * document (V1).
 *
 * Kept compatible with PHP 7.4, see class-velocity-cli.php.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Featured Deals page seed content.
 */
class Velocity_Deals_Page {

	/**
	 * Slug of the page this class fills. It is the address the list of
	 * deals always had.
	 */
	const SLUG = 'featured-investment-property-loans';

	/**
	 * The filters of the gallery: label, taxonomy, text of the empty choice.
	 * Region joins them once it has terms in use: run the command again with
	 * --force then, or add a Filter block for it to the query in the editor.
	 *
	 * @return array[] By key.
	 */
	private static function filters() {
		$filters = array(
			'type'    => array( 'Property Type', 'velocity-property-type', 'All types' ),
			'purpose' => array( 'Loan Purpose', 'velocity-property-purpose', 'All purposes' ),
			'program' => array( 'Loan Program', 'velocity-property-program', 'All programs' ),
			'region'  => array( 'Region', 'velocity-property-region', 'All regions' ),
		);

		// Region has no terms until Velocity sends the list of regions. A filter
		// with nothing to choose prints its label alone, so it waits until then.
		$regions = get_terms(
			array(
				'taxonomy'   => 'velocity-property-region',
				'hide_empty' => true,
				'number'     => 1,
				'fields'     => 'ids',
			)
		);
		if ( ! $regions || is_wp_error( $regions ) ) {
			unset( $filters['region'] );
		}

		return $filters;
	}

	/**
	 * What Kadence keeps about a query's filters, next to the query: one
	 * entry per filter block, with a number worked out from its settings.
	 *
	 * The block editor writes this when a query is saved. The numbers below
	 * are the ones it wrote for the four filters of the gallery on
	 * 2026-11-17. They depend on the taxonomy alone, so they are the same on
	 * every environment, and the command can create the query with its
	 * filters working. Kadence builds its index of the deals from them the
	 * first time the page is opened. If a filter's taxonomy changes here,
	 * open the query in the editor and save it once: the editor writes the
	 * new number.
	 *
	 * @param int $query_id Query post ID.
	 * @return array[]
	 */
	public static function facets( $query_id ) {
		$hashes = array(
			'type'    => 1710083716,
			'purpose' => 451343554,
			'program' => 3635205064,
			'region'  => 3888497028,
		);

		$facets = array();
		foreach ( self::filters() as $key => $filter ) {
			$facets[] = array(
				'hash'       => $hashes[ $key ],
				// The settings, in the order and with the empty values the editor writes.
				'attributes' => wp_json_encode(
					array(
						'source'        => 'taxonomy',
						'fieldType'     => 'post_field',
						'taxonomy'      => $filter[1],
						'post_field'    => 'post_type',
						'include'       => array(),
						'exclude'       => array(),
						'type'          => 'query-filter',
						'customField'   => '',
						'customMetaKey' => '',
						'uniqueID'      => Velocity_Seed::uid( $query_id, 'filter-' . $key ),
						'slug'          => $key,
					)
				),
			);
		}

		return $facets;
	}

	/**
	 * The lists of this page: the gallery of deals, with its filters, and
	 * the three latest case studies.
	 *
	 * @return array[] By key, as Velocity_Seed::query_meta() takes them.
	 */
	public static function queries() {
		return array(
			'gallery' => array(
				'title'      => 'Featured Deals: gallery',
				'post_type'  => 'velocity-property',
				'order_by'   => 'date',
				'order'      => 'desc',
				'per_page'   => '12',
				'columns'    => array( '3', '2', '1' ),
				'column_gap' => array( '24', '20', '20' ),
				'row_gap'    => array( '24', '20', '20' ),
				'card'       => array( __CLASS__, 'deal_card' ),
				'content'    => array( __CLASS__, 'gallery_content' ),
				'facets'     => array( __CLASS__, 'facets' ),
				// The look of the filter fields: settings of the query, under Style.
				'meta'       => array(
					'_kad_query_fieldBorderRadius' => array( '10', '10', '10', '10' ),
					'_kad_query_fieldBorderStyle'  => array(
						'top'    => array( 'rgba(211,112,49,0.38)', '', 1 ),
						'right'  => array( 'rgba(211,112,49,0.38)', '', 1 ),
						'bottom' => array( 'rgba(211,112,49,0.38)', '', 1 ),
						'left'   => array( 'rgba(211,112,49,0.38)', '', 1 ),
						'unit'   => 'px',
					),
					'_kad_query_labelFont'         => array(
						'color'         => 'palette3',
						'size'          => array( '14', '', '' ),
						'sizeType'      => 'px',
						'lineHeight'    => array( '', '', '' ),
						'lineType'      => '',
						'letterSpacing' => array( '', '', '' ),
						'letterType'    => 'px',
						'textTransform' => '',
						'family'        => '',
						'google'        => false,
						'style'         => '',
						'weight'        => '400',
						'variant'       => '',
						'subset'        => '',
						'loadGoogle'    => true,
						'padding'       => array( '', '', '', '' ),
						'margin'        => array( '', '', '', '' ),
					),
					'_kad_query_inputFont'         => array(
						'color'         => 'palette3',
						'size'          => array( '14', '', '' ),
						'sizeType'      => 'px',
						'lineHeight'    => array( '', '', '' ),
						'lineType'      => '',
						'letterSpacing' => array( '', '', '' ),
						'letterType'    => 'px',
						'textTransform' => '',
						'family'        => '',
						'google'        => false,
						'style'         => '',
						'weight'        => '400',
						'variant'       => '',
						'subset'        => '',
						'loadGoogle'    => true,
						'padding'       => array( '', '', '', '' ),
						'margin'        => array( '', '', '', '' ),
					),
				),
			),
			'stories' => array(
				'title'      => 'Featured Deals: case studies',
				'post_type'  => 'velocity-case-study',
				'order_by'   => 'date',
				'order'      => 'desc',
				'per_page'   => '3',
				'columns'    => array( '3', '2', '1' ),
				'column_gap' => array( '24', '20', '20' ),
				'row_gap'    => array( '24', '20', '20' ),
				'card'       => array( __CLASS__, 'story_card' ),
			),
		);
	}

	/**
	 * A photo that reads the post being listed, in a fixed shape.
	 *
	 * @param int    $card_id Query card post ID.
	 * @param string $key     Key for the unique ID.
	 * @return string
	 */
	private static function photo( $card_id, $key ) {
		$uid = Velocity_Seed::uid( $card_id, $key );

		return Velocity_Seed::block(
			'kadence/image',
			array(
				// A fixed shape, two wide by one tall, whatever the photo's own.
				'ratio'           => 'land21',
				'useRatio'        => true,
				'linkDestination' => 'none',
				'uniqueID'        => $uid,
				'marginDesktop'   => array( '0', '', '20', '' ),
				'borderRadius'    => array( 8, 8, 8, 8 ),
				'inQueryBlock'    => true,
				'kadenceDynamic'  => array(
					'url' => array(
						'enable'   => true,
						'source'   => '',
						'field'    => 'post|post_featured_image',
						'custom'   => '',
						'para'     => '',
						// The image size.
						'before'   => 'medium_large',
						'after'    => '',
						'fallback' => '',
					),
				),
			),
			sprintf(
				'<figure class="wp-block-kadence-image kb-image%1$s kb-image-is-ratio-size"><div class="kb-is-ratio-image kb-image-ratio-land21"><img src="%2$s" alt="" class="kb-img"/></div></figure>',
				$uid,
				esc_url( includes_url( 'images/blank.gif' ) )
			)
		);
	}

	/**
	 * Settings of a card of the two lists: the light card with a shadow.
	 *
	 * @param string $name   Name shown in the editor's list view.
	 * @param bool   $linked Whether the card links to its post.
	 * @return array
	 */
	private static function card( $name, $linked = true ) {
		return array_merge(
			Velocity_Home::card(
				array_filter(
					array(
						'background' => Velocity_Seed::color( 'palette8' ),
						// The whole card is a link only where there is a page to go to.
						'className'  => $linked ? 'vm-card-link' : '',
						'metadata'   => array( 'name' => $name ),
					)
				)
			),
			array(
				'inQueryBlock'   => true,
				// The gaps are each text's own margin, so a text left out leaves no hole.
				'rowGapVariable' => array( '', '', '' ),
				'rowGap'         => array( '', '', '' ),
			)
		);
	}

	/**
	 * The card of a deal in the gallery: photo, closing time, amount and
	 * place, program and purpose, and account executive.
	 *
	 * Every part reads the deal being listed. The texts are fields of the
	 * theme, under Velocity in the dynamic content list. A text with no
	 * value is left out. The card links nowhere: a deal has no page of
	 * its own.
	 *
	 * @param int $card_id Query card post ID.
	 * @return string
	 */
	public static function deal_card( $card_id ) {
		$rust = Velocity_Seed::color( 'palette2' );

		return Velocity_Seed::column(
			$card_id,
			'deal',
			self::photo( $card_id, 'photo' ) . "\n\n" .
			// The closing time, as a small label with the bolt. Left out when empty.
			Velocity_Home::t(
				$card_id,
				'deal-closed',
				'',
				array(
					'size'       => array( 14, '', '' ),
					'weight'     => '600',
					'lh'         => 1.5,
					'color'      => $rust,
					'background' => '#ffcea1',
					'padding'    => array( '4', '8', '4', '8' ),
					'radius'     => array( 5, 5, 5, 5 ),
					'icon'       => 'fe_zap',
					'margin'     => array( '0', '', '8', '' ),
					'dynamic'    => 'post|vm_deal_closed',
					'class'      => 'vm-tag vm-hide-empty',
				)
			) . "\n\n" .
			Velocity_Home::card_title(
				$card_id,
				'deal',
				'',
				array(
					'color'   => Velocity_Seed::color( 'palette3' ),
					'margin'  => array( '0', '', '8', '' ),
					// No link: a deal has no page of its own.
					'dynamic' => 'post|vm_deal_headline',
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'deal-terms',
				'',
				array(
					'size'    => array( 14, '', '' ),
					'lh'      => 1.1,
					'color'   => $rust,
					'margin'  => array( '0', '', '12', '' ),
					'dynamic' => 'post|vm_deal_terms',
					'class'   => 'vm-hide-empty',
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'deal-ae',
				'',
				array(
					'margin'  => array( '0', '', '0', '' ),
					'dynamic' => 'post|vm_deal_ae',
					'class'   => 'vm-hide-empty',
				)
			),
			self::card( 'Deal', false )
		);
	}

	/**
	 * The card of a case study: photo, program and purpose, title, summary,
	 * amount and date, and the link to the story.
	 *
	 * @param int $card_id Query card post ID.
	 * @return string
	 */
	public static function story_card( $card_id ) {
		$rule = array( Velocity_Seed::color( 'palette7' ), '', 1 );
		$none = array( '', '', '' );

		$figures = Velocity_Seed::column(
			$card_id,
			'story-figures',
			Velocity_Seed::column(
				$card_id,
				'story-amount-col',
				Velocity_Home::t(
					$card_id,
					'story-amount',
					'',
					array(
						'size'    => array( 14, '', '' ),
						'margin'  => array( '0', '', '0', '' ),
						'dynamic' => 'post|vm_case_amount',
					)
				),
				array(
					'flexGrow'     => array( 1, '', '' ),
					'inQueryBlock' => true,
					'metadata'     => array( 'name' => 'Amount' ),
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'story-date',
				'',
				array(
					'size'    => array( 14, '', '' ),
					'margin'  => array( '0', '', '0', '' ),
					'dynamic' => 'post|vm_case_date',
				)
			),
			array(
				'direction'    => array( 'horizontal', '', '' ),
				'padding'      => array( '16', '', '0', '' ),
				'margin'       => array( '16', '', '12', '' ),
				// A rule above the figures: a top border only.
				'borderStyle'  => array(
					array(
						'top'    => $rule,
						'right'  => $none,
						'bottom' => $none,
						'left'   => $none,
						'unit'   => 'px',
					),
				),
				'inQueryBlock' => true,
				'metadata'     => array( 'name' => 'Amount and date' ),
			)
		);

		return Velocity_Seed::column(
			$card_id,
			'story',
			self::photo( $card_id, 'photo' ) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'story-terms',
				'',
				array(
					'size'       => array( 12, '', '' ),
					'weight'     => '600',
					'lh'         => 1.3,
					'color'      => '#b04c11',
					'background' => '#fde3cc',
					'padding'    => array( '3', '8', '3', '8' ),
					'radius'     => array( 4, 4, 4, 4 ),
					'margin'     => array( '0', '', '8', '' ),
					'dynamic'    => 'post|vm_case_terms',
					'class'      => 'vm-tag vm-hide-empty',
				)
			) . "\n\n" .
			Velocity_Home::card_title(
				$card_id,
				'story',
				'',
				array(
					'color'   => Velocity_Seed::color( 'palette3' ),
					'margin'  => array( '0', '', '8', '' ),
					'dynamic' => 'post|post_title',
					'to_post' => true,
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'story-excerpt',
				'',
				array(
					'margin'  => array( '0', '', '0', '' ),
					'dynamic' => 'post|post_excerpt',
					'class'   => 'vm-hide-empty',
				)
			) . "\n\n" .
			$figures . "\n\n" .
			// Looks like a link. The card's title already links the whole card.
			Velocity_Home::t(
				$card_id,
				'story-more',
				'<u>Read the Full Story</u>',
				array(
					'weight' => '600',
					'margin' => array( '0', '', '0', '' ),
				)
			),
			self::card( 'Case study' )
		);
	}

	/**
	 * What goes inside the gallery's query: the filters, the cards, the
	 * pages and the message for no results.
	 *
	 * The filters, the sort, the reset and the pagination are Kadence's own
	 * blocks. They update the list without reloading the page.
	 *
	 * @param int $query_id Query post ID.
	 * @param int $card_id  Query card post ID.
	 * @return string Inner blocks of the kadence/query block.
	 */
	public static function gallery_content( $query_id, $card_id ) {
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

		// Sorting by amount reads the plain number the plugin keeps beside each
		// amount. Sorting by date reads the date the deal was published.
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
					'metaKey'     => 'velocity_property_loan_amount_number',
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
				// A text link, not a filled button. Kadence's query blocks take
				// plain colors, not the design tokens: Brand Navy and Deep Rust.
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
				// On phones the fields stack, with this space between them.
				'rowGapVariable'    => array( '', '', 'custom' ),
				'rowGap'            => array( '', '', 12 ),
				'margin'            => array( '0', '', '32', '' ),
				// Folds into one button on phones, and turns the sort into a pill with
				// its own menu. See
				// inc/filters.php and style.css.
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
			self::no_results( $query_id, 'No deals match those filters.' ) . "\n\n" .
			self::pagination( $query_id );
	}

	/**
	 * The message of a list with nothing to show, and its "Clear filters".
	 *
	 * @param int    $query_id Query post ID.
	 * @param string $text     The message.
	 * @return string
	 */
	public static function no_results( $query_id, $text ) {
		return
		Velocity_Seed::block(
			'kadence/query-noresults',
			array( 'uniqueID' => Velocity_Seed::uid( $query_id, 'noresults' ) ),
			// The Filter Reset block is only allowed in a query or a Section,
			// so a Section holds the message and its own "Clear filters".
			Velocity_Seed::column(
				$query_id,
				'noresults-box',
				Velocity_Home::t(
					$query_id,
					'noresults-text',
					$text,
					array(
						'align'  => 'center',
						'margin' => array( '0', '', '8', '' ),
					)
				) . "\n\n" .
				Velocity_Seed::block(
					'kadence/query-filter-reset',
					array(
						'uniqueID'        => Velocity_Seed::uid( $query_id, 'noresults-reset' ),
						'text'            => 'Clear filters',
						'hAlign'          => 'center',
						// Brand Navy and Deep Rust, as plain colors.
						'color'           => '#010739',
						'background'      => 'rgba(0,0,0,0)',
						'colorHover'      => '#bb5013',
						'backgroundHover' => 'rgba(0,0,0,0)',
					)
				),
				array(
					'padding'   => array( '32', '', '32', '' ),
					'textAlign' => array( 'center', '', '' ),
					'metadata'  => array( 'name' => 'No results' ),
				)
			)
		);
	}

	/**
	 * The pages of a list: the numbers between two arrows.
	 *
	 * @param int    $query_id Query post ID.
	 * @param string $scroll   Where a new page scrolls to: "card", the cards (Kadence's
	 *                         own choice), or "block", the top of the query, filters included.
	 * @return string
	 */
	public static function pagination( $query_id, $scroll = 'card' ) {
		return
		Velocity_Seed::block(
			'kadence/query-pagination',
			array_merge(
			'card' === $scroll ? array() : array( 'scrollTarget' => $scroll ),
			array(
				'uniqueID'          => Velocity_Seed::uid( $query_id, 'pagination' ),
				// Arrows for previous and next. Their circles are in style.css.
				'buttonContentType' => 'icon',
				'nextIcon'          => 'fe_arrowRight',
				'previousIcon'      => 'fe_arrowLeft',
				// Gray, Deep Rust and Brand Navy, as plain colors.
				'color'             => '#666666',
				'colorHover'        => '#bb5013',
				'colorActive'       => '#010739',
				'background'        => 'rgba(0,0,0,0)',
				'backgroundHover'   => 'rgba(0,0,0,0)',
				'backgroundActive'  => 'rgba(0,0,0,0)',
				'margin'            => array( '48', '', '0', '' ),
				'typography'        => array(
					array(
						'size'          => array( 30, '', 24 ),
						'sizeType'      => 'px',
						'lineHeight'    => array( '', '', '' ),
						'lineType'      => '',
						'letterSpacing' => '',
						'family'        => '',
						'google'        => '',
						'style'         => '',
						'weight'        => '',
						'variant'       => '',
						'subset'        => '',
						'loadGoogle'    => true,
						'padding'       => array( 'xxs', 'xs', 'xxs', 'xs' ),
						'marginTop'     => 8,
						'color'         => '',
						'background'    => '',
						'border'        => array( '', '', '', '' ),
						'borderRadius'  => array( '', '', '', '' ),
						'borderWidth'   => array( '', '', '', '' ),
						'colorHover'    => '',
						'backgroundHover' => '',
						'borderHover'   => array( '', '', '', '' ),
						'colorActive'   => '',
						'backgroundActive' => '',
						'borderActive'  => array( '', '', '', '' ),
						'textTransform' => '',
						'paddingTablet' => array( '', '', '', '' ),
						'paddingMobile' => array( '', '', '', '' ),
						'paddingType'   => 'px',
					),
				),
			)
			)
		);
	}

	/**
	 * Hero: title and intro, centered on navy.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function hero( $post_id ) {
		$white = Velocity_Seed::color( 'palette9' );

		$inner = Velocity_Seed::row(
			$post_id,
			'hero-inner',
			array(
				Velocity_Seed::column(
					$post_id,
					'hero-col',
					Velocity_Home::t(
						$post_id,
						'hero-title',
						'Funded Deals Across Every Program and Market',
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
						'Velocity works with brokers to fund deals of every size and shape across every market. Here’s a look at what we’ve helped close.',
						array(
							'align' => 'center',
							'color' => $white,
							'size'  => array( 16, '', '' ),
						)
					)
				),
			),
			array(
				'maxWidth' => 980,
				'padding'  => array( '0', '', '0', '' ),
				'metadata' => array( 'name' => 'Title and intro' ),
			)
		);

		return Velocity_Home::section(
			$post_id,
			'hero',
			$inner,
			'Hero',
			array(
				'bgColor'       => Velocity_Seed::color( 'palette3' ),
				'padding'       => array( '72', '', '64', '' ),
				'mobilePadding' => array( '48', '', '48', '' ),
			)
		);
	}

	/**
	 * Trust strip: five figures on the orange band.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function stats( $post_id ) {
		return Velocity_Home::stats(
			$post_id,
			array(
				array( '$2B+', 'Annual lending volume' ),
				array( '$13.8B+', 'Funded since 2004' ),
				array( '3,000+', 'Approved broker partners' ),
				array( '20+ years', 'Investor property lending' ),
				array( 'NYSE: VEL', 'Publicly traded parent' ),
			),
			// Five figures need more room than the three of the home page.
			array( 'maxWidth' => 1160 )
		);
	}

	/**
	 * The gallery of funded deals: header and the query with its filters.
	 *
	 * @param int   $post_id Page ID.
	 * @param int[] $queries Query post IDs by key.
	 * @return string
	 */
	public static function gallery( $post_id, $queries ) {
		return Velocity_Home::section(
			$post_id,
			'gallery',
			Velocity_Home::section_header(
				$post_id,
				'gallery',
				'Browse the Deals That We’ve Funded',
				'You’ll find deals that look like the one on your desk. Filter by program, purpose, property type, or date, or sort by loan amount or when it closed.',
				920
			) . "\n\n" .
			Velocity_Seed::block(
				'kadence/query',
				array(
					'uniqueID' => Velocity_Seed::uid( $post_id, 'gallery-query' ),
					'id'       => (int) $queries['gallery'],
				)
			),
			'Gallery of funded deals',
			array(
				'anchor'        => 'deals',
				'padding'       => array( '88', '', '88', '' ),
				'mobilePadding' => array( '56', '', '56', '' ),
			)
		);
	}

	/**
	 * The stories behind the deals: the three latest case studies and the
	 * button to all of them, on the peach band.
	 *
	 * vm-needs-items hides the whole section while no case study is
	 * published. See style.css.
	 *
	 * @param int   $post_id Page ID.
	 * @param int[] $queries Query post IDs by key.
	 * @return string
	 */
	public static function stories( $post_id, $queries ) {
		return Velocity_Home::section(
			$post_id,
			'stories',
			Velocity_Home::section_header(
				$post_id,
				'stories',
				'The Stories Behind the Deals',
				'In each of these stories, see how Velocity worked with brokers to find the right program, clear away obstacles, and fund the loan for their client.',
				920
			) . "\n\n" .
			Velocity_Seed::block(
				'kadence/query',
				array(
					'uniqueID' => Velocity_Seed::uid( $post_id, 'stories-query' ),
					'id'       => (int) $queries['stories'],
				)
			) . "\n\n" .
			Velocity_Seed::column(
				$post_id,
				'stories-action',
				Velocity_Home::buttons( $post_id, 'stories', array( array( 'See All Case Studies', home_url( '/case-studies/' ) ) ) ),
				array(
					'padding'  => array( '56', '', '0', '' ),
					'metadata' => array( 'name' => 'Button' ),
				)
			),
			'The stories behind the deals',
			array(
				'bgColor'       => '#f8e8de',
				'className'     => 'vm-needs-items',
				'padding'       => array( '80', '', '80', '' ),
				'mobilePadding' => array( '56', '', '48', '' ),
			)
		);
	}

	/**
	 * The call to action band that closes the page.
	 *
	 * Its top sits on the peach of the section above: the background of its
	 * own section is a gradient with a hard stop, peach then white. While
	 * the stories section is hidden the band sits on white, so the peach
	 * goes too: vm-after-items. See style.css.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function cta( $post_id ) {
		return Velocity_Home::cta_band(
			$post_id,
			'Ready to Fund Your Next Deal?',
			array( array( 'Become a Partner', home_url( '/become-a-partner/' ) ) ),
			1280,
			array(
				'backgroundSettingTab' => 'gradient',
				'gradient'             => 'linear-gradient(180deg,rgb(248,232,222) 136px,rgb(255,255,255) 136px)',
				'className'            => 'vm-after-items',
				'padding'              => array( '0', '', '32', '' ),
				'mobilePadding'        => array( '0', '', '24', '' ),
			)
		);
	}

	/**
	 * Every section, in page order.
	 *
	 * @param int   $post_id Page ID.
	 * @param int[] $queries Query post IDs by key.
	 * @return string
	 */
	public static function content( $post_id, $queries ) {
		return implode(
			"\n\n",
			array(
				self::hero( $post_id ),
				self::stats( $post_id ),
				self::gallery( $post_id, $queries ),
				self::stories( $post_id, $queries ),
				self::cta( $post_id ),
			)
		);
	}
}
