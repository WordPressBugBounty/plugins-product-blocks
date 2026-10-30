<?php
/**
 * Backorder Addons Core.
 *
 * @package WOPB\Backorder
 * @since v.1.0.7
 */

namespace WOPB;

use WC_Order_Item_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Backorder class.
 */
class Backorder {

	/**
	 * Setup class.
	 *
	 * @since v.1.0.7
	 */
	public function __construct() {
		// Add Script for Addons
		add_action( 'wp_enqueue_scripts', array( $this, 'add_backorder_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_backorder_scripts' ) );

		// Add Custom Field to Inventory Tab
		add_action( 'woocommerce_product_options_inventory_product_data', array( $this, 'product_options_inventory_product_data' ), 10, 2 );
		// Simple Product Data Save
		add_action( 'woocommerce_process_product_meta', array( $this, 'process_product_meta' ), 10, 2 );

		// Add Custom Field in Variable Product
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'product_after_variable_attributes' ), 10, 3 );
		// Variable Product Data Save
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_product_variation' ), 10, 2 );

		// Add to Cart button text
		add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'add_to_cart_button_text' ), 10, 2 );
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'add_to_cart_button_text' ), 10, 2 );

		// Get Stock HTML
		add_filter( 'woocommerce_get_stock_html', array( $this, 'get_stock_html' ), 10, 2 );

		// Validate Item When Add to Cart
		add_filter( 'woocommerce_add_to_cart', array( $this, 'add_to_cart_validation' ), 10, 6 );
		add_filter( 'woocommerce_update_cart_validation', array( $this, 'update_cart_validation' ), 10, 4 );

		// Add to Cart Item Name
		add_filter( 'woocommerce_cart_item_name', array( $this, 'cart_item_name' ), 10, 4 );

		// Checkout Order Line Meta Modify
		add_filter( 'woocommerce_checkout_create_order_line_item', array( $this, 'checkout_create_order_line_item' ), 10, 4 );

		// Order Item Formatted Meta Data
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( $this, 'order_item_get_formatted_meta_data' ), 10, 4 );

		// add column to order table in admin panel
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_column_in_order_listing_page' ), 10, 1 );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'set_order_type_column_value' ), 10, 2 );
		add_action( 'wopb_save_settings', array( $this, 'generate_css' ), 10, 1 ); // CSS Generator
	}

	/**
	 * Backorder JS & CSS Script
	 *
	 * @since v.1.0.7
	 * @return NULL
	 */
	public function add_backorder_scripts() {
		wp_enqueue_style( 'wopb-backorder-style', WOPB_URL . 'addons/backorder/css/backorder.css', array(), WOPB_VER );
		wp_enqueue_script( 'wopb-backorder-script', WOPB_URL . 'addons/backorder/js/backorder.js', array( 'jquery' ), WOPB_VER, true );
	}

	/**
	 * backorder Addons Initial Setup Action
	 *
	 * @return NULL
	 * @since v.1.0.7
	 */
	public function initial_setup() {
		$settings     = wopb_function()->get_setting();
		$initial_data = array(
			'backorder_heading'                 => 'yes',
			'backorder_button_text'             => __( 'Backorder', 'product-blocks' ),
			'backorder_add_to_cart_button_text' => __( 'Backorder Now', 'product-blocks' ),
			'backorder_remain_typo'             => array(
				'size'        => 16,
				'bold'        => false,
				'italic'      => false,
				'underline'   => false,
				'color'       => '#000000',
				'hover_color' => '',
			),
			'backorder_count_typo'              => array(
				'size'        => 16,
				'bold'        => false,
				'italic'      => false,
				'underline'   => false,
				'color'       => '#000000',
				'hover_color' => '',
			),
			'backorder_badge_typo'              => array(
				'size'        => 12,
				'bold'        => false,
				'italic'      => false,
				'underline'   => false,
				'color'       => '#ffffff',
				'hover_color' => '',
			),
			'backorder_badge_bg'                => array(
				'bg'       => '#007cba',
				'hover_bg' => '',
			),
			'backorder_badge_padding'           => array(
				'top'    => 5,
				'bottom' => 5,
				'left'   => 5,
				'right'  => 5,
			),
			'backorder_badge_border'            => array(
				'border' => 0,
				'color'  => '',
			),
			'backorder_badge_radius'            => 0,
		);
		foreach ( $initial_data as $key => $val ) {
			if ( ! isset( $settings[ $key ] ) ) {
				wopb_function()->set_setting( $key, $val );
			}
		}
		$this->generate_css( 'wopb_backorder' );
	}

	/**
	 * Backorder Custom Field to Inventory Tab
	 *
	 * @return HTML
	 * @since v.1.0.7
	 */
	public function product_options_inventory_product_data() {
		global $post, $product_object;
		if ( $product_object->is_type( 'simple' ) ) {
			echo wp_kses( $this->generate_field( $product_object ), wopb_function()->allowed_html_tags() );
		}
	}

	/**
	 * Backorder Custom Field to Variable Product
	 *
	 * @return HTML
	 * @since v.1.0.7
	 */
	public function product_after_variable_attributes( $loop, $variation_data, $variation ) {
		$product = wc_get_product( $variation->ID );
		echo wp_kses( $this->generate_field( $product, $loop ), wopb_function()->allowed_html_tags() );
	}

	/**
	 * Generate Custom Field
	 *
	 * @return HTML
	 * @since v.1.0.7
	 */
	public function generate_field( $product, $loop = '' ) {
		$html  = '';
		$index = '' !== $loop ? '[' . esc_attr( $loop ) . ']' : '';

		$html     .= '<div class="wopb-backorder-field-group">';
			$html .= '<h4 class="wopb-backorder-title">' . esc_html__( 'WowStore Backorder Information', 'product-blocks' ) . '</h4>';

			$wopb_buffer_level_1 = ob_get_level();
			ob_start();
		try {
			woocommerce_wp_text_input(
				array(
					'id'          => '_wopb_max_backorder' . esc_attr( $index ),
					'class'       => 'wopb-required w-60',
					'label'       => __( 'Available Quantity', 'product-blocks' ),
					'type'        => 'number',
					'value'       => $product->get_meta( '_wopb_max_backorder' ),
					'desc_tip'    => true,
					'description' => __( 'Enter the maximum amount of products available for backorder', 'product-blocks' ),
				)
			);

			/**
			 * Fires in the backorder fields of a product, after "Available Quantity". Output is echoed.
			 *
			 * @since 4.6.0
			 * @param \WC_Product $product Simple product or variation.
			 * @param int|string  $loop    Variation loop index, '' for simple products.
			 */
			do_action( 'wopb_backorder_product_fields', $product, $loop );
			$html .= ob_get_clean();
		} finally {
			while ( ob_get_level() > $wopb_buffer_level_1 ) {
				if ( ! ob_end_clean() ) {
					break;
				}
			}
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Backorder Simple Product Custom Data Save
	 *
	 * @return NULL
	 * @since v.1.0.7
	 */
	public function process_product_meta( $post_id ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return;
		}
		if ( $product->is_on_backorder() || ( ( $product->managing_stock() && $product->get_backorders() === 'yes' ) ) ) {
			$product->update_meta_data( '_wopb_max_backorder', isset( $_POST['_wopb_max_backorder'] ) ? sanitize_text_field( wp_unslash( $_POST['_wopb_max_backorder'] ) ) : '' );

			/**
			 * Fires before a product on backorder is saved, so more backorder data can be added.
			 *
			 * @since 4.6.0
			 * @param \WC_Product $product Simple product or variation.
			 * @param int|null    $index   Variation loop index, null for simple products.
			 */
			do_action( 'wopb_backorder_saved', $product, null );
			$product->save();
		}
	}

	/**
	 * Backorder Variable Product Custom Data Save
	 *
	 * @return NULL
	 * @since v.1.0.7
	 */
	public function save_product_variation( $variation_id, $a ) {
		// WooCommerce saves variations through its AJAX request, which carries the "save-variations" nonce.
		if ( ! isset( $_POST['security'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'save-variations' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $variation_id ) ) {
			return;
		}

		$product = wc_get_product( $variation_id );
		if ( ! $product ) {
			return;
		}
		if ( $product->is_on_backorder() || ( ( $product->managing_stock() && $product->get_backorders() === 'yes' ) ) ) {
			$product->update_meta_data( '_wopb_max_backorder', isset( $_POST['_wopb_max_backorder'][ $a ] ) ? sanitize_text_field( wp_unslash( $_POST['_wopb_max_backorder'][ $a ] ) ) : '' );

			/** This action is documented in addons/backorder/Backorder.php */
			do_action( 'wopb_backorder_saved', $product, $a );
			$product->save();
		}
	}

	/**
	 * Change Add to Cart Button Text
	 *
	 * @return STRING
	 * @since v.1.0.7
	 */
	public function add_to_cart_button_text( $text, $product ) {
		if ( $product &&
			$product->is_type( 'simple' ) &&
			! $product->managing_stock() &&
			$product->is_on_backorder() &&
			$product->is_purchasable() &&
			$this->is_active( $product ) &&
			wopb_function()->get_setting( 'backorder_add_to_cart_button_text' )
		) {
			return wopb_function()->get_setting( 'backorder_add_to_cart_button_text' );
		} else {
			return $text;
		}
	}

	/**
	 * Whether the backorder label, limit and messages apply to a product on backorder
	 *
	 * @param \WC_Product $product Product or variation.
	 * @return BOOLEAN
	 * @since 4.6.0
	 */
	private function is_active( $product ) {
		/**
		 * Filters whether the backorder label, limit and messages apply to a product on backorder.
		 *
		 * @since 4.6.0
		 * @param bool        $active  True by default.
		 * @param \WC_Product $product Product or variation.
		 */
		return (bool) apply_filters( 'wopb_backorder_active', true, $product );
	}

	/**
	 * Get Stock HTML in Product Details Page
	 *
	 * @return HTML
	 * @since v.1.0.7
	 */
	public function get_stock_html( $html, $product ) {
		if (
			( ( $product->managing_stock() && $product->get_backorders() === 'yes' ) ||
			$product->is_on_backorder() ) &&
			$this->is_active( $product ) &&
			$product->is_purchasable()
		) {
			$remaining_items = $this->remaining_item_count( $product );

			/**
			 * Filters the availability message of a product on backorder.
			 *
			 * @since 4.6.0
			 * @param string      $message Message markup, '' by default.
			 * @param \WC_Product $product Product or variation.
			 * @param string      $context 'stock' (product page) or 'cart'.
			 */
			$message = apply_filters( 'wopb_backorder_available_message', '', $product, 'stock' );

			$html      = '<div class="wopb-backorder-wrapper">';
			$html     .= '<div class="wopb-singlepage-backorder-message">';
				$html .= wp_kses_post( $message );
				$html .= '<input type="hidden" class="wopb-single-variation-backorder-text" value="' . esc_attr( wopb_function()->get_setting( 'backorder_add_to_cart_button_text' ) ) . '">';
			$html     .= '</div>';

			if ( $remaining_items ) {
				$html     .= '<div class="wopb-singlepage-backorder-remaining-item">';
					$html .= '<span class="wopb-backorder-remaining-label">' . esc_html__( 'Remaining Item only: ', 'product-blocks' ) . '</span>';
					$html .= '<span class="wopb-backorder-remaining-count">' . esc_html( $remaining_items ) . '</span>';
				$html     .= '</div>';
			}
			$html .= '</div>';
		}
		return $html;
	}

	/**
	 * Show Backorder Label in Cart
	 *
	 * @return HTML
	 * @since v.1.0.7
	 */
	public function cart_item_name( $name, $cart_item, $cart_item_key ) {
		$product = $cart_item['data'];
		if ( $cart_item['variation_id'] ) {
			$product = wc_get_product( $cart_item['variation_id'] );
		}
		if ( ! $product->managing_stock() && $product->is_on_backorder() && $this->is_active( $product ) ) {
			/** This filter is documented in addons/backorder/Backorder.php */
			$message = apply_filters( 'wopb_backorder_available_message', '', $product, 'cart' );

			$html = '<span class="wopb-cart-backorder-badge">' . esc_html( wopb_function()->get_setting( 'backorder_button_text' ) ) . '</span>';
			if ( $message ) {
				$html .= '<div class="wopb-cart-backorder-message">' . wp_kses_post( $message ) . '</div>';
			}
			return $name . $html;
		}
		return $name;
	}

	/**
	 * Add Meta When Checkout Order for Backorder
	 *
	 * @return NULL
	 * @since v.1.0.7
	 */
	public function checkout_create_order_line_item( WC_Order_Item_Product $item, $cart_item_key, $values, $order ) {
		$cart_item = WC()->cart->get_cart()[ $cart_item_key ];
		$product   = wc_get_product( $item['product_id'] );
		if ( $cart_item['variation_id'] ) {
			$product = wc_get_product( $cart_item['variation_id'] );
		}
		if ( ! $product->managing_stock() && $product->is_on_backorder() && $this->is_active( $product ) ) {
			$item->update_meta_data( 'wopb_backorder_item', 'yes' );
		}
	}

	/**
	 * Backorder Label Add to Order Item
	 *
	 * @return HTML
	 * @since v.1.0.7
	 */
	public function order_item_get_formatted_meta_data( $formatted_meta, $item ) {
		foreach ( $formatted_meta as $key => $meta ) {
			if ( 'wopb_backorder_item' === $meta->key ) {
				$meta->display_key = '<span class="wopb-cart-backorder-badge">' . esc_html( wopb_function()->get_setting( 'backorder_button_text' ) ) . '</span>';
			}
		}
		return $formatted_meta;
	}

	/**
	 * Inserting "Order Type" Column Before Last Elements
	 *
	 * @return ARRAY
	 * @since v.1.0.7
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
	 * Show Backorder Label to Order Table Column
	 *
	 * @return HTML
	 * @since v.1.0.7
	 */
	public function set_order_type_column_value( $column ) {
		global $the_order;
		if ( 'wopb_order_page_order_type' === $column ) {
			$has_item = false;
			$items    = $the_order->get_items();
			foreach ( $the_order->get_items() as $item ) {
				if ( $item->get_meta( 'wopb_backorder_item' ) === 'yes' ) {
					$has_item = true;
				}
			}
			if ( $has_item ) {
				echo '<span class="wopb-cart-backorder-badge">' . esc_html( wopb_function()->get_setting( 'backorder_button_text' ) ) . '</span>';
			}
		}
	}

	/**
	 * Validate Maximum Quantity Add to Cart
	 *
	 * @return NULL
	 * @since v.1.0.7
	 */
	public function add_to_cart_validation( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		$cart_item = WC()->cart->get_cart()[ $cart_item_key ];
		if ( ! $this->cart_quantity_validation( $cart_item['quantity'], $product_id, $variation_id ) ) {
			throw new \Exception( esc_html__( 'Quantity Exceeded for Backorder Item.', 'product-blocks' ) );
		}
	}

	public function update_cart_validation( $passed, $cart_item_key, $values, $quantity ) {
		if ( ! $this->cart_quantity_validation( $quantity, $values['product_id'], $values['variation_id'] ) ) {
			wc_add_notice( __( 'Quantity Exceeded for Backorder Item.', 'product-blocks' ), 'error' );
			$passed = false;
		}
		return $passed;
	}

	public function cart_quantity_validation( $quantity, $product_id, $variation_id = null ) {
		if ( $variation_id ) {
			$product = wc_get_product( $variation_id );
		} else {
			$product = wc_get_product( $product_id );
		}

		if ( ! $product->managing_stock() && $product->is_on_backorder() && $this->is_active( $product ) && $quantity > $this->remaining_item_count( $product ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get Remaining Quantity After Order
	 *
	 * @return NUMBER
	 * @since v.1.0.7
	 */
	public function remaining_item_count( $product ) {
		if ( $product->get_parent_id() && $product->get_parent_id() != 0 ) {
			$parent_product = wc_get_product( $product->get_parent_id() );
			if ( $parent_product->is_type( 'variable' ) ) {
				$booked_order_count = $this->get_total_backorder( $parent_product->get_Id(), $product->get_Id() );
			}
		} else {
			$booked_order_count = $this->get_total_backorder( $product->get_Id() );
		}
		$allow_max_backorder = $product->get_meta( '_wopb_max_backorder' );
		$remaining_items     = intval( $allow_max_backorder ) - intval( $booked_order_count );
		return $remaining_items;
	}

	/**
	 * Get Total Order Number
	 *
	 * @return NUMBER
	 * @since v.1.0.7
	 */
	public function get_total_backorder( $product_id, $variation_id = null ) {
		global $wpdb;
		$product_id = absint( $product_id );
		// Live order totals used to enforce backorder limits; not practical to cache.
		if ( $variation_id ) {
			$result = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- live order totals across WooCommerce's order lookup tables (no WP API) to enforce the backorder limit; a cached value would let the limit be exceeded.
				$wpdb->prepare(
					"
                    SELECT sum(order_product.product_qty) as total_order
                    FROM {$wpdb->prefix}wc_order_product_lookup as order_product
                    INNER JOIN {$wpdb->prefix}wc_order_stats AS order_stat
                        ON order_product.order_id = order_stat.order_id
                    INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta
                        ON order_product.order_item_id = order_item_meta.order_item_id AND order_item_meta.meta_key = 'wopb_backorder_item'
                    WHERE order_product.product_id = %d AND order_product.variation_id = %d
                        AND order_stat.status NOT IN ('wc-cancelled', 'wc-refunded')
                ",
					$product_id,
					absint( $variation_id )
				)
			);
		} else {
			$result = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- live order totals across WooCommerce's order lookup tables (no WP API) to enforce the backorder limit; a cached value would let the limit be exceeded.
				$wpdb->prepare(
					"
                    SELECT sum(order_product.product_qty) as total_order
                    FROM {$wpdb->prefix}wc_order_product_lookup as order_product
                    INNER JOIN {$wpdb->prefix}wc_order_stats AS order_stat
                        ON order_product.order_id = order_stat.order_id
                    INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta
                        ON order_product.order_item_id = order_item_meta.order_item_id AND order_item_meta.meta_key = 'wopb_backorder_item'
                    WHERE order_product.product_id = %d
                        AND order_stat.status NOT IN ('wc-cancelled', 'wc-refunded')
                ",
					$product_id
				)
			);
		}
		return isset( $result[0] ) && isset( $result[0]->total_order ) ? $result[0]->total_order : 0;
	}

	/**
	 * Dynamic CSS
	 *
	 * @param $key
	 * @return void
	 * @since v.4.0.0
	 */
	public function generate_css( $key ) {
		if ( 'wopb_backorder' === $key && method_exists( wopb_function(), 'convert_css' ) ) {
			$settings    = wopb_function()->get_setting();
			$badge_style = array_merge( $settings['backorder_badge_typo'], $settings['backorder_badge_bg'] );

			$css      = '.wopb-backorder-remaining-label{';
				$css .= wopb_function()->convert_css( 'general', $settings['backorder_remain_typo'] );
			$css     .= '}';
			$css     .= '.wopb-backorder-remaining-label:hover{';
				$css .= wopb_function()->convert_css( 'hover', $settings['backorder_remain_typo'] );
			$css     .= '}';

			$css     .= '.wopb-backorder-remaining-count{';
				$css .= wopb_function()->convert_css( 'general', $settings['backorder_count_typo'] );
			$css     .= '}';
			$css     .= '.wopb-backorder-remaining-count:hover{';
				$css .= wopb_function()->convert_css( 'hover', $settings['backorder_count_typo'] );
			$css     .= '}';

			$css     .= '.wopb-cart-backorder-badge{';
				$css .= wopb_function()->convert_css( 'general', $badge_style );
				$css .= wopb_function()->convert_css( 'border', $settings['backorder_badge_border'] );
				$css .= wopb_function()->convert_css( 'radius', $settings['backorder_badge_radius'] );
				$css .= 'padding: ' . wopb_function()->convert_css( 'dimension', $settings['backorder_badge_padding'] ) . ';';
			$css     .= '}';
			$css     .= '.wopb-cart-backorder-badge:hover{';
				$css .= wopb_function()->convert_css( 'hover', $badge_style );
			$css     .= '}';

			wopb_function()->update_css( $key, 'add', $css );
		}
	}
}
