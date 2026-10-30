<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template partial included directly into a method scope; these are local render-time variables, not plugin globals.

$is_block_theme = wp_is_block_theme();

if ( $is_block_theme ) {
	global $WOPB_HEADER_ID;
	global $WOPB_FOOTER_ID;
	?><!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<?php
		wp_head();
		?>
	</head>
	<body <?php body_class(); ?>>
	<?php
	wp_body_open();

	if ( ! $WOPB_HEADER_ID ) {
		echo '<header class="wp-block-template-part">';
		block_template_part( 'header' );
		echo '</header>';
	}
} else {
	get_header();
}

do_action( 'wopb_before_content' );

$width = absint( wopb_function()->get_setting( 'container_width' ) ); // Pixels, printed in the inline style.
$width = $width ? $width : 1140;
?>
<div 
	class="wopb-template-container" 
	style="margin:0 auto; max-width:<?php echo esc_attr( $width ); ?>px; padding: 0 15px; width: -webkit-fill-available; width: -moz-available;"
	<?php
	if ( wopb_function()->get_theme_name() === 'Divi' ) {
		echo 'id="main-content"'; }
	?>
>
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		endwhile;
	?>
</div>
<?php

do_action( 'wopb_after_content' );

if ( $is_block_theme ) {
	if ( ! $WOPB_FOOTER_ID ) {
		echo '<footer class="wp-block-template-part">';
		block_template_part( 'footer' );
		echo '</footer>';
	}
	wp_head();
	wp_footer();
	?>
	</body>
	</html>
	<?php
} else {
	get_footer();
}
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
