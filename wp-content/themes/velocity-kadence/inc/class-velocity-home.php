<?php
/**
 * Builds the home page sections as block content.
 *
 * Like the header and footer, this only produces the first version of the
 * page. After `wp velocity seed-home` the page is edited in the block editor.
 *
 * Each section is a Kadence row. Text uses core heading and paragraph blocks.
 * Visual components that repeat across the site (cards, stats) get a CSS class
 * and are styled in style.css, so they stay consistent wherever they are used.
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
	 * A decorative icon shipped with the theme, as a core image block.
	 *
	 * @param string $file  File name in assets/images/home.
	 * @param int    $width Width in px.
	 * @return string
	 */
	private static function icon( $file, $width ) {
		return Velocity_Seed::block(
			'image',
			array(
				'width'           => $width . 'px',
				'sizeSlug'        => 'full',
				'linkDestination' => 'none',
			),
			sprintf(
				'<figure class="wp-block-image size-full is-resized"><img src="%1$s" alt="" style="width:%2$dpx"/></figure>',
				esc_url( get_stylesheet_directory_uri() . '/assets/images/home/' . $file ),
				$width
			)
		);
	}

	/**
	 * A single primary button with an arrow.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $label   Button label.
	 * @param string $url     Button URL.
	 * @return string
	 */
	private static function button( $post_id, $key, $label, $url ) {
		$wrap = Velocity_Seed::uid( $post_id, $key . 'btns' );

		return Velocity_Seed::block(
			'kadence/advancedbtn',
			array( 'uniqueID' => $wrap ),
			'<div class="wp-block-kadence-advancedbtn kb-buttons-wrap kb-btns' . $wrap . '">' .
			Velocity_Seed::block(
				'kadence/singlebtn',
				array(
					'uniqueID' => Velocity_Seed::uid( $post_id, $key . 'btn' ),
					'text'     => $label,
					'link'     => $url,
					'icon'     => 'fe_arrowRight',
					'iconSide' => 'right',
				)
			) . '</div>'
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
			self::button( $post_id, 'hero', 'Become a Partner', home_url( '/become-a-partner/' ) );

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

		$header = Velocity_Seed::row(
			$post_id,
			'why-header',
			array(
				Velocity_Seed::column(
					$post_id,
					'why-header-col',
					self::heading( 'How We Help Brokers Win', 2, 'center' ) . "\n\n" .
					self::text( 'Where banks get stuck, Velocity keeps moving. By lending and holding our own money, we offer the flexibility you need alongside the stability you expect.', 'center', 'vm-subtitle' )
				),
			),
			array(
				'maxWidth' => 846,
				'padding'  => array( '0', '', '40', '' ),
				'metadata' => array( 'name' => 'Section header' ),
			)
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
					self::icon( $card[0], 35 ) . "\n\n" .
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
					'columnGutter'  => 'custom',
					'customGutter'  => array( 24, '', '' ),
					'tabletLayout'  => 'row',
					'mobileLayout'  => 'row',
					'padding'       => array( '0', '', '24', '' ),
					'className'     => 'vm-card-grid',
					'metadata'      => array( 'name' => 'Cards row ' . ( $row_index + 1 ) ),
				)
			);
		}

		$closing = self::heading( 'We help brokers move forward when others hit limits.', 3, 'center', 'vm-closing-line' );

		return Velocity_Seed::row(
			$post_id,
			'why',
			array(
				Velocity_Seed::column( $post_id, 'why-col', $header . "\n\n" . implode( "\n\n", $grids ) . "\n\n" . $closing ),
			),
			array(
				'align'           => 'full',
				'bgColor'         => 'palette9',
				'inheritMaxWidth' => true,
				'padding'         => array( '96', '', '96', '' ),
				'className'       => 'vm-section',
				'metadata'        => array( 'name' => 'How We Help Brokers Win' ),
			)
		);
	}

	/**
	 * The sections built so far, in page order.
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
			)
		);
	}
}
