<?php
defined( 'ABSPATH' ) || exit;

/**
 * Compare Addons Initial Configuration
 * @since v.1.1.0
 */
add_filter('wopb_addons_config', 'wopb_compare_config');
function wopb_compare_config( $config ) {
	$configuration = array(
		'name' => __( 'Product Compare', 'product-blocks' ),
        'desc' => __( " Let your shoppers compare multiple products by displaying a pop-up or redirecting to a compare page. It ensures better buying decisions.", 'product-blocks' ),
        'img' => WOPB_URL.'/assets/img/addons/compare.svg',
        'is_pro' => false,
        'live' => 'https://www.wpxpo.com/productx/addons/product-comparison/live_demo_args',
        'docs' => 'https://wpxpo.com/docs/productx/add-ons/compare-settings/addon_doc_args',
        'video' => '',
        'live_preview' => true,
        'type' => 'build',
        'priority' => 20
	);
	$config['wopb_compare'] = $configuration;
	return $config;
}

/**
 * Require Main File
 * @since v.1.1.0
 */
add_action('wp_loaded', 'wopb_compare_init');
function wopb_compare_init(){
	$settings = wopb_function()->get_setting();
	if ( isset($settings['wopb_compare']) ) {
		if ($settings['wopb_compare'] == 'true') {
			require_once WOPB_PATH . '/addons/compare/Compare.php';
			$obj = new \WOPB\Compare();
			if( !isset($settings['compare_heading']) ){
				$obj->initial_setup();
			}
		}
	}

	add_filter('wopb_settings', 'wopb_get_compare_settings', 10, 1);
}

/**
 * Compare Addons Default Settings Param
 *
 * @since v.1.1.0
 * @param ARRAY | Default Filter Congiguration
 * @return ARRAY
 */
function wopb_get_compare_settings($config){
    require_once WOPB_PATH . '/addons/compare/Compare.php';
    $compare = new \WOPB\Compare();
    $default_settings = $compare->default_settings();
    $arr = array(
        'wopb_compare' => array(
            'label' => __('Compare', 'product-blocks'),
            'attr' => array(
                'compare_heading' => array(
                    'type'  => 'heading',
                    'label' => __('Compare Settings', 'product-blocks'),
                ),
                'wopb_compare' => array(
                    'type' => 'hidden',
                    'value' => 'true'
                ),
                'tab' => (object)array(
                    'type'  => 'tab',
                    'options'  => array(
                        'settings' => (object)array(
                            'label' => __('Settings', 'product-blocks'),
                            'attr' => array(
                                'compare_basic_settings' => array(
                                    'type' => 'section',
                                    'label' => __('Basic Settings', 'product-blocks'),
                                    'attr' => array(
                                        'compare_page' => $default_settings['compare_page'],
                                        'compare_shop_enable' => $default_settings['compare_shop_enable'],
                                        'compare_single_enable' => $default_settings['compare_single_enable'],
                                        'compare_my_account_enable' => $default_settings['compare_my_account_enable'],
                                        'compare_action_added' => $default_settings['compare_action_added'],
                                        'compare_modal_container' => (object)[
                                            'type'=> 'container',
                                            'depends' => ['key' =>'compare_action_added', 'condition' => '==', 'value' => ['popup','sidebar']],
                                            'attr' => [
                                                'compare_modal_loading' => $default_settings['compare_modal_loading'],
                                                'compare_modal_open_animation' => $default_settings['compare_modal_open_animation'],
                                                'compare_modal_close_animation' => $default_settings['compare_modal_close_animation'],
                                            ]

                                        ],
                                        'compare_nav_container' => (object)[
                                            'type'=> 'container',
                                            'attr' => [
                                                'compare_nav_menu_enable' => $default_settings['compare_nav_menu_enable'],
                                                'compare_nav_menu_location' => $default_settings['compare_nav_menu_location'],
                                                'compare_nav_menu_type' => $default_settings['compare_nav_menu_type'],
                                                'compare_nav_icon' => $default_settings['compare_nav_icon'],
                                                'compare_nav_icon_position' => $default_settings['compare_nav_icon_position'],
                                                'compare_nav_click_action' => $default_settings['compare_nav_click_action'],
                                                'compare_nav_menu_shortcode' => $default_settings['compare_nav_menu_shortcode'],
                                            ]

                                        ],
                                        'compare_hide_empty_table' => $default_settings['compare_hide_empty_table'],
                                    ),
                                ),
                                'compare_button_settings' => array(
                                    'type' => 'section',
                                    'label' => __('Compare Button Settings', 'product-blocks'),
                                    'attr' => array(
                                        'compare_button_type' => $default_settings['compare_button_type'],
                                        'compare_text' => $default_settings['compare_text'],
                                        'compare_added_text' => $default_settings['compare_added_text'],
                                        'compare_button_icon_enable' => $default_settings['compare_button_icon_enable'],
                                        'compare_button_only_icon' => $default_settings['compare_button_only_icon'],
                                        'compare_icon_container' => (object)[
                                            'type'=> 'container',
                                            'depends' => ['key' =>'compare_button_icon_enable', 'condition' => '==', 'value' => 'yes'],
                                            'attr' => [
                                                'compare_button_icon' => $default_settings['compare_button_icon'],
                                                'compare_button_icon_position' => $default_settings['compare_button_icon_position'],
                                            ]

                                        ],
                                        'compare_position_shop_page' => $default_settings['compare_position_shop_page'],
                                        'compare_position' => $default_settings['compare_position'],
                                    ),
                                ),
                                'compare_table_settings' => array(
                                    'type' => 'section',
                                    'label' => __('Compare Table Settings', 'product-blocks'),
                                    'attr' => array(
                                        'compare_table_columns' => $default_settings['compare_table_columns'],
                                        'compare_add_product_button' => $default_settings['compare_add_product_button'],
                                        'compare_clear' => $default_settings['compare_clear'],
                                        'compare_first_column_sticky' => $default_settings['compare_first_column_sticky'],
                                        'compare_first_row_sticky' => $default_settings['compare_first_row_sticky'],
                                        'compare_close_button' => $default_settings['compare_close_button'],
                                    ),
                                ),
                            ),
                        ),
                        'design' => (object)array(
                            'label' => __('Design', 'product-blocks'),
                            'attr' => array(
                                'compare_designs' => array(
                                    'type' => 'section',
                                    'label' => __('Designs', 'product-blocks'),
                                    'attr' => array(
                                        'compare_layout' => $default_settings['compare_layout'],
                                        'compare_preset' => $default_settings['compare_preset'],
                                        'compare_color' => array(
                                            'type' => 'color',
                                            'label' => __('Color', 'product-blocks'),
                                            'fields' => [
                                                'compare_table_background' => $default_settings['compare_table_background'],
                                                'compare_table_heading_color' => $default_settings['compare_table_heading_color'],
                                                'compare_border_color' => $default_settings['compare_border_color'],
                                                'compare_odd_column_background' => $default_settings['compare_odd_column_background'],
                                                'compare_even_column_background' => $default_settings['compare_even_column_background'],
                                                'compare_text_color' => $default_settings['compare_text_color'],
                                                'compare_button_background' => $default_settings['compare_button_background'],
                                                'compare_link_color' => $default_settings['compare_link_color'],
                                                'compare_button_hover_color' => $default_settings['compare_button_hover_color'],
                                            ]
                                        ),
                                        'compare_typography' => (object)[
                                            'type'=> 'container',
                                            'label'=> __('Typography', 'product-blocks'),
                                            'group' => [
                                                'compare_heading_typo' => (object)[
                                                    'type'=> 'container',
                                                    'label'=> __('Table Heading', 'product-blocks'),
                                                    'fields' => [
                                                        'compare_heading_font_size' => $default_settings['compare_heading_font_size'],
                                                        'compare_heading_font_weight' => $default_settings['compare_heading_font_weight'],
                                                        'compare_heading_font_case' => $default_settings['compare_heading_font_case'],
                                                    ]
                                                ],
                                                'compare_button_typo' => (object)[
                                                    'type'=> 'container',
                                                    'label'=> __('Button', 'product-blocks'),
                                                    'fields' => [
                                                        'compare_button_font_size' => $default_settings['compare_button_font_size'],
                                                        'compare_button_font_weight' => $default_settings['compare_button_font_weight'],
                                                        'compare_button_font_case' => $default_settings['compare_button_font_case'],
                                                    ]
                                                ],
                                                'compare_title_typo' => (object)[
                                                    'type'=> 'container',
                                                    'label'=> __('Product Title', 'product-blocks'),
                                                    'fields' => [
                                                        'compare_title_font_size' => $default_settings['compare_title_font_size'],
                                                        'compare_title_font_weight' => $default_settings['compare_title_font_weight'],
                                                    ]
                                                ],
                                                'compare_text_typo' => (object)[
                                                    'type'=> 'container',
                                                    'label'=> __('Text', 'product-blocks'),
                                                    'fields' => [
                                                        'compare_text_font_size' => $default_settings['compare_text_font_size'],
                                                        'compare_text_font_weight' => $default_settings['compare_text_font_weight'],
                                                    ]
                                                ],
                                            ]
                                        ],
                                        'compare_layout_style' => (object)[
                                            'type'=> 'container',
                                            'label'=> __('Layout', 'product-blocks'),
                                            'group' => [
                                                'compare_layout_button' => (object)[
                                                    'type'=> 'container',
                                                    'label'=> __('Button', 'product-blocks'),
                                                    'fields' => [
                                                        'compare_layout_button_padding_y' => $default_settings['compare_layout_button_padding_y'],
                                                        'compare_layout_button_padding_x' => $default_settings['compare_layout_button_padding_x'],
                                                        'compare_layout_button_radius' => $default_settings['compare_layout_button_radius'],
                                                    ]
                                                ],
                                                'compare_layout_column' => (object)[
                                                    'type'=> 'container',
                                                    'label'=> __('Table Column', 'product-blocks'),
                                                    'fields' => [
                                                        'compare_layout_column_padding_y' => $default_settings['compare_layout_column_padding_y'],
                                                        'compare_layout_column_padding_x' => $default_settings['compare_layout_column_padding_x'],
                                                    ]
                                                ],
                                                'compare_layout_border_width' => (object)[
                                                    'type'=> 'container',
                                                    'label'=> __('Border', 'product-blocks'),
                                                    'fields' => [
                                                        'compare_layout_border_width' => $default_settings['compare_layout_border_width'],
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
            'css' => $compare->get_compare_css()
        )
    );

    return array_merge($config, $arr);
}