<?php
namespace WOPB\blocks;

defined('ABSPATH') || exit;

class Filter {

    public function __construct() {
        add_action( 'init', array( $this, 'register' ) );

        add_action( 'wc_ajax_wopb_show_more_filter_item', array( $this, 'show_more_callback' ) );
		add_action( 'wp_ajax_nopriv_wopb_show_more_filter_item', array( $this, 'show_more_callback' ) );
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
    public function content($attr) {
        $attr = wp_parse_args( $attr, $this->get_attributes() );

        $is_active = wopb_function()->get_setting( 'is_lc_active' );
        if ( ! $is_active ) { // Expire Date Check
            $start_date = get_option( 'edd_wopb_license_expire' );
            $is_active = ( $start_date && ( $start_date == 'lifetime' || strtotime( $start_date ) ) ) ? true : false;
        }

        $page_post_id =  ! empty($attr['currentPostId']) ? $attr['currentPostId'] : wopb_function()->get_ID();
        if ( $is_active && $post = get_post($page_post_id) ) {
            $is_mobile = wp_is_mobile();
            $html = $wraper_before = '';
            $block_name = 'filter';
            $attr['headingShow'] = true;
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
                $filter_content = $this->filter_content( $attr, $post );
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
    public function filter_content($attr, $post) {
        $counter = 0;
        $blocks = parse_blocks($post->post_content);
        $target_block_attr = [];
        $target_block_attr = $this->getTargetBlockAttributes($attr, $blocks, $target_block_attr);
        $queried_object = is_product_taxonomy() ? get_queried_object() : '';
        $query_term = array_merge(
            array(
                'query_tax' => $queried_object && $queried_object->taxonomy ? $queried_object->taxonomy : '',
                'query_term' => $queried_object && $queried_object->term_id ? $queried_object->term_id : '',
            ),
            $target_block_attr
        );

        $html = '<form autocomplete="off" action="javascript:">';
            foreach ( $attr['repeatableFilter'] as $active_filter ) {
                $section_class = 'wopb-filter-' . $active_filter['type'];
                $body_class = '';
                $content = '';

                ob_start();
                switch ( $active_filter['type'] ) {
                    case 'search':
                        $this->search_filter();
                        break;
                    case 'price':
                        $body_class = ' wopb-price-range-slider';
                        $this->price_filter();
                        break;
                    case 'status':
                        $this->status_filter( $attr, $query_term );
                        break;
                    case 'rating':
                        $this->rating_filter();
                        break;
                    case 'sort_by':
                        $this->sorting_filter( $attr );
                        break;

                    default:
                        $attr['viewTaxonomyLimit'] = ! empty( $attr['viewTaxonomyLimit'] ) ? intval( $attr['viewTaxonomyLimit'] ) : 10;
                        $object_taxonomies =  array_diff(get_object_taxonomies('product'), ['product_type', 'product_visibility', 'product_shipping_class']);

                        foreach ($object_taxonomies as $key) {
                            if ( $key === $active_filter['type'] ) {
                                $term_query = array_merge(array(
                                    'taxonomy' => $key,
                                    'limit' => $attr['viewTaxonomyLimit'],
                                ), $query_term);
                                if ( $queried_object && $key == $queried_object->taxonomy && $queried_object->term_id ) {
                                    $term_query['parent'] = $queried_object->term_id;
                                }
                                if(is_search()) {
                                    $term_query['search_query'] = get_search_query();
                                }
                                $params = $this->get_term_data($term_query);
                                if ( ! empty( $params['terms'] ) ) {
                                    $counter++;
                                    $section_class = isset($key) ? ' wopb-filter-' . esc_attr( $key ) : '';
                                    $params['attributes'] = array(
                                        'productCount' => $attr['productCount'],
                                        'expandTaxonomy' => $attr['expandTaxonomy'],
                                        'viewTaxonomyLimit' => $attr['viewTaxonomyLimit'],
                                    );
                                    $this->product_taxonomy_filter($params);
                                }
                            }
                        }
                }
                $content .= ob_get_clean();

                if( $content ) {
                    $html .= '<div class="wopb-filter-section ' . $section_class . '">';
                        if($attr['enableTaxonomyRelation'] && $counter == 1) {
                            ob_start();
                                $this->taxonomy_relation();
                            $html .= ob_get_clean();
                        }
                        //Header content
                        $html .= '<div class="wopb-filter-header">';
                            $html .= '<span class="wopb-filter-label">';
                                $html .= ! empty( $active_filter['label'] ) ? wp_kses_post( $active_filter['label'] ) : '';
                            $html .= '</span>';
                                if( $attr['togglePlusMinus'] ) {
                                    $html .= '<div class="wopb-filter-toggle">';
                                        $html .= '<span class="dashicons dashicons-plus-alt2 wopb-filter-plus"></span>';
                                        $html .= '<span class="dashicons dashicons-minus wopb-filter-minus"></span>';
                                    $html .= '</div>';
                                }
                        $html .= '</div>';

                        //Body content
                        $html .= '<div class="wopb-filter-body' . $body_class . '">';
                            $html .= $content;
                        $html .= '</div>';

                    $html .= '</div>';
                }
            }

            ob_start();
                $this->reset_filter();
            $html .= ob_get_clean();
        $html .= '</form>';
        return $html;
    }

    /**
     * Get Term by Taxonomy
     *
     * @param $args
     * @return array
     * @since v.4.1.1
     */
    public function get_term_data( $args ) {
        global $wpdb;
        global $wp_query;
        $query_vars = $wp_query->query_vars;
        $conditions = array();
        $post_conditions = array();
        $where_clause = '';
        if( ! str_starts_with($args['taxonomy'], 'pa_') && empty( $args['parent'] ) ) {
            $args['parent'] = 0;
        }
        //exclude category from wholesalex
        if ( $args['taxonomy'] == 'product_cat' && ! empty( $query_vars['__wholesalex_exclude_cat'] ) ) {
            $exclude_cat = implode(',', $query_vars['__wholesalex_exclude_cat']);
            $conditions[] = " terms.term_id NOT IN ($exclude_cat)";
        }

        //get taxonomy from grid and sync with query
        if( ! empty( $args['queryTaxValue'] ) && is_array( $args['queryTaxValue'] ) ) {
            $tax_slug = '';
            foreach ( $args['queryTaxValue'] as $tax ) {
                if( ! is_array( $tax ) ) {
                    $tax = (array)$tax;
                }
                if(
                    $args['taxonomy'] == $args['query_tax'] &&
                    empty( $args['query_term'] ) &&
                    empty( $args['child_check'] ) &&
                    ! empty( $tax['value'] )
                ) {
                    $tax_slug .= "'" . $tax['value'] . "',";
                }
            }

            //match taxonomy with value from $tax_slug variable
            if( ! empty( $tax_slug ) ) {
                $tax_slug = rtrim($tax_slug, ',');
                $post_conditions[] = $conditions[] = " terms.slug IN ($tax_slug)";
                unset( $args['parent'] );
            }
        }

        $join_2 = "INNER JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy_2 ON 
                    term_relation_2.term_taxonomy_id = term_taxonomy_2.term_taxonomy_id
                INNER JOIN {$wpdb->prefix}terms AS terms_2 ON 
                    term_taxonomy_2.term_id = terms_2.term_id";

        //get taxonomy from taxonomy / archive page
        if( ! empty( $args['query_term'] ) ) {
            $post_conditions[] = " terms_2.term_id IN ({$args['query_term']})
            OR terms_2.term_id IN (
                SELECT child_term_tax.term_id FROM
                    {$wpdb->prefix}term_taxonomy AS child_term_tax
                WHERE child_term_tax.parent = {$args['query_term']}
            )";
        }
        //product in query specific term
        if( ! empty( $post_conditions ) ) {
            $post_conditions = implode( ' OR ', $post_conditions );
            $conditions[] = "posts.ID IN (
                SELECT term_relation_2.object_id FROM 
                    {$wpdb->prefix}term_relationships AS term_relation_2 $join_2 
                WHERE term_taxonomy_2.taxonomy = '{$args['query_tax']}' 
                AND $post_conditions
            )";
        }


        //post not in query from $query_vars
        if ( ! empty( $query_vars['post__not_in'] ) ) {
            $product_not_in = implode(',', array_map('intval', $query_vars['post__not_in']));
            $conditions[] = " posts.ID NOT IN ($product_not_in)";
        }
        //term fetch by parent
        if( isset( $args['parent'] ) &&  $args['parent'] !== '' ) {
            $conditions[] = ' term_taxonomy_1.parent=' . $args['parent'];
        }
        //query if search page
        if( ! empty( $args['search_query'] ) ) {
            $conditions[] = " posts.post_title LIKE '%{$args['search_query']}%'";
        }
        //Merge all where condition
        if( ! empty( $conditions ) ) {
            $where_clause = ' AND ' . implode(' AND ', $conditions);
        }

        $inner_query = "FROM {$wpdb->prefix}terms AS terms
            INNER JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy_1 ON 
                terms.term_id = term_taxonomy_1.term_id
            LEFT JOIN {$wpdb->prefix}term_taxonomy AS child_term_tax ON    /* join child terms to show total products in parent term by both parent and child terms */
                child_term_tax.parent = term_taxonomy_1.term_id
            LEFT JOIN {$wpdb->prefix}term_relationships AS term_relation_1 ON  /* count product by main term and child term */
                term_relation_1.term_taxonomy_id IN 
                    (term_taxonomy_1.term_id, child_term_tax.term_id)
            LEFT JOIN {$wpdb->prefix}posts AS posts ON 
                term_relation_1.object_id = posts.ID
            WHERE term_taxonomy_1.taxonomy = '{$args['taxonomy']}'
                AND posts.post_type = 'product'
                AND posts.post_status = 'publish'
                AND posts.ID NOT IN (   /* ignore hidden product from catalog visibility */
                    SELECT term_relation_2.object_id FROM 
                        {$wpdb->prefix}term_relationships AS term_relation_2 $join_2
                    WHERE term_taxonomy_2.taxonomy = 'product_visibility'
                    AND terms_2.slug = 'exclude-from-catalog'
            ) $where_clause";
        $sql = $wpdb->prepare("SELECT terms.term_id, terms.name, terms.slug, COUNT(DISTINCT posts.ID) as product_count
            $inner_query
            GROUP BY terms.term_id
            LIMIT %d OFFSET %d", $args['limit'], ! empty( $args['offset'] ) ? $args['offset'] : 0);
        $terms = $wpdb->get_results($sql);

        $total_terms = '';
        if( ! empty( $terms ) ) {
            $total_terms = $wpdb->get_var("SELECT COUNT(DISTINCT terms.term_id) AS total_terms $inner_query");
        }

        return array(
            'query_params' => array(
                'taxonomy' => $args['taxonomy'],
                'query_tax' => $args['query_tax'],
                'query_term' => $args['query_term'],
                'queryTaxValue' => ! empty( $args['queryTaxValue'] ) ? $args['queryTaxValue'] : '',
                'parent' => ! empty( $args['parent'] ) ? $args['parent'] : '',
                'limit' => $args['limit'],
                'search_query' => ! empty( $term_query['search_query'] ) ? $term_query['search_query'] : '',
            ),
            'total_terms' => $total_terms,
            'terms' =>  $terms,
        );
    }
    public function removeFilterItem() {
?>
        <div class="wopb-filter-remove-section">
            <div class="wopb-active-items"></div>
            <span class="wopb-filter-remove-all">
                <?php esc_html_e('Clear All', 'product-blocks') ?> <span class="dashicons dashicons-no-alt wopb-filter-remove-icon">
            </span>
        </div>
<?php
    }

    public function search_filter() {
?>
    <input type="hidden" class="wopb-filter-slug" value="search">
    <div class="wopb-search-filter-body">
        <input type="text" class="wopb-filter-search-input" placeholder="<?php echo esc_html__('Search Products', 'product-blocks') ?>..."/>
        <span class="wopb-search-icon"><?php echo wopb_function()->svg_icon('search') ?></span>
    </div>
<?php
    }

    public function price_filter() {
        $max_price = $this->max_price();
?>
    <input type="hidden" class="wopb-filter-slug" value="price">
    <div class="wopb-price-range">
        <span class="wopb-price-range-bar"></span>
        <input type="range" class="wopb-price-range-input wopb-min-range" min="0" max="<?php echo esc_attr($max_price) ?>" value="0" step="1">
        <input type="range" class="wopb-price-range-input wopb-max-range" min="0" max="<?php echo esc_attr($max_price) ?>" value="<?php echo esc_attr($max_price) ?>" step="1">
    </div>
    <span class="wopb-input-group">
        <input type="number" class="wopb-filter-price-input wopb-min-price" value="0" min="0">
        <input type="number" class="wopb-filter-price-input wopb-max-price" value="<?php echo esc_attr($max_price) ?>" min="0" max="<?php echo esc_attr($max_price) ?>">
    </span>
<?php
    }

    public function status_filter($attr, $term_query) {
?>
    <input type="hidden" class="wopb-filter-slug" value="status">
    <div class="wopb-filter-check-list">
        <?php
            foreach (wc_get_product_stock_status_options() as $key => $status) {
                $count = wopb_function()->generate_stock_status_count_query($key, $term_query);
        ?>
            <div class="wopb-filter-item">
                <div class="wopb-item-content">
                    <label for="status_<?php echo esc_attr($key) ?>">
                        <input type="checkbox" class="wopb-status-input" id="status_<?php echo esc_attr($key) ?>" value="<?php echo esc_attr($key) ?>"/>
                        <?php echo esc_html($status) ?> <?php echo $attr['productCount'] ? esc_html('(' . $count .')') : '' ?>
                    </label>
                </div>
            </div>
        <?php } ?>
    </div>
<?php
    }

    public function rating_filter() {
?>
    <input type="hidden" class="wopb-filter-slug" value="rating">
    <div class="wopb-filter-check-list wopb-filter-ratings">
        <?php for ($row = 5; $row > 0; $row--) { ?>
            <div class="wopb-filter-item">
                <div class="wopb-item-content">
                    <label for="filter-rating-<?php echo esc_attr($row) ?>">
                        <input type="checkbox" class="wopb-rating-input" value="<?php echo esc_attr($row) ?>" id="filter-rating-<?php echo esc_attr($row) ?>">
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

     public function product_taxonomy_filter($params) {
        $attr = $params['attributes'];
?>
        <input
            type="hidden"
            class="wopb-filter-slug"
            value="product_taxonomy"
            data-taxonomy="<?php echo esc_attr($params['query_params']['taxonomy']) ?>"
            data-query="<?php echo esc_attr( json_encode( $params['query_params'] ) ) ?>"
            data-attributes="<?php echo esc_attr( json_encode( $attr ) ) ?>"
        />
        <div class="wopb-filter-check-list">
            <?php
                ! empty( $params['query_params']['taxonomy'] ) ? $this->product_taxonomy_terms($attr, $params) : '';
            ?>
        </div>
        <?php
            if( ! empty( $params['total_terms'] ) && $params['total_terms'] > $attr['viewTaxonomyLimit'] ) {
                $total_page = $params['total_terms'] / $attr['viewTaxonomyLimit'];
                $total_page = ceil((float)$total_page);

        ?>
                <a
                    href="javascript:"
                    class="wopb-filter-extend-control wopb-filter-show-more"
                    data-current-page="1"
                    data-total-page="<?php echo esc_attr($total_page); ?>"
                >
                    <?php esc_html_e('Show More', 'product-blocks') ?>
                </a>
                <a
                    href="javascript:"
                    class="wopb-filter-extend-control wopb-filter-show-less"
                    data-current-page="1"
                >
                    <?php esc_html_e('Show Less', 'product-blocks') ?>
                </a>
        <?php } ?>
<?php
    }


    /**
     * Show Term by Taxonomy
     *
     * @param $attr
     * @param $params
     * @return null
     * @since v.2.5.3
     */
    public function product_taxonomy_terms($attr, $params) {
        $query_params = $params['query_params'];
?>
        <?php
            foreach ( $params['terms'] as $term ) {
                $extended_item_class = isset($params['show_more']) ? 'wopb-filter-extended-item' : '';
        ?>
            <div class="wopb-filter-item <?php echo esc_attr($extended_item_class) ?>">
                <div class="wopb-item-content">
                    <label for="tax_term_<?php echo esc_attr($term->name . '_' . $term->term_id) ?>">
                        <input
                            type="checkbox"
                            class="wopb-filter-tax-term-input"
                            id="tax_term_<?php echo esc_attr($term->name . '_' . $term->term_id) ?>"
                            value="<?php echo esc_attr($term->term_id) ?>"
                            data-label="<?php echo esc_attr($term->name) ?>"
                        />
                        <?php
                        if(
                            strpos( $query_params['taxonomy'], 'pa_') === 0 &&
                            $tax_attribute = wopb_function()->get_attribute_by_taxonomy( $query_params['taxonomy'] )
                        ) {
                            $attr_value = get_term_meta($term->term_id, $tax_attribute->attribute_type, true);
                            if ( $tax_attribute->attribute_type === 'color' ) {
                                $color_html = $attr_value ? '<span class="wopb-filter-tax-color" style="background-color: ' . esc_attr($attr_value) . ';"></span>' : '';
                                echo $color_html; //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
                            }elseif ( $tax_attribute->attribute_type === 'image' ) {
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
                    if(  ! str_starts_with($query_params['taxonomy'], 'pa_') ) {
                        $query_params['parent'] = $term->term_id;
                        $query_params['child_check'] = true;
                        $child_data = $this->get_term_data($query_params);
                        if ( ! empty( $child_data['terms'] ) ) {
                            $params = array_merge($child_data, array( 'taxonomy' => $query_params['taxonomy'] ));
                    ?>
                         <div class="wopb-filter-check-list<?php
                                isset($attr['expandTaxonomy']) && $attr['expandTaxonomy'] == 'true'
                                    ? ''
                                    : esc_attr_e(' wopb-d-none')
                            ?>"
                        >
                            <?php $this->product_taxonomy_terms($attr, $params);?>
                        </div>
                    <?php } } ?>
                </div>
                <?php if ( ! empty( $child_data['terms'] ) ) { ?>
                    <div class="wopb-filter-child-toggle">
                        <span class="dashicons dashicons-arrow-right-alt2 wopb-filter-right-toggle<?php echo $attr['expandTaxonomy'] == 'true' ? ' wopb-d-none' : '' ?>"></span>
                        <span class="dashicons dashicons-arrow-down-alt2 wopb-filter-down-toggle<?php echo $attr['expandTaxonomy'] == 'true' ? '' : ' wopb-d-none' ?>"></span>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
<?php

    }

    public function sorting_filter($attr) {
?>
        <input type="hidden" class="wopb-filter-slug" value="sorting">
        <select name="sortBy" class="select wopb-filter-sorting-input">
            <?php foreach ($attr['sortingItems'] as $item) { ?>
                <option value="<?php echo esc_attr($item->value)?>">
                    <?php echo esc_html__($item->label, 'product-blocks')?>
                </option>
           <?php } ?>
        </select>
<?php
    }

    public function reset_filter() {
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

    public function max_price() {
        global $wpdb;
        $max_price = $wpdb->get_var("
            SELECT MAX(CAST(meta_value AS DECIMAL(10,2)))
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_price'
        ");
        return ceil((float)$max_price);
    }


    /**
	 * Show more filter item by ajax
     *
     * @since v.2.5.3
	 * @return null
	 */
    public function show_more_callback() {
        //phpcs:disable WordPress.Security.NonceVerification.Missing
        $query = $_POST['query'];
        $query['offset'] = ($_POST['current_page'] - 1) * $query['limit'];
        $params = $this->get_term_data($query);
        if ( ! empty( $params['terms'] ) ) {
            $params['show_more'] = true;
            echo $this->product_taxonomy_terms($_POST['attributes'], $params); //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        }
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
                if( ! empty( $block['attrs']['queryTaxValue'] ) ) {
                    $target_block_attr = array_merge($target_block_attr, array(
                        'query_tax' => !empty($block['attrs']['queryTax']) ? $block['attrs']['queryTax'] : 'product_cat',
                        'queryTaxValue' => json_decode( $block['attrs']['queryTaxValue'] ),
                    ));
                }
            } elseif (count($block['innerBlocks']) > 0) {
                $this->getTargetBlockAttributes($attr, $block['innerBlocks'], $target_block_attr);
            }
        }
        return $target_block_attr;
    }
}