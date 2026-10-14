<?php
/**
 * Adds a details panel to each Floor's own page (e.g. /floors/natural-oak/).
 *
 * Single Floor templates (ours in includes/templates.php, or a theme's) only print the
 * title, image and content. We use the "the_content" filter to append the material,
 * color, finish, price and visualizer button after the content, so it works in any theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the plugin CSS on single Floor pages too (the shortcode loads it on catalog pages).
 */
function floor_catalog_single_assets() {
	if ( is_singular( 'floor_product' ) ) {
		wp_enqueue_style( 'floor-catalog' );
	}
}
add_action( 'wp_enqueue_scripts', 'floor_catalog_single_assets', 20 ); // 20 = after the style is registered.

/**
 * Append the details panel to a Floor's content.
 *
 * @param string $content The post content.
 * @return string
 */
function floor_catalog_single_details( $content ) {
	// Only on a single Floor page, in the main content, not in excerpts or other loops.
	if ( ! is_singular( 'floor_product' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$id         = get_the_ID();
	$materials  = get_the_term_list( $id, 'floor_material', '', ', ' ); // Linked material names.
	$color      = get_post_meta( $id, '_floor_color', true );
	$finish     = get_post_meta( $id, '_floor_finish', true );
	$price      = get_post_meta( $id, '_floor_price_sqft', true );
	$visualizer = get_post_meta( $id, '_floor_visualizer_url', true );

	// Label => value pairs. Empty values are skipped below.
	$rows = array(
		__( 'Material', 'floor-catalog' ) => ( $materials && ! is_wp_error( $materials ) ) ? wp_kses_post( $materials ) : '',
		__( 'Color', 'floor-catalog' )    => esc_html( $color ),
		__( 'Finish', 'floor-catalog' )   => esc_html( $finish ),
		__( 'Price', 'floor-catalog' )    => '' !== $price
			/* translators: %s: price per square foot. */
			? esc_html( sprintf( __( '$%s / sq ft', 'floor-catalog' ), number_format_i18n( (float) $price, 2 ) ) )
			: '',
	);

	ob_start();
	?>
	<div class="floor-catalog floor-catalog__details">
		<dl class="floor-catalog__specs">
			<?php foreach ( $rows as $label => $value ) : ?>
				<?php if ( '' !== $value ) : ?>
					<div>
						<dt><?php echo esc_html( $label ); ?></dt>
						<dd><?php echo $value; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped when building $rows. ?></dd>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</dl>

		<div class="floor-catalog__actions">
			<?php if ( $visualizer ) : ?>
				<a class="floor-catalog__button" href="<?php echo esc_url( $visualizer ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'See it in your room', 'floor-catalog' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'floor-catalog' ); ?></span>
				</a>
			<?php endif; ?>
			<a class="floor-catalog__back" href="<?php echo esc_url( get_post_type_archive_link( 'floor_product' ) ); ?>">
				<?php esc_html_e( '← All floors', 'floor-catalog' ); ?>
			</a>
		</div>
	</div>
	<?php
	return $content . ob_get_clean();
}
add_filter( 'the_content', 'floor_catalog_single_details' );
