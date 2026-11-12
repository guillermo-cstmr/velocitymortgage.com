<?php
/*
Plugin Name: Velocity Mortgage: Must-Use Functionality
Text Domain: velocitymu
License: GPL2

	Copyright (C) Feb 14, 2017  Pixel Jar  info@pixeljar.com

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'VELOCITY_PATH', plugin_dir_path( __FILE__ ) . 'velocity/' );
define( 'VELOCITY_CMB2_PATH', VELOCITY_PATH . 'vendor/cmb2/cmb2/init.php' );

// Block User & Media REST Endpoints.
require_once VELOCITY_PATH . 'rest-blocker.php';

// Block Spammy Traffic
require_once VELOCITY_PATH . 'redirect-spam-traffic.php';

// Page Ordering
require_once VELOCITY_PATH . 'lib/simple-page-ordering/simple-page-ordering.php';

// Google Analytics/Pardot
require_once VELOCITY_PATH . 'global-scripts.php';

// CPTs
require_once VELOCITY_PATH . 'cpt-careers.php';
require_once VELOCITY_PATH . 'cpt-press.php';
require_once VELOCITY_PATH . 'cpt-properties.php';
require_once VELOCITY_PATH . 'cpt-case-studies.php';
require_once VELOCITY_PATH . 'cpt-win.php';
require_once VELOCITY_PATH . 'cpt-staff.php';
require_once VELOCITY_PATH . 'cpt-account-executive.php';
require_once VELOCITY_PATH . 'cpt-toolkit.php';
require_once VELOCITY_PATH . 'resource-modal.php';


// Salesforce Marketing Cloud Tracking
require_once VELOCITY_PATH . 'salesforce-conversion-tracking.php';

// Performance enhancements.
require_once VELOCITY_PATH . 'performance.php';

// Query Filters
require_once VELOCITY_PATH . 'query-filters.php';

function vm_velocity_property_posts_per_page( $query ) {
    // Only modify the main query on the front-end
    if ( ! is_admin() && $query->is_main_query() ) {
        // Check if it's the properties post type archive
        if ( is_post_type_archive( 'velocity-property' ) ) {
            $query->set( 'posts_per_page', 12 );
        }
    }
}
add_action( 'pre_get_posts', 'vm_velocity_property_posts_per_page' );
