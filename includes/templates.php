<?php
/**
 * Block templates for the Floors archive (/floors/) and single Floor pages.
 *
 * Without these, block themes fall back to their blog templates: the archive lists floors
 * like blog posts (no catalog grid) and single floors show an author/date line.
 *
 * Plugin templates sit in the template hierarchy below the theme's own files, so a theme
 * that ships archive-floor_product.html (or a template edited in the Site Editor) still wins.
 * Classic themes ignore block templates and keep working as before.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the templates. register_block_template() was added in WordPress 6.7,
 * so on older versions we skip it and the theme's templates are used.
 */
function floor_catalog_register_templates() {
	if ( ! function_exists( 'register_block_template' ) ) {
		return;
	}

	// Names use the "plugin-slug//template-slug" format. The slug part matches the
	// template hierarchy (archive-{post_type}, single-{post_type}), so WordPress picks it automatically.
	register_block_template(
		'floor-catalog//archive-floor_product',
		array(
			'title'       => __( 'Floors Archive', 'floor-catalog' ),
			'description' => __( 'The /floors/ page: the filterable Floor Catalog grid.', 'floor-catalog' ),
			'content'     => floor_catalog_archive_template_content(),
		)
	);

	register_block_template(
		'floor-catalog//single-floor_product',
		array(
			'title'       => __( 'Single Floor', 'floor-catalog' ),
			'description' => __( 'A single floor: title, image, description and the Floor Details panel.', 'floor-catalog' ),
			'content'     => floor_catalog_single_template_content(),
		)
	);
}
add_action( 'init', 'floor_catalog_register_templates' );

/**
 * Archive markup: header, heading, then the catalog block in a wide group so the grid
 * has room for 3 cards per row. Template parts with no "theme" attribute use the active theme's.
 *
 * We use our block, not a Shortcode block: in templates WordPress expands shortcodes
 * *before* rendering blocks, so the Shortcode block's wpautop() would add <p> and <br>
 * tags to the finished catalog HTML.
 *
 * @return string Block markup.
 */
function floor_catalog_archive_template_content() {
	return '<!-- wp:template-part {"slug":"header"} /-->

<!-- wp:group {"tagName":"main","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"},"padding":{"bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="margin-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:heading {"level":1} -->
		<h1 class="wp-block-heading">' . esc_html__( 'Our Floors', 'floor-catalog' ) . '</h1>
		<!-- /wp:heading -->

		<!-- wp:floor-catalog/catalog /-->
	</div>
	<!-- /wp:group -->
</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer"} /-->';
}

/**
 * Single floor markup: like a blog post template, minus the author/date line,
 * tags, comments and post navigation. The details panel arrives through post-content
 * (see includes/single.php).
 *
 * @return string Block markup.
 */
function floor_catalog_single_template_content() {
	return '<!-- wp:template-part {"slug":"header"} /-->

<!-- wp:group {"tagName":"main","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"},"padding":{"bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="margin-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:post-title {"level":1} /-->
	<!-- wp:post-featured-image {"aspectRatio":"3/2"} /-->
	<!-- wp:post-content {"layout":{"type":"constrained"}} /-->
</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer"} /-->';
}
