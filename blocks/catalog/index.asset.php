<?php
/**
 * Script dependencies for index.js. WordPress reads this file when block.json says
 * "editorScript": "file:./index.js". Build tools usually generate it; we write it by hand.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-server-side-render' ),
	'version'      => FLOOR_CATALOG_VERSION,
);
