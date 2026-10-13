<?php
/**
 * The [floor_catalog] shortcode: a responsive product grid with material filter buttons.
 *
 * Usage in any page or post:
 *   [floor_catalog]                      all floors
 *   [floor_catalog material="hardwood"]  only one material (use the material's slug)
 *   [floor_catalog limit="6"]            show at most 6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register our CSS and JS, but don't load them yet.
 * They are only enqueued when the shortcode is actually used, so other pages stay fast.
 */
function floor_catalog_register_assets() {
	wp_register_style(
		'floor-catalog',
		FLOOR_CATALOG_URL . 'assets/floor-catalog.css',
		array(),
		FLOOR_CATALOG_VERSION
	);
	wp_register_script(
		'floor-catalog',
		FLOOR_CATALOG_URL . 'assets/floor-catalog.js',
		array(),               // No jQuery needed.
		FLOOR_CATALOG_VERSION,
		true                   // Load in the footer.
	);
}
add_action( 'wp_enqueue_scripts', 'floor_catalog_register_assets' );

/**
 * Build the shortcode output.
 *
 * @param array $atts Attributes typed in the shortcode.
 * @return string HTML. Shortcodes must RETURN their HTML, never echo it.
 */
function floor_catalog_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'material' => '',
			'limit'    => 24,
		),
		$atts,
		'floor_catalog'
	);

	$query_args = array(
		'post_type'      => 'floor_product',
		'post_status'    => 'publish',
		'posts_per_page' => max( 1, absint( $atts['limit'] ) ),
		'orderby'        => 'title',
		'order'          => 'ASC',
		'no_found_rows'  => true, // We don't paginate, so skip the extra COUNT query.
	);

	if ( '' !== $atts['material'] ) {
		$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery -- small catalog query.
			array(
				'taxonomy' => 'floor_material',
				'field'    => 'slug',
				'terms'    => sanitize_title( $atts['material'] ),
			),
		);
	}

	$floors = new WP_Query( $query_args );

	if ( ! $floors->have_posts() ) {
		return '<p class="floor-catalog__empty">' . esc_html__( 'No floors to show yet.', 'floor-catalog' ) . '</p>';
	}

	wp_enqueue_style( 'floor-catalog' );
	wp_enqueue_script( 'floor-catalog' );

	// Collect the materials used by these floors, for the filter buttons.
	$materials = array();
	$cards     = '';

	while ( $floors->have_posts() ) {
		$floors->the_post();
		$id = get_the_ID();

		$terms      = get_the_terms( $id, 'floor_material' );
		$term_slugs = array();
		$term_names = array();
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$materials[ $term->slug ] = $term->name;
				$term_slugs[]             = $term->slug;
				$term_names[]             = $term->name;
			}
		}

		$cards .= floor_catalog_render_card( $id, $term_slugs, $term_names );
	}
	wp_reset_postdata(); // Always reset after a custom WP_Query.

	asort( $materials );

	ob_start(); // Capture the HTML below into a string instead of printing it.
	?>
	<div class="floor-catalog">
		<?php if ( count( $materials ) > 1 && '' === $atts['material'] ) : ?>
			<div class="floor-catalog__filters" role="group" aria-label="<?php esc_attr_e( 'Filter by material', 'floor-catalog' ); ?>">
				<button type="button" class="floor-catalog__filter is-active" data-filter="all" aria-pressed="true">
					<?php esc_html_e( 'All', 'floor-catalog' ); ?>
				</button>
				<?php foreach ( $materials as $slug => $name ) : ?>
					<button type="button" class="floor-catalog__filter" data-filter="<?php echo esc_attr( $slug ); ?>" aria-pressed="false">
						<?php echo esc_html( $name ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<ul class="floor-catalog__grid">
			<?php echo $cards; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in floor_catalog_render_card(). ?>
		</ul>
		<p class="floor-catalog__status screen-reader-text" aria-live="polite"></p>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'floor_catalog', 'floor_catalog_shortcode' );

/**
 * HTML for one product card. Every dynamic value is escaped here.
 *
 * @param int      $id         Post ID.
 * @param string[] $term_slugs Material slugs, used by the filter.
 * @param string[] $term_names Material names, shown on the card.
 * @return string
 */
function floor_catalog_render_card( $id, $term_slugs, $term_names ) {
	$color      = get_post_meta( $id, '_floor_color', true );
	$finish     = get_post_meta( $id, '_floor_finish', true );
	$price      = get_post_meta( $id, '_floor_price_sqft', true );
	$visualizer = get_post_meta( $id, '_floor_visualizer_url', true );

	$details = array_filter( array( implode( ', ', $term_names ), $color, $finish ) );

	ob_start();
	?>
	<li class="floor-catalog__card" data-materials="<?php echo esc_attr( implode( ' ', $term_slugs ) ); ?>">
		<a class="floor-catalog__image" href="<?php echo esc_url( get_permalink( $id ) ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			if ( has_post_thumbnail( $id ) ) {
				echo get_the_post_thumbnail( $id, 'medium_large', array( 'loading' => 'lazy' ) );
			} else {
				echo '<span class="floor-catalog__placeholder"></span>';
			}
			?>
		</a>
		<div class="floor-catalog__body">
			<h3 class="floor-catalog__title">
				<a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a>
			</h3>
			<?php if ( $details ) : ?>
				<p class="floor-catalog__meta"><?php echo esc_html( implode( ' · ', $details ) ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $price ) : ?>
				<p class="floor-catalog__price">
					<?php
					/* translators: %s: price per square foot. */
					printf( esc_html__( '$%s / sq ft', 'floor-catalog' ), esc_html( number_format_i18n( (float) $price, 2 ) ) );
					?>
				</p>
			<?php endif; ?>
			<?php if ( $visualizer ) : ?>
				<a class="floor-catalog__button" href="<?php echo esc_url( $visualizer ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'See it in your room', 'floor-catalog' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'floor-catalog' ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</li>
	<?php
	return ob_get_clean();
}
