<?php
/**
 * Plugin Name: Anima World
 * Description: Shortcode [anima_world] que monta un canvas three.js accesible con hotspots.
 * Version: 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ANIMA_WORLD_VERSION', '0.1.0' );
define( 'ANIMA_WORLD_URL', plugin_dir_url( __FILE__ ) );

add_action( 'wp_enqueue_scripts', function () {
    wp_register_script( 'three', 'https://cdn.jsdelivr.net/npm/three@0.164/build/three.min.js', [], '0.164.0', true );
    wp_register_script( 'three-orbit', 'https://cdn.jsdelivr.net/npm/three@0.164/examples/jsm/controls/OrbitControls.min.js', [ 'three' ], '0.164.0', true );
    wp_register_script( 'three-gltf', 'https://cdn.jsdelivr.net/npm/three@0.164/examples/jsm/loaders/GLTFLoader.min.js', [ 'three' ], '0.164.0', true );
    wp_register_script( 'three-draco', 'https://cdn.jsdelivr.net/npm/three@0.164/examples/jsm/loaders/DRACOLoader.js', [ 'three' ], '0.164.0', true );
    wp_register_script( 'three-meshopt', 'https://cdn.jsdelivr.net/npm/three@0.164/examples/jsm/loaders/MeshoptDecoder.js', [], '0.164.0', true );
    wp_register_script( 'three-ktx2', 'https://cdn.jsdelivr.net/npm/three@0.164/examples/jsm/loaders/KTX2Loader.min.js', [ 'three' ], '0.164.0', true );
    wp_register_script( 'anima-world', ANIMA_WORLD_URL . 'assets/js/anima-world.js', [ 'three', 'three-orbit', 'three-gltf', 'three-draco', 'three-meshopt', 'three-ktx2' ], ANIMA_WORLD_VERSION, true );
    wp_register_style( 'anima-world', ANIMA_WORLD_URL . 'assets/css/anima-world.css', [], ANIMA_WORLD_VERSION );
    wp_localize_script( 'anima-world', 'animaWorld', [
        'gltf'      => 'https://cdn.jsdelivr.net/gh/google/model-viewer@v1.12.1/packages/shared-assets/models/Astronaut.glb',
        'fallback'  => __( 'Tu dispositivo no soporta WebGL. Explora la versión ligera.', 'anima-world' ),
        'hotspots'  => [
            [
                'id'       => 'events',
                'label'    => __( 'Eventos', 'anima-world' ),
                'url'      => home_url( '/anima-world/eventos' ),
                'position' => [ 'top' => '35%', 'left' => '68%' ],
            ],
            [
                'id'       => 'hubs',
                'label'    => __( 'Salas', 'anima-world' ),
                'url'      => home_url( '/anima-world/salas' ),
                'position' => [ 'top' => '52%', 'left' => '42%' ],
            ],
            [
                'id'       => 'shop',
                'label'    => __( 'Tienda', 'anima-world' ),
                'url'      => home_url( '/anima-world/tienda' ),
                'position' => [ 'top' => '60%', 'left' => '80%' ],
            ],
        ],
    ] );
} );

add_shortcode( 'anima_world', function () {
    wp_enqueue_style( 'anima-world' );
    wp_enqueue_script( 'anima-world' );
    ob_start();
    ?>
    <section class="anima-world" aria-labelledby="anima-world-title">
        <h2 id="anima-world-title" class="screen-reader-text"><?php esc_html_e( 'Anima World', 'anima-world' ); ?></h2>
        <div class="anima-world__canvas" role="application" aria-describedby="anima-world-desc" tabindex="0">
            <canvas data-anima-world></canvas>
            <div class="anima-world__fallback" hidden>
                <p><?php esc_html_e( 'Tu dispositivo no soporta WebGL. Explora la versión ligera.', 'anima-world' ); ?></p>
            </div>
            <div class="anima-world__hotspots" aria-live="polite"></div>
        </div>
        <p id="anima-world-desc" class="screen-reader-text"><?php esc_html_e( 'Escena interactiva en 3D con accesos a eventos, salas y tienda.', 'anima-world' ); ?></p>
        <button class="anima-world__low-spec" type="button" data-low-spec data-low-spec-label="<?php echo esc_attr__( 'Modo low-spec', 'anima-world' ); ?>" data-perf-label="<?php echo esc_attr__( 'Modo performance', 'anima-world' ); ?>"><?php esc_html_e( 'Modo low-spec', 'anima-world' ); ?></button>
    </section>
    <?php
    return ob_get_clean();
} );
