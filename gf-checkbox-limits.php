<?php
/**
 * Plugin Name:       Gravity Forms Checkbox Limits
 * Plugin URI:        https://staffordtechnologies.net/checkbox-limiter/
 * Description:       Adds min/max checkbox selection limits to Gravity Forms checkbox fields, with front-end disabling and per-page server validation.
 * Version:           1.0.1
 * Author:            Stafford Technologies
 * Author URI:        https://staffordtechnologies.net/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gf-checkbox-limits
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 *
 * @package GFCheckboxLimits
 */

defined( 'ABSPATH' ) || exit;

define( 'GF_CHECKBOX_LIMITS_VERSION', '1.0.1' );
define( 'GF_CHECKBOX_LIMITS_FILE', __FILE__ );
define( 'GF_CHECKBOX_LIMITS_PATH', plugin_dir_path( __FILE__ ) );
define( 'GF_CHECKBOX_LIMITS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Support contact used in plugin meta and documentation.
 */
define( 'GF_CHECKBOX_LIMITS_SUPPORT_EMAIL', 'helpdesk@staffordtechnologies.net' );

/**
 * Register the add-on with Gravity Forms once the framework is ready.
 */
add_action( 'gform_loaded', 'gf_checkbox_limits_bootstrap', 5 );

/**
 * Plugins screen: Docs + Support links for this plugin only.
 */
add_filter( 'plugin_row_meta', 'gf_checkbox_limits_plugin_row_meta', 10, 2 );

/**
 * Bootstrap: load class and register with GFAddOn.
 *
 * @return void
 */
function gf_checkbox_limits_bootstrap() {
	if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
		return;
	}

	require_once GF_CHECKBOX_LIMITS_PATH . 'includes/class-gf-checkbox-limits.php';
	GFAddOn::register( 'GF_Checkbox_Limits' );
}

/**
 * Convenience accessor.
 *
 * @return GF_Checkbox_Limits|null
 */
function gf_checkbox_limits() {
	if ( ! class_exists( 'GF_Checkbox_Limits' ) ) {
		return null;
	}
	return GF_Checkbox_Limits::get_instance();
}

/**
 * Add Docs and Support links under this plugin on the Plugins screen.
 *
 * @param array  $links       Existing meta links.
 * @param string $plugin_file Plugin basename.
 * @return array
 */
function gf_checkbox_limits_plugin_row_meta( $links, $plugin_file ) {
	if ( plugin_basename( GF_CHECKBOX_LIMITS_FILE ) !== $plugin_file ) {
		return $links;
	}

	$page_url = 'https://staffordtechnologies.net/checkbox-limiter/';

	$links[] = sprintf(
		'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
		esc_url( $page_url ),
		esc_html__( 'Docs', 'gf-checkbox-limits' )
	);
	$links[] = sprintf(
		'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
		esc_url( $page_url ),
		esc_html__( 'Support', 'gf-checkbox-limits' )
	);

	return $links;
}
