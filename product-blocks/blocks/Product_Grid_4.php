<?php
namespace WOPB\blocks;

use WOPB\VariationSwatches;

defined('ABSPATH') || exit;

class Product_Grid_4{

    public function __construct() {
        add_action( 'init', array( $this, 'register' ) );
    }

    public function get_attributes() {
        return array (
            'layout' => '1',
            'productView' => 'grid',
            'contentLayout' => '1',
            'columns' => array('lg' => '3','sm' => '2','xs' => '1'),
            'slidesToShow' => (object) array('lg' => '3','sm' => '2','xs' => '1'),
            'autoPlay' => true,
            'showDots' => true,
            'showArrows' => true,
            'slideSpeed' => '3000',
            'showPrice' => true,
            'showReview' => false,
            'showCart' => true,
            'quickView' => true,
            'showCompare' => true,
            'showOutStock' => true,
            'showInStock' => false,
            'showShortDesc' => false,
            'showSale' => true,
            'showHot' => false,
            'showDeal' => false,
            'showWishList' => true,
            'filterShow' => false,
            'headingShow' => true,
            'paginationShow' => false,
            'catShow' => true,
            'titleShow' => true,
            'showImage' => true,
            'disableFlip' => false,
            'showVariationSwitch' => true,
            'variationSwitchPosition' => 'before_title',
            'queryTax' => 'product_cat',
            'arrowStyle' => 'leftAngle2#rightAngle2',
            'headingText' => 'Product Grid #4',
            'headingURL' => '',
            'headingBtnText' => 'View More',
            'headingStyle' => 'style1',
            'headingTag' => 'h2',
            'headingAlign' => 'left',
            'subHeadingShow' => false,
            'subHeadingText' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer ut sem augue. Sed at felis ut enim dignissim sodales.',
            'saleText' => 'Sale!',
            'saleDesign' => 'text',
            'saleStyle' => 'classic',
            'hotText' => 'Hot',
            'dealText' => 'Days|Hours|Minutes|Seconds',
            'shortDescLimit' => 7,
            'titleTag' => 'h3',
            'cartText' => 'Add to Cart',
            'cartActive' => 'View Cart',
            'enableCatLink' => true,
            'catPosition' => 'none',
            'imgCrop' => 'full',
            'imgAnimation' => 'none',
            'filterType' => 'product_cat',
            'filterText' => 'all',
            'filterCat' => '[]',
            'filterTag' => '["all"]',
            'filterAction' => '[]',
            'filterActionText' => 'Top Sale|Popular|On Sale|Most Rated|Top Rated|Featured|New Arrival',
            'filterMobile' => true,
            'filterMobileText' => 'More',
            'paginationType' => 'pagination',
            'loadMoreText' => 'Load More',
            'paginationText' => 'Previous|Next',
            'paginationNav' => 'textArrow',
            'paginationAjax' => true,
            'queryNumber' => 6,
        );
    }

    public function register() {
        register_block_type( 'product-blocks/product-grid-4',
            array(
                'editor_script' => 'wopb-blocks-editor-script',
                'editor_style'  => 'wopb-blocks-editor-css',
                'render_callback' =>  array( $this, 'content' )
            )
        );
    }

    public function content( $attr, $noAjax = false ) {
        $attr = wp_parse_args( $attr, $this->get_attributes() );

        if ( ! $noAjax ) {
            $paged = is_front_page() ? get_query_var('page') : get_query_var('paged');
            $attr['paged'] = $paged ? $paged : 1;
        }

        $wrapper_main_content = '';
        $block_name = 'product-grid-4';
        $page_post_id = wopb_function()->get_ID();
        $wraper_before = $wraper_after = $post_loop = '';
        $recent_posts = new \WP_Query( wopb_function()->get_query( $attr ) );
        $pageNum = wopb_function()->get_page_number($attr, $recent_posts->found_posts);

        $wishlist = wopb_function()->get_setting('wopb_wishlist') == 'true' ? true : false;
        $wishlist_data = $wishlist ? wopb_function()->get_wishlist_id() : array();
        $compare = wopb_function()->get_setting('wopb_compare') == 'true' ? true : false;
        $compare_data = $compare ? wopb_function()->get_compare_id() : array();
        $quickview = wopb_function()->get_setting('wopb_quickview') == 'true' ? true : false;

        $slider_attr = wc_implode_html_attributes(
            array(
                'data-slidestoshow'  => wopb_function()->slider_responsive_split($attr['slidesToShow']),
                'data-autoplay'      => esc_attr($attr['autoPlay']),
                'data-slidespeed'    => esc_attr($attr['slideSpeed']),
                'data-showdots'      => esc_attr($attr['showDots']),
                'data-showarrows'    => esc_attr($attr['showArrows'])
            )
        );
    
        if ( $recent_posts->have_posts() ) {
            $attr['className'] = !empty($attr['className']) ? preg_replace('/[^A-Za-z0-9_ -]/', '', $attr['className']) : '';
            $attr['align'] = !empty($attr['align']) ? 'align' . preg_replace('/[^A-Za-z0-9_ -]/', '', $attr['align']) : '';

            $switcher = wopb_function()->get_setting('wopb_variation_swatches') == 'true' ? true : false;
            if ( $switcher ) {
                $variation_switcher = new VariationSwatches();
            }
            
            $wraper_before .= '<div '.(isset($attr['advanceId'])?'id="'.sanitize_html_class($attr['advanceId']).'" ':'').' class="wp-block-product-blocks-'.esc_attr($block_name).' wopb-block-'.sanitize_html_class($attr["blockId"]).' '. $attr['className'] . $attr['align'] . '">';
                $wraper_before .= '<div class="wopb-block-wrapper">';

                    if ( $attr['headingShow'] || $attr['filterShow'] ) {
                        $wraper_before .= '<div class="wopb-heading-filter">';
                            $wraper_before .= '<div class="wopb-heading-filter-in">';
                                
                                // Heading
                                include WOPB_PATH . 'blocks/template/heading.php';
                                
                                if ( ($attr['filterShow'] ) && $attr['productView'] == 'grid' ) {
                                    $wraper_before .= '<div class="wopb-filter-navigation">';
                                        if($attr['filterShow']) {
                                            include WOPB_PATH . 'blocks/template/filter.php';
                                        }
                                    $wraper_before .= '</div>';
                                }

                            $wraper_before .= '</div>';
                        $wraper_before .= '</div>';
                    }

                    $wraper_before .= '<div class="wopb-wrapper-main-content">';
                        if ( $attr['productView'] == 'slide' ) {
                            $wrapper_main_content .= '<div class="wopb-product-blocks-slide" '.wp_kses_post($slider_attr).'>';
                        } else {
                            $wrapper_main_content .= '<div class="wopb-block-items-wrap wopb-block-row wopb-block-column-'.(! empty( $attr['columns']['lg'] ) ? intval($attr['columns']['lg']) : 3).'">';
                        }


                            $idx = $noAjax ? 1 : 0;
                            while ( $recent_posts->have_posts() ): $recent_posts->the_post();

                                $image_data = $category_data = $title_data = $price_data = $review_data = $cart_data = $title_review_data = $cat_price_data = $content_data = '';

                                include WOPB_PATH . 'blocks/template/data.php';
                                include WOPB_PATH . 'blocks/template/category.php';

                                if ( $product ) {
                                    $post_loop .= '<div class="wopb-block-item">';
                                        $post_loop .= '<div class="wopb-block-content-wrap '.($attr['layout']?'wopb-pg-l'.esc_attr($attr['layout']):'').'">';

                                            // Variation Switcher
                                            $variationSwitcher_data = '';
                                            if ( $switcher && $attr['showVariationSwitch'] ) {
                                                $variationSwitcher_data = $variation_switcher->loop_variation_form($product);
                                            }

                                                $image_data .= '<div class="wopb-block-image wopb-block-image-'.esc_attr($attr['imgAnimation']).'">';

                                                    if ( $attr["showSale"] || $attr["showHot"] ) {
                                                        $image_data .= '<div class="wopb-onsale-hot">';
                                                            if ( $attr["showSale"] && $product->is_on_sale() ) {
                                                                $image_data .= '<span class="wopb-onsale wopb-onsale-'.esc_attr($attr["saleStyle"]).'">';
                                                                    if($attr["saleDesign"] == 'digit') { $image_data .= '-' . esc_html($_discount); }
                                                                    if($attr["saleDesign"] == 'text') { $image_data .= isset($attr["saleText"]) ? esc_html($attr["saleText"]) : esc_html__('Sale!', 'product-blocks'); }
                                                                    if($attr["saleDesign"] == 'textDigit') { $image_data .= '-' . esc_html($_discount) . esc_html__(' Off', 'product-blocks'); }
                                                                $image_data .= '</span>';
                                                            }
                                                            if ( $attr["showHot"] && $product->is_featured() ) {
                                                                $image_data .= '<span class="wopb-hot">';
                                                                    $image_data .= isset($attr["hotText"]) ? esc_html($attr["hotText"]) : esc_html__('Hot', 'product-blocks');
                                                                $image_data .= '</span>';
                                                            }
                                                        $image_data .= '</div>';
                                                    }

                                                    if ( $attr["quickView"] || $attr["showCompare"] || $attr["showWishList"] || $attr["showCart"] ) {
                                                    $image_data .= '<div class="wopb-product-meta">';
                                                        if ( ($attr["layout"] == '2') && $attr["showCart"] ) {
                                                            $image_data .= wopb_function()->get_add_to_cart($product, $attr['cartText'], $attr['cartActive'], $attr['layout'], array(2, 4), $attr);
                                                        }
                                                        if ( $attr["layout"] == '3' || $attr["layout"] == '4' || $attr["layout"] == '5' || $attr["layout"] == '6' ) {
                                                            if ( $attr["quickView"] && $quickview ) {
                                                                $quick_params = array(
                                                                    'post' => $recent_posts,
                                                                    'post_id' => $post_id,
                                                                    'layout' => $attr['layout'],
                                                                    'position' => array(1, 2, 3, 4),
                                                                    'tooltip' => true,
                                                                );
                                                                $image_data .= wopb_function()->get_quick_view($quick_params);
                                                            }
                                                            if ( $attr["showCompare"] && $compare ) {
                                                                $image_data .= wopb_function()->get_compare($post_id, ['layout' => $attr["layout"], 'position' => array(1, 2, 3, 4)]);
                                                            }
                                                            if ( $attr["layout"] == '4' && $attr["showCart"] ) {
                                                                $image_data .= wopb_function()->get_add_to_cart($product, $attr['cartText'], $attr['cartActive'], $attr['layout'], array(2, 4), $attr);
                                                            }
                                                            if ( $wishlist ) {
                                                                if( ( $attr["layout"] == '5' || $attr["layout"] == '6' ) && $attr["showWishList"] ) {
                                                                    $image_data .= wopb_function()->get_wishlist_html($post_id, $wishlist_active, $attr["layout"], array(1, 2, 3, 4));
                                                                }
                                                            }
                                                        }
                                                    $image_data .= '</div>';
                                                    }

                                                    if ( $attr["layout"] == '1' || $attr["layout"] == '2' || $attr["layout"] == '3' ) {
                                                        $quick_params = array(
                                                            'post' => $recent_posts,
                                                            'post_id' => $post_id,
                                                            'layout' => $attr['layout'],
                                                            'position' => array(1, 2, 3, 4),
                                                            'tooltip' => false,
                                                            'icon' => false,
                                                        );
                                                        $image_data .= '<div class="wopb-quick-cart">';
                                                        if ( ( $attr["layout"] == '1' || $attr["layout"] == '2' )  && $attr['quickView'] && $quickview )  {
                                                            $image_data .= '<div class="wopb-product-btn">';
                                                            $image_data .= wopb_function()->get_quick_view($quick_params);
                                                            $image_data .= '</div>';
                                                        }
                                                        // Add to Cart URL
                                                        if ( $attr["layout"] == '3' && $attr['showCart'] ) {
                                                            $image_data .= '<div class="wopb-product-btn">';
                                                            $image_data .= wopb_function()->get_add_to_cart( $product, $attr['cartText'], $attr['cartActive'], $attr['layout'], array(1,2,3,4), false, $attr );
                                                            $image_data .= '</div>';
                                                        }
                                                        $image_data .= '</div>';
                                                    }

                                                    if ( $attr["showDeal"] ) {
                                                        $image_data .= wopb_function()->get_deals($product, $attr["dealText"]);
                                                    }

                                                    if ( $attr['catPosition'] != 'none' && $attr['catShow'] ) {
                                                        $image_data .= '<div class="wopb-category-img-grid">'.wp_kses_post($category).'</div>';
                                                    }

                                                    if ( $product->get_stock_status() == 'outofstock' && $attr["showOutStock"] ) {
                                                        $image_data .= '<div class="wopb-product-outofstock">';
                                                            $image_data .= '<span>'.esc_html__( "Out of stock", "product-blocks" ).'</span>';
                                                        $image_data .= '</div>';
                                                    } elseif ( $product->get_stock_status() == 'instock' && $attr["showInStock"] ) {
                                                        $image_data .= '<div class="wopb-product-instock">';
                                                        $image_data .= '<span>'.esc_html__( "In Stock", "product-blocks" ).'</span>';
                                                        $image_data .= '</div>';
                                                    }

                                                    // Image
                                                    if ( has_post_thumbnail() && $attr['showImage'] ) {
                                                        $image_data .= '<a href="'.esc_url($titlelink).'"><img alt="'.esc_attr($title).'" src="'.esc_url(wp_get_attachment_image_url( $post_thumb_id, ($attr['imgCrop'] ? $attr['imgCrop'] : 'full') )).'" />';
                                                            if ( ! $attr['disableFlip'] ) {
                                                                $image_data .= wopb_function()->get_flip_image($post_id, $title, $attr['imgCrop']);
                                                            }
                                                        $image_data .= '</a>';
                                                    } else {
                                                        $image_data .='<div class="empty-image">';
                                                            $image_data .= '<a href="'.esc_url($titlelink).'">';
                                                                $image_data .='<img alt='.esc_attr($title).' src="'.esc_url(wc_placeholder_img_src(($attr['imgCrop'] ? $attr['imgCrop'] : 'full'))).'"/>';
                                                            $image_data .= '</a>';
                                                        $image_data .='</div>';
                                                    }
                                                $image_data .= '</div>';

                                                $content_data .= '<div class="wopb-pg-cl '.($attr['contentLayout']?'wopb-pg-cl'.esc_attr($attr['contentLayout']):'').'">';
                                                    if ( $attr['titleShow'] || $attr['showReview'] || $attr['catShow'] || $attr['showPrice'] ) {
                                                        $content_data .= '<div class="wopb-product4-content">';
                                                            if ( $wishlist ) {
                                                                if ( ( $attr["layout"] == '1' || $attr["layout"] == '2' || $attr["layout"] == '3' || $attr["layout"] == '4' ) && $attr["showWishList"] ) {
                                                                    $content_data .= '<div class="wopb-wishlist-btn">';
                                                                    $content_data .= wopb_function()->get_wishlist_html( $post_id, $wishlist_active, $attr["layout"], array(1, 2, 3, 4) );
                                                                    $content_data .= '</div>';
                                                                }
                                                            }
                                                            $content_data .= '<div class="wopb-product4-content-in">';
                                                                // Category
                                                                if ( $attr['catPosition'] == 'none' && $attr['catShow'] ) {
                                                                    $content_data .= wp_kses_post($category);
                                                                }
                                                                // Title
                                                                if ( $attr['titleShow'] ) {
                                                                    include WOPB_PATH . 'blocks/template/title.php';
                                                                    if ( $attr['variationSwitchPosition'] == 'before_title' ) {
                                                                        $content_data .= $variationSwitcher_data;
                                                                    }
                                                                    $content_data .= $title_data;
                                                                    if ( $attr['variationSwitchPosition'] == 'after_title' ) {
                                                                        $content_data .= $variationSwitcher_data;
                                                                    }
                                                                }
                                                                // Review
                                                                if ( $attr['showReview'] ) {
                                                                    include WOPB_PATH . 'blocks/template/review.php';
                                                                    $content_data .= $review_data;
                                                                }
                                                                if ( $attr['showShortDesc'] ) {
                                                                    $content_data .= '<div class="wopb-short-description">'. wopb_function()->excerpt($post_id, $attr['shortDescLimit']) .'</div>';
                                                                }
                                                                $content_data .= '<div class="wopb-pg4-content '.($attr['layout']?'wopb-pg4-content'.esc_attr($attr['layout']):'').'">';
                                                                    // Price
                                                                    $content_data .= '<div class="wopb-pg4-content-product-price-container" >';
                                                                        if($attr['showPrice']){
                                                                            $content_data .= '<div class="wopb-product-price">'.$product->get_price_html().'</div>';
                                                                        }
                                                                    $content_data .= '</div>';

                                                                    if ( ( $attr["layout"] == '1' || $attr["layout"] == '5' || $attr["layout"] == '6' ) && $attr['showCart'] ) {
                                                                        $content_data .= wopb_function()->get_add_to_cart( $product, $attr['cartText'], $attr['cartActive'], $attr['layout'], array(), false, $attr );
                                                                    }
                                                                $content_data .= '</div>';
                                                            $content_data .= '</div>';
                                                        $content_data .= '</div>';
                                                    }
                                                $content_data .= '</div>';

                                                $post_loop .= $image_data.$content_data;

                                        $post_loop .= '</div>';
                                    $post_loop .= '</div>';
                                }
                                $idx ++;
                            endwhile;

                            $wrapper_main_content .= $post_loop;

                            if ( $attr['paginationShow'] && $attr['productView'] == 'grid' && $attr['paginationType'] == 'loadMore' ) {
                                $wrapper_main_content .= '<span class="wopb-loadmore-insert-before"></span>';
                            }
                        $wrapper_main_content .= '</div>';//wopb-block-items-wrap

                        // Load More
                        if ( $attr['paginationShow'] && $attr['productView'] == 'grid' && $attr['paginationType'] == 'loadMore' ) {
                            include WOPB_PATH . 'blocks/template/loadmore.php';
                        }

                        // Pagination
                        if ( $attr['paginationShow'] && $attr['productView'] == 'grid' && $attr['paginationType'] == 'pagination' ) {
                            include WOPB_PATH . 'blocks/template/pagination.php';
                        }

                        if ( $attr['productView'] == 'slide' && $attr['showArrows'] ) {
                            include WOPB_PATH . 'blocks/template/arrow.php';
                        }

                    $wraper_after .= '</div>';//wopb-wrapper-main-content
                $wraper_after .= '</div>';
            $wraper_after .= '</div>';

            wp_reset_query();
        }

        if ( $noAjax && $attr['ajax_source'] == 'filter' ) {
            if ( $post_loop === '' ) {
                $wrapper_main_content .= '<span class="wopb-no-product-found">' . __('No products were found of your matching selection', 'product-blocks') . '</span>';
            }
            return $wrapper_main_content;
        }

        return $noAjax ? $post_loop : $wraper_before.$wrapper_main_content.$wraper_after;
    }

}