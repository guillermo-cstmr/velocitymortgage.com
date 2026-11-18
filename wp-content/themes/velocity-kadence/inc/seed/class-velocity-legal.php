<?php
/**
 * The legal pages: Terms of Use and the Privacy Notice for California
 * Residents. Figma: "Legal Pages / Others".
 *
 * One layout for both: on the left a card with the list of sections, which
 * stays in view while the text scrolls; on the right the title and the text.
 *
 * The list of sections is Kadence's Table of Contents block. It reads the
 * headings of the page by itself, so a heading added or renamed in the
 * editor shows up in it. It lists the H2 headings only: a section that
 * should not be in the list is an H3, which looks the same.
 *
 * The text of each page is in inc/seed/data, one JSON file per page, taken
 * from the design. It is only read by the command that fills the page. After
 * that the text lives in the page and is edited in the block editor.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Builds the block markup of the legal pages.
 */
class Velocity_Legal {

	/**
	 * The legal pages: slug, title of the page in the admin, and data file.
	 *
	 * @return array[]
	 */
	public static function pages() {
		return array(
			'terms'   => array(
				'slug'  => 'terms-of-use',
				'title' => 'Terms of Use',
				'data'  => 'legal-terms.json',
			),
			// The one privacy page of the site, at the address of the old one.
			'privacy' => array(
				'slug'  => 'privacy',
				'title' => 'Privacy Notice for California Residents',
				'data'  => 'legal-privacy.json',
			),
		);
	}

	/**
	 * The text of a page, from its data file.
	 *
	 * @param string $file File name inside inc/seed/data.
	 * @return array|null Null when the file is missing or is not valid JSON.
	 */
	public static function data( $file ) {
		$path = __DIR__ . '/data/' . $file;

		if ( ! is_readable( $path ) ) {
			return null;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- A file of the theme.
		$data = json_decode( file_get_contents( $path ), true );

		return is_array( $data ) && isset( $data['sections'] ) ? $data : null;
	}

	/**
	 * A paragraph of body text. Figma style "Body": 16px on lines of 24px,
	 * and one empty line, 24px, before the next paragraph.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $html    Text. May contain inline HTML.
	 * @param bool   $tight   Whether a list follows: the design leaves no space before it.
	 * @return string
	 */
	private static function paragraph( $post_id, $key, $html, $tight = false ) {
		return Velocity_Home::t(
			$post_id,
			$key,
			$html,
			array(
				'size'       => array( 16, '', '' ),
				'lh'         => 1.5,
				'color'      => Velocity_Seed::color( 'palette3' ),
				'margin'     => array( '0', '', $tight ? '0' : '24', '' ),
				'link'       => Velocity_Seed::color( 'palette2' ),
				'link_hover' => Velocity_Seed::color( 'palette1' ),
			)
		);
	}

	/**
	 * A list, as a core List block: it numbers its items by itself, which the
	 * Kadence Icon List does not.
	 *
	 * @param string[] $items   Items. May contain inline HTML.
	 * @param bool     $ordered Whether the list is numbered.
	 * @param int      $level   2 for a list that belongs under the item before it.
	 * @return string
	 */
	private static function items( $items, $ordered, $level = 1 ) {
		$tag   = $ordered ? 'ol' : 'ul';
		$attrs = array( 'className' => 2 === $level ? 'vm-legal-list vm-legal-list--sub' : 'vm-legal-list' );
		$lis   = array();

		if ( $ordered ) {
			$attrs = array_merge( array( 'ordered' => true ), $attrs );
		}

		foreach ( $items as $item ) {
			$lis[] = Velocity_Seed::block( 'core/list-item', array(), '<li>' . $item . '</li>' );
		}

		return Velocity_Seed::block(
			'core/list',
			$attrs,
			'<' . $tag . ' class="wp-block-list ' . $attrs['className'] . '">' . implode( "\n\n", $lis ) . '</' . $tag . '>'
		);
	}

	/**
	 * The blocks of a run of text: paragraphs, lists and tables.
	 *
	 * @param int     $post_id Page ID.
	 * @param string  $key     Key for the unique IDs.
	 * @param array[] $blocks  Blocks of the data file.
	 * @return string[]
	 */
	private static function text( $post_id, $key, $blocks ) {
		$out = array();

		foreach ( $blocks as $index => $block ) {
			$id   = $key . '-' . $index;
			$type = isset( $block['type'] ) ? $block['type'] : 'p';

			if ( 'table' === $type ) {
				// On a phone the table is wider than the screen and scrolls
				// sideways (style.css, "vm-legal-table"). This line says so.
				// Its Section is hidden on desktop and tablet.
				$out[] = Velocity_Seed::column(
					$post_id,
					$id . '-hint',
					Velocity_Home::t(
						$post_id,
						$id . '-hint-text',
						'Swipe to see the full table &rarr;',
						array(
							'size'   => array( 14, '', '' ),
							'lh'     => 1.4,
							'weight' => 500,
							'align'  => 'right',
							'color'  => Velocity_Seed::color( 'palette2' ),
							'margin' => array( '0', '', '8', '' ),
						)
					),
					array(
						'vsdesk'   => true,
						'vstablet' => true,
						'metadata' => array( 'name' => 'Swipe hint (phone only)' ),
					)
				);
				$out[] = Velocity_Programs::table(
					$post_id,
					$id,
					$block['head'],
					$block['rows'],
					array(
						'size'    => 16,
						// The design: columns of 202, 416 and 80px with 20px
						// between them, a line under each row, no stripes.
						'widths'  => array( 30, 56.5, 13.5 ),
						'lines'   => true,
						'head'    => '400',
						'lh'      => 1.5,
						'padding' => array( '16', '20', '16', '0' ),
						'margin'  => array( '', '', '24', '' ),
						'class' => 'vm-legal-table',
						'name'  => 'Table',
					)
				);
			} elseif ( 'ul' === $type || 'ol' === $type ) {
				$out[] = self::items( $block['items'], 'ol' === $type, isset( $block['level'] ) ? (int) $block['level'] : 1 );
			} else {
				$next  = isset( $blocks[ $index + 1 ]['type'] ) ? $blocks[ $index + 1 ]['type'] : 'p';
				$out[] = self::paragraph( $post_id, $id, $block['html'], 'ul' === $next || 'ol' === $next );
			}
		}

		return $out;
	}

	/**
	 * The card with the list of sections: Kadence's Table of Contents.
	 *
	 * Every look it has is a setting of the block: the card, the size and the
	 * colors of the links, the color of the section in view, and the scroll to
	 * a section on a click. The lines between the links and the weight of the
	 * section in view are in style.css ("vm-legal-toc"): the block has no
	 * setting for them.
	 *
	 * On a phone and a tablet there is no room for a column, so the page has
	 * a second one ("bar"): closed, with a title that opens and closes it,
	 * both settings of the block too.
	 *
	 * @param int  $post_id Page ID.
	 * @param bool $bar     Whether it is the one that opens and closes.
	 * @return string
	 */
	private static function toc( $post_id, $bar = false ) {
		$line = array( '#e5e5e5', '', 1 );
		$more = array();

		if ( $bar ) {
			$more = array(
				'uniqueID'           => Velocity_Seed::uid( $post_id, 'toc-bar' ),
				'enableTitle'        => true,
				'title'              => 'On this page',
				'titleColor'         => Velocity_Seed::color( 'palette3' ),
				'titleSize'          => array( '16', '', '' ),
				'titleFontWeight'    => '600',
				// Closed until its title is pressed.
				'enableToggle'       => true,
				'enableTitleToggle'  => true,
				'startClosed'        => true,
				'toggleIcon'         => 'arrow',
				'containerPadding'   => array( '14', '20', '14', '20' ),
				'contentSize'        => array( '16', '', '' ),
				'contentMargin'      => array( '14', '0', '0', '0' ),
				// The fixed header and this bar itself.
				'smoothScrollOffset' => 142,
				'className'          => 'vm-legal-toc vm-legal-toc--bar',
			);
		}

		return Velocity_Seed::block(
			'kadence/tableofcontents',
			array_merge( array(
				'uniqueID'            => Velocity_Seed::uid( $post_id, 'toc' ),
				// The H2 headings only.
				'allowedHeaders'      => array(
					array(
						'h1' => false,
						'h2' => true,
						'h3' => false,
						'h4' => false,
						'h5' => false,
						'h6' => false,
					),
				),
				'enableTitle'         => false,
				'listStyle'           => 'none',
				'linkStyle'           => 'plain',
				'containerPadding'    => array( '32', '32', '32', '32' ),
				'containerBackground' => Velocity_Seed::color( 'palette9' ),
				'borderStyle'         => array(
					array(
						'top'    => $line,
						'right'  => $line,
						'bottom' => $line,
						'left'   => $line,
						'unit'   => 'px',
					),
				),
				'borderRadius'        => array( '8', '8', '8', '8' ),
				'displayShadow'       => true,
				'shadow'              => array(
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
				'contentColor'        => Velocity_Seed::color( 'palette3' ),
				'contentHoverColor'   => Velocity_Seed::color( 'palette2' ),
				'contentActiveColor'  => Velocity_Seed::color( 'palette2' ),
				'contentSize'         => array( '18', '', '16' ),
				'contentLineHeight'   => array( '20', '', '' ),
				'contentMargin'       => array( '0', '0', '0', '0' ),
				'listGap'             => array( '15', '', '' ),
				// The section in view is marked, and a click scrolls to it,
				// stopping under the fixed header.
				'enableScrollSpy'     => true,
				'enableSmoothScroll'  => true,
				'smoothScrollOffset'  => 120,
				'className'           => 'vm-legal-toc',
			), $more )
		);
	}

	/**
	 * A section heading. The sections of the list are H2, the others H3: both
	 * look the same. Figma style "H3 Blog": Poppins 24px.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $text    Heading.
	 * @param bool   $listed  Whether the heading is in the list of sections.
	 * @return string
	 */
	private static function heading( $post_id, $key, $text, $listed ) {
		return Velocity_Home::t(
			$post_id,
			$key,
			$text,
			array(
				'tag'    => $listed ? 'h2' : 'h3',
				'size'   => array( 24, '', 22 ),
				'lh'     => 1.1,
				'weight' => 400,
				'family' => 'heading',
				'color'  => Velocity_Seed::color( 'palette3' ),
				// 60px from the section before, 24px to its text.
				'margin' => array( '60', '', '24', '' ),
			)
		);
	}

	/**
	 * The page.
	 *
	 * @param int   $post_id Page ID.
	 * @param array $data    Text of the page: title, subtitle, toc, intro, sections.
	 * @return string
	 */
	public static function content( $post_id, $data ) {
		// The headings in the list of sections. The design shortens two of
		// them in the list ("toc"); the block prints the heading as it is.
		$listed = isset( $data['toc_headings'] ) ? $data['toc_headings'] : ( isset( $data['toc'] ) ? $data['toc'] : array() );
		$listed = array_map( 'strtolower', $listed );

		$main = array(
			// The list of sections of a phone and a tablet: a bar that stays
			// under the header while the text scrolls, and opens on a press.
			Velocity_Seed::column(
				$post_id,
				'bar',
				self::toc( $post_id, true ),
				array(
					'vsdesk'       => true,
					'sticky'       => true,
					// The height of the header of a phone and a tablet, and a little air.
					'stickyOffset' => array( '', 64, 64 ),
					'zIndex'       => 20,
					'margin'       => array( '', '', '32', '' ),
					'metadata'     => array( 'name' => 'Sections (phone and tablet)' ),
				)
			),
			Velocity_Home::t(
				$post_id,
				'title',
				esc_html( $data['title'] ),
				array(
					'tag'    => 'h1',
					'color'  => Velocity_Seed::color( 'palette1' ),
					'margin' => array( '0', '', '24', '' ),
				)
			),
		);

		if ( ! empty( $data['subtitle'] ) ) {
			$main[] = Velocity_Home::t(
				$post_id,
				'subtitle',
				$data['subtitle'],
				array(
					'size'   => array( 24, '', 20 ),
					'lh'     => 1.4,
					'color'  => Velocity_Seed::color( 'palette3' ),
					'margin' => array( '0', '', '38', '' ),
				)
			);
		}

		$main = array_merge( $main, self::text( $post_id, 'intro', isset( $data['intro'] ) ? $data['intro'] : array() ) );

		foreach ( $data['sections'] as $index => $section ) {
			$key    = 'section-' . $index;
			$main[] = self::heading(
				$post_id,
				$key,
				esc_html( $section['heading'] ),
				// With no list in the data, every section is listed.
				! $listed || in_array( strtolower( $section['heading'] ), $listed, true )
			);
			$main   = array_merge( $main, self::text( $post_id, $key, $section['blocks'] ) );
		}

		$side = Velocity_Seed::column(
			$post_id,
			'side',
			self::toc( $post_id ),
			array(
				// Stays in view while the text scrolls, under the fixed header.
				'sticky'       => true,
				'stickyOffset' => array( 120, '', '' ),
				// The card starts 44px under the top of the title.
				'padding'      => array( '44', '', '', '' ),
				// A phone and a tablet have the bar above the title instead.
				'vstablet'     => true,
				'vsmobile'     => true,
				'metadata'     => array( 'name' => 'Sections' ),
			)
		);

		$text = Velocity_Seed::column(
			$post_id,
			'main',
			implode( "\n\n", $main ),
			array(
				// Keeps the lines of text at the 738px of the design.
				'padding'       => array( '', '118', '', '' ),
				'tabletPadding' => array( '', '0', '', '' ),
				'metadata'      => array( 'name' => 'Text' ),
			)
		);

		return Velocity_Seed::row(
			$post_id,
			'legal',
			array( $side, $text ),
			array(
				'align'             => 'full',
				'bgColor'           => Velocity_Seed::color( 'palette9' ),
				'inheritMaxWidth'   => true,
				// A card of 343px, as in the design.
				'firstColumnWidth'  => 30,
				'secondColumnWidth' => 70,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 80, 40, 32 ),
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				'collapseGutter'    => 'custom',
				'customRowGutter'   => array( 40, 40, 32 ),
				'padding'           => array( '83', '', '72', '' ),
				'mobilePadding'     => array( '48', '', '64', '' ),
				'metadata'          => array( 'name' => 'Legal page' ),
			)
		);
	}

	/**
	 * The "page not found" screen. Figma: "404".
	 *
	 * It is a Kadence Element (Kadence > Elements, "404 page") that takes the
	 * place of the theme's own 404 content, so it is edited with blocks.
	 *
	 * "previous page" is a link with the class vm-back: inc/not-found.php
	 * makes it go one step back in the browser's history. Its address is the
	 * home page, for a visitor who has no page to go back to.
	 *
	 * @param int $post_id Element ID.
	 * @return string
	 */
	public static function not_found( $post_id ) {
		$white = Velocity_Seed::color( 'palette9' );
		$home  = esc_url( home_url( '/' ) );
		$text  = array(
			'align'      => 'center',
			'size'       => array( 20, '', 18 ),
			'lh'         => 1.31,
			'color'      => $white,
			'link'       => $white,
			'link_hover' => Velocity_Seed::color( 'palette1' ),
		);

		$inner = array(
			// WordPress's own class for a centered image.
			Velocity_Home::image( $post_id, 'icon', 'icon-report.svg', 35, '', 'aligncenter', 'legal' ),
			Velocity_Home::t(
				$post_id,
				'code',
				'404',
				array_merge(
					$text,
					array(
						'color'  => '#e6eff8',
						'margin' => array( '13', '', '0', '' ),
					)
				)
			),
			Velocity_Home::t(
				$post_id,
				'title',
				'Page not found',
				array(
					'tag'    => 'h1',
					'align'  => 'center',
					'color'  => '#e0e5f7',
					'margin' => array( '0', '', '24', '' ),
				)
			),
			Velocity_Home::t(
				$post_id,
				'text',
				'We couldn’t find the page you were looking for.<br>Go back to the <a href="' . $home . '" class="vm-back">previous page</a> or go to the <a href="' . $home . '">Velocity homepage</a>.',
				array_merge( $text, array( 'margin' => array( '0', '', '0', '' ) ) )
			),
		);

		return Velocity_Home::section(
			$post_id,
			'not-found',
			implode( "\n\n", $inner ),
			'Page not found',
			array(
				'bgColor'       => Velocity_Seed::color( 'palette3' ),
				'maxWidth'      => 840,
				'padding'       => array( '96', '', '96', '' ),
				'mobilePadding' => array( '72', '', '72', '' ),
			)
		);
	}
}
