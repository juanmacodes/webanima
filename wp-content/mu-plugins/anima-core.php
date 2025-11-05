<?php
/**
 * Plugin Name: Anima Core
 * Description: Core functionality for the Anima immersive experience backend.
 * Author: Anima
 * Version: 1.0.0
 * Requires at least: 6.3
 * Requires PHP: 8.1
 */

define( 'ANIMA_CORE_VERSION', '1.0.0' );
define( 'ANIMA_CORE_PATH', __DIR__ . '/anima-core' );
define( 'ANIMA_CORE_URL', plugins_url( 'anima-core', __FILE__ ) );

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

require_once ANIMA_CORE_PATH . '/utils.php';
require_once ANIMA_CORE_PATH . '/media.php';
require_once ANIMA_CORE_PATH . '/clean.php';
require_once ANIMA_CORE_PATH . '/cors.php';
require_once ANIMA_CORE_PATH . '/cpts.php';
require_once ANIMA_CORE_PATH . '/rest.php';

if ( function_exists( 'acf_add_local_field_group' ) ) {
require_once ANIMA_CORE_PATH . '/acf.php';
}
