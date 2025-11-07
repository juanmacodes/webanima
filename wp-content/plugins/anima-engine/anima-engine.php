<?php
/**
 * Plugin Name:       Anima Engine
 * Plugin URI:        https://example.com/anima-engine
 * Description:       Motor para gestionar contenidos, cursos y experiencias Anima con widgets de Elementor.
 * Version:           1.1.0
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

define( 'ANIMA_ENGINE_VERSION', '1.1.0' );
define( 'ANIMA_ENGINE_DB_VERSION', '1.0.0' );
define( 'ANIMA_ENGINE_API_VERSION', '1' );
define( 'ANIMA_ENGINE_PLUGIN_FILE', __FILE__ );
define( 'ANIMA_ENGINE_PATH', plugin_dir_path( __FILE__ ) );
define( 'ANIMA_ENGINE_URL', plugin_dir_url( __FILE__ ) );

require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/cpt-curso.php';
require_once __DIR__ . '/inc/taxonomias-curso.php';
require_once __DIR__ . '/inc/cpt-avatar.php';
require_once __DIR__ . '/inc/admin-metaboxes.php';
require_once __DIR__ . '/inc/rest-waitlist.php';
require_once __DIR__ . '/inc/shortcodes.php';
require_once __DIR__ . '/elementor/class-anima-elementor-loader.php';

add_action(
    'after_setup_theme',
    static function () {
        add_image_size( 'an_card_16x10', 1200, 750, true );
        add_image_size( 'an_square', 1000, 1000, true );
    }
);

add_action(
    'wp_enqueue_scripts',
    static function () {
        wp_enqueue_style(
            'anima-engine-ui',
            ANIMA_ENGINE_URL . 'assets/css/anima-ui.css',
            [],
            ANIMA_ENGINE_VERSION
        );

        wp_enqueue_script(
            'anima-engine-ui',
            ANIMA_ENGINE_URL . 'assets/js/anima-ui.js',
            [],
            ANIMA_ENGINE_VERSION,
            true
        );

        wp_localize_script(
            'anima-engine-ui',
            'animaEngineSettings',
            [
                'restWaitlist'   => rest_url( 'anima/v' . ANIMA_ENGINE_API_VERSION . '/waitlist' ),
                'nonce'          => wp_create_nonce( 'wp_rest' ),
                'errorGeneric'   => __( 'No se pudo completar tu solicitud. Inténtalo más tarde.', 'anima-engine' ),
                'errorName'      => __( 'Introduce tu nombre.', 'anima-engine' ),
                'errorEmail'     => __( 'Introduce un email válido.', 'anima-engine' ),
                'errorConsent'   => __( 'Debes aceptar la política de privacidad.', 'anima-engine' ),
                'successWaitlist'=> __( '¡Gracias! Te avisaremos en cuanto haya plaza.', 'anima-engine' ),
                'successRedirect'=> __( 'Te hemos llevado a la página de inscripción.', 'anima-engine' ),
                'lightboxLabel'  => __( 'Visor 3D del avatar', 'anima-engine' ),
            ]
        );

        wp_register_script(
            'anima-projects-tabs',
            ANIMA_ENGINE_URL . 'assets/js/anima-projects-tabs.js',
            [ 'elementor-frontend' ],
            ANIMA_ENGINE_VERSION,
            true
        );
    }
);

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
