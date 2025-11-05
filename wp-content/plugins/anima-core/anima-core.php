<?php
/**
 * Plugin Name:       Anima Core
 * Description:       Core functionality for Anima headless WordPress.
 * Version:           1.0.0
 * Requires PHP:      8.2
 * Requires at least: 6.0
 * Author:            Anima Avatar Agency
 * Text Domain:       anima-core
 */

defined( 'ABSPATH' ) || exit;

define( 'ANIMA_CORE_VERSION', '1.0.0' );
define( 'ANIMA_CORE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Load plugin textdomain.
 */
function anima_core_load_textdomain(): void {
    load_plugin_textdomain( 'anima-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'anima_core_load_textdomain' );

// Activation logic.
require_once ANIMA_CORE_PLUGIN_DIR . 'inc/activate.php';
register_activation_hook( __FILE__, 'anima_core_activate' );

// Utility functions.
require_once ANIMA_CORE_PLUGIN_DIR . 'inc/utilities.php';

// Custom post types and taxonomies.
require_once ANIMA_CORE_PLUGIN_DIR . 'inc/cpt-proyecto.php';
require_once ANIMA_CORE_PLUGIN_DIR . 'inc/cpt-curso.php';
require_once ANIMA_CORE_PLUGIN_DIR . 'inc/taxonomias.php';

// REST API endpoint.
require_once ANIMA_CORE_PLUGIN_DIR . 'inc/rest-waitlist.php';

// GraphQL integration.
require_once ANIMA_CORE_PLUGIN_DIR . 'inc/graphql.php';
