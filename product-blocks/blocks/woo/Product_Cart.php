<?php
namespace WOPB\blocks;

defined( 'ABSPATH' ) || exit;

class Product_Cart {

	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'template_redirect', array( $this, 'buy_now_submit' ) );
	}

	public function get_attributes() {
		return array(
			'showQuantity'        => true,
			'quantityBtnPosition' => 'right',
			'showQuantityBtn'     => true,
			'currentPostId'       => '',
			'showBuyNow'          => false,
			'removeProduct'       => true,
			'btnBuyText'          => 'Buy Now',
		);
	}

	public function register() {
		register_block_type(
			'product-blocks/product-cart',
			array(
				'editor_script'   => 'wopb-blocks-builder-script',
				'editor_style'    => 'wopb-blocks-editor-css',
				'render_callback' => array( $this, 'content' ),
			)
		);
	}

	public function content( $attr ) {
		global $product;
		$product       = wc_get_product(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WooCommerce's own $product global, not plugin-defined.
		$block_name    = 'product-cart';
		$wraper_before = $wraper_after = $content = '';
		$attr          = wp_parse_args( $attr, $this->get_attributes() );

		if ( ! empty( $product ) ) {
			$cart_class = '';

			if ( ! empty( $attr['showBuyNow'] ) ) {
				add_filter(
					'wopb_bottom_add_to_cart',
					array( new ProductCartAppendBuyNowButtonCallback( $attr, $product ), 'append_buy_now_button' )
				);
			}

			$attr['className'] = ! empty( $attr['className'] ) ? preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['className'] ) : '';

			$wraper_before .= '<div ' . ( isset( $attr['advanceId'] ) ? 'id="' . esc_attr( sanitize_html_class( $attr['advanceId'] ) ) . '" ' : '' ) . ' class="wp-block-product-blocks-' . esc_attr( $block_name ) . ' wopb-block-' . esc_attr( sanitize_html_class( $attr['blockId'] ) ) . ' ' . esc_attr( $attr['className'] ) . '">';

			if (
				! $attr['showQuantity'] ||
				( $product->get_manage_stock() && $product->get_stock_quantity() < 2 )
			) {
				$cart_class .= ' wopb-hide-qty';
				add_filter( 'woocommerce_is_sold_individually', array( $this, 'remove_quantity_fields' ), 10, 2 );
			}

			$wopb_buffer_level_1 = ob_get_level();
			ob_start();
			try {
				echo '<div class="wopb-product-wrapper wopb-builder-cart' . esc_attr( $cart_class ) . '" data-type="' . ( $attr['showQuantity'] && $attr['showQuantityBtn'] ? esc_attr( $attr['quantityBtnPosition'] ) : '' ) . '">';
				$this->remove_qty_element();
				woocommerce_template_single_add_to_cart();
				do_action( 'wopb_after_builder_add_cart_button' );
				echo '</div>';
				$form = ob_get_clean();
			} finally {
				while ( ob_get_level() > $wopb_buffer_level_1 ) {
					if ( ! ob_end_clean() ) {
						break;
					}
				}
			}
			$content .= $this->kses_cart_form( str_replace( 'single_add_to_cart_button', 'single_add_to_cart_button wopb-cart-button', $form ) );

			$wraper_after .= '</div>';
		}

		return $wraper_before . $content . $wraper_after;
	}

	/**
	 * Escape the add to cart form captured from WooCommerce and the extensions hooked into it.
	 * Beyond the shared form allowlist it keeps the template element WooCommerce clones for the
	 * variation gallery, the attributes variation swatch and product add-on plugins read, and
	 * the inline transition of the gallery.
	 *
	 * @param string $html Captured add to cart markup.
	 * @return string
	 */
	private function kses_cart_form( $html ) {
		$allowed = wopb_function()->allowed_form_html(
			array(
				'template' => array(
					'class' => true,
				),
				'div'      => array(
					'aria-relevant' => true,
					'readonly'      => true,
					'swatches-attr' => true,
				),
				'input'    => array(
					'hidden' => true,
				),
				'img'      => array(
					'itemprop' => true,
				),
				'span'     => array(
					'translate' => true,
				),
				'svg'      => array(
					'aria-hidden' => true,
					'focusable'   => true,
				),
			)
		);
		add_filter( 'safe_style_css', array( $this, 'allow_transition_style' ) );
		$html = wp_kses( $html, $allowed );
		remove_filter( 'safe_style_css', array( $this, 'allow_transition_style' ) );
		return $html;
	}

	/**
	 * Let the transition property through the inline style filter while the cart form is escaped.
	 *
	 * @param string[] $properties Allowed CSS properties.
	 * @return string[]
	 */
	public function allow_transition_style( $properties ) {
		$properties[] = 'transition';
		return $properties;
	}

	public function remove_qty_element() {
		remove_action( 'woocommerce_before_quantity_input_field', 'botiga_woocommerce_before_quantity_input_field' );
		remove_action( 'woocommerce_before_quantity_input_field', 'baseket_minus_btn' );
		remove_action( 'woocommerce_before_quantity_input_field', 'plant_minus_btn' );

		remove_action( 'woocommerce_before_add_to_cart_quantity', 'big_store_display_quantity_minus' );

		remove_action( 'woocommerce_after_quantity_input_field', 'botiga_woocommerce_after_quantity_input_field' );
		remove_action( 'woocommerce_after_quantity_input_field', 'baseket_plus_btn' );
		remove_action( 'woocommerce_after_quantity_input_field', 'plant_plus_btn' );

		remove_action( 'woocommerce_after_add_to_cart_quantity', 'big_store_display_quantity_plus' );
	}

	public function remove_quantity_fields( $return, $product ) {
		return true;
	}

	/**
	 * Click Buy Now and Redirect to Checkout Page
	 *
	 * @return null
	 * @since v.4.1.4
	 */
	public function buy_now_submit() {
		if ( ! isset( $_REQUEST['wopb-buy-now-nonce'] ) || ! is_string( $_REQUEST['wopb-buy-now-nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wopb-buy-now-nonce'] ) ), 'wopb-buy-now' ) ) {
			return;
		}
		$sanitized_request = wopb_function()->rest_sanitize_params( wp_unslash( $_REQUEST ) );
		if ( ! empty( $sanitized_request['wopb-buy-now'] ) ) {
			$product_id        = is_scalar( $sanitized_request['wopb-buy-now'] ) ? absint( $sanitized_request['wopb-buy-now'] ) : 0;
			if ( ! $product_id || ! wopb_function()->can_view_product( $product_id ) ) {
				return;
			}
			$qty               = floatval( ! empty( $sanitized_request['quantity'] ) ? $sanitized_request['quantity'] : 1 );
			$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $qty );
			if ( $passed_validation ) {
				$redirect = false;
				if ( ! empty( $sanitized_request['wopb-remove-product'] ) ) {
					WC()->cart->empty_cart();
					if ( ! empty( $sanitized_request['variation_id'] ) ) {
						$variation = array();
						foreach ( $sanitized_request as $name => $value ) {
							if ( str_starts_with( $name, 'attribute_' ) ) {
								$variation[ $name ] = $value;
							}
						}
						WC()->cart->add_to_cart( $product_id, $qty, $sanitized_request['variation_id'], $variation );
					}
				}

				if ( ! isset( $sanitized_request['variation_id'] ) ) {
					WC()->cart->add_to_cart( $product_id, $qty );
					$redirect = true;
				} elseif ( ! empty( $sanitized_request['variation_id'] ) ) {
					$redirect = true;
				}
				if ( $redirect ) {
					wp_safe_redirect( wc_get_checkout_url() );
					exit;
				} else {
					return;
				}
			}
		}
	}
}


/** Holds the render context for a removable, named hook callback. */
class ProductCartAppendBuyNowButtonCallback {
	private $attr;
	private $product;

	public function __construct( $attr, $product ) {
		$this->attr    = $attr;
		$this->product = $product;
	}

	public function append_buy_now_button( $content ) {
		$attr    = $this->attr;
		$product = $this->product;
		$content .= '<input type="hidden" id="wopb-buy-now-nonce" name="wopb-buy-now-nonce" value="' . esc_attr( wp_create_nonce( 'wopb-buy-now' ) ) . '" />';
		if ( ! empty( $attr['removeProduct'] ) ) {
			$content .= '<input type="hidden" name="wopb-remove-product" value="true">';
		}

		$content .= '<button ';
		$content .= 'type="submit" ';
		$content .= 'name="wopb-buy-now" ';
		$content .= 'class="wopb-buy-button ' . ( $product->is_type( 'variable' ) ? 'wc-variation-selection-needed button disabled' : '' ) . '" ';
		$content .= 'value="' . esc_attr( $product->get_id() ) . '" ';
		$content .= 'data-product_id="' . esc_attr( $product->get_id() ) . '" ';
		$content .= '>';
		$content .= ! empty( $attr['btnBuyText'] ) ? esc_html( $attr['btnBuyText'] ) : esc_html__( 'Buy Now', 'product-blocks' );
		$content .= '</button>';
		return $content;
	}
}
