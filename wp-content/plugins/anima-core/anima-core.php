<?php
/**
 * Plugin Name: Anima Core
 * Description: Funcionalidades núcleo para la agencia de avatares Anima: CPTs, taxonomías, metacampos, shortcode y endpoint REST.
 * Version: 1.0.0
 * Author: Anima Avatar Agency
 * Text Domain: anima-core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ANIMA_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'ANIMA_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once ANIMA_CORE_PATH . 'includes/post-types.php';
require_once ANIMA_CORE_PATH . 'includes/taxonomies.php';
require_once ANIMA_CORE_PATH . 'includes/meta-fields.php';
require_once ANIMA_CORE_PATH . 'includes/shortcodes.php';
require_once ANIMA_CORE_PATH . 'includes/rest-api.php';
require_once ANIMA_CORE_PATH . 'includes/integrations.php';
require_once ANIMA_CORE_PATH . 'includes/settings.php';
require_once ANIMA_CORE_PATH . 'includes/security.php';

/**
 * Inicializa todas las funcionalidades principales del plugin.
 */
function anima_bootstrap() {
    anima_register_post_types();
    anima_register_taxonomies();
    anima_register_meta_boxes();
    anima_register_shortcodes();
}
add_action( 'init', 'anima_bootstrap' );

add_action( 'rest_api_init', 'anima_register_rest_routes' );
add_action( 'graphql_register_types', 'anima_register_graphql_hooks' );
add_action( 'bp_include', 'anima_register_buddypress_hooks' );
add_action( 'init', 'anima_register_contact_settings' );
add_action( 'after_setup_theme', 'anima_register_security_headers' );
