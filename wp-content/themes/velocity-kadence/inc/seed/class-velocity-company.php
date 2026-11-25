<?php
/**
 * Builds the About Us page as block content.
 *
 * Like the home page, this only produces the first version of the page.
 * After `wp velocity seed-company` the page is edited in the block editor.
 * It follows the same rule: what a block setting can express is set on the
 * block. The classes used here are listed in docs/components.md.
 *
 * Two sections read existing content instead of holding their own, each
 * through a Kadence query and its card, like the careers listing page:
 * - "The People Moving Deals Forward" lists the staff of one type, from
 *   Staff in the admin.
 * - "Careers at Velocity" lists the published careers.
 *
 * Figma: page "Company", frame "Velocity About Us".
 *
 * Kept compatible with PHP 7.4, see class-velocity-cli.php.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * About Us page seed content.
 */
class Velocity_Company {

	/**
	 * Slug of the page this class fills.
	 */
	const SLUG = 'about';

	/**
	 * Name of the staff type listed in the people section.
	 */
	const STAFF_TYPE = 'Sales Directors';

	/**
	 * ID of the people section, for links such as /about/#team.
	 */
	const TEAM_ANCHOR = 'team';

	/**
	 * A Kadence Image block showing a file from assets/images/company.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $file    File name.
	 * @param int    $width   Max width in px.
	 * @param string $alt     Alternative text. Empty for decorative images.
	 * @param array  $extra   Extra block settings.
	 * @return string
	 */
	private static function image( $post_id, $key, $file, $width, $alt = '', $extra = array() ) {
		return Velocity_Home::image( $post_id, $key, $file, $width, $alt, '', 'company', $extra );
	}

	/**
	 * A centered section header whose intro can take a color.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $title   Title. May contain inline HTML.
	 * @param string $intro   Intro text.
	 * @param int    $width   Max width in px.
	 * @param string $color       Intro color, or empty to inherit.
	 * @param int    $intro_width Max width of the intro in px, or 0 for the header's.
	 * @return string
	 */
	private static function header( $post_id, $key, $title, $intro, $width, $color = '', $intro_width = 0 ) {
		$options = array( 'align' => 'center' );

		if ( $color ) {
			$options['color'] = $color;
		}

		// A title that needs the full width, over an intro that reads better narrow.
		if ( $intro_width ) {
			$options['width']  = $intro_width;
			$options['margin'] = array( '0', 'auto', '0', 'auto' );
		}

		return Velocity_Seed::row(
			$post_id,
			$key . '-header',
			array(
				Velocity_Seed::column(
					$post_id,
					$key . '-header-col',
					Velocity_Home::title( $post_id, $key, $title ) . "\n\n" .
					Velocity_Home::subtitle( $post_id, $key, $intro, $options )
				),
			),
			array(
				'maxWidth' => $width,
				'padding'  => array( '0', '', '56', '' ),
				'metadata' => array( 'name' => 'Section header' ),
			)
		);
	}

	/**
	 * Hero: the page title and its intro.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function hero( $post_id ) {
		$inner =
			Velocity_Home::t(
				$post_id,
				'hero-title',
				'We Believe Every Borrower Deserves an Opportunity',
				array(
					'tag'    => 'h1',
					'align'  => 'center',
					'margin' => array( '0', '', '24', '' ),
					'class'  => 'is-style-vm-reveal-lines',
				)
			) . "\n\n" .
			Velocity_Home::subtitle(
				$post_id,
				'hero',
				'At Velocity Mortgage Capital, we want to give borrowers of all types a chance. By lending and holding our own money, we can say “yes” when others won’t.',
				array(
					'align' => 'center',
					'color' => Velocity_Seed::color( 'palette6' ),
				)
			);

		return Velocity_Home::section(
			$post_id,
			'hero',
			$inner,
			'Hero',
			array(
				'inheritMaxWidth' => false,
				'maxWidth'        => 848,
				'padding'         => array( '88', '', '88', '' ),
				'mobilePadding'   => array( '56', '', '56', '' ),
			)
		);
	}

	/**
	 * One of the two cards of the mission panel.
	 *
	 * The text is 19px bold, larger than the 16px regular of the design: at
	 * 16px regular, Deep Rust on this background does not reach the contrast
	 * that WCAG AA asks of body text (4.1 to 1, it needs 4.5). At this size
	 * and weight it counts as large text, which needs 3 to 1.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $file    Icon file.
	 * @param string $title   Card title.
	 * @param string $text    Card text.
	 * @return string
	 */
	private static function mission_card( $post_id, $key, $file, $title, $text ) {
		$rust = Velocity_Seed::color( 'palette2' );

		return Velocity_Seed::column(
			$post_id,
			$key,
			// The icon has a Section of its own. Directly inside a card with a
			// set height, Kadence would stretch an SVG to fill it.
			Velocity_Seed::column(
				$post_id,
				$key . '-icon',
				self::image( $post_id, $key, $file, 35 ),
				array( 'metadata' => array( 'name' => 'Icon' ) )
			) . "\n\n" .
			Velocity_Home::card_title( $post_id, $key, $title, array( 'color' => $rust ) ) . "\n\n" .
			Velocity_Home::t(
				$post_id,
				$key . '-body',
				$text,
				array(
					'color'  => $rust,
					'size'   => array( 19, '', '' ),
					'weight' => '700',
					'lh'     => 1.4,
					'margin' => array( '0', '', '0', '' ),
				)
			),
			Velocity_Home::card(
				array(
					'background'         => '#f8e8de',
					'borderStyle'        => Velocity_Home::border( Velocity_Seed::color( 'palette1' ) ),
					'borderHoverStyle'   => Velocity_Home::border( Velocity_Seed::color( 'palette1' ) ),
					'displayHoverShadow' => false,
					'height'             => array( 293, 220, '' ),
					'textAlign'          => array( 'center', '', '' ),
					'justifyContent'     => array( 'center', '', '' ),
					'verticalAlignment'  => 'middle',
					'metadata'           => array( 'name' => $title ),
				)
			)
		);
	}

	/**
	 * Our mission, our promise: a dark panel with the two statements as cards.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function mission( $post_id ) {
		$white = Velocity_Seed::color( 'palette9' );

		$intro =
			Velocity_Home::t(
				$post_id,
				'mission-title',
				'Our Mission, Our Promise',
				array(
					'tag'    => 'h2',
					'color'  => $white,
					'margin' => array( '0', '', '24', '' ),
					'class'  => 'is-style-vm-reveal-lines',
				)
			) . "\n\n" .
			Velocity_Home::subtitle(
				$post_id,
				'mission',
				'Our mission is the purpose that propels our company forward every day. Our promise is the commitment we make to earn each broker’s trust.',
				array( 'color' => $white )
			);

		$cards = Velocity_Seed::row(
			$post_id,
			'mission-cards',
			array(
				self::mission_card( $post_id, 'mission-card-0', 'icon-mission.svg', 'Our Mission', 'Create opportunities for more kinds of borrowers to grow.' ),
				self::mission_card( $post_id, 'mission-card-1', 'icon-promise.svg', 'Our Promise', 'We help brokers move forward when others hit limits.' ),
			),
			array(
				'columnGutter'       => 'custom',
				'customGutter'       => array( 24, '', '' ),
				'columnsInnerHeight' => true,
				'mobileLayout'       => 'row',
				'padding'            => array( '0', '', '0', '' ),
				'metadata'           => array( 'name' => 'Mission and promise cards' ),
			)
		);

		$inner = Velocity_Seed::row(
			$post_id,
			'mission-row',
			array(
				Velocity_Seed::column( $post_id, 'mission-intro', $intro, array( 'metadata' => array( 'name' => 'Title and intro' ) ) ),
				Velocity_Seed::column( $post_id, 'mission-cards-col', $cards ),
			),
			array(
				'firstColumnWidth'  => 44,
				'secondColumnWidth' => 56,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 55, 40, '' ),
				'verticalAlignment' => 'middle',
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				// On wide screens the panel keeps growing, up to 1920px, and
				// its content stops here, like the panels of the home page.
				'maxWidth'          => 1280,
				'padding'           => array( '0', '', '0', '' ),
				'metadata'          => array( 'name' => 'Mission and promise' ),
			)
		);

		return Velocity_Home::panel(
			$post_id,
			'mission',
			$inner,
			'Our Mission, Our Promise',
			array(
				'backgroundType' => 'gradient',
				'background'     => Velocity_Seed::color( 'palette3' ),
				'gradient'       => 'linear-gradient(154deg,rgb(1,7,57) 31%,rgb(43,53,120) 80%)',
				'borderRadius'   => array( 20, 20, 20, 20 ),
				'padding'        => array( '48', '64', '48', '64' ),
				'tabletPadding'  => array( '48', '32', '48', '32' ),
				'mobilePadding'  => array( '48', '20', '48', '20' ),
			)
		);
	}

	/**
	 * The values: section header and five cards, each with its icon at the bottom.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function values( $post_id ) {
		$cards = array(
			array( 'icon-person.svg', 'In Your Corner', 'In every deal and every conversation, you get a real person who’s got your back.' ),
			array( 'icon-check.svg', 'Find the Win', 'When other lenders say no, we keep working to find a path to “yes.”' ),
			array( 'icon-forum.svg', 'Straight Talk', 'We tell you up front what we can do and what we can’t, so nothing comes as a surprise.' ),
			array( 'icon-long-term.svg', 'Here Long Term', 'Holding our own loans lets us offer 30-year terms so your clients can keep planning.' ),
			array( 'icon-book.svg', 'Education First', 'We make sure you understand how each loan works so you can place them successfully.' ),
		);

		$columns = array();
		foreach ( $cards as $index => $card ) {
			$key   = 'value-' . $index;
			$attrs = Velocity_Home::card(
				array_merge(
					array(
						'padding'           => array( '36', '24', '36', '24' ),
						// As tall as the design's on desktop and tablet, as tall as its text on phones.
						'height'            => array( 294, '', 0 ),
						// Title and text at the top, the icon at the bottom.
						'verticalAlignment' => 'space-between',
						'rowGap'            => array( 24, '', '' ),
						'metadata'          => array( 'name' => $card[1] ),
					),
					Velocity_Home::card_animation( $index )
				)
			);
			if ( $index ) {
				$attrs['id'] = $index + 1;
			}

			$columns[] = Velocity_Seed::column(
				$post_id,
				$key,
				Velocity_Seed::column(
					$post_id,
					$key . '-texts',
					Velocity_Home::card_title( $post_id, $key, $card[1] ) . "\n\n" .
					Velocity_Home::body( $post_id, $key, $card[2] ),
					array(
						'rowGapVariable' => array( 'custom', '', '' ),
						'rowGap'         => array( 24, '', '' ),
						'metadata'       => array( 'name' => 'Title and text' ),
					)
				) . "\n\n" .
				// In its own Section, like the mission cards' icons.
				Velocity_Seed::column(
					$post_id,
					$key . '-icon',
					self::image( $post_id, $key, $card[0], 48 ),
					array( 'metadata' => array( 'name' => 'Icon' ) )
				),
				$attrs
			);
		}

		$grid = Velocity_Seed::row(
			$post_id,
			'values-grid',
			$columns,
			array(
				'columnGutter'       => 'custom',
				'customGutter'       => array( 20, '', '' ),
				'columnsInnerHeight' => true,
				'tabletLayout'       => 'two-grid',
				'mobileLayout'       => 'row',
				'padding'            => array( '0', '', '0', '' ),
				'metadata'           => array( 'name' => 'Value cards' ),
			)
		);

		return Velocity_Home::section(
			$post_id,
			'values',
			Velocity_Home::section_header(
				$post_id,
				'values',
				'The Values That Guide <br>Our Decisions',
				'From the very first deal to every one after, these five pillars define how we work with brokers. They’re what you can expect from us.',
				840
			) . "\n\n" . $grid,
			'Our values'
		);
	}

	/**
	 * One figure laid over the story photo.
	 *
	 * The labels are 14px. The design draws them at 9.5px and 8px, which is
	 * too small to read.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $slot    Where it sits: volume, years or offices.
	 * @param string $figure  The figure.
	 * @param string $label   What the figure is.
	 * @param int    $size    Size of the figure in px.
	 * @return string
	 */
	private static function story_stat( $post_id, $key, $slot, $figure, $label, $size ) {
		$white = Velocity_Seed::color( 'palette9' );
		$wide  = 'offices' === $slot;

		$attrs = array(
			'background'   => Velocity_Seed::color( 'palette2' ),
			'borderRadius' => array( 9, 9, 9, 9 ),
			'padding'      => $wide ? array( '6', '16', '6', '16' ) : array( '16', '12', '16', '12' ),
			'textAlign'    => array( 'center', '', '' ),
			'className'    => 'vm-story-stat vm-story-stat--' . $slot,
			'metadata'     => array( 'name' => $figure . ' ' . $label ),
		);

		// The wide one reads on a single line: figure, then label.
		if ( $wide ) {
			$attrs['direction']         = array( 'horizontal', '', '' );
			$attrs['justifyContent']    = array( 'center', '', '' );
			$attrs['verticalAlignment'] = 'middle';
			$attrs['gutter']            = array( 10, '', '' );
			$attrs['gutterVariable']    = array( 'custom', 'custom', 'custom' );
		}

		return Velocity_Seed::column(
			$post_id,
			$key,
			Velocity_Home::t(
				$post_id,
				$key . '-figure',
				$figure,
				array(
					'family' => 'heading',
					'size'   => array( $size, '', '' ),
					'lh'     => 1.1,
					'color'  => $white,
					'margin' => array( '0', '', '0', '' ),
				)
			) . "\n\n" .
			Velocity_Home::t(
				$post_id,
				$key . '-label',
				$label,
				array(
					'size'   => array( 14, '', '' ),
					'lh'     => 1.3,
					'color'  => $white,
					'margin' => array( '0', '', '0', '' ),
				)
			),
			$attrs
		);
	}

	/**
	 * The Velocity story: a photo with three figures over it, and the
	 * history as a timeline.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function story( $post_id ) {
		// vm-story-media places the figures over the photo. See style.css.
		$media = Velocity_Seed::column(
			$post_id,
			'story-media',
			self::image(
				$post_id,
				'story-photo',
				'story.webp',
				339,
				'Four colleagues around a laptop in a meeting room',
				array( 'borderRadius' => array( 12, 12, 12, 12 ) )
			) . "\n\n" .
			self::story_stat( $post_id, 'story-stat-volume', 'volume', '$2B+', 'Funded annually (2025)', 47 ) . "\n\n" .
			self::story_stat( $post_id, 'story-stat-years', 'years', '20+', 'Years in investor lending', 53 ) . "\n\n" .
			self::story_stat( $post_id, 'story-stat-offices', 'offices', 'Nationwide', 'Lending in 7 offices', 16 ),
			array(
				'className' => 'vm-story-media',
				'metadata'  => array( 'name' => 'Photo and figures' ),
			)
		);

		// Text, and whether it is a point on the timeline.
		$entries = array(
			array( '<strong>In 2004,</strong> a group of mortgage and finance veterans got together to answer a question: why was it so hard for some investors to get funding and move forward with their plans? Even if they had equity, many borrowers still couldn’t get long-term loans. Was there a better way?', true ),
			array( 'Their answer was Velocity Mortgage Capital—a lender built for real estate investors, with a simple documentation process designed for how investors actually operate. This way, they could open up opportunities to the kinds of clients other lenders have long turned down.', false ),
			array( 'The idea caught on. By combining the flexibility of private lending with the stability of permanent financing, Velocity soon grew nationwide.', false ),
			array( '<strong>In January 2020,</strong> they became part of a publicly traded company.', true ),
			array( '<strong>And in 2025,</strong> Velocity was <strong>funding more than $2 billion in loans annually.</strong>', true ),
			array( '<strong>Today,</strong> thousands of brokers and their clients have come to rely on our direct portfolio lending services for all kinds of investment and commercial projects. But although we’ve grown, our focus remains the same as ever: to give every borrower, everywhere, a new chance.', true ),
		);

		$paragraphs = array();
		foreach ( $entries as $index => $entry ) {
			$options = array( 'margin' => array( '0', '', '0', '' ) );

			// Block style "Timeline point" (Styles panel): draws the dot.
			if ( $entry[1] ) {
				$options['class'] = 'is-style-vm-timeline-point';
			}

			$paragraphs[] = Velocity_Home::t( $post_id, 'story-entry-' . $index, $entry[0], $options );
		}

		$text =
			Velocity_Home::t(
				$post_id,
				'story-title',
				'The Velocity Story',
				array(
					'tag'    => 'h2',
					// In line with the timeline's text, which sits 26px in from its line.
					'margin' => array( '0', '', '32', '26' ),
					'class'  => 'is-style-vm-reveal-lines',
				)
			) . "\n\n" .
			// Block style "Timeline" (Styles panel): draws the line. See style.css.
			Velocity_Seed::column(
				$post_id,
				'story-timeline',
				implode( "\n\n", $paragraphs ),
				array(
					'className'      => 'is-style-vm-timeline',
					'padding'        => array( '', '', '', '26' ),
					'rowGapVariable' => array( 'custom', '', '' ),
					'rowGap'         => array( 24, '', '' ),
					'metadata'       => array( 'name' => 'Timeline' ),
				)
			);

		$inner = Velocity_Seed::row(
			$post_id,
			'story-row',
			array(
				$media,
				Velocity_Seed::column( $post_id, 'story-text', $text, array( 'metadata' => array( 'name' => 'Title and timeline' ) ) ),
			),
			array(
				'firstColumnWidth'  => 39,
				'secondColumnWidth' => 61,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 48, 48, '' ),
				'verticalAlignment' => 'middle',
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				// On wide screens the panel keeps growing, up to 1920px, and
				// its content stops here, like the panels of the home page.
				'maxWidth'          => 1280,
				'padding'           => array( '0', '', '0', '' ),
				'metadata'          => array( 'name' => 'Story' ),
			)
		);

		return Velocity_Home::panel(
			$post_id,
			'story',
			$inner,
			'The Velocity Story',
			array(
				'background'    => '#fff7ed',
				'padding'       => array( '56', '48', '56', '48' ),
				'tabletPadding' => array( '48', '32', '48', '32' ),
				'mobilePadding' => array( '48', '20', '48', '20' ),
			)
		);
	}

	/**
	 * Who we serve: three photo cards and the link to the process.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function brokerages( $post_id ) {
		$cards = array(
			array( 'brokerage-owner.webp', 'The Owner Who Is the Business', 'Brokerages with one name on every deal. We’re the lender that simplifies the complex.' ),
			array( 'brokerage-firm.webp', 'The Firm Focused <br>on Growth', 'Brokerages getting bigger deals. We’re the lender that expands what the team can win.' ),
			array( 'brokerage-national.webp', 'The Regional and National Brokerages', 'Brokerages with big teams and big pipelines. We’re the lender with capital to match.' ),
		);

		$white   = Velocity_Seed::color( 'palette9' );
		$columns = array();
		foreach ( $cards as $index => $card ) {
			$key   = 'brokerage-' . $index;
			$attrs = array_merge(
				array(
					// The photo is the Section's background, darkened toward the
					// bottom by its overlay so the text reads.
					'background'        => Velocity_Seed::color( 'palette3' ),
					'backgroundImg'     => array(
						array(
							'bgImg'           => get_stylesheet_directory_uri() . '/assets/images/company/' . $card[0],
							'bgImgID'         => '',
							'bgImgSize'       => 'cover',
							'bgImgPosition'   => 'center center',
							'bgImgAttachment' => 'scroll',
							'bgImgRepeat'     => 'no-repeat',
						),
					),
					'overlayType'       => 'gradient',
					'overlayGradient'   => 'linear-gradient(180deg,rgba(0,0,0,0.05) 14%,rgba(0,0,0,0.72) 67%)',
					'overlayOpacity'    => 1,
					'borderRadius'      => array( 8, 8, 8, 8 ),
					'padding'           => array( '24', '24', '24', '24' ),
					'height'            => array( 347, '', '' ),
					'verticalAlignment' => 'bottom',
					'rowGapVariable'    => array( 'custom', '', '' ),
					'rowGap'            => array( 9, '', '' ),
					'metadata'          => array( 'name' => wp_strip_all_tags( str_replace( ' <br>', ' ', $card[1] ) ) ),
				),
				Velocity_Home::card_animation( $index )
			);
			if ( $index ) {
				$attrs['id'] = $index + 1;
			}

			$columns[] = Velocity_Seed::column(
				$post_id,
				$key,
				Velocity_Home::card_title(
					$post_id,
					$key,
					$card[1],
					array(
						'size'  => array( 24, '', 22 ),
						'color' => $white,
						'width' => 280,
					)
				) . "\n\n" .
				Velocity_Home::t(
					$post_id,
					$key . '-body',
					$card[2],
					array(
						'color'  => $white,
						'margin' => array( '0', '', '0', '' ),
						'width'  => 280,
					)
				),
				$attrs
			);
		}

		$grid = Velocity_Seed::row(
			$post_id,
			'brokerages-grid',
			$columns,
			array(
				'maxWidth'     => 1089,
				'columnGutter' => 'custom',
				'customGutter' => array( 24, 16, '' ),
				'mobileLayout' => 'row',
				'padding'      => array( '0', '', '40', '' ),
				'metadata'     => array( 'name' => 'Brokerage cards' ),
			)
		);

		return Velocity_Home::section(
			$post_id,
			'brokerages',
			Velocity_Home::section_header(
				$post_id,
				'brokerages',
				'We Work With <br>Brokerages of Every Size',
				'A one-person brokerage runs differently than a national one. But after 20+ years of doing business with every kind, we know how to deliver success.',
				852
			) . "\n\n" . $grid . "\n\n" .
			Velocity_Home::buttons( $post_id, 'brokerages', array( array( 'See Our Full Process', home_url( '/process/' ) ) ) ),
			'Who we serve'
		);
	}

	/**
	 * The two lists of this page that read existing content. Each is a Kadence
	 * query (Kadence, Queries) with its own card (Kadence, Query Cards).
	 *
	 * @return array[] As Velocity_Seed::query_meta() takes them.
	 */
	public static function queries() {
		return array(
			'people'  => array(
				'title'      => 'About Us: people',
				'post_type'  => 'velocity-staff',
				'order_by'   => 'menu_order',
				'order'      => 'asc',
				'per_page'   => '12',
				'columns'    => array( '4', '3', '2' ),
				'column_gap' => array( '48', '24', '16' ),
				'row_gap'    => array( '32', '24', '16' ),
				'card'       => array( __CLASS__, 'person_card' ),
			),
			'careers' => array(
				'title'      => 'About Us: open positions',
				'post_type'  => 'velocity-career',
				'order_by'   => 'date',
				'order'      => 'desc',
				'per_page'   => '12',
				'columns'    => array( '3', '2', '1' ),
				'column_gap' => array( '24', '24', '16' ),
				'row_gap'    => array( '24', '24', '16' ),
				'card'       => array( __CLASS__, 'career_card' ),
			),
		);
	}

	/**
	 * The card of a person: photo, name, short title and LinkedIn.
	 *
	 * Every part reads the person being listed, with Kadence's dynamic
	 * content: the featured image, the title, the field "Staff Title (short)"
	 * and the field "Staff LinkedIn URL". The name links to the person's page.
	 * vm-card-link stretches that link over the whole card. See style.css.
	 *
	 * @param int $card_id Query card post ID.
	 * @return string
	 */
	public static function person_card( $card_id ) {
		$photo_uid = Velocity_Seed::uid( $card_id, 'photo' );
		$icons_uid = Velocity_Seed::uid( $card_id, 'icons' );
		$icon_uid  = Velocity_Seed::uid( $card_id, 'icon' );
		$linkedin  = "[kb-dynamic field='post|post_custom_field' custom='velocity_staff_linkedin_url' para='kb_custom_input' type='url']";

		$photo = Velocity_Seed::block(
			'kadence/image',
			array(
				'imgMaxWidth'      => 80,
				'linkDestination'  => 'none',
				'uniqueID'         => $photo_uid,
				'marginDesktop'    => array( '0', '', '24', '' ),
				'borderRadius'     => array( 50, 50, 50, 50 ),
				'borderRadiusUnit' => '%',
				'inQueryBlock'     => true,
				'kadenceDynamic'   => array(
					'url' => array(
						'enable'   => true,
						'source'   => '',
						'field'    => 'post|post_featured_image',
						'custom'   => '',
						'para'     => '',
						// The image size: a square crop.
						'before'   => 'thumbnail',
						'after'    => '',
						'fallback' => '',
					),
				),
			),
			sprintf(
				'<figure class="wp-block-kadence-image kb-image%1$s"><img src="%2$s" alt="" class="kb-img"/></figure>',
				$photo_uid,
				esc_url( includes_url( 'images/blank.gif' ) )
			)
		);

		$icon = Velocity_Seed::block(
			'kadence/icon',
			array(
				'uniqueID'      => $icons_uid,
				'textAlignment' => 'center',
				'inQueryBlock'  => true,
			),
			'<div class="wp-block-kadence-icon kt-svg-icons kt-svg-icons' . $icons_uid . ' alignnone">' .
			Velocity_Seed::block(
				'kadence/single-icon',
				array(
					'uniqueID'       => $icon_uid,
					'icon'           => 'fa_linkedin',
					'link'           => $linkedin,
					'target'         => '_blank',
					'size'           => 24,
					'title'          => 'LinkedIn',
					'color'          => Velocity_Seed::color( 'palette3' ),
					'hColor'         => Velocity_Seed::color( 'palette2' ),
					'padding'        => array( 10, 10, 10, 10 ),
					'inQueryBlock'   => true,
					'kadenceDynamic' => array(
						'link' => array(
							'enable'   => true,
							'source'   => '',
							'field'    => 'post|post_custom_field',
							'custom'   => 'velocity_staff_linkedin_url',
							'para'     => 'kb_custom_input',
							'before'   => '',
							'after'    => '',
							'fallback' => '',
						),
					),
				),
				'<div class="wp-block-kadence-single-icon kt-svg-style-default kt-svg-icon-wrap kt-svg-item-' . $icon_uid . '"><a href="' . $linkedin . '" class="kt-svg-icon-link" target="_blank" rel="noopener noreferrer"><span data-name="fa_linkedin" data-title="LinkedIn" class="kadence-dynamic-icon"></span></a></div>'
			) . '</div>'
		);

		return Velocity_Seed::column(
			$card_id,
			'person',
			$photo . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'person-name',
				'',
				array(
					'tag'     => 'h3',
					'align'   => 'center',
					'family'  => 'body',
					'size'    => array( 16, '', '' ),
					'weight'  => '700',
					'lh'      => 1.47,
					'color'   => Velocity_Seed::color( 'palette3' ),
					'margin'  => array( '0', '', '0', '' ),
					'dynamic' => 'post|post_title',
					'to_post' => true,
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'person-role',
				'',
				array(
					'align'   => 'center',
					'margin'  => array( '0', '', '14', '' ),
					'dynamic' => array( 'post|post_custom_field', 'velocity_staff_title_short' ),
				)
			) . "\n\n" . $icon,
			array(
				'inQueryBlock'    => true,
				'padding'         => array( '16', '12', '16', '12' ),
				'borderRadius'    => array( 14, 14, 14, 14 ),
				'backgroundHover' => Velocity_Seed::color( 'palette7' ),
				'textAlign'       => array( 'center', '', '' ),
				// Centers the photo, which is narrower than the card.
				'justifyContent'  => array( 'center', '', '' ),
				'className'       => 'vm-card-link',
				'metadata'        => array( 'name' => 'Person' ),
			)
		);
	}

	/**
	 * The card of an open position: title, offices and summary.
	 *
	 * Reads the career being listed: its title, linked to its page, the
	 * field "City/Cities" on one line (see inc/dynamic-content.php) and the
	 * field "Short Description".
	 *
	 * @param int $card_id Query card post ID.
	 * @return string
	 */
	public static function career_card( $card_id ) {
		$gray = Velocity_Seed::color( 'palette6' );

		return Velocity_Seed::column(
			$card_id,
			'career',
			Velocity_Home::card_title(
				$card_id,
				'career',
				'',
				array(
					'color'   => Velocity_Seed::color( 'palette3' ),
					'margin'  => array( '0', '', '24', '' ),
					'dynamic' => 'post|post_title',
					'to_post' => true,
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'career-city',
				'',
				array(
					'color'   => $gray,
					'margin'  => array( '0', '', '0', '' ),
					'dynamic' => 'post|vm_career_cities',
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'career-text',
				'',
				array(
					'color'   => $gray,
					'margin'  => array( '0', '', '0', '' ),
					'dynamic' => array( 'post|post_custom_field', 'career-short-description' ),
				)
			),
			array(
				'inQueryBlock'       => true,
				'background'         => Velocity_Seed::color( 'palette8' ),
				'borderRadius'       => array( 8, 8, 8, 8 ),
				'padding'            => array( '24', '24', '24', '24' ),
				'height'             => array( 200, '', '' ),
				'displayShadow'      => true,
				'shadow'             => array(
					array(
						'color'   => '#000000',
						'opacity' => 0.08,
						'spread'  => -4,
						'blur'    => 16,
						'hOffset' => 0,
						'vOffset' => 12,
						'inset'   => false,
					),
				),
				'displayHoverShadow' => true,
				'shadowHover'        => array(
					array(
						'color'   => '#010739',
						'opacity' => 0.18,
						'spread'  => -8,
						'blur'    => 24,
						'hOffset' => 0,
						'vOffset' => 16,
						'inset'   => false,
					),
				),
				'className'          => 'vm-card-link',
				'metadata'           => array( 'name' => 'Open position' ),
			)
		);
	}

	/**
	 * The people: the staff of one type, each linking to their page.
	 *
	 * The list is the Kadence query "About Us: people". Who is listed and in
	 * what order is set on that query; the look of a person is its card.
	 *
	 * @param int $post_id  Page ID.
	 * @param int $query_id ID of the people query.
	 * @return string
	 */
	public static function people( $post_id, $query_id ) {
		$list = Velocity_Seed::block(
			'kadence/query',
			array(
				'uniqueID'  => Velocity_Seed::uid( $post_id, 'people-query' ),
				'id'       => (int) $query_id,
			)
		);

		return Velocity_Home::section(
			$post_id,
			'people',
			self::header(
				$post_id,
				'people',
				'The People Moving Deals Forward',
				'Our sales directors lead the 100+ account executives who work on broker deals. Learn who they are and the experience they bring.',
				1021,
				Velocity_Seed::color( 'palette6' ),
				740
			) . "\n\n" . $list,
			'The people',
			array(
				'bgColor'   => '#f2f4fc',
				'anchor'    => self::TEAM_ANCHOR,
				// Centers a last line of people that is not full. See style.css.
				'className' => 'vm-people',
			)
		);
	}

	/**
	 * Careers: the open positions in a row that scrolls sideways.
	 *
	 * The list is the Kadence query "About Us: open positions". vm-carousel
	 * adds the two arrows when the positions do not fit. See
	 * assets/js/carousel.js.
	 *
	 * @param int $post_id  Page ID.
	 * @param int $query_id ID of the open positions query.
	 * @return string
	 */
	public static function careers( $post_id, $query_id ) {
		$list = Velocity_Seed::block(
			'kadence/query',
			array(
				'uniqueID' => Velocity_Seed::uid( $post_id, 'careers-query' ),
				'id'       => (int) $query_id,
			)
		);

		$inner =
			Velocity_Home::t(
				$post_id,
				'careers-title',
				'Careers at Velocity',
				array(
					'tag'    => 'h2',
					'margin' => array( '0', '', '24', '' ),
					'class'  => 'is-style-vm-reveal-lines',
				)
			) . "\n\n" .
			Velocity_Home::subtitle(
				$post_id,
				'careers',
				'We are rapidly growing our company and adding more lending professionals. Browse our open positions to find your fit.',
				array( 'margin' => array( '0', '', '38', '' ) )
			) . "\n\n" .
			Velocity_Seed::column(
				$post_id,
				'careers-carousel',
				$list . "\n\n" .
				Velocity_Home::buttons( $post_id, 'careers', array( array( 'Open Positions', home_url( '/careers/' ) ) ), 'left' ),
				array(
					'className'      => 'vm-carousel',
					'rowGapVariable' => array( 'custom', '', '' ),
					'rowGap'         => array( 24, '', '' ),
					'metadata'       => array( 'name' => 'Open positions' ),
				)
			);

		return Velocity_Home::section(
			$post_id,
			'careers',
			$inner,
			'Careers',
			array(
				'padding'       => array( '96', '', '48', '' ),
				'mobilePadding' => array( '64', '', '32', '' ),
			)
		);
	}

	/**
	 * The call to action band that closes the page.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function cta( $post_id ) {
		return Velocity_Home::cta_band(
			$post_id,
			'Want to Get Your Deals Going With Us?',
			array( array( 'Become a Partner', home_url( '/become-a-partner/' ) ) ),
			// As wide as the page content, so the title fits on one line.
			1280
		);
	}

	/**
	 * Every section, in page order.
	 *
	 * @param int   $post_id Page ID.
	 * @param int[] $queries IDs of the Kadence queries, by their key in queries().
	 * @return string
	 */
	public static function content( $post_id, $queries ) {
		return implode(
			"\n\n",
			array(
				self::hero( $post_id ),
				self::mission( $post_id ),
				self::values( $post_id ),
				self::story( $post_id ),
				self::brokerages( $post_id ),
				self::people( $post_id, $queries['people'] ),
				self::careers( $post_id, $queries['careers'] ),
				self::cta( $post_id ),
			)
		);
	}
}
