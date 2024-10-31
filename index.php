<?php
/*
 * Plugin Name:       R2B2 Monetization
 * Description:       Maximize your profits today with programmatic advertising.
 * Version:           1.0.3
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            R2B2
 * Author URI:        https://r2b2.io/
 * License:           GPL-3.0-only
 * Text Domain:       r2b2-monetization
 * Domain Path:       /languages
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/options.php';
require_once __DIR__ . '/plugin-uninstall.php';

add_action( 'plugins_loaded', 'r2b2_plugins_loaded' );
function r2b2_plugins_loaded() {
	load_plugin_textdomain( 'r2b2', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/', true );
}

/**
 * Insert placement scripts (sticky and vignette) and css styles into all pages.
 */
function r2b2_enqueue_script() {
	wp_enqueue_style( 'r2b2-styles', plugins_url( '/css/r2b2-styles.css', __FILE__ ), false, r2b2_get_plugin_version() );

	$options            = get_option( R2B2_OPTIONS );
	$listDelivery       = $options[ R2B2_OPTION_PLACEMENT_LIST_DELIVERY ] ?? '';
	$listDemo           = $options[ R2B2_OPTION_PLACEMENT_LIST_DEMO ] ?? '';
	$placementsDelivery = explode( "\n", $listDelivery ) ?? [];
	$placementsDemo     = explode( "\n", $listDemo ) ?? [];

	$deviceIsMobile = wp_is_mobile();

	foreach ( $placementsDelivery as $placement ) {
		if ( in_array( r2b2_get_mediatype( $placement ), R2B2_FORMATS_WITHOUT_CONTAINER ) ) {
			if ( $deviceIsMobile === r2b2_is_placement_mobile( $placement ) ) {
				$showDemo = in_array( $placement, $placementsDemo ) && current_user_can( 'administrator' );
				r2b2_enqueue_placement( $placement, $showDemo );
			}
		}
	}
}

add_action( 'wp_enqueue_scripts', 'r2b2_enqueue_script' );

/**
 * Shortcode insertion of specific placements.
 * Insert `[r2b2 placement='domain.example/group/position/mobile']` in the page editor to an exact position where you want the ad.
 */
function r2b2_shortcode_handler( $atts ) {

	// Attributes
	$atts = shortcode_atts(
		array(
			'placement' => '',
			'demo'      => false,
		),
		$atts
	);

	if ( ! empty( $atts['placement'] ) ) {
		$placementDiv = 'r2b2-ad--' . str_replace( '/', '_', $atts['placement'] );
		$placementUrl = $atts['placement'];
		if ( ! empty( $atts['demo'] ) && current_user_can( 'administrator' ) ) {
			return r2b2_selfpromo_html( $placementUrl );
		} else {
			return r2b2_placement_html( r2b2_delivery_url( $placementUrl ), $placementDiv );
		}
	}

	return '';
}

function r2b2_shortcodes_init() {
	add_shortcode( 'r2b2', 'r2b2_shortcode_handler' );
}

add_action( 'init', 'r2b2_shortcodes_init' );

/**
 * Automatic insertion of ads into posts.
 * Ads are placed in between paragraphs.
 */
add_filter( 'the_content', 'r2b2_insert_into_posts' );

function r2b2_insert_into_posts( $content ) {
	if ( ! empty( $content ) ) {
		$options            = get_option( R2B2_OPTIONS );
		$listDelivery       = $options[ R2B2_OPTION_PLACEMENT_LIST_DELIVERY ] ?? '';
		$listDemo           = $options[ R2B2_OPTION_PLACEMENT_LIST_DEMO ] ?? '';
		$placementsDelivery = explode( "\n", $listDelivery ) ?? [];
		$placementsDemo     = explode( "\n", $listDemo ) ?? [];

		$deviceIsMobile = wp_is_mobile();
		$placements     = [];

		foreach ( $placementsDelivery as $placement ) {
			if ( ! in_array( r2b2_get_mediatype( $placement ), R2B2_FORMATS_WITHOUT_CONTAINER ) ) {
				if ( $deviceIsMobile === r2b2_is_placement_mobile( $placement ) ) {
					$placements[] = $placement;
				}
			}
		}

		$paragraphs = explode( "</p>", $content );
		$content    = '';
		foreach ( $paragraphs as $index => $paragraph ) {
			$content .= $paragraph;
			$content .= "</p>";

			if ( ( $index + 1 ) % R2B2_FREQUENCY_IN_POST === 0 ) {
				$placementIndex = round( ( $index ) / R2B2_FREQUENCY_IN_POST ) - 1;
				if ( ! empty( $placements[ $placementIndex ] ) ) {
					$placements[ $placementIndex ] = preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $placements[ $placementIndex ] );
					$placementDiv                  = 'r2b2-ad--' . str_replace( [
							'/',
							'.'
						], '_', $placements[ $placementIndex ] );
					if ( in_array( $placements[ $placementIndex ], $placementsDemo ) && current_user_can( 'administrator' ) ) {
						$content .= r2b2_selfpromo_html( $placements[ $placementIndex ] );
					} else {
						$content .= r2b2_placement_html( r2b2_delivery_url( $placements[ $placementIndex ] ), $placementDiv );
					}
				}
			}
		}
	}

	return $content;
}
