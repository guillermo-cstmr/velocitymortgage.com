<?php
/**
 * Builds the Broker Tools page as block content.
 *
 * Like the other pages, this only produces the first version of the page.
 * After `wp velocity seed-toolkit-page` the page is edited in the block
 * editor. What a block setting can express is set on the block.
 *
 * The files listed on the page are not typed here. Each one is a post under
 * Broker Toolkit in the admin menu, and each list on the page is a Kadence
 * query that reads one section of them. The unlock form, its cookie and the
 * downloads are in the must-use plugin: cpt-toolkit.php and
 * resource-modal.php. See docs/database-and-content.md, "Broker Toolkit".
 *
 * Not to be confused with `seed-toolkit`, which creates the documents.
 *
 * Figma: page "Resources", section "Broker Toolkit".
 *
 * Kept compatible with PHP 7.4, see class-velocity-cli.php.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Broker Tools page seed content.
 */
class Velocity_Toolkit_Page {

	/**
	 * Slug of the page this class fills.
	 */
	const SLUG = 'broker-tools';

	/**
	 * Taxonomy that sorts the documents into the sections of the page.
	 */
	const TAXONOMY = 'velocity-toolkit-section';

	/**
	 * The sections of documents: name of the section in Broker Toolkit,
	 * Sections, and which card lists its documents.
	 *
	 * @return array[] By key.
	 */
	private static function sections() {
		return array(
			'residential' => array( 'Residential Forms Library', 'form' ),
			'commercial'  => array( 'Commercial Forms Library', 'form' ),
			'matrices'    => array( 'Matrices', 'document' ),
			'programs'    => array( 'Program Flyers', 'document' ),
			'foreign'     => array( 'Foreign Investor Flyers', 'document' ),
			'about'       => array( 'About Velocity', 'document' ),
			'customize'   => array( 'How to Customize a Flyer', 'document' ),
		);
	}

	/**
	 * The lists of this page: a Kadence query (Kadence, Queries) per
	 * section, in the order set by dragging the documents in the admin
	 * list. They share two cards (Kadence, Query Cards): one for a form of
	 * the forms libraries, one for every other document.
	 *
	 * @return array[] By section key, as Velocity_Seed::query_meta() takes them.
	 */
	public static function queries() {
		$cards = array(
			'form'     => array( array( __CLASS__, 'form_card' ), 'Toolkit: form card' ),
			'document' => array( array( __CLASS__, 'document_card' ), 'Toolkit: document card' ),
		);

		$queries = array();
		foreach ( self::sections() as $key => $section ) {
			$queries[ $key ] = array(
				'title'      => 'Toolkit: ' . $section[0],
				'post_type'  => 'velocity-toolkit-doc',
				'term_name'  => array( self::TAXONOMY, $section[0] ),
				'order_by'   => 'menu_order',
				'order'      => 'asc',
				'per_page'   => '50',
				'columns'    => array( '1', '1', '1' ),
				'column_gap' => array( '0', '0', '0' ),
				'row_gap'    => array( '0', '0', '0' ),
				'card'       => $cards[ $section[1] ][0],
				'card_key'   => $section[1],
				'card_title' => $cards[ $section[1] ][1],
			);
		}

		return $queries;
	}

	/**
	 * A download button of a document, inside a query card.
	 *
	 * Its link is one of the document's two download addresses, which the
	 * must-use plugin keeps in a custom field. The address is the site's
	 * own, never the file's. The label "Unlock to Download" turns into
	 * "Download" once the visitor has unlocked the toolkit.
	 *
	 * @param int    $card_id Query card post ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $label   Button label.
	 * @param string $field   Custom field with the address.
	 * @param int    $width   Width in px, or 0 for the width of its label.
	 * @return string
	 */
	private static function download_button( $card_id, $key, $label, $field, $width = 210 ) {
		$size = array( 'sizePreset' => 'small' );

		// A set width, so the button's column is as wide as the button and the
		// label never breaks.
		if ( $width ) {
			$size['widthType'] = 'fixed';
			$size['width']     = array( $width, '', '' );
		}

		return Velocity_Home::buttons(
			$card_id,
			$key,
			array(
				array(
					$label,
					"[kb-dynamic field='post|post_custom_field' custom='" . $field . "' para='kb_custom_input' type='url']",
					'fill',
					$size + array(
						'inQueryBlock'   => true,
						'kadenceDynamic' => array(
							'link' => array(
								'enable'   => true,
								'source'   => '',
								'field'    => 'post|post_custom_field',
								'custom'   => $field,
								'para'     => 'kb_custom_input',
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
	 * A small filled label, such as "New" or "White Label".
	 *
	 * @param int    $card_id Query card post ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $text    Text.
	 * @param array  $o       Extra options for Velocity_Home::t().
	 * @return string
	 */
	private static function label( $card_id, $key, $text, $o = array() ) {
		return Velocity_Home::t(
			$card_id,
			$key,
			$text,
			array_merge(
				array(
					'size'       => array( 12, '', '' ),
					'lh'         => 1.3,
					'color'      => Velocity_Seed::color( 'palette5' ),
					'background' => Velocity_Seed::color( 'palette7' ),
					'padding'    => array( '3', '8', '3', '8' ),
					'radius'     => array( 4, 4, 4, 4 ),
					'margin'     => array( '0', '', '8', '' ),
				),
				$o
			)
		);
	}

	/**
	 * A label centered over a button, only as wide as its text.
	 *
	 * @param int    $card_id Query card post ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $text    Text.
	 * @param string $class   Class for the row, or empty.
	 * @return string
	 */
	private static function label_row( $card_id, $key, $text, $class = '' ) {
		$attrs = array(
			'direction'      => array( 'horizontal', '', '' ),
			// Centered over the button, and at its left edge on phones.
			'justifyContent' => array( 'center', '', 'flex-start' ),
			'inQueryBlock'   => true,
			'metadata'       => array( 'name' => $text ),
		);
		if ( $class ) {
			$attrs['className'] = $class;
		}

		return Velocity_Seed::column( $card_id, $key . '-row', self::label( $card_id, $key, $text ), $attrs );
	}

	/**
	 * The card of a form in the forms libraries: its name, its file type
	 * and the download button, on one line.
	 *
	 * @param int $card_id Query card post ID.
	 * @return string
	 */
	public static function form_card( $card_id ) {
		$rule = array( '#d9d9d9', '', 1 );
		$none = array( '', '', '' );

		return Velocity_Seed::column(
			$card_id,
			'form',
			Velocity_Seed::column(
				$card_id,
				'form-name',
				Velocity_Home::t(
					$card_id,
					'form-title',
					'',
					array(
						'size'    => array( 14, '', '' ),
						'lh'      => 1.3,
						'margin'  => array( '0', '', '0', '' ),
						'dynamic' => 'post|post_title',
					)
				),
				array(
					'flexGrow'     => array( 1, '', '' ),
					'inQueryBlock' => true,
					'metadata'     => array( 'name' => 'Name' ),
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'form-type',
				'',
				array(
					'size'    => array( 12, '', '' ),
					'lh'      => 1.3,
					'margin'  => array( '0', '', '0', '' ),
					'dynamic' => 'post|vm_toolkit_file_type',
					'class'   => 'vm-hide-empty',
				)
			) . "\n\n" .
			self::download_button( $card_id, 'form', 'Download', 'velocity_toolkit_link', 0 ),
			array(
				'direction'         => array( 'horizontal', '', '' ),
				'wrapContent'       => array( 'nowrap', '', '' ),
				'verticalAlignment' => 'middle',
				'gutter'            => array( 16, '', 12 ),
				'gutterVariable'    => array( 'custom', 'custom', 'custom' ),
				'padding'           => array( '14', '16', '14', '16' ),
				'mobilePadding'     => array( '12', '0', '12', '0' ),
				// A rule under each form: a bottom border only.
				'borderStyle'       => array(
					array(
						'top'    => $none,
						'right'  => $none,
						'bottom' => $rule,
						'left'   => $none,
						'unit'   => 'px',
					),
				),
				'inQueryBlock'      => true,
				'metadata'          => array( 'name' => 'Form' ),
			)
		);
	}

	/**
	 * The card of a document: the "New" tag, its name and description on
	 * the left, and its download buttons on the right.
	 *
	 * A document with a White Label file shows two buttons, each under its
	 * label. One without shows a single button and no label: vm-toolkit-wl
	 * and vm-toolkit-branded hide themselves then. See resource-modal.php.
	 *
	 * @param int $card_id Query card post ID.
	 * @return string
	 */
	public static function document_card( $card_id ) {
		$rule = array( Velocity_Seed::color( 'palette5' ), '', 1 );
		$none = array( '', '', '' );

		$text = Velocity_Seed::column(
			$card_id,
			'doc-text',
			// The tag sits in a row of its own so it is only as wide as its text.
			Velocity_Seed::column(
				$card_id,
				'doc-new-row',
				self::label(
					$card_id,
					'doc-new',
					'',
					array(
						'color'      => '#b04c11',
						'background' => '#fde3cc',
						'dynamic'    => 'post|vm_toolkit_new',
						// Not printed for a document that is not new. See inc/dynamic-content.php.
						'class'      => 'vm-hide-empty',
					)
				),
				array(
					'direction'    => array( 'horizontal', '', '' ),
					'inQueryBlock' => true,
					'metadata'     => array( 'name' => 'New tag' ),
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'doc-title',
				'',
				array(
					'tag'     => 'h4',
					'family'  => 'body',
					'size'    => array( 16, '', '' ),
					'weight'  => '400',
					'lh'      => 1.47,
					'margin'  => array( '0', '', '0', '' ),
					'dynamic' => 'post|post_title',
				)
			) . "\n\n" .
			Velocity_Home::t(
				$card_id,
				'doc-excerpt',
				'',
				array(
					'size'    => array( 14, '', '' ),
					'lh'      => 1.3,
					'width'   => 700,
					// A max width centers a text. These margins keep it on the left.
					'margin'  => array( '12', 'auto', '0', '0' ),
					'dynamic' => 'post|post_excerpt',
					// Not printed for a document with no description.
					'class'   => 'vm-hide-empty',
				)
			),
			array(
				'flexGrow'     => array( 3, '', '' ),
				'inQueryBlock' => true,
				'metadata'     => array( 'name' => 'Name and description' ),
			)
		);

		$branded = Velocity_Seed::column(
			$card_id,
			'doc-branded',
			self::label_row( $card_id, 'doc-branded-label', 'Velocity Branded', 'vm-toolkit-branded' ) . "\n\n" .
			self::download_button( $card_id, 'doc', 'Unlock to Download', 'velocity_toolkit_link' ),
			array(
				// As wide as its button, so the label centers over it.
				// On phones it takes half the row, or all of it when it is alone.
				'maxWidth'     => array( 210, '', 600 ),
				'inQueryBlock' => true,
				'metadata'     => array( 'name' => 'Velocity branded' ),
			)
		);

		$white_label = Velocity_Seed::column(
			$card_id,
			'doc-wl',
			self::label_row( $card_id, 'doc-wl-label', 'White Label' ) . "\n\n" .
			self::download_button( $card_id, 'doc-wl', 'Unlock to Download', 'velocity_toolkit_link_wl' ),
			array(
				// On phones it takes half the row, or all of it when it is alone.
				'maxWidth'     => array( 210, '', 600 ),
				// Hides itself when the document has no White Label file.
				'className'    => 'vm-toolkit-wl',
				'inQueryBlock' => true,
				'metadata'     => array( 'name' => 'White label' ),
			)
		);

		$buttons = Velocity_Seed::column(
			$card_id,
			'doc-buttons',
			$branded . "\n\n" . $white_label,
			array(
				'direction'         => array( 'horizontal', '', '' ),
				'wrapContent'       => array( 'nowrap', '', '' ),
				// Two parts of the row's five. The name and description take three.
				'flexGrow'          => array( 2, '', '' ),
				// The buttons sit at the end of the row, and at its start on phones.
				'justifyContent'    => array( 'flex-end', '', 'flex-start' ),
				'verticalAlignment' => 'bottom',
				'gutter'            => array( 8, '', '' ),
				'gutterVariable'    => array( 'custom', 'custom', 'custom' ),
				'inQueryBlock'      => true,
				'metadata'          => array( 'name' => 'Buttons' ),
			)
		);

		return Velocity_Seed::column(
			$card_id,
			'doc',
			$text . "\n\n" . $buttons,
			array(
				'direction'         => array( 'horizontal', '', 'vertical' ),
				'wrapContent'       => array( 'nowrap', '', '' ),
				'verticalAlignment' => 'middle',
				'gutter'            => array( 32, 24, 16 ),
				'gutterVariable'    => array( 'custom', 'custom', 'custom' ),
				// On phones the buttons go under the text, with this space between.
				'rowGapVariable'    => array( '', '', 'custom' ),
				'rowGap'            => array( '', '', 16 ),
				'padding'           => array( '28', '22', '28', '22' ),
				'mobilePadding'     => array( '20', '0', '20', '0' ),
				// A rule under each document: a bottom border only.
				'borderStyle'       => array(
					array(
						'top'    => $none,
						'right'  => $none,
						'bottom' => $rule,
						'left'   => $none,
						'unit'   => 'px',
					),
				),
				'className'         => 'vm-toolkit-doc',
				'inQueryBlock'      => true,
				'metadata'          => array( 'name' => 'Document' ),
			)
		);
	}

	/**
	 * A Kadence query block.
	 *
	 * @param int    $post_id  Page ID.
	 * @param string $key      Section key.
	 * @param int[]  $queries  Query post IDs by section key.
	 * @return string
	 */
	private static function documents( $post_id, $key, $queries ) {
		return Velocity_Seed::block(
			'kadence/query',
			array(
				'uniqueID' => Velocity_Seed::uid( $post_id, 'query-' . $key ),
				'id'       => (int) $queries[ $key ],
			)
		);
	}

	/**
	 * Title and intro of a section, on the left.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $title   Title.
	 * @param string $intro   Intro text.
	 * @return string
	 */
	private static function header( $post_id, $key, $title, $intro ) {
		return Velocity_Home::t(
			$post_id,
			$key . '-title',
			$title,
			array(
				'tag'    => 'h2',
				'margin' => array( '0', '', '16', '' ),
				'class'  => 'is-style-vm-reveal-lines',
			)
		) . "\n\n" .
		Velocity_Home::subtitle(
			$post_id,
			$key,
			$intro,
			array(
				'width'  => 760,
				// A max width centers a text. These margins keep it on the left.
				'margin' => array( '0', 'auto', '32', '0' ),
			)
		);
	}

	/**
	 * A group of documents inside a section: its name, a line about it and
	 * its list.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Section key.
	 * @param string $title   Name of the group.
	 * @param string $intro   A line about it, or empty.
	 * @param int[]  $queries Query post IDs by section key.
	 * @return string
	 */
	private static function group( $post_id, $key, $title, $intro, $queries ) {
		$out = Velocity_Home::t(
			$post_id,
			$key . '-group-title',
			$title,
			array(
				'tag'    => 'h3',
				'size'   => array( 20, '', 18 ),
				'weight' => '700',
				'lh'     => 1.2,
				'margin' => array( '0', '', $intro ? '12' : '8', '' ),
			)
		);

		if ( $intro ) {
			$out .= "\n\n" . Velocity_Home::t(
				$post_id,
				$key . '-group-intro',
				$intro,
				array(
					'size'   => array( 14, '', '' ),
					'lh'     => 1.3,
					'margin' => array( '0', '', '16', '' ),
				)
			);
		}

		return $out . "\n\n" . self::documents( $post_id, $key, $queries );
	}

	/**
	 * Hero: title, intro, the note about the form and the links to the
	 * sections of the page.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function hero( $post_id ) {
		// No arrow: these stay on the page. Smaller on phones, so the five
		// take two lines at most. The design: 16px, regular weight, and a
		// dashed line around the ones that are not "All".
		$pill = array(
			'icon'          => '',
			'mobilePadding' => array( '8', '16', '8', '16' ),
			'typography'    => array(
				array(
					'size'          => array( 16, '', 14 ),
					'sizeType'      => 'px',
					// Lines of 24px: with the padding, a pill 56px tall.
					'lineHeight'    => array( 24, '', 20 ),
					'lineType'      => 'px',
					'letterSpacing' => array( '', '', '' ),
					'letterType'    => 'px',
					'textTransform' => '',
					'family'        => '',
					'google'        => '',
					'style'         => '',
					'weight'        => '400',
					'variant'       => '',
					'subset'        => '',
					'loadGoogle'    => true,
				),
			),
		);
		$dash = array( Velocity_Seed::color( 'palette1' ), 'dashed', 1 );
		$line = array_merge(
			$pill,
			array(
				'borderStyle' => array(
					array(
						'top'    => $dash,
						'right'  => $dash,
						'bottom' => $dash,
						'left'   => $dash,
						'unit'   => 'px',
					),
				),
			)
		);

		$inner =
			Velocity_Home::t(
				$post_id,
				'hero-title',
				'The Broker Toolkit Built <br>to Fuel Growth',
				array(
					'tag'    => 'h1',
					'margin' => array( '0', '', '24', '' ),
					'class'  => 'is-style-vm-reveal-lines',
				)
			) . "\n\n" .
			Velocity_Home::subtitle(
				$post_id,
				'hero',
				'Here’s where you’ll find the loan forms, rate sheet matrices, and program flyers you need to close on your next deal.',
				array( 'margin' => array( '0', '', '20', '' ) )
			) . "\n\n" .
			// The note sits in a row of its own so it is only as wide as its text.
			Velocity_Seed::column(
				$post_id,
				'hero-note-row',
				Velocity_Home::t(
					$post_id,
					'hero-note',
					'Fill out one short form to gain access to every file on this page in one click. Once you’re in our system, you won’t have to fill it out again.',
					array(
						'size'       => array( 14, '', '' ),
						'lh'         => 1.3,
						'color'      => '#b04c11',
						'background' => '#fde3cc',
						'padding'    => array( '8', '12', '8', '12' ),
						'radius'     => array( 8, 8, 8, 8 ),
						'margin'     => array( '0', '', '0', '' ),
						// vm-eyebrow adds the orange dot.
						'class'      => 'vm-eyebrow',
					)
				),
				array(
					'direction' => array( 'horizontal', '', '' ),
					'padding'   => array( '0', '', '32', '' ),
					'metadata'  => array( 'name' => 'Note' ),
				)
			) . "\n\n" .
			// Links to the sections of this page.
			Velocity_Home::buttons(
				$post_id,
				'hero-nav',
				array(
					array( 'All', '#forms', 'fill', $pill ),
					array( 'Forms', '#forms', 'outline', $line ),
					array( 'Matrices', '#matrices', 'outline', $line ),
					array( 'Flyers', '#flyers', 'outline', $line ),
					array( 'Links', '#links', 'outline', $line ),
				),
				'left',
				// 12px between two of them, as in the design.
				array( 12, 8 )
			);

		return Velocity_Home::section(
			$post_id,
			'hero',
			$inner,
			'Hero',
			array(
				'padding'       => array( '64', '', '56', '' ),
				'mobilePadding' => array( '40', '', '40', '' ),
			)
		);
	}

	/**
	 * Forms for residential and commercial deals: the two forms libraries
	 * side by side, on the light panel.
	 *
	 * @param int   $post_id Page ID.
	 * @param int[] $queries Query post IDs by section key.
	 * @return string
	 */
	public static function forms( $post_id, $queries ) {
		$libraries = array(
			'residential' => 'Residential Forms Library',
			'commercial'  => 'Commercial Forms Library',
		);

		$columns = array();
		foreach ( $libraries as $key => $title ) {
			$attrs = array( 'metadata' => array( 'name' => $title ) );
			if ( $columns ) {
				$attrs['id'] = 2;
			}

			$columns[] = Velocity_Seed::column( $post_id, 'forms-' . $key, self::group( $post_id, $key, $title, '', $queries ), $attrs );
		}

		$inner =
			self::header(
				$post_id,
				'forms',
				'Forms for Residential and Commercial Deals',
				'When you submit a loan, these are the forms we ask for. Download either our residential or commercial forms depending on your deal.'
			) . "\n\n" .
			Velocity_Seed::row(
				$post_id,
				'forms-row',
				$columns,
				array(
					'columnGutter' => 'custom',
					'customGutter' => array( 72, 40, 40 ),
					'tabletLayout' => 'row',
					'mobileLayout' => 'row',
					'padding'      => array( '0', '', '0', '' ),
					'metadata'     => array( 'name' => 'Forms libraries' ),
				)
			);

		return Velocity_Home::panel(
			$post_id,
			'forms',
			$inner,
			'Forms',
			array(
				'background' => Velocity_Seed::color( 'palette8' ),
				'padding'    => array( '72', '80', '72', '80' ),
			),
			array(
				'anchor'        => 'forms',
				'padding'       => array( '0', '32', '26', '32' ),
				'mobilePadding' => array( '0', '0', '0', '0' ),
			)
		);
	}

	/**
	 * Unlock the Toolkit: the form, on the page itself.
	 *
	 * The form is the same one the buttons open in a window. The site
	 * prints it once, and places it in the Section with the class
	 * vm-toolkit-inline. The whole panel hides for a visitor who has
	 * already unlocked the toolkit. See resource-modal.php.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function unlock( $post_id ) {
		$inner =
			Velocity_Home::section_header(
				$post_id,
				'unlock',
				'Unlock the Toolkit',
				'The one form to unlock everything on this page. Fill it out once and never again.',
				760
			) . "\n\n" .
			Velocity_Seed::row(
				$post_id,
				'unlock-form',
				array(
					Velocity_Seed::column(
						$post_id,
						'unlock-form-col',
						'',
						array(
							'className' => 'vm-toolkit-inline',
							'metadata'  => array( 'name' => 'The form goes here' ),
						)
					),
				),
				array(
					'maxWidth' => 700,
					'padding'  => array( '0', '', '0', '' ),
					'metadata' => array( 'name' => 'Form' ),
				)
			);

		return Velocity_Home::panel(
			$post_id,
			'unlock',
			$inner,
			'Unlock the Toolkit',
			array(
				'background' => '#f2f4fc',
				'padding'    => array( '72', '64', '72', '64' ),
			),
			array(
				'anchor'    => 'unlock',
				'className' => 'vm-toolkit-form',
			)
		);
	}

	/**
	 * Matrices: the rate sheets.
	 *
	 * @param int   $post_id Page ID.
	 * @param int[] $queries Query post IDs by section key.
	 * @return string
	 */
	public static function matrices( $post_id, $queries ) {
		$inner =
			self::header(
				$post_id,
				'matrices',
				'Matrices',
				'Our rate sheets for 1-4 unit deals and commercial deals, available in both Velocity’s branding or in white label versions. Unlock once, then download.'
			) . "\n\n" .
			Velocity_Home::t(
				$post_id,
				'matrices-label',
				'Velocity’s White Label and Rate Sheet Matrices',
				array(
					'size'   => array( 12, '', '' ),
					'lh'     => 1.3,
					'margin' => array( '0', '', '0', '' ),
				)
			) . "\n\n" .
			self::documents( $post_id, 'matrices', $queries );

		return Velocity_Home::section(
			$post_id,
			'matrices',
			$inner,
			'Matrices',
			array(
				'anchor'        => 'matrices',
				'padding'       => array( '72', '', '48', '' ),
				'mobilePadding' => array( '48', '', '32', '' ),
			)
		);
	}

	/**
	 * Mortgage program flyers, in four groups.
	 *
	 * @param int   $post_id Page ID.
	 * @param int[] $queries Query post IDs by section key.
	 * @return string
	 */
	public static function flyers( $post_id, $queries ) {
		$groups = array(
			'programs'  => array( 'Program Flyers', 'Flyers made for our programs that you can use to answer all your client questions.' ),
			'foreign'   => array( 'Foreign Investor Flyers', 'Flyers detailing our foreign investor program, built in English and five other languages, that you can use for investors living outside the U.S.' ),
			'about'     => array( 'About Velocity', 'Flyers that you can use to introduce your client to Velocity, our programs, and what sets us apart.' ),
			'customize' => array( 'How to Customize a Flyer', 'Read this first if you want to put your own logo or contact details on any of the flyers above.' ),
		);

		$inner =
			self::header(
				$post_id,
				'flyers',
				'Mortgage Program Flyers',
				'Marketing assets to help your clients see the value of a Velocity loan, available in both Velocity’s branding or your own. Unlock once, then download.'
			) . "\n\n" .
			Velocity_Home::subtitle(
				$post_id,
				'flyers-brands',
				'Available in Velocity’s brand or as white label.',
				array( 'margin' => array( '0', '', '0', '' ) )
			);

		foreach ( $groups as $key => $group ) {
			$inner .= "\n\n" . Velocity_Seed::column(
				$post_id,
				'flyers-' . $key,
				self::group( $post_id, $key, $group[0], $group[1], $queries ),
				array(
					'padding'  => array( '48', '', '0', '' ),
					'metadata' => array( 'name' => $group[0] ),
				)
			);
		}

		return Velocity_Home::section(
			$post_id,
			'flyers',
			$inner,
			'Mortgage program flyers',
			array(
				'anchor'        => 'flyers',
				'padding'       => array( '48', '', '96', '' ),
				'mobilePadding' => array( '32', '', '64', '' ),
			)
		);
	}

	/**
	 * Important links: four cards, two by two, on the peach band.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function links( $post_id ) {
		$cards = array(
			array( 'Broker Resources', 'Download our product brochure and rate sheet. Contact us for the password.', home_url( '/broker-resources/' ), true ),
			array( 'Broker Portal', 'Your login for the Velocity portal, where you can view and work on your deals.', 'https://www.generatevelocity.com/#/SignIn', false ),
			array( 'Featured Deals', 'See all the properties and deals that Velocity finances, program by program.', home_url( '/featured-deals/' ), false ),
			array( 'News and Insights', 'Expert articles to help you learn and grow, plus the latest Velocity updates.', home_url( '/news-insights/' ), false ),
		);

		$rows = array();
		foreach ( array_chunk( $cards, 2 ) as $row_index => $row_cards ) {
			$columns = array();
			foreach ( $row_cards as $index => $card ) {
				$key   = 'link-' . $row_index . '-' . $index;
				$attrs = Velocity_Home::card(
					array_merge(
						array(
							'background'        => Velocity_Seed::color( 'palette8' ),
							'padding'           => array( '32', '24', '32', '24' ),
							'verticalAlignment' => 'middle',
							'rowGap'            => array( 0, '', '' ),
							'metadata'          => array( 'name' => $card[0] ),
						),
						Velocity_Home::card_animation( $index )
					)
				);
				if ( $index ) {
					$attrs['id'] = 2;
				}

				$text = Velocity_Home::t( $post_id, $key . '-body', $card[1], array( 'margin' => array( '0', '', $card[3] ? '12' : '24', '' ) ) );

				if ( $card[3] ) {
					$text .= "\n\n" . Velocity_Home::t(
						$post_id,
						$key . '-partner',
						'<a href="' . esc_url( home_url( '/become-a-partner/' ) ) . '">Not a Partner Yet? Become a Partner</a>',
						array(
							'weight'     => '600',
							'margin'     => array( '0', '', '24', '' ),
							'link'       => Velocity_Seed::color( 'palette3' ),
							'link_hover' => Velocity_Seed::color( 'palette2' ),
						)
					);
				}

				$columns[] = Velocity_Seed::column(
					$post_id,
					$key,
					// The tag sits in a row of its own so it is only as wide as its text.
					Velocity_Seed::column(
						$post_id,
						$key . '-tag-row',
						self::label( $post_id, $key . '-tag', $card[0], array( 'weight' => '600', 'margin' => array( '0', '', '16', '' ) ) ),
						array(
							'direction' => array( 'horizontal', '', '' ),
							'metadata'  => array( 'name' => 'Tag' ),
						)
					) . "\n\n" .
					Velocity_Home::card_title( $post_id, $key, $card[0], array( 'margin' => array( '0', '', '16', '' ) ) ) . "\n\n" .
					$text . "\n\n" .
					Velocity_Home::buttons( $post_id, $key, array( array( 'Learn More', $card[2] ) ), 'left' ),
					$attrs
				);
			}

			$rows[] = Velocity_Seed::row(
				$post_id,
				'links-row-' . $row_index,
				$columns,
				array(
					'maxWidth'           => 1058,
					'columnGutter'       => 'custom',
					'customGutter'       => array( 24, '', '' ),
					'columnsInnerHeight' => true,
					'mobileLayout'       => 'row',
					'padding'            => array( '0', '', $row_index ? '0' : '24', '' ),
					'metadata'           => array( 'name' => 'Links row ' . ( $row_index + 1 ) ),
				)
			);
		}

		return Velocity_Home::section(
			$post_id,
			'links',
			Velocity_Home::section_header(
				$post_id,
				'links',
				'Important Links',
				'Some more links our brokers find useful, such as their portal login, past properties we’ve funded, and the latest industry news and insights.',
				790
			) . "\n\n" . implode( "\n\n", $rows ),
			'Important links',
			array(
				'anchor'        => 'links',
				'bgColor'       => '#f8e8de',
				'padding'       => array( '96', '', '80', '' ),
				'mobilePadding' => array( '64', '', '48', '' ),
			)
		);
	}

	/**
	 * The call to action band that closes the page.
	 *
	 * Its top sits on the peach of the section above: the background of its
	 * own section is a gradient with a hard stop, peach then white.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function cta( $post_id ) {
		return Velocity_Home::cta_band(
			$post_id,
			'Looking for Something Else?',
			array( array( 'Contact Us', home_url( '/contact/' ) ) ),
			1280,
			array(
				'backgroundSettingTab' => 'gradient',
				'gradient'             => 'linear-gradient(180deg,rgb(248,232,222) 136px,rgb(255,255,255) 136px)',
				'padding'              => array( '0', '', '32', '' ),
				'mobilePadding'        => array( '0', '', '24', '' ),
			),
			array(
				'intro' => 'Just tell us what you need. Once we know, we’ll send it to you or put you in touch with the right person at Velocity who can help.',
				'note'  => false,
			)
		);
	}

	/**
	 * Every section, in page order.
	 *
	 * @param int   $post_id Page ID.
	 * @param int[] $queries Query post IDs by section key.
	 * @return string
	 */
	public static function content( $post_id, $queries ) {
		return implode(
			"\n\n",
			array(
				self::hero( $post_id ),
				self::forms( $post_id, $queries ),
				self::unlock( $post_id ),
				self::matrices( $post_id, $queries ),
				self::flyers( $post_id, $queries ),
				self::links( $post_id ),
				self::cta( $post_id ),
			)
		);
	}
}
