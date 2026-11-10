<?php

namespace velocity\mu;

/**
 * Performance Enhancements.
 */

class Performance {

	/**
	 * Hook into WordPress
	 *
	 * @return void
	 */
	public static function hooks() {

	//	\add_action( 'wp_head', [ __CLASS__, 'preload_fonts' ], 1 );
	//	\add_action( 'wp_head', [ __CLASS__, 'preload_home_page_hero_image' ] );

	}

	/**
	 * Preload fonts.
	 *
	 * @return void
	 */
	public static function preload_fonts() {

		echo '<link rel="preload" as="stylesheet" href="https://fonts.typotheque.com/WF-031224-010287.css?ver=6.0">';
		echo '<link rel="preload" as="stylesheet" href="https://use.typekit.net/tpw3ywb.css?ver=6.0">';

	}

	/**
	 * Preload the first image in the carousel on the home page.
	 *
	 * @return void
	 */
	public static function preload_home_page_hero_image() {

		if ( ! is_front_page() ) {
			return;
		}

		printf(
			'<link rel="preload" href="%swp-content/uploads/2022/03/velocity-we-mean-business.jpg" as="image">',
			esc_attr( trailingslashit( get_home_url( null, '/' ) ) )
		);

	}

}

Performance::hooks();
