<?php
/**
 * Builds News and Insights as block content: the page that lists the
 * articles, at /insights/, and the page of an article.
 *
 * That address is the list of a post type (Insights, from the plugin
 * Velocity Insights), not a page. Its content is a Kadence Element,
 * "News and Insights" (Kadence > Elements), that takes the place of the
 * theme's own list. Like the pages, `wp velocity seed-insights` only
 * produces its first version: after that it is edited in the block editor.
 *
 * The articles come from two Kadence queries with one card each. What a
 * query has no setting for is in inc/insights.php: which article is the
 * featured one, and keeping it out of the list under it.
 *
 * Figma: page "Resources", section "Velocity - News and Insights".
 *
 * Kept compatible with PHP 7.4, see class-velocity-cli.php.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * News and Insights seed content.
 */
class Velocity_Insights {

	/**
	 * The post type and its taxonomy, from the plugin Velocity Insights.
	 */
	const POST_TYPE = 'insight';
	const TAXONOMY  = 'insight_topic';

	/**
	 * What Kadence keeps about the list's filter, next to the query. See
	 * Velocity_Deals_Page::facets(): the number is worked out from the
	 * filter's settings, the same way the block editor does.
	 *
	 * @param int $query_id Query post ID.
	 * @return array[]
	 */
	public static function facets( $query_id ) {
		$settings = array(
			'source'        => 'taxonomy',
			'fieldType'     => 'post_field',
			'taxonomy'      => self::TAXONOMY,
			'post_field'    => 'post_type',
			'include'       => array(),
			'exclude'       => array(),
			'type'          => 'query-filter',
			'customField'   => '',
			'customMetaKey' => '',
		);

		return array(
			array(
				'hash'       => self::hash( wp_json_encode( $settings ) ),
				'attributes' => wp_json_encode(
					array_merge(
						$settings,
						array(
							'uniqueID' => Velocity_Seed::uid( $query_id, 'filter-topic' ),
							'slug'     => 'topic',
						)
					)
				),
			),
		);
	}

	/**
	 * The number Kadence's editor gives a filter: a hash of its settings
	 * written as JSON (the "string-hash" of its script, 32 bits, unsigned).
	 *
	 * @param string $text The settings as JSON.
	 * @return int
	 */
	public static function hash( $text ) {
		$hash = 5381;

		for ( $i = strlen( $text ) - 1; $i >= 0; $i-- ) {
			// Keep the product in 32 bits, as the script's own arithmetic does.
			$hash = ( ( $hash * 33 ) & 0xFFFFFFFF ) ^ ord( $text[ $i ] );
		}

		return $hash & 0xFFFFFFFF;
	}

	/**
	 * The lists: the featured article and the list of all of them.
	 *
	 * "role" is what inc/insights.php reads to do what a query has no
	 * setting for.
	 *
	 * @return array[] By key, as Velocity_Seed::query_meta() takes them.
	 */
	public static function queries() {
		return array(
			'featured' => array(
				'title'      => 'News and Insights: featured article',
				'post_type'  => self::POST_TYPE,
				'order_by'   => 'date',
				'order'      => 'desc',
				'per_page'   => '1',
				'columns'    => array( '1', '1', '1' ),
				'column_gap' => array( '0', '0', '0' ),
				'row_gap'    => array( '0', '0', '0' ),
				'card'       => array( __CLASS__, 'featured_card' ),
				'meta'       => array( '_vm_query_role' => 'insights-featured' ),
			),
			'index'    => array(
				'title'      => 'News and Insights: articles',
				'post_type'  => self::POST_TYPE,
				'order_by'   => 'date',
				'order'      => 'desc',
				'per_page'   => '9',
				'columns'    => array( '3', '2', '1' ),
				'column_gap' => array( '32', '24', '20' ),
				'row_gap'    => array( '32', '24', '20' ),
				'card'       => array( __CLASS__, 'card' ),
				'content'    => array( __CLASS__, 'index_content' ),
				'facets'     => array( __CLASS__, 'facets' ),
				'meta'       => array( '_vm_query_role' => 'insights-index' ),
			),
			// Under an article: the latest three of its topics, in the same card.
			'related'  => array(
				'title'      => 'News and Insights: you may also like',
				'post_type'  => self::POST_TYPE,
				'order_by'   => 'date',
				'order'      => 'desc',
				'per_page'   => '3',
				'columns'    => array( '3', '2', '1' ),
				'column_gap' => array( '32', '24', '20' ),
				'row_gap'    => array( '32', '24', '20' ),
				'card'       => array( __CLASS__, 'card' ),
				'card_key'   => 'index',
				'card_title' => 'News and Insights: articles card',
				'meta'       => array( '_vm_query_role' => 'insights-related' ),
			),
		);
	}

	/**
	 * The photo of the article being listed, in a fixed shape.
	 *
	 * @param int    $card_id Query card post ID.
	 * @param string $key     Key for the unique ID.
	 * @param int[]  $radius  Radius of the four corners.
	 * @param string $size    Image size.
	 * @return string
	 */
	public static function photo( $card_id, $key, $radius, $size ) {
		$uid = Velocity_Seed::uid( $card_id, $key );

		return Velocity_Seed::block(
			'kadence/image',
			array(
				// Sixteen wide by nine tall, whatever the photo's own shape.
				'ratio'           => 'land169',
				'useRatio'        => true,
				'linkDestination' => 'none',
				'uniqueID'        => $uid,
				'marginDesktop'   => array( '0', '', '0', '' ),
				'borderRadius'    => $radius,
				'inQueryBlock'    => true,
				'kadenceDynamic'  => array(
					'url' => array(
						'enable'   => true,
						'source'   => '',
						'field'    => 'post|post_featured_image',
						'custom'   => '',
						'para'     => '',
						// The image size.
						'before'   => $size,
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
	}

	/**
	 * The card of an article: photo, topic, title, summary and the link.
	 *
	 * Every part reads the article being listed. The summary is its
	 * excerpt, on one line; with no excerpt, WordPress gives the start of
	 * the text instead. vm-card-link stretches the title's link over the whole card.
	 * The same card is used under an article, in "You may also like".
	 *
	 * @param int $card_id Query card post ID.
	 * @return string
	 */
	public static function card( $card_id ) {
		$text = Velocity_Seed::column(
			$card_id,
			'article-text',
			Velocity_Home::t(
				$card_id,
				'article-topic',
				'',
				array(
					'size'       => array( 14, '', '' ),
					'weight'     => '600',
					'lh'         => 1.5,
					'color'      => '#b04c11',
					'background' => 'rgba(241,133,33,0.2)',
					'padding'    => array( '4', '8', '4', '8' ),
					'radius'     => array( 3, 3, 3, 3 ),
					'margin'     => array( '0', '', '16', '' ),
					'dynamic'    => 'post|vm_insight_topic',
					'class'      => 'vm-tag vm-hide-empty',
				)
			) . "\n\n" .
			Velocity_Home::card_title(
				$card_id,
				'article',
				'',
				array(
					'color'   => Velocity_Seed::color( 'palette3' ),
					'lh'      => 1.1,
					'margin'  => array( '0', '', '16', '' ),
					'dynamic' => 'post|post_title',
					'to_post' => true,
					// A long title is cut after three lines. See style.css.
					'class'   => 'vm-lines-3',
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'article-excerpt',
				'',
				array(
					'color'   => Velocity_Seed::color( 'palette5' ),
					'margin'  => array( '0', '', '16', '' ),
					'dynamic' => 'post|post_excerpt',
					// One line, as in the design. See style.css.
					'class'   => 'vm-hide-empty vm-lines-1',
				)
			) . "\n\n" .
			// Looks like a link. The card's title already links the whole card.
			Velocity_Home::t(
				$card_id,
				'article-more',
				'<u>Read the Full Post</u>',
				array(
					'weight' => '600',
					'lh'     => 1.1,
					'margin' => array( '0', '', '0', '' ),
				)
			),
			array(
				'padding'      => array( '24', '24', '24', '24' ),
				'inQueryBlock' => true,
				'metadata'     => array( 'name' => 'Topic, title and summary' ),
			)
		);

		return Velocity_Seed::column(
			$card_id,
			'article',
			self::photo( $card_id, 'photo', array( 12, 12, 0, 0 ), 'medium_large' ) . "\n\n" . $text,
			array_merge(
				Velocity_Home::card(
					array(
						'background'   => Velocity_Seed::color( 'palette8' ),
						'borderStyle'  => Velocity_Home::border( Velocity_Seed::color( 'palette7' ) ),
						'borderRadius' => array( 12, 12, 12, 12 ),
						// The photo runs to the edges: the text has its own padding.
						'padding'      => array( '0', '0', '0', '0' ),
						'className'    => 'vm-card-link',
						'metadata'     => array( 'name' => 'Article' ),
					)
				),
				array(
					'inQueryBlock'   => true,
					'rowGapVariable' => array( '', '', '' ),
					'rowGap'         => array( '', '', '' ),
				)
			)
		);
	}

	/**
	 * The featured article: photo on the left, topic, title, summary and
	 * the button on the right. On phones the photo goes above.
	 *
	 * @param int $card_id Query card post ID.
	 * @return string
	 */
	public static function featured_card( $card_id ) {
		$gray = '#666666';

		$details =
			Velocity_Home::t(
				$card_id,
				'featured-topic',
				'',
				array(
					'size'    => array( 14, '', '' ),
					'lh'      => 1.1,
					'color'   => $gray,
					'margin'  => array( '0', '', '20', '' ),
					'dynamic' => 'post|vm_insight_topic',
					'class'   => 'vm-hide-empty',
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'featured-title',
				'',
				array(
					'tag'     => 'h2',
					'class'   => 'vm-lines-4',
					'size'    => array( 46, 36, 30 ),
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
					'margin'  => array( '0', '', '20', '' ),
					'dynamic' => 'post|post_excerpt',
					'class'   => 'vm-hide-empty vm-lines-2',
				)
			) . "\n\n" .
			self::post_button( $card_id, 'featured', 'Read the Full Post' );

		$row = Velocity_Seed::row(
			$card_id,
			'featured-row',
			array(
				Velocity_Seed::column(
					$card_id,
					'featured-photo-col',
					self::photo( $card_id, 'featured-photo', array( 12, 12, 12, 12 ), 'large' ),
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
						'metadata'          => array( 'name' => 'Topic, title, summary and button' ),
					)
				),
			),
			array(
				// 624px and 416px of the design's 1088.
				'firstColumnWidth'  => 60,
				'secondColumnWidth' => 40,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 48, 32, 24 ),
				'verticalAlignment' => 'middle',
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				'padding'           => array( '0', '', '0', '' ),
				'inQueryBlock'      => true,
				'metadata'          => array( 'name' => 'Featured article' ),
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
					'margin' => array( '0', '', '17', '' ),
				)
			) . "\n\n" . $row,
			array(
				'background'    => Velocity_Seed::color( 'palette7' ),
				'borderRadius'  => array( 20, 20, 20, 20 ),
				'padding'       => array( '48', '86', '67', '86' ),
				'tabletPadding' => array( '40', '32', '40', '32' ),
				'mobilePadding' => array( '24', '20', '32', '20' ),
				'inQueryBlock'  => true,
				'metadata'      => array( 'name' => 'Featured' ),
			)
		);
	}

	/**
	 * A filled button that goes to the article being listed.
	 *
	 * @param int    $card_id Query card post ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $text    Button text.
	 * @return string
	 */
	public static function post_button( $card_id, $key, $text ) {
		return Velocity_Home::buttons(
			$card_id,
			$key,
			array(
				array(
					$text,
					"[kb-dynamic field='post|post_url' type='url']",
					'fill',
					array(
						'inQueryBlock'   => true,
						'kadenceDynamic' => array(
							'link' => array(
								'enable'   => true,
								'source'   => '',
								'field'    => 'post|post_url',
								'custom'   => '',
								'para'     => '',
								'before'   => '',
								'after'    => '',
								'fallback' => '',
							),
						),
					),
				),
			),
			'left'
		);
	}

	/**
	 * What goes inside the list's query: the topics and the sort, the
	 * cards, the pages and the message for no results.
	 *
	 * Choosing a topic lists its articles on this same page, as the design
	 * notes. The sort reads "Newest first", which is the order the list
	 * starts in, and offers the other one.
	 *
	 * @param int $query_id Query post ID.
	 * @param int $card_id  Query card post ID.
	 * @return string Inner blocks of the kadence/query block.
	 */
	public static function index_content( $query_id, $card_id ) {
		$topics = Velocity_Seed::column(
			$query_id,
			'topics-col',
			Velocity_Seed::block(
				'kadence/query-filter',
				array(
					'uniqueID'    => Velocity_Seed::uid( $query_id, 'filter-topic' ),
					'placeholder' => 'Topics',
					'taxonomy'    => self::TAXONOMY,
					'label'       => 'Topics',
					'slug'        => 'topic',
				)
			),
			array(
				'flexGrow' => array( 1, '', '' ),
				'metadata' => array( 'name' => 'Topics' ),
			)
		);

		$sort = Velocity_Seed::block(
			'kadence/query-sort',
			array(
				'uniqueID'    => Velocity_Seed::uid( $query_id, 'sort' ),
				'sortItems'   => array( 'post_date' ),
				'post_date'   => array(
					'textDesc'    => 'Newest first',
					'textAsc'     => 'Oldest first',
					// The list starts newest first: that is the empty choice.
					'showDesc'    => false,
					'showAsc'     => true,
					'metaKey'     => '',
					'metaKeyType' => '',
				),
				'placeholder' => 'Newest first',
			)
		);

		$bar = Velocity_Seed::column(
			$query_id,
			'controls',
			$topics . "\n\n" . $sort,
			array(
				'direction'         => array( 'horizontal', '', '' ),
				'verticalAlignment' => 'middle',
				'gutter'            => array( 12, '', '' ),
				'gutterVariable'    => array( 'custom', 'custom', 'custom' ),
				'margin'            => array( '0', '', '32', '' ),
				// The topics as a pill and the sort as plain text. See
				// inc/filters.php and style.css.
				'className'         => 'vm-filters vm-filters--bar',
				'metadata'          => array( 'name' => 'Topics and sort' ),
			)
		);

		return $bar . "\n\n" .
			Velocity_Seed::block(
				'kadence/query-card',
				array(
					'uniqueID' => Velocity_Seed::uid( $query_id, 'card' ),
					'id'       => (int) $card_id,
				)
			) . "\n\n" .
			Velocity_Deals_Page::no_results( $query_id, 'No articles on that topic yet.' ) . "\n\n" .
			// A new page starts at the top of the list, topics included.
			Velocity_Deals_Page::pagination( $query_id, 'block' );
	}

	/**
	 * Hero: title and intro, centered on navy.
	 *
	 * @param int $post_id Element ID.
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
						'News and Insights That Keep You Ahead',
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
						'From market trends to best broker practices to what’s new at Velocity, discover the latest in investor lending to fuel your growth.',
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
		);

		return Velocity_Home::section(
			$post_id,
			'hero',
			$inner,
			'Hero',
			array(
				'bgColor'       => Velocity_Seed::color( 'palette3' ),
				'padding'       => array( '69', '', '60', '' ),
				'mobilePadding' => array( '48', '', '32', '' ),
			)
		);
	}

	/**
	 * The featured article, on navy under the hero.
	 *
	 * vm-needs-items hides the section while no article is featured.
	 *
	 * @param int   $post_id Element ID.
	 * @param int[] $queries Query post IDs by key.
	 * @return string
	 */
	public static function featured( $post_id, $queries ) {
		return Velocity_Home::section(
			$post_id,
			'featured',
			Velocity_Seed::block(
				'kadence/query',
				array(
					'uniqueID' => Velocity_Seed::uid( $post_id, 'featured-query' ),
					'id'       => (int) $queries['featured'],
				)
			),
			'Featured article',
			array(
				'bgColor'       => Velocity_Seed::color( 'palette3' ),
				'padding'       => array( '0', '', '64', '' ),
				'mobilePadding' => array( '0', '', '48', '' ),
				'className'     => 'vm-needs-items',
			)
		);
	}

	/**
	 * The list of articles, with its topics, sort and pages.
	 *
	 * @param int   $post_id Element ID.
	 * @param int[] $queries Query post IDs by key.
	 * @return string
	 */
	public static function index( $post_id, $queries ) {
		return Velocity_Home::section(
			$post_id,
			'index',
			Velocity_Seed::block(
				'kadence/query',
				array(
					'uniqueID' => Velocity_Seed::uid( $post_id, 'index-query' ),
					'id'       => (int) $queries['index'],
				)
			),
			'Articles',
			array(
				'anchor'        => 'articles',
				'padding'       => array( '64', '', '64', '' ),
				'mobilePadding' => array( '48', '', '48', '' ),
			)
		);
	}

	/**
	 * The newsletter band: title, intro and the sign-up form.
	 *
	 * The form is the shared "Newsletter Signup" (wp velocity seed-forms),
	 * printed by [vm_form name="newsletter"]. See inc/forms.php.
	 *
	 * @param int $post_id Element ID.
	 * @return string
	 */
	public static function newsletter( $post_id ) {
		$inner =
			Velocity_Home::title( $post_id, 'newsletter', 'Subscribe to Velocity’s Newsletter' ) . "\n\n" .
			Velocity_Home::subtitle(
				$post_id,
				'newsletter',
				'Get the latest industry insights, brokerage growth strategies, program updates, and more delivered directly to your inbox.',
				array(
					'align'  => 'center',
					'color'  => '#cccccc',
					'width'  => 800,
					'margin' => array( '0', 'auto', '32', 'auto' ),
				)
			) . "\n\n" .
			Velocity_Seed::column(
				$post_id,
				'newsletter-form',
				"<!-- wp:shortcode -->\n[vm_form name=\"newsletter\"]\n<!-- /wp:shortcode -->",
				array(
					'maxWidth' => array( 630, '', '' ),
					'margin'   => array( '0', 'auto', '0', 'auto' ),
					'metadata' => array( 'name' => 'Sign-up form' ),
				)
			);

		$band = Velocity_Seed::row(
			$post_id,
			'newsletter-band',
			array( Velocity_Seed::column( $post_id, 'newsletter-band-col', $inner ) ),
			array(
				'bgColor'       => Velocity_Seed::color( 'palette3' ),
				'bgImg'         => get_stylesheet_directory_uri() . '/assets/images/home/cta-background.jpg',
				'bgImgSize'     => 'cover',
				'bgImgPosition' => 'center center',
				'borderRadius'  => array( 26, 26, 26, 26 ),
				'maxWidth'      => 1280,
				'textColor'     => Velocity_Seed::color( 'palette9' ),
				'padding'       => array( '112', '80', '112', '80' ),
				'tabletPadding' => array( '80', '32', '80', '32' ),
				'mobilePadding' => array( '64', '20', '64', '20' ),
				'metadata'      => array( 'name' => 'Newsletter band' ),
			)
		);

		return Velocity_Home::section(
			$post_id,
			'newsletter',
			$band,
			'Newsletter',
			array(
				'padding'       => array( '96', '', '96', '' ),
				'mobilePadding' => array( '48', '', '64', '' ),
			)
		);
	}

	/**
	 * Every section of the list of articles, in page order.
	 *
	 * @param int   $post_id Element ID.
	 * @param int[] $queries Query post IDs by key.
	 * @return string
	 */
	public static function archive( $post_id, $queries ) {
		return implode(
			"\n\n",
			array(
				self::hero( $post_id ),
				self::featured( $post_id, $queries ),
				self::index( $post_id, $queries ),
				self::newsletter( $post_id ),
			)
		);
	}
	/**
	 * The top of an article: the way back to the list, its photo, and beside
	 * it the topic and date, the title, the author, the summary and the
	 * links to share it.
	 *
	 * Every text reads the article. "Written by" is a field of the theme,
	 * empty for a press release.
	 *
	 * @param int $post_id Element ID.
	 * @return string
	 */
	public static function article_top( $post_id ) {
		$gray = '#666666';
		$uid  = Velocity_Seed::uid( $post_id, 'top-photo' );

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
			Velocity_Home::t(
				$post_id,
				'top-meta',
				'',
				array(
					'size'    => array( 14, '', '' ),
					'lh'      => 1.1,
					'color'   => $gray,
					'margin'  => array( '0', '', '20', '' ),
					'dynamic' => 'post|vm_insight_meta',
					'class'   => 'vm-hide-empty',
				)
			) . "\n\n" .
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
				'top-byline',
				'',
				array(
					'size'    => array( 14, '', '' ),
					'lh'      => 1.1,
					'margin'  => array( '0', '', '20', '' ),
					'dynamic' => 'post|vm_insight_byline',
					'class'   => 'vm-hide-empty',
				)
			) . "\n\n" .
			Velocity_Home::t(
				$post_id,
				'top-excerpt',
				'',
				array(
					'margin'  => array( '0', '', '20', '' ),
					// Only an excerpt that was written. The start of the text, which a
					// card falls back on, would repeat what comes right under it.
					'dynamic' => 'post|vm_insight_excerpt',
					'class'   => 'vm-hide-empty',
				)
			) . "\n\n" .
			"<!-- wp:shortcode -->\n[vm_insight_share]\n<!-- /wp:shortcode -->";

		$row = Velocity_Seed::row(
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
						'metadata'          => array( 'name' => 'Topic, title, author and summary' ),
					)
				),
			),
			array(
				// 624px and 416px of the design's 1088.
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
		);

		$back = Velocity_Home::t(
			$post_id,
			'top-back',
			'<a href="' . esc_url( home_url( '/insights/' ) ) . '">&lt; News &amp; Insights</a>',
			array(
				'weight'     => '600',
				'lh'         => 1.1,
				'link'       => Velocity_Seed::color( 'palette1' ),
				'link_hover' => Velocity_Seed::color( 'palette2' ),
				'margin'     => array( '0', '', '29', '' ),
			)
		);

		return Velocity_Home::section(
			$post_id,
			'top',
			Velocity_Seed::row(
				$post_id,
				'top-inner',
				array( Velocity_Seed::column( $post_id, 'top-inner-col', $back . "\n\n" . $row ) ),
				array(
					'maxWidth' => 1088,
					'padding'  => array( '0', '', '0', '' ),
					'metadata' => array( 'name' => 'Back link, photo and title' ),
				)
			),
			'Top of the article',
			array(
				'padding'       => array( '48', '', '48', '' ),
				'mobilePadding' => array( '32', '', '32', '' ),
			)
		);
	}

	/**
	 * The article itself: what its author wrote in the editor, in a column
	 * 748px wide. vm-article sets the look of what is inside. See style.css.
	 *
	 * @param int $post_id Element ID.
	 * @return string
	 */
	public static function article_body( $post_id ) {
		return Velocity_Home::section(
			$post_id,
			'body',
			Velocity_Seed::row(
				$post_id,
				'body-inner',
				array(
					Velocity_Seed::column(
						$post_id,
						'body-col',
						Velocity_Seed::block(
							'kadence/dynamichtml',
							array(
								'uniqueID' => Velocity_Seed::uid( $post_id, 'body-content' ),
								'field'    => 'post|post_content',
								'metadata' => array( 'name' => 'The article' ),
							)
						),
						array( 'className' => 'vm-article' )
					),
				),
				array(
					'maxWidth' => 748,
					'padding'  => array( '0', '', '0', '' ),
					'metadata' => array( 'name' => 'Article column' ),
				)
			),
			'Article',
			array(
				'padding'       => array( '32', '', '32', '' ),
				'mobilePadding' => array( '16', '', '24', '' ),
			)
		);
	}

	/**
	 * Under the article: the box about its author and the links to share it.
	 *
	 * @param int $post_id Element ID.
	 * @return string
	 */
	public static function article_author( $post_id ) {
		return Velocity_Home::section(
			$post_id,
			'author',
			Velocity_Seed::row(
				$post_id,
				'author-inner',
				array(
					Velocity_Seed::column(
						$post_id,
						'author-col',
						"<!-- wp:shortcode -->\n[vm_insight_author]\n<!-- /wp:shortcode -->\n\n<!-- wp:shortcode -->\n[vm_insight_share]\n<!-- /wp:shortcode -->",
						array( 'className' => 'vm-article-end' )
					),
				),
				array(
					'maxWidth' => 762,
					'padding'  => array( '0', '', '0', '' ),
					'metadata' => array( 'name' => 'Author and share' ),
				)
			),
			'Author and share',
			array(
				'padding'       => array( '0', '', '80', '' ),
				'mobilePadding' => array( '0', '', '56', '' ),
			)
		);
	}

	/**
	 * "You may also like": the latest three articles of the same topics.
	 *
	 * vm-needs-items hides the section when there is none.
	 *
	 * @param int   $post_id Element ID.
	 * @param int[] $queries Query post IDs by key.
	 * @return string
	 */
	public static function article_related( $post_id, $queries ) {
		return Velocity_Home::section(
			$post_id,
			'related',
			Velocity_Home::t(
				$post_id,
				'related-title',
				'You may also like',
				array(
					'tag'    => 'h2',
					'align'  => 'center',
					'margin' => array( '0', '', '30', '' ),
				)
			) . "\n\n" .
			Velocity_Seed::block(
				'kadence/query',
				array(
					'uniqueID' => Velocity_Seed::uid( $post_id, 'related-query' ),
					'id'       => (int) $queries['related'],
				)
			),
			'You may also like',
			array(
				'padding'       => array( '0', '', '64', '' ),
				'mobilePadding' => array( '0', '', '48', '' ),
				'className'     => 'vm-needs-items',
			)
		);
	}

	/**
	 * Every section of the page of an article, in page order.
	 *
	 * @param int   $post_id Element ID.
	 * @param int[] $queries Query post IDs by key.
	 * @return string
	 */
	public static function single( $post_id, $queries ) {
		return implode(
			"\n\n",
			array(
				self::article_top( $post_id ),
				self::article_body( $post_id ),
				self::article_author( $post_id ),
				self::article_related( $post_id, $queries ),
			)
		);
	}
	/**
	 * A press quote as blocks: the pattern "Article: Press Quote" filled in.
	 *
	 * A press release kept its quote in three fields beside the text (the
	 * "Quote" box: headshot, quote, name and title). In an article it is part
	 * of the text. See patterns/article-press-quote.php.
	 *
	 * @param string $quote    The quote.
	 * @param string $name     Name and title.
	 * @param int    $image_id Media ID of the headshot, or 0.
	 * @return string Empty when there is nothing to show.
	 */
	public static function press_quote( $quote, $name, $image_id = 0 ) {
		$quote = trim( (string) $quote );
		// The box had the name and the title on two lines.
		$name = trim( preg_replace( '/\s*[\r\n]+\s*/', ', ', trim( (string) $name ) ) );
		$name = trim( preg_replace( '/,\s*,/', ',', $name ), ', ' );

		if ( '' === $quote && '' === $name ) {
			return '';
		}

		// The old template added the quotation marks itself.
		if ( '' !== $quote && ! preg_match( '/^["“]/u', $quote ) ) {
			$quote = '“' . $quote . '”';
		}

		$image = '';
		$url   = $image_id ? wp_get_attachment_image_url( (int) $image_id, 'thumbnail' ) : '';

		if ( $url ) {
			$image = sprintf(
				"<!-- wp:image {\"id\":%1\$d,\"sizeSlug\":\"thumbnail\"} -->\n<figure class=\"wp-block-image size-thumbnail\"><img src=\"%2\$s\" alt=\"\" class=\"wp-image-%1\$d\"/></figure>\n<!-- /wp:image -->\n\n",
				(int) $image_id,
				esc_url( $url )
			);
		}

		return "<!-- wp:group {\"metadata\":{\"name\":\"Press Quote\"},\"className\":\"vm-press-quote\",\"layout\":{\"type\":\"constrained\"}} -->\n" .
			'<div class="wp-block-group vm-press-quote">' . $image .
			"<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><!-- wp:paragraph -->\n<p>" . esc_html( $quote ) . "</p>\n<!-- /wp:paragraph -->" .
			( '' !== $name ? '<cite>' . esc_html( $name ) . '</cite>' : '' ) .
			"</blockquote>\n<!-- /wp:quote --></div>\n<!-- /wp:group -->";
	}
}
