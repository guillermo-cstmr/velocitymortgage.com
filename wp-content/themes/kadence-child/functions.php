<?php
/**
 * Setup Child Theme Styles
 */
function kadence_child_enqueue_styles() {
	wp_enqueue_style( 'kadence_child-style', get_stylesheet_directory_uri() . '/style.css', false, '1.0' );
}
add_action( 'wp_enqueue_scripts', 'kadence_child_enqueue_styles', 20 );


/**
 * Setup Child Theme Palettes
 *
 * @param string $palettes registered palette json.
 * @return string
 */
function kadence_child_change_palette_defaults( $palettes ) {
	$palettes = '{"palette":[{"color":"#2B6CB0","slug":"palette1","name":"Palette Color 1"},{"color":"#215387","slug":"palette2","name":"Palette Color 2"},{"color":"#1A202C","slug":"palette3","name":"Palette Color 3"},{"color":"#2D3748","slug":"palette4","name":"Palette Color 4"},{"color":"#4A5568","slug":"palette5","name":"Palette Color 5"},{"color":"#718096","slug":"palette6","name":"Palette Color 6"},{"color":"#EDF2F7","slug":"palette7","name":"Palette Color 7"},{"color":"#F7FAFC","slug":"palette8","name":"Palette Color 8"},{"color":"#ffffff","slug":"palette9","name":"Palette Color 9"}],"second-palette":[{"color":"#2B6CB0","slug":"palette1","name":"Palette Color 1"},{"color":"#215387","slug":"palette2","name":"Palette Color 2"},{"color":"#1A202C","slug":"palette3","name":"Palette Color 3"},{"color":"#2D3748","slug":"palette4","name":"Palette Color 4"},{"color":"#4A5568","slug":"palette5","name":"Palette Color 5"},{"color":"#718096","slug":"palette6","name":"Palette Color 6"},{"color":"#EDF2F7","slug":"palette7","name":"Palette Color 7"},{"color":"#F7FAFC","slug":"palette8","name":"Palette Color 8"},{"color":"#ffffff","slug":"palette9","name":"Palette Color 9"}],"third-palette":[{"color":"#2B6CB0","slug":"palette1","name":"Palette Color 1"},{"color":"#215387","slug":"palette2","name":"Palette Color 2"},{"color":"#1A202C","slug":"palette3","name":"Palette Color 3"},{"color":"#2D3748","slug":"palette4","name":"Palette Color 4"},{"color":"#4A5568","slug":"palette5","name":"Palette Color 5"},{"color":"#718096","slug":"palette6","name":"Palette Color 6"},{"color":"#EDF2F7","slug":"palette7","name":"Palette Color 7"},{"color":"#F7FAFC","slug":"palette8","name":"Palette Color 8"},{"color":"#ffffff","slug":"palette9","name":"Palette Color 9"}],"active":"palette"}';
	return $palettes;
}
add_filter( 'kadence_global_palette_defaults', 'kadence_child_change_palette_defaults', 20 );

/**
 * Setup Child Theme Defaults
 *
 * @param array $defaults registered option defaults with kadence theme.
 * @return array
 */
function kadence_child_change_option_defaults( $defaults ) {
	$new_defaults = '{"initial_version":"1.2.6"}';
	$new_defaults = json_decode( $new_defaults, true );
	return wp_parse_args( $new_defaults, $defaults );
}
add_filter( 'kadence_theme_options_defaults', 'kadence_child_change_option_defaults', 20 );

/**
 * On the staff mobile card block, add the staff member's title.
 * 
 * @param string $block_content The block content.
 * @param array  $block The block data.
 * @return string Modified block content.
 */
function vm_add_staff_title( $block_content, $block ) {
// Only proceed if our class is present.
	if ( strpos( $block_content, 'staff-mobile-card' ) === false ) {
		return $block_content;
	}

	// Check if we've already processed this content (avoid duplicate processing).
	if ( strpos( $block_content, 'staff-title' ) !== false ) {
		return $block_content;
	}

	// Decode to handle &amp; in href attributes.
    $decoded = html_entity_decode( $block_content, ENT_QUOTES );

	// Find each staff member block with post ID and h2 title.
	// Pattern to match: <li class="...post-3559...">....<h2 class="entry-title">...</h2>
	$pattern = '/<li class="[^"]*post-(\d+)[^"]*"[^>]*>.*?(<h2 class="entry-title"><a href="[^"]+">[^<]+<\/a><\/h2>)/s';
	preg_match_all( $pattern, $decoded, $matches, PREG_SET_ORDER );

	if ( empty( $matches ) ) {
		return $block_content;
	}

	// Replace each h2 tag with h2 + h3.
	$modified = $decoded;
	foreach ( $matches as $match ) {
		$post_id = intval( $match[1] );  // Extract post ID from class
		$h2_tag = $match[2];              // The h2 tag to find and replace

		// Get the staff title from post meta.
		$staff_title_text = get_post_meta( $post_id, 'velocity_staff_title_short', true );

		// Fallback to "John Smith" if no title found.
		if ( empty( $staff_title_text ) ) {
			$staff_title_text = 'John Smith';
		}

		$h3_tag = '<h3 class="staff-title">' . esc_html( $staff_title_text ) . '</h3>';

		// Replace this specific h2 with h2 + h3.
		$modified = preg_replace(
			'/' . preg_quote( $h2_tag, '/' ) . '/',
			$h2_tag . $h3_tag,
			$modified,
			1 // Replace only the first occurrence to avoid duplicates.
		);
	}

	return $modified;
}
add_filter( 'render_block', 'vm_add_staff_title', 20, 2 );