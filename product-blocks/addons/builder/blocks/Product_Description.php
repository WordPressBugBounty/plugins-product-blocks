<?php
namespace WOPB\blocks;

defined('ABSPATH') || exit;

class Product_Description{

    public function __construct() {
        add_action( 'init', array( $this, 'register' ) );
    }

    public function get_attributes() {
        return array(
            'showDescHeading' => true,
            'headingDescText' => 'Description',
        );
    }

    public function register() {
        register_block_type( 'product-blocks/product-description',
            array(
                'editor_script' => 'wopb-blocks-builder-script',
                'editor_style'  => 'wopb-blocks-editor-css',
                'render_callback' =>  array( $this, 'content' )
            )
        );
    }

    public function content( $attr ) {
        global $product;
        global $productx_desc;
        $product = wc_get_product();
        $block_name = 'product-description';
        $wraper_before = $wraper_after = $content = '';
        $attr = wp_parse_args( $attr, $this->get_attributes() );
        $productx_desc = $attr['showDescHeading'] ? $attr['headingDescText'] : '';
        
        if ( ! empty( $product ) ) {
            $description_heading = function() {
                global $productx_desc;
                return $productx_desc;
            };
            $attr['className'] = !empty($attr['className']) ? preg_replace('/[^A-Za-z0-9_ -]/', '', $attr['className']) : '';

            $wraper_before .= '<div '.(isset($attr['advanceId']) ? 'id="' . sanitize_html_class($attr['advanceId']) . '" ':'') . ' class="wp-block-product-blocks-' . esc_attr($block_name) . ' wopb-block-' . sanitize_html_class($attr["blockId"]) . ' ' . $attr['className'] . '">';
            $wraper_before .= '<div class="wopb-product-wrapper">';
            
            add_filter( 'woocommerce_product_description_heading', $description_heading );
            ob_start();
            woocommerce_product_description_tab();
            $content = ob_get_clean();
            remove_filter( 'woocommerce_product_description_heading', $description_heading );

            $wraper_after .= '</div>';
            $wraper_after .= '</div>';
        }

        return $wraper_before.$content.$wraper_after;
    }

}