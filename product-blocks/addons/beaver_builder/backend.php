<?php
defined( 'ABSPATH' ) || exit;

add_filter( 'wopb_addons_config', 'wopb_beaver_builder_config' );
function wopb_beaver_builder_config( $config ) {
	$configuration = array(
		'name' => __( 'Beaver Builder', 'product-blocks' ),
		'desc' => __( 'Use WowStore blocks, patterns, or templates while creating your site with the Beaver Builder.', 'product-blocks' ),
		'is_pro' => false,
		'live' => 'https://www.wpxpo.com/wowstore/woocommerce-page-builder-integrations/?utm_source=db-wstore-addons&utm_medium=builder_integration-demo&utm_campaign=wstore-dashboard',
		'docs' => 'https://wpxpo.com/docs/wowstore/add-ons/beaver-builder-addon/addon_doc_args',
		'type' => 'integration',
		'priority' => 40
	);
	$config['wopb_beaver_builder'] = $configuration;
	return $config;
}