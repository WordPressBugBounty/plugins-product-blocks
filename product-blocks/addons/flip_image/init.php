<?php
defined( 'ABSPATH' ) || exit;

/**
 * Flip Image Addons Initial Configuration
 * @since v.1.1.0
 */
add_filter('wopb_addons_config', 'wopb_flipimage_config');
function wopb_flipimage_config( $config ) {
	$configuration = array(
		'name' => __( 'Product Image Flipper', 'product-blocks' ),
        'desc' => __( 'It allows you to display a different image of products when the shoppers of your store hover over a product.', 'product-blocks' ),
        'img' => WOPB_URL.'/assets/img/addons/imageFlip.svg',
        'is_pro' => false,
        'live' => 'https://www.wpxpo.com/productx/addons/product-image-flipper/live_demo_args',
        'docs' => 'https://wpxpo.com/docs/productx/add-ons/flip-image-settings/addon_doc_args',
        'video' => '',
        'type' => 'exclusive',
        'priority' => 75
	);
	$config['wopb_flipimage'] = $configuration;
	return $config;
}

/**
 * Require Main File
 * @since v.1.1.0
 */
add_action('wp_loaded', 'wopb_flipimage_init');
function wopb_flipimage_init() {
	$settings = wopb_function()->get_setting();
	if ( isset( $settings['wopb_flipimage'] ) ) {
		if ( $settings['wopb_flipimage'] == 'true' ) {
			require_once WOPB_PATH . '/addons/flip_image/FlipImage.php';
			$obj = new \WOPB\FlipImage();
			if( !isset( $settings['flip_group_variable_disable'] ) ){
				$obj->initial_setup();
			}
		}
	}

	add_filter( 'wopb_settings', 'wopb_get_flip_image_settings', 10, 1 );
}




/**
 * FlipImage Addons Default Settings Param
 *
 * @since v.1.1.0
 * @param ARRAY | Default Filter Congiguration
 * @return ARRAY
 */
function wopb_get_flip_image_settings($config){
    $arr = array(
        'wopb_flipimage' => array(
            'label' => __('Flip Image', 'product-blocks'),
            'attr' => array(
                'wopb_flipimage' => array(
                    'type' => 'hidden',
                    'value' => 'true'
                ),
                'flipimage_heading' => array(
                    'type' => 'heading',
                    'label' => __('Flip Image Settings', 'product-blocks'),
                ),
                'flip_image_basic_settings' => array(
                    'type' => 'section',
                    'label' => __('Basic Settings', 'product-blocks'),
                    'attr' => array(
                        'flip_image_source' => array(
                            'type' => 'radio',
                            'label' => __('Flip Image Source', 'product-blocks'),
                            'display' => 'inline-box',
                            'options' => array(
                                'gallery' => __( 'First Image From Gallery','product-blocks' ),
                            ),
                            'pro' => array(
                                'feature' => __( 'Specific Flip Image Selection Option','product-blocks' ),
                            ),
                            'default' => 'gallery',
                        ),

                        'flip_animation_type' => array(
                            'type' => 'radio',
                            'label' => __('Product Flip Animation Type', 'product-blocks'),
                            'display' => 'inline-box',
                            'options' => array(
                                'fade_in' => __( 'Fade In','product-blocks' ),
                                'zoom_in' => __( 'Zoom In','product-blocks' ),
                                'fade_in_zoom_in' => __( 'Fade In & Zoom In','product-blocks' ),
                                'slide_right_in' => __( 'Slide From Right','product-blocks' ),
                                'flip_right_to_left' => __( 'Flip Right to Left','product-blocks' ),
                            ),
                            'default' => 'fade_in',
                            'desc' => __("Select Option For What You Want to Show on Product After Mouse Hover.", 'product-blocks')
                        ),

                        'flip_group_variable_disable' => array(
                            'type' => 'toggle',
                            'label' => __('Disable For Group & Variable Product', 'product-blocks'),
                            'default' => '',
                            'value' => 'yes',
                            'desc' => __("This option will disable image flipper to group & variable products.", 'product-blocks')
                        ),
                        'flip_mobile_device_disable' => array(
                            'type' => 'toggle',
                            'label' => __('Disable For Mobile Device', 'product-blocks'),
                            'default' => '',
                            'value' => 'yes',
                            'desc' => __("This option will disable image flipper to mobile devices.", 'product-blocks')
                        ),
                    ),
                )
            )
        )
    );

    return array_merge($config, $arr);
}   