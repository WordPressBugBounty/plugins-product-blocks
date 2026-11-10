<?php
/**
 * Autoload the plugin's legacy class paths without loading feature bootstraps.
 *
 * Class keys are lowercase because PHP class names are case-insensitive.
 * Add new class files to this map; templates and hook registration files must
 * continue to be included explicitly at their existing lifecycle hooks.
 *
 * @package WOPB
 */

namespace WOPB;

defined( 'ABSPATH' ) || exit;

spl_autoload_register(
	static function ( $class_name ) {
		static $classes = array(
			'wopb\\animatedcart'                          => 'addons/animated_cart/AnimatedCart.php',
			'wopb\\backorder'                             => 'addons/backorder/Backorder.php',
			'wopb\\blocks'                                => 'classes/Blocks.php',
			'wopb\\blockstyles'                           => 'classes/BlockStyles.php',
			'wopb\\blocks\\archive_title'                 => 'blocks/woo/Archive_Title.php',
			'wopb\\blocks\\cart_table'                    => 'blocks/woo/Cart_Table.php',
			'wopb\\blocks\\cart_total'                    => 'blocks/woo/Cart_Total.php',
			'wopb\\blocks\\carttotalcheckoutbuttoncallback' => 'blocks/woo/Cart_Total.php',
			'wopb\\blocks\\checkout_additional_information' => 'blocks/woo/Checkout_Additional_Information.php',
			'wopb\\blocks\\checkout_billing'              => 'blocks/woo/Checkout_Billing.php',
			'wopb\\blocks\\checkout_coupon'               => 'blocks/woo/Checkout_Coupon.php',
			'wopb\\blocks\\checkout_login'                => 'blocks/woo/Checkout_Login.php',
			'wopb\\blocks\\checkout_order_review'         => 'blocks/woo/Checkout_Order_Review.php',
			'wopb\\blocks\\checkout_payment_method'       => 'blocks/woo/Checkout_Payment_Method.php',
			'wopb\\blocks\\checkout_shipping'             => 'blocks/woo/Checkout_Shipping.php',
			'wopb\\blocks\\free_shipping_progress_bar'    => 'blocks/woo/Free_Shipping_Progress_Bar.php',
			'wopb\\blocks\\heading'                       => 'blocks/Heading.php',
			'wopb\\blocks\\image'                         => 'blocks/Image.php',
			'wopb\\blocks\\menu_compare'                  => 'blocks/Menu_Compare.php',
			'wopb\\blocks\\menu_wishlist'                 => 'blocks/Menu_Wishlist.php',
			'wopb\\blocks\\my_account'                    => 'blocks/woo/My_Account.php',
			'wopb\\blocks\\order_conformation'            => 'blocks/woo/Order_Conformation.php',
			'wopb\\blocks\\order_payment'                 => 'blocks/woo/Order_Payment.php',
			'wopb\\blocks\\product_additional_info'       => 'blocks/woo/Product_Additional_Info.php',
			'wopb\\blocks\\product_breadcrumb'            => 'blocks/woo/Product_Breadcrumb.php',
			'wopb\\blocks\\product_cart'                  => 'blocks/woo/Product_Cart.php',
			'wopb\\blocks\\product_category_1'            => 'blocks/Product_Category_1.php',
			'wopb\\blocks\\product_category_2'            => 'blocks/Product_Category_2.php',
			'wopb\\blocks\\product_description'           => 'blocks/woo/Product_Description.php',
			'wopb\\blocks\\product_grid_1'                => 'blocks/Product_Grid_1.php',
			'wopb\\blocks\\product_grid_2'                => 'blocks/Product_Grid_2.php',
			'wopb\\blocks\\product_grid_3'                => 'blocks/Product_Grid_3.php',
			'wopb\\blocks\\product_image'                 => 'blocks/woo/Product_Image.php',
			'wopb\\blocks\\product_list_1'                => 'blocks/Product_List_1.php',
			'wopb\\blocks\\product_meta'                  => 'blocks/woo/Product_Meta.php',
			'wopb\\blocks\\product_price'                 => 'blocks/woo/Product_Price.php',
			'wopb\\blocks\\product_rating'                => 'blocks/woo/Product_Rating.php',
			'wopb\\blocks\\product_review'                => 'blocks/woo/Product_Review.php',
			'wopb\\blocks\\product_short'                 => 'blocks/woo/Product_Short.php',
			'wopb\\blocks\\product_slider'                => 'blocks/Product_Slider.php',
			'wopb\\blocks\\product_stock'                 => 'blocks/woo/Product_Stock.php',
			'wopb\\blocks\\product_tab'                   => 'blocks/woo/Product_Tab.php',
			'wopb\\blocks\\product_title'                 => 'blocks/woo/Product_Title.php',
			'wopb\\blocks\\productcartappendbuynowbuttoncallback' => 'blocks/woo/Product_Cart.php',
			'wopb\\blocks\\social_share'                  => 'blocks/woo/Social_Share.php',
			'wopb\\blocks\\thankyou_address'              => 'blocks/woo/Thankyou_Address.php',
			'wopb\\blocks\\thankyou_order_details'        => 'blocks/woo/Thankyou_Order_Details.php',
			'wopb\\builder'                               => 'addons/builder/Builder.php',
			'wopb\\caches'                                => 'classes/Caches.php',
			'wopb\\callforprices'                         => 'addons/call_for_price/CallForPrice.php',
			'wopb\\cartreserved'                          => 'addons/cart_reserved/CartReserved.php',
			'wopb\\carttext'                              => 'addons/add_to_cart_text/CartText.php',
			'wopb\\compare'                               => 'addons/compare/Compare.php',
			'wopb\\compatibility'                         => 'classes/Compatibility.php',
			'wopb\\compatibility\\germanizedsupport'      => 'classes/compatibility/GermanizedSupport.php',
			'wopb\\condition'                             => 'addons/builder/Condition.php',
			'wopb\\custom_font'                           => 'addons/custom_font/Custom_Font.php',
			'wopb\\dashboard'                             => 'classes/Dashboard.php',
			'wopb\\externalcompatibility'                 => 'classes/ExternalCompatibility.php',
			'wopb\\flipimage'                             => 'addons/flip_image/FlipImage.php',
			'wopb\\functions'                             => 'classes/Functions.php',
			'wopb\\includes\\notice\\notice'              => 'includes/notice/class-notice.php',
			'wopb\\includes\\xpo'                         => 'includes/class-xpo.php',
			'wopb\\initialization'                        => 'classes/Initialization.php',
			'wopb\\nameprice'                             => 'addons/name_price/NamePrice.php',
			'wopb\\options'                               => 'classes/Options.php',
			'wopb\\preorder'                              => 'addons/preorder/Preorder.php',
			'wopb\\productvideo'                          => 'addons/product_video/ProductVideo.php',
			'wopb\\quickview'                             => 'addons/quick_view/Quickview.php',
			'wopb\\quickviewrenderproductgallerycallback' => 'addons/quick_view/Quickview.php',
			'wopb\\requestapi'                            => 'addons/builder/RequestAPI.php',
			'wopb\\rest_api'                              => 'classes/REST_API.php',
			'wopb\\salesnotification'                     => 'addons/sales_notification/SalesNotification.php',
			'wopb\\saved_templates'                       => 'addons/templates/Saved_Templates.php',
			'wopb\\shortcode'                             => 'addons/templates/Shortcode.php',
			'wopb\\sizechart'                             => 'addons/size_chart/SizeChart.php',
			'wopb\\socialshare'                           => 'addons/social_share/SocialShare.php',
			'wopb\\styles'                                => 'classes/Styles.php',
			'wopb\\templates'                             => 'classes/Templates.php',
			'wopb\\titlelimit'                            => 'addons/title_limit/TitleLimit.php',
			'wopb\\variationswatches'                     => 'addons/variation_swatches/VariationSwatches.php',
			'wopb\\wishlist'                              => 'addons/wishlist/Wishlist.php',
			'wopb\\woohooks'                              => 'classes/WooHooks.php',
		);

		$class_name = strtolower( $class_name );
		if ( isset( $classes[ $class_name ] ) ) {
			require_once WOPB_PATH . $classes[ $class_name ];
		}
	}
);
