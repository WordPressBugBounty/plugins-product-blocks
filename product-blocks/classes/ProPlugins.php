<?php
/**
 * Initial Setup.
 *
 * @package WOPB\Notice
 * @since v.2.4.4
 */
namespace WOPB;

defined( 'ABSPATH' ) || exit;

class ProPlugins {

	public function __construct() {
        $this->pro_addons_data();
	}

	/**
	 * Admin Init
	 *  * @since 3.0.0
	 * @return NULL
	 */
	public function pro_addons_data() {
        if ( ! wopb_function()->isPro() && wopb_function()->get_screen() == 'wopb-settings' ) {
            return add_filter( 'wopb_addons_config', function( $arr ) {
                $pro_addons = array(
                    'wopb_backorder' => array(
                        'name' => __( 'Backorder', 'product-blocks' ),
                        'desc' => __( 'Keep getting orders for the products that are currently out of stock and will be restocked soon.', 'product-blocks' ),
                        'is_pro' => true,
                        'live' => 'https://www.wpxpo.com/wowstore/woocommerce-backorder/live_demo_args',
                        'docs' => 'https://wpxpo.com/docs/wowstore/add-ons/back-order-addon/addon_doc_args',
                        'type' => 'sales',
                        'priority' => 40
                    ),
                    'wopb_call_for_price' => array(
                        'name' => __( 'Call for Price', 'product-blocks' ),
                        'desc' => __( "Display a calling button instead of the Add to Cart button for the products that don't have prices.", 'product-blocks' ),
                        'is_pro' => true,
                        'live' => 'https://www.wpxpo.com/wowstore/woocommerce-call-for-price/live_demo_args',
                        'docs' => 'https://wpxpo.com/docs/wowstore/add-ons/call-for-price-addon/addon_doc_args',
                        'type' => 'sales',
                        'priority' => 30
                    ),
                    'wopb_currency_switcher' => array(
                        'name' => __( 'Currency Switcher', 'product-blocks' ),
                        'desc' => __( 'It allows customers to switch product prices and make payments in their local currencies.', 'product-blocks' ),
                        'is_pro' => true,
                        'live' => 'https://www.wpxpo.com/wowstore/woocommerce-currency-switcher/live_demo_args',
                        'docs' => 'https://wpxpo.com/docs/wowstore/add-ons/currency-switcher-addon/addon_doc_args',
                        'type' => 'sales',
                        'priority' => 60
                    ),
                    'wopb_partial_payment' => array(
                        'name' => __( 'Partial Payment', 'product-blocks' ),
                        'desc' => __( 'Split product prices into parts and let the customers place orders by paying only a deposit amount.', 'product-blocks' ),
                        'is_pro' => true,
                        'live' => 'https://www.wpxpo.com/wowstore/woocommerce-partial-payment/live_demo_args',
                        'docs' => 'https://wpxpo.com/docs/wowstore/add-ons/partial-payment/addon_doc_args',
                        'type' => 'sales',
                        'priority' => 70
                    ),
                    'wopb_preorder' => array(
                        'name' => __( 'Pre-Orders', 'product-blocks' ),
                        'desc' => __( 'Display upcoming products as regular products to get orders for those not released yet.', 'product-blocks' ),
                        'is_pro' => true,
                        'live' => 'https://www.wpxpo.com/wowstore/woocommerce-pre-order/live_demo_args',
                        'docs' => 'https://wpxpo.com/docs/wowstore/add-ons/pre-order-addon/addon_doc_args',
                        'type' => 'sales',
                        'priority' => 50
                    ),
                    'wopb_stock_progress_bar' => array(
                        'name' => __( 'Stock Progress Bar', 'product-blocks' ),
                        'desc' => __( 'Visually highlight the total and remaining stocks of products to encourage shoppers to create FOMO.', 'product-blocks' ),
                        'is_pro' => true,
                        'live' => 'https://www.wpxpo.com/wowstore/woocommerce-stock-progress-bar/live_demo_args',
                        'docs' => 'https://wpxpo.com/docs/wowstore/add-ons/stock-progress-bar-addon/addon_doc_args',
                        'type' => 'sales',
                        'priority' => 80
                    ),
                    'wopb_cart_reserved' => array(
                        'name' => __( 'Cart Reserved Timer', 'product-blocks' ),
                        'desc' => __( 'Display a countdown timer and show a FOMO message once someone adds products to the cart.', 'product-blocks' ),
                        'is_pro' => true,
                        'live' => 'https://www.wpxpo.com/wowstore/woocommerce-cart-reserved-timer/live_demo_args',
                        'docs' => 'https://wpxpo.com/docs/wowstore/add-ons/cart-reserved-timer/addon_doc_args',
                        'type' => 'checkout_cart',
                        'priority' => 10
                    ),
                    'wopb_product_video' => array(
                        'name' => __( 'Product Video', 'product-blocks' ),
                        'desc' => __( "Display product-featured videos instead of featured images and grab users' attention to specific products.", 'product-blocks' ),
                        'is_pro' => true,
                        'live' => 'https://www.wpxpo.com/wowstore/woocommerce-product-video/live_demo_args',
                        'docs' => 'https://wpxpo.com/docs/wowstore/add-ons/product-video/addon_doc_args',
                        'type' => 'exclusive',
                        'priority' => 60
                    ),
                    'wopb_size_chart' => array(
                        'name' => __( 'Size Chart', 'product-blocks' ),
                        'desc' => __( 'Create & display size charts to help the potential buyers make better buying decisions.', 'product-blocks' ),
                        'is_pro' => true,
                        'live' => 'https://www.wpxpo.com/wowstore/woocommerce-size-chart/live_demo_args',
                        'docs' => 'https://wpxpo.com/docs/wowstore/add-ons/size-chart/addon_doc_args',
                        'type' => 'exclusive',
                        'priority' => 50
                    ),
                    'wopb_social_share' => array(
                        'name' => __( 'Quick Social Share', 'product-blocks' ),
                        'desc' => __( 'Display social share icons and let your shoppers share products with their social profiles instantly.', 'product-blocks' ),
                        'is_pro' => true,
                        'live' => 'https://www.wpxpo.com/wowstore/woocommerce-social-share/live_demo_args',
                        'docs' => 'https://wpxpo.com/docs/wowstore/add-ons/quick-social-share/addon_doc_args',
                        'type' => 'exclusive',
                        'priority' => 20
                    ),
                    'wopb_sticky_cart' => array(
                        'name' => __( 'Sticky Add to Cart', 'product-blocks' ),
                        'desc' => __( 'Make the Add to Cart Button Sticky on the top or bottom while shoppers scroll the product pages.', 'product-blocks' ),
                        'is_pro' => true,
                        'live' => 'https://www.wpxpo.com/wowstore/woocommerce-sticky-add-to-cart/live_demo_args',
                        'docs' => 'https://wpxpo.com/docs/wowstore/add-ons/sticky-add-to-cart/addon_doc_args',
                        'type' => 'checkout_cart',
                        'priority' => 20
                    ),
                );
                return array_merge($arr, $pro_addons);
            });
        }
	}
}