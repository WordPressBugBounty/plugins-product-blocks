<?php
/**
 * Preorder Addons Core.
 *
 * @package WOPB\Preorder
 * @since v.1.0.4
 */

namespace WOPB;

use WC_Order_Item_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Preorder class.
 */
class Preorder {


	/**
	 * Setup class.
	 *
	 * @since v.1.0.4
	 */
	public function __construct() {
		add_filter( 'product_type_options', array( $this, 'wopb_add_custom_product_type' ), 5 );

		// Pre-order field save in woocomerce product page in admin panel
		add_action( 'woocommerce_process_product_meta', array( $this, 'pre_order_woocommerce_fields_save' ), 10, 2 );

		// woocommerce add column to order table in admin panel
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_column_in_order_listing_page' ), 10, 1 );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'set_order_type_column_value' ), 10, 2 );

		// validate pre order item when add to cart
		add_filter( 'woocommerce_add_to_cart', array( $this, 'is_validate_order_item' ), 10, 1 );

		// WooCommerce cart item name
		add_filter( 'woocommerce_cart_item_name', array( $this, 'wopb_woocommerce_cart_item_name' ), 10, 4 );

		// WooCommerce order item meta data
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( $this, 'woocommerce_re_format_order_item_meta' ), 10, 4 );

		// WooCommerce checkout order line meta modify
		add_filter( 'woocommerce_checkout_create_order_line_item', array( $this, 'wopb_add_pre_order_meta_to_item' ), 10, 4 );

		// Filter hook for change add to cart button text in single product
		add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'add_to_cart_button_text' ), 10, 2 );
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'add_to_cart_button_text' ), 10, 2 );

		// WowStore tab in woocomerce product page in admin panel
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'wopb_wowstore_tab_data' ), 10, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'wopb_wowstore_custom_field_simple' ) );

		// Add Script for Addons
		add_action( 'wp_enqueue_scripts', array( $this, 'add_preorder_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_preorder_scripts' ) );

		// Before Single Product
		add_action( 'woocommerce_before_single_product', array( $this, 'before_single_product' ) );
		add_action( 'woocommerce_before_single_product', array( $this, 'before_single_product' ) );

		add_action( 'wopb_save_settings', array( $this, 'generate_css' ), 10, 1 ); // CSS Generator
	}

	/**
	 * Auto Convert Product Pre-order to Simple
	 *
	 * @since v.1.0.4
	 * @return NULL
	 */
	public function before_single_product( $admin_product = null ) {
		global $product;
		if (
			! $product->is_type( 'grouped' ) &&
			$product->is_purchasable()
		) {
			if ( ! wopb_function()->is_builder() ) {
				add_filter(
					'woocommerce_get_price_html',
					array( $this, 'filter_preorder_price' ),
					50,
					2
				);
			} else {
				add_filter(
					'wopb_after_single_product_price',
					array( $this, 'append_preorder_price' ),
					50,
					2
				);
			}
		}
	}

	/**
	 * Pre-Order JS & CSS Script
	 *
	 * @since v.1.0.4
	 * @return NULL
	 */
	public function add_preorder_scripts() {
		wp_enqueue_style( 'wopb-preorder-style', WOPB_URL . 'addons/preorder/css/preorder.css', array(), WOPB_VER );
		wp_enqueue_script( 'wopb-preorder-script', WOPB_URL . 'addons/preorder/js/preorder.js', array( 'jquery' ), WOPB_VER, true );
	}

	/**
	 * Pre-Order Custom Field
	 *
	 * @since v.1.0.4
	 * @return NULL
	 */
	public function wopb_wowstore_custom_field_simple() {
		$html = '';
		global $post;
		$html                 .= '<div class="panel woocommerce_options_panel hidden" id="productx_tab_data">';
			$html             .= '<div class="wopb-productx-options-tab-wrap">';
				$html         .= '<div class="wopb-woocommerce-preorder-field-group" id="wopb-woocommerce-preorder-field-group">';
					$html     .= $this->generate_field( $post->ID, '' );
				$html         .= '</div>';
				$html         .= '<div id="wopb-preorder-select-instruction">';
					$html     .= '<h2 class="wopb-preorder-select-instruction-title">';
						$html .= '<span class="dashicons dashicons-warning"></span>';
						$html .= __( 'For giving pre-order information please select Pre-Order Checkbox', 'product-blocks' );
					$html     .= '</h2>';
				$html         .= '</div>';
			$html             .= '</div>';
		$html                 .= '</div>';

		echo wopb_function()->wp_kses_safe( $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Productx tab in woocomerce product page in admin panel
	 *
	 * @return ARRAY
	 * @since v.1.0.4
	 */
	public function wopb_wowstore_tab_data( $product_data_tabs ) {
		$product_data_tabs['productx'] = array(
			'label'    => __( 'Preorder', 'product-blocks' ),
			'class'    => array( 'show_if_simple', 'hidden', 'wopb_product_tab' ),
			'target'   => 'productx_tab_data',
			'priority' => 15.1,
		);
		return $product_data_tabs;
	}

	/**
	 * Pre Order Option Added
	 *
	 * @return ARRAY
	 * @since v.1.0.4
	 */
	public function wopb_add_custom_product_type( $product_type_options ) {
		$product_type_options['wopb_preorder_simple'] = array(
			'id'            => '_wopb_preorder_simple',
			'wrapper_class' => 'show_if_simple wopb_preorder_simple',
			'label'         => __( 'Pre-Order', 'product-blocks' ),
			'description'   => __( 'If you want to set pre-order click here', 'product-blocks' ),
			'default'       => 'no',
		);
		return $product_type_options;
	}

	/**
	 * Change Add to Cart Button Text
	 *
	 * @return NULL
	 * @since v.1.0.4
	 */
	public function add_to_cart_button_text( $text, $product ) {
		if (
			$this->is_simple_preorder( $product ) &&
			wopb_function()->get_setting( 'preorder_add_to_cart_button_text' ) &&
			$product->is_purchasable()
		) {
			return wopb_function()->get_setting( 'preorder_add_to_cart_button_text' );
		} else {
			return $text;
		}
	}


	/**
	 * Preorder Addons Intitial Setup Action
	 *
	 * @return NULL
	 * @since v.1.0.4
	 */
	public function initial_setup() {
		$settings     = wopb_function()->get_setting();
		$initial_data = array(
			'preorder_heading'                 => 'yes',
			'preorder_button_text'             => __( 'Pre-order', 'product-blocks' ),
			'preorder_add_to_cart_button_text' => __( 'Pre-order Now', 'product-blocks' ),
			'preorder_coming_soon_text'        => 'Coming Soon',

			'preorder_available_typo'          => array(
				'size'        => 16,
				'bold'        => true,
				'italic'      => true,
				'underline'   => false,
				'color'       => '#398e29',
				'hover_color' => '',
			),
			'preorder_remain_typo'             => array(
				'size'        => 16,
				'bold'        => false,
				'italic'      => false,
				'underline'   => false,
				'color'       => '',
				'hover_color' => '',
			),
			'preorder_count_typo'              => array(
				'size'        => 16,
				'bold'        => true,
				'italic'      => false,
				'underline'   => false,
				'color'       => '#333333',
				'hover_color' => '',
			),
			'preorder_badge_typo'              => array(
				'size'        => 12,
				'bold'        => false,
				'italic'      => false,
				'underline'   => false,
				'color'       => '#ffffff',
				'hover_color' => '',
			),
			'preorder_badge_bg'                => array(
				'bg'       => '#007cba',
				'hover_bg' => '',
			),
			'preorder_badge_padding'           => array(
				'top'    => 5,
				'bottom' => 5,
				'left'   => 5,
				'right'  => 5,
			),
			'preorder_badge_border'            => array(
				'border' => 0,
				'color'  => '',
			),
			'preorder_badge_radius'            => 0,
		);
		foreach ( $initial_data as $key => $val ) {
			if ( ! isset( $settings[ $key ] ) ) {
				wopb_function()->set_setting( $key, $val );
			}
		}
		$this->generate_css( 'wopb_preorder' );
	}


	/**
	 * Save input field when product save in admin panel
	 *
	 * @param INT | Product ID
	 * @return NULL
	 * @since v.1.0.4
	 */
	public function pre_order_woocommerce_fields_save( $post_id ) {
		$product = wc_get_product( $post_id );

		// Nonce already verified by WC_Meta_Box_Product_Data::save() before this hook fires.
		if ( isset( $_POST['_wopb_preorder_simple'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$product->update_meta_data( '_wopb_preorder_simple', 'yes' );
			$product->update_meta_data( '_wopb_max_preorder', isset( $_POST['_wopb_max_preorder'] ) ? sanitize_text_field( wp_unslash( $_POST['_wopb_max_preorder'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$product->update_meta_data( '_wopb_preorder_coming_soon', isset( $_POST['_wopb_preorder_coming_soon'] ) ? sanitize_text_field( wp_unslash( $_POST['_wopb_preorder_coming_soon'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			/**
			 * Fires before a simple product set to pre-order is saved, so more pre-order data can be added.
			 *
			 * @since 4.6.0
			 * @param \WC_Product $product Simple product.
			 */
			do_action( 'wopb_preorder_saved', $product );
		} else {
			$product->update_meta_data( '_wopb_preorder_simple', '' );
		}
		$product->save();
	}

	/**
	 * Pre Order Custom Field in Variation
	 *
	 * @return NULL
	 * @since v.1.0.4
	 */
	public function generate_field( $post_id, $loop = '' ) {
		$html    = '';
		$loop    = '' !== $loop ? '[' . $loop . ']' : '';
		$product = wc_get_product( $post_id );

		$coming_soon_message         = $product->get_meta( '_wopb_preorder_coming_soon' );
		$default_coming_soon_message = wopb_function()->get_setting( 'preorder_coming_soon_text' );
		$html                       .= '<h4 class="wopb-woocommerce-preorder-title">' . __( 'WowStore Pre-order information', 'product-blocks' ) . '</h4>';
		$wopb_buffer_level_1         = ob_get_level();
		ob_start();
		try {
			woocommerce_wp_text_input(
				array(
					'id'          => '_wopb_max_preorder' . $loop,
					'class'       => 'wopb_required',
					'label'       => __( 'Available Quantity', 'product-blocks' ),
					'type'        => 'number',
					'value'       => $product->get_meta( '_wopb_max_preorder' ),
					'desc_tip'    => true,
					'description' => __( 'Enter the maximum amount of products available for pre-order', 'product-blocks' ),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'                => '_wopb_preorder_coming_soon' . $loop,
					'class'             => 'wopb_required',
					'label'             => __( 'Pre-Release Message', 'product-blocks' ),
					'type'              => 'text',
					'value'             => $coming_soon_message ? $coming_soon_message : $default_coming_soon_message,
					'desc_tip'          => true,
					'description'       => __( 'Enter a message if the pre-ordered product availability date and time are not given', 'product-blocks' ),
					'custom_attributes' => array(
						'required' => 'required',
					),
				)
			);

			/**
			 * Fires in the pre-order fields of a simple product, after "Pre-Release Message". Output is echoed.
			 *
			 * @since 4.6.0
			 * @param \WC_Product $product Simple product.
			 */
			do_action( 'wopb_preorder_product_fields', $product );

			$html .= ob_get_clean();
		} finally {
			while ( ob_get_level() > $wopb_buffer_level_1 ) {
				if ( ! ob_end_clean() ) {
					break;
				}
			}
		}

		return $html;
	}

	/**
	 * Show Pre-Order Single Content in Single Product
	 *
	 * @return STRING
	 * @since v.1.0.4
	 */
	public function pre_order_content( $price, $product ) {
		if ( ! is_product() ) {
			return $price;
		}
		if ( ! $product ) {
			return $price;
		}
		if ( ! $this->is_simple_preorder( $product ) ) {
			return $price;
		}

		// pre order message
		$html = $this->preorder_message( $product );

		// remaining qty
		$html = $this->remaining_item( $product, $html );

		/**
		 * Filters the pre-order details shown with the price on the product page.
		 *
		 * @since 4.6.0
		 * @param string      $html    Availability message and remaining count.
		 * @param \WC_Product $product Simple product set to pre-order.
		 * @param string      $price   Price markup the details are appended to.
		 */
		$html = apply_filters( 'wopb_preorder_content', $html, $product, $price );

		return "<span class='wopb-single-product-preorder'>{$price}{$html}</span>";
	}


	/**
	 * Show Pre-Order Message
	 *
	 * @return STRING
	 * @since v.1.0.4
	 */
	private function preorder_message( $product ) {
		$html = '';

		/**
		 * Filters the availability message of a pre-order product.
		 *
		 * @since 4.6.0
		 * @param string      $message Message markup, '' by default.
		 * @param \WC_Product $product Product set to pre-order.
		 * @param string      $context 'product' (product page) or 'cart'.
		 */
		$message = apply_filters( 'wopb_preorder_available_message', '', $product, 'product' );

		if ( $message ) {
			$html .= '<span class="wopb-singlepage-preorder-message">' . $message . '</span>';
		} else {
			$html     .= '<span class="wopb-singlepage-no-date">';
				$html .= '<span class="wopb-no-date-message">' . $product->get_meta( '_wopb_preorder_coming_soon' ) . '</span>';
			$html     .= '</span>';
		}
		return $html;
	}


	/**
	 * Show pre order remining item in single product  details
	 *
	 * @return STRING
	 * @since v.1.0.4
	 */
	private function remaining_item( $product, $html ) {
		$remaining_items = $this->remaining_item_count( $product );
		if ( $remaining_items ) {
			$html     .= '<span class="wopb-singlepage-preorder-remaining-item">';
				$html .= '<span class="wopb-preorder-remaining-label">' . __( 'Remaining Item only: ', 'product-blocks' ) . '</span>';
				$html .= '<span class="wopb-preorder-remaining-count">' . $remaining_items . '</span>';
			$html     .= '</span>';
		}
		return $html;
	}


	/**
	 * Show Pre-Order Remaining Item Count
	 *
	 * @return STRING
	 * @since v.1.0.4
	 */
	public function remaining_item_count( $product ) {
		$allow_max_preorder = $product->get_meta( '_wopb_max_preorder' );
		$booked_order_count = $this->get_total_product_order_by_meta( $product->get_Id() );
		$remaining_items    = intval( $allow_max_preorder ) - intval( $booked_order_count );
		return $remaining_items;
	}


	/**
	 * Show Pre-order Label in Cart
	 *
	 * @return STRING
	 * @since v.1.0.4
	 */
	public function wopb_woocommerce_cart_item_name( $name, $cart_item, $cart_item_key ) {
		$product = $cart_item['data'];
		if ( $this->is_simple_preorder( $product ) ) {
			/** This filter is documented in addons/preorder/Preorder.php */
			$message = apply_filters( 'wopb_preorder_available_message', '', $product, 'cart' );

			$pre_order_content = '<span class="wopb-cart-preorder-badge">' . wopb_function()->get_setting( 'preorder_button_text' ) . '</span>';
			if ( $message ) {
				$pre_order_content .= '<div class="wopb-cart-preorder-message">' . $message . '</div>';
			}
			return $name . $pre_order_content;
		}
		return $name;
	}


	/**
	 * Add Meta When Checkout Order for Pre-Order
	 *
	 * @return STRING
	 * @since v.1.0.4
	 */
	public function wopb_add_pre_order_meta_to_item( WC_Order_Item_Product $item, $cart_item_key, $values, $order ) {
		$cart_item = WC()->cart->get_cart()[ $cart_item_key ];
		if ( $this->is_simple_preorder( $cart_item['data'] ) ) {
			$item->update_meta_data( 'wopb_pre_order_item', 'yes' );
		}
	}


	/**
	 * Cart Page Pre Order Label Add
	 *
	 * @return HTML
	 * @since v.1.0.4
	 */
	public function woocommerce_re_format_order_item_meta( $formatted_meta, $item ) {
		foreach ( $formatted_meta as $key => $meta ) {
			if ( 'wopb_pre_order_item' === $meta->key ) {
				$meta->display_key = '<span class="wopb-cart-preorder-badge">' . wopb_function()->get_setting( 'preorder_button_text' ) . '</span>';
			}
		}
		return $formatted_meta;
	}


	/**
	 * Inserting "Order Type" Column Before Last Elements
	 *
	 * @return ARRAY
	 * @since v.1.0.4
	 */
	public function add_column_in_order_listing_page( $columns ) {
		$reordered_columns = array();
		foreach ( $columns as $key => $column ) {
			$reordered_columns[ $key ] = $column;
			if ( 'order_status' === $key ) {
				$reordered_columns['wopb_order_page_order_type'] = __( 'Order Type', 'product-blocks' );
			}
		}
		return $reordered_columns;
	}


	/**
	 * Show Pre-Order Label to Order Table Column
	 *
	 * @return STRING
	 * @since v.1.0.4
	 */
	public function set_order_type_column_value( $column ) {
		global $the_order;
		if ( 'wopb_order_page_order_type' === $column ) {
			$has_item = false;
			$items    = $the_order->get_items();
			foreach ( $the_order->get_items() as $item ) {
				if ( $item->get_meta( 'wopb_pre_order_item' ) === 'yes' ) {
					$has_item = true;
				}
			}
			if ( $has_item ) {
				echo '<span class="wopb-cart-preorder-badge">' . esc_html( wopb_function()->get_setting( 'preorder_button_text' ) ) . '</span>';
			}
		}
	}


	/**
	 * Get Total Order Number
	 *
	 * @return STRING
	 * @since v.1.0.4
	 */
	public function get_total_product_order_by_meta( $product_id ) {
		global $wpdb;
		$product_id = absint( $product_id );
		// Live order totals used to enforce pre-order limits; not practical to cache.
		$result = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"
                SELECT sum(order_product.product_qty) as total_order
                FROM {$wpdb->prefix}wc_order_product_lookup as order_product
                INNER JOIN {$wpdb->prefix}wc_order_stats AS order_stat
                    ON order_product.order_id = order_stat.order_id
                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta
                    ON order_product.order_item_id = order_item_meta.order_item_id AND order_item_meta.meta_key = 'wopb_pre_order_item'
                WHERE order_product.product_id = %d
                    AND order_stat.status NOT IN ('wc-cancelled', 'wc-refunded')
            ",
				$product_id
			)
		);
		return $result[0]->total_order;
	}


	public function is_simple_preorder( $product ) {
		if ( $product && $product->get_meta( '_wopb_preorder_simple' ) === 'yes' ) {
			return true;
		} else {
			return false;
		}
	}

	public function is_validate_order_item( $cart_item_key ) {
		$cart_item = WC()->cart->get_cart()[ $cart_item_key ];
		$product   = $cart_item['data'];

		if ( $this->is_simple_preorder( $product ) && $product->get_meta( '_wopb_max_preorder' ) && $cart_item['quantity'] > $this->remaining_item_count( $product ) ) {
			throw new \Exception( esc_html__( 'Quantity Exceeded for Pre-Order Item.', 'product-blocks' ) );
		}
	}

	/**
	 * Dynamic CSS
	 *
	 * @param $key
	 * @return void
	 * @since v.4.0.0
	 */
	public function generate_css( $key ) {
		if ( 'wopb_preorder' === $key && method_exists( wopb_function(), 'convert_css' ) ) {
			$settings    = wopb_function()->get_setting();
			$badge_style = array_merge( $settings['preorder_badge_typo'], $settings['preorder_badge_bg'] );

			$css      = '.wopb-single-product-preorder .wopb-no-date-message{';
				$css .= wopb_function()->convert_css( 'general', $settings['preorder_available_typo'] );
			$css     .= '}';
			$css     .= '.wopb-single-product-preorder .wopb-no-date-message:hover{';
				$css .= wopb_function()->convert_css( 'hover', $settings['preorder_available_typo'] );
			$css     .= '}';

			$css     .= '.wopb-preorder-remaining-label{';
				$css .= wopb_function()->convert_css( 'general', $settings['preorder_remain_typo'] );
			$css     .= '}';
			$css     .= '.wopb-preorder-remaining-label:hover{';
				$css .= wopb_function()->convert_css( 'hover', $settings['preorder_remain_typo'] );
			$css     .= '}';

			$css     .= '.wopb-preorder-remaining-count{';
				$css .= wopb_function()->convert_css( 'general', $settings['preorder_count_typo'] );
			$css     .= '}';
			$css     .= '.wopb-preorder-remaining-count:hover{';
				$css .= wopb_function()->convert_css( 'hover', $settings['preorder_count_typo'] );
			$css     .= '}';

			$css     .= '.wopb-cart-preorder-badge{';
				$css .= wopb_function()->convert_css( 'general', $badge_style );
				$css .= wopb_function()->convert_css( 'border', $settings['preorder_badge_border'] );
				$css .= wopb_function()->convert_css( 'radius', $settings['preorder_badge_radius'] );
				$css .= 'padding: ' . wopb_function()->convert_css( 'dimension', $settings['preorder_badge_padding'] ) . ';';
			$css     .= '}';
			$css     .= '.wopb-cart-preorder-badge:hover{';
				$css .= wopb_function()->convert_css( 'hover', $badge_style );
			$css     .= '}';

			wopb_function()->update_css( $key, 'add', $css );
		}
	}

	/** Named WordPress hook callback. */
	public function filter_preorder_price( $price, $product ) {
		return $this->pre_order_content( $price, $product );
	}

	/** Named WordPress hook callback. */
	public function append_preorder_price( $price, $product ) {
		return $this->pre_order_content( $price, $product );
	}
}
