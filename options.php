<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/options.php';

add_action( 'admin_enqueue_scripts', 'r2b2_admin_styles' );
function r2b2_admin_styles() {
	$pluginVersion = r2b2_get_plugin_version();
	$screenId      = get_current_screen()->id;
	if ( $screenId === 'settings_page_r2b2' || $screenId === 'toplevel_page_r2b2' ) {
		wp_enqueue_style( 'r2b2_admin_css_bootstrap', plugins_url( '/r2b2-ui/dist/css/styles.css', __FILE__ ), false, $pluginVersion );
		wp_enqueue_style( 'r2b2_admin_css', plugins_url( '/css/r2b2-admin.css', __FILE__ ), false, $pluginVersion );
		wp_enqueue_script( 'r2b2_admin_options_js', plugins_url( '/r2b2-options.js', __FILE__ ), false, $pluginVersion, [ 'strategy' => 'defer' ] );
	}
}

/**
 * Register options page for admin
 */
add_action( 'admin_menu', 'r2b2_admin_add_page' );

function r2b2_admin_add_page() {
	add_menu_page(
		'R2B2 Placements Settings',
		'R2B2',
		'manage_options',
		'r2b2',
		'r2b2_options_page',
		plugins_url( '/img/logo-white-positive-icon.png', __FILE__ ),
		84.9
	);
}

/**
 * Content of the options page
 */
function r2b2_options_page() {
	?>
    <div class="r2b2-options">
        <img src="<?php echo esc_attr( plugins_url( '/img/r2b2-logo-long.svg', __FILE__ ) ); ?>" alt="R2B2 logo"
             style="height: 2em;">
        <h2 class="heading heading-2" style="line-height: 1em;">R2B2 Monetization</h2>
        <p class="r2b2-perex">
            R2B2 Monetization helps you implement the R2B2 ad codes to your websites.
            Copy the source codes from R2B2, add them to the plugin with the "Add placements" button, and the ads will
            be inserted onto your website automatically.
            To check if the placement is correct, turn on a demo ad for any ad space, and turn off the demo mode later.
        </p>
        <noscript>
            <div style="margin: 10px; padding: 20px 15px; border: 2px solid red; background: white; color: red; font-size: large; font-weight: bold;">
                Javascript is not enabled on your device! Enable it to proceed.
            </div>
        </noscript>
        <h3 class="heading heading-3">Placements</h3>
        <button class="button button__primary button--sm" onclick="window.r2b2.displayPopupAddPlacements();">
            <span class="text-block text-block--bold text-block--md">&plus; Add placements</span>
        </button>
        <table id="r2b2-placement-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Media type</th>
                <th>Status</th>
                <th title="To validate functionality of a specific ad space you can switch on the demo ad. The demo ad will be displayed to the website administrators and doesn’t yield any profit.">
                    Demo ad
                </th>
            </tr>
            </thead>
        </table>
        <form action="options.php" method="post">
			<?php settings_fields( 'r2b2' ); ?>
			<?php do_settings_sections( 'r2b2' ); ?>
			<?php submit_button( 'Save' ); ?>
        </form>
    </div>

	<?php
}


/**
 * Register every option
 */
add_action( 'admin_init', 'r2b2_admin_init' );

function r2b2_admin_init() {
	register_setting( 'r2b2', R2B2_OPTIONS, 'r2b2_options_validate' );

	add_settings_section( 'r2b2_section_all', '', 'r2b2_section_all_text', 'r2b2' );
	add_settings_field( R2B2_OPTION_PLACEMENT_LIST, 'Placement list', 'r2b2_settings_list_setup', 'r2b2', 'r2b2_section_all' );
	add_settings_field( R2B2_OPTION_PLACEMENT_LIST_DELIVERY, 'Placements for delivery', 'r2b2_settings_list_delivery_setup', 'r2b2', 'r2b2_section_all' );
	add_settings_field( R2B2_OPTION_PLACEMENT_LIST_DEMO, 'Demo placements', 'r2b2_settings_list_demo_setup', 'r2b2', 'r2b2_section_all' );

}


function r2b2_settings_list_setup() {
	r2b2_textarea_render( R2B2_OPTION_PLACEMENT_LIST );
}

function r2b2_settings_list_delivery_setup() {
	r2b2_textarea_render( R2B2_OPTION_PLACEMENT_LIST_DELIVERY );
}

function r2b2_settings_list_demo_setup() {
	r2b2_textarea_render( R2B2_OPTION_PLACEMENT_LIST_DEMO );
}

function r2b2_textarea_render( $optionKey ) {
	$options       = get_option( R2B2_OPTIONS );
	$textareaValue = '';
	if ( isset( $options[ $optionKey ] ) ) {
		$textareaValue = $options[ $optionKey ];
	}
	echo "<textarea id='" . esc_attr( $optionKey ) . "' name='" . esc_attr( R2B2_OPTIONS ) . "[" . esc_attr( $optionKey ) . "]' rows='5' cols='75' spellcheck='false' style='white-space: pre;'>";
	echo ( esc_html( $textareaValue ) ) . "</textarea>";
}

/**
 * Section All
 */
function r2b2_section_all_text() {
	echo '';
}


/**
 * Form data validator
 * Validates data entered into settings form.
 *
 * @param $input array User input
 *
 * @return array Validated data for storage
 */
function r2b2_options_validate( $input ) {
	$valid = [];
	$lists = [
		R2B2_OPTION_PLACEMENT_LIST,
		R2B2_OPTION_PLACEMENT_LIST_DEMO,
		R2B2_OPTION_PLACEMENT_LIST_DELIVERY
	];
	$regex = '/^[a-z0-9\.\-_\/]+$/';
	foreach ( $lists as $listKey ) {
		$validPlacements = [];
		if ( ! empty( $input[ $listKey ] ) ) {
			$list = explode( "\n", $input[ $listKey ] );
			foreach ( $list as $placement ) {
				if ( empty( $placement ) ) {
					continue;
				}
				$placement = trim( $placement );
				if ( ! preg_match( $regex, $placement ) ) {
					continue;
				}
				if ( ! in_array( substr_count( $placement, '/' ), [ 2, 3 ] ) ) {
					continue;
				}
				$validPlacements[] = $placement;
			}
		}
		$valid[ $listKey ] = join( "\n", $validPlacements );
	}

	return $valid;
}

?>
