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
 * They are only enqueued when the shortcode or block is actually used, so other pages stay fast.
 * Registered on "init" (not "wp_enqueue_scripts") so the handles also exist in the block editor.
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
add_action( 'init', 'floor_catalog_register_assets' );

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

	// Enqueue here (not in the header) so the files only load on pages that use the shortcode.
	wp_enqueue_style( 'floor-catalog' );

	if ( ! $floors->have_posts() ) {
		return floor_catalog_render_empty( $atts['material'] );
	}

	wp_enqueue_script( 'floor-catalog' );

	// Collect the materials used by these floors, and how many of each, for the filter buttons.
	$materials = array();
	$counts    = array();
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
				$counts[ $term->slug ]    = isset( $counts[ $term->slug ] ) ? $counts[ $term->slug ] + 1 : 1;
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
	<div class="floor-catalog floor-catalog--grid">
		<?php if ( count( $materials ) > 1 && '' === $atts['material'] ) : ?>
			<div class="floor-catalog__filters" role="group" aria-label="<?php esc_attr_e( 'Filter by material', 'floor-catalog' ); ?>">
				<button type="button" class="floor-catalog__filter is-active" data-filter="all" aria-pressed="true">
					<?php esc_html_e( 'All', 'floor-catalog' ); ?>
					<span class="floor-catalog__count"><?php echo esc_html( number_format_i18n( $floors->post_count ) ); ?></span>
				</button>
				<?php foreach ( $materials as $slug => $name ) : ?>
					<button type="button" class="floor-catalog__filter" data-filter="<?php echo esc_attr( $slug ); ?>" aria-pressed="false">
						<?php echo esc_html( $name ); ?>
						<span class="floor-catalog__count"><?php echo esc_html( number_format_i18n( $counts[ $slug ] ) ); ?></span>
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
 * Friendly message when there are no floors to show.
 *
 * @param string $material Material slug from the shortcode, or ''.
 * @return string
 */
function floor_catalog_render_empty( $material ) {
	ob_start();
	?>
	<div class="floor-catalog">
		<div class="floor-catalog__empty">
			<p class="floor-catalog__empty-title"><?php esc_html_e( 'No floors here just yet', 'floor-catalog' ); ?></p>
			<p><?php esc_html_e( 'New floors are added regularly, so please check back soon.', 'floor-catalog' ); ?></p>
			<?php if ( '' !== $material ) : ?>
				<p><a href="<?php echo esc_url( get_post_type_archive_link( 'floor_product' ) ); ?>"><?php esc_html_e( 'Browse all floors', 'floor-catalog' ); ?></a></p>
			<?php endif; ?>
			<?php // Only people who can add floors see the admin shortcut. ?>
			<?php if ( current_user_can( 'edit_posts' ) ) : ?>
				<p><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=floor_product' ) ); ?>"><?php esc_html_e( 'Add a floor', 'floor-catalog' ); ?></a></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * HTML for one product card. Every dynamic value is escaped here.
 *
 * Called inside the shortcode's loop, so get_the_excerpt() sees the right post.
 *
 * @param int      $id         Post ID.
 * @param string[] $term_slugs Material slugs, used by the filter.
 * @param string[] $term_names Material names, shown as the badge.
 * @return string
 */
function floor_catalog_render_card( $id, $term_slugs, $term_names ) {
	$color      = get_post_meta( $id, '_floor_color', true );
	$finish     = get_post_meta( $id, '_floor_finish', true );
	$price      = get_post_meta( $id, '_floor_price_sqft', true );
	$visualizer = get_post_meta( $id, '_floor_visualizer_url', true );
	$excerpt    = get_the_excerpt( $id ); // Falls back to trimmed content when no manual excerpt is set.
	$permalink  = get_permalink( $id );

	// Material is shown as the badge, so the meta line only needs color and finish.
	$details = array_filter( array( $color, $finish ) );

	ob_start();
	?>
	<li class="floor-catalog__card" data-materials="<?php echo esc_attr( implode( ' ', $term_slugs ) ); ?>">
		<div class="floor-catalog__media">
			<?php // The title link below is the accessible link, so this duplicate is hidden from keyboard and screen readers. ?>
			<a class="floor-catalog__image" href="<?php echo esc_url( $permalink ); ?>" tabindex="-1" aria-hidden="true">
				<?php
				if ( has_post_thumbnail( $id ) ) {
					echo get_the_post_thumbnail( $id, 'medium_large', array( 'loading' => 'lazy' ) );
				} else {
					echo '<span class="floor-catalog__placeholder"></span>';
				}
				?>
			</a>
			<?php if ( $term_names ) : ?>
				<span class="floor-catalog__badge"><?php echo esc_html( implode( ', ', $term_names ) ); ?></span>
			<?php endif; ?>
		</div>
		<div class="floor-catalog__body">
			<h3 class="floor-catalog__title">
				<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a>
			</h3>
			<?php if ( '' !== $price ) : ?>
				<p class="floor-catalog__price">
					<?php
					printf(
						/* translators: %s: price per square foot, e.g. "$4.99". */
						esc_html__( '%s / sq ft', 'floor-catalog' ),
						'<span class="floor-catalog__amount">' . esc_html( '$' . number_format_i18n( (float) $price, 2 ) ) . '</span>'
					); // phpcs:ignore WordPress.Security.EscapeOutput -- the span's contents are escaped above.
					?>
				</p>
			<?php endif; ?>
			<?php if ( $details ) : ?>
				<p class="floor-catalog__meta"><?php echo esc_html( implode( ' · ', $details ) ); ?></p>
			<?php endif; ?>
			<?php if ( $excerpt ) : ?>
				<p class="floor-catalog__excerpt"><?php echo esc_html( wp_strip_all_tags( $excerpt ) ); ?></p>
			<?php endif; ?>
			<?php if ( $visualizer ) : ?>
				<div class="floor-catalog__footer">
					<a class="floor-catalog__button" href="<?php echo esc_url( $visualizer ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'See it in your room', 'floor-catalog' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'floor-catalog' ); ?></span>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</li>
	<?php
	return ob_get_clean();
}
