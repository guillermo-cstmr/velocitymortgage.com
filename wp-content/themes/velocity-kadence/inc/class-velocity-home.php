<?php
/**
 * Builds the home page sections as block content.
 *
 * Like the header and footer, this only produces the first version of the
 * page. After `wp velocity seed-home` the page is edited in the block editor.
 *
 * Each section is a Kadence row. Text uses core heading and paragraph blocks.
 * Visual components that repeat across the site (cards, tags, panels) get a
 * CSS class and are styled in style.css, so they stay consistent wherever
 * they are used. The classes are listed in docs/components.md.
 *
 * Figma: page "Homepage", frame "velocity-homepage-design".
 *
 * Kept compatible with PHP 7.4, see class-velocity-cli.php.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Home page seed content.
 */
class Velocity_Home {

	/**
	 * A core heading block.
	 *
	 * @param string $text  Heading text. May contain inline HTML.
	 * @param int    $level Heading level.
	 * @param string $align Text alignment, or empty.
	 * @param string $class Extra CSS class, or empty.
	 * @return string
	 */
	private static function heading( $text, $level, $align = '', $class = '' ) {
		$attrs   = array();
		$classes = array( 'wp-block-heading' );

		if ( $align ) {
			$attrs['textAlign'] = $align;
			$classes[]          = 'has-text-align-' . $align;
		}

		if ( 2 !== $level ) {
			$attrs['level'] = $level;
		}

		if ( $class ) {
			$attrs['className'] = $class;
			$classes[]          = $class;
		}

		return Velocity_Seed::block(
			'heading',
			$attrs,
			sprintf( '<h%1$d class="%2$s">%3$s</h%1$d>', $level, esc_attr( implode( ' ', $classes ) ), $text )
		);
	}

	/**
	 * A core paragraph block with an optional class.
	 *
	 * @param string $text  Text. May contain inline HTML.
	 * @param string $align Text alignment, or empty.
	 * @param string $class Extra CSS class, or empty.
	 * @return string
	 */
	private static function text( $text, $align = '', $class = '' ) {
		$attrs   = array();
		$classes = array();

		if ( $align ) {
			$attrs['align'] = $align;
			$classes[]      = 'has-text-align-' . $align;
		}

		if ( $class ) {
			$attrs['className'] = $class;
			$classes[]          = $class;
		}

		return Velocity_Seed::block(
			'paragraph',
			$attrs,
			sprintf( '<p%1$s>%2$s</p>', $classes ? ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' : '', $text )
		);
	}

	/**
	 * An image shipped with the theme, as a core image block.
	 *
	 * @param string $file  File name in assets/images/home.
	 * @param int    $width Width in px.
	 * @param string $alt   Alternative text. Empty for decorative images.
	 * @return string
	 */
	private static function image( $file, $width, $alt = '' ) {
		return Velocity_Seed::block(
			'image',
			array(
				'width'           => $width . 'px',
				'sizeSlug'        => 'full',
				'linkDestination' => 'none',
			),
			sprintf(
				'<figure class="wp-block-image size-full is-resized"><img src="%1$s" alt="%2$s" style="width:%3$dpx"/></figure>',
				esc_url( get_stylesheet_directory_uri() . '/assets/images/home/' . $file ),
				esc_attr( $alt ),
				$width
			)
		);
	}

	/**
	 * A group of Kadence buttons, each with an arrow.
	 *
	 * @param int     $post_id Page ID.
	 * @param string  $key     Key for the unique IDs.
	 * @param array[] $buttons Each: label, URL, style (fill or outline), extra class.
	 * @param string  $align   left or center.
	 * @return string
	 */
	private static function buttons( $post_id, $key, $buttons, $align = 'center' ) {
		$wrap  = Velocity_Seed::uid( $post_id, $key . 'btns' );
		$inner = array();

		foreach ( $buttons as $index => $button ) {
			$attrs = array(
				'uniqueID' => Velocity_Seed::uid( $post_id, $key . 'btn' . $index ),
				'text'     => $button[0],
				'link'     => $button[1],
				'icon'     => 'fe_arrowRight',
				'iconSide' => 'right',
			);

			if ( ! empty( $button[2] ) && 'fill' !== $button[2] ) {
				$attrs['inheritStyles'] = $button[2];
			}

			if ( ! empty( $button[3] ) ) {
				$attrs['className'] = $button[3];
			}

			$inner[] = Velocity_Seed::block( 'kadence/singlebtn', $attrs );
		}

		$attrs = array( 'uniqueID' => $wrap );
		if ( 'center' !== $align ) {
			$attrs = array(
				'hAlign'   => $align,
				'uniqueID' => $wrap,
			);
		}

		return Velocity_Seed::block(
			'kadence/advancedbtn',
			$attrs,
			'<div class="wp-block-kadence-advancedbtn kb-buttons-wrap kb-btns' . $wrap . '">' . implode( "\n\n", $inner ) . '</div>'
		);
	}

	/**
	 * A centered section header: title and intro, in a narrow row.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $title   Title. May contain inline HTML.
	 * @param string $intro   Intro text, or empty.
	 * @param int    $width   Max width in px.
	 * @return string
	 */
	private static function section_header( $post_id, $key, $title, $intro = '', $width = 846 ) {
		$inner = self::heading( $title, 2, 'center' );

		if ( $intro ) {
			$inner .= "\n\n" . self::text( $intro, 'center', 'vm-subtitle' );
		}

		return Velocity_Seed::row(
			$post_id,
			$key . '-header',
			array( Velocity_Seed::column( $post_id, $key . '-header-col', $inner ) ),
			array(
				'maxWidth' => $width,
				'padding'  => array( '0', '', '48', '' ),
				'metadata' => array( 'name' => 'Section header' ),
			)
		);
	}

	/**
	 * A full-width section wrapper.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $inner   Section content.
	 * @param string $name    Name shown in the editor's list view.
	 * @param array  $attrs   Extra row attributes.
	 * @return string
	 */
	private static function section( $post_id, $key, $inner, $name, $attrs = array() ) {
		return Velocity_Seed::row(
			$post_id,
			$key,
			array( Velocity_Seed::column( $post_id, $key . '-col', $inner ) ),
			array_merge(
				array(
					'align'           => 'full',
					'bgColor'         => 'palette9',
					'inheritMaxWidth' => true,
					'padding'         => array( '96', '', '96', '' ),
					'metadata'        => array( 'name' => $name ),
				),
				$attrs
			)
		);
	}

	/**
	 * Hero: headline, intro and the main call to action over the blue image.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function hero( $post_id ) {
		$inner =
			self::heading( 'More Ways to <br><strong>Get to “Yes”</strong>', 1, 'center' ) . "\n\n" .
			self::text( 'Velocity Mortgage Capital is a direct portfolio lender for investment properties with over 20 years of experience. When deals get complicated, we help you find a way forward.', 'center', 'vm-subtitle' ) . "\n\n" .
			self::buttons( $post_id, 'hero', array( array( 'Become a Partner', home_url( '/become-a-partner/' ) ) ) );

		return Velocity_Seed::row(
			$post_id,
			'hero',
			array( Velocity_Seed::column( $post_id, 'hero-col', $inner ) ),
			array(
				'align'             => 'full',
				'bgColor'           => 'palette3',
				'bgImg'             => get_stylesheet_directory_uri() . '/assets/images/home/hero-background.jpg',
				'bgImgSize'         => 'cover',
				'bgImgPosition'     => 'center center',
				'overlay'           => 'palette3',
				'overlayOpacity'    => 35,
				'minHeight'         => 593,
				'verticalAlignment' => 'middle',
				'maxWidth'          => 978,
				'textColor'         => 'palette9',
				'padding'           => array( '80', '', '80', '' ),
				'className'         => 'vm-hero',
				'metadata'          => array( 'name' => 'Hero' ),
			)
		);
	}

	/**
	 * Trust strip: five numbers on the orange band.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function stats( $post_id ) {
		$stats = array(
			array( '$2B+', 'Annual lending volume' ),
			array( '$13.8B+', 'Funded since 2004' ),
			array( '3,000+', 'Approved broker partners' ),
			array( '20+ years', 'Investor property lending' ),
			array( 'NYSE: VEL', 'Publicly traded parent' ),
		);

		$columns = array();
		foreach ( $stats as $index => $stat ) {
			$attrs = array( 'textAlign' => array( 'center', '', '' ) );
			if ( $index ) {
				$attrs['id'] = $index + 1;
			}

			$columns[] = Velocity_Seed::column(
				$post_id,
				'stat-' . $index,
				self::text( $stat[0], '', 'vm-stat-number' ) . "\n\n" . self::text( $stat[1], '', 'vm-stat-label' ),
				$attrs
			);
		}

		return Velocity_Seed::row(
			$post_id,
			'stats',
			$columns,
			array(
				'align'        => 'full',
				'bgColor'      => 'palette10',
				'textColor'    => 'palette3',
				'maxWidth'     => 1022,
				'tabletLayout' => 'three-grid',
				'mobileLayout' => 'two-grid',
				'padding'      => array( '32', '', '32', '' ),
				'className'    => 'vm-stats',
				'metadata'     => array( 'name' => 'Trust strip' ),
			)
		);
	}

	/**
	 * Why Velocity: section header, six cards and the closing line.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function why( $post_id ) {
		$cards = array(
			array( 'icon-join.svg', 'Client Relationships That Last for Decades', 'We lend out for 30 years, so the client you place today stays yours for the long run.' ),
			array( 'icon-approval-delegation.svg', 'Stable Funding in Shifting Markets', 'Our publicly held model keeps our funding steady—over $11B lent since 2004.' ),
			array( 'icon-directions-alt.svg', 'Direct Answers From Decision Makers', 'We lend and hold our own money, which means you never deal with intermediaries.' ),
			array( 'icon-steppers.svg', 'Every Deal Sets Up the Next One', 'As your clients grow their portfolios, every new property is an opportunity for you.' ),
			array( 'icon-trending-up.svg', 'Tools That Keep Your Business Growing', 'Put your own brand on flyers and brochures to make your marketing go further.' ),
			array( 'icon-check-box.svg', 'Say “Yes” to Self-Employed Clients', 'Place people based on their property’s value and income, not tax returns.' ),
		);

		$grids = array();
		foreach ( array_chunk( $cards, 3 ) as $row_index => $row_cards ) {
			$columns = array();
			foreach ( $row_cards as $index => $card ) {
				$attrs = array(
					'textAlign' => array( 'center', '', '' ),
					'className' => 'vm-card',
				);
				if ( $index ) {
					$attrs['id'] = $index + 1;
				}

				$columns[] = Velocity_Seed::column(
					$post_id,
					'why-card-' . $row_index . '-' . $index,
					self::image( $card[0], 35 ) . "\n\n" .
					self::heading( $card[1], 3, '', 'vm-h3-small' ) . "\n\n" .
					self::text( $card[2] ),
					$attrs
				);
			}

			$grids[] = Velocity_Seed::row(
				$post_id,
				'why-grid-' . $row_index,
				$columns,
				array(
					'columnGutter' => 'custom',
					'customGutter' => array( 24, '', '' ),
					'tabletLayout' => 'row',
					'mobileLayout' => 'row',
					'padding'      => array( '0', '', '24', '' ),
					'metadata'     => array( 'name' => 'Cards row ' . ( $row_index + 1 ) ),
				)
			);
		}

		return self::section(
			$post_id,
			'why',
			self::section_header(
				$post_id,
				'why',
				'How We Help Brokers Win',
				'Where banks get stuck, Velocity keeps moving. By lending and holding our own money, we offer the flexibility you need alongside the stability you expect.'
			) . "\n\n" . implode( "\n\n", $grids ) . "\n\n" .
			self::heading( 'We help brokers move forward when others hit limits.', 3, 'center', 'vm-closing-line' ),
			'How We Help Brokers Win'
		);
	}

	/**
	 * Programs: four programs, text and image alternating sides.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function programs( $post_id ) {
		$programs = array(
			array( 'Long-term loan', '30-Year Fixed, Built to Buy and Hold', 'Buy, refinance, or cash out on residential or commercial property with a rate that never changes.', 'Explore 30-Year Fixed', 'program-30-year-fixed.webp', 521, 'A broker smiling during a meeting' ),
			array( 'Long-term loan', 'Fast50, a Yes When Credit Is the Problem', 'Like the 30-Year Fixed, but with looser credit rules. Perfect for clients with plenty of equity.', 'Explore Fast50', 'program-fast50.webp', 542, 'A hand holding a set of house keys' ),
			array( 'Short-term loan', 'Flex I/O, a Bridge Loan Between Projects', 'Interest-only payments for 24 months, with no prepay penalty. Available on 1-4 unit properties.', 'Explore Flex I/O', 'program-flex-io.webp', 542, 'Two people reviewing documents at a table' ),
			array( 'Long-term loan', 'Super Jumbo, Large-Balance Financing', 'The 30-Year Fixed for luxury property, $2MM to $10MM, for seasoned investors in major metro areas.', 'Explore Super Jumbo', 'program-super-jumbo.webp', 542, 'Two men shaking hands across a desk' ),
		);

		$rows = array();
		foreach ( $programs as $index => $program ) {
			// The first program is the featured one: image left, inside a white card.
			$image_first = 0 === $index || 2 === $index;

			$text = Velocity_Seed::column(
				$post_id,
				'program-text-' . $index,
				self::text( $program[0], '', 'vm-eyebrow' ) . "\n\n" .
				self::heading( $program[1], 3 ) . "\n\n" .
				self::text( $program[2], '', 'vm-subtitle' ) . "\n\n" .
				self::buttons( $post_id, 'program-' . $index, array( array( $program[3], '#', 'outline' ) ), 'left' ),
				array(
					'id'                => $image_first ? 2 : 1,
					'verticalAlignment' => 'middle',
					'className'         => 'vm-program-text',
				)
			);

			$image = Velocity_Seed::column(
				$post_id,
				'program-image-' . $index,
				self::image( $program[4], $program[5], $program[6] ),
				array(
					'id'                => $image_first ? 1 : 2,
					'verticalAlignment' => 'middle',
					'className'         => 'vm-program-image',
				)
			);

			$rows[] = Velocity_Seed::row(
				$post_id,
				'program-' . $index,
				$image_first ? array( $image, $text ) : array( $text, $image ),
				array(
					'maxWidth'          => 1060,
					'verticalAlignment' => 'middle',
					'columnGutter'      => 'custom',
					'customGutter'      => array( 64, 32, '' ),
					'tabletLayout'      => 'row',
					'mobileLayout'      => 'row',
					'padding'           => array( '0', '', '80', '' ),
					'className'         => 0 === $index ? 'vm-program vm-program--featured' : 'vm-program',
					'metadata'          => array( 'name' => 'Program: ' . $program[1] ),
				)
			);
		}

		$inner =
			self::section_header(
				$post_id,
				'programs',
				'One Lender, <br>Four Programs to Grow',
				'Long-term financing meets the flexibility of private lending, available on anything from single rentals to commercial buildings nationwide.'
			) . "\n\n" . implode( "\n\n", $rows ) . "\n\n" .
			self::buttons( $post_id, 'programs-all', array( array( 'See Programs Side by Side', home_url( '/mortgage-programs/' ) ) ) ) . "\n\n" .
			self::text( 'Foreign borrowers are eligible for every program, but borrowing limits may vary. See program pages for details.', 'center', 'vm-note' );

		return self::section(
			$post_id,
			'programs',
			$inner,
			'Programs',
			array(
				'bgColor'   => '#f2f4fc',
				'padding'   => array( '112', '', '112', '' ),
				'className' => 'vm-panel',
			)
		);
	}

	/**
	 * Calculator: header, a placeholder for the calculator, and the follow-up.
	 *
	 * The calculator itself is custom development and is not built yet.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function calculator( $post_id ) {
		$inner =
			self::section_header(
				$post_id,
				'calculator',
				'Grow Your Book With <br><strong>the Right Program</strong>',
				'Our loan scenario calculator shows which program works best for you. Enter your deal’s numbers—we’ll do the rest.'
			) . "\n\n" .
			self::text( 'Loan scenario calculator: to be built. This block marks where it goes.', 'center', 'vm-placeholder' ) . "\n\n" .
			self::text( 'Got your answer? An account executive is ready to work the deal with you.', 'center', 'vm-note vm-note--rust' ) . "\n\n" .
			self::buttons( $post_id, 'calculator', array( array( 'Bring Us Your Deal', home_url( '/contact-us/' ) ) ) );

		return self::section( $post_id, 'calculator', $inner, 'Calculator' );
	}

	/**
	 * Steps: how a broker works with Velocity, in five steps.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function steps( $post_id ) {
		$steps = array(
			array( 'Register on <br>Our Site', 'Fill out a short form and we’ll reach out within 24 to 48 hours.' ),
			array( 'Talk to an Account Executive', 'Your account executive walks you through our programs and what approval will take.' ),
			array( 'Bring a Deal to Get Approved', 'One round of paperwork to your broker of record, and the approval lasts a year.' ),
			array( 'Submit the Deal for Processing', 'You get loan terms first, then the list of documents we need to move forward.' ),
			array( 'Close the Loan in 2 Weeks to 45 Days', 'The faster you send us the requested documents, the faster we’ll fund the loan.' ),
		);

		$columns = array();
		foreach ( $steps as $index => $step ) {
			$attrs = array(
				'textAlign' => array( 'center', '', '' ),
				'className' => 'vm-step',
			);
			if ( $index ) {
				$attrs['id'] = $index + 1;
			}

			$columns[] = Velocity_Seed::column(
				$post_id,
				'step-' . $index,
				self::text( (string) ( $index + 1 ), '', 'vm-step-number' ) . "\n\n" .
				self::heading( $step[0], 3, '', 'vm-h3-small' ) . "\n\n" .
				self::text( $step[1] ),
				$attrs
			);
		}

		$inner =
			self::section_header(
				$post_id,
				'steps',
				'How Brokers Grow With Velocity',
				'We keep it simple. Register with us in minutes and hear back within 48 hours. Then just bring your deal for approval and we’ll help you close.',
				1055
			) . "\n\n" .
			Velocity_Seed::row(
				$post_id,
				'steps-grid',
				$columns,
				array(
					'columnGutter' => 'custom',
					'customGutter' => array( 16, '', '' ),
					'tabletLayout' => 'three-grid',
					'mobileLayout' => 'row',
					'padding'      => array( '0', '', '48', '' ),
					'metadata'     => array( 'name' => 'Steps' ),
				)
			) . "\n\n" .
			self::buttons( $post_id, 'steps', array( array( 'See It Step-by-Step', '#', 'outline', 'vm-btn-on-dark' ) ) );

		return self::section(
			$post_id,
			'steps',
			$inner,
			'How Brokers Grow With Velocity',
			array(
				'bgColor'   => 'palette3',
				'textColor' => 'palette9',
				'padding'   => array( '112', '', '112', '' ),
				'className' => 'vm-panel vm-panel--gradient',
			)
		);
	}

	/**
	 * Deals: the three latest funded properties, read from the properties.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function deals( $post_id ) {
		$card = Velocity_Seed::block(
			'group',
			array( 'className' => 'vm-deal-card' ),
			'<div class="wp-block-group vm-deal-card">' .
			Velocity_Seed::block(
				'post-featured-image',
				array(
					'isLink'      => true,
					'aspectRatio' => '16/10',
				)
			) . "\n\n" .
			Velocity_Seed::block(
				'post-terms',
				array(
					'term'      => 'velocity-property-type',
					'className' => 'vm-h3-small',
				)
			) . "\n\n" .
			Velocity_Seed::block(
				'post-terms',
				array(
					'term'      => 'velocity-property-program',
					'className' => 'vm-deal-program',
				)
			) . "\n\n" .
			Velocity_Seed::block( 'post-title', array( 'level' => 3, 'isLink' => true, 'className' => 'vm-deal-location' ) ) .
			'</div>'
		);

		$query = Velocity_Seed::block(
			'query',
			array(
				'queryId'   => 1,
				'query'     => array(
					'perPage'  => 3,
					'pages'    => 0,
					'offset'   => 0,
					'postType' => 'velocity-property',
					'order'    => 'desc',
					'orderBy'  => 'date',
					'author'   => '',
					'search'   => '',
					'exclude'  => array(),
					'sticky'   => '',
					'inherit'  => false,
				),
				'className' => 'vm-deals',
			),
			'<div class="wp-block-query vm-deals">' .
			Velocity_Seed::block(
				'post-template',
				array(
					'layout' => array(
						'type'        => 'grid',
						'columnCount' => 3,
					),
				),
				$card
			) . '</div>'
		);

		$inner =
			self::section_header(
				$post_id,
				'deals',
				'Deals Other Brokers Already Placed',
				'See how Velocity has helped brokers close everything from fourplexes to mixed-use buildings to warehouses, and more.'
			) . "\n\n" . $query . "\n\n" .
			self::buttons( $post_id, 'deals', array( array( 'Explore More Closed Deals', home_url( '/featured-investment-property-loans/' ) ) ) );

		return self::section( $post_id, 'deals', $inner, 'Closed deals', array( 'padding' => array( '96', '', '48', '' ) ) );
	}

	/**
	 * Resources: three cards that lead to the resource areas.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function resources( $post_id ) {
		$cards = array(
			array( 'Broker Toolkit', 'Forms, Flyers, and Marketing Tools', 'Everything you need to fund deals, educate borrowers, and win more clients.', home_url( '/broker-toolkit/' ) ),
			array( 'Blog', 'News and Insights For Brokers', 'What’s trending in investor lending, from the people who make these loans every day.', home_url( '/insights/' ) ),
			array( 'Case Studies', 'Case Studies and Success Stories', 'How brokers like you placed the hard deals, and what winning looked like.', '#' ),
		);

		$columns = array();
		foreach ( $cards as $index => $card ) {
			$attrs = array( 'className' => 'vm-card vm-card--left' );
			if ( $index ) {
				$attrs['id'] = $index + 1;
			}

			$columns[] = Velocity_Seed::column(
				$post_id,
				'resource-' . $index,
				self::text( $card[0], '', 'vm-tag' ) . "\n\n" .
				self::heading( $card[1], 3, '', 'vm-h6' ) . "\n\n" .
				self::text( $card[2] ) . "\n\n" .
				self::buttons( $post_id, 'resource-' . $index, array( array( 'Learn More', $card[3] ) ), 'left' ),
				$attrs
			);
		}

		$inner =
			self::section_header(
				$post_id,
				'resources',
				'Resources to Keep Your Brokerage Growing',
				'Find insights you’ll want to share with investors, get tips from seasoned lenders, and read how other brokers are building their business.'
			) . "\n\n" .
			Velocity_Seed::row(
				$post_id,
				'resources-grid',
				$columns,
				array(
					'columnGutter' => 'custom',
					'customGutter' => array( 24, '', '' ),
					'tabletLayout' => 'row',
					'mobileLayout' => 'row',
					'padding'      => array( '0', '', '0', '' ),
					'metadata'     => array( 'name' => 'Resource cards' ),
				)
			);

		return self::section( $post_id, 'resources', $inner, 'Resources', array( 'padding' => array( '48', '', '72', '' ) ) );
	}

	/**
	 * Final call to action, on the dark blue image.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function cta( $post_id ) {
		$band = Velocity_Seed::row(
			$post_id,
			'cta-band',
			array(
				Velocity_Seed::column(
					$post_id,
					'cta-band-col',
					self::heading( 'Ready to Close More Deals?', 2, 'center' ) . "\n\n" .
					self::text( 'Register today and hear back within 48 hours. One signed package approves you for a year. Start closing the deals banks turn down.', 'center', 'vm-subtitle' ) . "\n\n" .
					self::buttons(
						$post_id,
						'cta',
						array(
							array( 'Become a Partner', home_url( '/become-a-partner/' ) ),
							array( 'Start a Conversation', home_url( '/contact-us/' ), 'outline', 'vm-btn-on-dark' ),
						)
					) . "\n\n" .
					self::text( 'Join 3,000+ approved broker partners already placing deals with Velocity.', 'center', 'vm-cta-note' )
				),
			),
			array(
				'bgColor'       => 'palette3',
				'bgImg'         => get_stylesheet_directory_uri() . '/assets/images/home/cta-background.jpg',
				'bgImgSize'     => 'cover',
				'bgImgPosition' => 'center center',
				'maxWidth'      => 1040,
				'textColor'     => 'palette9',
				'padding'       => array( '112', '', '112', '' ),
				'className'     => 'vm-cta',
				'metadata'      => array( 'name' => 'Call to action band' ),
			)
		);

		return self::section( $post_id, 'cta', $band, 'Final call to action' );
	}

	/**
	 * Frequently asked questions, as native details blocks.
	 *
	 * Only the first answer is in the design. The rest are placeholders.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function faq( $post_id ) {
		$pending = 'Answer to be provided.';

		$faqs = array(
			array(
				'Is Velocity a bank or a private lender?',
				array(
					'Neither. Velocity is a direct portfolio lender. That means we lend out and hold only our own money, allowing us to offer 30-year financing with the flexibility brokers expect from a private lender.',
					'Private lenders (what we call hard money lenders) only lend for 12 to 24 months, after which the client has to find a new loan, while banks turn down deals that don’t fit their rules. We take the best of both so your client gets the long-term loan they want and you retain your relationship.',
				),
			),
			array( 'Who can work with Velocity?', array( $pending ) ),
			array( 'Which states need a broker license?', array( $pending ) ),
			array( 'What is a business purpose loan?', array( $pending ) ),
			array( 'What is a wholesale lender, and is Velocity one?', array( $pending ) ),
		);

		$items = array();
		foreach ( $faqs as $index => $faq ) {
			$answers = array();
			foreach ( $faq[1] as $paragraph ) {
				$answers[] = self::text( $paragraph );
			}

			$attrs = array( 'className' => 'vm-faq' );
			if ( 0 === $index ) {
				$attrs = array(
					'showContent' => true,
					'className'   => 'vm-faq',
				);
			}

			$items[] = Velocity_Seed::block(
				'details',
				$attrs,
				'<details class="wp-block-details vm-faq"' . ( 0 === $index ? ' open' : '' ) . '><summary>' . esc_html( $faq[0] ) . '</summary>' . implode( "\n\n", $answers ) . '</details>'
			);
		}

		return self::section(
			$post_id,
			'faq',
			self::section_header( $post_id, 'faq', 'Frequently Asked Questions' ) . "\n\n" . implode( "\n\n", $items ),
			'Frequently asked questions',
			array( 'padding' => array( '48', '', '96', '' ) )
		);
	}

	/**
	 * Every section, in page order.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function content( $post_id ) {
		return implode(
			"\n\n",
			array(
				self::hero( $post_id ),
				self::stats( $post_id ),
				self::why( $post_id ),
				self::programs( $post_id ),
				self::calculator( $post_id ),
				self::steps( $post_id ),
				self::deals( $post_id ),
				self::resources( $post_id ),
				self::cta( $post_id ),
				self::faq( $post_id ),
			)
		);
	}
}
