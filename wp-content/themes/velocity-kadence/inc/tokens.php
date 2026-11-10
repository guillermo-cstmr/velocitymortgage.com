<?php
/**
 * Design tokens from the Figma style guide, set as Kadence defaults.
 *
 * Source: Figma "Velocity Website", page "Style Guide".
 *
 * These are DEFAULTS. A value saved in the database wins over them:
 * - Typography, buttons and layout are theme mods. A new child theme has none
 *   saved, so the defaults below apply as soon as the theme is active.
 * - The palette is the global option `kadence_global_palette`. If that option
 *   exists, it hides the palette below. Run `wp velocity cleanup` to remove it.
 *
 * Do not change these from the Customizer. Edit this file and deploy.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * The palette, mapped to the role Kadence gives each slot.
 *
 * @return array[] Slot number => color and name.
 */
function vm_palette() {
	return array(
		1  => array( '#d37031', 'Action Orange' ),      // Accent.
		2  => array( '#bb5013', 'Deep Rust' ),          // Accent, hover.
		3  => array( '#010739', 'Brand Navy' ),         // Headings.
		4  => array( '#010739', 'Brand Navy (body)' ),  // Body text. Navy in the design.
		5  => array( '#2b3578', 'Indigo' ),             // Medium text.
		6  => array( '#666666', 'Gray' ),               // Subtle text.
		7  => array( '#e0e5f7', 'Light Blue' ),         // Subtle background.
		8  => array( '#f9f9f9', 'Off White' ),          // Lighter background.
		9  => array( '#ffffff', 'White' ),
		10 => array( '#f18521', 'Supporting Orange' ),  // Complement.
		11 => array( '#13612e', 'Success' ),
		12 => array( '#1159af', 'Info' ),
		13 => array( '#b82105', 'Alert' ),
		14 => array( '#f7630c', 'Warning' ),
		15 => array( '#f5a524', 'Rating' ),
	);
}

/**
 * Replace Kadence's default palette with the Velocity palette.
 *
 * @param string $palettes Default palette as JSON.
 * @return string
 */
function vm_palette_defaults( $palettes ) {
	$colors = array();

	foreach ( vm_palette() as $slot => $color ) {
		$colors[] = array(
			'color' => $color[0],
			'slug'  => 'palette' . $slot,
			'name'  => $color[1],
		);
	}

	// Kadence keeps three palettes. The site only uses one, so all three match.
	return wp_json_encode(
		array(
			'palette'        => $colors,
			'second-palette' => $colors,
			'third-palette'  => $colors,
			'active'         => 'palette',
		)
	);
}
add_filter( 'kadence_global_palette_defaults', 'vm_palette_defaults', 20 );

/**
 * Build a Kadence heading typography array.
 *
 * Headings inherit the family from `heading_font`.
 *
 * @param int    $desktop Desktop size in px.
 * @param int    $tablet  Tablet size in px.
 * @param int    $mobile  Mobile size in px.
 * @param string $weight  Desktop font weight.
 * @param float  $spacing Letter spacing in em.
 * @return array
 */
function vm_heading( $desktop, $tablet, $mobile, $weight, $spacing = 0 ) {
	return array(
		'size'          => array(
			'desktop' => $desktop,
			'tablet'  => $tablet,
			'mobile'  => $mobile,
		),
		'sizeType'      => 'px',
		'lineHeight'    => array(
			'desktop' => 1.1,
		),
		'lineType'      => '-',
		'letterSpacing' => array(
			'desktop' => $spacing,
		),
		'spacingType'   => 'em',
		'family'        => 'inherit',
		'google'        => false,
		'weight'        => $weight,
		'variant'       => '400' === $weight ? 'regular' : $weight,
		'color'         => 'palette3',
	);
}

/**
 * Typography for the two button styles in the design.
 *
 * The family is named explicitly so Kadence loads the 600 weight.
 *
 * @return array
 */
function vm_button_typography() {
	return array(
		'size'       => array(
			'desktop' => 20,
		),
		'sizeType'   => 'px',
		'lineHeight' => array(
			'desktop' => 1.1,
		),
		'lineType'   => '-',
		'family'     => 'Encode Sans',
		'google'     => true,
		'weight'     => '600',
		'variant'    => '600',
	);
}

/**
 * Pill radius for every button style.
 *
 * @return array
 */
function vm_button_radius() {
	return array(
		'size' => array(
			'mobile'  => '',
			'tablet'  => '',
			'desktop' => 9999,
		),
		'unit' => array(
			'mobile'  => 'px',
			'tablet'  => 'px',
			'desktop' => 'px',
		),
	);
}

/**
 * Button padding: 16px vertical, 24px horizontal.
 *
 * @return array
 */
function vm_button_padding() {
	return array(
		'size'   => array(
			'desktop' => array( 16, 24, 16, 24 ),
		),
		'unit'   => array(
			'desktop' => 'px',
		),
		'locked' => array(
			'desktop' => false,
		),
	);
}

/**
 * A disabled shadow. Buttons are flat in the design.
 *
 * @return array
 */
function vm_no_shadow() {
	return array(
		'color'    => 'rgba(0,0,0,0)',
		'hOffset'  => 0,
		'vOffset'  => 0,
		'blur'     => 0,
		'spread'   => 0,
		'inset'    => false,
		'disabled' => true,
	);
}

/**
 * Replace Kadence's option defaults with the style guide values.
 *
 * @param array $defaults Kadence option defaults.
 * @return array
 */
function vm_option_defaults( $defaults ) {
	$tokens = array(

		/*
		 * Layout. The design has a 1280px content column with 64px gutters.
		 * Kadence's content width includes the gutters, so 1280 + 2 x 64.
		 */
		'content_width'                   => array(
			'size' => 1408,
			'unit' => 'px',
		),
		'content_edge_spacing'            => array(
			'size' => array(
				'mobile'  => 20,
				'tablet'  => 32,
				'desktop' => 64,
			),
			'unit' => array(
				'mobile'  => 'px',
				'tablet'  => 'px',
				'desktop' => 'px',
			),
		),
		'site_background'                 => array(
			'desktop' => array(
				'color' => 'palette9',
			),
		),

		/*
		 * Pages are built with blocks, edge to edge. No theme title, no box,
		 * no vertical padding around the content.
		 */
		'page_layout'                     => 'fullwidth',
		'page_content_style'              => 'unboxed',
		'page_vertical_padding'           => 'hide',
		'page_title'                      => false,

		/*
		 * Fonts. Served from the site, not from Google's servers.
		 */
		'load_fonts_local'                => true,
		'preload_fonts_local'             => true,
		'base_font'                       => array(
			'size'       => array(
				'desktop' => 16,
			),
			'sizeType'   => 'px',
			'lineHeight' => array(
				'desktop' => 1.47,
			),
			'lineType'   => '-',
			'family'     => 'Encode Sans',
			'google'     => true,
			'weight'     => '400',
			'variant'    => 'regular',
			'color'      => 'palette4',
		),
		'heading_font'                    => array(
			'family'   => 'Poppins',
			'google'   => true,
			'fallback' => 'sans-serif',
			'variant'  => array( 'regular', '500', '600', '700' ),
		),

		/*
		 * Headings. Desktop and mobile sizes are from Figma.
		 * Tablet sizes are NOT in the design: they are an interpolation.
		 * Mobile weights are set in style.css.
		 */
		'h1_font'                         => vm_heading( 74, 56, 40, '400', -0.03 ),
		'h2_font'                         => vm_heading( 56, 44, 32, '400', -0.03 ),
		'h3_font'                         => vm_heading( 46, 36, 26, '400' ),
		'h4_font'                         => vm_heading( 38, 30, 22, '500' ),
		'h5_font'                         => vm_heading( 30, 24, 18, '600', -0.03 ),
		'h6_font'                         => vm_heading( 24, 20, 18, '700' ),

		/*
		 * Links.
		 */
		'link_color'                      => array(
			'highlight'      => 'palette2',
			'highlight-alt'  => 'palette3',
			'highlight-alt2' => 'palette9',
			'style'          => 'standard',
		),

		/*
		 * Primary button. Figma: Buttons / Primary, default and hover.
		 * The default fill in Figma is #c96728, which is not one of the named
		 * colors. It is kept as designed until the designer confirms.
		 */
		'buttons_color'                   => array(
			'color' => 'palette9',
			'hover' => 'palette9',
		),
		'buttons_background'              => array(
			'color' => '#c96728',
			'hover' => 'palette2',
		),
		'buttons_border_radius'           => vm_button_radius(),
		'buttons_typography'              => vm_button_typography(),
		'buttons_padding'                 => vm_button_padding(),
		'buttons_shadow'                  => vm_no_shadow(),
		'buttons_shadow_hover'            => vm_no_shadow(),

		/*
		 * Outline button. Figma: Buttons / Secondary, default and hover.
		 */
		'buttons_outline_color'           => array(
			'color' => 'palette1',
			'hover' => 'palette9',
		),
		'buttons_outline_background'      => array(
			'color' => '',
			'hover' => 'palette2',
		),
		'buttons_outline_border_colors'   => array(
			'color' => 'palette1',
			'hover' => 'palette2',
		),
		'buttons_outline_border_radius'   => vm_button_radius(),
		'buttons_outline_typography'      => vm_button_typography(),
		'buttons_outline_padding'         => vm_button_padding(),
		'buttons_outline_shadow'          => vm_no_shadow(),
		'buttons_outline_shadow_hover'    => vm_no_shadow(),

		/*
		 * Kadence's "secondary" style. Figma: Buttons / Primary, negative.
		 * White button for dark backgrounds.
		 */
		'buttons_secondary_color'         => array(
			'color' => 'palette3',
			'hover' => 'palette3',
		),
		'buttons_secondary_background'    => array(
			'color' => 'palette9',
			'hover' => 'palette7',
		),
		'buttons_secondary_border_radius' => vm_button_radius(),
		'buttons_secondary_typography'    => vm_button_typography(),
		'buttons_secondary_padding'       => vm_button_padding(),
		'buttons_secondary_shadow'        => vm_no_shadow(),
		'buttons_secondary_shadow_hover'  => vm_no_shadow(),
	);

	return array_merge( $defaults, $tokens );
}
add_filter( 'kadence_theme_options_defaults', 'vm_option_defaults', 20 );
