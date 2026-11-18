<?php
/**
 * Contact page content.
 *
 * Builds the first version of the Contact page ("Contact & Locations" in
 * Figma) as Kadence blocks, with the same helpers as the home page. It is
 * written once by "wp velocity seed-contact". After that the page is edited
 * in the block editor like any other.
 *
 * Section 1, "Get in touch": heading, intro and three ways to reach Velocity
 * on the left, the contact form on the right.
 * Section 2, "Our offices": the Offices Map block (blocks/offices-map).
 *
 * See docs/components.md, "Offices map" and "Forms".
 *
 * Kept compatible with PHP 7.4, see class-velocity-cli.php.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Contact page seed content.
 */
class Velocity_Contact {

	/**
	 * The offices in the design: city, address lines, and where the design
	 * draws the pin's tip on the 904 by 585 map.
	 *
	 * A pin is placed from its address. The design spreads the pins of
	 * neighboring offices apart so they can be told from each other, so each
	 * seeded office is moved from its real place to the one in the design.
	 */
	const OFFICES = array(
		array( 'Los Angeles, CA', "2945 Townsgate Rd., Suite 110\nWestlake Village, CA 91361", 45, 318 ),
		array( 'Orange, CA', "600 City Parkway West, Suite 510\nOrange, CA 92868", 67, 335 ),
		array( 'Orange County, CA', "300 S. El Camino Real, Suite 213\nSan Clemente, CA 92672", 84, 371 ),
		array( 'Las Vegas, NV', "6725 Via Austi Pkwy, Suite 260\nLas Vegas, NV 89119", 137, 264 ),
		array( 'Philadelphia, PA', "1787 Sentry Parkway W, Suite 400\nBlue Bell, PA 19422", 815, 216 ),
		array( 'Boston, MA', "20 Cabot Blvd., Suite 300\nMansfield, MA 02048", 860, 147 ),
		array( 'Miami, FL', "9155 South Dadeland Blvd., Suite 1400\nMiami, FL 33156", 766, 532 ),
	);

	/**
	 * The seeded offices with their coordinates, as the block saves them.
	 *
	 * Each address is looked up in the Census Bureau geocoder, like the editor
	 * does. An address that is not found is kept without a pin.
	 *
	 * @return array List with the offices and the names of the ones not found.
	 *               Each office also says how it was found, in "precision",
	 *               which the caller reports and removes before saving.
	 */
	public static function offices() {
		$offices = array();
		$missing = array();

		foreach ( self::OFFICES as $office ) {
			$item  = array(
				'city'      => $office[0],
				'address'   => $office[1],
				'lat'       => '',
				'lng'       => '',
				'precision' => '',
			);
			$point = vm_offices_geocode( $office[1] );

			if ( $point ) {
				$real              = vm_offices_project( $point['lat'], $point['lng'] );
				$item['lat']       = (string) $point['lat'];
				$item['lng']       = (string) $point['lng'];
				$item['located']   = trim( $office[1] );
				$item['precision'] = $point['precision'];
				$item['moveRight'] = (string) round( $office[2] - $real[0] );
				$item['moveDown']  = (string) round( $office[3] - $real[1] );
			} else {
				$missing[] = $office[0];
			}

			$offices[] = $item;
		}

		return array( $offices, $missing );
	}

	/**
	 * A Kadence Image block showing an icon shipped with the theme.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $file    File name in assets/images/contact.
	 * @return string
	 */
	private static function icon( $post_id, $key, $file ) {
		$uid = Velocity_Seed::uid( $post_id, 'img-' . $key );

		return Velocity_Seed::block(
			'kadence/image',
			array(
				'imgMaxWidth'     => 26,
				'linkDestination' => 'none',
				'uniqueID'        => $uid,
				'marginDesktop'   => array( '0', '', '0', '' ),
			),
			sprintf(
				'<figure class="wp-block-kadence-image kb-image%1$s image-is-svg"><img src="%2$s" alt="" class="kb-img"/></figure>',
				$uid,
				esc_url( get_stylesheet_directory_uri() . '/assets/images/contact/' . $file )
			)
		);
	}

	/**
	 * One way to reach Velocity: an icon, a label and a link.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $file    Icon file.
	 * @param string $label   Small text above the link.
	 * @param string $text    Link text.
	 * @param string $url     Link address.
	 * @return string
	 */
	private static function method( $post_id, $key, $file, $label, $text, $url ) {
		$texts =
			Velocity_Home::t(
				$post_id,
				$key . '-label',
				$label,
				array( 'margin' => array( '0', '', '0', '' ) )
			) . "\n\n" .
			Velocity_Home::t(
				$post_id,
				$key . '-link',
				sprintf( '<a href="%s">%s</a>', esc_url( $url ), $text ),
				array(
					'size'       => array( 18, '', '' ),
					'weight'     => '600',
					'lh'         => 1.31,
					'margin'     => array( '0', '', '0', '' ),
					'link'       => Velocity_Seed::color( 'palette3' ),
					'link_hover' => Velocity_Seed::color( 'palette2' ),
				)
			);

		return Velocity_Seed::column(
			$post_id,
			$key,
			self::icon( $post_id, $key, $file ) . "\n\n" .
			Velocity_Seed::column( $post_id, $key . '-texts', $texts, array( 'metadata' => array( 'name' => 'Label and link' ) ) ),
			array(
				'background'        => 'rgba(255,255,255,0.48)',
				'borderRadius'      => array( 8, 8, 8, 8 ),
				'padding'           => array( '13', '24', '13', '24' ),
				'maxWidth'          => array( 417, '', '' ),
				'direction'         => array( 'horizontal', '', '' ),
				'verticalAlignment' => 'middle',
				'gutter'            => array( 20, '', '' ),
				'gutterVariable'    => array( 'custom', 'custom', 'custom' ),
				'metadata'          => array( 'name' => $label ),
			)
		);
	}

	/**
	 * Get in touch: heading, intro and contact methods beside the form.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function touch( $post_id ) {
		$methods = Velocity_Seed::column(
			$post_id,
			'touch-methods',
			Velocity_Home::t(
				$post_id,
				'touch-methods-title',
				'Other ways to reach us',
				array(
					'tag'    => 'h2',
					'size'   => array( 22, '', '' ),
					'weight' => '700',
					'lh'     => 1.1,
					'margin' => array( '0', '', '0', '' ),
				)
			) . "\n\n" .
			// The cards sit in a horizontal Section that wraps, one card per line.
			// There a max width keeps each card at 417px and aligned left. In a
			// vertical Section, Kadence centers anything that has a max width.
			Velocity_Seed::column(
				$post_id,
				'touch-methods-list',
				self::method( $post_id, 'touch-call', 'icon-call.svg', 'Call us', '(888) 822-3304', 'tel:+18888223304' ) . "\n\n" .
				self::method( $post_id, 'touch-mail', 'icon-mail.svg', 'Email us', 'info@velocitymortgage.com', 'mailto:info@velocitymortgage.com' ) . "\n\n" .
				self::method( $post_id, 'touch-partner', 'icon-handshake.svg', 'Not a partner yet?', 'Become a Partner', home_url( '/become-a-partner/' ) ),
				array(
					'direction'      => array( 'horizontal', '', '' ),
					'wrapContent'    => array( 'wrap', '', '' ),
					'gutter'         => array( 20, '', '' ),
					'gutterVariable' => array( 'custom', 'custom', 'custom' ),
					'rowGapVariable' => array( 'custom', '', '' ),
					'rowGap'         => array( 20, '', '' ),
					'metadata'       => array( 'name' => 'Contact methods' ),
				)
			),
			array(
				// The rule above the block: a top border only.
				'borderStyle'    => array(
					array(
						'top'    => array( 'rgba(43,53,120,0.45)', '', 1 ),
						'right'  => array( '', '', '' ),
						'bottom' => array( '', '', '' ),
						'left'   => array( '', '', '' ),
						'unit'   => 'px',
					),
				),
				'padding'        => array( '32', '', '0', '' ),
				'rowGapVariable' => array( 'custom', '', '' ),
				'rowGap'         => array( 24, '', '' ),
				'metadata'       => array( 'name' => 'Other ways to reach us' ),
			)
		);

		$intro =
			Velocity_Home::t(
				$post_id,
				'touch-title',
				'Get in Touch With Velocity',
				array(
					'tag'    => 'h1',
					'margin' => array( '0', '', '24', '' ),
					'class'  => 'is-style-vm-reveal-lines',
				)
			) . "\n\n" .
			Velocity_Home::subtitle(
				$post_id,
				'touch',
				'Whether you have a question about a program, a deal you want our opinion on, a status update on a file, or just a general inquiry, we’re here to help.',
				array(
					'color'  => '#595959',
					'margin' => array( '0', '', '48', '' ),
				)
			) . "\n\n" . $methods;

		$form = Velocity_Seed::column(
			$post_id,
			'touch-form',
			Velocity_Home::t(
				$post_id,
				'touch-form-title',
				'Contact Us',
				array(
					'tag'    => 'h2',
					'size'   => array( 24, '', '' ),
					'weight' => '700',
					'lh'     => 1.1,
					'margin' => array( '0', '', '0', '' ),
				)
			) . "\n\n<!-- wp:shortcode -->\n[vm_form name=\"contact\"]\n<!-- /wp:shortcode -->",
			array(
				'background'     => Velocity_Seed::color( 'palette7' ),
				'borderStyle'    => Velocity_Home::border( '#99a2dd' ),
				'borderRadius'   => array( 8, 8, 8, 8 ),
				'padding'        => array( '32', '32', '32', '32' ),
				'mobilePadding'  => array( '24', '20', '24', '20' ),
				'rowGapVariable' => array( 'custom', '', '' ),
				'rowGap'         => array( 24, '', '' ),
				'metadata'       => array( 'name' => 'Contact form' ),
			)
		);

		return Velocity_Seed::row(
			$post_id,
			'touch',
			array(
				Velocity_Seed::column( $post_id, 'touch-intro', $intro, array( 'metadata' => array( 'name' => 'Contact information' ) ) ),
				Velocity_Seed::column( $post_id, 'touch-form-col', $form ),
			),
			array(
				'align'             => 'full',
				'bgColor'           => Velocity_Seed::color( 'palette7' ),
				'inheritMaxWidth'   => true,
				'padding'           => array( '64', '', '64', '' ),
				'verticalAlignment' => 'middle',
				'columnGutter'      => 'custom',
				'customGutter'      => array( 120, 48, '' ),
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				'metadata'          => array( 'name' => 'Get in touch' ),
			)
		);
	}

	/**
	 * Our offices: title, intro and the map.
	 *
	 * @param int   $post_id Page ID.
	 * @param array $offices Offices, as the block saves them.
	 * @return string
	 */
	public static function map( $post_id, $offices ) {
		$inner =
			Velocity_Home::title( $post_id, 'offices', 'Our Offices' ) . "\n\n" .
			Velocity_Home::subtitle(
				$post_id,
				'offices',
				'Velocity is headquartered in Westlake Village, California, with offices in seven cities. Here is where to find us.',
				array(
					'align'  => 'center',
					'width'  => 741,
					'margin' => array( '0', 'auto', '52', 'auto' ),
				)
			) . "\n\n" .
			Velocity_Seed::block( 'velocity/offices-map', array( 'offices' => $offices ) );

		return Velocity_Home::section(
			$post_id,
			'offices',
			$inner,
			'Our offices',
			array(
				'padding'       => array( '80', '', '80', '' ),
				'mobilePadding' => array( '64', '', '64', '' ),
			)
		);
	}

	/**
	 * Every section, in page order.
	 *
	 * @param int   $post_id Page ID.
	 * @param array $offices Offices, as the block saves them.
	 * @return string
	 */
	public static function content( $post_id, $offices ) {
		return self::touch( $post_id ) . "\n\n" . self::map( $post_id, $offices );
	}
}
