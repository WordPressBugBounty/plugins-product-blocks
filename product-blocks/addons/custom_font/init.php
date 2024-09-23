<?php
defined( 'ABSPATH' ) || exit;

add_filter('wopb_addons_config', 'wopb_custom_font_config');
function wopb_custom_font_config( $config ) {
	$configuration = array(
		'name' => __( 'Custom Font', 'product-blocks' ),
		'desc' => __( 'It allows you to upload custom fonts and use them on any ProductX blocks with all typographical options.', 'product-blocks' ),
		'img' => WOPB_URL.'assets/img/addons/custom_font.svg',
		'is_pro' => false,
		'live' => 'https://www.wpxpo.com/how-to-add-font-to-wordpress-woocommerce-store/live_demo_args',
		'docs' => 'https://wpxpo.com/docs/productx/add-ons/custom-fonts/addon_doc_args',
		'video' => 'https://www.youtube.com/watch?v=zOXVVQ41Pig&ab_channel=WPXPO',
		'type' => 'build',
		'priority' => 30
	);
	$arr['wopb_custom_font'] = $configuration;
	return $arr + $config;
}

add_action('init', 'wopb_custom_font_init');
function wopb_custom_font_init() {
    if(!wopb_function()->get_setting('wopb_custom_font')) {
        wopb_function()->set_setting('wopb_custom_font', 'true');
    }
	$settings = wopb_function()->get_setting('wopb_custom_font');
	if ($settings == 'true') {
		require_once WOPB_PATH . '/addons/custom_font/Custom_Font.php';
		new \WOPB\Custom_Font();
	}
}