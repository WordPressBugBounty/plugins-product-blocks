<?php
defined( 'ABSPATH' ) || exit;

if ( wp_is_block_theme() ) {
    wp_site_icon();
	wp_head();
    block_template_part( 'header' );
	wp_head();
} else {
	get_header();
}
$page_id = wopb_function()->conditions( 'return' );

do_action( 'wopb_before_content' );

$width          = get_post_meta( $page_id, '__wopb_container_width', true );
$sidebar        = get_post_meta( $page_id, 'wopb-builder-sidebar', true );
$widget_area    = get_post_meta( $page_id, 'wopb-builder-widget-area', true );
$has_widget     = ( $sidebar && $widget_area != '' ) ? true : false;

if ( $width ) {
    echo '<div class="wopb-builder-container product ' . ( ( $has_widget?' wopb-widget-'.esc_attr( $sidebar ) : '' ) ) . '" style="max-width: ' . esc_attr( $width ) . 'px; margin: 0 auto;">';
    if ( is_product() ) {
        wc_print_notices();
    }
}
if ( $has_widget && $sidebar == 'left' ) {
   echo '<div class="wopb-sidebar-left">';
       if ( is_active_sidebar( $widget_area ) ) {
           dynamic_sidebar( $widget_area );
       }
   echo '</div>';
}
if ( $page_id && $has_widget ) {
    echo '<div class="wopb-builder-wrap">';
}
if ( is_checkout() && ! ( is_wc_endpoint_url() || is_wc_endpoint_url( 'order-pay' ) || is_wc_endpoint_url( 'order-received' ) ) ) {
    $checkout = WC()->checkout();
    remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
    remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
    do_action( 'woocommerce_before_checkout_form', $checkout );

    // If checkout registration is disabled and not logged in, the user cannot checkout.
    if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
        echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'product-blocks' ) ) );
        return;
    }
    echo '<form name="checkout" method="post" class="checkout woocommerce-checkout wopb-checkout-form" action="'. esc_url( wc_get_checkout_url() ).'" enctype="multipart/form-data" style="display:block">';
}

if ( $page_id ) {
    if ( have_posts() ) :
        the_post();
        if ( $page_id ) {
            $content_post = get_post( $page_id );
            $content = $content_post->post_content;
            if ( has_blocks( $content ) ) {
                $blocks = parse_blocks( $content );
                $embed = new WP_Embed();
                foreach ( $blocks as $block ) {
                    echo $embed->autoembed( do_shortcode( render_block( $block ) ) ); //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
                }
            } else {
                the_content();
            }
        }
    endif;
}
if ( is_checkout() && ! ( is_wc_endpoint_url() || is_wc_endpoint_url( 'order-pay' ) || is_wc_endpoint_url( 'order-received' ) ) ) {
    echo '</form>';
    do_action( 'woocommerce_after_checkout_form', $checkout ); 
}

if ( $page_id && $has_widget ) {
    echo '</div>';
}
if ( $has_widget && $sidebar == 'right' ) {
    echo '<div class="wopb-sidebar-right">';
        if ( is_active_sidebar( $widget_area ) ) {
            dynamic_sidebar( $widget_area );
        }
    echo '</div>';
}

if ( $width ) {
    echo '</div>';
}

do_action( 'wopb_after_content' );

if ( wp_is_block_theme() ) {
    wp_footer();
	block_template_part( 'footer' );
} else {
	get_footer();
}