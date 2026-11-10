<?php

class Velocity_Google_Analytics {

	function __construct() {}

	public static function hooks() {

		if ( 'production' !== \wp_get_environment_type() ) {
			// return;
		}

		add_action( 'wp_head', array( __CLASS__, 'wp_head' ), 1 );
		add_action( 'fl_body_open', array( __CLASS__, 'fl_body_open' ), 10 );

	}

	public static function wp_head() { ?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-K6K4Z4D');</script>
<!-- End Google Tag Manager -->
	<?php }

	public static function fl_body_open() { ?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K6K4Z4D"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
	<?php }

}

Velocity_Google_Analytics::hooks();
