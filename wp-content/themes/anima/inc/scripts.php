<?php
/**
 * Encolado de estilos y scripts.
 *
 * @package Anima
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action(
    'wp_enqueue_scripts',
    static function (): void {
        wp_enqueue_style( 'anima-style', ANIMA_THEME_URL . '/style.css', [], ANIMA_VERSION );
        wp_enqueue_style( 'anima-theme', ANIMA_ASSETS_URL . '/css/theme.css', [ 'anima-style' ], ANIMA_VERSION );

        wp_register_script( 'anima-frontend', ANIMA_ASSETS_URL . '/js/frontend.js', [], ANIMA_VERSION, true );
        $localize = [
            'restUrl'      => esc_url_raw( get_rest_url( null, '/anima/v1/waitlist' ) ),
            'nonce'        => wp_create_nonce( 'wp_rest' ),
            'successLabel' => __( 'Gracias por unirte, te contactaremos pronto.', 'anima' ),
            'errorLabel'   => __( 'Hubo un error al enviar el formulario. Intenta nuevamente.', 'anima' ),
        ];
        wp_localize_script( 'anima-frontend', 'AnimaFrontend', $localize );
        wp_enqueue_script( 'anima-frontend' );

        global $post;
        $has_loft = false;
        if ( is_page() ) {
            $has_loft = is_page( 'anima-world' );
        }

        if ( ! $has_loft && $post instanceof \WP_Post ) {
            $has_loft = has_shortcode( $post->post_content, 'anima_world_loft' );
        }

        if ( $has_loft ) {
            wp_register_script( 'three', 'https://cdn.jsdelivr.net/npm/three@0.157/build/three.min.js', [], '0.157.0', true );
            wp_register_script( 'anima-loft', ANIMA_ASSETS_URL . '/js/loft.js', [ 'three' ], ANIMA_VERSION, true );
            wp_enqueue_style( 'anima-loft', ANIMA_ASSETS_URL . '/css/anima-world.css', [], ANIMA_VERSION );
            wp_enqueue_script( 'three' );
            wp_enqueue_script( 'anima-loft' );
        }
    }
);

add_filter(
    'script_loader_tag',
    static function ( string $tag, string $handle ): string {
        $defer = [ 'anima-frontend', 'anima-loft' ];
        if ( in_array( $handle, $defer, true ) ) {
            return str_replace( ' src', ' defer src', $tag );
        }
        return $tag;
    },
    10,
    2
);
