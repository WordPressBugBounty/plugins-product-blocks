<?php
namespace WOPB\blocks;

defined('ABSPATH') || exit;

class Product_Rating{

    public function __construct() {
        add_action( 'init', array( $this, 'register' ) );
    }

    public function get_attributes() {
        return array(
            'blockId' => '',
            'enableLabel' => false,
            'enableOrder' => false,
            'reivewText' => 'Reviews',
            'orderLabelText' => 'Orders',
            'ratingSeparator' => '/'
        );
    }

    public function register() {
        register_block_type( 'product-blocks/product-rating',
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
        $block_name = 'product-rating';
        $wraper_before = $wraper_after = $content = '';
        $attr = wp_parse_args( $attr, $this->get_attributes() );        

        if ( ! empty( $product ) ) {
            if ( $product->get_average_rating() > 0 ) {
                $attr['className'] = !empty($attr['className']) ? preg_replace('/[^A-Za-z0-9_ -]/', '', $attr['className']) : '';

                $wraper_before .= '<div '.(isset($attr['advanceId']) ? 'id="' . sanitize_html_class($attr['advanceId']) . '" ':'') . ' class="wp-block-product-blocks-' . esc_attr($block_name) . ' wopb-block-' . sanitize_html_class($attr["blockId"]) . ' ' . $attr['className'] . '">';
                    $content .= '<div class="wopb-product-wrapper">';
                        if ( $attr['enableLabel'] ) {
                            $content .= '<span class="wopb-rating-label">'.esc_html($attr['reivewText']).'</span>';
                        }
                        ob_start();
                        woocommerce_template_single_rating();
                        if ( $attr['enableOrder'] && $attr['ratingSeparator'] ) {
                            echo '<span class="rating-builder-separator wopb-block-space">'.esc_html($attr['ratingSeparator']).'</span>';
                        }
                        if ( $attr['enableOrder'] ) {
                            echo '<span class="rating-builder-order">'.wp_kses_post($product->get_total_sales()).' '.esc_html($attr['orderLabelText']).'</span>';
                        }
                        $content .= ob_get_clean();
                    $content .= '</div>';
                $wraper_after .= '</div>';   
            }
        }

        return $wraper_before . $content . $wraper_after;
    }

}