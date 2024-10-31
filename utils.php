<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/options.php';

function r2b2_get_plugin_version() {
	return '1.0.0';
}

function r2b2_placement_html( $script_src, $div_id = null ) {
	$html = '';
	if ( ! empty( $div_id ) ) {
		$html .= '<div id="' . esc_attr( $div_id ) . '" class="r2b2-ad"></div>';
	}
	$html .= '<script src="' . esc_attr( $script_src ) . '" async"></script>';

	return $html;
}

function r2b2_selfpromo_html( $name, $width = 300, $height = 300 ) {
	$html = '';
	$html = '<div class="r2b2-ad">';
	$html .= '<iframe width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" frameborder="0" ';
	$html .= 'src="' . esc_attr( R2B2_DELIVERY_DOMAIN . 'static/selfpromo/banner.html?name=' . urlencode( $name ) ) . '"></iframe>';
	$html .= '</div>';

	return $html;
}

function r2b2_delivery_url( $path ) {
	return R2B2_DELIVERY_DOMAIN . 'get/' . $path;
}

function r2b2_enqueue_placement( $placement_path, $track_selfpromo = false ) {
	wp_enqueue_script(
		'r2b2-placement-' . str_replace( '/', '-', $placement_path ),
		r2b2_delivery_url( $track_selfpromo ? $placement_path . '?selfpromo=true' : $placement_path ),
		[], r2b2_get_plugin_version(), [ 'in_footer' => true ]
	);
}

function r2b2_get_mediatype( $placement_name ) {
	if ( str_contains( $placement_name, 'fixed' ) || str_contains( $placement_name, 'sticky' ) ) {
		return R2B2_FORMAT_STICKY;
	} elseif ( str_contains( $placement_name, 'vignette' ) ) {
		return R2B2_FORMAT_VIGNETTE;
	} elseif ( str_contains( $placement_name, 'interscroll' ) ) {
		return R2B2_FORMAT_VIGNETTE;
	} elseif ( str_contains( $placement_name, 'in-media' ) || str_contains( $placement_name, 'inmedia' ) ) {
		return R2B2_FORMAT_VIGNETTE;
	} elseif ( str_contains( $placement_name, 'native' ) ) {
		return R2B2_FORMAT_VIGNETTE;
	} elseif ( str_contains( $placement_name, 'outstream' ) ) {
		return R2B2_FORMAT_VIGNETTE;
	} elseif ( str_contains( $placement_name, 'branding' ) || str_contains( $placement_name, 'skin' ) ) {
		return R2B2_FORMAT_VIGNETTE;
	} else {
		return R2B2_FORMAT_BANNER;
	}
}

function r2b2_is_placement_mobile( $placement_name ) {
	return str_contains( $placement_name, 'mobile' );
}
