<?php
defined( 'ABSPATH' ) || exit;

/**
 * Variation Swatches Initial Configuration
 * @since v.2.2.7
 */
add_filter('wopb_addons_config', 'wopb_variation_swatches_config');
function wopb_variation_swatches_config( $config ) {
	$configuration = array(
		'name' => __( 'Variation Swatches', 'product-blocks' ),
        'desc' => __( ' Ensure effortless shopping experiences by converting product attributes into beautiful sizes, colors, and image swatches.', 'product-blocks' ),
        'img' => WOPB_URL.'/assets/img/addons/variation_switcher.svg',
        'is_pro' => false,
        'live' => 'https://www.wpxpo.com/productx/addons/woocommerce-variation-swatches/live_demo_args',
        'docs' => 'https://wpxpo.com/docs/productx/add-ons/variation-swatches-addon/addon_doc_args',
        'video' => 'https://www.youtube.com/watch?v=i65S6QiFgFE',
        'type' => 'build',
        'priority' => 5
	);
	$config['wopb_variation_swatches'] = $configuration;
	return $config;
}

/**
 * Require Main File
 * @since v.2.2.7
 */
add_action('wp_loaded', 'wopb_variation_swatches_init');
function wopb_variation_swatches_init(){
	$settings = wopb_function()->get_setting();
	if ( isset($settings['wopb_variation_swatches']) ) {
		if ($settings['wopb_variation_swatches'] == 'true') {
			require_once WOPB_PATH . '/addons/variation_swatches/VariationSwatches.php';
			$obj = new \WOPB\VariationSwatches();
			if( !isset($settings['variation_switch_heading']) ){
				$obj->initial_setup();
			}
		}
	}

	add_filter('wopb_settings', 'get_variation_swatches_settings', 10, 1);
}

/**
 * Variation Swatches Addons Default Settings Param
 *
 * @param ARRAY | Default Filter Configuration
 * @return ARRAY
 * @since v.2.2.7
 */
function get_variation_swatches_settings($config)
{
    $arr = array(
        'wopb_variation_swatches' => array(
            'label' => __('Variation Swatches', 'product-blocks'),
            'attr' => array(
                'variation_switch_heading' => array(
                    'type' => 'heading',
                    'label' => __('Variation Swatches Settings', 'product-blocks'),
                ),
                'variation_switch_tooltip_enable' => array(
                    'type' => 'switch',
                    'label' => __('Enable / Disable Tooltip', 'product-blocks'),
                    'default' => 'yes',
                    'desc' => 'Click if you want to show tooltip',
                ),
                'variation_switch_shape_style' => array(
                    'type' => 'radio',
                    'label' => __('Shape Style (Color, Image)', 'product-blocks'),
                    'options' => array(
                        'square' => __( 'Square','product-blocks' ),
                        'circle' => __( 'Circle','product-blocks' ),
                    ),
                    'default' => 'square'
                ),
                'variation_switch_label_is_background' => array(
                    'type' => 'switch',
                    'label' => __('Label Background', 'product-blocks'),
                    'default' => 'yes',
                    'desc' => __('Click if you want to show/hide background', 'product-blocks')
                ),
                'variation_switch_dropdown_to_button' => array(
                    'type' => 'switch',
                    'label' => __('Dropdown to Button', 'product-blocks'),
                    'default' => '',
                    'desc' => 'Convert default dropdowns to button type',
                ),
                'variation_switch_width' => array(
                    'type' => 'text',
                    'label' => __('Width (PX)', 'product-blocks'),
                    'default' => __('25', 'product-blocks'),
                ),
                'variation_switch_height' => array(
                    'type' => 'text',
                    'label' => __('Height (PX)', 'product-blocks'),
                    'default' => __('25', 'product-blocks'),
                ),
                'variation_image_width' => array(
                    'type' => 'text',
                    'label' => __('Image Width (PX)', 'product-blocks'),
                    'default' => __('40', 'product-blocks'),
                ),
                'variation_image_height' => array(
                    'type' => 'text',
                    'label' => __('Image Height (PX)', 'product-blocks'),
                    'default' => __('40', 'product-blocks'),
                ),
                'variation_switch_shop_page_enable' => array(
                    'type' => 'switch',
                    'label' => __('Enable / Disable', 'product-blocks'),
                    'default' => '',
                    'desc' => 'Show swatches in shop/archive pages',
                ),
                'product_image_in_variation_switch' => array(
                    'type' => 'switch',
                    'label' => __('Product Image in Swatch', 'product-blocks'),
                    'default' => '',
                    'desc' => 'Show image in variation image from product',
                    'pro' => true,
                ),
                'variation_switch_position' => array(
                    'type' => 'select',
                    'label' => __('Swatches Position(Shop/Listing)', 'product-blocks'),
                    'options' => [
                            'before_title' => 'Before Title',
                            'after_title' => 'After Title',
                            'before_price' => 'Before Price',
                            'after_price' => 'After Price',
                            'before_cart' => 'Before Cart',
                            'after_cart' => 'After Cart',
                    ],
                    'default' => 'after_cart', 'product-blocks',
                    'desc' => 'Choose where to insert swatches in shop/listing', 'product-blocks'
                ),
            )
        )
    );

    return array_merge($config, $arr);
}