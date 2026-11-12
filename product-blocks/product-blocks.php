<?php
/**
 * Plugin Name: WowStore
 * Description: <a href="https://www.wpxpo.com/product/wowstore/?utm_source=wowstore_org&utm_medium=wpxpo&utm_campaign=wstore-dashboard">WowStore</a> is an all-in-one solution for creating visually stunning and conversion-focused WooCommerce stores. The main and attractive features are WooCommerce Builder, Variation Swatches, Wishlist, Comparison, etc.
 * Version:     4.6.4
 * Requires at least: 6.8
 * Requires PHP: 7.4
 * Author:      WowStore Team
 * Author URI:  https://www.wpxpo.com/product/wowstore/?utm_source=db-wstore-plugin&utm_medium=wpxpo&utm_campaign=wstore-dashboard
 * Text Domain: product-blocks
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires Plugins: woocommerce
 *
 * @package     WowStore
 **/

defined( 'ABSPATH' ) || exit;

// Requirements guard: runs before any other file loads, so an unsupported site sees a notice instead of errors.
// Keep this block to syntax that every supported PHP version accepts.
$wopb_min_php = '7.4';
$wopb_min_wp  = '6.8';
$wopb_problem = '';
if ( version_compare( PHP_VERSION, $wopb_min_php, '<' ) ) {
	$wopb_problem = 'php';
} elseif ( version_compare( $GLOBALS['wp_version'], $wopb_min_wp, '<' ) ) {
	$wopb_problem = 'wp';
}
if ( '' !== $wopb_problem ) {
	// Messages are translated inside the notice, after init, so the guard itself never loads translations too early.
	add_action(
		'admin_notices',
		static function () use ( $wopb_problem, $wopb_min_php, $wopb_min_wp ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			if ( 'php' === $wopb_problem ) {
				/* translators: 1: required PHP version, 2: PHP version the site runs. */
				$message = sprintf( __( 'WowStore needs PHP %1$s or newer, but this site runs PHP %2$s. Ask your host to upgrade PHP, then reload this page.', 'product-blocks' ), $wopb_min_php, PHP_VERSION );
			} else {
				/* translators: 1: required WordPress version, 2: WordPress version the site runs. */
				$message = sprintf( __( 'WowStore needs WordPress %1$s or newer, but this site runs WordPress %2$s. Update WordPress under Dashboard > Updates, then reload this page.', 'product-blocks' ), $wopb_min_wp, $GLOBALS['wp_version'] );
			}
			echo '<div class="notice notice-error"><p><strong>WowStore:</strong> ' . esc_html( $message ) . '</p></div>';
		}
	);
	return;
}

// Define Constants.
define( 'WOPB_VER', '4.6.4' );
define( 'WOPB_URL', plugin_dir_url( __FILE__ ) );
define( 'WOPB_BASE', plugin_basename( __FILE__ ) );
define( 'WOPB_PATH', plugin_dir_path( __FILE__ ) );

// Register class loading before helpers and initialization.
require_once WOPB_PATH . 'includes/autoload.php';

// Language and Template Load.
add_action( 'init', 'wopb_language_n_template_load' );

/**
 * Loads the language files and templates for the Product Blocks plugin.
 *
 * This function is responsible for initializing the localization and template loading
 * mechanisms required by the plugin to support multiple languages and custom templates.
 *
 * @return void
 */
function wopb_language_n_template_load() {
	// Template Load .
	if ( class_exists( 'woocommerce' ) ) {
		new \WOPB\Templates();
	}
}

// Common Functions.
if ( ! function_exists( 'wopb_function' ) ) {
	/**
	 * Retrieves the instance of the Functions class.
	 *
	 * This function ensures that the Functions class is loaded and returns its singleton instance.
	 *
	 * @return \WOPB\Functions
	 */
	function wopb_function() {
		return \WOPB\Functions::get_instance();
	}
}

// Plugin Initialization and others.
new \WOPB\Initialization();
