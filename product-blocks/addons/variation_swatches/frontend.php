<?php
defined( 'ABSPATH' ) || exit;

/**
 * Require Main File
 * @since v.2.2.7
 */
add_action( 'wp_loaded', 'wopb_variation_swatches_init' );
function wopb_variation_swatches_init() {
	if ( wopb_function()->get_setting( 'wopb_variation_swatches' ) == 'true' ) {
		require_once WOPB_PATH . '/addons/variation_swatches/VariationSwatches.php';
		$obj = new \WOPB\VariationSwatches();
		if ( ! wopb_function()->get_setting( 'variation_switch_heading' ) ) {
			$obj->initial_setup();
		}
	}
}