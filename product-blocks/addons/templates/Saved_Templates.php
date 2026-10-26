<?php
/**
 * SaveTemplate Addons Core.
 *
 * @package WOPB\SaveTemplate
 * @since v.1.1.0
 */

namespace WOPB;

defined( 'ABSPATH' ) || exit;

class Saved_Templates {
	public function __construct() {
		$this->templates_post_type_callback();
		add_filter( 'manage_wopb_templates_posts_columns', array( $this, 'templates_table_head' ) );
		add_action( 'manage_wopb_templates_posts_custom_column', array( $this, 'templates_table_content' ), 10, 2 );
	}

	/**
	 * Template Heading Add
	 *
	 * @since v.1.1.0
	 * @return NULL
	 */
	public function templates_table_head( $defaults ) {
		$type_array = array( 'type' => __( 'Shortcode', 'product-blocks' ) );
		array_splice( $defaults, 2, 0, $type_array );
		return $defaults;
	}

	/**
	 * Column Content
	 *
	 * @since v.1.1.0
	 * @return NULL
	 */
	public function templates_table_content( $column_name, $post_id ) {
		echo '<code class="wopb-shortcode-copy">[product_blocks id="' . esc_attr( $post_id ) . '"]</code>';
	}

	/**
	 * Templates Post Type Register
	 *
	 * @since v.1.1.0
	 * @return NULL
	 */
	public function templates_post_type_callback() {
		$labels = array(
			'name'               => _x( 'Saved Templates', 'Templates', 'product-blocks' ),
			'singular_name'      => _x( 'Saved Template', 'Templates', 'product-blocks' ),
			'menu_name'          => __( 'Saved Templates', 'product-blocks' ),
			'parent_item_colon'  => __( 'Parent Template', 'product-blocks' ),
			'all_items'          => __( 'Saved Templates', 'product-blocks' ),
			'view_item'          => __( 'View Template', 'product-blocks' ),
			'add_new_item'       => __( 'Add New Template', 'product-blocks' ),
			'add_new'            => __( 'Add New Template', 'product-blocks' ),
			'edit_item'          => __( 'Edit Template', 'product-blocks' ),
			'update_item'        => __( 'Update Template', 'product-blocks' ),
			'search_items'       => __( 'Search Template', 'product-blocks' ),
			'not_found'          => __( 'No Template Found', 'product-blocks' ),
			'not_found_in_trash' => __( 'Not Template found in Trash', 'product-blocks' ),
		);
		$args   = array(
			'labels'              => $labels,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor' ),
			'hierarchical'        => false,
			'public'              => false,
			'rewrite'             => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'exclude_from_search' => true,
			'capability_type'     => 'page',
		);
		register_post_type( 'wopb_templates', $args );
	}
}
