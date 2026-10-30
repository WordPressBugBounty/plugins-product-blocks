<?php
/**
 * Size Chart Addons Core.
 *
 * @package WOPB\SizeChart
 * @since v.3.2.0
 */

namespace WOPB;

defined( 'ABSPATH' ) || exit;

/**
 * SizeChart class.
 */
class SizeChart {

	private $size_chart = 'wopb-size-chart';

	/**
	 * Setup class.
	 *
	 * @since v.3.2.0
	 */
	public function __construct() {
		$this->custom_post_register();
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_size_chart_assets' ), 999 );
		add_filter( 'gutenberg_can_edit_post_type', array( $this, 'gutenberg_editor_disable' ), 10, 2 );
		add_filter( 'use_block_editor_for_post_type', array( $this, 'gutenberg_editor_disable' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'size_chart_meta_boxes' ) );
		add_action( 'save_post_wopb-size-chart', array( $this, 'save_meta_fields' ), 10, 2 );

		// Size Chart tab in product edit page of admin panel
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'size_chart_admin_product_tab' ), 5, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'size_chart_admin_product_tab_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'size_chart_admin_fields_save' ), 10, 2 );

		// Size chart button and popup on the product page.
		$chart_btn_position = wopb_function()->get_setting( 'size_chart_position' );
		$meta_position      = array(
			'before_meta' => 'woocommerce_product_meta_start',
			'after_meta'  => 'woocommerce_product_meta_end',
		);
		if ( isset( $meta_position[ $chart_btn_position ] ) ) {
			add_action( $meta_position[ $chart_btn_position ], array( $this, 'size_chart_meta_position' ) );
		} else {
			add_action( 'after_add_cart' === $chart_btn_position ? 'wopb_bottom_add_to_cart' : 'wopb_top_add_to_cart', array( $this, 'size_chart_position' ) );
		}
		// CSS Generator
		add_action( 'wopb_save_settings', array( $this, 'generate_css' ), 10, 1 );

		/**
		 * Fires once the Size Chart add-on is set up.
		 *
		 * @since 4.6.0
		 * @param SizeChart $size_chart This add-on; `get_chart_id()` and `chart_body()` are public.
		 */
		do_action( 'wopb_size_chart_init', $this );
	}

	/**
	 * Enqueue Style & Script
	 *
	 * @return void
	 * @since v.3.2.0
	 */
	public function enqueue_size_chart_assets() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && ! in_array( $screen->post_type, array( 'product', $this->size_chart ), true ) ) {
			return;
		}
		if ( wopb_function()->get_setting( 'is_wc_ready' ) ) {
			wp_enqueue_style( 'woocommerce_admin_styles' );
		}
	}

	/**
	 * Register Custom Size Chart Post Type
	 *
	 * @return void
	 * @since v.3.2.0
	 */
	public function custom_post_register() {
		$labels = array(
			'name'                  => esc_html_x( 'Size Chart', 'Post Type General Name', 'product-blocks' ),
			'singular_name'         => esc_html_x( 'Size Chart', 'Post Type Singular Name', 'product-blocks' ),
			'menu_name'             => esc_html__( 'Size Chart', 'product-blocks' ),
			'name_admin_bar'        => esc_html__( 'Size Chart', 'product-blocks' ),
			'archives'              => esc_html__( 'Size Chart Archives', 'product-blocks' ),
			'all_items'             => esc_html__( 'Size Charts', 'product-blocks' ),
			'add_new'               => esc_html__( 'Add New Chart', 'product-blocks' ),
			'add_new_item'          => esc_html__( 'Add New Chart', 'product-blocks' ),
			'edit_item'             => esc_html__( 'Edit Chart', 'product-blocks' ),
			'update_item'           => esc_html__( 'Update Chart', 'product-blocks' ),
			'view_item'             => esc_html__( 'View Chart', 'product-blocks' ),
			'search_items'          => esc_html__( 'Search Size Charts', 'product-blocks' ),
			'not_found'             => esc_html__( 'Not found', 'product-blocks' ),
			'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'product-blocks' ),
			'insert_into_item'      => esc_html__( 'Insert into Size Chart', 'product-blocks' ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this Size Chart', 'product-blocks' ),
			'items_list'            => esc_html__( 'Size Charts', 'product-blocks' ),
			'items_list_navigation' => esc_html__( 'Size Charts navigation', 'product-blocks' ),
			'filter_items_list'     => esc_html__( 'Filter from charts', 'product-blocks' ),
		);

		$chart_arg = array(
			'label'               => esc_html__( 'Size Chart', 'product-blocks' ),
			'description'         => esc_html__( 'Size Chart', 'product-blocks' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'revisions' ),
			'show_in_menu'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'rewrite'             => array(
				'slug'       => $this->size_chart,
				'with_front' => true,
			),
			'query_var'           => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'page',
			'show_in_rest'        => false,
			'rest_base'           => $this->size_chart,
		);

		/**
		 * Filters the size chart post type arguments.
		 *
		 * @since 4.6.0
		 * @param array $chart_arg Arguments for `register_post_type()`.
		 */
		register_post_type( $this->size_chart, apply_filters( 'wopb_size_chart_post_type_args', $chart_arg ) );
	}

	/**
	 * Disable Gutenberg Editor as By Default
	 *
	 * @param bool   $can_edit
	 * @param string $post_type
	 * @return bool
	 * @since v.3.2.0
	 */
	public function gutenberg_editor_disable( bool $can_edit, string $post_type ) {
		return $this->size_chart == $post_type ? false : $can_edit;
	}

	/**
	 * Custom Meta Box Added
	 *
	 * @return void
	 * @since v.3.2.0
	 */
	public function size_chart_meta_boxes() {
		add_meta_box(
			'wopb-size-chart-table',
			__( 'Chart Table', 'product-blocks' ),
			array( $this, 'table_field_callback' ),
			$this->size_chart,
			'advanced',
			'default'
		);
		add_meta_box(
			'wopb-size-chart-assign',
			__( 'Assigning Chart', 'product-blocks' ),
			array( $this, 'assign_fields_callback' ),
			$this->size_chart,
			'side',
			'default'
		);
	}

	/**
	 * Dynamic Editable Table into the Meta box
	 *
	 * @return void
	 * @since v.3.2.0
	 */
	public function table_field_callback() {
		global $post;
		$chart_columns    = get_post_meta( $post->ID, 'wopb_sc_column', true );
		$heading_position = metadata_exists( 'post', $post->ID, 'wopb_sc_heading_position' )
			? get_post_meta( $post->ID, 'wopb_sc_heading_position', true )
			: 'on';
		$hide_name        = metadata_exists( 'post', $post->ID, 'wopb_sc_hide_title' )
			? get_post_meta( $post->ID, 'wopb_sc_hide_title', true )
			: '';

		$head_default    = __( 'Title', 'product-blocks' );
		$content_default = __( 'Content', 'product-blocks' );
		if ( empty( $chart_columns ) ) {
			$chart_columns = array_merge(
				array( array_fill( 0, 3, $head_default ) ),
				array_fill( 0, 3, array_fill( 0, 3, $content_default ) )
			);
		}
		?>
		<p class="wopb-toogle-field-wrap">
			<span class="wopb-label">
				<?php echo esc_html__( 'Table Heading Position', 'product-blocks' ); ?>
			</span>
			<span class="wopb-toggle-control">
				<span><?php echo esc_html__( 'Column', 'product-blocks' ); ?></span>
				<span class="wopb-position-input">
					<input
						type="checkbox"
						name="wopb_sc_heading_position"
						id="wopb-sc-heading-position"
						data-head-default="<?php echo esc_attr( $head_default ); ?>"
						data-content-default="<?php echo esc_attr( $content_default ); ?>"
						<?php echo '' !== $heading_position ? 'checked' : ''; ?>
					>
					<label for="wopb-sc-heading-position" class="wopb-toggle"></label>
				</span>
				<span><?php echo esc_html__( 'Row', 'product-blocks' ); ?></span>
			</span>
		</p>
		<p class="wopb-toogle-field-wrap">
			<span class="wopb-label">
				<?php echo esc_html__( 'Hide Chart Name', 'product-blocks' ); ?>
			</span>
			<span class="wopb-toggle-control">
				<span>
					<input
						type="checkbox"
						name="wopb_sc_hide_title"
						id="wopb-sc-hide-title"
						<?php echo '' !== $hide_name ? 'checked' : ''; ?>
					>
					<label for="wopb-sc-hide-title" class="wopb-toggle"></label>
				</span>
			</span>
		</p>
		<?php wp_nonce_field( 'wopb_size_chart_save', 'wopb_size_chart_nonce' ); ?>
		<input type="hidden" name="wopb_sc_column_array" id="wopb-sc-column-array">
		<table class="wopb-sc-table">
			<tbody>
				<tr class="wopb-sc-control-col">
					<?php
					if ( isset( $chart_columns[0] ) ) {
						foreach ( $chart_columns[0] as $row_index => $column ) {
							?>
							<td>
								<a class="wopb-sc-control-btn wopb-sc-add-item">
									<span class="dashicons dashicons-plus-alt2"></span>
									<span class="wopb-sc-tooltip"><?php echo esc_html__( 'Add Column', 'product-blocks' ); ?></span>
								</a>
								<a class="wopb-sc-control-btn wopb-sc-del-item">
									<span class="dashicons dashicons-minus"></span>
									<span class="wopb-sc-tooltip"><?php echo esc_html__( 'Remove Column', 'product-blocks' ); ?></span>
								</a>
							</td>
							<?php
						}
					}
					?>
				</tr>
				<?php
				if ( is_array( $chart_columns ) ) {
					foreach ( $chart_columns as $row_index => $columns ) {
						$row_control_class = 0 == $row_index ? ' wopb-d-none' : '';
						?>
						<tr class="wopb-sc-input-row">
							<?php
							foreach ( $columns as $col_index => $column ) {
								$col_class = '';
								if ( '' != $heading_position && 0 == $row_index ) {
									$col_class = ' wopb-sc-head-col';
								} elseif ( '' == $heading_position && 0 == $col_index ) {
									$col_class = ' wopb-sc-head-col';
								}
								?>
								<td class="wopb-sc-input-col<?php echo esc_attr( $col_class ); ?>">
									<input
										type="text"
										value="<?php echo esc_attr( $column ); ?>"
										class="wopb-sc-solumn" placeholder='Enter text..'
										autocomplete="off"
									>
								</td>
							<?php } ?>
							<td class="wopb-sc-control-row">
								<?php if ( $row_index > 0 ) { ?>
									<a class="wopb-sc-control-btn wopb-sc-add-item<?php echo esc_attr( $row_control_class ); ?>">
										<span class="dashicons dashicons-plus-alt2"></span>
										<span class="wopb-sc-tooltip"><?php echo esc_html__( 'Add Row', 'product-blocks' ); ?></span>
									</a>
									<a class="wopb-sc-control-btn wopb-sc-del-item<?php echo esc_html( $row_control_class ); ?>">
										<span class="dashicons dashicons-minus"></span>
										<span class="wopb-sc-tooltip"><?php echo esc_html__( 'Remove Row', 'product-blocks' ); ?></span>
									</a>
								<?php } ?>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Assign All Meta Field Markup
	 *
	 * @return void
	 * @since v.3.2.0
	 */
	public function assign_fields_callback() {
		global $post;
		$all_product_selected = get_post_meta( $post->ID, 'wopb_sc_all_product', true );
		$get_category         = get_post_meta( $post->ID, 'wopb_sc_category', true );
		$exclude_products     = get_post_meta( $post->ID, 'wopb_sc_exclude_products', true );
		?>
		<p>
			<input
				type="checkbox"
				id="wopb-sc-all-product"
				name="wopb_sc_all_product"
				value="1"
				<?php checked( $all_product_selected, true, true ); ?>
			/>
			<label for="wopb-sc-all-product">
				<?php echo esc_html__( 'Apply On All Products ?', 'product-blocks' ); ?>
			</label>
		</p>
		<div class="wopb-sc-category<?php echo ( $all_product_selected ? ' wopb-d-none' : '' ); ?>">
			<label><?php echo esc_html__( 'Assign Category', 'product-blocks' ); ?></label>
			<div>
				<select name="wopb_sc_category[]" class="wopb-select2" multiple>
					<?php
						$categories = get_terms(
							array(
								'taxonomy' => 'product_cat',
								'fields'   => 'all',
								'orderby'  => 'id',
								'order'    => 'ASC',
							)
						);
					foreach ( $categories as $category ) {
						$selected = '';
						if ( ! empty( $get_category ) && in_array( $category->term_id, $get_category ) ) {
							$selected = 'selected';
						}
						echo '<option value="' . esc_attr( $category->term_id ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $category->name ) . '</option>';
					}
					?>
				</select>
			</div>
		</div>

		<?php
		/**
		 * Fires in the size chart's "Assigning Chart" meta box, after the category field.
		 *
		 * @since 4.6.0
		 * @param \WP_Post $post Size chart being edited.
		 */
		do_action( 'wopb_size_chart_assign_fields', $post );

		$this->render_product_select_field(
			'wopb_sc_exclude_products',
			! empty( $exclude_products ) ? $exclude_products : array(),
			__( 'Exclude Products', 'product-blocks' ),
			'wopb-sc-exclude-products'
		);
	}

	/**
	 * Product Selected Fields
	 *
	 * @param string $key
	 * @param array  $selected_products
	 * @param string $label
	 * @param string $class
	 * @return void
	 * @since v.3.2.0
	 */
	private function render_product_select_field( string $key, array $selected_products, string $label, string $class = '' ) {
		?>
		<p class="wopb-sc-products <?php echo esc_attr( $class ); ?>">
			<label for="<?php echo esc_attr( $key ); ?>">
				<?php
					echo esc_html( $label ) . wp_kses( wc_help_tip( __( 'Select Products List To Show Specific Product.', 'product-blocks' ) ), wopb_function()->allowed_html_tags( array( 'span' => array( 'tabindex' => true ) ) ) );
				?>
			</label>
			<select
				id="<?php echo esc_attr( $key ); ?>"
				class="wc-product-search"
				name="<?php echo esc_attr( $key ) . '[]'; ?>"
				multiple="multiple"
				data-placeholder="<?php echo esc_attr__( 'Search for a product', 'product-blocks' ); ?>"
				data-action="woocommerce_json_search_products" data-minimum_input_length="3"
				data-exclude_type="variation"
				style="min-width: 250px;">
				<?php
				foreach ( $selected_products as $product_id ) {
					$product = wc_get_product( $product_id );
					if ( is_object( $product ) ) {
						$selected = '';
						if ( in_array( $product_id, $selected_products ) ) {
							$selected = 'selected';
						}
						?>
							<option
								value="<?php echo esc_attr( $product_id ); ?>"
								<?php echo esc_attr( $selected ); ?>
							>
								<?php echo esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ); ?>
							</option>
						<?php
					}
				}
				?>
			</select>
		</p>
		<?php
	}

	/**
	 * Save Size Chart Meta box Fields
	 *
	 * @param int    $post_id
	 * @param object $post
	 * @return void
	 * @since v.3.2.0
	 */
	public function save_meta_fields( int $post_id, object $post ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || $this->size_chart !== get_post_type( $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['wopb_size_chart_nonce'] ) || ! is_string( $_POST['wopb_size_chart_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wopb_size_chart_nonce'] ) ), 'wopb_size_chart_save' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['wopb_sc_column_array'] ) && is_string( $_POST['wopb_sc_column_array'] ) ) {
			// Decode first, then sanitize each cell: sanitizing the encoded JSON breaks cells holding quotes.
			$column_array = $this->sanitize_chart_rows( json_decode( wp_unslash( $_POST['wopb_sc_column_array'] ), true ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each cell is sanitized by sanitize_chart_rows().
			if ( null !== $column_array ) {
				// The metadata API unslashes values again; preserve literal backslashes in sanitized cells.
				update_post_meta( $post_id, 'wopb_sc_column', wp_slash( $column_array ) );
			}
		}
		$fields = array(
			'wopb_sc_heading_position',
			'wopb_sc_hide_title',
			'wopb_sc_all_product',
			'wopb_sc_category',
			'wopb_sc_exclude_products',
		);
		foreach ( $fields as $field ) {
			update_post_meta( $post_id, $field, isset( $_POST[ $field ] ) ? map_deep( wp_unslash( $_POST[ $field ] ), 'sanitize_text_field' ) : '' );
		}
	}

	/**
	 * Sanitize the chart table posted by the editor: a list of rows, each a list of cell texts.
	 *
	 * @param mixed $rows Decoded table.
	 * @return array|null Sanitized rows, or null when the table isn't that shape, so the saved chart is kept.
	 * @since 4.6.0
	 */
	private function sanitize_chart_rows( $rows ) {
		if ( ! is_array( $rows ) ) {
			return null;
		}
		$sanitized = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				return null;
			}
			$cells = array();
			foreach ( $row as $cell ) {
				if ( null !== $cell && ! is_scalar( $cell ) ) {
					return null;
				}
				$cells[] = sanitize_text_field( (string) $cell );
			}
			$sanitized[] = $cells;
		}
		return $sanitized;
	}

	/**
	 * Size Chart tab in product edit page in admin panel
	 *
	 * @return array
	 * @since v.4.0.0
	 */
	public function size_chart_admin_product_tab( $product_data_tabs ) {
		$product_data_tabs['wopb_size_chart'] = array(
			'label'    => __( 'Size Chart', 'product-blocks' ),
			'class'    => array( 'wopb_product_tab' ),
			'target'   => 'wopb_size_chart_tab_data',
			'priority' => 15.3,
		);
		return $product_data_tabs;
	}

	/**
	 * Size Chart Custom Field
	 *
	 * @return void
	 * @since v.4.0.0
	 */
	public function size_chart_admin_product_tab_fields() {
		global $post;
		$args_1 = array( // Query for get all charts for dropdown
			'post_type'      => $this->size_chart,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		);
		$charts = new \WP_Query( $args_1 );

		$get_charts = get_post_meta( $post->ID, 'wopb_size_chart_ids', true );
		?>
		<div class="panel woocommerce_options_panel hidden" id="wopb_size_chart_tab_data">
			<div class="wopb-field-group">
				<?php if ( $charts->have_posts() ) { ?>
					<label for="wopb_size_chart_ids">
						<?php echo esc_html__( 'Select Size Chart', 'product-blocks' ); ?>
					</label>
					<select
							id="wopb_size_chart_ids"
							class="wopb-select2"
							name="wopb_size_chart_ids[]" 
						>
						<option value=""><?php echo esc_html__( 'Select Size Chart', 'product-blocks' ); ?></option>
						<?php
						foreach ( $charts->posts as $chart ) {
							$chart_id     = $chart->ID;
								$selected = '';
							if ( ! empty( $get_charts ) && in_array( $chart_id, $get_charts ) ) {
								$selected = 'selected';
							}
							?>
									<option
										value="<?php echo esc_attr( $chart_id ); ?>"
									<?php echo esc_attr( $selected ); ?>
									>
									<?php echo esc_html( $chart->post_title ); ?>
									</option>
						<?php } ?>
					</select>
				<?php } else { ?>
					<div class="wopb-chart-create-desc">
						<?php echo esc_html__( 'There is no chart available', 'product-blocks' ); ?>
						<a
							href="<?php echo esc_url( admin_url( 'post-new.php?post_type=wopb-size-chart' ) ); ?>"
							class="wopb-add-chart-btn"
						>
							<?php echo esc_html__( 'Create Chart', 'product-blocks' ); ?>
						</a>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Save input field when product save in admin panel
	 *
	 * @param INT $post_id
	 * @return void
	 * @since v.1.0.4
	 */
	public function size_chart_admin_fields_save( $post_id ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$args    = array(
			'post_type'      => $this->size_chart,
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- no taxonomy equivalent for the chart<->product relationship.
				array(
					'key'     => 'wopb_sc_include_products',
					'value'   => $post_id,
					'compare' => 'LIKE',
				),
			),
		);
		$results = new \WP_Query( $args );

		$chart_ids = array();
		if ( isset( $_POST['wopb_size_chart_ids'] ) ) {
			$chart_ids = array_map( 'absint', array_filter( map_deep( wp_unslash( (array) $_POST['wopb_size_chart_ids'] ), 'sanitize_text_field' ), 'is_scalar' ) );
		}
		// Only real size charts the user may edit can be linked to or unlinked from the product.
		$chart_ids          = array_values( array_filter( array_unique( $chart_ids ), array( $this, 'can_link_chart' ) ) );
		$selected_chart_ids = $chart_ids;
		if ( $results->posts ) {
			foreach ( $results->posts as $chart ) {
				if ( ! $this->can_link_chart( $chart->ID ) ) {
					continue;
				}
				$include_products = get_post_meta( $chart->ID, 'wopb_sc_include_products', true );
				if ( ! empty( $include_products ) && is_array( $include_products ) ) {
					if (
						! empty( $chart_ids ) &&
						in_array( $chart->ID, $chart_ids, true ) &&
						in_array( $post_id, $include_products )
					) {
						// Check selected chart and product id exist or not in existing chart.
						// If already exist skip then chart id save in database.
						$chart_ids = array_diff( $chart_ids, array( $chart->ID ) );
					} elseif ( in_array( $post_id, $include_products ) ) {
						// If product id exist in existing chart then product remove from the chart.
						$include_products = array_diff( $include_products, array( $post_id ) );
						update_post_meta( $chart->ID, 'wopb_sc_include_products', $include_products );
					}
				}
			}
		}
		// Product save to selected chart.
		if ( ! empty( $chart_ids ) ) {
			foreach ( $chart_ids as $chart_id ) {
				$include_products = get_post_meta( $chart_id, 'wopb_sc_include_products', true );
				if ( empty( $include_products ) || ! is_array( $include_products ) ) {
					$include_products = array();
				}
				$include_products[] = $post_id;
				update_post_meta( $chart_id, 'wopb_sc_include_products', $include_products );
			}
		}
		update_post_meta( $post_id, 'wopb_size_chart_ids', $selected_chart_ids );
	}

	/**
	 * Whether a post is a size chart the current user can edit
	 *
	 * @param int $chart_id Post ID.
	 * @return bool
	 * @since 4.6.0
	 */
	public function can_link_chart( $chart_id ) {
		return get_post_type( $chart_id ) === $this->size_chart && current_user_can( 'edit_post', $chart_id );
	}

	/**
	 * Get Products by Product IDS
	 *
	 * @param array $product_ids
	 * @return string|void
	 * @since v.3.2.0
	 */
	public function product_names_by_ids( array $product_ids ) {
		$products      = wc_get_products(
			array(
				'limit'   => -1,
				'include' => $product_ids,
			)
		);
		$product_names = array();

		foreach ( $products as $product ) {
			$product_names[] = $product->get_name();
		}

		if ( ! empty( $product_names ) ) {
			return implode( ', ', $product_names );
		}
	}

	/**
	 * Display Button Position Base On The Hooks
	 *
	 * @return string
	 * @since v.3.2.0
	 */
	public function size_chart_position() {
		$table_content = $this->table_content();
		if ( $table_content ) {
			$html          = '';
			$html         .= '<div class="wopb-chart-btn-wrapper">';
				$html     .= '<span id="wopb-chart-modal-btn">';
					$html .= wopb_function()->svg_icon( wopb_function()->get_setting( 'size_chart_btn_icon' ) );
					$html .= esc_html( wopb_function()->get_setting( 'size_chart_btn_text' ) );
				$html     .= '</span>';
			$html         .= '</div>';
			$html         .= $table_content;
			return wp_kses( $html, wopb_function()->allowed_html_tags() );
		}
	}

	/**
	 * Display Button Position Base On The Hooks
	 *
	 * @return void
	 * @since v.3.2.0
	 */
	public function size_chart_meta_position() {
		echo wp_kses( (string) $this->size_chart_position(), wopb_function()->allowed_html_tags() );
	}

	/**
	 * Check If Product Assigned In Size Chart
	 *
	 * @param array $chart_ids
	 * @return bool
	 * @since v.3.2.0
	 */
	public function is_assigned_on_products( array $chart_ids ) {
		global $product;
		$exclude_products = get_post_meta( $chart_ids[0], 'wopb_sc_exclude_products', true );
		$categories       = get_post_meta( $chart_ids[0], 'wopb_sc_category', true );

		if ( ! empty( $exclude_products ) && in_array( $product->get_id(), $exclude_products ) ) {
			return false;
		} elseif ( get_post_meta( $chart_ids[0], 'wopb_sc_all_product', true ) ) {
			return true;
		} elseif ( ! empty( $categories ) && array_intersect( $product->get_category_ids(), $categories ) ) {
			return true;
		}

		/**
		 * Filters whether a size chart applies to the product, after the free checks.
		 * Not called for products the chart excludes.
		 *
		 * @since 4.6.0
		 * @param bool        $assigned Whether the chart applies.
		 * @param int         $chart_id Size chart post ID.
		 * @param \WC_Product $product  Product being viewed.
		 */
		return (bool) apply_filters( 'wopb_size_chart_assigned', false, $chart_ids[0], $product );
	}

	/**
	 * Chart and Product Details For Reuse
	 *
	 * @return array
	 * @since v.3.2.0
	 */
	public function chart_list() {
		global $product;
		$args = array(
			'post_type'      => $this->size_chart,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'desc',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- no taxonomy equivalent for the chart<->product relationship.
				'relation' => 'OR',
				array(
					'key'     => 'wopb_sc_all_product',
					'value'   => 1,
					'compare' => '=',
				),
				array(
					'key'     => 'wopb_sc_exclude_products',
					'value'   => '"' . $product->get_id() . '"',
					'compare' => 'LIKE',
				),
			),
		);
		if ( ! empty( $product->get_category_ids() ) ) {
			foreach ( $product->get_category_ids() as $category_id ) {
				$args['meta_query'][] = array(
					'key'     => 'wopb_sc_category',
					'value'   => '"' . $category_id . '"',
					'compare' => 'LIKE',
				);
			}
		}

		/**
		 * Filters the query for size charts that may apply to the product.
		 *
		 * @since 4.6.0
		 * @param array       $args    `WP_Query` arguments.
		 * @param \WC_Product $product Product being viewed.
		 */
		$args   = apply_filters( 'wopb_size_chart_query_args', $args, $product );
		$query  = new \WP_Query( $args );
		$charts = $query->posts;
		return wp_list_pluck( $charts, 'ID' );
	}

	/**
	 * Size Chart ID For The Current Product
	 *
	 * @return int Chart ID, or 0 when no published chart applies.
	 * @since 4.6.0
	 */
	public function get_chart_id() {
		if ( ! is_product() ) {
			return 0;
		}
		global $product;
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return 0;
		}
		$chart_ids = get_post_meta( $product->get_id(), 'wopb_size_chart_ids', true );
		if ( ! empty( $chart_ids[0] ) && get_post_status( $chart_ids[0] ) === 'publish' ) {
			return absint( $chart_ids[0] );
		}
		$chart_ids = $this->chart_list();
		if ( $chart_ids && $this->is_assigned_on_products( $chart_ids ) ) {
			return absint( $chart_ids[0] );
		}
		return 0;
	}

	/**
	 * Size Chart Body Markup
	 *
	 * @param int $chart_id Size chart post ID.
	 * @return string
	 * @since 4.6.0
	 */
	public function chart_body( $chart_id ) {
		$table_rows       = get_post_meta( $chart_id, 'wopb_sc_column', true );
		$heading_position = get_post_meta( $chart_id, 'wopb_sc_heading_position', true );
		$html             = '';
		if ( ! empty( $table_rows ) && is_array( $table_rows ) ) {
			$table_class         = ' wopb-scrollbar';
			$table_class        .= $heading_position != '' ? ' wopb-heading-row' : ' wopb-heading-col';
			$wopb_buffer_level_1 = ob_get_level();
			ob_start();
			try {
				?>
			<div class="wopb-sc-table<?php echo esc_attr( $table_class ); ?>">
				<table >
				<?php
				foreach ( $table_rows as $key => $columns ) {
					?>
						<tr class="wopb-sc-table-row">
					<?php
					foreach ( $columns as $index => $column ) {
						$col_class = '';
						if ( '' != $heading_position && 0 == $key ) {
							$col_class = 'wopb-sc-head-col';
						} elseif ( '' == $heading_position && 0 == $index ) {
							$col_class = 'wopb-sc-head-col';
						}
						?>
							<td class="<?php echo esc_attr( $col_class ); ?>">
							<?php echo esc_html( $column ); ?>
							</td>
							<?php } ?>
						</tr>
					<?php } ?>
				</table>
			</div>
				<?php
				$html = ob_get_clean();
			} finally {
				while ( ob_get_level() > $wopb_buffer_level_1 ) {
					if ( ! ob_end_clean() ) {
						break;
					}
				}
			}
		}

		/**
		 * Filters the size chart body markup.
		 *
		 * @since 4.6.0
		 * @param string $html     Chart table markup, or '' when the chart has no rows.
		 * @param int    $chart_id Size chart post ID.
		 */
		return wp_kses( apply_filters( 'wopb_size_chart_content', $html, $chart_id ), wopb_function()->allowed_html_tags() );
	}

	/**
	 * Table Content Preview in Front-end
	 *
	 * @return void|string
	 * @since v.3.2.0
	 */
	public function table_content() {
		$chart_id = $this->get_chart_id();
		if ( ! $chart_id ) {
			return;
		}
		$hide_title          = get_post_meta( $chart_id, 'wopb_sc_hide_title', true );
		$title               = get_the_title( $chart_id );
		$wopb_buffer_level_2 = ob_get_level();
		ob_start();
		try {
			?>
		<div class="wopb-size-chart-wrapper wopb-size-chart-modal">
			<div class="wopb-chart-modal-content">
				<div class="wopb-sc-title">
					<?php echo ! $hide_title ? esc_html( $title ) : ''; ?>
					<span class="wopb-chart-modal-close">
						<svg width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 6 6 18M6 6l12 12" stroke="#070707" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</span>
				</div>
				<div class="wopb-chart-body">
					<?php echo wp_kses_post( $this->chart_body( $chart_id ) ); ?>
				</div>
			</div>
		</div>
			<?php
			return ob_get_clean();
		} finally {
			while ( ob_get_level() > $wopb_buffer_level_2 ) {
				if ( ! ob_end_clean() ) {
					break;
				}
			}
		}
	}

	/**
	 * CSS Generator
	 *
	 * @return void
	 * @since v.3.2.0
	 */
	public function generate_css( $key ) {
		if ( 'wopb_size_chart' === $key && method_exists( wopb_function(), 'convert_css' ) ) {
			$settings  = wopb_function()->get_setting();
			$btn_style = array_merge( $settings['size_chart_btn_typo'], $settings['size_chart_btn_bg'] );

			$css      = '#wopb-chart-modal-btn {';
				$css .= wopb_function()->convert_css( 'general', $btn_style );
				$css .= 'padding: ' . wopb_function()->convert_css( 'dimension', $settings['size_chart_btn_padding'] ) . ';';
				$css .= wopb_function()->convert_css(
					'border',
					array(
						'border' => $settings['size_chart_btn_border']['border'] ?? 0,
						'color'  => $settings['size_chart_btn_border']['color'] ?? '',
					)
				);
				$css .= wopb_function()->convert_css( 'radius', $settings['size_chart_btn_radius'] ?? '' );
			$css     .= '}';
			$css     .= '#wopb-chart-modal-btn:hover {';
				$css .= wopb_function()->convert_css( 'hover', $btn_style );
			$css     .= '}';
			$css     .= '#wopb-chart-modal-btn svg{';
				$css      .= isset( $settings['size_chart_btn_icon_size']['color'] ) ? 'fill: ' . wopb_function()->sanitize_css_color( $settings['size_chart_btn_icon_size']['color'] ) . ';' : '';
				$icon_size = wopb_function()->sanitize_css_number( $settings['size_chart_btn_icon_size'] ?? '' );
				$css      .= 'width : ' . ( ! empty( $icon_size ) ? $icon_size : 20 ) . 'px;';
				$css      .= 'height : ' . ( ! empty( $icon_size ) ? $icon_size : 20 ) . 'px;';
			$css     .= '}';

			$css     .= '.wopb-size-chart-wrapper .wopb-sc-table th, .wopb-size-chart-wrapper .wopb-sc-table td {';
				$table_border = wopb_function()->sanitize_css_number( $settings['size_chart_table_border']['border'] ?? '' );
				$table_color  = wopb_function()->sanitize_css_color( $settings['size_chart_table_border']['color'] ?? '' );
				$css         .= 'border: ' .
					( ! empty( $table_border )
						? $table_border
						: 1
					) . 'px solid ' .
					( ! empty( $table_color )
						? $table_color
						: '#646464'
					) . ';';
			$css     .= '}';

			$css     .= '.wopb-size-chart-wrapper .wopb-sc-table .wopb-sc-head-col {';
				$css .= isset( $settings['size_chart_heading_bg']['bg'] ) ? 'background-color: ' . wopb_function()->sanitize_css_color( $settings['size_chart_heading_bg']['bg'] ) . ';' : '';
				$css .= isset( $settings['size_chart_heading_color']['color'] ) ? 'color: ' . wopb_function()->sanitize_css_color( $settings['size_chart_heading_color']['color'] ) . ';' : '';
			$css     .= '}';
			$css     .= '.wopb-size-chart-wrapper .wopb-sc-table .wopb-sc-head-col:hover {';
				$css .= isset( $settings['size_chart_heading_bg']['hover_bg'] ) ? 'background-color: ' . wopb_function()->sanitize_css_color( $settings['size_chart_heading_bg']['hover_bg'] ) . ';' : '';
				$css .= isset( $settings['size_chart_heading_color']['hover_color'] ) ? 'color: ' . wopb_function()->sanitize_css_color( $settings['size_chart_heading_color']['hover_color'] ) . ';' : '';
			$css     .= '}';

			$css     .= '.wopb-sc-table-row:nth-child(even) {';
				$css .= isset( $settings['size_chart_even_row_bg']['bg'] ) ? 'background-color: ' . wopb_function()->sanitize_css_color( $settings['size_chart_even_row_bg']['bg'] ) . ';' : '';
				$css .= isset( $settings['size_chart_even_row_color']['color'] ) ? 'color: ' . wopb_function()->sanitize_css_color( $settings['size_chart_even_row_color']['color'] ) . ';' : '';
			$css     .= '}';
			$css     .= '.wopb-sc-table-row:nth-child(even):hover {';
				$css .= isset( $settings['size_chart_even_row_bg']['hover_bg'] ) ? 'background-color: ' . wopb_function()->sanitize_css_color( $settings['size_chart_even_row_bg']['hover_bg'] ) . ';' : '';
				$css .= isset( $settings['size_chart_even_row_color']['hover_color'] ) ? 'color: ' . wopb_function()->sanitize_css_color( $settings['size_chart_even_row_color']['hover_color'] ) . ';' : '';
			$css     .= '}';

			$css     .= '.wopb-sc-table-row:nth-child(odd) {';
				$css .= isset( $settings['size_chart_odd_row_bg']['bg'] ) ? 'background-color: ' . wopb_function()->sanitize_css_color( $settings['size_chart_odd_row_bg']['bg'] ) . ';' : '';
				$css .= isset( $settings['size_chart_odd_row_color']['color'] ) ? 'color: ' . wopb_function()->sanitize_css_color( $settings['size_chart_odd_row_color']['color'] ) . ';' : '';
			$css     .= '}';
			$css     .= '.wopb-sc-table-row:nth-child(odd):hover {';
				$css .= isset( $settings['size_chart_odd_row_bg']['hover_bg'] ) ? 'background-color: ' . wopb_function()->sanitize_css_color( $settings['size_chart_odd_row_bg']['hover_bg'] ) . ';' : '';
				$css .= isset( $settings['size_chart_odd_row_color']['hover_color'] ) ? 'color: ' . wopb_function()->sanitize_css_color( $settings['size_chart_odd_row_color']['hover_color'] ) . ';' : '';
			$css     .= '}';

			wopb_function()->update_css( $key, 'add', $css );
		}
	}
}
