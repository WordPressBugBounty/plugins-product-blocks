<?php
namespace WOPB\blocks;

defined( 'ABSPATH' ) || exit;

class Checkout_Shipping {
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function get_attributes() {
		return array(
			'showTitle'     => true,
			'shippingTitle' => 'Shipping Address',
			'checkboxTitle' => 'Ship to Different address ?',
			'currentPostId' => '',
		);
	}

	public function register() {
		register_block_type(
			'product-blocks/checkout-shipping',
			array(
				'editor_script'   => 'wopb-blocks-builder-script',
				'editor_style'    => 'wopb-blocks-editor-css',
				'render_callback' => array( $this, 'content' ),
			)
		);
	}

	public function content( $attr, $noAjax = false ) {
		if ( function_exists( 'WC' ) && is_checkout() ) {
			$block_name    = 'checkout-shipping';
			$wraper_before = $wraper_after = $content = '';
			$attr          = wp_parse_args( $attr, $this->get_attributes() );

			if ( WC()->cart->needs_shipping_address() === true ) {
				$attr['className'] = ! empty( $attr['className'] ) ? preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['className'] ) : '';

				$wraper_before     .= '<div ' . ( isset( $attr['advanceId'] ) ? 'id="' . sanitize_html_class( $attr['advanceId'] ) . '" ' : '' ) . ' class="wp-block-product-blocks-' . $block_name . ' wopb-block-' . sanitize_html_class( $attr['blockId'] ) . ' ' . $attr['className'] . '">';
					$wraper_before .= '<div class="wopb-product-wrapper">';

				if ( ! is_admin() ) {
					if ( isset( WC()->customer ) ) {
						$wopb_buffer_level_1 = ob_get_level();
						ob_start();
						try {
							require_once WOPB_PATH . 'blocks/woo/checkout/shipping/Template.php';
							$content .= ob_get_clean();
						} finally {
							while ( ob_get_level() > $wopb_buffer_level_1 ) {
								if ( ! ob_end_clean() ) {
									break;
								}
							}
						}
					}
				}

					$wraper_after .= '</div>';
				$wraper_after     .= '</div>';
			}

			return $wraper_before . $content . $wraper_after;
		}
	}
}
