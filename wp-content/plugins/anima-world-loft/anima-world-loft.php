<?php
/**
 * Plugin Name:       Anima World – Loft
 * Description:       Provee el shortcode [anima_world_loft] para renderizar la escena interactiva del loft.
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Version:           0.1.0
 * Author:            Anima World
 * Text Domain:       anima-world-loft
 * Domain Path:       /languages
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ANIMA_WORLD_LOFT_VERSION', '0.1.0' );
define( 'ANIMA_WORLD_LOFT_URL', plugin_dir_url( __FILE__ ) );
define( 'ANIMA_WORLD_LOFT_PATH', plugin_dir_path( __FILE__ ) );

final class Anima_World_Loft_Plugin {

    private static ?Anima_World_Loft_Plugin $instance = null;

    private function __construct() {
        add_action( 'init', [ $this, 'register_shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
    }

    public static function instance(): Anima_World_Loft_Plugin {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function register_shortcode(): void {
        add_shortcode( 'anima_world_loft', [ $this, 'render_shortcode' ] );
    }

    public function register_assets(): void {
        wp_register_script(
            'anima-world-loft-three',
            'https://cdn.jsdelivr.net/npm/three@0.164.1/build/three.min.js',
            [],
            '0.164.1',
            true
        );

        wp_register_script(
            'anima-world-loft-orbit-controls',
            'https://cdn.jsdelivr.net/npm/three@0.164.1/examples/jsm/controls/OrbitControls.min.js',
            [ 'anima-world-loft-three' ],
            '0.164.1',
            true
        );

        wp_register_script(
            'anima-world-loft-gltf-loader',
            'https://cdn.jsdelivr.net/npm/three@0.164.1/examples/jsm/loaders/GLTFLoader.min.js',
            [ 'anima-world-loft-three' ],
            '0.164.1',
            true
        );

        wp_register_script(
            'anima-world-loft-tween',
            'https://cdn.jsdelivr.net/npm/@tweenjs/tween.js@18.6.4/dist/tween.umd.js',
            [],
            '18.6.4',
            true
        );

        wp_register_script(
            'anima-world-loft-frontend',
            ANIMA_WORLD_LOFT_URL . 'assets/js/anima-world-loft.js',
            [
                'anima-world-loft-three',
                'anima-world-loft-orbit-controls',
                'anima-world-loft-gltf-loader',
                'anima-world-loft-tween',
            ],
            ANIMA_WORLD_LOFT_VERSION,
            true
        );

        wp_register_style(
            'anima-world-loft-frontend',
            ANIMA_WORLD_LOFT_URL . 'assets/css/anima-world-loft.css',
            [],
            ANIMA_WORLD_LOFT_VERSION
        );
    }

    public function render_shortcode( array $attributes = [], ?string $content = null ): string {
        wp_enqueue_style( 'anima-world-loft-frontend' );
        wp_enqueue_script( 'anima-world-loft-frontend' );

        $defaults = [
            'class' => '',
        ];

        $attributes           = shortcode_atts( $defaults, $attributes, 'anima_world_loft' );
        $additional_classes   = array_filter( preg_split( '/\s+/', (string) $attributes['class'] ) );
        $sanitized_additional = array_map( 'sanitize_html_class', $additional_classes );
        $classes              = trim( 'anima-world-loft ' . implode( ' ', $sanitized_additional ) );

        ob_start();
        ?>
        <section class="<?php echo esc_attr( $classes ); ?>" aria-labelledby="anima-world-loft__title">
            <h2 id="anima-world-loft__title" class="screen-reader-text"><?php esc_html_e( 'Anima World Loft', 'anima-world-loft' ); ?></h2>
            <div class="anima-world-loft__canvas" role="application" aria-describedby="anima-world-loft__description">
                <canvas class="anima-world-loft__stage" data-anima-world-loft-stage></canvas>
                <div class="anima-world-loft__overlay" data-anima-world-loft-overlay>
                    <button type="button" class="anima-world-loft__start" data-anima-world-loft-start>
                        <?php esc_html_e( 'Iniciar tour', 'anima-world-loft' ); ?>
                    </button>
                </div>
            </div>
            <p id="anima-world-loft__description" class="screen-reader-text">
                <?php esc_html_e( 'Escena 3D interactiva con un recorrido guiado por el loft de Anima World.', 'anima-world-loft' ); ?>
            </p>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}

function anima_world_loft_bootstrap(): void {
    Anima_World_Loft_Plugin::instance();
}

anima_world_loft_bootstrap();
