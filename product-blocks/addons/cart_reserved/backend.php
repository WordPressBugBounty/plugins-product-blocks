<?php
defined( 'ABSPATH' ) || exit;

/**
 * Cart Reserved Timer Addons Initial Configuration
 *
 * @since v.3.2.0
 */
add_filter( 'wopb_addons_config', 'wopb_cart_reserved_init' );
function wopb_cart_reserved_init( $config ) {
	$configuration                = array(
		'name'     => __( 'Cart Reserved Timer', 'product-blocks' ),
		'desc'     => __( 'Display a countdown timer and show a FOMO message once someone adds products to the cart.', 'product-blocks' ),
		'live'     => 'https://www.wpxpo.com/product/wowstore/features/woocommerce-cart-reserved-timer/',
		'docs'     => 'https://wpxpo.com/docs/wowstore/add-ons/cart-reserved-timer/',
		'type'     => 'checkout_cart',
		'priority' => 10,
	);
	$config['wopb_cart_reserved'] = $configuration;
	return $config;
}

/**
 * Show "What to do after the timer expires?" with its one free choice, "Hide Timer".
 *
 * Pro adds its own choices to this field, or the whole field on older versions, so
 * with Pro active the field is left to it.
 *
 * @param array $config Settings screens, keyed by add-on.
 * @return array
 */
add_filter( 'wopb_settings', 'wopb_cart_reserved_expire_field', 11, 1 );
function wopb_cart_reserved_expire_field( $config ) {
	$path = array( 'wopb_cart_reserved', 'attr', 'tab', 'options', 'settings', 'attr', 'container_1', 'attr' );
	$node = $config;
	foreach ( $path as $step ) {
		if ( ! isset( $node[ $step ] ) ) {
			return $config;
		}
		$node = $node[ $step ];
	}
	if ( ! wopb_function()->show_upsell() || isset( $node['cart_reserved_expire'] ) ) {
		return $config;
	}

	$position = array_search( 'cart_reserved_time_msg', array_keys( $node ), true );
	$position = false === $position ? count( $node ) : $position + 1;
	$field    = array(
		'cart_reserved_expire' => array(
			'type'    => 'radio',
			'label'   => __( 'What to do after the timer expires?', 'product-blocks' ),
			'options' => array(
				'hide' => __( 'Hide Timer', 'product-blocks' ),
			),
			'default' => 'hide',
		),
	);

	$config['wopb_cart_reserved']['attr']['tab']['options']['settings']['attr']['container_1']['attr'] =
		array_slice( $node, 0, $position, true ) + $field + array_slice( $node, $position, null, true );
	return $config;
}

/**
 * Cart Reserved Timer Addons Default Settings
 *
 * @since v.3.2.0
 * @param ARRAY | Default Congiguration
 * @return ARRAY
 */
add_filter( 'wopb_settings', 'wopb_get_cart_reserved_opt', 10, 1 );
function wopb_get_cart_reserved_opt( $config ) {
	$arr = array(
		'wopb_cart_reserved' => array(
			'label' => __( 'Cart Reserved Timer Settings', 'product-blocks' ),
			'attr'  => array(

				'tab' => array(
					'type'    => 'tab',
					'options' => array(
						'settings' => array(
							'label' => __( 'Settings', 'product-blocks' ),
							'attr'  => array(
								'wopb_cart_reserved' => array(
									'type'  => 'toggle',
									'value' => 'false',
									'label' => __( 'Enable Cart Reserved', 'product-blocks' ),
									'desc'  => __( 'Enable cart reserved on your website', 'product-blocks' ),
								),
								'container_1'        => array(
									'type' => 'container',
									'attr' => array(
										'cart_reserved_time' => array(
											'type'    => 'number',
											'label'   => __( 'Countdown Duration Minutes', 'product-blocks' ),
											'default' => 10,
										),
										'cart_reserved_msg' => array(
											'type'    => 'text',
											'label'   => __( 'Cart Reserved Message', 'product-blocks' ),
											'default' => 'An item of your cart is in high demand.',
										),
										'cart_reserved_time_msg' => array(
											'type'    => 'text',
											'label'   => __( 'Timer Message', 'product-blocks' ),
											'default' => 'Your cart is saved for {time} minutes!',
										),
										'cart_reserved_icon' => array(
											'type'    => 'radio',
											'label'   => __( 'Choose An Icon', 'product-blocks' ),
											'display' => 'inline-box',
											'options' => array(
												'fire'   => '🔥',
												'watch'  => '⏱️',
												'bell'   => '🔔',
												'timer'  => '⏳',
												'rocket' => '🚀',
												'bomb'   => '🧨',
												'blast'  => '💥',
											),
											'default' => 'fire',
										),
									),
								),
							),
						),
						'design'   => array(
							'label' => __( 'Design', 'product-blocks' ),
							'attr'  => array(
								'container_2' => array(
									'type' => 'container',
									'attr' => array(
										'cart_reserved_bg' => array(
											'type'    => 'color2',
											'field1'  => 'bg',
											'field2'  => 'hover_bg',
											'label'   => __( 'Background Color', 'product-blocks' ),
											'default' => (object) array(
												'bg'       => '#F8F9FA',
												'hover_bg' => '',
											),
											'tooltip' => __( 'Background Color', 'product-blocks' ),
										),
										'cart_reserved_padding' => array(
											'type'    => 'number',
											'label'   => __( 'Padding', 'product-blocks' ),
											'default' => 10,
										),
										'cart_reserved_border' => array(
											'type'    => 'border',
											'label'   => __( 'Border', 'product-blocks' ),
											'default' => (object) array(
												'border' => 1.6,
												'color'  => '#DDDDDD',
												'border_style' => 'dashed',
											),
										),
										'cart_reserved_radius' => array(
											'type'    => 'number',
											'label'   => __( 'Border Radius', 'product-blocks' ),
											'default' => 4,
										),
										'cart_reserved_msg_typo' => array(
											'type'    => 'typography',
											'label'   => __( 'Message Typography', 'product-blocks' ),
											'default' => (object) array(
												'size'   => 14,
												'bold'   => false,
												'italic' => false,
												'underline' => false,
												'color'  => '#000000',
												'hover_color' => '',
											),
										),
										'cart_reserved_typo' => array(
											'type'    => 'typography',
											'label'   => __( 'Reserved Typography', 'product-blocks' ),
											'default' => (object) array(
												'size'   => 16,
												'bold'   => true,
												'italic' => false,
												'underline' => false,
												'color'  => '#000000',
												'hover_color' => '',
											),
										),
										'cart_reserved_timer_color' => array(
											'type'    => 'color2',
											'field1'  => 'color',
											'field2'  => 'hover_color',
											'label'   => __( 'Timer Color', 'product-blocks' ),
											'default' => (object) array(
												'color' => '#ff176b',
												'hover_color' => '',
											),
											'tooltip' => __( 'Label Color', 'product-blocks' ),
										),
										'cart_reserved_icon_size' => array(
											'type'    => 'number',
											'label'   => __( 'Icon Size', 'product-blocks' ),
											'default' => 25,
										),
									),
								),
							),
						),
					),
				),
			),
		),
	);
	return array_merge( $config, $arr );
}
