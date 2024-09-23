<?php
namespace WOPB\blocks;

defined('ABSPATH') || exit;

class Product_Additional_Info{

    public function __construct() {
        add_action( 'init', array( $this, 'register' ) );
    }

    public function get_attributes() {
        return array(
            'showHeading' => true,
            'headingText' => 'Additional information'
        );
    }

    public function register() {
        register_block_type( 'product-blocks/product-additional-info',
            array(
                'editor_script' => 'wopb-blocks-builder-script',
                'editor_style'  => 'wopb-blocks-editor-css',
                'render_callback' =>  array( $this, 'content' )
            )
        );
    }

    public function content( $attr ) {
        global $product;
        global $productx_info;
        $product = wc_get_product();
        $block_name = 'product-additional-info';
        $wraper_before = $wraper_after = $content = '';
        $attr = wp_parse_args( $attr, $this->get_attributes() );
        $productx_info = $attr['showHeading'] ? $attr['headingText'] : '';

        if ( ! empty( $product ) ) {
            if ( $product->has_attributes() || $product->has_dimensions() || $product->has_weight() ) {
                $attr['className'] = !empty($attr['className']) ? preg_replace('/[^A-Za-z0-9_ -]/', '', $attr['className']) : '';

                $additional_heading = function() {
                    global $productx_info;
                    return $productx_info;
                };
    
                $wraper_before .= '<div '.(isset($attr['advanceId']) ? 'id="' . sanitize_html_class($attr['advanceId']) . '" ':'') . ' class="wp-block-product-blocks-' . esc_attr($block_name) . ' wopb-block-' . sanitize_html_class($attr["blockId"]) . ' ' . $attr['className'] . '">';
                $wraper_before .= '<div class="wopb-product-wrapper">';
    
                add_filter( 'woocommerce_product_additional_information_heading', $additional_heading );
    
                ob_start();
                woocommerce_product_additional_information_tab();
                $content .= ob_get_clean();
    
                remove_filter( 'woocommerce_product_additional_information_heading', $additional_heading );
    
                $wraper_after .= '</div>';
                $wraper_after .= '</div>';
            }
        }

        return $wraper_before.$content.$wraper_after;
    }

}