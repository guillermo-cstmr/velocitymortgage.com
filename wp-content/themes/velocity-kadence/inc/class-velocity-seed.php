<?php
/**
 * Builds the header and the footer as Kadence block content.
 *
 * The header is a Kadence Blocks header post, the menus are Kadence navigation
 * posts and the footer is a Kadence Element. All of them are normal block
 * content: once created they are edited in the block editor like anything else.
 *
 * This class only produces the FIRST version of them, so the same starting
 * point can be created on Local, staging and production with one command
 * instead of being rebuilt by hand three times. See `wp velocity seed-chrome`.
 *
 * Figma: Style Guide page, sections "Main Nav" and "Footer".
 *
 * Kept compatible with PHP 7.4, see class-velocity-cli.php.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Header and footer seed content.
 */
class Velocity_Seed {

	/**
	 * Post meta that marks a post as created by this class, with its key.
	 */
	const MARKER = '_vm_seed';

	/**
	 * The site menu. Label, target, optional CSS class, optional children.
	 *
	 * A target starting with "/" is a path on this site. If a page lives there
	 * the link is tied to that page. "#" is a placeholder for a page that does
	 * not exist yet.
	 *
	 * @return array[]
	 */
	public static function menu() {
		return array(
			array(
				'Broker Programs',
				'#',
				'',
				array(
					array( 'Programs Overview', '/mortgage-programs/' ),
					array( '30-Year-Fixed', '#' ),
					array( 'Flex I/O', '#' ),
					array( 'Fast50', '#' ),
					array( 'Process', '#', 'vm-menu-featured' ),
				),
			),
			array(
				'Resources',
				'#',
				'',
				array(
					array( 'News and Insights', '/insights/' ),
					array( 'Featured Deals', '/featured-investment-property-loans/' ),
					array( 'Case Studies', '#' ),
					array( 'Broker Tools', '/broker-toolkit/' ),
				),
			),
			array(
				'Company',
				'#',
				'',
				array(
					array( 'About Us', '#' ),
					array( 'Our Team', '/team/' ),
					array( 'Careers', '/careers/' ),
				),
			),
			array( 'Contact', '/contact-us/' ),
		);
	}

	/**
	 * Legal links under the footer divider.
	 *
	 * @return array[]
	 */
	public static function legal_menu() {
		return array(
			array( 'Privacy Policy', '/privacy/' ),
			array( 'Terms of Service', '/terms-of-use/' ),
			array( 'Licenses', '#' ),
			array( 'Forms Library', '#' ),
		);
	}

	/**
	 * Every navigation post to create: key => title, items and settings.
	 *
	 * The footer columns mirror the header dropdowns, as in the design.
	 *
	 * @return array[]
	 */
	public static function navigations() {
		$menu = self::menu();

		$footer = array(
			'orientation'    => 'vertical',
			'linkColor'      => 'palette9',
			'linkColorHover' => 'palette10',
			// "spacing" is the gap between items, "paddingLink" is inside each link.
			'spacing'        => array( '8', '0', '8', '0' ),
			'spacingUnit'    => 'px',
			'paddingLink'    => array( '4', '0', '4', '0' ),
			'typography'     => self::typography( 14, '500', 1.1 ),
		);

		return array(
			'nav-header'          => array(
				'title' => 'Header',
				'items' => $menu,
				'meta'  => array(
					'linkColor'                   => 'palette9',
					'linkColorHover'              => 'palette10',
					'linkColorActive'             => 'palette10',
					'spacing'                     => array( '', '22', '', '22' ),
					'spacingUnit'                 => 'px',
					'typography'                  => self::typography( 16, '', '', 18 ),
					'backgroundDropdown'          => 'palette7',
					'linkColorDropdown'           => 'palette3',
					'linkColorDropdownHover'      => 'palette2',
					'linkColorDropdownActive'     => 'palette2',
					'dropdownTypography'          => self::typography( 16 ),
					'dropdownWidth'               => '290',
					'dropdownWidthUnit'           => 'px',
					'dropdownVerticalSpacing'     => '12',
					'dropdownVerticalSpacingUnit' => 'px',
					'dropdownBorderRadius'        => array( 16, 16, 16, 16 ),
					'paddingDropdown'             => array( '24', '20', '24', '20' ),

					/*
					 * The same menu is used in the mobile drawer, so links are
					 * maintained in one place. On tablet and mobile it turns
					 * vertical and its dropdowns lose the light blue panel.
					 */
					'orientationTablet'              => 'vertical',
					'orientationMobile'              => 'vertical',
					'backgroundDropdownTablet'       => 'rgba(0,0,0,0)',
					'backgroundDropdownMobile'       => 'rgba(0,0,0,0)',
					'linkColorDropdownTablet'        => 'palette9',
					'linkColorDropdownMobile'        => 'palette9',
					'linkColorDropdownHoverTablet'   => 'palette10',
					'linkColorDropdownHoverMobile'   => 'palette10',
					'linkColorDropdownActiveTablet'  => 'palette10',
					'linkColorDropdownActiveMobile'  => 'palette10',
					'dropdownBorderRadiusTablet'     => array( 0, 0, 0, 0 ),
					'dropdownBorderRadiusMobile'     => array( 0, 0, 0, 0 ),
					'tabletPaddingDropdown'          => array( '0', '0', '8', '16' ),
					'mobilePaddingDropdown'          => array( '0', '0', '8', '16' ),
				),
			),
			'nav-footer-programs' => array(
				'title' => 'Footer: Broker Programs',
				'items' => $menu[0][3],
				'meta'  => $footer,
			),
			'nav-footer-resources' => array(
				'title' => 'Footer: Resources',
				'items' => $menu[1][3],
				'meta'  => $footer,
			),
			'nav-footer-company'  => array(
				'title' => 'Footer: Company',
				'items' => $menu[2][3],
				'meta'  => $footer,
			),
			'nav-footer-contact'  => array(
				'title' => 'Footer: Contact',
				'items' => array( $menu[3] ),
				'meta'  => $footer,
			),
			'nav-footer-legal'    => array(
				'title' => 'Footer: Legal',
				'items' => self::legal_menu(),
				'meta'  => array(
					'linkColor'      => 'palette9',
					'linkColorHover' => 'palette10',
					'spacing'        => array( '', '12', '', '12' ),
					'spacingUnit'    => 'px',
					'typography'     => self::typography( 14 ),
				),
			),
		);
	}

	/**
	 * A Kadence Blocks typography value.
	 *
	 * @param int          $size        Desktop size in px.
	 * @param string       $weight      Font weight, or empty to inherit.
	 * @param float|string $line_height Unitless line height, or empty to inherit.
	 * @param int|string   $tablet_size Tablet and mobile size in px, or empty to inherit.
	 * @return array
	 */
	public static function typography( $size, $weight = '', $line_height = '', $tablet_size = '' ) {
		return array(
			array(
				'size'          => array( $size, $tablet_size, '' ),
				'sizeType'      => 'px',
				'lineHeight'    => array( $line_height, '', '' ),
				'lineType'      => '',
				'letterSpacing' => array( '', '', '' ),
				'letterType'    => 'px',
				'textTransform' => '',
				'family'        => '',
				'google'        => false,
				'style'         => '',
				'weight'        => $weight,
				'variant'       => '',
				'subset'        => '',
				'loadGoogle'    => true,
			),
		);
	}

	/**
	 * The value Kadence Blocks stores for a palette color.
	 *
	 * Kadence Blocks keeps colors as design tokens, written like
	 * "{primitive.color.brand.primary}". The tokens are synced from the theme
	 * palette, so they follow inc/tokens.php. The older "palette1" format still
	 * renders on the site, but the color control in the editor shows it as
	 * empty, so it must not be used.
	 *
	 * @param string $slot Palette slot, "palette1" to "palette10".
	 * @return string
	 */
	public static function color( $slot ) {
		$tokens = array(
			'palette1' => '{primitive.color.brand.primary}',   // Action Orange.
			'palette2' => '{primitive.color.brand.secondary}', // Deep Rust.
			'palette3' => '{primitive.color.neutral.900}',     // Brand Navy.
			'palette4' => '{primitive.color.neutral.900}',     // Brand Navy.
			'palette5' => '{primitive.color.neutral.600}',     // Indigo.
			'palette6' => '{primitive.color.neutral.500}',     // Gray.
			'palette7' => '{primitive.color.neutral.100}',     // Light Blue.
			'palette8' => '{primitive.color.neutral.50}',      // Off White.
			'palette9' => '{primitive.color.neutral.0}',       // White.
			// Supporting Orange has no token of its own, so it is a plain value.
			'palette10' => '#f18521',
		);

		return isset( $tokens[ $slot ] ) ? $tokens[ $slot ] : $slot;
	}

	/**
	 * A stable Kadence unique ID for a block, prefixed with its post ID.
	 *
	 * @param int    $post_id Post the block lives in.
	 * @param string $key     Anything unique within that post.
	 * @return string
	 */
	public static function uid( $post_id, $key ) {
		$hash = md5( $key );

		return $post_id . '_' . substr( $hash, 0, 6 ) . '-' . substr( $hash, 6, 2 );
	}

	/**
	 * Serialize one block.
	 *
	 * @param string      $name  Block name without the "wp:" prefix.
	 * @param array       $attrs Block attributes.
	 * @param string|null $inner Inner content, or null for a self-closing block.
	 * @return string
	 */
	public static function block( $name, $attrs = array(), $inner = null ) {
		$json = $attrs ? ' ' . serialize_block_attributes( $attrs ) : '';

		if ( null === $inner ) {
			return '<!-- wp:' . $name . $json . ' /-->';
		}

		return '<!-- wp:' . $name . $json . " -->\n" . $inner . "\n<!-- /wp:" . $name . ' -->';
	}

	/**
	 * Navigation link blocks for a list of menu items, recursively.
	 *
	 * @param int    $post_id Navigation post ID.
	 * @param array  $items   Menu items.
	 * @param string $prefix  Key prefix, to keep unique IDs distinct.
	 * @return string
	 */
	public static function navigation_links( $post_id, $items, $prefix = '' ) {
		$out = array();

		foreach ( $items as $index => $item ) {
			$key    = $prefix . $index;
			$target = $item[1];
			$page   = '#' === $target ? null : get_page_by_path( trim( $target, '/' ) );

			$attrs = array(
				'uniqueID' => self::uid( $post_id, 'link' . $key ),
				'label'    => $item[0],
			);

			if ( $page ) {
				$attrs['type'] = 'page';
				$attrs['id']   = (int) $page->ID;
				$attrs['url']  = get_permalink( $page );
				$attrs['kind'] = 'post-type';
			} else {
				$attrs['url']  = '#' === $target ? '#' : home_url( $target );
				$attrs['kind'] = 'custom';
			}

			if ( ! empty( $item[2] ) ) {
				$attrs['className'] = $item[2];
			}

			$children = empty( $item[3] ) ? null : self::navigation_links( $post_id, $item[3], $key . '-' );

			$out[] = self::block( 'kadence/navigation-link', $attrs, $children );
		}

		return implode( "\n\n", $out );
	}

	/**
	 * Content of a navigation post.
	 *
	 * @param int   $post_id Navigation post ID.
	 * @param array $items   Menu items.
	 * @return string
	 */
	public static function navigation_content( $post_id, $items ) {
		return self::block(
			'kadence/navigation',
			array( 'uniqueID' => self::uid( $post_id, 'nav' ) ),
			self::navigation_links( $post_id, $items )
		);
	}

	/**
	 * A core image block that points to a file shipped with the theme.
	 *
	 * The image is not in the media library on purpose: media IDs differ
	 * between environments, a theme file is the same everywhere.
	 *
	 * @param string $file  File name in assets/images.
	 * @param int    $width Width in px.
	 * @param string $class Extra class.
	 * @return string
	 */
	public static function theme_image( $file, $width, $class = '' ) {
		$attrs = array(
			'width'           => $width . 'px',
			'sizeSlug'        => 'full',
			'linkDestination' => 'custom',
		);

		if ( $class ) {
			$attrs['className'] = $class;
		}

		$html = sprintf(
			'<figure class="wp-block-image size-full is-resized%1$s"><a href="%2$s"><img src="%3$s" alt="%4$s" style="width:%5$dpx"/></a></figure>',
			$class ? ' ' . esc_attr( $class ) : '',
			esc_url( home_url( '/' ) ),
			esc_url( get_stylesheet_directory_uri() . '/assets/images/' . $file ),
			esc_attr( get_bloginfo( 'name', 'display' ) ),
			$width
		);

		return self::block( 'image', $attrs, $html );
	}

	/**
	 * The two header buttons: "Broker Login" and "Become a Partner".
	 *
	 * @param int    $post_id Header post ID.
	 * @param string $key     Key prefix for unique IDs.
	 * @return string
	 */
	public static function header_buttons( $post_id, $key ) {
		$wrap = self::uid( $post_id, $key . 'btns' );

		$login = self::block(
			'kadence/singlebtn',
			array(
				'uniqueID'        => self::uid( $post_id, $key . 'login' ),
				'text'            => 'Broker Login',
				'link'            => 'https://www.generatevelocity.com/#/SignIn',
				'icon'            => 'fe_user',
				'iconSide'        => 'left',
				'color'           => self::color( 'palette1' ),
				'background'      => 'rgba(0,0,0,0)',
				'colorHover'      => self::color( 'palette10' ),
				'backgroundHover' => 'rgba(0,0,0,0)',
				'typography'      => self::typography( 16, '600' ),
				'className'       => 'vm-login-link',
			)
		);

		// Placeholder link until the destination is confirmed with the client.
		$partner = self::block(
			'kadence/singlebtn',
			array(
				'uniqueID' => self::uid( $post_id, $key . 'partner' ),
				'text'     => 'Become a Partner',
				'link'     => home_url( '/become-a-partner/' ),
				'icon'     => 'fe_arrowRight',
				'iconSide' => 'right',
			)
		);

		return self::block(
			'kadence/advancedbtn',
			array( 'uniqueID' => $wrap ),
			'<div class="wp-block-kadence-advancedbtn kb-buttons-wrap kb-btns' . $wrap . '">' . $login . "\n\n" . $partner . '</div>'
		);
	}

	/**
	 * One header column, empty or with content.
	 *
	 * @param int         $post_id  Header post ID.
	 * @param string      $key      Key for the unique ID.
	 * @param string      $location Kadence column location.
	 * @param string      $name     Name shown in the editor's list view.
	 * @param string|null $inner    Inner blocks.
	 * @return string
	 */
	private static function header_column( $post_id, $key, $location, $name, $inner = null ) {
		return self::block(
			'kadence/header-column',
			array(
				'uniqueID' => self::uid( $post_id, $key ),
				'location' => $location,
				'metadata' => array( 'name' => $name ),
			),
			$inner
		);
	}

	/**
	 * One desktop header row: left section, center, right section.
	 *
	 * @param int    $post_id  Header post ID.
	 * @param string $location top, center or bottom.
	 * @param string $name     Name shown in the editor.
	 * @param array  $slots    Content by column location.
	 * @param array  $attrs    Extra row attributes.
	 * @return string
	 */
	private static function desktop_row( $post_id, $location, $name, $slots = array(), $attrs = array() ) {
		$key = 'd-' . $location . '-';
		$get = static function ( $slot ) use ( $slots ) {
			return isset( $slots[ $slot ] ) ? $slots[ $slot ] : null;
		};

		$left = self::block(
			'kadence/header-section',
			array(
				'uniqueID' => self::uid( $post_id, $key . 'sl' ),
				'location' => 'left',
				'metadata' => array( 'name' => 'Left Section' ),
			),
			self::header_column( $post_id, $key . 'l', 'left', 'Left', $get( 'left' ) ) . "\n\n" .
			self::header_column( $post_id, $key . 'cl', 'center-left', 'Center Left', $get( 'center-left' ) )
		);

		$right = self::block(
			'kadence/header-section',
			array(
				'uniqueID' => self::uid( $post_id, $key . 'sr' ),
				'location' => 'right',
				'metadata' => array( 'name' => 'Right Section' ),
			),
			self::header_column( $post_id, $key . 'cr', 'center-right', 'Center Right', $get( 'center-right' ) ) . "\n\n" .
			self::header_column( $post_id, $key . 'r', 'right', 'Right', $get( 'right' ) )
		);

		return self::block(
			'kadence/header-row',
			array_merge(
				array(
					'uniqueID' => self::uid( $post_id, $key . 'row' ),
					'location' => $location,
					'metadata' => array( 'name' => $name ),
				),
				$attrs
			),
			$left . "\n\n" . self::header_column( $post_id, $key . 'c', 'center', 'Center', $get( 'center' ) ) . "\n\n" . $right
		);
	}

	/**
	 * One tablet and mobile header row: left, center, right.
	 *
	 * @param int    $post_id  Header post ID.
	 * @param string $location top, center or bottom.
	 * @param string $name     Name shown in the editor.
	 * @param array  $slots    Content by column location.
	 * @param array  $attrs    Extra row attributes.
	 * @return string
	 */
	private static function tablet_row( $post_id, $location, $name, $slots = array(), $attrs = array() ) {
		$key  = 't-' . $location . '-';
		$cols = array();

		foreach ( array( 'tablet-left' => 'Left', 'tablet-center' => 'Center', 'tablet-right' => 'Right' ) as $slot => $label ) {
			$cols[] = self::header_column( $post_id, $key . $slot, $slot, $label, isset( $slots[ $slot ] ) ? $slots[ $slot ] : null );
		}

		return self::block(
			'kadence/header-row',
			array_merge(
				array(
					'uniqueID' => self::uid( $post_id, $key . 'row' ),
					'location' => $location,
					'metadata' => array( 'name' => $name ),
				),
				$attrs
			),
			implode( "\n\n", $cols )
		);
	}

	/**
	 * Content of the header post.
	 *
	 * @param int $post_id Header post ID.
	 * @param int $nav_id  Navigation post ID, used on desktop and in the mobile drawer.
	 * @return string
	 */
	public static function header_content( $post_id, $nav_id ) {
		$nav = self::block(
			'kadence/navigation',
			array(
				'uniqueID' => self::uid( $post_id, 'nav' ),
				'id'       => (int) $nav_id,
			)
		);

		$desktop = self::block(
			'kadence/header-container-desktop',
			array( 'uniqueID' => self::uid( $post_id, 'desktop' ) ),
			self::desktop_row( $post_id, 'top', 'Top Row' ) . "\n\n" .
			self::desktop_row(
				$post_id,
				'center',
				'Middle Row',
				array(
					'left'        => self::theme_image( 'logo-white.svg', 224, 'vm-logo' ),
					'center-left' => $nav,
					'right'       => self::header_buttons( $post_id, 'd' ),
				),
				array( 'minHeight' => 103 )
			) . "\n\n" .
			self::desktop_row( $post_id, 'bottom', 'Bottom Row' )
		);

		$trigger = self::block(
			'kadence/off-canvas-trigger',
			array(
				'uniqueID'             => self::uid( $post_id, 'trigger' ),
				'iconSize'             => 28,
				'iconColor'            => self::color( 'palette9' ),
				'iconColorTablet'      => self::color( 'palette9' ),
				'iconColorMobile'      => self::color( 'palette9' ),
				'iconColorHover'       => self::color( 'palette10' ),
				'iconColorHoverTablet' => self::color( 'palette10' ),
				'iconColorHoverMobile' => self::color( 'palette10' ),
			)
		);

		$tablet = self::block(
			'kadence/header-container-tablet',
			array( 'uniqueID' => self::uid( $post_id, 'tablet' ) ),
			self::tablet_row( $post_id, 'top', 'Top Row' ) . "\n\n" .
			self::tablet_row(
				$post_id,
				'center',
				'Middle Row',
				array(
					'tablet-left'  => self::theme_image( 'logo-white.svg', 170, 'vm-logo' ),
					'tablet-right' => $trigger,
				),
				array( 'minHeight' => 72 )
			) . "\n\n" .
			self::tablet_row( $post_id, 'bottom', 'Bottom Row' )
		);

		$off_canvas = self::block(
			'kadence/off-canvas',
			array(
				'uniqueID'        => self::uid( $post_id, 'offcanvas' ),
				'slideFrom'       => 'right',
				'backgroundColor' => self::color( 'palette3' ),
				'closeIconColor'  => self::color( 'palette9' ),
			),
			self::block(
				'kadence/navigation',
				array(
					'uniqueID' => self::uid( $post_id, 'nav-mobile' ),
					'id'       => (int) $nav_id,
				)
			) . "\n\n" . self::header_buttons( $post_id, 'm' )
		);

		return self::block(
			'kadence/header',
			array( 'uniqueID' => self::uid( $post_id, 'header' ) ),
			$desktop . "\n\n" . $tablet . "\n\n" . $off_canvas
		);
	}

	/**
	 * One Kadence column block.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $inner   Inner blocks.
	 * @param array  $attrs   Extra attributes.
	 * @return string
	 */
	public static function column( $post_id, $key, $inner, $attrs = array() ) {
		$uid = self::uid( $post_id, $key );

		return self::block(
			'kadence/column',
			array_merge(
				array(
					'borderWidth' => array( '', '', '', '' ),
					'uniqueID'    => $uid,
					'kbVersion'   => 2,
				),
				$attrs
			),
			'<div class="wp-block-kadence-column kadence-column' . $uid . ( empty( $attrs['className'] ) ? '' : ' ' . esc_attr( $attrs['className'] ) ) . '"' . self::aos_markup( $attrs ) . '><div class="kt-inside-inner-col">' . $inner . '</div></div>'
		);
	}

	/**
	 * HTML attributes for Kadence's Animate on Scroll, as the block saves them.
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	public static function aos_markup( $attrs ) {
		if ( empty( $attrs['kadenceAnimation'] ) ) {
			return '';
		}

		$o   = isset( $attrs['kadenceAOSOptions'][0] ) ? $attrs['kadenceAOSOptions'][0] : array();
		$out = ' data-aos="' . esc_attr( $attrs['kadenceAnimation'] ) . '"';
		foreach ( array( 'offset', 'duration', 'delay', 'easing' ) as $key ) {
			if ( ! empty( $o[ $key ] ) ) {
				$out .= ' data-aos-' . $key . '="' . esc_attr( $o[ $key ] ) . '"';
			}
		}
		if ( isset( $o['once'] ) && '' !== $o['once'] ) {
			$out .= ' data-aos-once="' . ( true === $o['once'] ? 'true' : esc_attr( $o['once'] ) ) . '"';
		}

		return $out;
	}

	/**
	 * One Kadence row layout block.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Key for the unique ID.
	 * @param array  $columns Column blocks.
	 * @param array  $attrs   Extra attributes.
	 * @return string
	 */
	public static function row( $post_id, $key, $columns, $attrs = array() ) {
		return self::block(
			'kadence/rowlayout',
			array_merge(
				array(
					'uniqueID'  => self::uid( $post_id, $key ),
					'columns'   => count( $columns ),
					'colLayout' => 'equal',
					'kbVersion' => 2,
				),
				$attrs
			),
			implode( "\n\n", $columns )
		);
	}

	/**
	 * A core paragraph block.
	 *
	 * @param string $text  Text. May contain shortcodes.
	 * @param int    $size  Font size in px.
	 * @param string $align Text alignment, or empty.
	 * @return string
	 */
	public static function paragraph( $text, $size, $align = '' ) {
		$attrs = array(
			'style' => array(
				'typography' => array( 'fontSize' => $size . 'px' ),
			),
		);

		if ( $align ) {
			$attrs = array_merge( array( 'align' => $align ), $attrs );
		}

		return self::block(
			'paragraph',
			$attrs,
			sprintf(
				'<p%1$s style="font-size:%2$dpx">%3$s</p>',
				$align ? ' class="has-text-align-' . esc_attr( $align ) . '"' : '',
				$size,
				$text
			)
		);
	}

	/**
	 * Content of the footer element.
	 *
	 * @param int   $post_id Element post ID.
	 * @param int[] $nav_ids Navigation post IDs by seed key.
	 * @return string
	 */
	public static function footer_content( $post_id, $nav_ids ) {
		$nav = static function ( $key ) use ( $post_id, $nav_ids ) {
			return self::block(
				'kadence/navigation',
				array(
					'uniqueID' => self::uid( $post_id, $key ),
					'id'       => (int) $nav_ids[ $key ],
				)
			);
		};

		$social = self::block(
			'social-links',
			array(
				'iconColor'      => 'theme-palette1',
				'iconColorValue' => 'var(--global-palette1)',
				'className'      => 'is-style-logos-only',
				'layout'         => array(
					'type'           => 'flex',
					'justifyContent' => 'right',
				),
			),
			'<ul class="wp-block-social-links has-icon-color is-style-logos-only">' .
			self::block(
				'social-link',
				array(
					'url'     => 'https://www.facebook.com/VelocityMortgage/',
					'service' => 'facebook',
				)
			) . "\n\n" .
			self::block(
				'social-link',
				array(
					'url'     => 'https://www.instagram.com/velocitymortgagecapital/',
					'service' => 'instagram',
				)
			) . "\n\n" .
			self::block(
				'social-link',
				array(
					'url'     => 'https://www.linkedin.com/company/velocity-commercial-capital/',
					'service' => 'linkedin',
				)
			) . '</ul>'
		);

		$links = self::row(
			$post_id,
			'links',
			array(
				self::column( $post_id, 'links-1', $nav( 'nav-footer-programs' ) ),
				self::column( $post_id, 'links-2', $nav( 'nav-footer-resources' ), array( 'id' => 2 ) ),
				self::column( $post_id, 'links-3', $nav( 'nav-footer-company' ), array( 'id' => 3 ) ),
				self::column( $post_id, 'links-4', $nav( 'nav-footer-contact' ), array( 'id' => 4 ) ),
			),
			array(
				'tabletLayout' => 'two-grid',
				'mobileLayout' => 'two-grid',
				'padding'      => array( '0', '', '0', '' ),
				'metadata'     => array( 'name' => 'Link columns' ),
			)
		);

		$main = self::row(
			$post_id,
			'main',
			array(
				self::column( $post_id, 'main-logo', self::theme_image( 'logomark-white.svg', 91, 'vm-footer-logo' ) ),
				self::column( $post_id, 'main-links', $links, array( 'id' => 2 ) ),
				self::column(
					$post_id,
					'main-social',
					$social . "\n\n" . self::paragraph( '© [vm_year] Velocity Mortgage Capital.<br>All rights reserved.', 14, 'right' ),
					array( 'id' => 3 )
				),
			),
			array(
				'columnsUnlocked'   => true,
				'firstColumnWidth'  => 22,
				'secondColumnWidth' => 54,
				'thirdColumnWidth'  => 24,
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				'bgColor'           => self::color( 'palette3' ),
				'textColor'         => self::color( 'palette9' ),
				'linkColor'         => self::color( 'palette9' ),
				'linkHoverColor'    => self::color( 'palette10' ),
				'inheritMaxWidth'   => true,
				'padding'           => array( '80', '', '36', '' ),
				'className'         => 'vm-footer-main',
				'metadata'          => array( 'name' => 'Footer: logo, links, social' ),
			)
		);

		// Legal copy. Do not edit without approval from the client.
		$disclosure = '©[vm_year] Velocity Commercial Capital, LLC. DBA Velocity Mortgage Capital. All rights reserved. California Finance Lenders License #603A366. For mortgage professionals only. This information is intended for the exclusive use of licensed real estate and mortgage lending professionals in accordance with local laws and regulations. Distribution to the general public is prohibited. Rates and programs are subject to change without notice.';

		$legal = self::row(
			$post_id,
			'legal',
			array(
				self::column( $post_id, 'legal-text', self::paragraph( $disclosure, 12 ) ),
				self::column( $post_id, 'legal-links', $nav( 'nav-footer-legal' ), array( 'id' => 2 ) ),
			),
			array(
				'colLayout'       => 'left-golden',
				'tabletLayout'    => 'row',
				'mobileLayout'    => 'row',
				'bgColor'         => self::color( 'palette3' ),
				'textColor'       => self::color( 'palette9' ),
				'linkColor'       => self::color( 'palette9' ),
				'linkHoverColor'  => self::color( 'palette10' ),
				'inheritMaxWidth' => true,
				'padding'         => array( '32', '', '80', '' ),
				'className'       => 'vm-footer-legal',
				'metadata'        => array( 'name' => 'Footer: disclosure and legal links' ),
			)
		);

		// One <footer> element around both rows: the Group's HTML element
		// setting (Advanced). It is the page's footer landmark.
		return self::footer_landmark( $main . "\n\n" . $legal );
	}

	/**
	 * A Group block rendered as <footer>, with no spacing of its own.
	 *
	 * @param string $inner Inner blocks.
	 * @return string
	 */
	public static function footer_landmark( $inner ) {
		return self::block(
			'group',
			array(
				'tagName'  => 'footer',
				'metadata' => array( 'name' => 'Footer' ),
				'style'    => array( 'spacing' => array( 'blockGap' => '0' ) ),
				'layout'   => array( 'type' => 'default' ),
			),
			'<footer class="wp-block-group">' . "\n" . $inner . "\n" . '</footer>'
		);
	}
}
