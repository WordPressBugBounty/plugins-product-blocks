<?php
/**
 * Sale Notification Addons Core.
 *
 * @package WOPB\SalesNotification
 * @since v.4.0.0
 */

namespace WOPB;

defined( 'ABSPATH' ) || exit;

/**
 * SaleNotification class.
 */
class SalesNotification {

	/**
	 * Setup class.
	 *
	 * @since v.4.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'sales_notification_route' ) );
		add_filter( 'body_class', array( $this, 'sales_notification_body_class' ), 9999, 1 );
		add_action( 'wopb_save_settings', array( $this, 'generate_css' ), 10, 1 ); // CSS Generator
	}

	/**
	 * CSS Generator
	 *
	 * @param $key
	 * @return null
	 * @since v.4.0.0
	 */
	public function generate_css( $key ) {
		if ( 'wopb_sales_notification' === $key ) {
			$settings = wopb_function()->get_setting();
			$css      = '.wopb-notification-wrap { ' . wopb_function()->sanitize_css_keyword( $settings['sales_push_display_position'] ?? '', array( 'left', 'right' ), 'left' ) . ': 20px; }';
			$css     .= '.wopb-notification .wopb-notification-wrap {';
				$css .= isset( $settings['sales_bg_color']['bg'] ) ? ( 'background-color: ' . wopb_function()->sanitize_css_color( $settings['sales_bg_color']['bg'] ) . ';' ) : '';
			$css     .= '}';
			$css     .= '.wopb-notification .wopb-notification-wrap:hover {';
				$css .= isset( $settings['sales_bg_color']['hover_bg'] ) ? ( 'background-color: ' . wopb_function()->sanitize_css_color( $settings['sales_bg_color']['hover_bg'] ) . ';' ) : '';
			$css     .= '}';

			$css .= '.wopb-notification-name {' . wopb_function()->convert_css( 'general', $settings['sales_name_typo'] ) . '}';
			$css .= '.wopb-notification-name:hover {' . wopb_function()->convert_css( 'hover', $settings['sales_name_typo'] ) . '}';
			$css .= '.wopb-notification-name span{' . wopb_function()->convert_css( 'general', $settings['sales_label_typo'] ) . '}';
			$css .= '.wopb-notification-name span:hover{' . wopb_function()->convert_css( 'hover', $settings['sales_label_typo'] ) . '}';

			$css .= '.wopb-notification-product {' . wopb_function()->convert_css( 'general', $settings['sales_title_typo'] ) . '}';
			$css .= '.wopb-notification-product:hover {' . wopb_function()->convert_css( 'hover', $settings['sales_title_typo'] ) . '}';

			$css .= '.wopb-notification-time {' . wopb_function()->convert_css( 'general', $settings['sales_time_typo'] ) . '}';
			$css .= '.wopb-notification-time:hover {' . wopb_function()->convert_css( 'hover', $settings['sales_time_typo'] ) . '}';

			$css .= '.wopb-notification-wrap { border-radius: ' . wopb_function()->sanitize_css_number( $settings['sales_radius'] ?? '' ) . 'px; }';
			$css .= '.wopb-notification-wrap .wopb-img-wrap img { border-radius: ' . wopb_function()->sanitize_css_number( $settings['sales_image_radius'] ?? '' ) . 'px; }';

			$css .= '.wopb-notification-close {' . ( isset( $settings['sales_close_bg']['bg'] ) ? ( 'background-color: ' . wopb_function()->sanitize_css_color( $settings['sales_close_bg']['bg'] ) . ';' ) : '' ) . '}';
			$css .= '.wopb-notification-close:hover {' . ( isset( $settings['sales_close_bg']['hover_bg'] ) ? ( 'background-color: ' . wopb_function()->sanitize_css_color( $settings['sales_close_bg']['hover_bg'] ) . ';' ) : '' ) . '}';
			$css .= '.wopb-notification-close::after {' . ( isset( $settings['sales_close_color']['color'] ) ? ( 'color: ' . wopb_function()->sanitize_css_color( $settings['sales_close_color']['color'] ) . ';' ) : '' ) . '}';
			$css .= '.wopb-notification-close:hover::after {' . ( isset( $settings['sales_close_color']['hover_color'] ) ? ( 'color: ' . wopb_function()->sanitize_css_color( $settings['sales_close_color']['hover_color'] ) . ';' ) : '' ) . '}';

			wopb_function()->update_css( $key, 'add', $css );
		}
	}

	/**
	 * Add Body Class for Checking
	 *
	 * @param $classes
	 * @return array
	 * @since v.4.0.0
	 */
	public function sales_notification_body_class( $classes ) {
		$classes[] = 'wopb-notification-body';
		$classes[] = 'wopb-gap-' . wopb_function()->get_setting( 'sales_gap_time' );
		$classes[] = 'wopb-stay-' . wopb_function()->get_setting( 'sales_stay_time' );
		return $classes;
	}

	/**
	 * Sale Notification
	 *
	 * @return null
	 * @since v.4.0.0
	 */
	public function sales_notification_route() {
		register_rest_route(
			'wopb/v1',
			'/sales-notification/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'public_notification_action' ),
				'permission_callback' => '__return_true', // Shown to every visitor; the response holds no order details and a buyer name only if the owner chose one.
			)
		);
	}

	/**
	 * Recent sales for the notification popup
	 *
	 * Public, so each notification holds only a published, visible product and
	 * how long ago it was bought. The buyer's name is added only when the owner
	 * picks one in the Name setting (default: No Name). No order number, price
	 * or address is read or returned.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return array
	 * @since v.4.0.0
	 */
	public function public_notification_action( $request ) {
		$output = array();
		if ( ! $this->should_display_notification( absint( $request->get_param( 'page_id' ) ) ) ) {
			return array(
				'html'    => $output,
				'message' => 'Notification not displayed.',
			);
		}

		$number = absint( wopb_function()->get_setting( 'sales_number' ) );
		$orders = wc_get_orders(
			array(
				'limit'   => min( 20, max( 1, $number ? $number : 10 ) ),
				'orderby' => 'date',
				'order'   => 'DESC',
				'status'  => wc_get_is_paid_statuses(),
				'type'    => 'shop_order',
			)
		);
		if ( empty( $orders ) ) {
			return array(
				'html'    => $output,
				'message' => 'No recent orders.',
			);
		}

		$name    = wopb_function()->get_setting( 'sales_name' );
		$label   = wopb_function()->get_setting( 'sales_label' );
		$image   = wopb_function()->get_setting( 'sales_image' );
		$time    = wopb_function()->get_setting( 'sales_time' );
		$prefix  = wopb_function()->get_setting( 'sales_time_prefix' );
		$postfix = wopb_function()->get_setting( 'sales_time_postfix' );

		foreach ( $orders as $order ) {
			$product = $this->get_notification_product( $order );
			$created = $order->get_date_created();
			if ( ! $product || ! $created ) {
				continue;
			}

			$customer_name = $this->get_customer_name( $order, $name );
			$title_html    = trim( esc_html( $customer_name ) . ' ' . ( $label ? '<span>' . esc_html( $label ) . '</span>' : '' ) );

			$image_url = '';
			if ( 'yes' === $image ) {
				$image_url = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );
				$image_url = $image_url ? $image_url : wc_placeholder_img_src( 'thumbnail' );
			}

			$html          = '<div class="wopb-notification wopb-animation-notification active">';
				$html     .= '<div class="wopb-notification-wrap" style="' . ( $image_url ? 'padding: 10px' : 'padding: 12px 25px' ) . '">';
					$html .= '<a href="' . esc_url( $product->get_permalink() ) . '">';
			if ( $image_url ) {
				$html     .= '<div class="wopb-img-wrap">';
					$html .= '<img alt="" src="' . esc_url( $image_url ) . '"/>';
				$html     .= '</div>';
			}
						$html     .= '<div class="wopb-col">';
			if ( $title_html ) {
							$html .= '<div class="wopb-notification-name">' . $title_html . '</div>';
			}
							$html .= '<div class="wopb-notification-product">' . esc_html( $product->get_name() ) . '</div>';
			if ( 'yes' === $time ) {
				$html .= '<div class="wopb-notification-time">' . esc_html( $prefix . ' ' . human_time_diff( $created->getTimestamp(), time() ) . ' ' . $postfix ) . '</div>';
			}
						$html .= '</div>';
					$html     .= '</a>';
					$html     .= '<span class="wopb-notification-close"></span>';
				$html         .= '</div>';
			$html             .= '</div>';

			$output[] = $html;
		}

		return array(
			'html'    => $output,
			'message' => 'No recent orders.',
		);
	}

	/**
	 * Buyer name for a notification
	 *
	 * Empty for "No Name" (the default and any unknown value), so nothing
	 * about the buyer is read. Falls back to "Someone" when the chosen name
	 * is blank on the order.
	 *
	 * @param \WC_Order $order Order.
	 * @param string    $type  Value of the Name setting.
	 * @return string
	 * @since 4.6.0
	 */
	private function get_customer_name( $order, $type ) {
		$types = array( 'display', 'first_name', 'last_name', 'full', 'user_name', 'nick_name' );
		if ( ! in_array( $type, $types, true ) ) {
			return '';
		}

		$first = $order->get_billing_first_name();
		$last  = $order->get_billing_last_name();
		$user  = $order->get_user_id() ? get_userdata( $order->get_user_id() ) : false;

		if ( $user ) {
			$first = $user->first_name ? $user->first_name : $first;
			$last  = $user->last_name ? $user->last_name : $last;
		}

		$name = '';
		switch ( $type ) {
			case 'first_name':
				$name = $first;
				break;
			case 'last_name':
				$name = $last;
				break;
			case 'user_name':
				$name = $user ? $user->user_login : '';
				break;
			case 'nick_name':
				$name = $user ? $user->nickname : '';
				break;
			case 'display':
				$name = $user ? $user->display_name : '';
				break;
		}
		if ( '' === trim( $name ) ) {
			// "Full" and any blank choice fall back to the full name.
			$name = trim( $first . ' ' . $last );
		}

		return '' === $name ? __( 'Someone', 'product-blocks' ) : $name;
	}

	/**
	 * First product of an order that any visitor may see
	 *
	 * @param \WC_Order $order Order.
	 * @return \WC_Product|null
	 * @since 4.6.0
	 */
	private function get_notification_product( $order ) {
		foreach ( $order->get_items() as $item ) {
			if ( ! is_callable( array( $item, 'get_product_id' ) ) ) {
				continue;
			}
			$product = wc_get_product( $item->get_product_id() );
			if (
				$product &&
				'publish' === $product->get_status() &&
				$product->is_visible() &&
				! post_password_required( $product->get_id() )
			) {
				return $product;
			}
		}
		return null;
	}

	/**
	 * Check if notification should display on specific page
	 *
	 * @param int $page_id
	 * @return bool
	 * @since v.4.0.0
	 */
	public function should_display_notification( $page_id = 0 ) {
		$action = wopb_function()->get_setting( 'select_page_visibility' );

		if ( 'all' === $action ) {
			return true;
		}

		$selected_page_list = wopb_function()->get_setting( 'custom_page_selection' );

		$is_page_in_list = is_array( $selected_page_list ) && in_array( $page_id, $selected_page_list );

		return 'include' === $action ? $is_page_in_list : ! $is_page_in_list;
	}
}
