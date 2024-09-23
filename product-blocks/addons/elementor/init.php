<?php
defined( 'ABSPATH' ) || exit;

add_filter('wopb_addons_config', 'wopb_elementor_config');
function wopb_elementor_config( $config ) {
	$configuration = array(
		'name' => __( 'Elementor', 'product-blocks' ),
		'desc' => __( 'This integration addon allows you to use any of ProductX or Gutenberg blocks while building any pages with the Elementor plugin.', 'product-blocks' ),
		'img' => WOPB_URL.'/assets/img/addons/elementor.svg',
		'is_pro' => false,
		'live' => '',
		'docs' => 'https://wpxpo.com/docs/productx/add-ons/elementor-addon/addon_doc_args',
		'video' => '',
		'type' => 'integration',
		'priority' => 70
	);
	$config['wopb_elementor'] = $configuration;
	return $config;
}

add_action('plugins_loaded', 'wopb_elementor_init');
function wopb_elementor_init() {
	$settings = wopb_function()->get_setting('wopb_elementor');
	if ($settings == 'true') {
		if (did_action( 'elementor/loaded' )) {
			require_once WOPB_PATH . '/addons/elementor/Elementor.php';
			Elementor_WOPB_Extension::instance();
		}
	}
}