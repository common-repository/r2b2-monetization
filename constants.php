<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( "R2B2_OPTIONS", 'r2b2-settings' );

define( "R2B2_OPTION_PLACEMENT_LIST", 'r2b2_settings_list' );
define( "R2B2_OPTION_PLACEMENT_LIST_DEMO", 'r2b2_settings_list_demo' );
define( "R2B2_OPTION_PLACEMENT_LIST_DELIVERY", 'r2b2_settings_list_delivery' );

define( "R2B2_DELIVERY_DOMAIN", '//delivery.r2b2.io/' );
define( "R2B2_FREQUENCY_IN_POST", 2 );

define( "R2B2_FORMAT_STICKY", 'sticky' );
define( "R2B2_FORMAT_VIGNETTE", 'vignette' );
define( "R2B2_FORMAT_BANNER", 'banner' );
define( "R2B2_FORMAT_INTERSCROLLER", 'interscroller' );
define( "R2B2_FORMAT_INMEDIA", 'in-media' );
define( "R2B2_FORMAT_NATIVE", 'native' );
define( "R2B2_FORMAT_OUTSTREAM", 'outstream' );
define( "R2B2_FORMAT_BRANDING", 'branding' );

define( "R2B2_FORMATS_WITHOUT_CONTAINER", [
	R2B2_FORMAT_STICKY,
	R2B2_FORMAT_VIGNETTE,
	R2B2_FORMAT_BRANDING,
	R2B2_FORMAT_INMEDIA
] );
