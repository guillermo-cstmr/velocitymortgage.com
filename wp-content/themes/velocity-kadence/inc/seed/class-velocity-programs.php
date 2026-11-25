<?php
/**
 * Builds the Programs Overview page as block content.
 *
 * Like the home page, this only produces the first version of the page.
 * After `wp velocity seed-programs-page` the page is edited in the block
 * editor. It follows the same rule: what a block setting can express is set
 * on the block. The classes used here are listed in docs/components.md.
 *
 * The four programs reuse what the home page uses for its own: the eyebrow,
 * and the "Program: cut left" and "Program: cut right" image styles, which
 * draw the diagonal cut and the orange bar on desktop and show the photo
 * whole, with rounded corners, on tablet and phones.
 *
 * Figma: page "Broker Programs", frame "Visual Design - Programs Overview".
 * The copy is the one in the design. The answers to the frequently asked
 * questions the design shows closed come from the copy document (V1d).
 *
 * Kept compatible with PHP 7.4, see class-velocity-cli.php.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Programs Overview page seed content.
 */
class Velocity_Programs {

	/**
	 * Slug of the page this class fills.
	 */
	const SLUG = 'loan-programs';

	/**
	 * Address of each program's own page. Order: 30-Year Fixed, Fast50,
	 * Flex I/O, Super Jumbo.
	 *
	 * @return string[]
	 */
	private static function links() {
		return array(
			Velocity_Program::url( '30-year-fixed' ),
			Velocity_Program::url( 'fast50' ),
			Velocity_Program::url( 'flex-io' ),
			Velocity_Program::url( 'super-jumbo' ),
		);
	}

	/**
	 * A Kadence Image block showing a file from assets/images/programs.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $file    File name.
	 * @param int    $width   Max width in px.
	 * @param string $alt     Alternative text. Empty for decorative images.
	 * @param string $class   Extra class, such as a block style.
	 * @return string
	 */
	private static function image( $post_id, $key, $file, $width, $alt = '', $class = '' ) {
		return Velocity_Home::image( $post_id, $key, $file, $width, $alt, $class, 'programs' );
	}

	/**
	 * Hero: title, intro and the main button beside the illustration.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function hero( $post_id ) {
		$text =
			Velocity_Home::t(
				$post_id,
				'hero-title',
				'Four Programs, <br><strong>One Perfect Fit</strong>',
				array(
					'tag'    => 'h1',
					// Looser than the 1.1 of every other h1: here the "g" of
					// "Programs" sits right above the "f" of "Perfect", in bold,
					// and at 1.1 the two letters touch.
					'lh'     => 1.2,
					'margin' => array( '0', '', '32', '' ),
					'class'  => 'is-style-vm-reveal-lines',
				)
			) . "\n\n" .
			Velocity_Home::subtitle(
				$post_id,
				'hero',
				'Whether your client needs a long-term loan or a short-term bridge, we’ve got it here. Compare loan amounts, LTV limits, and time to close for each below.',
				array( 'margin' => array( '0', '', '32', '' ) )
			) . "\n\n" .
			Velocity_Home::buttons( $post_id, 'hero', array( array( 'Become a Partner', home_url( '/become-a-partner/' ) ) ), 'left' );

		// The four programs around the photo, in the order they come in.
		$labels = array(
			array( 'diamond', 'vm-badge--top-right', 'Super Jumbo' ),
			array( 'gesture', 'vm-badge--upper-left', 'Flex I/O' ),
			array( 'acute', 'vm-badge--lower-right', 'Fast50' ),
			array( 'calendar', 'vm-badge--bottom-left', '30-Year Fixed' ),
		);

		$badges = array();
		foreach ( $labels as $label ) {
			$badges[] = array(
				'icon'           => $label[0],
				'at'             => $label[1],
				'padding'        => array( '14', '16', '14', '16' ),
				'mobile_padding' => array( '10', '12', '10', '12' ),
				'lines'          => array( array( $label[2], '500' ) ),
			);
		}

		// 527px in the design: the photo is 488, and Super Jumbo's label
		// reaches 39px past its right side.
		$art = Velocity_Home::photo_badges(
			$post_id,
			'hero-art',
			self::image( $post_id, 'hero-art', 'photo-overview.webp', 488, 'A client receiving the keys to a property' ),
			$badges,
			527
		);

		return Velocity_Seed::row(
			$post_id,
			'hero',
			array(
				Velocity_Seed::column( $post_id, 'hero-text', $text, array( 'verticalAlignment' => 'middle' ) ),
				Velocity_Seed::column(
					$post_id,
					'hero-art-col',
					$art,
					array(
						'id'                => 2,
						'verticalAlignment' => 'middle',
					)
				),
			),
			array(
				'align'             => 'full',
				'bgColor'           => Velocity_Seed::color( 'palette3' ),
				'textColor'         => Velocity_Seed::color( 'palette9' ),
				'inheritMaxWidth'   => true,
				'firstColumnWidth'  => 56,
				'secondColumnWidth' => 44,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 64, 40, '' ),
				'verticalAlignment' => 'middle',
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				'padding'           => array( '96', '', '96', '' ),
				'tabletPadding'     => array( '64', '', '64', '' ),
				'mobilePadding'     => array( '48', '', '48', '' ),
				'metadata'          => array( 'name' => 'Hero' ),
			)
		);
	}

	/**
	 * Trust strip: two figures on the orange band, as on the home page.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function stats( $post_id ) {
		return Velocity_Home::stats(
			$post_id,
			array(
				array( 'Foreign Investors', 'Eligible on every program' ),
				array( 'Business purpose', 'Investment property only' ),
			),
			// The two sit together at the middle of the band, 477px from each
			// side in the design. vm-stats--spread keeps each as wide as its text.
			array(
				'maxWidth'  => 615,
				'className' => 'vm-stats vm-stats--spread',
			),
			22
		);
	}

	/**
	 * Five ways to use the programs: three cards, then two centered under them.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function ways( $post_id ) {
		$cards = array(
			array( 'icon-storefront.svg', 'Finance Commercial Property', 'Office, retail, warehouse, or self-storage. 30-Year Fixed or Fast50 can fund them all.' ),
			array( 'icon-mixed-use.svg', 'Fund a Mixed-Use for 30 Years', 'Buildings that mix shops and homes fit 30-Year Fixed and Fast50. No unit cap either.' ),
			array( 'icon-cottage.svg', 'Finance an Assisted Living Home', 'We treat 1-4 unit care homes just like we do rentals. Fund them with a 30-Year Fixed.' ),
			array( 'icon-multiple.svg', 'Place Multiple Rentals on One Loan', 'Two or more rentals in the same county? Put them all on the same 30-Year Fixed program.' ),
			array( 'icon-globe.svg', 'Lend to Clients From Other Countries', 'Investors with U.S. credit can get up to 75% LTV. Investors without can get up to 65%.' ),
		);

		$grids = array();
		foreach ( array_chunk( $cards, 3 ) as $row_index => $row_cards ) {
			$columns = array();
			foreach ( $row_cards as $index => $card ) {
				$key   = 'way-' . $row_index . '-' . $index;
				$attrs = Velocity_Home::card(
					array_merge(
						array(
							'height'            => array( 263, '', 0 ),
							'textAlign'         => array( 'center', '', '' ),
							'justifyContent'    => array( 'center', '', '' ),
							'verticalAlignment' => 'middle',
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
					// The icon has a Section of its own. Directly inside a card with a
					// set height, Kadence would stretch an SVG to fill it.
					Velocity_Seed::column(
						$post_id,
						$key . '-icon',
						self::image( $post_id, $key, $card[0], 35 ),
						array( 'metadata' => array( 'name' => 'Icon' ) )
					) . "\n\n" .
					Velocity_Home::card_title( $post_id, $key, $card[1] ) . "\n\n" .
					Velocity_Home::body( $post_id, $key, $card[2] ),
					$attrs
				);
			}

			$row_attrs = array(
				'columnGutter'       => 'custom',
				'customGutter'       => array( 20, '', '' ),
				'columnsInnerHeight' => true,
				'tabletLayout'       => 'row',
				'mobileLayout'       => 'row',
				'padding'            => array( '0', '', '24', '' ),
				'metadata'           => array( 'name' => 'Cards row ' . ( $row_index + 1 ) ),
			);

			// The last row holds two cards, as wide as the three above, centered.
			if ( count( $row_cards ) < 3 ) {
				$row_attrs['maxWidth'] = 841;
			}

			$grids[] = Velocity_Seed::row( $post_id, 'ways-grid-' . $row_index, $columns, $row_attrs );
		}

		return Velocity_Home::section(
			$post_id,
			'ways',
			Velocity_Home::section_header(
				$post_id,
				'ways',
				'Five Ways to Put Our Programs to Use',
				'Commercial or mixed-use properties, care homes or multiple rentals, even foreign borrower loans—discover the diverse uses of our four programs.',
				831
			) . "\n\n" . implode( "\n\n", $grids ),
			'Five ways to use the programs',
			array(
				'padding'       => array( '96', '', '72', '' ),
				'mobilePadding' => array( '64', '', '40', '' ),
			)
		);
	}

	/**
	 * The three figures under a program: label, figure and note, between two rules.
	 *
	 * @param int     $post_id Page ID.
	 * @param string  $key     Key for the unique IDs.
	 * @param array[] $stats   Each: label, figure, note.
	 * @return string
	 */
	private static function program_stats( $post_id, $key, $stats ) {
		$indigo  = Velocity_Seed::color( 'palette5' );
		$columns = array();

		foreach ( $stats as $index => $stat ) {
			$stat_key = $key . '-stat-' . $index;
			$attrs    = array(
				'rowGapVariable' => array( 'custom', '', '' ),
				'rowGap'         => array( 12, '', 8 ),
				'metadata'       => array( 'name' => $stat[0] ),
			);
			if ( $index ) {
				$attrs['id'] = $index + 1;
			}

			$columns[] = Velocity_Seed::column(
				$post_id,
				$stat_key,
				Velocity_Home::t(
					$post_id,
					$stat_key . '-label',
					$stat[0],
					array(
						'color'  => $indigo,
						'margin' => array( '0', '', '0', '' ),
					)
				) . "\n\n" .
				Velocity_Home::t(
					$post_id,
					$stat_key . '-figure',
					$stat[1],
					array(
						'family' => 'heading',
						'size'   => array( 30, 24, 24 ),
						'weight' => '600',
						'lh'     => 1.1,
						'margin' => array( '0', '', '0', '' ),
					)
				) . "\n\n" .
				Velocity_Home::t(
					$post_id,
					$stat_key . '-note',
					$stat[2],
					array(
						'color'  => $indigo,
						'size'   => array( 14, '', '' ),
						'lh'     => 1.1,
						'margin' => array( '0', '', '0', '' ),
					)
				),
				$attrs
			);
		}

		$rule = array( 'rgba(43,53,120,0.45)', '', 1 );
		$none = array( '', '', '' );

		return Velocity_Seed::row(
			$post_id,
			$key . '-stats',
			$columns,
			array(
				'columnGutter' => 'custom',
				'customGutter' => array( 20, '', 16 ),
				'mobileLayout' => 'row',
				'padding'      => array( '14', '8', '14', '8' ),
				// A rule above and one below: top and bottom borders only.
				'borderStyle'  => array(
					array(
						'top'    => $rule,
						'right'  => $none,
						'bottom' => $rule,
						'left'   => $none,
						'unit'   => 'px',
					),
				),
				'metadata'     => array( 'name' => 'Figures' ),
			)
		);
	}

	/**
	 * The four programs: photo and text on alternating sides, each with its
	 * three figures underneath.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function programs( $post_id ) {
		$close    = 'From submission to funding';
		$links    = self::links();
		// Super Jumbo comes before Flex I/O here: the order of the approved copy.
		$links    = array( $links[0], $links[1], $links[3], $links[2] );
		$programs = array(
			array(
				'Long-term loan',
				'30-Year Fixed, Built to Buy and Hold',
				'Buy, refinance, or cash out on residential or commercial property with a rate that never changes.',
				'Explore 30-Year Fixed',
				'program-30-year-fixed.webp',
				'A shop front under an awning, on the ground floor of a stone building',
				array(
					array( 'Loan amounts', '$75K to $2MM', '1-4 units; commercial from $100K' ),
					array( 'LTV limit', 'Up to 75%', '70% on office, retail, and similar' ),
					array( 'Time to close', '2 weeks to 45 days', $close ),
				),
			),
			array(
				'Long-term loan',
				'Fast50, Equity Does the Talking',
				'Like the 30-Year Fixed, with the weight on property equity—up to 50% of the property’s value.',
				'Explore Fast50',
				'program-fast50.webp',
				'A row of gabled houses against a blue sky',
				array(
					array( 'Loan amounts', 'Up to $2MM', '1-4 units; commercial from $100K' ),
					array( 'LTV limit', 'Up to 50%', 'On every property type' ),
					array( 'Time to close', '2 weeks to 45 days', $close ),
				),
			),
			array(
				'Long-term loan',
				'Super Jumbo, Large-Balance Financing',
				'The 30-Year Fixed for luxury property, $2MM to $10MM, for seasoned investors in major metro areas.',
				'Explore Super Jumbo',
				'program-super-jumbo.webp',
				'A modern house with a lit pool at dusk',
				array(
					array( 'Loan amounts', '$2MM to $10MM', '1-4 units only' ),
					array( 'LTV limit', 'Up to 65%', 'Same on a purchase or refinance' ),
					array( 'Time to close', '2 to 4 weeks', $close ),
				),
			),
			array(
				// The design repeats "Long-term loan" here. Flex I/O is the short-term one.
				// It comes last, after Super Jumbo: the order of the approved copy.
				'Short-term loan',
				'Flex I/O, a Bridge Loan Between Projects',
				'Interest-only payments for 24 months, with no prepay penalty. Available on 1-4 unit properties.',
				'Explore Flex I/O',
				'program-flex-io.webp',
				'A brick apartment building with balconies',
				array(
					array( 'Loan amounts', '$75K to $2MM', '1-4 units only' ),
					array( 'LTV limit', 'Up to 75%', '70% above $1MM' ),
					array( 'Time to close', '2 to 4 weeks', $close ),
				),
			),
		);

		$rows = array();
		foreach ( $programs as $index => $program ) {
			$key         = 'program-' . $index;
			$image_first = 0 === $index % 2;
			// Image left: blocks travel right. Image right: they travel left.
			$slide = $image_first ? 'fade-right' : 'fade-left';

			$text = Velocity_Seed::column(
				$post_id,
				$key . '-text',
				// vm-eyebrow adds the orange dot. Everything else is block settings.
				Velocity_Home::t(
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
				Velocity_Home::t(
					$post_id,
					$key . '-title',
					$program[1],
					array(
						'tag'    => 'h3',
						'margin' => array( '0', '', '24', '' ),
					)
				) . "\n\n" .
				Velocity_Home::subtitle( $post_id, $key, $program[2], array( 'margin' => array( '0', '', '32', '' ) ) ) . "\n\n" .
				Velocity_Home::buttons( $post_id, $key, array( array( $program[3], $links[ $index ] ) ), 'left' ),
				array(
					'id'                => $image_first ? 2 : 1,
					'verticalAlignment' => 'middle',
				) + Velocity_Home::card_animation( 1, $slide )
			);

			$image = Velocity_Seed::column(
				$post_id,
				$key . '-image',
				// The photo is a plain rectangle; the cut and the orange bar come from
				// the image's block style. See inc/block-styles.php.
				self::image( $post_id, $key, $program[4], 542, $program[5], $image_first ? 'is-style-vm-cut-right' : 'is-style-vm-cut-left' ),
				array(
					'id'                => $image_first ? 1 : 2,
					'verticalAlignment' => 'middle',
				) + Velocity_Home::card_animation( 0, $slide )
			);

			$row = Velocity_Seed::row(
				$post_id,
				$key,
				$image_first ? array( $image, $text ) : array( $text, $image ),
				array(
					'verticalAlignment' => 'middle',
					'columnGutter'      => 'custom',
					'customGutter'      => array( 33, 32, '' ),
					'tabletLayout'      => 'row',
					'mobileLayout'      => 'row',
					// On tablet and mobile the image always comes first.
					'collapseOrder'     => $image_first ? 'left-to-right' : 'right-to-left',
					'padding'           => array( '0', '', '32', '' ),
					'metadata'          => array( 'name' => 'Image and text' ),
				)
			);

			$rows[] = Velocity_Seed::row(
				$post_id,
				$key . '-wrap',
				array(
					Velocity_Seed::column(
						$post_id,
						$key . '-wrap-col',
						$row . "\n\n" . self::program_stats( $post_id, $key, $program[6] )
					),
				),
				array(
					'maxWidth' => 1062,
					'padding'  => array( '0', '', $index === count( $programs ) - 1 ? '0' : '64', '' ),
					'metadata' => array( 'name' => 'Program: ' . $program[1] ),
				)
			);
		}

		$inner =
			Velocity_Home::section_header(
				$post_id,
				'programs',
				'Choose the Loan Your Client Needs',
				'Your clients don’t all fit one loan, so we built four. More ways to “yes,” more ways to build your book and move your clients forward.',
				850
			) . "\n\n" . implode( "\n\n", $rows );

		return Velocity_Home::panel(
			$post_id,
			'programs',
			$inner,
			'The four programs',
			array(
				'background' => '#f2f4fc',
				'padding'    => array( '96', '64', '96', '64' ),
			)
		);
	}

	/**
	 * The List block of a table cell, with no space around it.
	 *
	 * An item is a string. An item with items of its own, such as
	 * "Refinances:" and its three limits, is array( text, items ).
	 *
	 * @param array $items Items.
	 * @return string
	 */
	private static function cell_list( $items ) {
		$out = '';

		foreach ( $items as $item ) {
			if ( is_array( $item ) ) {
				$out .= Velocity_Seed::block( 'list-item', array(), '<li>' . $item[0] . self::cell_list( $item[1] ) . '</li>' );
			} else {
				$out .= Velocity_Seed::block( 'list-item', array(), '<li>' . $item . '</li>' );
			}
		}

		return Velocity_Seed::block(
			'list',
			array(
				'style' => array(
					'spacing' => array(
						'margin'  => array(
							'top'    => '0',
							'bottom' => '0',
						),
						'padding' => array( 'left' => '18px' ),
					),
				),
			),
			'<ul style="margin-top:0;margin-bottom:0;padding-left:18px" class="wp-block-list">' . $out . '</ul>'
		);
	}

	/**
	 * A Kadence Table block.
	 *
	 * The striped rows, the cell padding, the column widths and the text
	 * sizes are all block settings. Each cell holds blocks of its own: a Text
	 * (Adv), or a List for several items.
	 *
	 * @param int      $post_id Page ID.
	 * @param string   $key     Key for the unique IDs.
	 * @param string[] $head    Header cells. May contain inline HTML.
	 * @param array[]  $rows    Rows of cells. A cell is a string, or a list of items.
	 * @param array    $o       Options: size (text size in px, 14), first (width of
	 *                          the first column in percent, 22), label (weight of
	 *                          a row's label, 400), class and name. For a table
	 *                          with lines instead of stripes: lines (true),
	 *                          widths (percent of every column), head (weight
	 *                          of the header, 600), lh (line height, 1.3) and
	 *                          padding (of a cell: top, right, bottom, left) and
	 *                          margin (of the table).
	 * @return string
	 */
	public static function table( $post_id, $key, $head, $rows, $o = array() ) {
		$o = array_merge(
			array(
				'size'    => 14,
				'first'   => 22,
				'label'   => '400',
				'lines'   => false,
				'widths'  => array(),
				'head'    => '600',
				'lh'      => 1.3,
				'padding' => array( '16', '16', '16', '16' ),
				'margin'  => array(),
				// Keeps a readable width on small screens and scrolls sideways. See style.css.
				'class'   => 'vm-compare',
				'name'    => 'Comparison table',
			),
			$o
		);

		$plain = array(
			'size'   => array( $o['size'], '', '' ),
			'weight' => '400',
			'lh'     => $o['lh'],
			'margin' => array( '0', '', '0', '' ),
		);

		$lines = array( $head );
		foreach ( $rows as $row ) {
			$lines[] = $row;
		}

		$table_rows = array();
		foreach ( $lines as $r => $line ) {
			$cells = array();
			foreach ( $line as $c => $cell ) {
				$cell_key = $key . '-' . $r . '-' . $c;

				if ( is_array( $cell ) ) {
					// Several items: a List block, with no space around it.
					$inner = self::cell_list( $cell );
				} elseif ( 0 === $r && $c ) {
					// A program's name, in the header's own size and weight.
					$inner = Velocity_Home::t(
						$post_id,
						$cell_key,
						$cell,
						array(
							'margin'     => array( '0', '', '0', '' ),
							'link'       => Velocity_Seed::color( 'palette3' ),
							'link_hover' => Velocity_Seed::color( 'palette2' ),
						)
					);
				} else {
					$text = $plain;
					if ( 0 === $r ) {
						$text['size'] = array( 16, '', '' );
					}
					if ( 0 === $c ) {
						$text['weight'] = $o['label'];
					}

					$inner = Velocity_Home::t( $post_id, $cell_key, $cell, $text );
				}

				$cells[] = Velocity_Seed::block(
					'kadence/table-data',
					array(
						'uniqueID' => Velocity_Seed::uid( $post_id, $cell_key . '-cell' ),
						'column'   => $c,
					),
					$inner
				);
			}

			$table_rows[] = Velocity_Seed::block(
				'kadence/table-row',
				array(
					'uniqueID' => Velocity_Seed::uid( $post_id, $key . '-row-' . $r ),
					'row'      => $r,
				),
				implode( "\n\n", $cells )
			);
		}

		// The first column names the row: 280px of 1280 in the design. The
		// other columns share the rest.
		$columns = array();
		foreach ( $head as $c => $cell ) {
			$columns[] = array(
				'useAuto' => $o['widths'] ? false : 0 !== $c,
				'width'   => $o['widths'] ? $o['widths'][ $c ] : ( 0 === $c ? $o['first'] : '' ),
				'unit'    => '%',
			);
		}

		$none = array( '', '', '' );

		$attrs = array(
			'uniqueID'            => Velocity_Seed::uid( $post_id, $key ),
			'columns'             => count( $head ),
			'isFirstRowHeader'    => true,
			'isFirstColumnHeader' => true,
			'evenOddBackground'   => true,
			'backgroundColorOdd'  => '#f2f4fc',
			'backgroundColorEven' => Velocity_Seed::color( 'palette9' ),
			'cellPadding'         => $o['padding'],
			'mobileCellPadding'   => $o['lines'] ? $o['padding'] : array( '12', '12', '12', '12' ),
			'headerAlign'         => 'left',
			'headerTypography'    => Velocity_Seed::typography( 16, $o['head'], 1.47 ),
			'dataTypography'      => Velocity_Seed::typography( $o['size'], '', $o['lh'] ),
			'columnSettings'      => $columns,
			'className'           => $o['class'],
			'borderStyle'         => array(
				array(
					'top'    => $none,
					'right'  => $none,
					'bottom' => $none,
					'left'   => $none,
					'unit'   => 'px',
				),
			),
			'metadata'            => array( 'name' => $o['name'] ),
		);

		if ( $o['margin'] ) {
			$attrs['margin'] = $o['margin'];
		}

		// Lines under the rows instead of stripes.
		if ( $o['lines'] ) {
			$attrs['evenOddBackground'] = false;
			$attrs['borderOnRowOnly']   = true;
			$attrs['borderStyle'][0]['bottom'] = array( '#d1d6de', 'solid', 1 );
			unset( $attrs['backgroundColorOdd'], $attrs['backgroundColorEven'] );
		}

		// No class leaves the table to shrink with the screen.
		if ( ! $attrs['className'] ) {
			unset( $attrs['className'] );
		}

		return Velocity_Seed::block(
			'kadence/table',
			$attrs,
			implode( "\n\n", $table_rows )
		);
	}

	/**
	 * The comparison: the four programs side by side, and the closing line.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function compare( $post_id ) {
		$links = self::links();
		$head  = array( 'Program Feature' );
		// Name => position of its address in links(). The table lists Super
		// Jumbo before Flex I/O, as the design does.
		$columns = array(
			'30-Year Fixed' => 0,
			'Fast50'        => 1,
			'Super Jumbo'   => 3,
			'Flex I/O'      => 2,
		);
		foreach ( $columns as $name => $index ) {
			$head[] = sprintf( '<a href="%s">%s</a>', esc_url( $links[ $index ] ), $name );
		}

		$fee  = array( 'A fee that steps down over five years' );
		$rows = array(
			array(
				'What It’s For',
				'Long-term financing to buy, keep, or cash out of investment property',
				'Long-term financing when credit history is the problem but equity is not',
				'The 30-Year Fixed on luxury 1-4 unit property, for seasoned investors in major metro areas',
				'Short-term loans on a 1-4 unit property the investor will sell or refinance',
			),
			array(
				'Term Details',
				array( 'Fixed for 30 years', 'Shorter terms available' ),
				array( 'Fixed for 30 years', 'Shorter terms available' ),
				array( 'Fixed for 30 years', 'Shorter terms available' ),
				array( '24 months, interest-only' ),
			),
			array(
				'Loan Amounts',
				array( '$75K to $2MM on 1-4 units', 'Commercial from $100K' ),
				array( 'Up to $2MM on 1-4 units', 'Commercial from $100K' ),
				array( '$2MM to $10MM on 1-4 units' ),
				array( '$75K to $2MM' ),
			),
			array(
				'LTV Limit',
				array( '75%', '70% on office, retail, and other commercial', 'Combined financing to 90% on purchases' ),
				array( '50%' ),
				array( '65%', 'Combined financing to 90% on purchases' ),
				array( '75%', '70% above $1MM' ),
			),
			array(
				'Credit Minimum',
				array( 'Ask your account executive' ),
				array( 'None' ),
				array( '600' ),
				array( '675' ),
			),
			array(
				'Property Types',
				array( '1-4 units and commercial, multifamily and mixed-use included', 'Super Jumbo 1-4 units only' ),
				array( 'Same as the 30-Year Fixed' ),
				array( '1-4 units only' ),
				array( '1-4 units only' ),
			),
			array(
				'Prepayment Fees',
				$fee,
				$fee,
				$fee,
				array( 'No fee' ),
			),
			array(
				'Time to Close',
				array( '1-4 units, 2 to 4 weeks', 'Commercial, 30 to 45 days' ),
				array( '1-4 units, 2 to 4 weeks', 'Commercial, 30 to 45 days' ),
				array( '2 to 4 weeks' ),
				array( '2 to 4 weeks' ),
			),
			array(
				'Foreign Borrower LTV',
				array( 'Investor 65%', 'National up to 75%' ),
				array( 'Eligible', 'The 50% limit applies to everyone' ),
				array( 'Investor 65%', 'National up to 65%, the loan’s limit' ),
				array( 'Investor 65%' ),
			),
		);

		$inner =
			Velocity_Home::t(
				$post_id,
				'compare-title',
				'See Our Programs Side by Side',
				array(
					'tag'    => 'h2',
					'margin' => array( '0', '', '16', '' ),
					'class'  => 'is-style-vm-reveal-lines',
				)
			) . "\n\n" .
			Velocity_Home::subtitle(
				$post_id,
				'compare',
				'We put all the numbers you want to know in one place. Compare each program here before getting the full picture on each program page.',
				array( 'margin' => array( '0', '', '48', '' ) )
			) . "\n\n" .
			// vm-compare--pick: on a phone the visitor picks two programs to see
			// side by side. See assets/js/compare.js.
			self::table( $post_id, 'compare-table', $head, $rows, array( 'class' => 'vm-compare vm-compare--pick' ) ) . "\n\n" .
			Velocity_Home::t(
				$post_id,
				'compare-closing',
				'Foreign nationals (with U.S. credit) qualify for up to 75% LTV and foreign investors (without U.S. credit) up to 65% LTV—subject to each program’s LTV limit. See program pages for full details.',
				array(
					'align'  => 'center',
					'family' => 'heading',
					'color'  => Velocity_Seed::color( 'palette2' ),
					'size'   => array( 30, 26, 22 ),
					'weight' => '600',
					'lh'     => 1.17,
					'width'  => 1000,
					'margin' => array( '80', 'auto', '0', 'auto' ),
				)
			);

		return Velocity_Home::section( $post_id, 'compare', $inner, 'Compare the programs' );
	}

	/**
	 * Who Velocity says yes to: title and intro beside four client profiles.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function clients( $post_id ) {
		$profiles = array(
			'Self-employed clients with tax returns that don’t show their full finances.',
			'Investors paying for their next property with equity instead of savings.',
			'Small business owners growing their company with equity they earn from rentals.',
			'Clients from outside the country, including both those with and without credit.',
		);

		$items = array();
		foreach ( $profiles as $index => $profile ) {
			$key     = 'client-' . $index;
			$items[] = Velocity_Seed::column(
				$post_id,
				$key,
				self::image( $post_id, $key, 'icon-check-circle.svg', 32 ) . "\n\n" .
				Velocity_Seed::column(
					$post_id,
					$key . '-text',
					Velocity_Home::t(
						$post_id,
						$key . '-line',
						$profile,
						array(
							// "Darker orange" in the design. It passes AA on this background.
							'color'  => '#b04c11',
							'margin' => array( '0', '', '0', '' ),
						)
					),
					array(
						'flexGrow' => array( 1, '', '' ),
						'metadata' => array( 'name' => 'Text' ),
					)
				),
				array(
					'background'        => 'rgba(211,112,49,0.16)',
					'borderStyle'       => Velocity_Home::border( Velocity_Seed::color( 'palette2' ) ),
					'borderRadius'      => array( 16, 16, 16, 16 ),
					'padding'           => array( '24', '24', '24', '24' ),
					'mobilePadding'     => array( '16', '16', '16', '16' ),
					'direction'         => array( 'horizontal', '', '' ),
					'wrapContent'       => array( 'nowrap', '', '' ),
					'verticalAlignment' => 'middle',
					'gutter'            => array( 13, '', '' ),
					'gutterVariable'    => array( 'custom', 'custom', 'custom' ),
					'metadata'          => array( 'name' => 'Profile ' . ( $index + 1 ) ),
				) + Velocity_Home::card_animation( $index )
			);
		}

		$intro =
			Velocity_Home::t(
				$post_id,
				'clients-title',
				'Say “Yes” to More Clients',
				array(
					'tag'    => 'h2',
					'margin' => array( '0', '', '16', '' ),
					'class'  => 'is-style-vm-reveal-lines',
				)
			) . "\n\n" .
			Velocity_Home::subtitle(
				$post_id,
				'clients',
				'We lend with simple documentation, focused on the property’s value—and that opens the door to a wider range of clients. Here are four you may recognize.'
			);

		$row = Velocity_Seed::row(
			$post_id,
			'clients-row',
			array(
				Velocity_Seed::column( $post_id, 'clients-intro', $intro, array( 'metadata' => array( 'name' => 'Title and intro' ) ) ),
				Velocity_Seed::column(
					$post_id,
					'clients-list',
					implode( "\n\n", $items ),
					array(
						'id'             => 2,
						'rowGapVariable' => array( 'custom', '', '' ),
						'rowGap'         => array( 16, '', '' ),
						'metadata'       => array( 'name' => 'Client profiles' ),
					)
				),
			),
			array(
				'firstColumnWidth'  => 41,
				'secondColumnWidth' => 59,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 20, 32, '' ),
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				'padding'           => array( '0', '', '0', '' ),
				'metadata'          => array( 'name' => 'Clients' ),
			)
		);

		return Velocity_Home::section(
			$post_id,
			'clients',
			$row,
			'Who we say yes to',
			array(
				'padding'       => array( '48', '', '48', '' ),
				'mobilePadding' => array( '32', '', '32', '' ),
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
			'Found Your Client’s Perfect Fit?',
			array(
				array( 'Become a Partner', home_url( '/become-a-partner/' ) ),
				array( 'See How It Works', home_url( '/process/' ), 'outline', Velocity_Home::on_dark() ),
			),
			1280
		);
	}

	/**
	 * Frequently asked questions.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function faq( $post_id ) {
		return Velocity_Home::faq_section(
			$post_id,
			array(
				array(
					'Which program fits a rental my client plans to keep?',
					array(
						'The 30-Year Fixed is the best program for clients planning on keeping an investment property. With this loan, payment never changes and no refinance is forced later on. It is paid down to zero over the term, with no lump sum at the end.',
						'If credit history is an obstacle, Fast50 is the same 30-year program but with no minimum credit score. It will fund up to half the property’s value. Both long-term loans are for rental or business assets, never your client’s home.',
					),
				),
				array(
					'What is the smallest and largest loan Velocity makes?',
					array(
						'The 30-Year Fixed runs from $75,000 to $2 million on 1-4 unit rentals. Super Jumbo carries the same loan from $2 million to $10 million. On commercial property, the 30-Year Fixed and Fast50 start at $100,000. Fast50 tops out at $2 million on 1-4 unit rentals, while Flex I/O runs from $75,000 to $2 million.',
						'Loan amounts, the LTV limit, and time to close appear in and above the comparison table on every program page. If your deal sits at the edge of a range, your account executive can tell you whether it works or what would make it work.',
					),
				),
				array(
					'What does LTV mean, and what are Velocity’s limits?',
					array(
						'By program, LTV limits are 75% for 30-Year Fixed or 70% on office, retail, and commercial property; up to 65% for Super Jumbo; up to 50% for Fast50; and up to 75% for Flex I/O (or 70% above $1 million).',
						'These numbers represent an upper limit. Your account executive will confirm where a deal lands.',
					),
				),
				array(
					'Does Velocity lend on commercial property?',
					array(
						'Yes. Both the 30-Year Fixed and Fast50 offer commercial loans for investment property like office, retail, warehouse, self-storage, and automotive, plus mixed-use buildings and buildings with five or more residential units. Commercial loans start at $100,000, on 30-year terms.',
						'This range lets us become the one lender across your client’s whole portfolio. The fourplex they bought last year and the small retail building they are adding can both sit on the same 30-year program with one account executive who knows both deals.',
					),
				),
				array(
					'Can a foreign national get an investment property loan?',
					array(
						'Yes. They can choose from all four programs. Our foreign national loan is the same as our 30-Year Fixed. A foreign national living in the U.S. with U.S. credit can borrow up to 75% of the property’s value, and a foreign investor living abroad with no U.S. credit can borrow up to 65%.',
						'Fast50’s 50% limit and Super Jumbo’s 65% limit apply to everyone. The property must be an investment instead of a residence, and the loan goes through you as the broker. Our team includes Spanish, Farsi, Chinese, and Vietnamese speakers when needed.',
					),
				),
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
				self::ways( $post_id ),
				self::programs( $post_id ),
				self::compare( $post_id ),
				self::clients( $post_id ),
				self::cta( $post_id ),
				self::faq( $post_id ),
			)
		);
	}
}
