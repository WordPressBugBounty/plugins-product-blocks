<?php
namespace WOPB;

defined( 'ABSPATH' ) || exit;

class Condition {
	private $header_id      = '';
	private $footer_id      = '';
	private $theme_name     = '';
	private $is_block_theme = '';
	public function __construct() {
		add_action( 'wp', array( $this, 'checkfor_header_footer' ), 999 );
		add_filter( 'template_include', array( $this, 'include_builder_files' ), 999 );
	}

	public function checkfor_header_footer() {
		$this->theme_name     = get_template();
		$this->is_block_theme = wp_is_block_theme();
		$header_id            = wopb_function()->conditions( 'header' );
		$footer_id            = wopb_function()->conditions( 'footer' );
		global $WOPB_HEADER_ID;
		global $WOPB_FOOTER_ID;

		if ( $header_id ) {
			$WOPB_HEADER_ID  = $header_id;
			$this->header_id = $header_id;
			do_action(
				'wopb_enqueue_wowstore_block_css',
				array(
					'post_id' => $header_id,
					'css'     => '',
				)
			);
			if ( $this->is_block_theme ) {
				add_action( 'wp_head', array( $this, 'wopb_header_builder_template' ) );
			} else {
				switch ( $this->theme_name ) {
					case 'astra':
						remove_all_actions( 'astra_header' );
						add_action( 'astra_header', array( $this, 'wopb_header_builder_template' ) );
						break;
					default:
						add_action( 'get_header', array( $this, 'wopb_header_builder_template' ) );
				}
			}
		}
		if ( $footer_id ) {
			$WOPB_FOOTER_ID  = $footer_id;
			$this->footer_id = $footer_id;
			do_action(
				'wopb_enqueue_wowstore_block_css',
				array(
					'post_id' => $footer_id,
					'css'     => '',
				)
			);
			if ( $this->is_block_theme ) {
				add_action( 'wp_footer', array( $this, 'wopb_footer_builder_template' ) );
			} else {
				switch ( $this->theme_name ) {
					case 'astra':
						remove_all_actions( 'astra_footer' );
						add_action( 'astra_footer', array( $this, 'wopb_footer_builder_template' ) );
						break;
					case 'generatepress':
						remove_action( 'generate_footer', 'generate_construct_footer_widgets' );
						remove_action( 'generate_footer', 'generate_construct_footer' );
						add_action( 'generate_footer', array( $this, 'wopb_footer_builder_template' ) );
						break;
					default:
						add_action( 'get_footer', array( $this, 'wopb_footer_builder_template' ) );
				}
			}
		}
	}

	public function wopb_header_builder_template() {
		if ( $this->header_id ) {
			if ( $this->is_block_theme ) {
				remove_action( 'wp_head', array( $this, 'wopb_header_builder_template' ) );
			} else {
				if ( $this->theme_name != 'astra' ) {  // Astra theme issue
					require_once WOPB_PATH . 'addons/builder/templates/header.php';
				}
				$templates   = array();
				$templates[] = 'header.php';
				remove_all_actions( 'wp_head' );
				if ( $this->theme_name != 'bricks' ) {  // Conflict with Bricks Builder Backend
					$wopb_buffer_level_1 = ob_get_level();
					ob_start();
					try {
						locate_template( $templates, true );
						ob_get_clean();
					} finally {
						while ( ob_get_level() > $wopb_buffer_level_1 ) {
							if ( ! ob_end_clean() ) {
								break;
							}
						}
					}
				} else {
					locate_template( $templates, true );
					wp_enqueue_style( 'wp-block-library' );  // Gutenberg CSS issue Bricks Builder frontend
				}
			}
			?> 
				<header id="wopb-header-template">
					<?php
						echo wp_kses( wopb_function()->content( $this->header_id ), $this->allowed_template_html() );
					?>
				</header> 
			<?php
		}
	}
	public function wopb_footer_builder_template() {
		if ( $this->footer_id ) {
			if ( ! $this->is_block_theme ) {
				if ( $this->theme_name == 'astra' ) {  // Astra theme issue
					wp_footer();
				} else {
					require_once WOPB_PATH . 'addons/builder/templates/footer.php';
				}
				$templates   = array();
				$templates[] = 'footer.php';
				remove_all_actions( 'wp_footer' );
				if ( $this->theme_name != 'bricks' ) { // Conflict with Bricks Builder Backend
					$wopb_buffer_level_2 = ob_get_level();
					ob_start();
					try {
						locate_template( $templates, true );
						ob_get_clean();
					} finally {
						while ( ob_get_level() > $wopb_buffer_level_2 ) {
							if ( ! ob_end_clean() ) {
								break;
							}
						}
					}
				} else {
					locate_template( $templates, true );
				}
			}
			?>
							<footer id="wopb-footer-template" class="<?php echo 'wopb-builderid-' . esc_attr( $this->footer_id ); ?>" role="contentinfo">
					<?php
						echo wp_kses( wopb_function()->content( $this->footer_id ), $this->allowed_template_html() );
					?>
				</footer> 
			<?php
		}
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

	public function include_builder_files( $template ) {
		$includes = wopb_function()->conditions( 'includes' );
		return $includes ? $includes : $template;
	}
}
