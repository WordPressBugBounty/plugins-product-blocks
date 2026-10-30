<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- template partial included directly into a method scope; these are local render-time variables/functions, not plugin globals.

$order = wopb_function()->get_thankyou_order();

if ( $order ) {
	do_action( 'woocommerce_before_thankyou', $order->get_id() );
	?>
	<div class="wopb-thankyou-order-conformation-container">
		<?php if ( $attr['showHead'] ) { ?>
			<div class="wopb-order-heading-section" >
				<div class="wopb-order-heading"><?php echo esc_html( $attr['orderHeadText'] . $order->get_order_number() ); ?> </div>
			</div>
			<?php
		}
		if ( $order->has_status( 'failed' ) ) {
			?>
			<div class="wopb-thankyou-order-fail">
				<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'product-blocks' ); ?></p>

				<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
					<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'product-blocks' ); ?></a>
					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My account', 'product-blocks' ); ?></a>
					<?php endif; ?>
				</p>
			</div>

			<?php
		} elseif ( $attr['showMessage'] ) {
			?>
				<div class="wopb-order-message-section" >
					<div class="wopb-order-message"> <?php echo esc_html( $attr['messageText'] ); ?></div>
				</div>
				<?php

		}
		?>
	</div>
	<?php
} else {
	$order_id = get_query_var( 'order-received' );
	// Older WooCommerce shortcodes cannot safely handle an array-valued order key.
	if ( isset( $_GET['key'] ) && is_string( $_GET['key'] ) && is_scalar( $order_id ) && ctype_digit( (string) $order_id ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only emailed order link, which cannot carry a nonce; WooCommerce's shortcode validates this order key (and the shopper) before showing any order details.
		\WC_Shortcode_Checkout::output( array() );
	} else {
		wc_get_template( 'checkout/thankyou.php', array( 'order' => false ) );
	}
}
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals
