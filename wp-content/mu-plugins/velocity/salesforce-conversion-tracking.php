<?php
/**
 * This file handles Salesforce Marketing Cloud conversion tracking.
 *
 * @package Velocity_Mortgage
 */

/**
 * This class handles Salesforce Marketing Clour conversion tracking.
 */
class VM_Salesforce_Conversion_Tracking {

	/**
	 * Hooks into WordPress.
	 *
	 * @return void
	 */
	public static function hooks() {

		add_action( 'init', array( __CLASS__, 'set_cookie' ) );
		add_action( 'wp_footer', array( __CLASS__, 'generate_tracking_pixel' ) );

	}

	/**
	 * Captures the URL parameters passed from SF and stores them in a cookie.
	 *
	 * @return void
	 */
	public static function set_cookie() {

		// Cookie Object with placeholders.
		$cookie = [];

		// Job ID.
		if ( isset( $_GET['j'] ) ) {
			$cookie['j'] = wp_strip_all_tags( wp_unslash( $_GET['j'] ) );
		}

		// Email.
		if ( isset( $_GET['e'] ) ) {
			$cookie['e'] = wp_strip_all_tags( wp_unslash( $_GET['e'] ) );
		}

		// Subscriber ID.
		if ( isset( $_GET['sfmc_sub'] ) ) {
			$cookie['sfmc_sub'] = wp_strip_all_tags( wp_unslash( $_GET['sfmc_sub'] ) );
		}

		// List ID.
		if ( isset( $_GET['l'] ) ) {
			$cookie['l'] = wp_strip_all_tags( wp_unslash( $_GET['l'] ) );
		}

		// ID of the Landing Page URL.
		if ( isset( $_GET['u'] ) ) {
			$cookie['u'] = wp_strip_all_tags( wp_unslash( $_GET['u'] ) );
		}

		// Job Batch ID.
		if ( isset( $_GET['jb'] ) ) {
			$cookie['jb'] = wp_strip_all_tags( wp_unslash( $_GET['jb'] ) );
		}

		// Member ID.
		if ( isset( $_GET['mid'] ) ) {
			$cookie['mid'] = wp_strip_all_tags( wp_unslash( $_GET['mid'] ) );
		}

		// Set the cookie so that it works for the entire site and expires after 30 days.
		if ( ! empty( $cookie ) ) {
			setcookie( 'vm_sfmc', json_encode( $cookie ), time() + MONTH_IN_SECONDS, '/' );
		}

	}

	/**
	 * Generates an image tag that reports values back to Salesforce Marketing Cloud.
	 *
	 * @return void
	 */
	public static function generate_tracking_pixel() {

		global $post;

		if ( isset( $_COOKIE['vm_sfmc'] ) ) {

			$vm_sfmc = json_decode( wp_unslash( urldecode( $_COOKIE['vm_sfmc'] ) ), true );
			$xml = array(
				'<system>',
				'<system_name>tracking</system_name>',
				sprintf(
					'<action>%s</action>',
					'conversion'
				),
			);

			// Member ID.
			if ( isset( $vm_sfmc['mid'] ) && ! empty( $vm_sfmc['mid'] ) ) {
				$xml[] = sprintf(
					'<member_id>%s</member_id>',
					$vm_sfmc['mid']
				);
			} else {
				return;
			}

			// Job ID.
			if ( isset( $vm_sfmc['j'] ) && ! empty( $vm_sfmc['j'] ) ) {
				$xml[] = sprintf(
					'<job_id>%s</job_id>',
					$vm_sfmc['j']
				);
			} else {
				return;
			}

			// Email.
			if ( isset( $vm_sfmc['e'] ) && ! empty( $vm_sfmc['e'] ) ) {
				$xml[] = sprintf(
					'<email>%s</email>',
					$vm_sfmc['e']
				);
			} else {
				$xml[] = '<email></email>';
			}

			// Subscriber ID.
			if ( isset( $vm_sfmc['sfmc_sub'] ) && ! empty( $vm_sfmc['sfmc_sub'] ) ) {
				$xml[] = sprintf(
					'<sub_id>%s</sub_id>',
					$vm_sfmc['sfmc_sub']
				);
			} else {
				return;
			}

			// List ID.
			if ( isset( $vm_sfmc['l'] ) && ! empty( $vm_sfmc['l'] ) ) {
				$xml[] = sprintf(
					'<list>%s</list>',
					$vm_sfmc['l']
				);
			} else {
				return;
			}

			// ID of the Landing Page URL.
			if ( isset( $vm_sfmc['u'] ) && ! empty( $vm_sfmc['u'] ) ) {
				$xml[] = sprintf(
					'<original_link_id>%s</original_link_id>',
					$vm_sfmc['u']
				);
			} else {
				return;
			}

			// Job Batch ID.
			if ( isset( $vm_sfmc['jb'] ) && ! empty( $vm_sfmc['jb'] ) ) {
				$xml[] = sprintf(
					'<BatchID>%s</BatchID>',
					$vm_sfmc['jb']
				);
			} else {
				return;
			}

			// Unique ID for Conversion.
			$xml[] = sprintf(
				'<conversion_link_id>%s</conversion_link_id>',
				$post->ID
			);

			// Name for the Conversion Page.
			$xml[] = sprintf(
				'<link_alias>%s</link_alias>',
				get_the_title( $post->ID )
			);

			// Display Order.
			$xml[] = '<display_order>3</display_order>';

			// Display Order.
			$xml[] = '<data_set></data_set>';

			$xml[] = '</system>';

			$img = sprintf(
				'<img src="https://click.exacttarget.com/conversion.aspx?xml=%s" width="1" height="1">',
				implode( '', $xml )
			);

			echo $img;

		}

	}

}
VM_Salesforce_Conversion_Tracking::hooks();
