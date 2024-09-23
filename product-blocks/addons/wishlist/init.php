<?php
defined( 'ABSPATH' ) || exit;

/**
 * Wishlist Addons Initial Configuration
 * @since v.1.1.0
 */
add_filter('wopb_addons_config', 'wopb_wishlist_config');
function wopb_wishlist_config( $config ) {
	$configuration = array(
		'name' => __( 'Wishlist', 'product-blocks' ),
        'desc' => __( 'It allows your shoppers to add their desired product to a list that they are willing to purchase later from the wishlist page.', 'product-blocks' ),
        'img' => WOPB_URL.'/assets/img/addons/wishlist.svg',
        'is_pro' => false,
        'live' => 'https://www.wpxpo.com/productx/addons/wishlist/live_demo_args',
        'docs' => 'https://wpxpo.com/docs/productx/add-ons/wishlist-settings/addon_doc_args',
        'video' => '',
        'type' => 'build',
        'priority' => 10
	);
	$config['wopb_wishlist'] = $configuration;
	return $config;
}

/**
 * Require Main File
 * @since v.1.1.0
 */
add_action('wp_loaded', 'wopb_wishlist_init');
function wopb_wishlist_init(){
	$settings = wopb_function()->get_setting();
	if ( isset($settings['wopb_wishlist']) ) {
		if ($settings['wopb_wishlist'] == 'true') {
			require_once WOPB_PATH . '/addons/wishlist/Wishlist.php';
			$obj = new \WOPB\Wishlist();
			if( !isset($settings['wishlist_heading']) ){
				$obj->initial_setup();
			}
		}
	}

	add_filter('wopb_settings', 'get_wishlist_settings', 10, 1);
}

/**
 * Wishlist Addons Default Settings Param
 *
 * @since v.1.1.0
 * @param ARRAY | Default Filter Congiguration
 * @return ARRAY
 */
function get_wishlist_settings($config){
    $arr = array(
        'wopb_wishlist' => array(
            'label' => __('Wishlist', 'product-blocks'),
            'attr' => array(
                'wishlist_heading' => array(
                    'type' => 'heading',
                    'label' => __('Wishlist Settings', 'product-blocks'),
                ),
                'wishlist_page' => array(
                    'type' => 'select',
                    'label' => __('Wishlist Page', 'product-blocks'),
                    'options' => wopb_function()->all_pages(true),
                    'desc' => '[wopb_wishlist] '.__('Use shortcode inside wishlist page.', 'product-blocks')
                ),
                'wishlist_require_login' => array(
                    'type' => 'switch',
                    'label' => __('Enable / Disable', 'product-blocks'),
                    'default' => '',
                    'pro' => true,
                    'desc' => __('Require Login for Wishlist.', 'product-blocks')
                ),
                'wishlist_empty' => array(
                    'type' => 'switch',
                    'label' => __('Enable / Disable', 'product-blocks'),
                    'default' => '',
                    'pro' => true,
                    'desc' => __('Empty Wishlist After All Add To Cart.', 'product-blocks')
                ),
                'wishlist_redirect_cart' => array(
                    'type' => 'switch',
                    'label' => __('Enable / Disable', 'product-blocks'),
                    'default' => '',
                    // 'pro' => false,
                    'desc' => __('Redirect to Cart.', 'product-blocks')
                ),
                'wishlist_button' => array(
                    'type' => 'text',
                    'label' => __('Button Text', 'product-blocks'),
                    'default' => __('Add to Wishlist', 'product-blocks')
                ),
                'wishlist_browse' => array(
                    'type' => 'text',
                    'label' => __('Browse Wishlist Text', 'product-blocks'),
                    'default' => __('Browse Wishlist', 'product-blocks')
                ),
                'wishlist_single_enable' => array(
                    'type' => 'switch',
                    'label' => __('Enable / Disable', 'product-blocks'),
                    'default' => 'yes',
                    'desc' => __('Enable Wishlist in Single Page.', 'product-blocks')
                ),
                'wishlist_shop_enable' => array(
                    'type' => 'switch',
                    'label' => __('Enable / Disable', 'product-blocks'),
                    'default' => 'no',
                    'desc' => __('Enable Wishlist in default Shop Page.', 'product-blocks')
                ),
                'wishlist_position' => array(
                    'type' => 'radio',
                    'label' => __('Position on Single Page', 'product-blocks'),
                    'options' => array(
                        'before_cart' => __( 'Before Cart','product-blocks' ),
                        'after_cart' => __( 'After Cart','product-blocks' ),
                    ),
                    'default' => 'after_cart'
                ),
                'wishlist_action' => array(
                    'type' => 'radio',
                    'label' => __('Action', 'product-blocks'),
                    'options' => array(
                        'show_wishlist' => __( 'Popup Wishlist','product-blocks' ),
                        'add_wishlist' => __( 'Added to Wishlist','product-blocks' ),
                    ),
                    'default' => 'show_wishlist'
                ),
                'wishlist_action_added' => array(
                    'type' => 'radio',
                    'label' => __('Action after Added', 'product-blocks'),
                    'options' => array(
                        'popup' => __( 'Popup','product-blocks' ),
                        'redirect' => __( 'Redirect to Page','product-blocks' ),
                    ),
                    'default' => 'popup'
                ),
                'wopb_wishlist' => array(
                    'type' => 'hidden',
                    'value' => 'true'
                )
            )
        )
    );

    return array_merge($config, $arr);
}