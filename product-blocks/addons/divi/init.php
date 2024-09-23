<?php
defined( 'ABSPATH' ) || exit;

add_filter('wopb_addons_config', 'wopb_divi_config');
function wopb_divi_config( $config ) {
	$configuration = array(
		'name' => __( 'Divi', 'product-blocks' ),
		'desc' => __( 'With this integration addon, you can create your custom design with ProductX or other Gutenberg blocks and use the design with Divi Builder.', 'product-blocks' ),
		'img' => WOPB_URL.'/assets/img/addons/divi.svg',
		'is_pro' => false,
		'live' => '',
		'docs' => 'https://wpxpo.com/docs/productx/add-ons/divi-addon/addon_doc_args',
		'video' => '',
		'type' => 'integration',
		'priority' => 75
	);
	$config['wopb_divi'] = $configuration;
	return $config;
}


function wopb_divi_builder() {
	$settings = wopb_function()->get_setting('wopb_divi');
	if ($settings == 'true') {
		if ( class_exists( 'ET_Builder_Module' ) ) {
			require_once WOPB_PATH . '/addons/divi/divi.php';

			$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_id = isset($_GET['post']) ? sanitize_text_field($_GET['post']) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ($action && $post_id) {
				if (get_post_type($post_id) == 'wopb_templates') {
					add_filter( 'et_builder_enable_classic_editor', '__return_false' );
				}
			}
		}
	}
}
add_action( 'init', 'wopb_divi_builder' );