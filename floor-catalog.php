<?php
/**
 * Plugin Name:       Floor Catalog
 * Description:       A simple product catalog for flooring retailers: add floors in WP Admin and show them in a filterable grid with the [floor_catalog] shortcode.
 * Version:           1.4.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Kingsley Okine
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       floor-catalog
 *
 * HOW A PLUGIN WORKS (quick tour)
 * - WordPress reads the comment block above to list the plugin on the Plugins screen.
 * - This file is loaded on every request once the plugin is active.
 * - We don't run code directly. We "hook" our functions onto moments in WordPress's
 *   lifecycle with add_action() / add_filter(), and WordPress calls them at the right time.
 */

// Stop the file from being opened directly in a browser (security best practice).
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Constants we reuse everywhere. Prefix everything with "floor_catalog" / "FLOOR_CATALOG"
// so our names never clash with WordPress core or other plugins.
define( 'FLOOR_CATALOG_VERSION', '1.4.0' );
define( 'FLOOR_CATALOG_PATH', plugin_dir_path( __FILE__ ) ); // Server path, for PHP includes.
define( 'FLOOR_CATALOG_URL', plugin_dir_url( __FILE__ ) );   // Web URL, for CSS/JS files.

// Each file handles one job. Keeping them separate makes the plugin easier to read.
require_once FLOOR_CATALOG_PATH . 'includes/post-type.php';  // The "Floors" content type + Material categories.
require_once FLOOR_CATALOG_PATH . 'includes/meta-box.php';   // Extra fields: color, finish, price, visualizer link.
require_once FLOOR_CATALOG_PATH . 'includes/shortcode.php';  // The [floor_catalog] grid on the front end.
require_once FLOOR_CATALOG_PATH . 'includes/single.php';     // Details panel on each Floor's own page.
require_once FLOOR_CATALOG_PATH . 'includes/templates.php';  // Block templates for /floors/ and single Floor pages.
require_once FLOOR_CATALOG_PATH . 'includes/block.php';      // The Floor Catalog block (reuses the shortcode).

/**
 * Runs once when the plugin is activated.
 * We register the post type, then flush rewrite rules so URLs like /floors/oak-natural/ work immediately.
 */
function floor_catalog_activate() {
	floor_catalog_register_post_type();
	floor_catalog_register_taxonomy();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'floor_catalog_activate' );

/**
 * Runs once when the plugin is deactivated. Removes our URL rules.
 */
function floor_catalog_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'floor_catalog_deactivate' );
