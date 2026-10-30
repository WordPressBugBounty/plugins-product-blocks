<?php
/**
 * Product Video Addons Core.
 *
 * @package WOPB\ProductVideo
 * @since v.3.2.0
 */

namespace WOPB;

defined( 'ABSPATH' ) || exit;

/**
 * ProductVideo class.
 */
class ProductVideo {

	private $def_value = array(
		'type'   => 'media',
		'url'    => '',
		'img'    => '',
		'img_id' => '',
	);

	/**
	 * Setup class.
	 *
	 * @since v.3.2.0
	 */
	public function __construct() {
		add_filter( 'add_meta_boxes', array( $this, 'init_metabox_callback' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'product_meta_save' ), 10, 2 );

		add_action( 'wp_enqueue_scripts', array( $this, 'load_script' ) );

		add_filter( 'wopb_product_video', array( $this, 'product_video_thumbnail' ), 10, 3 );

		// Single Product Option
		if ( wopb_function()->get_setting( 'product_video_single' ) === 'yes' ) {
			if ( method_exists( wopb_function(), 'get_theme_name' ) && wopb_function()->get_theme_name() === 'blocksy' ) {
				add_action( 'woocommerce_product_thumbnails', array( $this, 'video_in_thumbnails' ), 100 );
			} else {
				add_filter( 'woocommerce_single_product_image_thumbnail_html', array( $this, 'image_video_thumbnail_callback' ), 10, 2 );
			}
			add_filter( 'woocommerce_single_product_image_gallery_classes', array( $this, 'add_extra_class_callback' ) );
		}
	}

	/**
	 * Product Video Class for Single Galary
	 *
	 * @param $classes
	 * @return null
	 * @since v.4.0.0
	 */
	public function add_extra_class_callback( $classes ) {
		global $product;
		$video_meta = get_post_meta( $product->get_id(), '__wopb_product_video', true );
		if ( $video_meta && ! empty( $video_meta['url'] ) ) {
			$classes[] = 'wopb-product-video-gallery';
		}
		return $classes;
	}

	/**
	 * Product Video Render for Blocksy theme
	 *
	 * @return void
	 * @since v.4.0.0
	 */
	public function video_in_thumbnails() {
		global $product;
		echo wp_kses( $this->product_video_thumbnail( '', $product, '' ), wopb_function()->allowed_html_tags() );
	}

	/**
	 * Product Video Class for Single Gallery HTML
	 *
	 * @return string
	 * @since v.4.0.0
	 */
	public function image_video_thumbnail_callback( $html, $post_thumbnail_id ) {
		global $product;
		$data = $this->get_data( $product->get_id() );
		if ( ! empty( $data['url'] ) ) {
			if ( $post_thumbnail_id && ! wopb_function()->is_builder() ) {
				$attachment_ids   = array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() );
				$total_attachment = count( $attachment_ids );
				$index            = array_search( $post_thumbnail_id, $attachment_ids );
				$position         = ! empty( $data['single_position'] ) ? $data['single_position'] : 'first';

				$attachment_id = ! empty( $data['img'] ) && ! empty( $data['img_id'] ) ? $data['img_id'] : $post_thumbnail_id;
				$prefix        = wc_get_gallery_image_html( $attachment_id );
				$prefix        = str_replace( 'woocommerce-product-gallery__image', 'woocommerce-product-gallery__image wopb-product-video-section', $prefix );
				$prefix        = str_replace( '<img', $this->product_video_thumbnail( '', '', $post_thumbnail_id ) . '<img', $prefix );
				if ( 'first' === $position && 0 == $index ) {
					$html = $prefix . $html;
				} elseif (
					( 'after_first_image' === $position && ( 0 == $index || 1 == $total_attachment ) ) ||
					( 'last' === $position && $index == $total_attachment - 1 )
				) {
					$html = $html . $prefix;
				}
				return $html;
			} else {
				$str = '<div class="woocommerce-product-gallery__image--placeholder">';
				return str_replace( $str, $str . $this->product_video_thumbnail( '', '', '' ), $html );
			}
		}
		return $html;
	}

	/**
	 * Product Video Thumbnail HTML
	 *
	 * @param $output
	 * @param $product
	 * @param $attachment_id
	 * @return string
	 * @since v.4.0.0
	 */
	public function product_video_thumbnail( $output, $product, $attachment_id ) {
		if ( ! $product ) {
			global $product;
		}
		$product_id = $product->get_id();
		$data_value = $this->get_player_settings( $product_id );
		$gallery    = $product->get_gallery_image_ids();

		if ( empty( $attachment_id ) ) {
			$attachment_id = get_post_thumbnail_id( $product_id );
		}
		if ( empty( $attachment_id ) && ! empty( $gallery ) ) {
			$attachment_id = $gallery[0];
		}

		if ( $attachment_id ) {
			$video_url    = $data_value['url'];
			$fallback_url = $data_value['img'] ? $data_value['img'] : WOPB_URL . 'assets/img/fallback.svg';
			if ( $video_url ) {
				$single_position = ! empty( $data_value['single_position'] ) ? $data_value['single_position'] : 'first';
				$item_attr       = 'muted';
				$display         = wopb_function()->get_setting( 'product_video_display' );
				add_filter( 'oembed_result', array( $this, 'oembed_video_control' ), 10, 3 );
				$video_render = wp_oembed_get( $video_url, array( 'wopb_product_video' => true ) );
				if ( ! $video_render ) {
					$video_render  = '<video class="wopb-custom-video" controls>';
					$video_render .= '<source src="' . esc_url( $video_url ) . '" type="video/mp4">';
					$video_render .= '</video>';
				}
				$output          = '<div class="wopb-product-video-wrapper" ';
					$output     .= 'data-fallback-src="' . esc_url( $fallback_url ) . '" ';
					$output     .= 'data-single-position="' . esc_attr( $single_position ) . '" ';
				$output         .= '>';
					$output     .= '<span class="wopb-video-play-icon' . ( $display !== 'icon' ? ' wopb-d-none' : '' ) . '">';
						$output .= wopb_function()->svg_icon( wopb_function()->get_setting( 'product_video_icon' ) );
					$output     .= '</span>';
					$output     .= '<span class="wopb-video-item' . ( $display === 'icon' ? ' wopb-d-none' : '' ) . '" ' . $item_attr . '>';
						$output .= $video_render;
					$output     .= '</span>';
				$output         .= '</div>';

				/**
				 * Filters the markup of a product's video.
				 *
				 * @since 4.6.0
				 * @param string $output     Video markup: the wrapper, play icon and `<video>` or oEmbed `<iframe>`.
				 * @param int    $product_id Product ID.
				 * @param array  $data_value Video settings from the `__wopb_product_video` meta.
				 */
				$output = apply_filters( 'wopb_product_video_html', $output, $product_id, $data_value );
			}
		}

		return wp_kses( $output, wopb_function()->allowed_html_tags() );
	}

	/**
	 * Modify Oembed Video Control
	 *
	 * @param $html
	 * @param $url
	 * @param $args
	 * @return NULL
	 * @since v.4.0.0
	 */
	public function oembed_video_control( $html, $url, $args ) {
		if ( isset( $args['wopb_product_video'] ) && $args['wopb_product_video'] ) {
			$param_content = 'enablejsapi=1';

			if ( str_contains( $url, 'vimeo.com' ) ) {
				$html = preg_replace( '/src="(.+?)"/', 'src="$1?' . ( 'loop=1&' . $param_content ) . '"', $html );
			} else {
				$replace_html = 'feature=oembed&' . $param_content;
				$html         = str_replace( 'feature=oembed', $replace_html, $html );
			}
		}
		return $html;
	}

	/**
	 * Load Dependent Script
	 *
	 * @return void
	 * @since v.4.0.0
	 */
	public function load_script() {
		wp_enqueue_script( 'flexslider' );
	}

	/**
	 * Get Video Meta Data
	 *
	 * @return array
	 * @since v.4.0.0
	 */
	public function get_data( $post_id ) {
		$data_value = get_post_meta( $post_id, '__wopb_product_video', true );
		return $data_value ? $data_value : $this->def_value;
	}

	/**
	 * Get Video Player Settings
	 *
	 * @param int $post_id Product ID.
	 * @return array
	 * @since 4.6.0
	 */
	private function get_player_settings( $post_id ) {
		$data_value = $this->get_data( $post_id );
		return is_array( $data_value ) ? $data_value + $this->def_value : $this->def_value;
	}

	/**
	 * Product Video Metabox Init
	 *
	 * @return void
	 *
	 * @since v.4.0.0
	 */
	public function init_metabox_callback() {
		$title = '<div class="wopb-single-product-meta-box"><img src="' . esc_url( WOPB_URL . 'assets/img/logo-sm.svg' ) . '" /><span>' . esc_html__( 'Product Video', 'product-blocks' ) . '</span></div>';
		add_meta_box(
			'wowstore-product-video-metabox',
			$title,
			array( $this, 'video_metabox_fields' ),
			'product',
			'side'
		);
	}

	/**
	 * Product Video Input Fields
	 *
	 * @return void
	 *
	 * @since v.3.2.0
	 */
	public function video_metabox_fields() {
		global $post;
		$data_value         = $this->get_player_settings( $post->ID );
		$type_depend_class  = ' wopb-media-depend';
		$type_depend_class .= $data_value['type'] !== 'media' ? ' wopb-d-none' : '';
		$single_positions   = array(
			'first'             => __( 'First Position', 'product-blocks' ),
			'last'              => __( 'Last Position', 'product-blocks' ),
			'after_first_image' => __( 'After First Image', 'product-blocks' ),
		)
		?>
		<div class="wopb-video-meta-fields">
			<div class="wopb-video-type-tabs">
				<label class="wopb-video-type-tab<?php echo esc_attr( $data_value['type'] !== 'media' ? ' wopb-active' : '' ); ?>">
					<input name="wopb_video_type" value="youtube" type="radio">
					<?php echo esc_html__( 'Youtube / Vimeo', 'product-blocks' ); ?>
				</label>
				<label class="wopb-video-type-tab<?php echo esc_attr( $data_value['type'] === 'media' ? ' wopb-active' : '' ); ?>">
					<input name="wopb_video_type" value="media" type="radio">
					<?php echo esc_html__( 'Media Library', 'product-blocks' ); ?>
				</label>
			</div>
			<p class="form-field wopb_video_url_field ">
				<label for="wopb_video_url"><?php echo esc_html__( 'Video URL', 'product-blocks' ); ?></label>
				<span class="wopb-input-media-group">
					<input type="text" class="short" name="wopb_video_url" id="wopb_video_url" value="<?php echo esc_url( $data_value['url'] ); ?>">
					<a class="wopb-open-media wopb-open-media-btn <?php echo esc_attr( $type_depend_class ); ?>" data-library-type="video" data-title="Inset Video" data-btn-text="Add New Video" data-target="#wopb_video_url">
						<span class="dashicons dashicons-cloud-upload"></span>
					</a>
				</span>
				<span class="wopb-video-note"><?php echo esc_html__( 'Note: Custom video frame size should be same as the featured image', 'product-blocks' ); ?></span>
			</p>
			<p class="form-field wopb_video_image_field">
				<label for="wopb_video_image"><?php echo esc_html__( 'Gallery Image', 'product-blocks' ); ?></label>
				<span class="wopb-input-media-group">
					<input type="text" class="short" name="wopb_video_image" id="wopb_video_image" value="<?php echo esc_url( $data_value['img'] ); ?>">
					<input type="hidden" name="wopb_video_image_id" id="wopb_video_image_id" value="<?php echo esc_attr( $data_value['img_id'] ); ?>">
					<a class="wopb-open-media wopb-open-media-btn" data-library-type="image" data-title="Inset Image" data-btn-text="Add New Image" data-target="#wopb_video_image" data-target2="#wopb_video_image_id">
						<span class="dashicons dashicons-cloud-upload"></span>
					</a>
				</span>
				<span class="wopb-video-note"><?php echo esc_html__( 'Note: Image size should be same as the featured image', 'product-blocks' ); ?></span>
			</p>
			<p class="form-field">
				<label for="wopb_video_single_position">
					<?php echo esc_html__( 'Position in Single Product Gallery', 'product-blocks' ); ?>
				</label>
				<select name="wopb_video_single_position" class="wopb-video-single-position" id="wopb_video_single_position">
					<?php
					foreach ( $single_positions as $key => $value ) {
						?>
						<option
							value="<?php echo esc_attr( $key ); ?>"
						<?php
							selected( $data_value['single_position'] ?? '', $key );
						?>
						>
						<?php echo esc_html( $value ); ?>
						</option>
					<?php } ?>
				</select>
			</p>
			<?php
			/**
			 * Fires in the product video meta box, after the gallery position field, for
			 * add-ons that print their own fields. Free keeps the meta it already holds.
			 *
			 * @since 4.6.0
			 * @param \WP_Post $post Product being edited.
			 */
			do_action( 'wopb_product_video_fields', $post );
			?>
		</div>
		<?php
	}

	/**
	 * Backorder Simple Product Custom Data Save
	 *
	 * @param $post_id
	 * @return void
	 * @since v.3.2.0
	 */
	public function product_meta_save( $post_id ) {
		if ( ! $post_id ) {
			return;
		}
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$data = $this->get_data( $post_id );
		if ( isset( $_POST['wopb_video_type'] ) ) {
			$data['type'] = sanitize_text_field( wp_unslash( $_POST['wopb_video_type'] ) );
		}
		if ( isset( $_POST['wopb_video_url'] ) ) {
			$data['url'] = sanitize_text_field( wp_unslash( $_POST['wopb_video_url'] ) );
		}
		if ( isset( $_POST['wopb_video_image'] ) ) {
			$data['img'] = sanitize_text_field( wp_unslash( $_POST['wopb_video_image'] ) );
		}
		if ( isset( $_POST['wopb_video_image_id'] ) ) {
			$data['img_id'] = sanitize_text_field( wp_unslash( $_POST['wopb_video_image_id'] ) );
		}
		if ( isset( $_POST['wopb_video_single_position'] ) ) {
			$data['single_position'] = sanitize_text_field( wp_unslash( $_POST['wopb_video_single_position'] ) );
		}
		update_post_meta( $post_id, '__wopb_product_video', $data );
	}
}
