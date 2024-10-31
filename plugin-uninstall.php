<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/options.php';
/**
 * Activate the plugin.
 */
function r2b2_uninstall()
{
    delete_option(R2B2_OPTIONS);
}

register_uninstall_hook(__FILE__, 'r2b2_uninstall');
