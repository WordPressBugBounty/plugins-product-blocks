<?php
/**
 * Shortcode Core.
 *
 * @package WOPB\Shortcode
 * @since v.1.1.0
 */

namespace WOPB;

defined( 'ABSPATH' ) || exit;

class Shortcode {
	/**
	 * Maximum nesting depth of [product_blocks] inside saved templates.
	 */
	const MAX_DEPTH = 10;

	/**
	 * IDs of the templates being rendered right now, to stop a template from rendering itself.
	 *
	 * @var int[]
	 */
	private static $rendering = array();

	public function __construct() {
		add_shortcode( 'product_blocks', array( $this, 'shortcode_callback' ) );
	}

	/**
	 * Shortcode Callback
	 *
	 * @since v.1.1.0
	 * @return STRING | HTML of the shortcode
	 */
	public function shortcode_callback( $atts = array(), $content = null ) {
		$atts = shortcode_atts(
			array(
				'id' => '',
			),
			$atts
		);

		$content     = '';
		$pre_content = '';
		$id          = is_numeric( $atts['id'] ) ? absint( $atts['id'] ) : 0;
		if ( $id && ! in_array( $id, self::$rendering, true ) && count( self::$rendering ) < self::MAX_DEPTH ) {
			$content_post = get_post( $id );
			if ( $content_post && 'publish' === $content_post->post_status && '' === $content_post->post_password && $this->is_allowed_post_type( $content_post->post_type ) ) {
				do_action(
					'wopb_enqueue_wowstore_block_css',
					array(
						'post_id' => $id,
						'css'     => '',
					)
				);

				// Breakdance builder support for its shortcode render
				$current_url            = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
				$_breakdance_doing_ajax = isset( $_GET['_breakdance_doing_ajax'] ) ? sanitize_text_field( wp_unslash( $_GET['_breakdance_doing_ajax'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only: Breakdance's preview request flag only decides whether the template CSS is printed inline; builder preview URLs cannot carry a nonce.
				if ( ! empty( $_breakdance_doing_ajax ) ||
					strpos( $current_url, 'bricks/v1/render_element' ) !== false
				) {

					if ( ! empty( $_breakdance_doing_ajax ) && get_template() === 'bricks' ) {
						get_header();
					}
					// A <style> tag whose id is esc_attr()'d and whose CSS has tags stripped; wp_kses() would turn child selectors (>) and & into entities and break the CSS.
					$pre_content .= wopb_function()->build_css_for_inline_print( $id, true );
				}
				self::$rendering[] = $id;
				try {
					$content = do_blocks( $content_post->post_content );
					$content = do_shortcode( $content );
				} finally {
					array_pop( self::$rendering );
				}
				$content = str_replace( ']]>', ']]&gt;', $content );
				$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content );
				$content = preg_replace( '/^(?:<br\s*\/?>\s*)+/', '', $content );
				return $pre_content . '<div class="wopb-shortcode" data-postid="' . esc_attr( $id ) . '">' . wp_kses( $content, $this->allowed_template_html() ) . '</div>';
			}
		}
		return '';
	}

	/**
	 * Allowed HTML for rendered template content: the shared form list plus the SVG and microdata attributes the blocks print.
	 *
	 * @return array
	 */
	private function allowed_template_html() {
		return wopb_function()->allowed_form_html(
			array(
				'svg'    => array(
					'aria-hidden' => true,
					'focusable'   => true,
					'role'        => true,
				),
				'g'      => array( 'data-*' => true ),
				'path'   => array(
					'data-*'    => true,
					'fill-rule' => true,
				),
				'strong' => array( 'itemprop' => true ),
			)
		);
	}

	/**
	 * Whether the shortcode may render posts of this type: saved templates, or a type the public can already view.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	private function is_allowed_post_type( $post_type ) {
		return 'wopb_templates' === $post_type || is_post_type_viewable( $post_type );
	}
}
