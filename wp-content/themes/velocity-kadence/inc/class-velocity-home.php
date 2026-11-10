<?php
/**
 * Builds the home page sections as block content.
 *
 * Like the header and footer, this only produces the first version of the
 * page. After `wp velocity seed-home` the page is edited in the block editor.
 *
 * The rule followed here, and the one to follow when building other pages:
 * whatever the block settings can express is set ON THE BLOCK, so it shows up
 * in the editor sidebar and anyone can change it. Borders, radius, shadows,
 * padding, backgrounds, alignment and text sizes are all block settings.
 * CSS classes are kept for the few things the sidebar cannot do. They are
 * listed in docs/components.md.
 *
 * Blocks used: Kadence Row Layout and Section for structure, Kadence Text
 * (Adv) for text, Kadence Image, Kadence Advanced Button.
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
	 * Card shadow at rest. Figma effect "medium".
	 *
	 * @return array
	 */
	private static function shadow() {
		return array(
			array(
				'color'   => '#000000',
				'opacity' => 0.08,
				'spread'  => -4,
				'blur'    => 16,
				'hOffset' => 0,
				'vOffset' => 12,
				'inset'   => false,
			),
		);
	}

	/**
	 * Card shadow on hover: deeper, with a navy tint.
	 *
	 * @return array
	 */
	private static function shadow_hover() {
		return array(
			array(
				'color'   => '#010739',
				'opacity' => 0.18,
				'spread'  => -8,
				'blur'    => 32,
				'hOffset' => 0,
				'vOffset' => 24,
				'inset'   => false,
			),
		);
	}

	/**
	 * A one pixel border on all sides, in Kadence's format.
	 *
	 * @param string $color Border color.
	 * @return array
	 */
	private static function border( $color ) {
		$side = array( $color, '', 1 );

		return array(
			array(
				'top'    => $side,
				'right'  => $side,
				'bottom' => $side,
				'left'   => $side,
				'unit'   => 'px',
			),
		);
	}

	/**
	 * Settings of a card: a Kadence Section with border, radius and shadow.
	 *
	 * @param array $overrides Settings that differ from the default card.
	 * @return array
	 */
	private static function card( $overrides = array() ) {
		return array_merge(
			array(
				'background'         => Velocity_Seed::color( 'palette9' ),
				'borderStyle'        => self::border( '#e5e5e5' ),
				'borderHoverStyle'   => self::border( 'rgba(43,53,120,0.24)' ),
				'borderRadius'       => array( 8, 8, 8, 8 ),
				'padding'            => array( '32', '32', '32', '32' ),
				'displayShadow'      => true,
				'shadow'             => self::shadow(),
				'displayHoverShadow' => true,
				'shadowHover'        => self::shadow_hover(),
				// A custom gap between the blocks stacked inside the card.
				'rowGapVariable'     => array( 'custom', '', '' ),
				'rowGap'             => array( 16, '', '' ),
			),
			$overrides
		);
	}

	/**
	 * A Kadence Text (Adv) block.
	 *
	 * Options: tag (h1 to h6, or p), align, size [desktop, tablet, mobile],
	 * weight, lh (unitless line height), color and background (palette slug or
	 * any CSS color), margin and padding [top, right, bottom, left], transform,
	 * family ("heading" for the heading font), width (max width in px), class.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $text    Text. May contain inline HTML.
	 * @param array  $o       Options.
	 * @return string
	 */
	private static function t( $post_id, $key, $text, $o = array() ) {
		$uid     = Velocity_Seed::uid( $post_id, 't-' . $key );
		$tag     = isset( $o['tag'] ) ? $o['tag'] : 'p';
		$attrs   = array( 'uniqueID' => $uid );
		$classes = array( 'kt-adv-heading' . $uid );

		if ( 'h' === $tag[0] ) {
			if ( 'h2' !== $tag ) {
				$attrs['level'] = (int) $tag[1];
			}
		} else {
			$attrs['htmlTag'] = $tag;
		}

		if ( ! empty( $o['class'] ) ) {
			$attrs['className'] = $o['class'];
			$classes[]          = $o['class'];
		}

		$classes[] = 'wp-block-kadence-advancedheading';

		if ( ! empty( $o['align'] ) ) {
			$attrs['align'] = $o['align'];
		}

		if ( ! empty( $o['color'] ) ) {
			$attrs['color'] = $o['color'];
		}

		if ( ! empty( $o['size'] ) ) {
			$attrs['fontSize'] = $o['size'];
		}

		if ( ! empty( $o['lh'] ) ) {
			$attrs['fontHeight']     = array( $o['lh'], '', '' );
			$attrs['fontHeightType'] = '-';
		}

		if ( ! empty( $o['weight'] ) ) {
			$attrs['fontWeight'] = (string) $o['weight'];
		}

		if ( ! empty( $o['family'] ) && 'heading' === $o['family'] ) {
			$attrs['typography'] = 'var( --global-heading-font-family, inherit )';
		}

		if ( ! empty( $o['transform'] ) ) {
			$attrs['textTransform'] = $o['transform'];
		}

		if ( isset( $o['margin'] ) ) {
			$attrs['margin'] = $o['margin'];
		}

		if ( isset( $o['width'] ) ) {
			$attrs['maxWidth'] = array( $o['width'], '', '' );
		}

		// A highlighted word drawn as a pill, using Kadence's text highlight.
		if ( ! empty( $o['pill'] ) ) {
			$attrs['markColor']        = Velocity_Seed::color( 'palette5' );
			$attrs['markBG']           = Velocity_Seed::color( 'palette7' );
			$attrs['markPadding']      = array( 4, 8, 4, 8 );
			$attrs['markBorderRadius'] = array( 5, 5, 5, 5 );
			$attrs['markFontWeight']   = '600';
			$text                      = '<mark class="kt-highlight">' . $text . '</mark>';
		}

		return Velocity_Seed::block(
			'kadence/advancedheading',
			$attrs,
			sprintf(
				'<%1$s class="%2$s" data-kb-block="kb-adv-heading%3$s">%4$s</%1$s>',
				$tag,
				esc_attr( implode( ' ', $classes ) ),
				$uid,
				$text
			)
		);
	}

	/**
	 * A section title: H2, centered.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $text    Title. May contain inline HTML.
	 * @return string
	 */
	private static function title( $post_id, $key, $text ) {
		return self::t(
			$post_id,
			$key . '-title',
			$text,
			array(
				'tag'    => 'h2',
				'align'  => 'center',
				'margin' => array( '0', '', '16', '' ),
			)
		);
	}

	/**
	 * Intro text under a title. Figma style "Subtitle".
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $text    Text.
	 * @param array  $o       Extra options for t().
	 * @return string
	 */
	private static function subtitle( $post_id, $key, $text, $o = array() ) {
		return self::t(
			$post_id,
			$key . '-subtitle',
			$text,
			array_merge(
				array(
					'size'   => array( 20, '', 18 ),
					'lh'     => 1.31,
					'margin' => array( '0', '', '0', '' ),
				),
				$o
			)
		);
	}

	/**
	 * Body text with no margin, for use inside cards.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $text    Text.
	 * @return string
	 */
	private static function body( $post_id, $key, $text ) {
		return self::t( $post_id, $key . '-body', $text, array( 'margin' => array( '0', '', '0', '' ) ) );
	}

	/**
	 * A card or step title: H3 at 22px bold. Figma style "H3 Small".
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $text    Title.
	 * @param array  $o       Extra options for t().
	 * @return string
	 */
	private static function card_title( $post_id, $key, $text, $o = array() ) {
		return self::t(
			$post_id,
			$key . '-title',
			$text,
			array_merge(
				array(
					'tag'    => 'h3',
					'size'   => array( 22, '', '' ),
					'weight' => '700',
					'margin' => array( '0', '', '0', '' ),
				),
				$o
			)
		);
	}

	/**
	 * A Kadence Image block showing a file shipped with the theme.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $file    File name in assets/images/home.
	 * @param int    $width   Max width in px.
	 * @param string $alt     Alternative text. Empty for decorative images.
	 * @return string
	 */
	private static function image( $post_id, $key, $file, $width, $alt = '' ) {
		$uid = Velocity_Seed::uid( $post_id, 'img-' . $key );

		return Velocity_Seed::block(
			'kadence/image',
			array(
				'imgMaxWidth'     => $width,
				'linkDestination' => 'none',
				'uniqueID'        => $uid,
				'marginDesktop'   => array( '0', '', '0', '' ),
			),
			sprintf(
				'<figure class="wp-block-kadence-image kb-image%1$s%2$s"><img src="%3$s" alt="%4$s" class="kb-img"/></figure>',
				$uid,
				'.svg' === substr( $file, -4 ) ? ' image-is-svg' : '',
				esc_url( get_stylesheet_directory_uri() . '/assets/images/home/' . $file ),
				esc_attr( $alt )
			)
		);
	}

	/**
	 * Settings of an outline button on a dark background: white instead of
	 * orange, turning solid white with navy text on hover.
	 *
	 * @return array
	 */
	private static function on_dark() {
		$white = Velocity_Seed::color( 'palette9' );

		return array(
			'color'            => $white,
			'colorHover'       => Velocity_Seed::color( 'palette3' ),
			'backgroundHover'  => $white,
			'borderStyle'      => self::border( $white ),
			'borderHoverStyle' => self::border( $white ),
		);
	}

	/**
	 * A group of Kadence buttons, each with an arrow.
	 *
	 * @param int     $post_id Page ID.
	 * @param string  $key     Key for the unique IDs.
	 * @param array[] $buttons Each: label, URL, style (fill or outline), extra settings.
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

			// Extra settings for this one button, such as its own colors.
			if ( ! empty( $button[3] ) ) {
				$attrs = array_merge( $attrs, $button[3] );
			}

			$inner[] = Velocity_Seed::block( 'kadence/singlebtn', $attrs );
		}

		$attrs = array( 'uniqueID' => $wrap );

		if ( 'center' !== $align ) {
			$attrs['hAlign'] = $align;
		}

		if ( count( $buttons ) > 1 ) {
			$attrs['gap'] = array( 'md', '', 'sm' );
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
		$inner = self::title( $post_id, $key, $title );

		if ( $intro ) {
			$inner .= "\n\n" . self::subtitle( $post_id, $key, $intro, array( 'align' => 'center' ) );
		}

		return Velocity_Seed::row(
			$post_id,
			$key . '-header',
			array( Velocity_Seed::column( $post_id, $key . '-header-col', $inner ) ),
			array(
				'maxWidth' => $width,
				'padding'  => array( '0', '', '56', '' ),
				'metadata' => array( 'name' => 'Section header' ),
			)
		);
	}

	/**
	 * A full-width section, with its content held to the site's content width.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $inner   Section content.
	 * @param string $name    Name shown in the editor's list view.
	 * @param array  $attrs   Extra row settings.
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
					'bgColor'         => Velocity_Seed::color( 'palette9' ),
					'inheritMaxWidth' => true,
					'padding'         => array( '96', '', '96', '' ),
					'mobilePadding'   => array( '64', '', '64', '' ),
					'metadata'        => array( 'name' => $name ),
				),
				$attrs
			)
		);
	}

	/**
	 * A panel: a rounded, colored area inset from the page edges.
	 *
	 * The outer row only provides the side gutters. The Section inside it
	 * carries the background, the radius and the padding. On mobile the
	 * gutters and the radius drop to zero, so the panel runs edge to edge.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $inner   Panel content.
	 * @param string $name    Name shown in the editor's list view.
	 * @param array  $surface Background settings for the inner Section.
	 * @return string
	 */
	private static function panel( $post_id, $key, $inner, $name, $surface ) {
		return Velocity_Seed::row(
			$post_id,
			$key,
			array(
				Velocity_Seed::column(
					$post_id,
					$key . '-col',
					$inner,
					array_merge(
						array(
							'borderRadius'       => array( 30, 30, 30, 30 ),
							'mobileBorderRadius' => array( 0, 0, 0, 0 ),
							'padding'            => array( '112', '64', '112', '64' ),
							'tabletPadding'      => array( '80', '32', '80', '32' ),
							'mobilePadding'      => array( '64', '20', '64', '20' ),
						),
						$surface
					)
				),
			),
			array(
				'align'         => 'full',
				'padding'       => array( '0', '32', '0', '32' ),
				'mobilePadding' => array( '0', '0', '0', '0' ),
				'metadata'      => array( 'name' => $name ),
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
			self::t(
				$post_id,
				'hero-title',
				'More Ways to <br><strong>Get to “Yes”</strong>',
				array(
					'tag'    => 'h1',
					'align'  => 'center',
					'margin' => array( '0', '', '24', '' ),
				)
			) . "\n\n" .
			self::subtitle(
				$post_id,
				'hero',
				'Velocity Mortgage Capital is a direct portfolio lender for investment properties with over 20 years of experience. When deals get complicated, we help you find a way forward.',
				array(
					'align'  => 'center',
					'margin' => array( '0', '', '40', '' ),
				)
			) . "\n\n" .
			self::buttons( $post_id, 'hero', array( array( 'Become a Partner', home_url( '/become-a-partner/' ) ) ) );

		return Velocity_Seed::row(
			$post_id,
			'hero',
			array( Velocity_Seed::column( $post_id, 'hero-col', $inner ) ),
			array(
				'align'             => 'full',
				'bgColor'           => Velocity_Seed::color( 'palette3' ),
				'bgImg'             => get_stylesheet_directory_uri() . '/assets/images/home/hero-background.jpg',
				'bgImgSize'         => 'cover',
				'bgImgPosition'     => 'center center',
				'overlay'           => Velocity_Seed::color( 'palette3' ),
				'overlayOpacity'    => 35,
				'minHeight'         => 593,
				'verticalAlignment' => 'middle',
				'maxWidth'          => 978,
				'textColor'         => Velocity_Seed::color( 'palette9' ),
				'padding'           => array( '80', '', '80', '' ),
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
				self::t(
					$post_id,
					'stat-number-' . $index,
					$stat[0],
					array(
						'family' => 'heading',
						'size'   => array( 24, '', '' ),
						'weight' => '700',
						'lh'     => 1.1,
						'margin' => array( '0', '', '4', '' ),
					)
				) . "\n\n" .
				self::t(
					$post_id,
					'stat-label-' . $index,
					$stat[1],
					array(
						'size'   => array( 14, '', '' ),
						'lh'     => 1.1,
						'margin' => array( '0', '', '0', '' ),
					)
				),
				$attrs
			);
		}

		// vm-stats: on tablet and mobile the items wrap centered. See components.md.
		return Velocity_Seed::row(
			$post_id,
			'stats',
			$columns,
			array(
				'align'        => 'full',
				'bgColor'      => Velocity_Seed::color( 'palette10' ),
				'textColor'    => Velocity_Seed::color( 'palette3' ),
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
				$key   = 'why-card-' . $row_index . '-' . $index;
				$attrs = self::card(
					array(
						'textAlign'         => array( 'center', '', '' ),
						'justifyContent'    => array( 'center', '', '' ),
						'verticalAlignment' => 'middle',
					)
				);
				if ( $index ) {
					$attrs['id'] = $index + 1;
				}

				$columns[] = Velocity_Seed::column(
					$post_id,
					$key,
					self::image( $post_id, $key, $card[0], 35 ) . "\n\n" .
					self::card_title( $post_id, $key, $card[1] ) . "\n\n" .
					self::body( $post_id, $key, $card[2] ),
					$attrs
				);
			}

			$grids[] = Velocity_Seed::row(
				$post_id,
				'why-grid-' . $row_index,
				$columns,
				array(
					'columnGutter'       => 'custom',
					'customGutter'       => array( 24, '', '' ),
					'columnsInnerHeight' => true,
					'tabletLayout'       => 'row',
					'mobileLayout'       => 'row',
					'padding'            => array( '0', '', '24', '' ),
					'metadata'           => array( 'name' => 'Cards row ' . ( $row_index + 1 ) ),
				)
			);
		}

		$closing = self::t(
			$post_id,
			'why-closing',
			'We help brokers move forward when others hit limits.',
			array(
				'tag'    => 'h3',
				'align'  => 'center',
				'color'  => Velocity_Seed::color( 'palette2' ),
				'size'   => array( 30, '', 22 ),
				'weight' => '600',
				'margin' => array( '40', '', '0', '' ),
			)
		);

		return self::section(
			$post_id,
			'why',
			self::section_header(
				$post_id,
				'why',
				'How We Help Brokers Win',
				'Where banks get stuck, Velocity keeps moving. By lending and holding our own money, we offer the flexibility you need alongside the stability you expect.'
			) . "\n\n" . implode( "\n\n", $grids ) . "\n\n" . $closing,
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
			$key         = 'program-' . $index;
			$featured    = 0 === $index;
			$image_first = 0 === $index || 2 === $index;

			$text = Velocity_Seed::column(
				$post_id,
				$key . '-text',
				// vm-eyebrow adds the orange dot. Everything else is block settings.
				self::t(
					$post_id,
					$key . '-eyebrow',
					$program[0],
					array(
						'size'      => array( 14, '', '' ),
						'lh'        => 1.1,
						'transform' => 'uppercase',
						'margin'    => array( '0', '', '24', '' ),
						'class'     => 'vm-eyebrow',
					)
				) . "\n\n" .
				self::t(
					$post_id,
					$key . '-title',
					$program[1],
					array(
						'tag'    => 'h3',
						'margin' => array( '0', '', '24', '' ),
					)
				) . "\n\n" .
				self::subtitle( $post_id, $key, $program[2], array( 'margin' => array( '0', '', '40', '' ) ) ) . "\n\n" .
				self::buttons( $post_id, $key, array( array( $program[3], '#', 'outline' ) ), 'left' ),
				array(
					'id'                => $image_first ? 2 : 1,
					'verticalAlignment' => 'middle',
					'padding'           => $featured ? array( '32', '24', '32', '0' ) : array( '', '', '', '' ),
					'tabletPadding'     => $featured ? array( '0', '24', '32', '24' ) : array( '', '', '', '' ),
				)
			);

			$image = Velocity_Seed::column(
				$post_id,
				$key . '-image',
				self::image( $post_id, $key, $program[4], $program[5], $program[6] ),
				array(
					'id'                => $image_first ? 1 : 2,
					'verticalAlignment' => 'middle',
				)
			);

			$row = Velocity_Seed::row(
				$post_id,
				$key,
				$image_first ? array( $image, $text ) : array( $text, $image ),
				array(
					'verticalAlignment' => 'middle',
					'columnGutter'      => 'custom',
					'customGutter'      => array( 64, 32, '' ),
					'tabletLayout'      => 'row',
					'mobileLayout'      => 'row',
					// On tablet and mobile the image always comes first.
					'collapseOrder'     => $image_first ? 'left-to-right' : 'right-to-left',
					'padding'           => array( '0', '', '0', '' ),
					'metadata'          => array( 'name' => 'Image and text' ),
				)
			);

			// The first program sits inside a white card.
			$wrapper = $featured
				? array(
					'background'   => Velocity_Seed::color( 'palette9' ),
					'borderStyle'  => self::border( 'rgba(43,53,120,0.24)' ),
					'borderRadius' => array( 16, 16, 16, 16 ),
				)
				: array();

			$rows[] = Velocity_Seed::row(
				$post_id,
				$key . '-wrap',
				array( Velocity_Seed::column( $post_id, $key . '-wrap-col', $row, $wrapper ) ),
				array(
					'maxWidth' => 1060,
					'padding'  => array( '0', '', '80', '' ),
					'metadata' => array( 'name' => 'Program: ' . $program[1] ),
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
			self::subtitle(
				$post_id,
				'programs-note',
				'Foreign borrowers are eligible for every program, but borrowing limits may vary. See program pages for details.',
				array(
					'align'  => 'center',
					'color'  => '#494949',
					'margin' => array( '20', '', '0', '' ),
				)
			);

		return self::panel( $post_id, 'programs', $inner, 'Programs', array( 'background' => '#f2f4fc' ) );
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
			// vm-placeholder draws the dashed box. Delete this block when the calculator exists.
			self::t(
				$post_id,
				'calculator-placeholder',
				'Loan scenario calculator: to be built. This block marks where it goes.',
				array(
					'align' => 'center',
					'class' => 'vm-placeholder',
				)
			) . "\n\n" .
			self::subtitle(
				$post_id,
				'calculator-note',
				'Got your answer? An account executive is ready to work the deal with you.',
				array(
					'align'  => 'center',
					'color'  => '#8a4b2e',
					'margin' => array( '0', '', '32', '' ),
				)
			) . "\n\n" .
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
			$key   = 'step-' . $index;
			$attrs = array( 'textAlign' => array( 'center', '', '' ) );
			if ( $index ) {
				$attrs['id'] = $index + 1;
			}

			$columns[] = Velocity_Seed::column(
				$post_id,
				$key,
				self::t(
					$post_id,
					$key . '-number',
					(string) ( $index + 1 ),
					array(
						'family' => 'heading',
						'color'  => Velocity_Seed::color( 'palette10' ),
						'size'   => array( 50, '', 40 ),
						'weight' => '300',
						'lh'     => 1.1,
						'margin' => array( '0', '', '24', '' ),
					)
				) . "\n\n" .
				self::card_title( $post_id, $key, $step[0], array( 'margin' => array( '0', '', '12', '' ) ) ) . "\n\n" .
				self::body( $post_id, $key, $step[1] ),
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
			self::buttons( $post_id, 'steps', array( array( 'See It Step-by-Step', '#', 'outline', self::on_dark() ) ) );

		return self::panel(
			$post_id,
			'steps',
			$inner,
			'How Brokers Grow With Velocity',
			array(
				'backgroundType' => 'gradient',
				'gradient'       => 'linear-gradient(341deg,var(--global-palette5) 24%,var(--global-palette3) 69%)',
				'textColor'      => Velocity_Seed::color( 'palette9' ),
			)
		);
	}

	/**
	 * Deals: the three latest funded properties, read from the properties.
	 *
	 * A Query Loop of core blocks. The card look comes from the classes
	 * vm-deals and vm-deal-card, see components.md.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function deals( $post_id ) {
		$card = Velocity_Seed::block(
			'group',
			array( 'className' => 'vm-deal-card' ),
			'<div class="wp-block-group vm-deal-card">' .
			// The photo: 160px tall, cropped to fill. Figma "deal-image-placeholder".
			Velocity_Seed::block(
				'post-featured-image',
				array(
					'height' => '160px',
					'scale'  => 'cover',
				)
			) . "\n\n" .
			// The text of the card. See vm_deal_info_shortcode() in inc/shortcodes.php.
			Velocity_Seed::block( 'shortcode', array(), '[vm_deal_info]' ) .
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

		return self::section(
			$post_id,
			'deals',
			$inner,
			'Closed deals',
			array(
				'padding'       => array( '96', '', '48', '' ),
				'mobilePadding' => array( '64', '', '32', '' ),
			)
		);
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
			$key   = 'resource-' . $index;
			$attrs = self::card(
				array(
					'background'        => Velocity_Seed::color( 'palette8' ),
					'borderStyle'       => self::border( '#ffffff' ),
					'padding'           => array( '36', '24', '36', '24' ),
					'rowGap'            => array( 24, '', '' ),
					'verticalAlignment' => 'bottom',
				)
			);
			if ( $index ) {
				$attrs['id'] = $index + 1;
			}

			$columns[] = Velocity_Seed::column(
				$post_id,
				$key,
				self::t(
					$post_id,
					$key . '-tag',
					$card[0],
					array(
						'size'   => array( 14, '', '' ),
						'lh'     => 1.5,
						'margin' => array( '0', '', '0', '' ),
						'pill'   => true,
					)
				) . "\n\n" .
				self::card_title( $post_id, $key, $card[1], array( 'size' => array( 24, '', '' ) ) ) . "\n\n" .
				self::body( $post_id, $key, $card[2] ) . "\n\n" .
				self::buttons( $post_id, $key, array( array( 'Learn More', $card[3] ) ), 'left' ),
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
					'columnGutter'       => 'custom',
					'customGutter'       => array( 24, '', '' ),
					'columnsInnerHeight' => true,
					'tabletLayout'       => 'row',
					'mobileLayout'       => 'row',
					'padding'            => array( '0', '', '0', '' ),
					'metadata'           => array( 'name' => 'Resource cards' ),
				)
			);

		return self::section(
			$post_id,
			'resources',
			$inner,
			'Resources',
			array(
				'padding'       => array( '48', '', '72', '' ),
				'mobilePadding' => array( '32', '', '48', '' ),
			)
		);
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
					self::title( $post_id, 'cta', 'Ready to Close More Deals?' ) . "\n\n" .
					self::subtitle(
						$post_id,
						'cta',
						'Register today and hear back within 48 hours. One signed package approves you for a year. Start closing the deals banks turn down.',
						array(
							'align'  => 'center',
							'width'  => 800,
							'margin' => array( '0', 'auto', '32', 'auto' ),
						)
					) . "\n\n" .
					self::buttons(
						$post_id,
						'cta',
						array(
							array( 'Become a Partner', home_url( '/become-a-partner/' ) ),
							array( 'Start a Conversation', home_url( '/contact-us/' ), 'outline', self::on_dark() ),
						)
					) . "\n\n" .
					self::t(
						$post_id,
						'cta-note',
						'Join 3,000+ approved broker partners already placing deals with Velocity.',
						array(
							'align'  => 'center',
							'size'   => array( 18, '', '' ),
							'weight' => '600',
							'lh'     => 1.31,
							'margin' => array( '48', '', '0', '' ),
						)
					)
				),
			),
			array(
				'bgColor'       => Velocity_Seed::color( 'palette3' ),
				'bgImg'         => get_stylesheet_directory_uri() . '/assets/images/home/cta-background.jpg',
				'bgImgSize'     => 'cover',
				'bgImgPosition' => 'center center',
				'borderRadius'  => array( 26, 26, 26, 26 ),
				'maxWidth'      => 1040,
				'textColor'     => Velocity_Seed::color( 'palette9' ),
				'padding'       => array( '112', '', '112', '' ),
				'mobilePadding' => array( '64', '', '64', '' ),
				'metadata'      => array( 'name' => 'Call to action band' ),
			)
		);

		return self::section( $post_id, 'cta', $band, 'Final call to action' );
	}

	/**
	 * Frequently asked questions, as native Details blocks.
	 *
	 * The class vm-faq styles them and groups them: opening one closes the
	 * others. Only the first answer is in the design, the rest are placeholders.
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
				$answers[] = Velocity_Seed::block( 'paragraph', array(), '<p>' . $paragraph . '</p>' );
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
			array(
				'padding'       => array( '48', '', '96', '' ),
				'mobilePadding' => array( '32', '', '64', '' ),
			)
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
