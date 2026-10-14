<?php
/**
 * Registers the "Floors" custom post type and the "Material" taxonomy.
 *
 * A custom post type is a new kind of content, like Posts and Pages but for our products.
 * A taxonomy is a way to group them, like Categories, here used for materials (Oak, Tile, Vinyl...).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the floor_product post type.
 * Hooked to "init", the point in every request where WordPress expects content types to be registered.
 */
function floor_catalog_register_post_type() {
	$labels = array(
		'name'               => __( 'Floors', 'floor-catalog' ),
		'singular_name'      => __( 'Floor', 'floor-catalog' ),
		'add_new_item'       => __( 'Add New Floor', 'floor-catalog' ),
		'edit_item'          => __( 'Edit Floor', 'floor-catalog' ),
		'new_item'           => __( 'New Floor', 'floor-catalog' ),
		'view_item'          => __( 'View Floor', 'floor-catalog' ),
		'search_items'       => __( 'Search Floors', 'floor-catalog' ),
		'not_found'          => __( 'No floors found.', 'floor-catalog' ),
		'not_found_in_trash' => __( 'No floors found in Trash.', 'floor-catalog' ),
		'menu_name'          => __( 'Floor Catalog', 'floor-catalog' ),
	);

	register_post_type(
		'floor_product',
		array(
			'labels'       => $labels,
			'public'       => true,                  // Visible on the site and in WP Admin.
			'has_archive'  => true,                  // Gives us a listing page at /floors/.
			'rewrite'      => array( 'slug' => 'floors' ),
			'menu_icon'    => 'dashicons-grid-view', // Icon in the admin sidebar.
			'menu_position' => 20,
			'show_in_rest' => true,                  // Needed for the block editor.
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		)
	);
}
add_action( 'init', 'floor_catalog_register_post_type' );

/**
 * Register the floor_material taxonomy and attach it to our post type.
 */
function floor_catalog_register_taxonomy() {
	// Any label left out falls back to WordPress's "Category" wording, so we set them all.
	$labels = array(
		'name'              => __( 'Materials', 'floor-catalog' ),
		'singular_name'     => __( 'Material', 'floor-catalog' ),
		'search_items'      => __( 'Search Materials', 'floor-catalog' ),
		'all_items'         => __( 'All Materials', 'floor-catalog' ),
		'parent_item'       => __( 'Parent Material', 'floor-catalog' ),
		'parent_item_colon' => __( 'Parent Material:', 'floor-catalog' ),
		'edit_item'         => __( 'Edit Material', 'floor-catalog' ),
		'view_item'         => __( 'View Material', 'floor-catalog' ),
		'update_item'       => __( 'Update Material', 'floor-catalog' ),
		'add_new_item'      => __( 'Add New Material', 'floor-catalog' ),
		'new_item_name'     => __( 'New Material Name', 'floor-catalog' ),
		'not_found'         => __( 'No materials found.', 'floor-catalog' ),
		'back_to_items'     => __( '&larr; Go to Materials', 'floor-catalog' ),
	);

	register_taxonomy(
		'floor_material',
		'floor_product',
		array(
			'labels'            => $labels,
			'hierarchical'      => true,  // Behaves like Categories (checkboxes), not Tags.
			'show_admin_column' => true,  // Shows the material in the Floors list table.
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'material' ),
		)
	);
}
add_action( 'init', 'floor_catalog_register_taxonomy' );
