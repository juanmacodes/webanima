<?php
/**
 * Plugin Name:       Anima Engine
 * Plugin URI:        https://example.com/anima-engine
 * Description:       Motor para gestionar contenidos inmersivos, avatares y experiencias XR.
 * Version:           1.0.0
 * Author:            Equipo Anima
 * Author URI:        https://example.com
 * Text Domain:       anima-engine
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 *
 * @package Anima\Engine
 */

defined( 'ABSPATH' ) || exit;

define( 'ANIMA_ENGINE_VERSION', '1.0.0' );
define( 'ANIMA_ENGINE_DB_VERSION', '1.0.0' );
define( 'ANIMA_ENGINE_API_VERSION', '1' );
define( 'ANIMA_ENGINE_PLUGIN_FILE', __FILE__ );
define( 'ANIMA_ENGINE_PATH', plugin_dir_path( __FILE__ ) );
define( 'ANIMA_ENGINE_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register(
    static function ( $class ) {
        if ( 0 !== strpos( $class, 'Anima\\Engine\\' ) ) {
            return;
        }

        $relative = substr( $class, strlen( 'Anima\\Engine\\' ) );
        $relative = str_replace( '\\', DIRECTORY_SEPARATOR, $relative );
        $path     = ANIMA_ENGINE_PATH . 'src/' . $relative . '.php';

        if ( file_exists( $path ) ) {
            require_once $path;
        }
    }
);

add_action(
    'plugins_loaded',
    static function () {
        load_plugin_textdomain( 'anima-engine', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        $plugin = new Anima\Engine\Plugin();
        $plugin->init();
    }
);

register_activation_hook(
    __FILE__,
    static function () {
        $activator = new Anima\Engine\Install\Activator();
        $activator->activate();
    }
);
