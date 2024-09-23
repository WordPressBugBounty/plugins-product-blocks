<?php
defined( 'ABSPATH' ) || exit;

add_filter('wopb_addons_config', 'wopb_oxygen_config');
function wopb_oxygen_config( $config ) {
	$configuration = array(
		'name' => __( 'Oxygen Builder', 'product-blocks' ),
		'desc' => __( 'Create custom designs with ProductX or other Gutenberg blocks, save them as saved templates, and use them with the Oxygen Builder.', 'product-blocks' ),
		'img' => WOPB_URL.'/assets/img/addons/oxygen.svg',
		'is_pro' => false,
		'live' => '',
		'docs' => 'https://wpxpo.com/docs/productx/add-ons/oxygen-builder-addon/addon_doc_args',
		'video' => '',
		'type' => 'integration',
		'priority' => 80
	);
	$config['wopb_oxygen'] = $configuration;
	return  $config;
}


function wopb_oxygen_builder() {
	$settings = wopb_function()->get_setting('wopb_oxygen');
	if ($settings == 'true') {
		if ( class_exists( 'OxygenElement' ) ) {
			require_once WOPB_PATH . '/addons/oxygen/oxygen.php';
		}
	}
}
add_action( 'wp_loaded', 'wopb_oxygen_builder' );