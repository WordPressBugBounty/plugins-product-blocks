<?php
defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Class is prefixed with the plugin's former name (ProductX); kept so existing integrations keep working.
class ProductXElement extends OxyEl {

	public function init() {
		// Do some initial things here.
	}

	public function afterInit() {
		$this->removeApplyParamsButton();
	}

	public function name() {
		return __( 'WowStore Templates', 'product-blocks' );
	}

	public function slug() {
		return 'productx-templates';
	}

	public function icon() {
		return WOPB_URL . 'addons/oxygen/icon.svg';
	}

	public function button_place() {
		return 'interactive';
	}

	public function button_priority() {
		return 9;
	}

	public function render( $options, $defaults, $content ) {
		$templates = $options['templates'];

		if ( $templates ) {
			if ( isset($_GET['action']) && strpos(sanitize_key($_GET['action']), 'oxy_render_oxy') !== false ) { // phpcs:ignore
                echo wopb_function()->build_css_for_inline_print($templates, true); //phpcs:ignore
			}
			$args      = array(
				'p'         => $templates,
				'post_type' => 'wopb_templates',
			);
			$the_query = new \WP_Query( $args );
			if ( $the_query->have_posts() ) {
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					the_content();
				}
				wp_reset_postdata();
			}
		} elseif ( isset( $_GET['action'] ) && strpos( sanitize_text_field( wp_unslash( $_GET['action'] ) ), 'oxy_render_oxy' ) !== false ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			// phpcs:ignore
				/* translators: %s: is no of template */
				echo '<p style="text-align:center;">' . sprintf( esc_html__( 'Pick a Template from your saved ones. Or create a template from: %s.', 'product-blocks' ) . ' ', '<strong><i>' . esc_html( 'Dashboard > WowStore > Saved Templates' ) . '</i></strong>' ) . '</p>';
		}
	}

	public function controls() {
		$this->addOptionControl(
			array(
				'type'    => 'dropdown',
				'name'    => esc_html__( 'Select Your Template', 'product-blocks' ),
				'slug'    => 'templates',
				'default' => '',
			)
		)->setValue( wopb_function()->get_all_lists( 'wopb_templates' ) )->rebuildElementOnChange();
	}

	public function defaultCSS() {
		wopb_function()->register_scripts_common();
	}
}
new ProductXElement();
