<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- the variables below belong to the method scope that includes this partial; the functions it declares carry the wopb_ prefix.

WC()->cart->calculate_totals();
WC()->cart->calculate_shipping();

$free_shipping_progress = 0;
$free_shipping_cart_due = 0;

if ( ! empty( $free_shipping_instance_id ) ) {
	$sub_total = WC()->cart->get_subtotal() + ( WC()->cart->get_subtotal_tax() ? WC()->cart->get_subtotal_tax() : 0 );
	$data      = get_option( 'woocommerce_free_shipping_' . $free_shipping_instance_id . '_settings', array() );

	if ( isset( $data['min_amount'] ) ) {
		$data['min_amount'] = wopb_function()->get_price_data( $data['min_amount'] )['value'];
		if ( $data['min_amount'] > $sub_total ) {
			$free_shipping_cart_due = $data['min_amount'] - $sub_total;
			$free_shipping_progress = floor( ( $sub_total * 100 ) / $data['min_amount'] );
		} else {
			$free_shipping_progress = 100;
		}
	}
}

function wopb_free_shipping_progress_bar( $free_shipping_progress, $attr ) {
	$html      = '';
	$html     .= '<div class="wopb-progress-area ">';
		$html .= '<div class="wopb-progress-bar-filled" style="width:' . esc_attr( $free_shipping_progress ) . '%;"></div>';
	$html     .= '</div>';
	return $attr['showProgress'] ? $html : '';
}

?>

<div class="wopb-progress-bar wopb-free-progress-bar-section">
	<?php
	if ( $attr['progressTop'] ) {
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wopb_free_shipping_progress_bar( $free_shipping_progress, $attr );
	}
	?>
	<div class="wopb-progress-msg">
		<?php if ( 100 == $free_shipping_progress ) { ?>
			<span><?php echo esc_html( $attr['freeShipText'] ); ?></span>
		<?php } else { ?>
			<span><?php echo wp_kses( $attr['beforePriceText'], wopb_function()->allowed_html_tags() ); ?></span>
			<strong>
				<span class="woocommerce-Price-amount amount wopb-shippingRemainingAmount">
					<?php
						echo wc_price( $free_shipping_cart_due ); //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</span>
			</strong>
			<span><?php echo wp_kses( $attr['afterPriceText'], wopb_function()->allowed_html_tags() ); ?></span>
		<?php } ?>
	</div>
	<?php
	if ( ! $attr['progressTop'] ) {
		echo wopb_free_shipping_progress_bar( $free_shipping_progress, $attr ); //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>
</div>

<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals ?>
