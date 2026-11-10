<?php
/**
 * Generate block styles from the same local control definitions as the editor.
 *
 * Selectors and declarations are plugin-owned. Requests can supply only block
 * names and structured control values, never CSS rules or selector templates.
 *
 * @package WOPB
 */
namespace WOPB;

defined( 'ABSPATH' ) || exit;

class BlockStyles {
	private static $schema;
	private $rules = array(
		'lg'    => array(),
		'sm'    => array(),
		'xs'    => array(),
		'plain' => array(),
	);
	private $fonts = array();
	private $imports = array();
	private $count = 0;
	private $custom_fonts;
	private $global_settings = array();

	/** Validate the transport before any preview, option, metadata or file writes. */
	public static function valid_blocks( $blocks, $depth = 0, &$count = 0 ) {
		if ( ! is_array( $blocks ) || $depth > 32 || ( $blocks && array_keys( $blocks ) !== range( 0, count( $blocks ) - 1 ) ) || ( 0 === $depth && strlen( wp_json_encode( $blocks ) ) > 1048576 ) ) {
			return false;
		}
		foreach ( $blocks as $block ) {
			if ( ++$count > 1000 || ! is_array( $block ) || ( isset( $block['blockName'] ) && ! is_string( $block['blockName'] ) ) || ( isset( $block['name'] ) && ! is_string( $block['name'] ) ) || ! is_array( $block['attrs'] ?? $block['attributes'] ?? array() ) ) {
				return false;
			}
			if ( ! self::valid_blocks( $block['innerBlocks'] ?? array(), $depth + 1, $count ) ) {
				return false;
			}
		}
		return true;
	}

	/** Generate only this post's own blocks; callers authorize referenced posts. */
	public static function get_post_css( $post_id ) {
		if ( 'wopb-widget' === $post_id ) {
			$widgets = get_option( 'widget_block', array() );
			$content = '';
			foreach ( is_array( $widgets ) ? $widgets : array() as $widget ) {
				if ( is_array( $widget ) && isset( $widget['content'] ) && is_string( $widget['content'] ) ) {
					$content .= $widget['content'];
				}
			}
		} else {
			$post = get_post( $post_id );
			$content = $post && isset( $post->post_content ) ? $post->post_content : '';
		}
		return self::generate( parse_blocks( $content ) );
	}

	/** Render a bounded list of structured blocks, including nested controls. */
	public static function generate( $blocks ) {
		if ( null === self::$schema ) {
			self::$schema = wp_json_file_decode( WOPB_PATH . 'assets/blocks/style-schema.json', array( 'associative' => true ) );
			self::$schema = apply_filters( 'wopb_block_style_schema', is_array( self::$schema ) ? self::$schema : array() );
		}
		$generator = new self();
		$global = get_option( 'productx_global', array() );
		$generator->global_settings = is_array( $global ) ? $global : array();
		$generator->walk( $blocks );
		$css = implode( '', array_unique( $generator->imports ) ) . implode( '', array_unique( $generator->fonts ) ) . implode( '', $generator->rules['lg'] );
		foreach ( array(
			'sm' => 992,
			'xs' => 768,
		) as $device => $fallback ) {
			$width = $generator->breakpoint( 'breakpoint' . ucfirst( $device ), $fallback );
			if ( $generator->rules[ $device ] ) {
				$css .= '@media (max-width:' . $width . 'px){' . implode( '', $generator->rules[ $device ] ) . '}';
			}
		}
		return $css . implode( '', $generator->rules['plain'] );
	}

	private function walk( $blocks, $depth = 0 ) {
		if ( ! is_array( $blocks ) || $depth > 32 ) {
			return;
		}
		foreach ( $blocks as $block ) {
			if ( ++$this->count > 1000 || ! is_array( $block ) ) {
				return;
			}
			$name = $block['blockName'] ?? $block['name'] ?? '';
			$values = $block['attrs'] ?? $block['attributes'] ?? array();
			if ( is_string( $name ) && isset( self::$schema[ $name ] ) && is_array( $values ) && isset( $values['blockId'] ) && is_string( $values['blockId'] ) && preg_match( '/^[a-zA-Z0-9_-]{1,100}$/D', $values['blockId'] ) ) {
				$this->block( self::$schema[ $name ], $values );
			}
			$this->walk( $block['innerBlocks'] ?? array(), $depth + 1 );
		}
	}

	private function block( $definitions, $values ) {
		$settings = array();
		foreach ( $definitions as $key => $definition ) {
			$settings[ $key ] = $values[ $key ] ?? $definition['default'] ?? null;
		}
		foreach ( $definitions as $key => $definition ) {
			$value = $settings[ $key ];
			if ( isset( $definition['fields'] ) && is_array( $value ) ) {
				foreach ( array_slice( $value, 0, 100 ) as $index => $row ) {
					if ( ! is_array( $row ) || ! is_int( $index ) ) {
						continue;
					}
					foreach ( $definition['fields'] as $field => $field_definition ) {
						$this->control( $field, $row[ $field ] ?? $field_definition['default'] ?? '', $field_definition, $row, $values['blockId'], $index );
					}
				}
			} else {
				$this->control( $key, $value, $definition, $settings, $values['blockId'] );
			}
		}
	}

	private function control( $key, $value, $definition, $settings, $id, $index = 0 ) {
		foreach ( $definition['style'] ?? array() as $style ) {
			if ( ! isset( $style['selector'] ) || ! $this->depends( $style, $settings ) ) {
				continue;
			}
			foreach ( (array) $style['selector'] as $template ) {
				$template = str_replace( array( '{{WOPB}}', '{{ULTP}}', '{{REPEAT_CLASS}}' ), array( '.wopb-block-' . $id, '.ultp-block-' . $id, '.wopb-repeat-' . $index ), $template );
				if ( ! is_array( $value ) ) {
					if ( $this->truthy( $value ) ) {
						$rule = $this->substitute( $template, $key, $value, $definition, $settings );
						$hide = array(
							'hideExtraLarge' => '(min-width:' . $this->breakpoint( 'breakpointSm', 1280 ) . 'px)',
							'hideDesktop'    => '(max-width:' . $this->breakpoint( 'breakpointSm', 1280 ) . 'px) and (min-width:992px)',
							'hideTablet'     => '(max-width:' . $this->breakpoint( 'breakpointSm', 992 ) . 'px) and (min-width:767px)',
							'hideMobile'     => '(max-width:' . $this->breakpoint( 'breakpointXs', 768 ) . 'px)',
						);
						$this->rules['plain'][] = isset( $hide[ $key ] ) && $rule ? '@media ' . $hide[ $key ] . '{' . $rule . '}' : $rule;
					}
					continue;
				}
				$responsive = false;
				foreach ( array( 'lg', 'sm', 'xs' ) as $device ) {
					if ( isset( $value[ $device ] ) && $this->truthy( $value[ $device ] ) ) {
						$responsive = true;
						$part = is_array( $value[ $device ] ) ? $this->dimension( $value[ $device ] ) : $this->component( $value[ $device ] ) . $this->unit( $value[ 'u' . $device ] ?? $value['unit'] ?? '' );
						$others = $settings;
						$another = $definition['anotherKey'] ?? '';
						if ( $another && isset( $others[ $another ][ $device ] ) ) {
							$others[ $another ] = $others[ $another ][ $device ];
						}
						$this->rules[ $device ][] = $this->substitute( $template, $key, $part, $definition, $others );
					}
				}
				if ( $responsive ) {
					continue;
				}
				$default = is_array( $definition['default'] ?? null ) ? $definition['default'] : array();
				foreach ( array( 'openTypography', 'openBorder', 'openShadow', 'openColor', 'openFilter' ) as $flag ) {
					if ( array_key_exists( $flag, $default ) && empty( $value[ $flag ] ) ) {
						continue 2;
					}
				}
				if ( array_key_exists( 'openTypography', $default ) ) {
					$this->typography( $template, $value );
				} elseif ( array_key_exists( 'openBorder', $default ) ) {
					$declarations = 'border-color:' . $this->component( $value['color'] ?? '#555d66' ) . ' !important;border-style:' . $this->component( $value['type'] ?? 'solid' ) . ' !important;border-width:' . $this->dimension( $value['width'] ?? array() ) . ' !important;';
					$this->append( $template, $declarations );
				} elseif ( array_key_exists( 'openShadow', $default ) ) {
					$width = $value['width'] ?? array();
					$parts = array();
					foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
						$parts[] = $this->number( $width[ $side ] ?? 0 ) . 'px';
					}
					$this->append( $template, 'box-shadow:' . ( 'inset' === ( $value['inset'] ?? '' ) ? 'inset ' : '' ) . implode( ' ', $parts ) . ' ' . $this->component( $value['color'] ?? '' ) . ';' );
				} elseif ( array_key_exists( 'openColor', $default ) ) {
					$declarations = $this->background( $value );
					if ( ! empty( $default['replace'] ) ) {
						// Only this locally declared control can replace a declaration list.
						$this->rules['plain'][] = str_replace( '{{' . $key . '}}', $declarations, $template );
					} else {
						$this->append( $template, $declarations );
					}
				} elseif ( array_key_exists( 'openFilter', $default ) ) {
					$filter = '';
					foreach ( array(
						'hue'        => array( 'hue-rotate', 'deg' ),
						'saturation' => array( 'saturate', '%' ),
						'brightness' => array( 'brightness', '%' ),
						'contrast'   => array( 'contrast', '%' ),
						'invert'     => array( 'invert', '%' ),
						'blur'       => array( 'blur', 'px' ),
					) as $field => $format ) {
						if ( ! empty( $value[ $field ] ) ) {
							$filter .= $format[0] . '(' . $this->number( $value[ $field ] ) . $format[1] . ') ';
						}
					}
					$this->rules['plain'][] = str_replace( '{{' . $key . '}}', 'filter:' . $filter . ';', $template );
				} elseif ( array_intersect( array( 'top', 'right', 'bottom', 'left' ), array_keys( $default ) ) ) {
					$this->rules['plain'][] = $this->substitute( $template, $key, $this->dimension( $value ), $definition, $settings );
				} elseif ( isset( $default['url'] ) && ! empty( $value['id'] ) ) {
					$this->append( $template, 'background-image:' . $this->url( $value['url'] ?? '' ) . ';' );
				}
			}
		}
	}

	private function substitute( $template, $key, $value, $definition, $settings ) {
		$value = is_bool( $value ) ? '' : $this->component( $value );
		if ( false === $value ) {
			return '';
		}
		$template = str_replace( '{{' . $key . '}}', $value, $template );
		$another = $definition['anotherKey'] ?? '';
		if ( $another ) {
			$template = str_replace( '{{' . $another . '}}', $this->component( $settings[ $another ] ?? '' ), $template );
		}
		return false === strpos( $template, '{{' ) ? $template : '';
	}

	/** A component cannot introduce declarations, selectors, imports or URLs. */
	private function component( $value ) {
		if ( ! is_string( $value ) && ! is_int( $value ) && ! is_float( $value ) ) {
			return false;
		}
		$value = (string) $value;
		if ( strlen( $value ) > 1000 || ! preg_match( '/^[a-zA-Z0-9\s#.,%()_+*!\/-]*$/D', $value ) || preg_match( '/(?:url|expression|image-set|attr)\s*\(|\/\*|\*\//i', $value ) ) {
			return false;
		}
		return $value;
	}

	private function number( $value ) {
		return is_scalar( $value ) && is_numeric( $value ) && abs( (float) $value ) <= 100000 ? (string) ( 0 + $value ) : '0';
	}

	private function truthy( $value ) {
		return null !== $value && false !== $value && '' !== $value && 0 !== $value && 0.0 !== $value;
	}

	private function breakpoint( $key, $fallback ) {
		$value = $this->global_settings[ $key ] ?? $fallback;
		return is_scalar( $value ) && ctype_digit( (string) $value ) && $value >= 240 && $value <= 3840 ? (int) $value : $fallback;
	}

	private function unit( $unit ) {
		return in_array( $unit, array( '', 'px', '%', 'em', 'rem', 'vh', 'vw', 'vmin', 'vmax', 'ch', 'ex', 'pt' ), true ) ? $unit : '';
	}

	private function dimension( $value ) {
		if ( ! is_array( $value ) ) {
			return '';
		}
		$parts = array();
		foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
			$parts[] = $this->number( $value[ $side ] ?? 0 ) . $this->unit( $value['unit'] ?? 'px' );
		}
		return implode( ' ', $parts );
	}

	private function append( $selector, $declarations, $device = 'plain' ) {
		if ( false === strpos( $selector, '{{' ) && false === strpos( $selector, '{' ) && $declarations ) {
			$this->rules[ $device ][] = $selector . '{' . $declarations . '}';
		}
	}

	private function typography( $selector, $value ) {
		$css = '';
		foreach ( array(
			'weight'     => 'font-weight',
			'color'      => 'color',
			'style'      => 'font-style',
			'transform'  => 'text-transform',
			'decoration' => 'text-decoration',
		) as $key => $property ) {
			$part = $this->component( $value[ $key ] ?? '' );
			if ( $part ) {
				$css .= $property . ':' . $part . ( 'decoration' === $key ? ' !important' : '' ) . ';';
			}
		}
		$family = $value['family'] ?? '';
		$custom = is_string( $family ) && '' !== $family && 'none' !== $family ? $this->custom_fonts() : array();
		if ( is_string( $family ) && '' !== $family && 'none' !== $family && strlen( $family ) <= 300 && ( isset( $custom[ $family ] ) || preg_match( '/^[\p{L}\p{N} _-]{1,100}$/uD', $family ) ) ) {
			$quoted_family = "'" . str_replace( array( '\\', "'", '<', '>', "\n", "\r", "\f" ), array( '\\\\', "\\'", '\\3c ', '\\3e ', '\\a ', '\\d ', '\\c ' ), $family ) . "'";
			$google = 'yes' === wopb_function()->get_setting( 'enable_google_font' ) && 'yes' !== wopb_function()->get_setting( 'disable_google_font' );
			$system = in_array( $family, array( 'Arial', 'Tahoma', 'Verdana', 'Helvetica', 'Times New Roman', 'Trebuchet MS', 'Georgia' ), true );
			if ( $google || $system || isset( $custom[ $family ] ) ) {
				$css .= 'font-family:' . $quoted_family . ',' . $this->component( $value['type'] ?? 'sans-serif' ) . ';';
			}
			if ( isset( $custom[ $family ] ) ) {
				foreach ( $custom[ $family ] as $face ) {
					if ( ! is_array( $face ) ) {
						continue;
					}
					$sources = array();
					foreach ( array(
						'woff'  => 'woff',
						'woff2' => 'woff2',
						'ttf'   => 'TrueType',
						'svg'   => 'svg',
						'eot'   => 'eot',
					) as $key => $format ) {
						$url = $this->url( $face[ $key ] ?? '' );
						if ( 'none' !== $url ) {
							$sources[] = $url . " format('" . $format . "')";
						}
					}
					$weight = $this->component( $face['weight'] ?? 'normal' );
					if ( $sources && preg_match( '/^(?:normal|bold|[1-9][0-9]{0,2}|1000)$/D', $weight ) ) {
						$this->fonts[] = '@font-face{font-family:' . $quoted_family . ';font-weight:' . $weight . ';font-display:auto;src:' . implode( ',', $sources ) . ';}';
					}
				}
			} elseif ( $google && ! $system ) {
				$this->imports[] = "@import url('https://fonts.googleapis.com/css?family=" . rawurlencode( $family ) . ':' . $this->number( $value['weight'] ?? 400 ) . "');";
			}
		}
		$this->append( $selector, $css );
		foreach ( array(
			'size'    => 'font-size',
			'height'  => 'line-height',
			'spacing' => 'letter-spacing',
		) as $field => $property ) {
			$data = $value[ $field ] ?? array();
			foreach ( array( 'lg', 'sm', 'xs' ) as $device ) {
				if ( is_array( $data ) && isset( $data[ $device ] ) && $this->truthy( $data[ $device ] ) ) {
					$part = $this->component( $data[ $device ] );
					if ( false !== $part ) {
						$this->append( $selector, $property . ':' . $part . $this->unit( $data[ 'u' . $device ] ?? $data['unit'] ?? '' ) . ( 'height' === $field ? ' !important' : '' ) . ';', $device );
					}
				}
			}
		}
	}

	private function custom_fonts() {
		if ( null === $this->custom_fonts ) {
			$this->custom_fonts = array();
			if ( 'true' === wopb_function()->get_setting( 'wopb_custom_font' ) ) {
				$posts = get_posts(
					array(
						'post_type'   => 'wopb_custom_font',
						'post_status' => 'publish',
						'numberposts' => 10,
						'order'       => 'ASC',
					)
				);
				foreach ( $posts as $post ) {
					$faces = get_post_meta( $post->ID, '__font_settings', true );
					if ( is_array( $faces ) ) {
						$this->custom_fonts[ $post->post_title ] = $faces;
					}
				}
			}
		}
		return $this->custom_fonts;
	}

	private function background( $value ) {
		$type = $value['type'] ?? '';
		$css = ! empty( $value['clip'] ) ? '-webkit-background-clip:text;-webkit-text-fill-color:transparent;' : '';
		if ( 'color' === $type ) {
			$css .= 'background-color:' . $this->component( $value['color'] ?? '' ) . ';';
		} elseif ( 'gradient' === $type ) {
			$gradient = $value['gradient'] ?? '';
			if ( is_array( $gradient ) ) {
				$position = 'linear' === ( $gradient['type'] ?? '' ) ? 'linear-gradient(' . $this->number( $gradient['direction'] ?? 0 ) . 'deg,' : 'radial-gradient(circle at ' . $this->component( $gradient['radial'] ?? 'center' ) . ',';
				$gradient = $position . $this->component( $gradient['color1'] ?? '' ) . ' ' . $this->number( $gradient['start'] ?? 0 ) . '%,' . $this->component( $gradient['color2'] ?? '' ) . ' ' . $this->number( $gradient['stop'] ?? 100 ) . '%)';
			}
			if ( is_string( $gradient ) && preg_match( '/^(?:linear|radial)-gradient\(/', $gradient ) ) {
				$css .= 'background-image:' . $this->component( $gradient ) . ';';
			}
		} elseif ( 'image' === $type || 'video' === $type ) {
			$css .= 'background-image:' . $this->url( $value[ 'video' === $type ? 'fallback' : 'image' ] ?? '' ) . ';';
			$css .= 'background-color:' . $this->component( $value['fallbackColor'] ?? $value['color'] ?? '' ) . ';';
			foreach ( array( 'attachment', 'repeat', 'size' ) as $field ) {
				if ( ! empty( $value[ $field ] ) ) {
					$css .= 'background-' . $field . ':' . $this->component( $value[ $field ] ) . ';';
				}
			}
			if ( isset( $value['position']['x'], $value['position']['y'] ) ) {
				$css .= 'background-position:' . $this->number( 100 * (float) $value['position']['x'] ) . '% ' . $this->number( 100 * (float) $value['position']['y'] ) . '%;';
			}
		}
		return $css;
	}

	/** Media controls accept HTTP(S) URLs without CSS delimiters or escapes. */
	private function url( $value ) {
		if ( ! is_string( $value ) || strlen( $value ) > 2048 || preg_match( '/[\x00-\x20\x7f"\'()<>\\\\]/', $value ) ) {
			return 'none';
		}
		$parts = wp_parse_url( $value );
		if ( ! is_array( $parts ) || empty( $parts['host'] ) || ! in_array( $parts['scheme'] ?? '', array( 'http', 'https' ), true ) ) {
			return 'none';
		}
		return 'url("' . esc_url_raw( $value, array( 'http', 'https' ) ) . '")';
	}

	private function depends( $style, $settings ) {
		foreach ( $style['depends'] ?? array() as $dependency ) {
			$actual = $settings[ $dependency['key'] ] ?? null;
			$expected = $dependency['value'] ?? null;
			$match = is_array( $expected ) ? in_array( $actual, $expected, false ) : $actual == $expected;
			if ( '!=' === $dependency['condition'] ? $match : ! $match ) {
				return false;
			}
		}
		return true;
	}
}
