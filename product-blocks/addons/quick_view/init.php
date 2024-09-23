<?php
defined( 'ABSPATH' ) || exit;

/**
 * Quickview Addons Initial Configuration
 * @since v.1.1.0
 */
add_filter('wopb_addons_config', 'wopb_quickview_config');
function wopb_quickview_config( $config ) {
	$configuration = array(
		'name' => __( 'Quick View', 'product-blocks' ),
        'desc' => __( 'It allows your store’s visitors to check out the product details in a pop-up instead of visiting the product pages.', 'product-blocks' ),
        'img' => WOPB_URL.'/assets/img/addons/quickview.svg',
        'is_pro' => false,
        'live' => 'https://www.wpxpo.com/productx/addons/quick-view/live_demo_args',
        'docs' => 'https://wpxpo.com/docs/productx/add-ons/quickview-settings/addon_doc_args',
        'video' => '',
        'live_preview' => true,
        'type' => 'build',
        'priority' => 15
	);
	$config['wopb_quickview'] = $configuration;
	return $config;
}

/**
 * Require Main File
 * @since v.1.1.0
 */
add_action('wp_loaded', 'wopb_quickview_init');
function wopb_quickview_init(){
	$settings = wopb_function()->get_setting();
	if ( isset($settings['wopb_quickview']) ) {
		if ($settings['wopb_quickview'] == 'true') {
			require_once WOPB_PATH.'/addons/quick_view/Quickview.php';
			$obj = new \WOPB\Quickview();
			if( !isset($settings['quickview_heading']) ){
				$obj->initial_setup();
			}
		}
	}

	add_filter('wopb_settings', 'wopb_get_quickview_settings', 10, 1);
}

/**
 * Quickview Addons Default Settings Param
 *
 * @param ARRAY | Default Filter Congiguration
 * @return ARRAY
 * @since v.1.1.0
 */
function wopb_get_quickview_settings($config)
{
    require_once WOPB_PATH.'/addons/quick_view/Quickview.php';
    $quick_view = new \WOPB\Quickview();
    $default_settings = $quick_view->default_settings();
    $arr = array(
        'wopb_quickview' => array(
            'label' => __('QuickView', 'product-blocks'),
            'attr' => array(
                'quickview_heading' => array(
                    'type' => 'heading',
                    'label' => __('Quick View Settings', 'product-blocks'),
                ),
                'wopb_quickview' => array(
                    'type' => 'hidden',
                    'value' => 'true'
                ),
                'tab' => (object)array(
                    'type'  => 'tab',
                    'options'  => array(
                        'settings' => (object)array(
                            'label' => __('Settings', 'product-blocks'),
                            'attr' => array(
                                'quick_view_basic_settings' => array(
                                    'type' => 'section',
                                    'label' => __('Basic Settings', 'product-blocks'),
                                    'attr' => array(
                                        'quick_view_mobile_enable' => $default_settings['quick_view_mobile_enable'],
                                        'quick_view_shop_enable' => $default_settings['quick_view_shop_enable'],
                                        'quick_view_archive_enable' => $default_settings['quick_view_archive_enable'],
                                        'quick_view_click_action' => $default_settings['quick_view_click_action'],
                                        'quick_view_loader_container' => (object)[
                                            'type'=> 'container',
                                            'attr' => [
                                                'quick_view_loader' => $default_settings['quick_view_loader'],
                                                'quick_view_open_animation' => $default_settings['quick_view_open_animation'],
                                                'quick_view_close_animation' => $default_settings['quick_view_close_animation'],
                                            ]

                                        ],
                                    ),
                                ),
                                'quick_view_button_settings' => array(
                                    'type' => 'section',
                                    'label' => __('Quick View Button Settings', 'product-blocks'),
                                    'attr' => array(
                                        'quick_view_button_type' => $default_settings['quick_view_button_type'],
                                        'quick_view_text' => $default_settings['quick_view_text'],
                                        'quick_view_button_icon_enable' => $default_settings['quick_view_button_icon_enable'],
                                        'quick_view_button_only_icon' => $default_settings['quick_view_button_only_icon'],
                                        'quick_view_icon_container' => (object)[
                                            'type'=> 'container',
                                            'depends' => ['key' =>'quick_view_button_icon_enable', 'condition' => '==', 'value' => 'yes'],
                                            'attr' => [
                                                'quick_view_button_icon' => $default_settings['quick_view_button_icon'],
                                                'quick_view_button_icon_position' => $default_settings['quick_view_button_icon_position'],
                                            ]

                                        ],
                                        'quick_view_position' => $default_settings['quick_view_position'],
                                    ),
                                ),
                                'quick_view_modal_settings' => array(
                                    'type' => 'section',
                                    'label' => __('Quick View Modal Box Settings', 'product-blocks'),
                                    'attr' => array(
                                        'quick_view_contents' => $default_settings['quick_view_contents'],
                                        'quick_view_thumbnail_container' => (object)[
                                            'type'=> 'container',
                                            'attr' => [
                                                'quick_view_image_type' => $default_settings['quick_view_image_type'],
                                                'quick_view_image_gallery' => $default_settings['quick_view_image_gallery'],
                                                'quick_view_image_pagination' => $default_settings['quick_view_image_pagination'],
                                            ]

                                        ],
                                        'quick_view_image_effect_container' => (object)[
                                            'type'=> 'container',
                                            'attr' => [
                                                'quick_view_image_effect' => $default_settings['quick_view_image_effect'],
                                                'quick_view_image_effect_type' => $default_settings['quick_view_image_effect_type'],
                                                'quick_view_image_hover_icon' => $default_settings['quick_view_image_hover_icon'],
                                            ]

                                        ],
                                        'quick_view_buy_now' => $default_settings['quick_view_buy_now'],
                                        'quick_view_thumbnail_freeze' => $default_settings['quick_view_thumbnail_freeze'],
                                        'quick_view_product_navigation' => $default_settings['quick_view_product_navigation'],
                                        'quick_view_close_button' => $default_settings['quick_view_close_button'],
                                        'quick_view_close_outside_click' => $default_settings['quick_view_close_outside_click'],
                                        'quick_view_close_add_to_cart' => $default_settings['quick_view_close_add_to_cart'],
                                    ),
                                ),
                            ),
                        ),
                        'design' => (object)array(
                            'label' => __('Design', 'product-blocks'),
                            'attr' => array(
                                'quick_view_designs' => array(
                                    'type' => 'section',
                                    'label' => __('Designs', 'product-blocks'),
                                    'attr' => array(
                                        'quick_view_layout' => $default_settings['quick_view_layout'],
                                        'quick_view_preset' => $default_settings['quick_view_preset'],
                                        'quick_view_color' => array(
                                            'type' => 'color',
                                            'label' => __('Color', 'product-blocks'),
                                            'fields' => [
                                                'quick_view_modal_background' => $default_settings['quick_view_modal_background'],
                                                'quick_view_title_color' => $default_settings['quick_view_title_color'],
                                                'quick_view_border_color' => $default_settings['quick_view_border_color'],
                                                'quick_view_text_color' => $default_settings['quick_view_text_color'],
                                                'quick_view_button_background' => $default_settings['quick_view_button_background'],
                                                'quick_view_button_color' => $default_settings['quick_view_button_color'],
                                                'quick_view_link_color' => $default_settings['quick_view_link_color'],
                                                'quick_view_button_hover_color' => $default_settings['quick_view_button_hover_color'],
                                            ]
                                        ),
                                        'quick_view_typography' => (object)[
                                            'type'=> 'container',
                                            'label'=> __('Typography', 'product-blocks'),
                                            'group' => [
                                                'quick_view_title_typo' => (object)[
                                                    'type'=> 'container',
                                                    'label'=> __('Title', 'product-blocks'),
                                                    'fields' => [
                                                        'quick_view_title_font_size' => $default_settings['quick_view_title_font_size'],
                                                        'quick_view_title_font_weight' => $default_settings['quick_view_title_font_weight'],
                                                        'quick_view_title_font_case' => $default_settings['quick_view_title_font_case'],
                                                    ]
                                                ],
                                                'quick_view_button_typo' => (object)[
                                                    'type'=> 'container',
                                                    'label'=> __('Button', 'product-blocks'),
                                                    'fields' => [
                                                        'quick_view_button_font_size' => $default_settings['quick_view_button_font_size'],
                                                        'quick_view_button_font_weight' => $default_settings['quick_view_button_font_weight'],
                                                        'quick_view_button_font_case' => $default_settings['quick_view_button_font_case'],
                                                    ]
                                                ],
                                                'quick_view_text_typo' => (object)[
                                                    'type'=> 'container',
                                                    'label'=> __('Text', 'product-blocks'),
                                                    'fields' => [
                                                        'quick_view_text_font_size' => $default_settings['quick_view_text_font_size'],
                                                        'quick_view_text_font_weight' => $default_settings['quick_view_text_font_weight'],
                                                    ]
                                                ],
                                            ]
                                        ],
                                        'quick_view_layout_style' => (object)[
                                            'type'=> 'container',
                                            'label'=> __('Layout', 'product-blocks'),
                                            'group' => [
                                                'quick_view_layout_button' => (object)[
                                                    'type'=> 'container',
                                                    'label'=> __('Button', 'product-blocks'),
                                                    'fields' => [
                                                        'quick_view_button_padding_y' => $default_settings['quick_view_button_padding_y'],
                                                        'quick_view_button_padding_x' => $default_settings['quick_view_button_padding_x'],
                                                        'quick_view_button_radius' => $default_settings['quick_view_button_radius'],
                                                    ]
                                                ],
                                                'quick_view_layout_content_gap' => (object)[
                                                    'type'=> 'container',
                                                    'label'=> __('Modal Content Column Gap', 'product-blocks'),
                                                    'fields' => [
                                                        'quick_view_content_inner_gap' => $default_settings['quick_view_content_inner_gap'],
                                                    ]
                                                ],
                                                'quick_view_layout_border_width' => (object)[
                                                    'type'=> 'container',
                                                    'label'=> __('Border', 'product-blocks'),
                                                    'fields' => [
                                                        'quick_view_border_width' => $default_settings['quick_view_border_width'],
                                                    ]
                                                ],
                                                'quick_view_layout_thumbnail_size' => (object)[
                                                    'type'=> 'container',
                                                    'label'=> __('Product Thumbnail Size', 'product-blocks'),
                                                    'fields' => [
                                                        'quick_view_thumbnail_ratio' => $default_settings['quick_view_thumbnail_ratio'],
                                                    ]
                                                ],
                                            ]
                                        ],
                                    ),
                                ),
                            )
                        )
                    ),
                ),
            ),
            'css' => $quick_view->get_css()
        )
    );

    return array_merge($config, $arr);
}