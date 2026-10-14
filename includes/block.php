<?php
/**
 * The "Floor Catalog" block (floor-catalog/catalog).
 *
 * A dynamic block: block.json describes it, and PHP renders it on every page view by
 * reusing the shortcode function, so the block and the shortcode can never drift apart.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the block from its block.json folder.
 */
function floor_catalog_register_block() {
	register_block_type(
		FLOOR_CATALOG_PATH . 'blocks/catalog',
		array(
			'render_callback' => 'floor_catalog_render_block',
		)
	);
}
add_action( 'init', 'floor_catalog_register_block' );

/**
 * Render the block by passing its attributes to the shortcode function.
 *
 * @param array $attributes Block attributes (defaults come from block.json).
 * @return string HTML.
 */
function floor_catalog_render_block( $attributes ) {
	return floor_catalog_shortcode(
		array(
			'material' => isset( $attributes['material'] ) ? (string) $attributes['material'] : '',
			'limit'    => isset( $attributes['limit'] ) ? absint( $attributes['limit'] ) : 24,
		)
	);
}
