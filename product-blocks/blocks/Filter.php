<?php
namespace WOPB\blocks;

defined('ABSPATH') || exit;

class Filter {

    public function __construct() {
        add_action( 'init', array( $this, 'register' ) );
        
        add_action( 'wc_ajax_wopb_show_more_filter_item', array( $this, 'wopb_show_more_filter_item_callback' ) );
		add_action( 'wp_ajax_nopriv_wopb_show_more_filter_item', array( $this, 'wopb_show_more_filter_item_callback' ) );
    }
    
    public function get_attributes() {
        return array (
            'repeatableFilter' => array (
              0 => array('type' => 'search','label' => 'Filter By Search'),
              1 => array('type' => 'price','label' => 'Filter By Price'),
              2 => array('type' => 'product_cat','label' => 'Filter By Category'),
              3 => array('type' => 'status','label' => 'Filter By Status'),
              4 => array('type' => 'rating','label' => 'Filter By Rating')
             ),
            'sortingItems' => array (
              0 => (object) array('label' => 'Select Sort By','value' => ''),
              1 => (object) array('label' => 'Default Sorting','value' => 'default'),
              2 => (object) array('label' => 'Sort by popularity','value' => 'popular'),
              3 => (object) array('label' => 'Sort by latest','value' => 'latest'),
              4 => (object) array('label' => 'Sort by average rating','value' => 'rating'),
              5 => (object) array('label' => 'Sort by price: low to high','value' => 'price_low'),
              6 => (object) array('label' => 'Sort by price: high to low','value' => 'price_high')
             ),
            'blockTarget' => '',
            'clearFilter' => true,
            'filterHeading' => true,
            'productCount' => true,
            'expandTaxonomy' => false,
            'enableTaxonomyRelation' => false,
            'viewTaxonomyLimit' => 10,
            'togglePlusMinus' => true,
            'togglePlusMinusInitialOpen' => true,
            'toggleInitialMobile' => false,
            'filterHeadText' => 'Filter',
            'currentPostId' =>  '',
        );
    }

    public function register() {
        register_block_type( 'product-blocks/filter',
            array(
                'editor_script' => 'wopb-blocks-editor-script',
                'editor_style'  => 'wopb-blocks-editor-css',
                'render_callback' =>  array( $this, 'content' )
            )
        );
    }

    /**
     * This
     * @return string
     */
    public function content($attr, $noAjax = false) {
        $attr = wp_parse_args( $attr, $this->get_attributes() );

        $is_active = wopb_function()->get_setting( 'is_lc_active' );
        if ( ! $is_active ) { // Expire Date Check
            $start_date = get_option( 'edd_wopb_license_expire' );
            $is_active = ( $start_date && ( $start_date == 'lifetime' || strtotime( $start_date ) ) ) ? true : false;
        }

        $page_post_id = wopb_function()->get_ID();
        if ( $is_active && $post = get_post($page_post_id) ) {
            $is_mobile = wp_is_mobile();
            $html = $wraper_before = '';
            $block_name = 'filter';
            $blocks = parse_blocks($post->post_content);
            $target_block_attr = [];
            $target_block_attr = $this->getTargetBlockAttributes($attr, $blocks, $target_block_attr);
            if( empty( $target_block_attr['queryTax'] ) ) {
                $target_block_attr['queryTax'] = 'product_cat';
            }
            $attr['headingShow'] = true;
            $active_filters = $attr['repeatableFilter'];
            $wrapper_class = '';
            $wrapper_class .= 'wopb-filter-block-front-end ';
            if( $attr['togglePlusMinus'] ) {
                if( ! $attr['togglePlusMinusInitialOpen'] || ( $is_mobile && $attr['toggleInitialMobile'] ) ) {
                    $wrapper_class .= ' wopb-filter-toggle-initial-close';
                }elseif( $attr['togglePlusMinusInitialOpen'] ) {
                    $wrapper_class .= ' wopb-filter-toggle-initial-open';
                }
            }

            $attr['className'] = !empty($attr['className']) ? preg_replace('/[^A-Za-z0-9_ -]/', '', $attr['className']) : '';
            $attr['blockTarget'] = !empty($attr['blockTarget']) ? sanitize_html_class($attr['blockTarget']) : '';

            $wraper_before .= '<div '.(isset($attr['advanceId'])?'id="'.sanitize_html_class($attr['advanceId']).'" ':'').' class="wp-block-product-blocks-' . esc_attr($block_name) . ' wopb-block-' . sanitize_html_class($attr["blockId"]) . ' ' . $attr["className"] . '">';
                $wraper_before .= '<div class="wopb-product-wrapper wopb-filter-block ' . $wrapper_class . '" data-postid = "' . $page_post_id . '" data-block-target = "' . $attr['blockTarget'] . '" data-current-url="' . get_pagenum_link() . '">';

                if ( $attr['filterHeading'] || $is_mobile ) {
                    $html .= '<div class="wopb-filter-title-section">';
                        $html .= '<span class="wopb-filter-title">'.wp_kses($attr['filterHeadText'], wopb_function()->allowed_html_tags()).'</span>';
                        $html .= '<span class="dashicons dashicons-filter wopb-filter-icon"></span>';
                    $html .= '</div>';
                }
                if ( $attr['clearFilter'] ) {
                    ob_start();
                    $this->removeFilterItem();
                    $html .= ob_get_clean();
                }
                $filter_content = $this->filter_content($active_filters, $target_block_attr, $attr);
                $html .= ! $is_mobile ? $filter_content : '';
                $wraper_after = '</div>';
            $wraper_after .= '</div>';

            if( $is_mobile ) {
                $modal_content = $wraper_before . $html . $filter_content . $wraper_after;
                add_action( 'wopb_footer', function () use( $modal_content ) {
                    echo $this->filter_in_footer($modal_content);
                } );
            }
            return $wraper_before . $html . $wraper_after;
        }
    }

    /**
     * Filter in Footer Hook
     *
     * @param $filter_content
     * @return string
     * @since v.4.0.6
     */
    public function filter_in_footer($filter_content) {
        $filter_modal = '';
        $filter_modal .= '<div class="wopb-filter-modal">';
            $filter_modal .= '<div class="wopb-modal-overlay"></div>';
            $filter_modal .= '<div class="wopb-filter-content">';
                $filter_modal .= '<div class="wopb-modal-header">';
                    $filter_modal .= '<a class="wopb-modal-close">';
                        $filter_modal .= wopb_function()->svg_icon( 'close' );
                    $filter_modal .= '</a>';
                    $filter_modal .= '<span class="wopb-modal-title">';
                        $filter_modal .= __('Filter', 'product-blocks');
                    $filter_modal .= '</span>';
                $filter_modal .= '</div>';
            $filter_modal .= $filter_content;
            $filter_modal .= '</div>';
        $filter_modal .= '</div>';
        return $filter_modal;
    }

    /**
     * Filter Content
     *
     * @since v.4.0.6
     * @return string
     */
    public function filter_content($active_filters, $target_block_attr, $attr) {
        $tax_count = 0;
        $html = '<form autocomplete="off" action="javascript:">';
            foreach ( $active_filters as $active_filter ) {
                $params = [
                    'headerLabel' => !empty($active_filter['label']) ? $active_filter['label'] : '',
                    'target_block_attr' => $target_block_attr
                ];

                switch ( $active_filter['type'] ) {
                    case 'search':
                        ob_start();
                        $this->search_filter( $attr, $params );
                        $html .= ob_get_clean();
                        break;
                    case 'price':
                        ob_start();
                        $this->price_filter( $attr, $params );
                        $html .= ob_get_clean();
                        break;
                    case 'status':
                        ob_start();
                        $this->status_filter( $attr, $params );
                        $html .= ob_get_clean();
                        break;
                    case 'rating':
                        ob_start();
                        $this->rating_filter( $attr, $params );
                        $html .= ob_get_clean();
                        break;
                    case 'sort_by':
                        ob_start();
                        $this->sorting_filter( $attr, $params );
                        $html .= ob_get_clean();
                        break;

                    default:
                        global $wp_query;
                        $query_vars = $wp_query->query_vars;
                        $attr['viewTaxonomyLimit'] = !empty($attr['viewTaxonomyLimit']) ? intval($attr['viewTaxonomyLimit']) : 10;
                        $object_taxonomies =  array_diff(get_object_taxonomies('product'), ['product_type', 'product_visibility', 'product_shipping_class']);
                        $queried_object = get_queried_object();

                        foreach ($object_taxonomies as $key) {
                            $taxonomy = get_taxonomy($key);
                            $exclude_terms = [];

                            if ( $taxonomy->name === $active_filter['type'] ) {
                                $term_query = array (
                                    'taxonomy' => $key,
                                    'hide_empty' => true,
                                );
                                if ( is_product_category() && $key == 'product_cat' ) {
                                    $term_query['parent'] = $queried_object->term_id;
                                } elseif ( $key !== 'product_tag' ) {
                                    $term_query['parent'] = 0;
                                }

                                if ( wopb_function()->get_attribute_by_taxonomy( $key ) ) {
                                    $taxonomy->attribute = wopb_function()->get_attribute_by_taxonomy( $key );
                                    unset( $term_query['parent'] );
                                }
                                $taxonomy->total_terms = count( get_terms( $term_query ) );
                                $term_query['number'] = $attr['viewTaxonomyLimit'];
                                $params['hiddenTermCount'] = 0;

                                if ( $term_query['taxonomy']==='product_cat' ) {
                                    if ( isset( $query_vars['__wholesalex_exclude_cat'] ) && !empty( $query_vars['__wholesalex_exclude_cat'] ) ) {
                                        $term_query['exclude'] = $query_vars['__wholesalex_exclude_cat']; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
                                    }
                                    if ( isset( $query_vars['__wholesalex_include_cat'] ) && ! empty( $query_vars['__wholesalex_include_cat'] ) ) {
                                        $term_query['include'] = $query_vars['__wholesalex_include_cat'];
                                    }
                                }
                                if( ! empty( $target_block_attr['queryTaxValue'] ) && $key == $target_block_attr['queryTax'] ) { //Merge taxonomy from selected grid
                                    $tax_value = json_decode( $target_block_attr['queryTaxValue'] );
                                    if ( ! empty( $tax_value ) && is_array( $tax_value ) ) {
                                        foreach ( $tax_value as $tax ) {
                                            if( isset( $tax->value ) ) {
                                                $term_query['slug'][] = $tax->value;
                                                unset( $term_query['parent'] );
                                            }
                                        }
                                    }
                                }
                                if ( is_product_taxonomy() || ( isset( $query_vars['post__not_in'] ) && ! empty( $query_vars['post__not_in'] ) ) ) {
                                    if ( is_product_taxonomy() ) {
                                        $attr['is_product_taxonomy'] = true;
                                        $attr['query_obj_taxonomy'] = $queried_object->taxonomy;
                                        $attr['query_obj_term'] = $queried_object->term_id;
                                    }
                                    if ( isset( $query_vars['post__not_in'] ) && ! empty( $query_vars['post__not_in'] ) ) {
                                        $attr['post__not_in'] = $query_vars['post__not_in']; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
                                    }
                                    $exclude_terms = $this->exclude_terms( get_terms( $term_query ), $attr );
                                    $params['hiddenTermCount'] = count( $exclude_terms ) > 0 ? count( $exclude_terms ) : $params['hiddenTermCount'];
                                    $taxonomy->total_terms = $taxonomy->total_terms - $params['hiddenTermCount'];
                                }
                                $taxonomy->terms = get_terms($term_query);
                                $params['taxonomy'] = $taxonomy;
                                if ( ! empty( $term_query['parent'] ) ) {
                                    $params['parent_id'] = $term_query['parent'];
                                }
                                if ( $taxonomy->terms ) {
                                    $tax_count++;
                                    $params['tax_count'] = $tax_count;
                                    ob_start();
                                    $this->product_taxonomy_filter($attr, $params);
                                    $html .= ob_get_clean();
                                }
                            }
                        }
                }
            }

            ob_start();
                $this->reset_filter( $attr );
            $html .= ob_get_clean();
        $html .= '</form>';
        return $html;
    }

    public function removeFilterItem() {
?>
        <div class="wopb-filter-remove-section">
            <div class="wopb-filter-active-item-list">
            </div>
            <span class="wopb-filter-remove-all">
                <?php esc_html_e('Clear All', 'product-blocks') ?> <span class="dashicons dashicons-no-alt wopb-filter-remove-icon">
            </span>
        </div>
<?php
    }

    public function filter_header_content ($attr, $params) {
?>
        <div class="wopb-filter-header">
            <span class="wopb-filter-label">
                <?php echo wp_kses_post( $params['headerLabel'] ) ?>
            </span>
            <?php if($attr['togglePlusMinus']) { ?>
                <div class="wopb-filter-toggle">
                    <span class="dashicons dashicons-plus-alt2 wopb-filter-plus"></span>
                    <span class="dashicons dashicons-minus wopb-filter-minus"></span>
                </div>
            <?php } ?>
        </div>
<?php
    }

    public function search_filter($attr, $params) {
?>
        <div class="wopb-filter-section wopb-filter-search">
            <?php $this->filter_header_content($attr, $params); ?>
            <div class="wopb-filter-body">
                <input type="hidden" class="wopb-filter-slug" value="search">
                <div class="wopb-search-filter-body">
                    <input type="text" class="wopb-filter-search-input" placeholder="<?php echo esc_html__('Search Products', 'product-blocks') ?>..."/>
                    <span class="wopb-search-icon">
                        <img src="<?php echo esc_url(WOPB_URL); ?>/assets/img/svg/search.svg" alt="<?php echo esc_html__('Image', 'product-blocks')?>" />
                    </span>
                </div>
            </div>
        </div>
<?php
    }

    public function price_filter($attr, $params) {
        $highest_price = $this->get_highest_price();
?>
        <div class="wopb-filter-section wopb-filter-price">
            <?php $this->filter_header_content($attr, $params); ?>

            <div class="wopb-filter-body wopb-price-range-slider">
                <input type="hidden" class="wopb-filter-slug" value="price">
                <div class="wopb-price-range">
                    <span class="wopb-price-range-bar"></span>
                    <input type="range" class="wopb-price-range-input wopb-price-range-input-min" min="0" max="<?php echo esc_attr($highest_price) ?>" value="0" step="1">
                    <input type="range" class="wopb-price-range-input wopb-price-range-input-max" min="0" max="<?php echo esc_attr($highest_price) ?>" value="<?php echo esc_attr($highest_price) ?>" step="1">
                </div>
                <span class="wopb-filter-price-input-group">
                    <input type="number" class="wopb-filter-price-input wopb-filter-price-min" value="0" min="0">
                    <input type="number" class="wopb-filter-price-input wopb-filter-price-max" value="<?php echo esc_attr($highest_price) ?>" min="0" max="<?php echo esc_attr($highest_price) ?>">
                </span>
            </div>
        </div>
<?php
    }

    public function status_filter($attr, $params) {
        $stock_params = [];
        $queried_object = get_queried_object();
        if(is_product_taxonomy()) {
            $stock_params['taxonomy'] = $queried_object->taxonomy;
            $stock_params['taxonomy_term_id'] = $queried_object->term_id;
        }
        $stock_params['target_block_attr'] = $params['target_block_attr'];
?>
        <div class="wopb-filter-section wopb-filter-status">
            <?php $this->filter_header_content($attr, $params); ?>

            <div class="wopb-filter-body">
                <input type="hidden" class="wopb-filter-slug" value="status">
                <div class="wopb-filter-check-list">
                    <?php
                        foreach (wc_get_product_stock_status_options() as $key => $status) {
                            $count = wopb_function()->generate_stock_status_count_query($key, $stock_params);
//                            if($count > 0) {
                    ?>
                        <div class="wopb-filter-check-item-section">
                            <div class="wopb-filter-check-item">
                                <label for="status_<?php echo esc_attr($key) ?>">
                                    <input type="checkbox" class="wopb-filter-status-input" id="status_<?php echo esc_attr($key) ?>" value="<?php echo esc_attr($key) ?>"/>
                                    <?php echo esc_html($status) ?> <?php echo $attr['productCount'] ? esc_html('(' . $count .')') : '' ?>
                                </label>
                            </div>
                        </div>
                    <?php } /*}*/ ?>
                </div>
            </div>
        </div>
<?php
    }

    public function rating_filter($attr, $params) {
?>
        <div class="wopb-filter-section wopb-filter-rating">
            <?php $this->filter_header_content($attr, $params); ?>

            <div class="wopb-filter-body">
                <input type="hidden" class="wopb-filter-slug" value="rating">
                <div class="wopb-filter-check-list wopb-filter-ratings">
                    <?php for ($row = 5; $row > 0; $row--) { ?>
                        <div class="wopb-filter-check-item-section">
                            <div class="wopb-filter-check-item">
                                <label for="filter-rating-<?php echo esc_attr($row) ?>">
                                    <input type="checkbox" class="wopb-filter-rating-input" value="<?php echo esc_attr($row) ?>" id="filter-rating-<?php echo esc_attr($row) ?>">
                                    <?php for ($filledStar = $row; $filledStar > 0; $filledStar--) { ?>
                                        <span class="dashicons dashicons-star-filled"></span>
                                    <?php } ?>
                                    <?php for ($emptyStar = 0; $emptyStar < 5- $row; $emptyStar++) { ?>
                                        <span class="dashicons dashicons-star-empty"></span>
                                    <?php } ?>
                                </label>
                            </div>
                        </div>
                   <?php } ?>
                </div>
            </div>
        </div>
<?php
    }

    public function taxonomy_relation() {
?>
        <div class="wopb-taxonomy-relation">
            <div class="wopb-relation-heading"><?php echo esc_html__('Taxonomy Relation', 'product-blocks'); ?></div>
            <div class="wopb-filter-body">
                <input type="hidden" class="wopb-filter-slug" value="tax_relation">
                <label for="wopb_tax_relation_and">
                    <input name="tax_relation" type="radio" class="wopb-filter-tax-relation" id="wopb_tax_relation_and" value="AND" checked>
                    <span>AND</span>
                </label>
                <label for="wopb_tax_relation_or">
                    <input name="tax_relation" type="radio" class="wopb-filter-tax-relation" id="wopb_tax_relation_or" value="OR">
                    <span>OR</span>
                </label>
            </div>
        </div>
<?php
    }

     public function product_taxonomy_filter($attr, $params) {

         if(is_search()) {
             $attr['is_search'] = is_search();
             $attr['search_query'] = get_search_query();
         }
         if($attr['enableTaxonomyRelation'] && $params['tax_count'] == 1) {
             $this->taxonomy_relation();
         }
?>
        <div class="wopb-filter-section<?php echo isset($params['taxonomy']->name) ? ' wopb-filter-' . esc_attr( $params['taxonomy']->name ) : '' ?>">
            <?php
                $this->filter_header_content($attr, $params);
            ?>

            <div class="wopb-filter-body">
                <input
                    type="hidden"
                    class="wopb-filter-slug"
                    value="product_taxonomy"
                    data-taxonomy="<?php echo esc_attr($params['taxonomy']->name) ?>"
                    data-parent="<?php echo ! empty( $params['parent_id'] ) ? esc_attr( $params['parent_id'] ) : '' ?>"
                    data-term-limit="<?php echo esc_attr($attr['viewTaxonomyLimit']) ?>"
                    data-attributes="<?php echo esc_attr(wp_json_encode($attr)) ?>"
                    data-target-block-attributes="<?php echo esc_attr(wp_json_encode($params['target_block_attr'])) ?>"
                />
                <div class="wopb-filter-check-list">
                    <?php
                        !empty($params['taxonomy']) ? $this->product_taxonomy_terms($attr, $params) : '';
                    ?>
                </div>
                <?php
                    if( $params['taxonomy']->total_terms > $attr['viewTaxonomyLimit']) {
                        $item_total_page = $params['taxonomy']->total_terms / $attr['viewTaxonomyLimit'];
                        $item_total_page = ceil((float)$item_total_page);

                ?>
                        <a href="javascript:" class="wopb-filter-extend-control wopb-filter-show-more" data-item-page="1" data-item-total-page="<?php echo esc_attr($item_total_page); ?>">
                            <?php esc_html_e('Show More', 'product-blocks') ?>
                        </a>
                        <a href="javascript:" class="wopb-filter-extend-control wopb-filter-show-less" data-item-page="1">
                            <?php esc_html_e('Show Less', 'product-blocks') ?>
                        </a>
                <?php } ?>
            </div>
        </div>
<?php
    }



    public function product_taxonomy_terms($attr, $params) {
        global $wp_query;
        $query_vars = $wp_query->query_vars;
        $taxonomy = $params['taxonomy'];
?>
        <?php
            $key = 0;
            foreach ($taxonomy->terms as $term) {
                $key++;
                 $child_term_query = array (
                    'taxonomy' => $taxonomy->name,
                    'hide_empty' => true,
                    'parent' => $term->term_id,
                    'number' => $attr['viewTaxonomyLimit']
                );
                $query_args = array(
                    'posts_per_page' => -1,
                    'post_type' => 'product',
                    'post_status' => 'publish',
                );
                if(isset($attr['is_search']) && $attr['is_search']) {
                    $query_args['s'] = $attr['search_query'];
                }
                $term->child_terms = get_terms($child_term_query);
                $tax_query = [];
                $tax_query[] = array(
                    'taxonomy' => $taxonomy->name,
                    'field' => 'term_id',
                    'terms'    => $term->term_id,
                );
                $tax_query[] = array(
                    'taxonomy' => 'product_visibility',
                    'field' => 'name',
                    'terms' => 'exclude-from-catalog',
                    'operator' => 'NOT IN',
                );
                if(isset($attr['is_product_taxonomy']) && $attr['is_product_taxonomy']) {
                    $tax_query[] = [
                        'taxonomy' => $attr['query_obj_taxonomy'],
                        'field' => 'id',
                        'terms' => $attr['query_obj_term'],
                        'operator' => 'IN'
                    ];
                }
                if( isset($params['target_block_attr']['queryCat']) && $params['target_block_attr']['queryCat'] ) {
                    $tax_query[] = array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => json_decode(stripslashes($params['target_block_attr']['queryCat'])),
                        'operator' => 'IN',
                    );
                }
                if( ! empty( $params['target_block_attr']['queryTaxValue'] ) ) {
                    $tax_value = json_decode( $params['target_block_attr']['queryTaxValue'] );
                    if ( ! empty( $tax_value ) && is_array( $tax_value ) ) {
                        $query_tax = ['relation' => 'or'];
                        foreach ( $tax_value as $tax ) {
                            if( isset( $tax->value ) ) {
                                $query_tax[] = array(
                                    'taxonomy' => $params['target_block_attr']['queryTax'],
                                    'field' => 'slug',
                                    'terms' => $tax->value,
                                    'operator' => 'IN',
                                );
                            }
                        }
                        $tax_query[] = $query_tax;
                    }
                }
                $query_args['tax_query'] = $tax_query;
                if(isset($query_vars['post__not_in']) && !empty($query_vars['post__not_in'])) {
                    $query_args['post__not_in'] = $query_vars['post__not_in']; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
                }elseif(isset($attr['post__not_in']) && !empty($attr['post__not_in'])) {
                    $query_args['post__not_in'] = $attr['post__not_in']; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
                }
                $recent_posts = new \WP_Query( $query_args);
                $term->product_count = count($recent_posts->posts);
                $extended_item_class = isset($params['show_more']) ? 'wopb-filter-extended-item' : '';
                if (isset($params['term_type']) && $params['term_type'] == 'child' && !$term->product_count) {
                    continue;
                }
        ?>
            <div class="wopb-filter-check-item-section <?php echo esc_attr($extended_item_class) ?>"
                 data-hidden-term-count="<?php echo esc_attr($params['hiddenTermCount']) ?>">
                <div class="wopb-filter-check-item">
                    <label for="tax_term_<?php echo esc_attr($term->name . '_' . $term->term_id) ?>">
                        <input
                            type="checkbox"
                            class="wopb-filter-tax-term-input"
                            id="tax_term_<?php echo esc_attr($term->name . '_' . $term->term_id) ?>"
                            value="<?php echo esc_attr($term->term_id) ?>"
                            data-label="<?php echo esc_attr($term->name) ?>"
                        />
                        <?php
                        if( isset( $taxonomy->attribute ) && $taxonomy->attribute->attribute_type ) {
                            $attr_value = get_term_meta($term->term_id, $taxonomy->attribute->attribute_type, true);
                            if ( $taxonomy->attribute->attribute_type === 'color' ) {
                                $color_html = $attr_value ? '<span class="wopb-filter-tax-color" style="background-color: ' . esc_attr($attr_value) . ';"></span>' : '';
                                echo $color_html; //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
                            }elseif ( $taxonomy->attribute->attribute_type === 'image' ) {
                                if( $attr_value ) {
                                    echo wp_get_attachment_image($attr_value);
                                }else {
                                    echo '<img src="' . WOPB_URL . '/assets/img/fallback.svg" />';
                                }
                            }
                        }
                        ?>
                       <span><?php echo esc_html($term->name) ?> <?php echo $attr['productCount'] ? esc_html('(' . $term->product_count .')') : '' ?></span>
                    </label>
                    <?php
                        if($term->child_terms) {
                            $params['taxonomy']->terms = $term->child_terms;
                            $params['term_type'] = 'child';
                    ?>
                         <div class="wopb-filter-check-list wopb-filter-child-check-list<?php
                                isset($attr['expandTaxonomy']) && $attr['expandTaxonomy'] == 'true'
                                    ? ''
                                    : esc_attr_e(' wopb-d-none')
                            ?>"
                        >
                            <?php $this->product_taxonomy_terms($attr, $params);?>
                        </div>
                    <?php } ?>
                </div>
                <?php if($term->child_terms) { ?>
                    <div class="wopb-filter-child-toggle">
                        <span class="dashicons dashicons-arrow-right-alt2 wopb-filter-right-toggle<?php echo $attr['expandTaxonomy'] == 'true' ? ' wopb-d-none' : '' ?>"></span>
                        <span class="dashicons dashicons-arrow-down-alt2 wopb-filter-down-toggle<?php echo $attr['expandTaxonomy'] == 'true' ? '' : ' wopb-d-none' ?>"></span>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
<?php

    }

    public function sorting_filter($attr, $params) {
?>
        <div class="wopb-filter-section wopb-filter-sorting">
            <?php $this->filter_header_content($attr, $params); ?>

            <div class="wopb-filter-body">
                <input type="hidden" class="wopb-filter-slug" value="sorting">
                <select name="sortBy" class="select wopb-filter-sorting-input">
                    <?php foreach ($attr['sortingItems'] as $item) { ?>
                        <option value="<?php echo esc_attr($item->value)?>">
                            <?php echo esc_html__($item->label, 'product-blocks')?>
                        </option>
                   <?php } ?>
                </select>
            </div>
        </div>
<?php
    }

    public function reset_filter($attr) {
        $queried_object = get_queried_object();
        $slug = '';
        $current_page_value = '';
        $taxonomy = '';
        if(is_product_taxonomy()) {
            $slug = 'product_taxonomy';
            $current_page_value = $queried_object->term_id;
            $taxonomy = $queried_object->taxonomy;
        }elseif (is_search()) {
            $slug = 'product_search';
            $current_page_value = get_search_query();
        }
?>
        <div class="wopb-filter-section wopb-filter-reset-section">
            <div class="wopb-filter-body">
                <input type="hidden" class="wopb-filter-slug wopb-filter-slug-reset wopb-d-none" value="reset">
                <?php if(isset($slug)) { ?>
                    <input type="hidden" class="wopb-filter-current-page wopb-d-none" value="<?php echo esc_attr($current_page_value); ?>" data-slug="<?php echo esc_attr($slug); ?>" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                <?php } ?>
            </div>
        </div>
<?php
    }

    public function get_highest_price() {
        $queried_object = get_queried_object();
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
        );

        if(is_product_taxonomy()) {
            $args['tax_query'][] = [
                'taxonomy' => $queried_object->taxonomy,
                'field' => 'id',
                'terms' => $queried_object->term_id,
                'operator' => 'IN'
            ];
        }

        $query = new \WP_Query($args);
        $max_price = '';
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product = wc_get_product(get_the_ID());
                if($product->is_type('variable')) {
                    if($product->get_stock_status() != 'outofstock') {
                        $variation_ids = $product->get_children();
                        foreach ($variation_ids as $variation_id) {
                            $variation = wc_get_product($variation_id);
                            if( $variation ) {
                                if ( $variation->get_sale_price() ) {
                                    $max_price = $max_price < $variation->get_sale_price() ? $variation->get_sale_price() : $max_price;
                                } else {
                                    $max_price = $max_price < $variation->get_regular_price() ? $variation->get_regular_price() : $max_price;
                                }
                            }
                        }
                    }
                }else {
                    if($product->get_sale_price()) {
                        $max_price = $max_price < $product->get_sale_price() ? $product->get_sale_price() : $max_price;
                    }else {
                        $max_price = $max_price < $product->get_regular_price() ? $product->get_regular_price() : $max_price;
                    }
                }
            }
        }
        wp_reset_postdata();
        return ceil((float)$max_price);
    }


    /**
	 * Show more filter item by ajax
     *
     * @since v.2.5.3
	 * @return HTML
	 */
    public function wopb_show_more_filter_item_callback() {
        //phpcs:disable WordPress.Security.NonceVerification.Missing
        $attr = isset( $_POST['attributes'])? $_POST['attributes']:array();
        $taxonomy = new \stdClass();
        $taxonomy->name = isset($_POST['taxonomy'])? sanitize_text_field($_POST['taxonomy']):'';
        $exclude_terms = [];
        $params = [
            'taxonomy' => $taxonomy,
            'show_more' => true,
            'target_block_attr' => $_POST['target_block_attr'],
            'hiddenTermCount' => isset($_POST['hiddenTermCount'])?sanitize_text_field($_POST['hiddenTermCount']):0,
        ];
        $term_query = array (
            'taxonomy' => $taxonomy->name,
            'hide_empty' => true,
        );

        if(wopb_function()->get_attribute_by_taxonomy($taxonomy->name)) {
            $taxonomy->attribute = wopb_function()->get_attribute_by_taxonomy($taxonomy->name);
        }elseif($taxonomy->name !== 'product_tag') {
            $term_query['parent'] = 0;
        }
        if( ! empty( $_POST['parent'] ) ) {
            $term_query['parent'] = $_POST['parent'];
        }
        $term_offset = ((isset($_POST['item_page'])?sanitize_text_field($_POST['item_page']):1) - 1) * (isset($_POST['term_limit'])? sanitize_text_field( $_POST['term_limit']):1);
        $term_offset = $params['hiddenTermCount'] > 0 ? $term_offset + $params['hiddenTermCount'] : $term_offset;
        $term_query = array_merge(
            [
                'offset' => $term_offset,
                'number' => isset($_POST['term_limit'])? sanitize_text_field($_POST['term_limit']):1
            ], $term_query);
        if((isset($attr['is_product_taxonomy']) && $attr['is_product_taxonomy']) || (isset($attr['post__not_in']) && !empty($attr['post__not_in']))) {
            $exclude_terms = $this->exclude_terms(get_terms($term_query), $attr);
            $params['hiddenTermCount'] = count($exclude_terms) > 0 ? count($exclude_terms) : $params['hiddenTermCount'];
        }

        $taxonomy->terms = get_terms($term_query);
        if(count($taxonomy->terms) == 0) {
            return false;
        }
        echo $this->product_taxonomy_terms($attr, $params); //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        wp_die();
    }

    /**
     * Get targeted filter block attribute
     *
     * @param $attr
     * @param $blocks
     * @param $target_block_attr
     * @return array
     * @since v.2.5.4
     */
    public function getTargetBlockAttributes ($attr, $blocks, &$target_block_attr) {
        foreach ($blocks as $block) {
            if($block['blockName'] == 'product-blocks/'.$attr['blockTarget'] ) {
                $target_block_attr = $block['attrs'];
            } elseif (count($block['innerBlocks']) > 0) {
                $this->getTargetBlockAttributes($attr, $block['innerBlocks'], $target_block_attr);
            }
        }
        return $target_block_attr;
    }

    /**
     * Exclude any terms of taxonomy
     *
     * @param $terms
     * @param $attr
     * @return array
     * @since v.3.1.0
     */
    public function exclude_terms($terms, $attr = []) {
        $exclude_args = [];
        foreach($terms as $key => $term) {
            $query_args = array(
                'posts_per_page' => -1,
                'post_type' => 'product',
                'post_status' => 'publish',
            );
            $query_args['tax_query'][] = array(
                'taxonomy' => $term->taxonomy,
                'field' => 'term_id',
                'terms'    => $term->term_id,
            );
            $query_args['tax_query'][] = array(
                'taxonomy' => 'product_visibility',
                'field' => 'name',
                'terms' => 'exclude-from-catalog',
                'operator' => 'NOT IN',
            );
            if(isset($attr['is_product_taxonomy']) && $attr['is_product_taxonomy']) {
                $query_args['tax_query'][] = [
                    'taxonomy' => $attr['query_obj_taxonomy'],
                    'field' => 'id',
                    'terms' => $attr['query_obj_term'],
                    'operator' => 'IN'
                ];
            }

            if(isset($attr['post__not_in']) && !empty($attr['post__not_in'])) {
                $query_args['post__not_in'] = $attr['post__not_in']; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
            }
            $recent_posts = new \WP_Query( $query_args);
            if(count($recent_posts->posts) == 0) {
                $exclude_args[] = $term->term_id;
            }
        }

        if(!empty($exclude_args)) {
            add_filter( 'get_terms_args', function ($args) use($exclude_args) {
                $args['exclude'] = $exclude_args; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
                return $args;
            } );
        }
        return $exclude_args;
    }
}