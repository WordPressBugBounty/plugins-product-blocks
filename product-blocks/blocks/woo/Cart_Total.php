<?php
namespace WOPB\blocks;

defined( 'ABSPATH' ) || exit;

class Cart_Total {
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function get_attributes() {
		return array(
			'cartTotalTxt'  => 'Cart Total',
			'subTotalTxt'   => 'Subtotal',
			'totalTxt'      => 'Total',
			'checkoutTxt'   => 'Proceed to checkout',
			'currentPostId' => '',
		);
	}

	public function register() {
		register_block_type(
			'product-blocks/cart-total',
			array(
				'editor_script'   => 'wopb-blocks-builder-script',
				'editor_style'    => 'wopb-blocks-editor-css',
				'render_callback' => array( $this, 'content' ),
			)
		);
	}

	public function content( $attr, $noAjax = false ) {
		$block_name    = 'cart-total';
		$wraper_before = $wraper_after = $content = '';
		$attr          = wp_parse_args( $attr, $this->get_attributes() );

		if (
			function_exists( 'WC' ) &&
			! is_admin() &&
			isset( WC()->customer ) &&
			! WC()->cart->is_empty()
		) {
			$attr['className'] = ! empty( $attr['className'] ) ? preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['className'] ) : '';

			$wraper_before     .= '<div ' . ( isset( $attr['advanceId'] ) ? 'id="' . esc_attr( sanitize_html_class( $attr['advanceId'] ) ) . '" ' : '' ) . ' class="wp-block-product-blocks-' . esc_attr( $block_name ) . ' wopb-block-' . esc_attr( sanitize_html_class( $attr['blockId'] ) ) . ' ' . esc_attr( $attr['className'] ) . '">';
				$wraper_before .= '<div class="wopb-product-wrapper">';

				$wopb_buffer_level_1 = ob_get_level();
				ob_start();
			try {
				require_once WOPB_PATH . 'blocks/woo/cart_total/Template.php';
				do_action( 'woocommerce_after_cart_totals' );
				$content .= wp_kses(
					ob_get_clean(),
					wopb_function()->allowed_form_html(
						array(
							'noscript'                    => array(),
							'wc-order-attribution-inputs' => array(),
							'span'                        => array( 'translate' => true ),
							'select'                      => array( 'rel' => true ),
						)
					)
				);
			} finally {
				while ( ob_get_level() > $wopb_buffer_level_1 ) {
					if ( ! ob_end_clean() ) {
						break;
					}
				}
			}

				$wraper_after .= '</div>';
			$wraper_after     .= '</div> ';
		}
		return $wraper_before . $content . $wraper_after;
	}
}


/** Holds the render context for a removable, named hook callback. */
class CartTotalCheckoutButtonCallback {
	private $attr;

	public function __construct( $attr ) {
		$this->attr = $attr;
	}

	public function render_checkout_button() {
		$attr = $this->attr;
		echo '<a href="' . esc_url( wc_get_checkout_url() ) . '" class="checkout-button button alt wc-forward">';
		echo esc_html( $attr['checkoutTxt'] );
		echo '</a>';
	}
}
