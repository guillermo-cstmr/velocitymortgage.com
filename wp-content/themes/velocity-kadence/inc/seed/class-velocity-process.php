<?php
/**
 * Builds the Process page as block content.
 *
 * Like the home page, this only produces the first version of the page.
 * After `wp velocity seed-process` the page is edited in the block editor.
 * It follows the same rule: what a block setting can express is set on the
 * block. The classes used here are listed in docs/components.md.
 *
 * Figma: page "Broker Programs", frame "Velocity - Broker Process". The copy
 * is the one in the design, which matches the page copy document (V1). The
 * answers to the frequently asked questions come from that document: the
 * design only shows the first.
 *
 * Kept compatible with PHP 7.4, see class-velocity-cli.php.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Process page seed content.
 */
class Velocity_Process {

	/**
	 * Slug of the page this class fills.
	 */
	const SLUG = 'process';

	/**
	 * A Kadence Image block showing a file from assets/images/process.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $file    File name.
	 * @param int    $width   Max width in px.
	 * @param string $alt     Alternative text. Empty for decorative images.
	 * @return string
	 */
	private static function image( $post_id, $key, $file, $width, $alt = '' ) {
		return Velocity_Home::image( $post_id, $key, $file, $width, $alt, '', 'process' );
	}

	/**
	 * A left-aligned section header: title and intro.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $title   Title.
	 * @param string $intro   Intro text.
	 * @param string $color   Text color, or empty to inherit.
	 * @return string
	 */
	private static function header( $post_id, $key, $title, $intro, $color = '' ) {
		$title_options = array(
			'tag'    => 'h2',
			'margin' => array( '0', '', '24', '' ),
			'class'  => 'is-style-vm-reveal-lines',
		);
		$intro_options = array(
			'width'  => 1069,
			// A max width centers a text. These margins keep it on the left.
			'margin' => array( '0', 'auto', '40', '0' ),
		);

		if ( $color ) {
			$title_options['color'] = $color;
			$intro_options['color'] = $color;
		}

		return Velocity_Home::t( $post_id, $key . '-title', $title, $title_options ) . "\n\n" .
			Velocity_Home::subtitle( $post_id, $key, $intro, $intro_options );
	}

	/**
	 * A card with an icon above a title and a text, centered.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique IDs.
	 * @param array  $card    Icon file, title, text.
	 * @param array  $attrs   Settings that differ from the default card.
	 * @return string
	 */
	private static function icon_card( $post_id, $key, $card, $attrs = array() ) {
		return Velocity_Seed::column(
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
			Velocity_Home::card(
				array_merge(
					array(
						'textAlign'         => array( 'center', '', '' ),
						'justifyContent'    => array( 'center', '', '' ),
						'verticalAlignment' => 'middle',
						'metadata'          => array( 'name' => $card[1] ),
					),
					$attrs
				)
			)
		);
	}

	/**
	 * Hero: title, intro and the main button beside the drawing of the steps.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function hero( $post_id ) {
		$text =
			Velocity_Home::t(
				$post_id,
				'hero-title',
				'What To Expect at Each Step',
				array(
					'tag'    => 'h1',
					'margin' => array( '0', '', '24', '' ),
					'class'  => 'is-style-vm-reveal-lines',
				)
			) . "\n\n" .
			Velocity_Home::subtitle(
				$post_id,
				'hero',
				'Start with a short form, hear back within 24 hours, then get approved when your first deal is ready. Most 1-4 unit deals close in 2 to 4 weeks; commercial in 30 to 45 days.',
				array( 'margin' => array( '0', '', '24', '' ) )
			) . "\n\n" .
			Velocity_Home::buttons( $post_id, 'hero', array( array( 'Become a Partner', home_url( '/become-a-partner/' ) ) ), 'left' );

		return Velocity_Seed::row(
			$post_id,
			'hero',
			array(
				Velocity_Seed::column( $post_id, 'hero-text', $text, array( 'verticalAlignment' => 'middle' ) ),
				Velocity_Seed::column(
					$post_id,
					'hero-art-col',
					self::image( $post_id, 'hero-art', 'hero-steps.svg', 460, 'Four steps rising along a line: a form, a clock, a check mark and a house' ),
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
				'firstColumnWidth'  => 60,
				'secondColumnWidth' => 40,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 56, 40, '' ),
				'verticalAlignment' => 'middle',
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				'padding'           => array( '80', '', '80', '' ),
				'tabletPadding'     => array( '64', '', '64', '' ),
				'mobilePadding'     => array( '48', '', '48', '' ),
				'metadata'          => array( 'name' => 'Hero' ),
			)
		);
	}

	/**
	 * The people you will work with: section header and four cards.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function people( $post_id ) {
		$cards = array(
			array( 'icon-person.svg', 'Day One: Your Account Executive', 'From the first hello to final terms, your personal contact for whatever you need.' ),
			array( 'icon-tab-move.svg', 'During Processing: Your Account Manager', 'Once the loan is live, this person ensures it moves forward toward closing.' ),
			array( 'icon-approval.svg', 'For Approval: The Broker Coordinator', 'Alongside your account executive, they take care of your deal’s paperwork.' ),
			array( 'icon-handshake.svg', 'For Sign Off: The Operations Team', 'The last word on every deal. Once they review and sign off, your deal closes.' ),
		);

		$columns = array();
		foreach ( $cards as $index => $card ) {
			$attrs = array_merge(
				array( 'height' => array( 263, '', 0 ) ),
				Velocity_Home::card_animation( $index )
			);
			if ( $index ) {
				$attrs['id'] = $index + 1;
			}

			$columns[] = self::icon_card( $post_id, 'person-' . $index, $card, $attrs );
		}

		$grid = Velocity_Seed::row(
			$post_id,
			'people-grid',
			$columns,
			array(
				'columnGutter'       => 'custom',
				'customGutter'       => array( 20, '', '' ),
				'columnsInnerHeight' => true,
				'tabletLayout'       => 'two-grid',
				'mobileLayout'       => 'row',
				'padding'            => array( '0', '', '0', '' ),
				'metadata'           => array( 'name' => 'Cards' ),
			)
		);

		return Velocity_Home::section(
			$post_id,
			'people',
			Velocity_Home::section_header(
				$post_id,
				'people',
				'The People You’ll Work With',
				'At Velocity we lend out our own money as a direct portfolio lender. That means our experienced professionals only work to answer your needs.',
				854
			) . "\n\n" . $grid,
			'The people you will work with',
			array(
				'padding'       => array( '80', '', '80', '' ),
				'mobilePadding' => array( '64', '', '48', '' ),
			)
		);
	}

	/**
	 * A list of numbered steps: a square with the number, then a title and a text.
	 *
	 * Each step is a Section with a rule above it, set as its top border.
	 *
	 * @param int     $post_id Page ID.
	 * @param string  $key     Key for the unique IDs.
	 * @param array[] $steps   Each: title, text.
	 * @param string  $rule    Color of the rules.
	 * @return string
	 */
	private static function steps( $post_id, $key, $steps, $rule ) {
		$none = array( '', '', '' );
		$out  = array();

		foreach ( $steps as $index => $step ) {
			$step_key = $key . '-' . $index;

			$marker = Velocity_Seed::column(
				$post_id,
				$step_key . '-marker',
				Velocity_Home::t(
					$post_id,
					$step_key . '-number',
					(string) ( $index + 1 ),
					array(
						'align'  => 'center',
						'family' => 'heading',
						'size'   => array( 22, '', '' ),
						'weight' => '700',
						'lh'     => 1.1,
						'color'  => Velocity_Seed::color( 'palette2' ),
						'margin' => array( '0', '', '0', '' ),
					)
				),
				array(
					'background'        => 'rgba(211,112,49,0.26)',
					'borderStyle'       => Velocity_Home::border( Velocity_Seed::color( 'palette1' ) ),
					'borderRadius'      => array( 5, 5, 5, 5 ),
					'maxWidth'          => array( 48, '', '' ),
					'height'            => array( 48, '', '' ),
					'verticalAlignment' => 'middle',
					'metadata'          => array( 'name' => 'Number' ),
				)
			);

			$details = Velocity_Seed::column(
				$post_id,
				$step_key . '-details',
				Velocity_Home::card_title( $post_id, $step_key, $step[0] ) . "\n\n" .
				Velocity_Home::body( $post_id, $step_key, $step[1] ),
				array(
					'flexGrow'       => array( 1, '', '' ),
					'rowGapVariable' => array( 'custom', '', '' ),
					'rowGap'         => array( 12, '', '' ),
					'metadata'       => array( 'name' => 'Title and text' ),
				)
			);

			$out[] = Velocity_Seed::column(
				$post_id,
				$step_key,
				$marker . "\n\n" . $details,
				array(
					'direction'      => array( 'horizontal', '', '' ),
					'wrapContent'    => array( 'nowrap', '', '' ),
					'gutter'         => array( 24, '', 16 ),
					'gutterVariable' => array( 'custom', 'custom', 'custom' ),
					'padding'        => array( '24', '', '24', '' ),
					'borderStyle'    => array(
						array(
							'top'    => array( $rule, '', 1 ),
							'right'  => $none,
							'bottom' => $none,
							'left'   => $none,
							'unit'   => 'px',
						),
					),
					'metadata'       => array( 'name' => 'Step ' . ( $index + 1 ) . ': ' . $step[0] ),
				)
			);
		}

		return implode( "\n\n", $out );
	}

	/**
	 * Becoming a partner: the five steps.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function partner( $post_id ) {
		$steps = array(
			array( 'Register on Our Site', 'Start with a quick introduction. Who you are, where you do business, and that’s it.' ),
			array( 'Hear Back Within 24 Hours', 'After registering, our broker approval team will respond in 24 hours.' ),
			array( 'Tell Us About Your Business', 'Your account executive learns what you do, then shows you how Velocity can help.' ),
			array( 'Get Set Up in Our System', 'Once registered, we’ll give you login details for the broker portal so you can start pricing and submitting loans.' ),
			array( 'Bring Your First Deal and Get Approved', 'Your account executive sends the approval package for the broker of record to sign.' ),
		);

		return Velocity_Home::section(
			$post_id,
			'partner',
			self::header(
				$post_id,
				'partner',
				'Partner With Us in Five Steps',
				'Just a short form, a single conversation, and some paperwork if you already have a deal. That’s how simple it is.'
			) . "\n\n" . self::steps( $post_id, 'partner-step', $steps, Velocity_Seed::color( 'palette2' ) ),
			'Partner with us in five steps',
			array(
				'padding'       => array( '72', '', '48', '' ),
				'mobilePadding' => array( '48', '', '32', '' ),
			)
		);
	}

	/**
	 * A titled list of documents, as a Kadence Icon List with a dot for bullet.
	 *
	 * @param int      $post_id Page ID.
	 * @param string   $key     Key for the unique IDs.
	 * @param string   $title   List title.
	 * @param string[] $items   List items.
	 * @return string
	 */
	private static function document_list( $post_id, $key, $title, $items ) {
		$white = Velocity_Seed::color( 'palette9' );
		$uid   = Velocity_Seed::uid( $post_id, $key . '-list' );
		$lis   = array();

		foreach ( $items as $index => $item ) {
			$item_uid = Velocity_Seed::uid( $post_id, $key . '-item-' . $index );
			$lis[]    = Velocity_Seed::block(
				'kadence/listitem',
				array( 'uniqueID' => $item_uid ),
				'<li class="wp-block-kadence-listitem kt-svg-icon-list-item-wrap kt-svg-icon-list-item-' . $item_uid . '"><span data-name="USE_PARENT_DEFAULT_ICON" data-stroke="USE_PARENT_DEFAULT_WIDTH" data-class="kt-svg-icon-list-single" class="kadence-dynamic-icon"></span><span class="kt-svg-icon-list-text">' . $item . '</span></li>'
			);
		}

		$list = Velocity_Seed::block(
			'kadence/iconlist',
			array(
				'listGap'      => 20,
				'listLabelGap' => 12,
				'uniqueID'     => $uid,
				'iconAlign'    => 'top',
				'iconSize'     => array( 6, '', '' ),
				'icon'         => 'fas_circle',
				'color'        => $white,
				'listMargin'   => array( '0', '0', '0', '0' ),
				'listStyles'   => array(
					array(
						'size'          => array( 16, '', '' ),
						'sizeType'      => 'px',
						'lineHeight'    => array( 1.47, '', '' ),
						'lineType'      => '',
						'letterSpacing' => '',
						'family'        => '',
						'google'        => false,
						'style'         => '',
						'weight'        => '',
						'variant'       => '',
						'subset'        => '',
						'loadGoogle'    => true,
						'color'         => $white,
						'textTransform' => '',
					),
				),
			),
			'<div class="wp-block-kadence-iconlist kt-svg-icon-list-items kt-svg-icon-list-items' . $uid . ' kt-svg-icon-list-columns-1 alignnone kt-list-icon-aligntop"><ul class="kt-svg-icon-list">' . implode( "\n\n", $lis ) . '</ul></div>'
		);

		return Velocity_Seed::column(
			$post_id,
			$key,
			Velocity_Home::card_title( $post_id, $key, $title, array( 'color' => $white ) ) . "\n\n" . $list,
			array(
				'padding'        => array( '24', '', '0', '' ),
				'rowGapVariable' => array( 'custom', '', '' ),
				'rowGap'         => array( 20, '', '' ),
				'borderStyle'    => array(
					array(
						'top'    => array( $white, '', 1 ),
						'right'  => array( '', '', '' ),
						'bottom' => array( '', '', '' ),
						'left'   => array( '', '', '' ),
						'unit'   => 'px',
					),
				),
				'metadata'       => array( 'name' => $title ),
			)
		);
	}

	/**
	 * The approval package: a dark panel with what is signed and what is sent.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function approval( $post_id ) {
		$white = Velocity_Seed::color( 'palette9' );

		$lists = Velocity_Seed::row(
			$post_id,
			'approval-lists',
			array(
				self::document_list(
					$post_id,
					'approval-sign',
					'Three Documents to Sign',
					array(
						'The Broker Agreement, one page',
						'The Loan Fraud Zero Tolerance acknowledgment, initialed',
						'A Form W-9, completed in full',
					)
				),
				self::document_list(
					$post_id,
					'approval-info',
					'Information Required',
					array(
						'An EIN information sheet, if your brokerage uses an EIN',
						'Proof your brokerage is in good standing in its home state',
						'A driver’s license for the person signing',
						'Your broker license, where required',
					)
				),
			),
			array(
				'maxWidth'     => 1280,
				'columnGutter' => 'custom',
				'customGutter' => array( 48, 32, '' ),
				'mobileLayout' => 'row',
				'padding'      => array( '0', '', '40', '' ),
				'metadata'     => array( 'name' => 'Document lists' ),
			)
		);

		$inner = Velocity_Seed::row(
			$post_id,
			'approval-inner',
			array(
				Velocity_Seed::column(
					$post_id,
					'approval-inner-col',
					self::header(
						$post_id,
						'approval',
						'A Closer Look at Our Approval Package',
						'This is step five of our partnership process, broken down for you to see. Once the broker of record signs, your approval lasts a whole year.',
						$white
					) . "\n\n" . $lists . "\n\n" .
					Velocity_Home::t(
						$post_id,
						'approval-note',
						'All signatures come from the broker of record—your firm’s owner or officer.',
						array(
							'color'  => $white,
							'margin' => array( '0', '', '0', '' ),
						)
					)
				),
			),
			array(
				// On wide screens the panel keeps growing, up to 1920px, and
				// its content stops here, like the panels of the home page.
				'maxWidth' => 1280,
				'padding'  => array( '0', '', '0', '' ),
				'metadata' => array( 'name' => 'Approval package' ),
			)
		);

		return Velocity_Home::panel(
			$post_id,
			'approval',
			$inner,
			'A Closer Look at Our Approval Package',
			array(
				'backgroundType' => 'gradient',
				'background'     => Velocity_Seed::color( 'palette3' ),
				'gradient'       => 'linear-gradient(118deg,rgb(1,7,57) 14%,rgb(43,53,120) 92%)',
				'borderRadius'   => array( 20, 20, 20, 20 ),
				'padding'        => array( '72', '48', '72', '48' ),
				'tabletPadding'  => array( '56', '32', '56', '32' ),
				'mobilePadding'  => array( '48', '20', '48', '20' ),
			)
		);
	}

	/**
	 * Your first deal: four steps beside the box of closing times.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function deal( $post_id ) {
		$steps = array(
			array( 'Your Account Executive Reviews It', 'We review the loan to make sure it fits our product bucket.' ),
			array( 'Velocity Sends You the Conditional Loan Approval', 'We give you the CLA for you to send to your client for their signature via Docusign.' ),
			array( 'Your Client Pays for the Appraisal', 'After they sign the CLA and pay the appraisal, the loan is submitted into processing.' ),
			array( 'Processing, Condition Review, and Funding', 'Your account manager collects all documents and the underwriting team signs off.' ),
		);

		$figures = array(
			array( '2 to 4 weeks', 'To close for 1-4 unit properties' ),
			array( '30 to 45 days', 'To close for commercial properties' ),
			array( '2 wks, 1-4 units', 'Processing to funding' ),
			array( '3 wks, commercial', 'Processing to funding' ),
		);

		$figure_blocks = array();
		foreach ( $figures as $index => $figure ) {
			$key             = 'deal-figure-' . $index;
			$figure_blocks[] = Velocity_Seed::column(
				$post_id,
				$key,
				Velocity_Home::t(
					$post_id,
					$key . '-value',
					$figure[0],
					array(
						'family' => 'heading',
						'size'   => array( 22, '', '' ),
						'weight' => '700',
						'lh'     => 1.1,
						'margin' => array( '0', '', '0', '' ),
					)
				) . "\n\n" .
				Velocity_Home::t(
					$post_id,
					$key . '-label',
					$figure[1],
					array(
						'size'   => array( 14, '', '' ),
						'lh'     => 1.1,
						'margin' => array( '0', '', '0', '' ),
					)
				),
				array(
					'rowGapVariable' => array( 'custom', '', '' ),
					'rowGap'         => array( 12, '', '' ),
					'metadata'       => array( 'name' => $figure[0] ),
				)
			);
		}

		$row = Velocity_Seed::row(
			$post_id,
			'deal-row',
			array(
				Velocity_Seed::column(
					$post_id,
					'deal-steps',
					self::steps( $post_id, 'deal-step', $steps, '#c6c6c6' ),
					array( 'metadata' => array( 'name' => 'Steps' ) )
				),
				Velocity_Seed::column(
					$post_id,
					'deal-figures',
					implode( "\n\n", $figure_blocks ),
					array(
						'id'             => 2,
						'background'     => 'rgba(43,64,120,0.14)',
						'borderStyle'    => Velocity_Home::border( Velocity_Seed::color( 'palette3' ) ),
						'borderRadius'   => array( 12, 12, 12, 12 ),
						'padding'        => array( '32', '32', '32', '32' ),
						'rowGapVariable' => array( 'custom', '', '' ),
						'rowGap'         => array( 56, 32, 24 ),
						'metadata'       => array( 'name' => 'Closing times' ),
					)
				),
			),
			array(
				'firstColumnWidth'  => 76,
				'secondColumnWidth' => 24,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 40, 32, '' ),
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				'padding'           => array( '0', '', '40', '' ),
				'metadata'          => array( 'name' => 'Steps and closing times' ),
			)
		);

		return Velocity_Home::section(
			$post_id,
			'deal',
			self::header(
				$post_id,
				'deal',
				'How Your First Deal Moves Forward',
				'Your first deal is the one you got approved. Here are the four steps that it takes next, from terms to funding.'
			) . "\n\n" . $row . "\n\n" .
			Velocity_Home::t(
				$post_id,
				'deal-note',
				'Send documents while the appraisal is out so your file moves right to closing.',
				array( 'margin' => array( '0', '', '0', '' ) )
			),
			'How your first deal moves forward',
			array(
				'padding'       => array( '72', '', '72', '' ),
				'mobilePadding' => array( '48', '', '48', '' ),
			)
		);
	}

	/**
	 * Borrower documents: title and intro beside a carousel of cards, and
	 * the note on broker licenses.
	 *
	 * vm-carousel and vm-carousel-track make the cards scroll sideways, with
	 * arrows. See assets/js/carousel.js.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function documents( $post_id ) {
		$cards = array(
			array( 'icon-contract-edit.svg', 'Your Client’s Identity, in Writing', 'Your borrower’s ID, Social Security number, and LLC formation papers.' ),
			array( 'icon-house-shield.svg', 'Proof the Property Is an Investment', 'Our business-purpose loans mean that borrowers cannot live on the property.' ),
			array( 'icon-bank.svg', 'Where Your Client’s Money Comes From', 'Bank statements showing sufficient funds and where any deposits came from.' ),
			array( 'icon-contract.svg', 'The Property and All Its Paperwork', 'Purchase contract or any rental leases, plus insurance and the title paperwork.' ),
			array( 'icon-payments.svg', 'What Cash-Out Money Is For', 'A short written statement that describes the business use of the cash.' ),
		);

		$rust        = Velocity_Seed::color( 'palette2' );
		$card_blocks = array();
		foreach ( $cards as $index => $card ) {
			$card_blocks[] = self::icon_card(
				$post_id,
				'document-' . $index,
				$card,
				array(
					'borderStyle'        => Velocity_Home::border( $rust ),
					'borderHoverStyle'   => Velocity_Home::border( $rust ),
					'displayShadow'      => false,
					'displayHoverShadow' => false,
					// One width for every screen: on phones style.css caps it at 86% of the list.
					'maxWidth'           => array( 410, '', '' ),
					'height'             => array( 220, '', '' ),
				)
			);
		}

		$carousel = Velocity_Seed::column(
			$post_id,
			'documents-carousel',
			Velocity_Seed::column(
				$post_id,
				'documents-track',
				implode( "\n\n", $card_blocks ),
				array(
					'direction'      => array( 'horizontal', '', '' ),
					'wrapContent'    => array( 'nowrap', '', '' ),
					'gutter'         => array( 23, '', 16 ),
					'gutterVariable' => array( 'custom', 'custom', 'custom' ),
					'className'      => 'vm-carousel-track',
					'metadata'       => array( 'name' => 'Cards' ),
				)
			),
			array(
				'id'        => 2,
				'className' => 'vm-carousel vm-carousel--below',
				'metadata'  => array( 'name' => 'Document cards' ),
			)
		);

		$intro = Velocity_Seed::column(
			$post_id,
			'documents-intro',
			Velocity_Home::t(
				$post_id,
				'documents-title',
				'What You’ll Need From Your Client',
				array(
					'tag'    => 'h2',
					'margin' => array( '0', '', '24', '' ),
					'class'  => 'is-style-vm-reveal-lines',
				)
			) . "\n\n" .
			Velocity_Home::subtitle(
				$post_id,
				'documents',
				'Keep the deal moving by making sure that your client can show who they are, their money is real, and their property is what they say it is.'
			),
			array(
				'verticalAlignment' => 'middle',
				'metadata'          => array( 'name' => 'Title and intro' ),
			)
		);

		$row = Velocity_Seed::row(
			$post_id,
			'documents-row',
			array( $intro, $carousel ),
			array(
				'firstColumnWidth'  => 41,
				'secondColumnWidth' => 59,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 21, 32, '' ),
				'verticalAlignment' => 'middle',
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				'padding'           => array( '0', '', '40', '' ),
				'metadata'          => array( 'name' => 'Borrower documents' ),
			)
		);

		$note = Velocity_Home::t(
			$post_id,
			'documents-note',
			'Once a loan is in processing, your account manager will send you an exact list.',
			array(
				'align'  => 'center',
				'margin' => array( '0', '', '86', '' ),
			)
		);

		// The licenses note: text on the left, button on the right.
		$states = Velocity_Seed::row(
			$post_id,
			'states',
			array(
				Velocity_Seed::column(
					$post_id,
					'states-text',
					Velocity_Home::t(
						$post_id,
						'states-title',
						'Broker licenses are required in California, Nevada, Arizona, Idaho, and Oregon. Brokerages in other states need to be either an LLC or a corporation.',
						array(
							'tag'    => 'h3',
							'size'   => array( 24, '', 20 ),
							'weight' => '700',
							'lh'     => 1.1,
							'margin' => array( '0', '', '28', '' ),
						)
					) . "\n\n" .
					Velocity_Home::subtitle( $post_id, 'states', 'Not sure what you need? Your account executive knows what your state requires.' ),
					array( 'verticalAlignment' => 'middle' )
				),
				Velocity_Seed::column(
					$post_id,
					'states-action',
					Velocity_Home::buttons( $post_id, 'states', array( array( 'Ask About Your State', home_url( '/contact/' ) ) ), 'left' ),
					array(
						'id'                => 2,
						'verticalAlignment' => 'middle',
					)
				),
			),
			array(
				'maxWidth'          => 1184,
				'bgColor'           => 'rgba(211,112,49,0.3)',
				'borderStyle'       => Velocity_Home::border( Velocity_Seed::color( 'palette1' ) ),
				'borderRadius'      => array( 12, 12, 12, 12 ),
				'firstColumnWidth'  => 70,
				'secondColumnWidth' => 30,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 28, 24, '' ),
				'verticalAlignment' => 'middle',
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				'padding'           => array( '40', '56', '40', '56' ),
				'tabletPadding'     => array( '32', '32', '32', '32' ),
				'mobilePadding'     => array( '24', '20', '24', '20' ),
				'metadata'          => array( 'name' => 'Broker licenses' ),
			)
		);

		return Velocity_Home::section(
			$post_id,
			'documents',
			$row . "\n\n" . $note . "\n\n" . $states,
			'What you will need from your client',
			array(
				'bgColor'       => '#f8e8de',
				'padding'       => array( '72', '', '72', '' ),
				'mobilePadding' => array( '48', '', '48', '' ),
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
			'You Know the Steps. Now Take the First.',
			array(
				array( 'Become a Partner', home_url( '/become-a-partner/' ) ),
				array( 'See All Programs', home_url( '/loan-programs/' ), 'outline', Velocity_Home::on_dark() ),
			),
			1280,
			// The color of the section above goes on behind the top of the band,
			// as on the program pages.
			array(
				'backgroundSettingTab' => 'gradient',
				'gradient'             => 'linear-gradient(180deg,rgb(248,232,222) 136px,rgb(255,255,255) 136px)',
				'padding'              => array( '0', '', '48', '' ),
				'mobilePadding'        => array( '0', '', '32', '' ),
			)
		);
	}

	/**
	 * Frequently asked questions. Copy from the page copy document (V1).
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function faq( $post_id ) {
		return Velocity_Home::faq_section(
			$post_id,
			array(
				array(
					'Do I need approval before I send a deal?',
					array(
						'No—approval happens after. You first register with Velocity, talk to an account executive, and send a deal when you have one. We’ll send the approval package when that first deal is ready. After your broker of record signs and returns it, your approval runs for a year.',
						'The order of all this is on purpose. We do it so you’re set up early, your first deal keeps moving, and the paperwork only happens when there’s a real loan to close. Your account executive and the Broker Coordinator will help you with every line of it.',
					),
				),
				array(
					'How long does approval last?',
					array(
						// As the copy document has it. The rest of the page says a year.
						'It’s valid for 30 days. Once your broker of record signs and returns the approval package, your brokerage holds approval in our system for the next 30 days. Every deal you send us in that time goes straight to your account executive without another approval package.',
						'Note that where you can close in depends on your licensing. Five states require a broker license, and everywhere else we look for an LLC or a corporation. Be sure to ask your account executive what applies to you before the first file.',
					),
				),
				array(
					'Who signs the approval package?',
					array(
						'Your broker of record, which can be either the owner, officer, or someone authorized to sign for the brokerage. At a one-person shop, it’s you. At a larger firm, it’s the owner or an officer, rather than a loan officer or processor.',
						'The package is short and consists of just a one-page broker agreement, a fraud zero-tolerance acknowledgment, a W-9, and four attachments your office already has, such as proof of good standing and the driver’s license of the person signing.',
					),
				),
				array(
					'How long does a loan take to close?',
					array(
						'Most 1-4 unit deals close in 2 to 4 weeks; commercial deals in 30 to 45 days.',
						'Want to close as fast as possible? Have your full file ready by uploading your required conditions in the broker portal prior to the appraisal being completed. Before promising your client a date though, ask your account executive for current timing.',
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
				self::people( $post_id ),
				self::partner( $post_id ),
				self::approval( $post_id ),
				self::deal( $post_id ),
				self::documents( $post_id ),
				self::cta( $post_id ),
				self::faq( $post_id ),
			)
		);
	}
}
