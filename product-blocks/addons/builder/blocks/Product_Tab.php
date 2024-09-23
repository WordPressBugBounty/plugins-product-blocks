<?php
namespace WOPB\blocks;

defined('ABSPATH') || exit;

class Product_Tab{

    public function __construct() {
        add_action( 'init', array( $this, 'register' ) );
    }

    public function get_attributes() {
        return array (
            'showDescription' => true,
            'showAddInfo' => true,
            'showReview' => true,
            'showDesc' => true,
            'descText' => 'Description',
            'showAddInfoHeading' => true,
            'headingText' => 'Product Tab',
        );
    }

    public function register() {
        register_block_type( 'product-blocks/product-tab',
            array(
                'editor_script' => 'wopb-blocks-builder-script',
                'editor_style'  => 'wopb-blocks-editor-css',
                'render_callback' =>  array( $this, 'content' )
            )
        );
    }

    public function content( $attr ) {
        global $product;
        $product = wc_get_product();
        $block_name = 'product-tab';
        $wraper_before = $wraper_after = $content = '';
        $attr = wp_parse_args( $attr, $this->get_attributes() );        
        
        if ( ! empty( $product ) ) {
            global $productx_tab;
            $productx_tab['desc'] = $attr['showDescription'];
            $productx_tab['info'] = $attr['showAddInfo'];
            $productx_tab['review'] = $attr['showReview'];
            $hide_description = function( $tabs ) {
                global $productx_tab;
                if (!$productx_tab['desc']) {
                    unset( $tabs['description'] );
                }
                if (!$productx_tab['info']) {
                    unset( $tabs['additional_information'] );
                }
                if (!$productx_tab['review']) {
                    unset( $tabs['reviews'] );
                }
                return $tabs;
            };

            $productx_tab['add_heading'] = $attr['showAddInfoHeading'];
            $productx_tab['add_text'] = esc_html($attr['headingText']);
            $additional_heading = function() {
                global $productx_tab;
                return $productx_tab['add_heading'] ? $productx_tab['add_text'] : '';
            };

            $productx_tab['desc_heading'] = $attr['showDesc'];
            $productx_tab['desc_text'] = esc_html($attr['descText']);
            $description_heading = function() {
                global $productx_tab;
                return $productx_tab['desc_heading'] ? $productx_tab['desc_text'] : '';
            };

            $attr['className'] = !empty($attr['className']) ? preg_replace('/[^A-Za-z0-9_ -]/', '', $attr['className']) : '';
            $attr['align'] = !empty($attr['align']) ? preg_replace('/[^A-Za-z0-9_ -]/', '', $attr['align']) : '';
            
            $wraper_before .= '<div ' . (isset($attr['advanceId']) ? 'id="' . sanitize_html_class($attr['advanceId']).'" ':'').' class="wp-block-product-blocks-' . esc_attr($block_name).' wopb-block-' . sanitize_html_class($attr["blockId"]).' ' . $attr['className'] . $attr['align'] . '">';
                $wraper_before .= '<div class="wopb-product-wrapper">';
                    $wraper_before .= '<div class="product">';

                    add_filter( 'woocommerce_product_tabs', $hide_description );
                    add_filter( 'woocommerce_product_additional_information_heading', $additional_heading );
                    add_filter( 'woocommerce_product_description_heading', $description_heading );

                    ob_start();
                    woocommerce_output_product_data_tabs();
                    $content .= ob_get_clean();

                    remove_filter( 'woocommerce_product_tabs', $hide_description );
                    remove_filter( 'woocommerce_product_additional_information_heading', $additional_heading );
                    remove_filter( 'woocommerce_product_description_heading', $description_heading );
                    $wraper_after .= '</div>';
                $wraper_after .= '</div>';
            $wraper_after .= '</div>';
        }

        return $wraper_before.$content.$wraper_after;
    }

}