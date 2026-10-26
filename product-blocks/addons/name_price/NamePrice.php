<?php
/**
 * Name Your Price Addons Core.
 *
 * @package WOPB\NamePrice
 * @since v.4.0.0
 */

namespace WOPB;

defined( 'ABSPATH' ) || exit;

/**
 * NamePrice class.
 */
class NamePrice {

	/**
	 * Setup class.
	 *
	 * @since v.4.0.0
	 */
	private $def_value = array(
		'enable'       => 'no',
		'suggested'    => '',
		'chunk_enable' => 'no',
		'chunk'        => '',
		'min'          => '',
		'max'          => '',
	);

	public function __construct() {

		// Show Suggested Price for Simple Products.
		add_action( 'woocommerce_product_options_pricing', array( $this, 'simple_product_fields' ) );

		// Variation options Show Suggested Prices.
		add_action( 'woocommerce_variation_options_pricing', array( $this, 'variable_product_fields' ), 10, 3 );

		// Save Custom Fields of Simple Products
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_simple_product_fields' ), 10, 1 );

		// Save Custom Fields of Variable Products
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_variable_product_fields' ), 10, 2 );

		// Add Custom Fields for Simple Products
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'product_suggested_name_price' ) );

		// Add to cart link change for redirect
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'loop_add_to_cart_link' ), 100, 3 );

		// Get the add to cart button text for the single page.
		add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'single_add_cart_label_text' ), 9999 );

		// Get the add to cart button text.
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'shop_add_cart_label_text' ), 9999, 2 );

		// Check If Price is Between Min and Max Price
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 10, 4 );

		// Filter cart item data for add to cart requests.
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'set_custom_prices' ), 10, 3 );

		// Set cart item price
		add_filter( 'woocommerce_get_cart_contents', array( $this, 'get_cart_contents' ), 10, 1 );

		// See if prices should be shown for each variation after selection.
		add_filter( 'woocommerce_available_variation', array( $this, 'get_all_variations' ), 10, 3 );

		add_filter( 'woocommerce_get_price_html', array( $this, 'price_html' ), 10, 2 );

		// CSS Generator
		add_action( 'wopb_save_settings', array( $this, 'generate_css' ), 10, 1 );
	}

	/**
	 * Display Simple Product
	 *
	 * @return void
	 */
	public function simple_product_fields() {
		global $product_object;
		$this->generate_fields( $product_object );
	}

	/**
	 * Show Custom Field for Variable
	 *
	 * @param $loop
	 * @param $variation_data
	 * @param $variation
	 * @return void
	 * @since v.4.0.0
	 */
	public function variable_product_fields( $loop, $variation_data, $variation ) {
		$product = wc_get_product( $variation->ID );
		$this->generate_fields( $product, $loop );
	}

	/**
	 * Custom Fields and Settings.
	 *
	 * @param $product
	 * @param $loop
	 * @return void
	 * @since v.4.0.0
	 */
	public function generate_fields( $product, $loop = '' ) {
		$data            = $this->get_data( $product->get_id() );
		$loop            = '' !== $loop ? '[' . $loop . ']' : '';
		$currency_symbol = ' (' . get_woocommerce_currency_symbol() . ')'; ?>
		<div class="wopb-name-price-option">
			<div>
				<?php
				woocommerce_wp_checkbox(
					array(
						'id'          => '_wopb_name_price' . $loop,
						'class'       => 'checkbox wopb-name-price-enable',
						'value'       => $data['enable'],
						'label'       => __( 'Name Your Price', 'product-blocks' ),
						'description' => __( 'Turn on custom price / Name Your Price for this Product', 'product-blocks' ),
						'default'     => 'no',
					)
				);
				?>
			</div>
			<div class="wopb-name-price-wrap">
				<?php
				woocommerce_wp_checkbox(
					array(
						'id'          => '_wopb_name_hide_price' . $loop,
						'class'       => 'checkbox',
						'value'       => isset( $data['hide_price'] ) ? $data['hide_price'] : '',
						'label'       => __( 'Hide Main Price', 'product-blocks' ),
						'description' => __( 'Turn on if you want to hide main price in single product', 'product-blocks' ),
						'default'     => 'no',
					)
				);
				?>
				<?php
				woocommerce_wp_text_input(
					array(
						'id'        => '_wopb_name_price_suggested' . $loop,
						'type'      => 'number',
						'label'     => __( 'Suggested Price', 'product-blocks' ) . $currency_symbol,
						'data_type' => 'price',
						'value'     => $data['suggested'],
					)
				);
				woocommerce_wp_checkbox(
					array(
						'id'          => '_wopb_name_price_chunk_enable' . $loop,
						'value'       => $data['chunk_enable'],
						'label'       => __( 'Price Chunk', 'product-blocks' ),
						'description' => __( 'Show Chunked Price Instead of Input Field', 'product-blocks' ),
						'default'     => 'no',
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'          => '_wopb_name_price_chunk' . $loop,
						'type'        => 'text',
						'label'       => __( 'Suggested Chunk', 'product-blocks' ),
						'value'       => $data['chunk'],
						'description' => __( 'Suggested Chunk eg: 10, 15, 20', 'product-blocks' ),
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'                => '_wopb_name_price_min' . $loop,
						'type'              => 'number',
						'label'             => __( 'Minimum Price', 'product-blocks' ) . $currency_symbol,
						'data_type'         => 'price',
						'value'             => $data['min'],
						'custom_attributes' => array( 'min' => 0 ),
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'                => '_wopb_name_price_max' . $loop,
						'type'              => 'number',
						'label'             => __( 'Maximum Price', 'product-blocks' ) . $currency_symbol,
						'data_type'         => 'price',
						'value'             => $data['max'],
						'custom_attributes' => array( 'min' => 0 ),
					)
				);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Save Custom Field for Simple Product
	 *
	 * @param $post_id
	 * @param string $loop it's loop number for variable product
	 * @return void
	 * @since v.4.0.0
	 */
	public function save_simple_product_fields( $post_id, $loop = '' ) {
		$this->save_product_fields( $post_id );
	}

	/**
	 * Save Custom Field for Variable Product
	 *
	 * @return void
	 * @since v.4.0.0
	 */
	public function save_variable_product_fields( $variation_id, $loop ) {
		$this->save_product_fields( $variation_id, $loop );
	}

	/**
	 * Get Video Meta Data
	 *
	 * @return array
	 * @since v.4.0.0
	 */
	public function get_data( $post_id ) {
		$data_value = get_post_meta( $post_id, '_wopb_name_price', true );
		return is_array( $data_value ) ? array_merge( $this->def_value, $data_value ) : $this->def_value;
	}

	/**
	 * Save Custom Field for Variable or Simple Product
	 *
	 * @return void
	 * @since v.4.0.0
	 */
	public function save_product_fields( $product_id, $loop = '' ) {
		$data     = $this->def_value;
		$key_list = array(
			'enable'       => '_wopb_name_price',
			'hide_price'   => '_wopb_name_hide_price',
			'suggested'    => '_wopb_name_price_suggested',
			'chunk_enable' => '_wopb_name_price_chunk_enable',
			'chunk'        => '_wopb_name_price_chunk',
			'min'          => '_wopb_name_price_min',
			'max'          => '_wopb_name_price_max',
		);
		$input    = array();
		foreach ( $key_list as $key => $val ) {
			if ( isset( $_POST[ $val ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce authorizes this product-save hook.
				// loop is coming from woocommcerce and starts from 0.
				if ( '' !== $loop && 0 <= $loop ) {
					if ( is_array( $_POST[ $val ] ) && isset( $_POST[ $val ][ $loop ] ) && is_scalar( $_POST[ $val ][ $loop ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
						$input[ $key ] = sanitize_text_field( wp_unslash( $_POST[ $val ][ $loop ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
					}
				} elseif ( is_scalar( $_POST[ $val ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$input[ $key ] = sanitize_text_field( wp_unslash( $_POST[ $val ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				}
			}
		}
		update_post_meta( $product_id, '_wopb_name_price', array_merge( $data, $input ) );
	}

	/**
	 * Custom Name Price Field for Simple Product Name Price Addon
	 *
	 * @return void
	 * @since v.4.0.0
	 */
	public function product_suggested_name_price() {
		global $product;
		$type = $product->get_type();
		if ( 'variable' !== $type && 'grouped' !== $type && 'external' !== $type ) {
			$wopb_buffer_level_1 = ob_get_level();
			ob_start();
			try {
				$this->common_data( $product->get_id() );
				$name_price_data_safe = wopb_function()->wp_kses_safe( ob_get_clean() );
			} finally {
				while ( ob_get_level() > $wopb_buffer_level_1 ) {
					if ( ! ob_end_clean() ) {
						break;
					}
				}
			}
            echo $name_price_data_safe; // phpcs:ignore
		}
	}

	/**
	 * Generate HTML for the Frontend
	 *
	 * @return void | null
	 * @since v.4.0.0
	 */
	public function common_data( $product_id ) {
		$data = $this->get_data( $product_id );
		if ( 'yes' !== $data['enable'] ) {
			return;
		}
		$label_min          = wopb_function()->get_setting( 'name_price_min_show' );
		$label_max          = wopb_function()->get_setting( 'name_price_max_show' );
		$price_title        = wopb_function()->get_setting( 'name_price_label' );
		$chunk_price_enable = $data['chunk_enable'];
		$price_min          = $data['min'];
		$price_max          = $data['max'];
		$price_chunk        = $data['chunk'];
		$price_chunk        = $price_chunk ? explode( ',', $price_chunk ) : array();
		$price_suggest      = $data['suggested'];

		if ( $price_suggest || $price_min || $price_max || ! empty( $price_chunk ) ) {
			?>
			<div class="wopb-name-price-main-wrapper">
				<div
					class="wopb-name-price-wrapper"
					data-min="<?php echo esc_attr( wopb_function()->get_price_data( $price_min )['value'] ); ?>"
					data-max="<?php echo esc_attr( wopb_function()->get_price_data( $price_max )['value'] ); ?>"
				>
					<div class="wopb-input-section<?php echo esc_attr( $chunk_price_enable === 'yes' ? ' wopb-d-none' : '' ); ?>">
						<label class="wopb-name-price-label" for="wopb-name-price-box">
							<?php echo esc_html( $price_title ); ?>
						</label>
						<input
							type="number"
							id="wopb-name-price-box"
							class="wopb-name-price-field"
							name="wopb_custom_prices"
							min="<?php echo esc_attr( $price_min ? wopb_function()->get_price_data( $price_min )['value'] : 1 ); ?>"
							<?php echo esc_attr( $price_max ? 'max="' . wopb_function()->get_price_data( $price_max )['value'] . '"' : '' ); ?>
							value="<?php echo esc_attr( $price_suggest ? wopb_function()->get_price_data( $price_suggest )['value'] : ( $price_min ? wopb_function()->get_price_data( $price_min )['value'] : 1 ) ); ?>"
						/>
					</div>
					<?php if ( count( $price_chunk ) > 0 ) { ?>
						<div class="wopb-chunk-price-wrapper">
							<?php foreach ( $price_chunk as $key => $chunk ) { ?>
								<span>
									<input
										type="radio"
										id="wopb-chunk-<?php echo esc_attr( $key ); ?>"
										name="wopb_custom_prices_suggestion"
										value="<?php echo esc_attr( wopb_function()->get_price_data( $chunk )['value'] ); ?>"
										class="wopb-custom-chunk-prices"
									/>
									<label class="wopb-chunk-price-label" for="wopb-chunk-<?php echo esc_attr( $key ); ?>"><?php echo wc_price( wopb_function()->get_price_data( $chunk )['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
								</span>
							<?php } ?>
						</div>
					<?php } ?>
				</div>

				<div id="wopb-price-warning"></div>
				<div class="wopb-min-max-price">
					<?php
					if ( $price_min && 'yes' === $label_min ) {
						$min_safe = wopb_function()->wp_kses_safe( $this->min_max_generate( 'minimum', wopb_function()->get_setting( 'name_price_min' ), $price_min ) );
						echo $min_safe; // phpcs:ignore
					}
					if ( $price_max && 'yes' === $label_max ) {
						$max_safe = wopb_function()->wp_kses_safe( $this->min_max_generate( 'maximum', wopb_function()->get_setting( 'name_price_max' ), $price_max ) );
						echo $max_safe; // phpcs:ignore
					}
					?>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Generate Min and max Price for Name Your Price Addon.
	 *
	 * @param $class_min_max
	 * @param $prices_titles
	 * @param $price_min_max
	 * @return string
	 * @since v.4.0.0
	 */
	public function min_max_generate( $class_min_max, $prices_titles, $price_min_max ) {
		$html      = '<div id="wopb-' . $class_min_max . '-price">';
			$html .= '<span class="wopb-range-label">' . $prices_titles . '</span> <span class="wopb-range-price">' . wc_price( wopb_function()->get_price_data( $price_min_max )['value'] ) . '</span>';
		$html     .= '</div>';
		return $html;
	}

	/**
	 * Add To Cart Link
	 *
	 * @param $link
	 * @param $product
	 * @param $args
	 * @return string
	 * @since v.4.0.0
	 */
	public function loop_add_to_cart_link( $link, $product, $args ) {
		$product_id    = $product->get_id();
		$data          = $this->get_data( $product_id );
		$name_price    = $data['enable'];
		$price_min     = $data['min'];
		$price_max     = $data['max'];
		$price_chunk   = $data['chunk'];
		$price_chunk   = $price_chunk ? explode( ',', $price_chunk ) : array();
		$price_suggest = $data['suggested'];

		if ( $name_price && ( $price_suggest || $price_min || $price_max || ! empty( $price_chunk ) ) ) {
			return str_replace(
				array( 'href="?add-to-cart=' . $product_id . '"', 'add_to_cart_button', 'ajax_add_to_cart' ),
				array( 'href="' . get_permalink( $product_id ) . '"', '', '' ),
				$link
			);
		}
		return $link;
	}

	/**
	 * Rename Single Product Page Add to Cart
	 *
	 * @param $label
	 * @return string
	 * @since v.4.0.0
	 */
	public function single_add_cart_label_text( $label ) {
		$product_id = get_the_ID();
		$data       = $this->get_data( $product_id );
		if ( $product_id && 'yes' === $data['enable'] && $text = wopb_function()->get_setting( 'name_price_single' ) ) {
			$label = $text;
		}
		return $label;
	}

	/**
	 * Rename the button on the Shop page
	 *
	 * @param $label
	 * @param $product
	 * @return string
	 * @since v.4.0.0
	 */
	public function shop_add_cart_label_text( $label, $product ) {
		$data = $this->get_data( $product->get_id() );
		if ( $product && isset( $data['enable'] ) && 'yes' === $data['enable'] && $text = wopb_function()->get_setting( 'name_price_archive' ) ) {
			$label = $text;
		}
		return $label;
	}

	/**
	 * Set Custom Price for Individual Product
	 *
	 * @param $cart
	 * @param $product_id
	 * @return array
	 * @since v.4.0.0
	 */
	public function set_custom_prices( $cart, $product_id, $variation_id = 0 ) {
		unset( $cart['wopb_custom_prices'] );
		$price = $this->get_valid_custom_price( $variation_id ? $variation_id : $product_id, $this->get_requested_custom_price() );
		if ( false !== $price ) {
			$cart['wopb_custom_prices'] = $price;
		}
		return $cart;
	}

	/**
	 * Read price input without letting arrays, objects or booleans become money.
	 *
	 * @return string|int|float|null
	 */
	private function get_requested_custom_price() {
		// WooCommerce's public add-to-cart flow does not require an admin nonce.
		return isset( $_POST['wopb_custom_prices'] ) && ( is_string( $_POST['wopb_custom_prices'] ) || is_int( $_POST['wopb_custom_prices'] ) || is_float( $_POST['wopb_custom_prices'] ) ) ? wp_unslash( $_POST['wopb_custom_prices'] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Monetary validation is shared by all price application points below.
	}

	/**
	 * Apply the effective product's opt-in and bounds to a finite decimal price.
	 *
	 * @param int   $product_id Effective product or variation ID.
	 * @param mixed $value      Requested or restored price.
	 * @return string|false Canonical WooCommerce decimal, or false.
	 */
	private function get_valid_custom_price( $product_id, $value ) {
		$data    = $this->get_data( $product_id );
		$product = wc_get_product( $product_id );
		if ( ! $product || 'yes' !== $data['enable'] || ! ( is_string( $value ) || is_int( $value ) || is_float( $value ) ) ) {
			return false;
		}
		$value = trim( (string) $value );
		if ( strlen( $value ) > 32 || ! preg_match( '/^[0-9]+(?:[.,][0-9]+)?$/D', $value ) || ! is_finite( (float) str_replace( ',', '.', $value ) ) ) {
			return false;
		}
		$price = wc_format_decimal( str_replace( ',', '.', $value ), wc_get_price_decimals() );
		$min   = wopb_function()->get_price_data( $data['min'] ? $data['min'] : 1 )['value'];
		$max   = wopb_function()->get_price_data( $data['max'] ? $data['max'] : $product->get_price() )['value'];
		if ( ! is_numeric( $min ) || ! is_numeric( $max ) || ! is_finite( (float) $min ) || ! is_finite( (float) $max ) || (float) $price <= 0 || $price < $min || $price > $max ) {
			return false;
		}
		return $price;
	}

	/**
	 * Set Cart Item Price
	 *
	 * @param $cart_contents
	 * @return array
	 * @since v.4.0.4
	 */
	public function get_cart_contents( $cart_contents ) {
		foreach ( $cart_contents as $key => $cart_item ) {
			if ( array_key_exists( 'wopb_custom_prices', $cart_item ) ) {
				$product_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
				$price      = $this->get_valid_custom_price( $product_id, $cart_item['wopb_custom_prices'] );
				if ( false !== $price ) {
					$cart_contents[ $key ]['wopb_custom_prices'] = $price;
					$cart_item['data']->set_price( $price );
				} else {
					unset( $cart_contents[ $key ]['wopb_custom_prices'] );
					$product = wc_get_product( $product_id );
					if ( $product ) {
						$cart_item['data']->set_price( $product->get_price() );
					}
				}
			}
		}
		return $cart_contents;
	}

	/**
	 * Show Each of Variation Products for Variable Name Price Addon
	 *
	 * @param $data
	 * @param $product
	 * @param $variation
	 * @return object
	 * @since v.4.0.0
	 */
	public function get_all_variations( $data, $product, $variation ) {
		$wopb_buffer_level_2 = ob_get_level();
		ob_start();
		try {
			$this->common_data( $variation->get_id(), $product->get_type() );
			$data['variation_description'] .= ob_get_clean();
		} finally {
			while ( ob_get_level() > $wopb_buffer_level_2 ) {
				if ( ! ob_end_clean() ) {
					break;
				}
			}
		}
		return $data;
	}

	/**
	 * Hide Main Price
	 *
	 * @param $price
	 * @param $product
	 * @return string
	 * @since v.4.0.5
	 */
	public function price_html( $price, $product ) {
		$name_price = $product->get_meta( '_wopb_name_price' );
		if (
			! empty( $name_price['enable'] ) && 'yes' === $name_price['enable'] &&
			! empty( $name_price['hide_price'] ) && 'yes' === $name_price['hide_price']
		) {
			return '';
		}
		return $price;
	}

	/**
	 * Check If Price is Between Min and Max Price
	 *
	 * @param $passed
	 * @param $product_id
	 * @param $quantity
	 * @return boolean
	 * @since v.4.0.0
	 */
	public function add_to_cart_validation( $passed, $product_id, $quantity, $variation_id = 0 ) {
		$product_id = $variation_id ? $variation_id : $product_id;
		$data       = $this->get_data( $product_id );
		if ( 'yes' === $data['enable'] && false === $this->get_valid_custom_price( $product_id, $this->get_requested_custom_price() ) ) {
			wc_add_notice( __( 'Enter a valid product price within the allowed minimum and maximum.', 'product-blocks' ), 'error' );
			return false;
		}
		return $passed;
	}

	/**
	 * CSS Generator
	 *
	 * @since v.4.0.0
	 * @return void
	 */
	public function generate_css( $key ) {
		if ( 'wopb_name_price' === $key ) {
			$settings          = wopb_function()->get_setting();
			$price_split_style = array_merge( $settings['name_price_split_typo'], $settings['name_price_split_bg'] );

			$css      = '.wopb-name-price-wrapper .wopb-name-price-label{';
				$css .= wopb_function()->convert_css( 'general', $settings['name_price_lvl_typo'] );
			$css     .= '}';
			$css     .= '.wopb-name-price-wrapper .wopb-name-price-label:hover{';
				$css .= wopb_function()->convert_css( 'hover', $settings['name_price_lvl_typo'] );
			$css     .= '}';

			$css     .= '.wopb-name-price-wrapper .wopb-chunk-price-label{';
				$css .= wopb_function()->convert_css( 'general', $price_split_style );
				$css .= wopb_function()->convert_css( 'border', $settings['name_price_split_border'] );
				$css .= wopb_function()->convert_css( 'radius', $settings['name_price_split_radius'] );
				$css .= 'padding: ' . wopb_function()->convert_css( 'dimension', $settings['name_price_split_padding'] ) . ';';
			$css     .= '}';
			$css     .= '.wopb-name-price-wrapper .wopb-chunk-price-label:hover{';
				$css .= wopb_function()->convert_css( 'hover', $price_split_style );
			$css     .= '}';

			$css     .= '.wopb-min-max-price .wopb-range-label{';
				$css .= wopb_function()->convert_css( 'general', $settings['name_price_range_lvl_typo'] );
			$css     .= '}';
			$css     .= '.wopb-min-max-price .wopb-range-label:hover{';
				$css .= wopb_function()->convert_css( 'hover', $settings['name_price_range_lvl_typo'] );
			$css     .= '}';

			$css     .= '.wopb-min-max-price .wopb-range-price{';
				$css .= wopb_function()->convert_css( 'general', $settings['name_price_range_typo'] );
			$css     .= '}';
			$css     .= '.wopb-min-max-price .wopb-range-price:hover{';
				$css .= wopb_function()->convert_css( 'hover', $settings['name_price_range_typo'] );
			$css     .= '}';

			wopb_function()->update_css( $key, 'add', $css );
		}
	}
}
