<?php
/**
 * Adds a "Floor Details" box to the Floor edit screen and saves its fields.
 *
 * Fields are stored as post meta (key/value pairs attached to each Floor).
 * This file shows the three security habits every WordPress plugin needs:
 *   1. Nonces            - prove the form was submitted from our own screen.
 *   2. Capability checks - make sure the user is allowed to edit this post.
 *   3. Sanitize input, escape output - never trust or print raw data.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One place that describes every field. The form and the save code both read from this,
 * so adding a field later means adding one line here.
 *
 * @return array<string, array{label: string, type: string}>
 */
function floor_catalog_fields() {
	return array(
		'_floor_color'          => array( 'label' => __( 'Color', 'floor-catalog' ), 'type' => 'text' ),
		'_floor_finish'         => array( 'label' => __( 'Finish (e.g. Matte, Gloss)', 'floor-catalog' ), 'type' => 'text' ),
		'_floor_price_sqft'     => array( 'label' => __( 'Price per sq ft (USD)', 'floor-catalog' ), 'type' => 'number' ),
		'_floor_visualizer_url' => array( 'label' => __( 'Room visualizer link', 'floor-catalog' ), 'type' => 'url' ),
	);
}

/**
 * Register the meta box on the floor_product edit screen.
 */
function floor_catalog_add_meta_box() {
	add_meta_box(
		'floor_catalog_details',            // HTML id of the box.
		__( 'Floor Details', 'floor-catalog' ),
		'floor_catalog_render_meta_box',    // Function that prints the box.
		'floor_product',                    // Only on our post type.
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'floor_catalog_add_meta_box' );

/**
 * Print the fields inside the meta box.
 *
 * @param WP_Post $post The Floor being edited.
 */
function floor_catalog_render_meta_box( $post ) {
	// Hidden nonce field. We check it again when saving.
	wp_nonce_field( 'floor_catalog_save', 'floor_catalog_nonce' );

	echo '<table class="form-table" role="presentation">';
	foreach ( floor_catalog_fields() as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		$step  = 'number' === $field['type'] ? ' step="0.01" min="0"' : '';

		printf(
			'<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="%3$s" id="%1$s" name="%1$s" value="%4$s" class="regular-text"%5$s></td></tr>',
			esc_attr( $key ),
			esc_html( $field['label'] ),
			esc_attr( $field['type'] ),
			esc_attr( $value ),
			$step // Fixed string built above, safe to print.
		);
	}
	echo '</table>';
}

/**
 * Save the fields when a Floor is saved.
 *
 * @param int $post_id ID of the post being saved.
 */
function floor_catalog_save_meta( $post_id ) {
	// 1. Nonce: was this submitted from our form?
	if ( ! isset( $_POST['floor_catalog_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['floor_catalog_nonce'] ) ), 'floor_catalog_save' ) ) {
		return;
	}

	// Autosaves don't include our fields, so skip them.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// 2. Capability: may this user edit this post?
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// 3. Sanitize each field according to its type, then save.
	foreach ( floor_catalog_fields() as $key => $field ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.

		switch ( $field['type'] ) {
			case 'number':
				$clean = '' === $raw ? '' : (string) round( abs( (float) $raw ), 2 );
				break;
			case 'url':
				$clean = esc_url_raw( $raw );
				break;
			default:
				$clean = sanitize_text_field( $raw );
		}

		if ( '' === $clean ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $clean );
		}
	}
}
add_action( 'save_post_floor_product', 'floor_catalog_save_meta' );
