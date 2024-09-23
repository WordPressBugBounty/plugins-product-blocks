<?php
defined( 'ABSPATH' ) || exit;

/**
 * SaveTemplate Addons Initial Configuration
 * @since v.1.1.0
 */
add_filter( 'wopb_addons_config', 'wopb_templates_config' );
function wopb_templates_config( $config ) {
	$configuration = array(
		'name' => __( 'Saved Template', 'product-blocks' ),
		'desc' => __( 'Create reusable templates with the Blocks and Starter Packs and use them anywhere via shortcode.', 'product-blocks' ),
		'is_pro' => false,
		'live' => 'https://www.wpxpo.com/wowstore/woocommerce-saved-template/live_demo_args',
		'docs' => 'https://wpxpo.com/docs/wowstore/add-ons/saved-templates-addon/addon_doc_args',
		'type' => 'build',
		'priority' => 60
	);
	$config['wopb_templates'] = $configuration;
	return $config;
}