<?php
/**
 * FlipImage Addons Core.
 * 
 * @package WOPB\FlipImage
 * @since v.1.1.0
 */

namespace WOPB;

defined('ABSPATH') || exit;

class FlipImage {

    private $mobile_disable         = '';
    private $animation_type         = '';
    private $group_variable_disable = '';
    private $image_source           = '';

    public function __construct() {
        $this->mobile_disable           = wopb_function()->get_setting( 'flip_mobile_device_disable' );
        $this->animation_type           = wopb_function()->get_setting( 'flip_animation_type' );
        $this->group_variable_disable   = wopb_function()->get_setting( 'flip_group_variable_disable' );
        $this->image_source             = wopb_function()->get_setting( 'flip_image_source' );

        add_action( 'wp_enqueue_scripts', array( $this, 'add_flip_image_scripts' ) );
        if ( $this->image_source == 'feature' ) {
            add_action( 'add_meta_boxes', array( $this, 'feature_image_add_metabox' ) );
            add_action( 'save_post', array( $this, 'feature_image_save' ), 10, 1 );
        }
        add_action( 'woocommerce_before_shop_loop_item', function () {
            add_filter( 'wp_get_attachment_image', array( $this, 'flip_image_default_callback' ), 10, 1 );
        }, 10 );
        add_filter( 'wopb_flip_image', array( $this, 'flip_image_callback' ), 10, 3 );
    }

    /**
     * Flip Image Script
     *
     * @return NULL
     * @since v.3.1.5
     */
    public function flip_image_callback( $output, $product, $size = 'full' ) {
        $image_id = '';
        if ( $this->group_variable_disable == 'yes' && in_array( $product->get_type(), array( 'grouped', 'variable' ) ) ) {
            return '';
        }
        if ( $this->mobile_disable == 'yes' ) {
            if ( wp_is_mobile() ) {
                return;
            }
        }

        if ( $this->image_source == 'feature' ) {
            $image_id = get_post_meta( $product->get_id(), '_flip_image_id', true );
        } else {
            $attachment_ids = $product->get_gallery_image_ids();
            $image_id = isset( $attachment_ids[0] ) ? $attachment_ids[0] : '';
        }

        return $image_id ? '<img class="wopb-flip-image wopb-' . esc_attr( $this->animation_type ) . '" alt="' . esc_attr( $product->get_name() ) . '" src="' . esc_url( wp_get_attachment_image_url( $image_id, $size ) ) . '" />' : '';
    }

    /**
     * Flip Image Script
     *
     * @return NULL
     * @since v.3.1.5
     */
    public function add_flip_image_scripts() {
        wp_enqueue_style( 'wopb-animation-css', WOPB_URL . 'assets/css/animation.min.css', array(), WOPB_VER );
        wp_enqueue_style( 'wopb-flip-image-style', WOPB_URL . 'addons/flip_image/css/flip_image.min.css', array(), WOPB_VER );
        wp_enqueue_script( 'wopb-flip-image-script', WOPB_URL . 'addons/flip_image/js/flip_image.js', array( 'jquery', 'wp-api-fetch' ), WOPB_VER, true );
    }


    /**
     * Default Shop Page Image Flip
     *
     * @return null
     * @since v.3.1.5
     */
     public function flip_image_default_callback( $html ) {
         global $product;
         global $woocommerce_loop;
         if (
             $product &&
             ( is_archive() || $woocommerce_loop )
         ) {
             $html .= $this->flip_image_callback( '', $product );
             return $html;
         }
         return $html;
    }


    /**
	 * Flip Image Meta Box Register
     * 
	 * @return NULL
     * @since v.1.1.0
	 */
    function feature_image_add_metabox() {
        $title = '<div class="wopb-single-product-meta-box"><img src="' . WOPB_URL . 'assets/img/logo-sm.svg" /><span>'. __( 'Flip Image', 'product-blocks' ).'</span></div>';
        add_meta_box( 'flipimage-feature-image', $title, array( $this, 'feature_image_metabox' ), 'product', 'side', 'low' );
    }

    /**
     * Flip Image Meta Box
     *
     * @param $post
     * @return NULL
     * @since v.1.1.0
     */
    function feature_image_metabox( $post ) {
        $image_id = get_post_meta( $post->ID, '_flip_image_id', true );
        $thumbnail_html = $image_id ? wp_get_attachment_image( $image_id, array( 254, 254 ) ) : ''; ?>
        <div class="wopb-flip-image">
            <?php echo $thumbnail_html ? wp_kses_post( $thumbnail_html ) : '<img class="hidden" src=""/>'; ?>
            <p class="hide-if-no-js"><a href="javascript:;" id="<?php echo $thumbnail_html ? 'remove_feature_image_button' : 'upload_feature_image_button'; ?>" data-uploader_title="<?php esc_attr_e( 'Select Flip Image', 'product-blocks' ); ?>" data-uploader_button_text="<?php esc_attr_e( 'Set Flip Image', 'product-blocks' ); ?>"><?php  echo $thumbnail_html ? esc_html__( 'Remove Flip Image', 'product-blocks' ) : esc_html__( 'Set Flip Image Source', 'product-blocks' ); ?></a></p>
            <input type="hidden" id="upload_feature_image" name="_flip_image" value="<?php echo esc_attr( $image_id ); ?>" />
        </div>
    <?php }

    /**
     * Flip Image Save From Meta Box
     *
     * @param $post_id
     * @return NULL
     * @since v.1.1.0
     */
    function feature_image_save( $post_id ) {
        if ( isset( $_POST['_flip_image'] ) ) { //phpcs:disable WordPress.Security.NonceVerification.Missing
            $image_id = (int) sanitize_text_field( $_POST['_flip_image'] );  //phpcs:disable WordPress.Security.NonceVerification.Missing
            if ( $image_id ) {
                update_post_meta( $post_id, '_flip_image_id', $image_id );
            } else {
                delete_post_meta( $post_id, '_flip_image_id' );
            }
        }
    }
}