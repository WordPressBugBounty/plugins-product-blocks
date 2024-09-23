<?php
defined( 'ABSPATH' ) || exit;

add_filter('wopb_addons_config', 'wopb_builder_config');
function wopb_builder_config( $config ) {
	$configuration = array(
		'name' => __( 'Woo Builder', 'product-blocks' ),
		'desc' => __( 'Design all pages of your WooCommerce store from scratch or save time by importing premade templates with a single click.', 'product-blocks' ),
		'img' => WOPB_URL.'assets/img/addons/builder.svg',
		'is_pro' => false,
		'live' => 'https://www.wpxpo.com/productx/addons/woocommerce-builder/live_demo_args',
		'docs' => 'https://wpxpo.com/docs/productx/productx-woocommerce-builder/addon_doc_args',
		'video' => 'https://www.youtube.com/watch?v=fu3DH7mEZ8U&ab_channel=WPXPO',
		'type' => 'build',
		'priority' => 1
	);
	$config['wopb_builder'] = $configuration;
	return $config;
}

add_action('init', 'wopb_builder_init');
function wopb_builder_init() {
	$settings = wopb_function()->get_setting('wopb_builder');
	if ($settings == 'true') {
		require_once WOPB_PATH . '/addons/builder/Builder.php';
		require_once WOPB_PATH . '/addons/builder/Condition.php';
		require_once WOPB_PATH . '/addons/builder/RequestAPI.php';
		new \WOPB\Builder();
		new \WOPB\Condition();
		new \WOPB\RequestAPI();
	}
	
}

if (wopb_function()->get_setting('wopb_builder') == 'true') {
	add_action( 'after_setup_theme', 'wopb_gallery_image_support' );
	function wopb_gallery_image_support() {
		if(!class_exists('Flatsome_Default')){
            add_theme_support( 'wc-product-gallery-slider' );
        }
	}
}